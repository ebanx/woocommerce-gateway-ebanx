<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Debit_Card_Gateway extends WC_EBANX_Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id           = 'ebanx-debit-card';
        $this->method_title = __('EBANX - Debit Card', 'woocommerce-gateway-ebanx');

        $this->api_name    = 'debitcard';
        $this->title       = __('Tarjeta de Débito', 'woocommerce-gateway-ebanx');
        $this->description = __('Paga con tarjeta de débito.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = is_array($this->configs->settings['mexico_payment_methods']) ? in_array($this->id, $this->configs->settings['mexico_payment_methods']) ? 'yes' : false : false;
    }

    /**
     * Check if the method is available to show to the users
     *
     * @return boolean
     */
    public function is_available()
    {
        return parent::is_available() && $this->getTransactionAddress('country') === WC_Ebanx_Gateway_Utils::COUNTRY_MEXICO;
    }

    /**
     * Insert the necessary assets on checkout page
     *
     * @return void
     */
    public function checkout_assets()
    {
        if (is_checkout()) {
            wp_enqueue_script('wc-debit-card-form');
            wp_enqueue_script('woocommerce_ebanx_debit', plugins_url('assets/js/debit-card.js', WC_EBANX::DIR), array('jquery-payment'), WC_EBANX::VERSION, true);
        }

        parent::checkout_assets();
    }

    /**
     * The HTML structure on checkout page
     */
    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        wc_get_template(
            'debit-card/payment-form.php',
            array(
                'language' => $this->language,
                'cart_total' => $this->get_order_total()
            ),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

    /**
     * Mount the data to send to EBANX API
     *
     * @param  WC_Order $order
     * @return array
     */
    protected function request_data($order)
    {
        if (empty($_POST['ebanx_debit_token']) || empty($_POST['ebanx_billing_cvv'])) {
            throw new Exception("Missing ebanx card params.");
        }

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $this->api_name;

        // TODO: need fingerprint ?

        $data['payment']['card'] = array(
            'token'    => $_POST['ebanx_debit_token'],
            'card_cvv' => $_POST['ebanx_billing_cvv'],
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
        update_post_meta($order->id, '_masked_card_number', $_POST['ebanx_masked_card_number']);
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

        $data = array(
            'data' => array(
                'card_brand_name' => get_post_meta($order->id, '_cards_brand_name', true),
                'order_amount' => $order_amount,
                'masked_card' => substr(get_post_meta($order->id, '_masked_card_number', true), -4),
                'customer_email' => $order->billing_email,
                'customer_name' => $order->billing_first_name
            ),
            'order_status' => $order->get_status(),
            'method' => 'debit-card'
        );

        parent::thankyou_page($data);
    }
}
