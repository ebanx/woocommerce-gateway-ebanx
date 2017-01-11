<?php

require 'ebanx-php/src/autoload.php';

if (!defined('ABSPATH')) {
    exit;
}

abstract class WC_EBANX_Gateway extends WC_Payment_Gateway
{
    protected static $ebanx_params = array();
    protected static $initializedGateways = 0;
    protected static $totalGateways = 0;

    public function __construct()
    {
        self::$totalGateways++;

        $this->userId = get_current_user_id();

        $this->configs = new WC_EBANX_Global_Gateway();

        $this->is_sandbox_mode = $this->configs->settings['sandbox_mode_enabled'] === 'yes';

        $this->private_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];

        $this->public_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['live_public_key'];

        if ($this->configs->settings['debug_enabled'] === 'yes') {
            $this->log = new WC_Logger();
        }

        add_action('wp_enqueue_scripts', array($this, 'checkout_assets'), 100);

        add_filter('woocommerce_checkout_fields', function ($fields) {
            $cpf = get_user_meta($this->userId, '_ebanx_billing_brazil_document');
            $rut = get_user_meta($this->userId, '_ebanx_billing_chile_document');
            $birth_date_br = get_user_meta($this->userId, '_ebanx_billing_brazil_birth_date');
            $birth_date_cl = get_user_meta($this->userId, '_ebanx_billing_chile_birth_date');

            $fields['billing']['ebanx_billing_brazil_birth_date'] = array(
                'type'  => 'text',
                'label' => __('Birth Date', 'woocommerce-gateway-ebanx'),
                'default' => isset($birth_date_br[0]) ? $birth_date_br[0] : ''
            );
            $fields['billing']['ebanx_billing_brazil_document'] = array(
                'type'     => 'text',
                'label'    => 'CPF',
                'default' => isset($cpf[0]) ? $cpf[0] : ''
            );
            $fields['billing']['ebanx_billing_chile_birth_date'] = array(
                'type'  => 'text',
                'label' => __('Birth Date', 'woocommerce-gateway-ebanx'),
                'default' => isset($birth_date_cl[0]) ? $birth_date_cl[0] : ''
            );
            $fields['billing']['ebanx_billing_chile_document'] = array(
                'type'     => 'text',
                'label'    => 'RUT',
                'default' => isset($rut[0]) ? $rut[0] : ''
            );
            return $fields;
        });

        $this->supports = array(
            // 'subscriptions',
            'refunds',
        );

