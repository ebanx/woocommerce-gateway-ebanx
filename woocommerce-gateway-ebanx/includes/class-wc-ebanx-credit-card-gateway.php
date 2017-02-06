<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Credit_Card_Gateway extends WC_EBANX_Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->api_name = '_creditcard';

        parent::__construct();

        add_action('woocommerce_order_actions', array($this, 'auto_capture'));

        add_action('woocommerce_order_action_custom_action', array($this, 'capture_payment_action'));
    }

    /**
     * Check the Auto Capture
     *
     * @param  array $actions
     * @return array
     */
    public function auto_capture($actions) {
        if (is_array($actions)) {
            $actions['custom_action'] = __('Capture by EBANX');
        }

        return $actions;
    }

    /**
     * Action to capture the payment
     *
     * @param  WC_Order $order WooCommerce Order
     * @return void
     */
    public function capture_payment_action($order)
    {
        if ($order->get_status() != 'pending' || $order->payment_method != $this->id) {
            return;
        }

        \Ebanx\Config::set([
            'integrationKey' => $this->private_key,
            'testMode' => $this->is_sandbox_mode,
        ]);

        $request = \Ebanx\Ebanx::doCapture(['hash' => get_post_meta($order->id, '_ebanx_payment_hash')]);

        if ($request->status != 'SUCCESS') {
            return;
        }

        if ($request->payment->status == 'CO') {
            $order->payment_complete();
            $order->update_status('processing');
            $order->add_order_note(__('EBANX: Transaction captured by ' . wp_get_current_user()->data->user_email, 'woocommerce-gateway-ebanx'));
        }
    }

    /**
     * Insert the necessary assets on checkout page
     *
     * @return void
     */
    public function checkout_assets()
    {
        if (is_checkout()) {
            wp_enqueue_script('wc-credit-card-form');
            // Using // to avoid conflicts between http and https protocols
            wp_enqueue_script('ebanx_fingerprint', '//js.ebanx.com/device-fingerprint-1.4.min.js', '', null, true);
            wp_enqueue_script('ebanx', '//js.ebanx.com/ebanx-1.4.min.js', '', null, true);
            wp_enqueue_script('woocommerce_ebanx_jquery_mask', plugins_url('assets/js/jquery-mask.js', WC_EBANX::DIR), array());
            wp_enqueue_script('woocommerce_ebanx', plugins_url('assets/js/credit-card.js', WC_EBANX::DIR), array('jquery-payment', 'ebanx'), WC_EBANX::VERSION, true);

            // If we're on the checkout page we need to pass ebanx.js the address of the order.
            if (is_checkout_pay_page() && isset($_GET['order']) && isset($_GET['order_id'])) {
                $order_key = urldecode($_GET['order']);
                $order_id = absint($_GET['order_id']);
                $order = wc_get_order($order_id);

                if ($order->id === $order_id && $order->order_key === $order_key) {
                    static::$ebanx_params['billing_first_name'] = $order->billing_first_name;
                    static::$ebanx_params['billing_last_name'] = $order->billing_last_name;
                    static::$ebanx_params['billing_address_1'] = $order->billing_address_1;
                    static::$ebanx_params['billing_address_2'] = $order->billing_address_2;
                    static::$ebanx_params['billing_state'] = $order->billing_state;
                    static::$ebanx_params['billing_city'] = $order->billing_city;
                    static::$ebanx_params['billing_postcode'] = $order->billing_postcode;
                    static::$ebanx_params['billing_country'] = $order->billing_country;
                }
            }
        }

        parent::checkout_assets();
    }

    /**
     * Mount the data to send to EBANX API
     *
     * @param  WC_Order $order
     * @return array
     */
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

        if (in_array($this->getTransactionAddress('country'), WC_EBANX_Gateway_Utils::$CREDIT_CARD_COUNTRIES)) {
            $data['payment']['instalments'] = '1';

            if ($this->configs->settings['credit_card_instalments'] > 1) {
                if (empty($_POST['ebanx_billing_instalments'])) {
                    throw new Exception('MISSING-INSTALMENTS');
                }

                $data['payment']['instalments'] = $_POST['ebanx_billing_instalments'];
            }
        }

        if (!empty($_POST['ebanx_device_fingerprint'])) {
            $data['device_id'] = $_POST['ebanx_device_fingerprint'];
        }

        $data['payment']['payment_type_code'] = $_POST['ebanx_brand'];
        $data['payment']['creditcard'] = array(
            'token' => $_POST['ebanx_token'],
            'card_cvv' => $_POST['ebanx_billing_cvv'],
            'auto_capture' => ($this->configs->settings['capture_enabled'] === 'yes'),
        );

        return $data;
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
        if ($request->status == 'ERROR' || !$request->payment->pre_approved) {
            return $this->process_response_error($request, $order);
        }

        parent::process_response($request, $order);
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
        parent::save_order_meta_fields($order, $request);

        update_post_meta($order->id, '_cards_brand_name', $request->payment->payment_type_code);
        update_post_meta($order->id, '_instalments_number', $request->payment->instalments);
        update_post_meta($order->id, '_masked_card_number', $_POST['ebanx_masked_card_number']);
    }

    /**
     * Save user's meta fields for future use
     *
     * @param  WC_Order $order The order created
     * @return void
     */
    protected function save_user_meta_fields($order)
    {
        parent::save_user_meta_fields($order);

        if ($this->userId && $this->configs->settings['save_card_data'] === 'yes' && isset($_POST['ebanx-save-credit-card']) && $_POST['ebanx-save-credit-card'] === 'yes') {
            $cards = get_user_meta($this->userId, '_ebanx_credit_card_token', true);
            $cards = !empty($cards) ? $cards : [];

            $card = new \stdClass();

            $card->brand = $_POST['ebanx_brand'];
            $card->token = $_POST['ebanx_token'];
            $card->masked_number = $_POST['ebanx_masked_card_number'];

            foreach ($cards as $cd) {
                if (empty($cd)) {
                	continue;
                }

                if ($cd->masked_number == $card->masked_number && $cd->brand == $card->brand) {
                    $cd->token = $card->token;
                    unset($card);
                }
            }

            // TODO: Implement token due date
            if (isset($card)) {
            	$cards[] = $card;
            }

            update_user_meta($this->userId, '_ebanx_credit_card_token', $cards);
        }
    }

    /**
     * The page of order received, we call them as "Thank you pages"
     *
     * @param  WC_Order $order The order created
     * @return void
     */
    public static function thankyou_page($order)
    {
        $order_amount = $order->get_total();
        $instalments_number = get_post_meta($order->id, '_instalments_number')[0] || 1;

        $data = array(
            'data' => array(
                'card_brand_name' => get_post_meta($order->id, '_cards_brand_name', true),
                'order_amount' => $order_amount,
                'instalments_number' => $instalments_number,
                'instalments_amount' => round($order_amount / $instalments_number, 2),
                'masked_card' => substr(get_post_meta($order->id, '_masked_card_number', true), -4),
                'customer_email' => $order->billing_email,
                'customer_name' => $order->billing_first_name
            ),
            'order_status' => $order->get_status(),
            'method' => $order->payment_method
        );

        parent::thankyou_page($data);
    }
}
