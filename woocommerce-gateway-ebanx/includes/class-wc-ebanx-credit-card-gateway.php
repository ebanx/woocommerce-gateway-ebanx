<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Credit_Card_Gateway extends WC_EBANX_Gateway
{
    public function __construct()
    {
        $this->id           = 'ebanx-credit-card';
        $this->method_title = __('EBANX - Credit Card', 'woocommerce-gateway-ebanx');

        $this->api_name    = '_creditcard';
        $this->title       = __('Credit Card', 'woocommerce-gateway-ebanx');
        $this->description = __('Pay with credit card.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        add_action('woocommerce_order_actions', function ($actions){
            if (is_array($actions)){
                $actions['custom_action'] = __('Capture by EBANX');
            }

            return $actions;
        });

        add_action('woocommerce_order_action_custom_action', array($this, 'capturePaymentAction'));
    }

    function capturePaymentAction($order){
        if ($order->get_status() != 'pending' || $order->payment_method != $this->id) {
            return;
        }

        \Ebanx\Config::set([
            'integrationKey' => $this->private_key,
            'testMode'       => $this->is_test_mode,
        ]);

        $request = \Ebanx\Ebanx::doCapture(['hash' => get_post_meta($order->id, '_ebanx_payment_hash')]);

        if ($request->status != 'SUCCESS') {
            return;
        }

        if ($request->payment->status == 'CO') {
            $order->payment_complete();
            $order->update_status('completed');
            $order->add_order_note(__('EBANX: Transaction captured by '.wp_get_current_user()->data->user_email, 'woocommerce-gateway-ebanx'));
        }
    }

    public function checkout_assets()
    {
        parent::checkout_assets();

        if (is_checkout()) {
            wp_enqueue_script('wc-credit-card-form');
            // Using // to avoid conflicts between http and https protocols
            wp_enqueue_script('ebanx_fingerprint', '//s3-sa-east-1.amazonaws.com/downloads.ebanx.com/poc-checkout/src/device-fingerprint.js', '', '1.0', true); // TODO: REMOVE THIS
            wp_enqueue_script('ebanx', '//s3-sa-east-1.amazonaws.com/downloads.ebanx.com/poc-checkout/src/ebanx.js', '', '1.0', true);
            wp_enqueue_script('woocommerce_ebanx_jquery_mask', plugins_url('assets/js/jquery-mask.js', WC_EBANX::DIR), array());
            wp_enqueue_script('woocommerce_ebanx', plugins_url('assets/js/credit-card.js', WC_EBANX::DIR), array('jquery-payment', 'ebanx'), WC_EBANX::VERSION, true);

            $ebanx_params = array(
                'key'  => $this->public_key,
                'mode' => $this->is_test_mode ? 'test' : 'production',
            );

            // If we're on the pay page we need to pass ebanx.js the address of the order.
            if (is_checkout_pay_page() && isset($_GET['order']) && isset($_GET['order_id'])) { // TODO: WE CAN REMOVE THIS?
                $order_key = urldecode($_GET['order']);
                $order_id  = absint($_GET['order_id']);
                $order     = wc_get_order($order_id);

                if ($order->id === $order_id && $order->order_key === $order_key) {
                    $ebanx_params['billing_first_name'] = $order->billing_first_name;
                    $ebanx_params['billing_last_name']  = $order->billing_last_name;
                    $ebanx_params['billing_address_1']  = $order->billing_address_1;
                    $ebanx_params['billing_address_2']  = $order->billing_address_2;
                    $ebanx_params['billing_state']      = $order->billing_state;
                    $ebanx_params['billing_city']       = $order->billing_city;
                    $ebanx_params['billing_postcode']   = $order->billing_postcode;
                    $ebanx_params['billing_country']    = $order->billing_country;
                }
            }

            wp_localize_script('woocommerce_ebanx', 'wc_ebanx_params', apply_filters('wc_ebanx_params', $ebanx_params));
        }
    }

    public function show_icon()
    {
        return plugins_url('/assets/images/' . $this->id . '-' . $this->getTransactionAddress('country') . '.png', plugin_basename(dirname(__FILE__)));
    }

    public function is_available()
    {
        switch ($this->getTransactionAddress('country')) {
            case WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL:
                $this->title = 'Cartão de Crédito';
                break;
            case WC_EBANX_Gateway_Utils::COUNTRY_MEXICO:
                $this->title = 'Tarjeta de Crédito';
                break;
            default:
                $this->title = 'Credit Card';
                break;
        }

        $this->method = $this->getTransactionAddress('country') === WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL ? 'brazil_payment_methods' : ($this->getTransactionAddress('country') === WC_EBANX_Gateway_Utils::COUNTRY_MEXICO ? 'mexico_payment_methods' : false);
        $this->enabled = $this->method && is_array($this->configs->settings[$this->method]) ? in_array($this->id, $this->configs->settings[$this->method]) ? 'yes' : false : false;

        return parent::is_available();
    }

    public function payment_fields()
    {
        $languages = array(
            'mx' => 'es',
            'cl' => 'es',
            'pe' => 'es',
            'co' => 'es',
            'br' => 'pt-br',
        );
        $language = $languages[$this->language];

        $messages = array(
            'pt-br' => array(
                'title' => '',
                'number' => 'Número do Cartão',
                'expiry' => 'Data de validade (MM/YY)',
                'cvv' => 'Código de segurança',
                'instalments' => 'Número de parcelas',
                'save_card' => 'Salvar este cartão para compras futuras',
                'name' => 'Nome impresso no cartão',
                'another' => 'Usar um outro cartão'
            ),
            'es' => array(
                'title' => '',
                'number' => 'Número de la tarjeta',
                'expiry' => 'Fecha de expiración (MM/AA)',
                'cvv' => 'Código de verificación',
                'instalments' => 'Meses sin interesses',
                'save_card' => 'Guarda esta tarjeta para compras futuras.',
                'name' => 'Titular de la tarjeta',
                'another' => 'Otra tarjeta de crédito'
            )
        );

        $cart_total = $this->get_order_total();

        $cards = array_filter((array) get_user_meta($this->userId, '_ebanx_credit_card_token', true), function ($card) {
            return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number); // TODO: Implement token due date
        });

        // echo wp_kses_post(wpautop(wptexturize($messages[$language]['title'])));

        wc_get_template(
            'credit-card/payment-form.php',
            array(
                'language'        => $this->language,
                'cards'           => (array) $cards,
                'cart_total'      => $cart_total,
                'country'         => $this->getTransactionAddress('country'),
                'max_installment' => $this->configs->settings['credit_card_instalments'],
                'place_order_enabled' => (isset($this->configs->settings['save_card_data']) && $this->configs->settings['save_card_data'] === 'yes'),
                't' => $messages[$language]
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    public static function thankyou_page($order)
    {
        $data = array(
            'instalments' => get_post_meta($order->id, '_instalments_number', true),
            'card_brand'  => get_post_meta($order->id, '_cards_brand_name', true),
            // TODO: display masked number
        );

        if (isset($data['instalments'])) {
            wc_get_template(
                'credit-card/payment-instructions.php',
                $data,
                'woocommerce/ebanx/',
                WC_EBANX::get_templates_path()
            );
        }
    }

    protected function request_data($order)
    {
        if (empty($_POST['ebanx_token']) ||
            empty($_POST['ebanx_masked_card_number']) ||
            empty($_POST['ebanx_brand']) ||
            empty($_POST['ebanx_billing_cvv'])
        ) {
            throw new Exception('MISSING-CARD-PARAMS');
        }

        if (empty($_POST['ebanx_is_one_click']) && empty($_POST['ebanx_device_fingerprint'])) {
            throw new Exception('MISSING-DEVICE-FINGERPRINT');
        }

        $data = parent::request_data($order);

        if (in_array(trim(strtolower(WC()->customer->get_shipping_country())), WC_EBANX_Gateway_Utils::$CREDIT_CARD_COUNTRIES)) {
            if (empty($_POST['ebanx_billing_instalments'])) {
                throw new Exception('MISSING-INSTALMENTS');
            }

            $data['payment']['instalments'] = $_POST['ebanx_billing_instalments'];
        }

        if (!empty($_POST['ebanx_device_fingerprint'])) {
            $data['device_id'] = $_POST['ebanx_device_fingerprint'];
        }

        $data['payment']['payment_type_code'] = $_POST['ebanx_brand'];
        $data['payment']['creditcard']        = array(
            'token'    => $_POST['ebanx_token'],
            'card_cvv' => $_POST['ebanx_billing_cvv'],
            'auto_capture' => ($this->configs->settings['capture_enabled'] === 'yes'),
        );

        return $data;
    }

    protected function process_response($request, $order)
    {
        if ($request->status == 'ERROR' || !$request->payment->pre_approved) {
            return $this->process_response_error($request, $order);
        }

        parent::process_response($request, $order);
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        update_post_meta($order->id, '_cards_brand_name', $request->payment->payment_type_code);
        update_post_meta($order->id, '_instalments_number', $request->payment->instalments);
    }

    protected function save_user_meta_fields($order)
    {
        parent::save_user_meta_fields($order);

        if ($this->userId && $this->configs->settings['save_card_data'] === 'yes' && isset($_POST['ebanx-save-credit-card']) && $_POST['ebanx-save-credit-card'] === 'yes') {
            $cards = get_user_meta($this->userId, '_ebanx_credit_card_token', true);
            $cards = !empty($cards) ? $cards : [];

            $card = new \stdClass();

            $card->brand         = $_POST['ebanx_brand'];
            $card->token         = $_POST['ebanx_token'];
            $card->masked_number = $_POST['ebanx_masked_card_number'];

            foreach ($cards as $cd) {
                if ($cd->masked_number == $card->masked_number && $cd->brand == $card->brand) {
                    $cd->token = $card->token;
                    unset($card);
                }
            }

            // TODO: Implement token due date

            $cards[] = $card;

            update_user_meta($this->userId, '_ebanx_credit_card_token', $cards);
        }
    }
}