        $this->icon = $this->show_icon();
    }

    public function show_icon()
    {
        return plugins_url('/assets/images/' . $this->id . '.png', plugin_basename(dirname(__FILE__)));
    }

    public function checkout_assets()
    {
        if (is_checkout()) {
            wp_enqueue_script('woocommerce_ebanx_checkout_fields', plugins_url('assets/js/checkout-fields.js', WC_EBANX::DIR));
        }
        if (
            is_wc_endpoint_url( 'order-pay' ) ||
            is_wc_endpoint_url( 'order-received' ) ||
            is_wc_endpoint_url( 'view-order' ) ||
            is_checkout()
        ) {
            wp_enqueue_style(
                'woocommerce_ebanx_paying_via_ebanx_style',
                plugins_url('assets/css/paying-via-ebanx.css', WC_EBANX::DIR)
            );

            static::$ebanx_params = array(
                'key'  => $this->public_key,
                'mode' => $this->is_sandbox_mode ? 'test' : 'production',
            );

            self::$initializedGateways++;

            if(self::$initializedGateways === self::$totalGateways)
                wp_localize_script('woocommerce_ebanx', 'wc_ebanx_params', apply_filters('wc_ebanx_params', static::$ebanx_params));
        }
    }
    public function admin_options()
    {
        include dirname(__FILE__) . '/admin/views/html-admin-page.php';
    }

    public function is_available()
    {
        $this->language = $this->getTransactionAddress('country');

        return parent::is_available() && !empty($this->public_key) && !empty($this->private_key) && $this->enabled === 'yes';
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = wc_get_order($order_id);

        $hash = get_post_meta($order->id, '_ebanx_payment_hash', true);

        if (!$order || is_null($amount) || !$hash) {
            return false;
        }

        $data = array(
            'hash'        => $hash,
            'amount'      => $amount,
            'operation'   => 'request',
            'description' => $reason,
        );

        $config = [
            'integrationKey' => $this->private_key,
            'testMode'       => $this->is_sandbox_mode,
        ];

        \Ebanx\Config::set($config);

        $request = \Ebanx\EBANX::doRefund($data);

        if ($request->status !== 'SUCCESS') {
            return false;
        }

        $order->add_order_note(sprintf('Refund requested to EBANX %s - Refund ID: %s - Reason: %s', wc_price($amount), $request->refund->id, $reason));

        $refunds = current(get_post_meta((int) $order_id, "_ebanx_payment_refunds"));

        $request->refund->wc_refund = current($order->get_refunds());

        $refunds[] = $request->refund;

        update_post_meta($order->id, "_ebanx_payment_refunds", $refunds);

        return true;
    }

    protected function request_data($order)
    {
        $data = array(
            'mode'      => 'full',
            'operation' => 'request',
            'payment'   => array(
                'user_value_1'          => 'name=plugin',
                'user_value_2'          => 'value=woocommerce',
                'user_value_3'          => 'version='.WC_EBANX::VERSION,
                'country'               => $order->get_address()['country'],
                'currency_code'         => WC_EBANX_Gateway_Utils::CURRENCY_CODE_USD, // TODO: Dynamic
                'name'                  => $order->get_address()['first_name'] . " " . $order->get_address()['last_name'],
                'email'                 => $order->get_address()['email'],
                "phone_number"          => $order->get_address()['phone'],
                'amount_total'          => $order->get_total(),
                'order_number'          => $order->id,
                'merchant_payment_code' => $order->id . '-' . md5(rand(123123, 9999999)),
                'items' => array_map(function($prd) {
                    $p = new \stdClass();

                    $p->name = $prd['name'];
                    $p->unit_price = $prd['line_subtotal'];
                    $p->quantity = $prd['qty'];
                    $p->type = $prd['type'];

                    return $p;
                }, $order->get_items()),
            )
        );

        if (!empty($this->configs->settings['due_date_days']) && in_array($this->api_name, array_keys(WC_EBANX_Gateway_Utils::$CASH_PAYMENTS_TIMEZONES)))
        {
            $date = new DateTime();

            $date->setTimezone(new DateTimeZone(WC_EBANX_Gateway_Utils::$CASH_PAYMENTS_TIMEZONES[$this->api_name]));
            $date->modify("+{$this->configs->settings['due_date_days']} day");

            $data['payment']['due_date'] = $date->format('d/m/Y');
        }

        if (trim(strtolower(WC()->customer->get_shipping_country())) === WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL) {
            if (empty($_POST['ebanx_billing_brazil_document']) ||
                empty($_POST['ebanx_billing_brazil_birth_date']) ||
                empty($_POST['billing_postcode']) ||
                empty($_POST['billing_address_1']) ||
                empty($_POST['billing_city']) ||
                empty($_POST['billing_state'])
            ) {
                throw new Exception('INVALID-FIELDS');
            }

            $_POST['ebanx_billing_document'] = $_POST['ebanx_billing_brazil_document'];
            $_POST['ebanx_billing_birth_date'] = $_POST['ebanx_billing_brazil_birth_date'];
        }

        if (trim(strtolower(WC()->customer->get_shipping_country())) === WC_EBANX_Gateway_Utils::COUNTRY_CHILE) {
            if (empty($_POST['ebanx_billing_chile_document']) || empty($_POST['ebanx_billing_chile_birth_date'])) {
                throw new Exception('INVALID-FIELDS');
            }

            $_POST['ebanx_billing_document'] = $_POST['ebanx_billing_chile_document'];
            $_POST['ebanx_billing_birth_date'] = $_POST['ebanx_billing_chile_birth_date'];
        }

        $addresses = $_POST['billing_address_1'];

        if (!empty($_POST['billing_address_2'])) {
            $addresses .= " $_POST[billing_address_2]";
        }

        $addresses = WC_Ebanx_Gateway_Utils::split_street($addresses);

        $street_number = empty($addresses['houseNumber']) ? 'S/N' : trim($addresses['houseNumber'] . ' ' . $addresses['additionToAddress2']);

        $data['payment'] = array_merge($data['payment'], array(
            'document'      => $_POST['ebanx_billing_document'],
            'birth_date'    => $_POST['ebanx_billing_birth_date'],
            'zipcode'       => $_POST['billing_postcode'],
            'address'       => $_POST['billing_address_1'],
            'street_number' => $street_number,
            'city'          => $_POST['billing_city'],
            'state'         => $_POST['billing_state'],
        ));

        return $data;
    }

    protected function getTransactionAddress($attr = '')
    {
        if (empty(WC()->customer) || is_admin()) {
            return false;
        }

        if (empty($_POST['billing_country']) && empty(WC()->customer->get_shipping_country())) {
            throw new Exception('INVALID-BILLING-COUNTRY');
        }

        if (!empty($_POST['billing_country'])) {
            $this->address['country'] = trim(strtolower($_POST['billing_country']));
        } else {
            $this->address['country'] = trim(strtolower(WC()->customer->get_shipping_country()));
        }

        if ($attr !== '' && !empty($this->address[$attr])) {
            return $this->address[$attr];
        }

        return $this->address;
    }

    public function process_payment($order_id)
    {
        try {
            $order = wc_get_order($order_id);

            if ($order->get_total() > 0) {
                $data = $this->request_data($order);

                $config = [
                    'integrationKey' => $this->private_key,
                    'testMode'       => $this->is_sandbox_mode,
                ];

                \Ebanx\Config::set($config);
                \Ebanx\Config::setDirectMode(true);

                $request = \Ebanx\EBANX::doRequest($data);

                $this->process_response($request, $order); // TODO: What make when response_Error called?
            } else {
                $order->payment_complete();
            }

            return $this->dispatch(array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ));
        } catch (Exception $e) {

            $this->language = $this->getTransactionAddress('country');

            $code = $e->getMessage();

            $languages = array(
                'mx' => 'es',
                'cl' => 'es',
                'pe' => 'es',
                'co' => 'es',
                'br' => 'pt-br',
            );
            $language = $languages[$this->language];

            $errors = array(
                'pt-br' => array(
                    'GENERAL'                    => 'Não foi possível concluir a compra. Por favor, tente novamente ou entre em contato com o site.',
                    'BP-DPAR-4'                  => 'Invalid integration key.',
                    'BP-DR-13'                   => 'Informe o seu nome completo.',
                    'BP-DR-15'                   => 'Informe um email válido.',
                    'BP-DR-19'                   => 'Informe a sua data de nascimento no formato dia/mes/ano, por exemplo, 30/10/1980.',
                    'BP-DR-23'                   => 'O número do seu CPF é obrigatório.',
                    'BP-DR-24'                   => 'Informe o seu CEP.',
                    'BP-DR-25'                   => 'Informe o seu endereço.',
                    'BP-DR-26'                   => 'O número da sua residência é obrigatório.',
                    'BP-DR-27'                   => 'Informe a sua cidade.',
                    'BP-DR-28'                   => 'Informe o seu estado.',
                    'BP-DR-29'                   => 'Informe um estado do Brasil válido.',
                    'BP-DR-30'                   => 'O país deve ser Brasil.',
                    'BP-DR-31'                   => 'Informe um telefone celular.',
                    'BP-DR-32'                   => 'O seu telefone celular deve ser um número válido.',
                    'BP-DR-39'                   => 'Seu nome, cpf e data de nascimento não coincidem, por favor, revise seus dados e tente novamente.',
                    'BP-DR-40'                   => 'Você atingiu o limite de pagamento.',
                    'BP-DR-48'                   => 'Preencha as informações de cartão de crédito.',
                    'BP-DR-49'                   => 'Insira o número do cartão de crédito.',
                    'BP-DR-51'                   => 'Insira o nome que está impresso no cartão de crédito.',
                    'BP-DR-52'                   => 'O nome do cartão deve ter até 50 caracteres.',
                    'BP-DR-54'                   => 'Digite o CVV que está impresso no cartão.',
                    'BP-DR-55'                   => 'Digite o CVV corretamente.',
                    'BP-DR-56'                   => 'Digite a data de validade do seu cartão.',
                    'BP-DR-57'                   => 'A sua data deve estar no formato mes/ano, por exemplo, 12/2020.',
                    'BP-DR-59'                   => 'A data é inferior a permitida.',
                    'BP-DR-61'                   => 'Não foi possível utilizar este cartão de crédito. Contate o site informando este código: BP-DR-61.',
                    'BP-DR-75'                   => 'O número do cartão de crédito é inválido.',
                    'BP-DR-77'                   => 'Este país não está habilitado.',
                    'BP-DR-78'                   => 'Este site não vende para este país.',
                    'BP-DR-79'                   => 'O número de parcelas não é permitido, por favor, escolha outro.',
                    'BP-DR-83'                   => 'Identificamos que seu cartão é estrangeiro, por favor, use outro.',
                    'BP-DR-84'                   => 'Identificamos que esta compra já foi processada anteriormente.',
                    'BP-DR-89'                   => 'O número de parcelas não é permitida, entre em contato com o site.',
                    'BP-DR-95'                   => 'O nome impresso no cartão não é válido, número não são permitidos.',
                    'BP-DR-97'                   => 'Compras parceladas não são permitidas em cartões pré pagos.',
                    'BP-DR-98'                   => 'O país relacionado ao email digitado não corresponde ao país do método de pagamento.',
                    'BP-DR-100'                  => 'Compras parceladas não são permitidas em cartões de débito.',
                    'MISSING-CARD-PARAMS'        => 'Verifique se os dados do cartão de crédito estão corretos.',
                    'MISSING-DEVICE-FINGERPRINT' => 'Algo aconteceu e não conseguimos concluir a sua compra. Por favor tente novamente.',
                    'MISSING-CVV'                => 'Por favor digite o CVV do seu cartão de crédito.',
                    'MISSING-INSTALMENTS'        => 'Por favor escolha em quantas parcelas você quer pagar.',
                    'MISSING-BANK-NAME'          => 'Escolha um banco que deseja efetuar a sua compra.',
                    'INVALID-SAFETYPAY-TYPE'     => 'Escolha uma opção para o método de pagamento SafetyPay.',
                    'INVALID-FIELDS'             => 'Alguns campos não foram preenchidos corretamente. Por favor, verifique e tente novamente.',
                    'INVALID-BILLING-COUNTRY'    => 'Por favor, escolha um país.',
                    'INVALID-ADDRESS'            => 'Insira o seu endereço completo com o número da casa, apartamento ou estabelecimento.',
                    'AR-TS-0'                    => 'Ops! Sua compra não foi autorizada. Entre em contato com a operadora do seu cartão para mais informações.',
                    'AR-TS-1'                    => 'Ops! Sua compra não foi autorizada. Tente novamente em alguns instantes.',
                ),
                'es'    => array(
                    'GENERAL'                    => 'No pudimos concluir tu compra. Por favor intenta nuevamente o entra en contacto con el sitio web.',
                    'BP-DR-13'                   => 'Por favor, escribe tu nombre completo.',
                    'BP-DR-15'                   => 'El email no es válido. ',
                    'BP-DR-19'                   => 'Escribe tu fecha de nacimiento en el formato DD/MM/AA.',
                    'BP-DR-23'                   => 'O número do seu CPF é obrigatório.',
                    'BP-DR-24'                   => 'Por favor, escribe tu código postal.',
                    'BP-DR-25'                   => 'Por favor, escribe tu dirección.',
                    'BP-DR-26'                   => 'Tu número de residencia es obligatorio.',
                    'BP-DR-27'                   => 'Por favor, dinos tu ciudad de residencia.',
                    'BP-DR-28'                   => 'Por favor, dinos tu estado de residencia.',
                    'BP-DR-29'                   => 'Escribe un estado válido.',
                    'BP-DR-30'                   => 'Tú país debe ser Brazil.',
                    'BP-DR-31'                   => 'Por favor, dinos tu número de celular.',
                    'BP-DR-32'                   => 'El número de teléfono no es válido. Intenta de nuevo.',
                    'BP-DR-39'                   => 'Seu nome, cpf e data de nascimento não coincidem, por favor, revise seus dados e tente novamente.',
                    'BP-DR-40'                   => 'Disculpa, has alcanzado tu límite de compra.',
                    'BP-DR-48'                   => 'Preencha as informações de cartão de crédito.',
                    'BP-DR-49'                   => 'Por favor, introduce el número de tarjeta de crédito.',
                    'BP-DR-51'                   => 'Por favor, introduce el nombre como está en tu tarjeta de crédito.',
                    'BP-DR-52'                   => 'El nombre en la tarjeta no debe superar los 50 caracteres.',
                    'BP-DR-54'                   => 'Por favor, introduce el CVV impreso en la tarjeta.',
                    'BP-DR-55'                   => 'Por favor, introduce el CVV correctamente.',
                    'BP-DR-56'                   => 'Por favor, introduce la fecha de vencimiento de tu tarjeta.',
                    'BP-DR-57'                   => 'Por favor, escribe la fecha en el formato MM/AAAA',
                    'BP-DR-59'                   => 'Por favor, introduce una fecha válida.',
                    'BP-DR-61'                   => 'Disculpa pero no fue posible procesar tu tarjeta de crédito. Contacta el sitio web informado este código: BP-DR-61.',
                    'BP-DR-75'                   => 'El número de tarjeta de crédito es inválido.',
                    'BP-DR-77'                   => 'Disculpa, el país que has declarado no está habilitado.',
                    'BP-DR-78'                   => 'Disculpa, aun no vendemos en el país que declaraste.',
                    'BP-DR-79'                   => 'Disculpa, selecciona otro número de meses sin intereses.',
                    'BP-DR-83'                   => 'Disculpa, no aceptamos tarjetas de crédito extranjeros. Por favor usa otro.',
                    'BP-DR-84'                   => 'Esta compra ya fue procesada.',
                    'BP-DR-89'                   => 'El número de meses sin intereses seleccionado es inválido. Entra en contacto con el sitio web.',
                    'BP-DR-95'                   => 'El nombre escrito en la tarjeta no es válido. Números no son permitidos.',
                    'BP-DR-97'                   => 'Disculpa, la opción de pago "meses sin intereses" no es permitida para tarjetas pre-pago.',
                    'BP-DR-98'                   => 'O país relacionada o correo electrónico digitados no corresponde el método del pago.',
                    'BP-DR-100'                  => 'Disculpa, la opción de pago "meses sin intereses" no es permitida para tarjetas de débito.',
                    'MISSING-CARD-PARAMS'        => 'Por favor, verifica que la información de la tarjeta esté correcta.',
                    'MISSING-DEVICE-FINGERPRINT' => 'Hemos encontrado un error y no fue posible concluir la compra. Por favor intenta de nuevo.',
                    'MISSING-CVV'                => 'Por favor, introduce el CVV de tu tarjeta de crédito.',
                    'MISSING-INSTALMENTS'        => 'Por favor, escoge en cuántos meses sin intereses deseas pagar.',
                    'MISSING-BANK-NAME'          => 'Por favor, escoge el banco para finalizar la compra.',
                    'INVALID-SAFETYPAY-TYPE'     => 'Por favor, escoge una opción para el método de pago SafetyPay.',
                    'INVALID-FIELDS'             => 'Algunos campos no fueron llenados correctamente. Por favor verifica e inténtalo de nuevo.',
                    'INVALID-BILLING-COUNTRY'    => 'Por favor, escoge un país.',
                    'INVALID-ADDRESS'            => 'Por favor, introduce tu dirección completa. Número de residencia o apartamento.',
                    'AR-TS-0'                    => 'Ups! Su compra no ha sido autorizada. Por favor, póngase en contacto con el operador de la tarjeta para obtener más informaciónes.',
                    'AR-TS-1'                    => 'Ups! Su compra no ha sido autorizada. Por favor, inténtelo de nuevo en unos instantes.',
                ),
            );

            $message = !empty($errors[$language][$code]) ? $errors[$language][$code] : $errors[$language]['GENERAL'] . " ({$code})";

            WC()->session->set('refresh_totals', true);
            WC_Ebanx::log('EBANX Error: $message');

            wc_add_notice($message, 'error');
            return;
        }
    }

    public static function thankyou_page($order)
    {
        $payment_method = $order->payment_method;
        $customer_billing_country = get_post_meta($order->id, '_billing_country');
        $customer_country = strtolower($customer_billing_country[0]);
        $card_brand_name = get_post_meta($order->id, '_cards_brand_name');
        $order_amount = get_post_meta($order->id, '_order_total')[0];
        $instalments_number = get_post_meta($order->id, '_instalments_number')[0];
        $instalments_amount = round($order_amount / $instalments_number, 2);
        $masked_card = get_post_meta($order->id, '_masked_card_number')[0];
        $customer_email = get_post_meta($order->id, '_billing_email', true);
        $customer_name = get_post_meta($order->id, '_billing_first_name', true);
        $merchant = $order->merchant;

        $languages = array(
            'mx' => 'es',
            'cl' => 'es',
            'pe' => 'es',
            'co' => 'es',
            'br' => 'pt-br',
        );

        $language = $languages[$customer_country];

        $messages = array(
            'pt-br' => array(
                'payment_approved' => sprintf('Pagamento confirmado, %s.', $customer_name),
                'important_data' => '<strong>Resumo da compra:</strong>',
                'total_amount' => 'Valor:',
                'installments' => sprintf('Pagamento parcelado em %s vezes', $instalments_number),
                'single_installment' => 'Pagamento à vista',
                'card_last_numbers' => sprintf('Pago com Cartão %s:', ucwords($card_brand_name[0])),
                'thanks_message' => 'Obrigado por ter comprado conosco.',
                'completed' => array(
                    'title' => 'Obrigado! O pagamento foi realizado com sucesso',
                    'thanks_message' => sprintf('Um comprovante foi enviado para o email <strong>%s</strong>.', $customer_email),
                )
            ),
            'es' => array(
                'payment_approved' => sprintf('Pago aprobado, %s.', $customer_name),
                'important_data' => '<strong>Resumo de la compra:</strong>',
                'total_amount' => 'Valor:',
                'installments' => sprintf('Pago en %s meses sin intereses', $instalments_number),
                'single_installment' => 'Pago en una sola exhibición',
                'card_last_numbers' => sprintf('Pago con tarjeta %s:', ucwords($card_brand_name[0])),
                'thanks_message' => 'Gracias por haber comprado con nosotros.',
                'completed' => array(
                    'title' => '¡Gracias! Solicitud realizada corrrectamente.',
                    'thanks_message' => sprintf('La confirmación de tu pedido será enviado a <strong>%s</strong>.', $customer_email),
                )
            )
        );

        wc_get_template(
            $payment_method !== 'ebanx-credit-card' ? 'payment-completed.php' : 'credit-card/payment-completed.php',
            array(
                'order_amount' => $order_amount,
                'instalments_number' => $instalments_number,
                'instalments_amount' => $instalments_amount,
                'masked_card' => $masked_card,
                't' => $messages[$language],
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    protected function dispatch($data)
    {
        WC()->cart->empty_cart();

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        // To save only on DB to internal use
        update_post_meta($order->id, '_ebanx_payment_hash', $request->payment->hash);
        update_post_meta($order->id, '_ebanx_payment_open_date', $request->payment->open_date);
        if (isset($_POST['billing_email'])) {
            update_post_meta($order->id, '_ebanx_payment_customer_email', sanitize_email($_POST['billing_email']));
        }

        if (isset($_POST['billing_phone'])) {
            update_post_meta($order->id, '_ebanx_payment_customer_phone', sanitize_text_field($_POST['billing_phone']));
        }

        if (isset($_POST['billing_address_1'])) {
            update_post_meta($order->id, '_ebanx_payment_customer_address', sanitize_text_field($_POST['billing_address_1']));
        }

        // To show to the merchant
        update_post_meta($order->id, 'Payment\'s Hash', $request->payment->hash);

        $this->save_user_meta_fields($order);
    }

    protected function process_response_error($request, $order)
    {
        $code = $request->status_code;

        $error_message = 'EBANX: An error occurred: ' . $code . ' - ' . $request->status_message;

        $order->update_status('failed', $error_message);
        $order->add_order_note($error_message);

        throw new Exception($code);
    }

    protected function process_response($request, $order)
    {
        WC_Ebanx::log("Processing response: " . print_r($request, true));

        if ($request->status == 'ERROR') {
            return $this->process_response_error($request, $order);
        }

        $message = 'Payment approved. Hash: ' . $request->payment->hash;

        WC_Ebanx::log($message);

        if ($request->payment->pre_approved && $request->payment->status == 'CO') {
            $order->add_order_note(__('EBANX: Transaction paid.', 'woocommerce-gateway-ebanx'));
            $order->payment_complete($request->payment->hash);
            $order->update_status('completed');
        }

        $this->save_order_meta_fields($order, $request);
    }

    protected function save_user_meta_fields($order)
    {
        if ($this->userId) {
            if (trim(strtolower($order->get_address()['country'])) === WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL) {
                update_user_meta($this->userId, '_ebanx_billing_brazil_document', $_POST['ebanx_billing_brazil_document']);
                update_user_meta($this->userId, '_ebanx_billing_brazil_birth_date', $_POST['ebanx_billing_brazil_birth_date']);
            }

            if (trim(strtolower($order->get_address()['country'])) === WC_EBANX_Gateway_Utils::COUNTRY_CHILE) {
                update_user_meta($this->userId, '_ebanx_billing_chile_document', $_POST['ebanx_billing_document']);
                update_user_meta($this->userId, '_ebanx_billing_chile_birth_date', $_POST['ebanx_billing_birth_date']);
            }
        }
    }

    final public function process_hook(array $codes, $notificationType)
    {
        $config = [
            'integrationKey' => $this->private_key,
            'testMode'       => $this->is_sandbox_mode,
        ];

        \Ebanx\Config::set($config);

        $data = \Ebanx\EBANX::doQuery($codes);

        $order = reset(get_posts(array(
            'meta_query' => array(
                array(
                    'key'   => '_ebanx_payment_hash',
                    'value' => $data->payment->hash,
                ),
            ),
            'post_type'  => 'shop_order',
        )));

        $order = new WC_Order($order->ID);

        // TODO: if (empty($order)) {}
        // TODO: if ($data->status != "SUCCESS")

        switch (strtoupper($notificationType)) {
            case 'REFUND':
                $refunds = current(get_post_meta($order->id, "_ebanx_payment_refunds"));

                foreach ($refunds as $k => $ref) {
                    foreach ($data->payment->refunds as $refund) {
                        if ($ref->id == $refund->id) {
                            if ($refund->status == 'CO' && $refunds[$k]->status != 'CO') {
                                $order->add_order_note(sprintf('Refund confirmed to EBANX - Refund ID: %s', $refund->id));
                            }
                            if ($refund->status == 'CA' && $refunds[$k]->status != 'CA') {
                                $order->add_order_note(sprintf('Refund canceled to EBANX - Refund ID: %s', $refund->id));
                            }

                            $refunds[$k]->status       = $refund->status; // status == co save note
                            $refunds[$k]->cancel_date  = $refund->cancel_date;
                            $refunds[$k]->request_date = $refund->request_date;
                            $refunds[$k]->pending_date = $refund->pending_date;
                            $refunds[$k]->confirm_date = $refund->confirm_date;
                        }
                    }
                }

                update_post_meta($order->id, "_ebanx_payment_refunds", $refunds);
                break;
            case 'UPDATE':
                switch (strtoupper($data->payment->status)) {
                    case 'CO':
                        $order->update_status('completed');
                        break;
                    case 'CA':
                        $order->update_status('cancelled');
                        break;
                    case 'PE':
                        $order->update_status('pending');
                        break;
                    case 'OP':
                        $order->update_status('processing');
                        break;
                }
                // TODO: How to call process response to finish the transaction and save meta fields?
                break;
        };
    }
}
