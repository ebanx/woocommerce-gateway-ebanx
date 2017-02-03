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

    /**
     * Constructor
     */
    public function __construct()
    {
        self::$totalGateways++;

        $this->userId = get_current_user_id();

        $this->configs = new WC_EBANX_Global_Gateway();

        $this->is_sandbox_mode = ($this->configs->settings['sandbox_mode_enabled'] === 'yes');

        $this->private_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_private_key'] : $this->configs->settings['live_private_key'];

        $this->public_key = $this->is_sandbox_mode ? $this->configs->settings['sandbox_public_key'] : $this->configs->settings['live_public_key'];

        if ($this->configs->settings['debug_enabled'] === 'yes') {
            $this->log = new WC_Logger();
        }

        add_action('wp_enqueue_scripts', array($this, 'checkout_assets'), 100);

        add_filter('woocommerce_checkout_fields', array($this, 'checkout_fields'));

        $this->supports = array(
            // 'subscriptions',
            'refunds',
        );

        $this->icon = $this->show_icon();

        $this->names = $this->get_billing_field_names();
    }

    /**
     * Check if the method is available to show to the users
     *
     * @return boolean
     */
    public function is_available()
    {
        $this->language = $this->getTransactionAddress('country');

        return parent::is_available() && !empty($this->public_key) && !empty($this->private_key) && $this->enabled === 'yes';
    }

    /**
     * Insert custom billing fields on checkout page
     *
     * @param  array $fields WooCommerce's fields
     * @return array         The new fields
     */
    public function checkout_fields($fields) {
    	$fields_options = array();
    	if (isset($this->configs->settings['brazil_taxes_options']) && is_array($this->configs->settings['brazil_taxes_options'])) {
    		$fields_options = $this->configs->settings['brazil_taxes_options'];
    	}

    	$disable_own_fields = isset($this->configs->settings['checkout_manager_enabled']) && $this->configs->settings['checkout_manager_enabled'] === "yes";

        $cpf = get_user_meta($this->userId, '_ebanx_billing_brazil_document', true);
        $birth_date_br = get_user_meta($this->userId, '_ebanx_billing_brazil_birth_date', true);

        $cnpj = get_user_meta($this->userId, '_ebanx_billing_brazil_cnpj', true);

        $rut = get_user_meta($this->userId, '_ebanx_billing_chile_document', true);
        $birth_date_cl = get_user_meta($this->userId, '_ebanx_billing_chile_birth_date', true);

        $ebanx_selector = array(
        	'type' => 'select',
        	'label' => __('Select an option', 'woocommerce-gateway-ebanx'),
        	'default' => 'cpf',
        	'class' => array('ebanx_billing_brazil_selector'),
        	'options' => array(
        		'cpf' => __('CPF - Individuals', 'woocommerce-gateway-ebanx'),
        		'cnpj' => __('CNPJ - Companies', 'woocommerce-gateway-ebanx')
        	)
        );

        $ebanx_billing_brazil_birth_date = array(
            'type'  => 'text',
            'label' => __('Birth Date', 'woocommerce-gateway-ebanx'),
            'class' => array('ebanx_billing_brazil_birth_date', 'ebanx_billing_brazil_cpf', 'ebanx_billing_brazil_selector_option'),
            'default' => isset($birth_date_br) ? $birth_date_br : ''
        );
        $ebanx_billing_brazil_document = array(
            'type'     => 'text',
            'label'    => 'CPF',
            'class' => array('ebanx_billing_brazil_document', 'ebanx_billing_brazil_cpf', 'ebanx_billing_brazil_selector_option'),
            'default' => isset($cpf) ? $cpf : ''
        );

        $ebanx_billing_brazil_cnpj = array(
            'type'     => 'text',
            'label'    => 'CNPJ',
            'class' => array('ebanx_billing_brazil_cnpj', 'ebanx_billing_brazil_cnpj', 'ebanx_billing_brazil_selector_option'),
            'default' => isset($cnpj) ? $cnpj : ''
        );

        $ebanx_billing_chile_birth_date = array(
            'type'  => 'text',
            'label' => __('Birth Date', 'woocommerce-gateway-ebanx'),
            'class' => array('ebanx_billing_chile_birth_date'),
            'default' => isset($birth_date_cl) ? $birth_date_cl : ''
        );
        $ebanx_billing_chile_document = array(
            'type'     => 'text',
            'label'    => 'RUT',
            'class' => array('ebanx_billing_chile_document'),
            'default' => isset($rut) ? $rut : ''
        );

        if (!$disable_own_fields) {
	        // CPF and CNPJ are enabled
	        if (in_array('cpf', $fields_options) && in_array('cnpj', $fields_options)) {
	        	$fields['billing']['ebanx_billing_brazil_selector'] = $ebanx_selector;
	        }

	        // CPF is enabled
	        if (in_array('cpf', $fields_options)) {
	    		$fields['billing']['ebanx_billing_brazil_document'] = $ebanx_billing_brazil_document;

	        	$fields['billing']['ebanx_billing_brazil_birth_date'] = $ebanx_billing_brazil_birth_date;
	        }

	        // CNPJ is enabled
	        if (in_array('cnpj', $fields_options)) {
	        	$fields['billing']['ebanx_billing_brazil_cnpj'] = $ebanx_billing_brazil_cnpj;
	        }
	    }

        // For Chile
        $fields['billing']['ebanx_billing_chile_document'] = $ebanx_billing_chile_document;
        $fields['billing']['ebanx_billing_chile_birth_date'] = $ebanx_billing_chile_birth_date;

        return $fields;
    }

    /**
     * Check if the merchant uses a checkout manager and configured the checkout manager options
     *
     * @return boolean
     */
    protected function is_checkout_manager_settings_empty() {
    	return empty($this->get_settings_or_default('checkout_manager_cpf_brazil'))
    			&& empty($this->get_settings_or_default('checkout_manager_birthdate'))
    			&& empty($this->get_settings_or_default('checkout_manager_cnpj_brazil'));
    }

    /**
     * Fetches the billing field names for compatibility with checkout managers
     *
     * @return array
     */
    public function get_billing_field_names() {
        return array(
        	// Brazil CPF
            'ebanx_billing_brazil_document' => $this->get_settings_or_default('checkout_manager_cpf_brazil', 'ebanx_billing_brazil_document'),
            'ebanx_billing_brazil_birth_date' => $this->get_settings_or_default('checkout_manager_birthdate', 'ebanx_billing_brazil_birth_date'),

            // Brazil CNPJ
            'ebanx_billing_brazil_cnpj' => $this->get_settings_or_default('checkout_manager_cnpj_brazil', 'ebanx_billing_brazil_cnpj'),

            // Chile Fields
            'ebanx_billing_chile_document' => 'ebanx_billing_chile_document',
            'ebanx_billing_chile_birth_date' => 'ebanx_billing_chile_birth_date'
        );
    }

    /**
     * Fetches a single setting from the gateway settings if found, otherwise it returns an optional default value
     *
     * @param  string $name    The setting name to fetch
     * @param  mixed  $default The default value in case setting is not present
     * @return mixed
     */
    private function get_settings_or_default($name, $default=null) {
        if(!isset($this->configs->settings[$name]) || empty($this->configs->settings[$name])) {
            return $default;
        }

        return $this->configs->settings[$name];
    }

    /**
     * The icon on the right of the gateway name on checkout page
     *
     * @return string The URI of the icon
     */
    public function show_icon()
    {
        return plugins_url('/assets/images/' . $this->id . '.png', plugin_basename(dirname(__FILE__)));
    }

    /**
     * Insert the necessary assets on checkout page
     *
     * @return void
     */
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

            if (self::$initializedGateways === self::$totalGateways) {
                wp_localize_script('woocommerce_ebanx', 'wc_ebanx_params', apply_filters('wc_ebanx_params', static::$ebanx_params));
            }
        }
    }

    /**
     * Output the admin settings in the correct format.
     *
     * @return void
     */
    public function admin_options()
    {
        include dirname(__FILE__) . '/admin/views/html-admin-page.php';
    }

    /**
     * Process a refund created by the merchant
     * @param  integer $order_id    The id of the order created
     * @param  int $amount          The amount of the refund
     * @param  string $reason       Optional description
     * @return boolean
     */
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

    /**
     * Mount the data to send to EBANX API
     *
     * @param  WC_Order $order
     * @return array
     */
    protected function request_data($order)
    {
        $home_url = esc_url( home_url() );

    	$is_cpf = false;
    	$is_cnpj = false;

        $data = array(
            'mode'      => 'full',
            'operation' => 'request',
            'notification_url' => $home_url,
            'payment'   => array(
            	'redirect_url' => $home_url,
                'user_value_1'          => 'name=plugin',
                'user_value_2'          => 'value=woocommerce',
                'user_value_3'          => 'version=' . WC_EBANX::VERSION,
                'country'               => $order->billing_country,
                'currency_code'         => WC_EBANX_Gateway_Utils::CURRENCY_CODE_USD, // TODO: Dynamic
                'name'                  => $order->billing_first_name . ' ' . $order->billing_last_name,
                'email'                 => $order->billing_email,
                "phone_number"          => $order->billing_phone,
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

        if ($this->getTransactionAddress('country') === WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL) {

        	$is_cpf = !empty($_POST[$this->names['ebanx_billing_brazil_document']]);
        	$is_cnpj = !empty($_POST[$this->names['ebanx_billing_brazil_cnpj']]);

            if (
            	(!$is_cpf && !$is_cnpj) ||
            	empty($_POST['billing_postcode']) ||
                empty($_POST['billing_address_1']) ||
                empty($_POST['billing_city']) ||
                empty($_POST['billing_state'])
            ) {
                throw new Exception('INVALID-FIELDS');
            }

            if ($is_cnpj) {
            	if (empty($_POST['billing_company'])) {
            		throw new Exception('INVALID-FIELDS');
            	}

            	$_POST['ebanx_billing_document'] = $_POST[$this->names['ebanx_billing_brazil_cnpj']];
            }
            else if ($is_cpf) {
            	$_POST['ebanx_billing_document'] = $_POST[$this->names['ebanx_billing_brazil_document']];
            	$_POST['ebanx_billing_birth_date'] = $_POST[$this->names['ebanx_billing_brazil_birth_date']];
            }

        }

        if ($this->getTransactionAddress('country') === WC_EBANX_Gateway_Utils::COUNTRY_CHILE) {
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

        $newData = array();
        $newData['payment'] = array();

        $newData['payment']['person_type'] = 'personal';

        if (!empty($_POST['ebanx_billing_document'])) {
            $newData['payment']['document'] = $_POST['ebanx_billing_document'];
        }

        if (!empty($_POST['ebanx_billing_birth_date'])) {
            $newData['payment']['birth_date'] = $_POST['ebanx_billing_birth_date'];
        }

        if (!empty($_POST['billing_postcode'])) {
            $newData['payment']['zipcode'] = $_POST['billing_postcode'];
        }

        if (!empty($_POST['billing_address_1'])) {
            $newData['payment']['address'] = $_POST['billing_address_1'];
        }

        if (!empty($street_number)) {
            $newData['payment']['street_number'] = $street_number;
        }

        if (!empty($_POST['billing_city'])) {
            $newData['payment']['city'] = $_POST['billing_city'];
        }

        if (!empty($_POST['billing_state'])) {
            $newData['payment']['state'] = $_POST['billing_state'];
        }

        if ($this->getTransactionAddress('country') === WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL) {

	        if ($is_cnpj) {
	            $newData['payment']['person_type'] = 'business';
	            $newData['payment']['responsible'] = array(
	            	"name" => $data['payment']['name']
	            );
	            $newData['payment']['name'] = $_POST['billing_company'];
	        }
	    }

        $data['payment'] = array_merge($data['payment'], $newData['payment']);

        return $data;
    }

    /**
     * Get the customer's address
     *
     * @param  string $attr
     * @return boolean|string
     */
    protected function getTransactionAddress($attr = '')
    {
        if (empty(WC()->customer) || is_admin() || (empty($_POST['billing_country']) && empty(WC()->customer->get_country()))) {
            return false;
        }

        if (!empty($_POST['billing_country'])) {
            $this->address['country'] = trim(strtolower($_POST['billing_country']));
        } else {
            $this->address['country'] = trim(strtolower(WC()->customer->get_country()));
        }

        if ($attr !== '' && !empty($this->address[$attr])) {
            return $this->address[$attr];
        }

        return $this->address;
    }

    /**
     * The main method to process the payment came from WooCommerce checkout
     * This method check the informations sent by WooCommerce and if them are fine, it sends the request to EBANX API
     * The catch captures the errors and check the code sent by EBANX API and then show to the users the right error message
     *
     * @param  integer $order_id    The ID of the order created
     * @return void
     */
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
                    'BP-DR-20'                   => 'Informe a sua data de nascimento no formato dia/mes/ano, por exemplo, 30/10/1980.',
                    'BP-DR-23'                   => 'O número de documento informado é inválido.',
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
                ),
                'es'    => array(
                    'GENERAL'                    => 'No pudimos concluir tu compra. Por favor intenta nuevamente o entra en contacto con el sitio web.',
                    'BP-DR-13'                   => 'Por favor, escribe tu nombre completo.',
                    'BP-DR-15'                   => 'El email no es válido. ',
                    'BP-DR-19'                   => 'Escribe tu fecha de nacimiento en el formato DD/MM/AA.',
                    'BP-DR-20'                   => 'Escribe tu fecha de nacimiento en el formato DD/MM/AA.',
                    'BP-DR-23'                   => 'El numero de documento no es valido.',
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
                ),
            );

            $message = !empty($errors[$language][$code]) ? $errors[$language][$code] : $errors[$language]['GENERAL'] . " ({$code})";

            WC()->session->set('refresh_totals', true);
            WC_Ebanx::log("EBANX Error: $message");

            wc_add_notice($message, 'error');
            return;
        }
    }

    /**
     * The page of order received, we call them as "Thank you pages"
     *
     * @param  WC_Order $order The order created
     * @return void
     */
    public static function thankyou_page($data)
    {
        $file_name = "{$data['method']}/payment-{$data['order_status']}.php";

        if (file_exists(WC_EBANX::get_templates_path() . $file_name)) {
            wc_get_template(
                $file_name,
                $data['data'],
                'woocommerce/ebanx/',
                WC_EBANX::get_templates_path()
            );
        }
    }

    /**
     * Clean the cart and dispatch the data to request
     *
     * @param  array $data  The checkout's data
     * @return array
     */
    protected function dispatch($data)
    {
        WC()->cart->empty_cart();

        return $data;
    }

    /**
     * Save order's meta fields for future use
     *
     * @param  WC_Order $order The order created
     * @param  Object $request The request from EBANX success response
     * @return void
     */
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

        // It shows to the merchant
        update_post_meta($order->id, 'Payment\'s Hash', $request->payment->hash);
    }

    /**
     * Save user's meta fields for future use
     *
     * @param  WC_Order $order The order created
     * @return void
     */
    protected function save_user_meta_fields($order)
    {
        if ($this->userId) {
            if (trim(strtolower($order->billing_country)) === WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL) {
            	if (isset($_POST[$this->names['ebanx_billing_brazil_document']])) {
	                update_user_meta($this->userId, '_ebanx_billing_brazil_document', sanitize_text_field($_POST[$this->names['ebanx_billing_brazil_document']]));
	            }

	            if (isset($_POST[$this->names['ebanx_billing_brazil_birth_date']])) {
	                update_user_meta($this->userId, '_ebanx_billing_brazil_birth_date', sanitize_text_field($_POST[$this->names['ebanx_billing_brazil_birth_date']]));
	            }

                if (isset($_POST[$this->names['ebanx_billing_brazil_cnpj']])) {
                	update_user_meta($this->userId, '_ebanx_billing_brazil_cnpj', sanitize_text_field($_POST[$this->names['ebanx_billing_brazil_cnpj']]));
                }
            }

            if (trim(strtolower($order->billing_country)) === WC_EBANX_Gateway_Utils::COUNTRY_CHILE) {
            	if (isset($_POST['ebanx_billing_chile_document'])) {
                	update_user_meta($this->userId, '_ebanx_billing_chile_document', sanitize_text_field($_POST['ebanx_billing_chile_document']));
                }

                if (isset($_POST['ebanx_billing_chile_birth_date'])) {
                	update_user_meta($this->userId, '_ebanx_billing_chile_birth_date', sanitize_text_field($_POST['ebanx_billing_chile_birth_date']));
                }
            }
        }
    }

    /**
     * It just process the errors response and it generates a log to the merchant on Wordpress panel
     * It always throws an Exception
     *
     * @param  Object $request      The EBANX error object
     * @param  WC_Order $order      The WooCommerce order
     * @return void
     */
    protected function process_response_error($request, $order)
    {
        $code = $request->status_code;

        $error_message = __(sprintf('EBANX: An error occurred: %s - %s', $code, $request->status_message), 'woocommerce-gateway-ebanx');

        $order->update_status('failed', $error_message);
        $order->add_order_note($error_message);

        throw new Exception($code);
    }

    /**
     * Process the response of request from EBANX API
     *
     * @param  Object $request The result of request
     * @param  WC_Order $order   The order created
     * @return void
     */
    protected function process_response($request, $order)
    {
        WC_Ebanx::log("Processing response: " . print_r($request, true));

        if ($request->status == 'ERROR') {
            return $this->process_response_error($request, $order);
        }

        $message = __(sprintf('Payment approved. Hash: %s', $request->payment->hash), 'woocommerce-gateway-ebanx');

        WC_Ebanx::log($message);

        if ($request->payment->status == 'CA') {
            $order->add_order_note(__('EBANX: Payment failed.', 'woocommerce-gateway-ebanx'));
            $order->update_status('failed');
        }

        if ($request->payment->status == 'OP') {
            $order->add_order_note(__('EBANX: Payment opened.', 'woocommerce-gateway-ebanx'));
            $order->update_status('pending');
        }

        if ($request->payment->status == 'PE') {
            $order->add_order_note(__('EBANX: Waiting payment.', 'woocommerce-gateway-ebanx'));
            $order->update_status('on-hold');
        }

        if ($request->payment->pre_approved && $request->payment->status == 'CO') {
            $order->add_order_note(__('EBANX: Transaction paid.', 'woocommerce-gateway-ebanx'));
            $order->payment_complete($request->payment->hash);
            $order->update_status('processing');
        }

        // Save post's meta fields
        $this->save_order_meta_fields($order, $request);

        // Save user's fields
        $this->save_user_meta_fields($order);
    }

    /**
     * Create the hooks to process cash payments
     *
     * @param  array  $codes
     * @param  string $notificationType     The type of the description
     * @return void
     */
    final public function process_hook(array $codes, $notificationType)
    {
        $config = [
            'integrationKey' => $this->private_key,
            'testMode'       => $this->is_sandbox_mode,
        ];

        \Ebanx\Config::set($config);

        /**
         * Validates the request parameters
         */
        if (isset($codes['hash']) && !empty($codes['hash']) && isset($codes['merchant_payment_code']) && !empty($codes['merchant_payment_code'])) {
            unset($codes['merchant_payment_code']);
        }

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
                        $order->update_status('processing');
                        break;
                    case 'CA':
                        $order->update_status('failed');
                        break;
                    case 'PE':
                        $order->update_status('on-hold');
                        break;
                    case 'OP':
                        $order->update_status('pending');
                        break;
                }
                break;
        };

        return $order;
    }
}
