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

        $this->title       = __('Credit Card');
        $this->description = __('Credit Card description');

        parent::__construct();
    }

    public function checkout_scripts()
    {
        parent::checkout_scripts();

        if (is_checkout()) {
            wp_enqueue_script('wc-credit-card-form');
            // Using // to avoid conflicts between http and https protocols
            wp_enqueue_script('ebanx_fingerprint', '//downloads.ebanx.com/poc-checkout/src/device-fingerprint.js', '', '1.0', true); // TODO: REMOVE THIS
            wp_enqueue_script('ebanx', '//downloads.ebanx.com/poc-checkout/src/ebanx.js', '', '1.0', true);
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
        $this->method = ($this->getTransactionAddress('country') === WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL) ? 'brazil_payment_methods' : 'mexico_payment_methods';
        $this->enabled = in_array($this->id, $this->configs->settings[$this->method]) ? 'yes' : false;

        return parent::is_available();
    }

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        $cart_total = $this->get_order_total();

        $cards = array_filter((array) get_user_meta($this->userId, '__ebanx_credit_card_token', true), function ($card) {
            return !empty($card->brand) && !empty($card->token) && !empty($card->masked_number); // TODO: Implement token due date
        });

        wc_get_template(
            'credit-card/payment-form.php',
            array(
                'cards'           => (array) $cards,
                'cart_total'      => $cart_total,
                'max_installment' => $this->configs->settings['credit_card_instalments'],
                'place_order_enabled' => (isset($this->configs->settings['enable_place_order']) && $this->configs->settings['enable_place_order'] === 'yes'),
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    public static function thankyou_page($order_id)
    {
        $order = new WC_Order($order_id);

        $data = array(
            'instalments' => get_post_meta($order->id, 'Number of Instalments', true),
            'card_brand'  => get_post_meta($order->id, 'Card\'s Brand Name', true),
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

        update_post_meta($order->id, 'Card\'s Brand Name', $request->payment->payment_type_code);
        update_post_meta($order->id, 'Number of Instalments', $request->payment->instalments);
    }

    protected function save_user_meta_fields($order)
    {
        parent::save_user_meta_fields($order);

        if ($this->userId && $this->configs->settings['enable_place_order'] === 'yes' && isset($_POST['ebanx-save-credit-card']) && $_POST['ebanx-save-credit-card'] === 'yes') {
            $cards = get_user_meta($this->userId, '__ebanx_credit_card_token', true);
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

            update_user_meta($this->userId, '__ebanx_credit_card_token', $cards);
        }
    }
}
