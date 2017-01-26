<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Pagoefectivo_Gateway extends WC_EBANX_Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id           = 'ebanx-pagoefectivo';
        $this->method_title = __('EBANX - Pagoefectivo', 'woocommerce-gateway-ebanx');

        $this->api_name    = 'pagoefectivo';
        $this->title       = __('PagoEfectivo', 'woocommerce-gateway-ebanx');
        $this->description = __('Paga con PagoEfectivo.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = is_array($this->configs->settings['peru_payment_methods']) ? in_array($this->id, $this->configs->settings['peru_payment_methods']) ? 'yes' : false : false;
    }

    /**
     * This method always will return false, it doesn't need to show to the customers
     *
     * @return boolean Always return false
     */
    public function is_available()
    {
        return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_PERU;
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
            'pagoefectivo/payment-form.php',
            array(
                'language'    => $this->language,
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
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

        update_post_meta($order->id, '_pagoefectivo_url', $request->redirect_url);
    }

    /**
     * The page of order received, we call them as "Thank you pages"
     *
     * @param  WC_Order $order The order created
     * @return void
     */
    public static function thankyou_page($order)
    {
        $pagoefectivo_url = get_post_meta($order->id, '_pagoefectivo_url', true);

        $data = array(
            'data' => array(
                'url_basic' => $pagoefectivo_url,
                'url_iframe' => get_site_url() . '/?ebanx=order-received&url=' . $pagoefectivo_url,
                'customer_email' => $order->billing_email
            ),
            'order_status' => $order->get_status(),
            'method' => 'pagoefectivo'
        );

        parent::thankyou_page($data);

        wp_enqueue_script('woocommerce_ebanx_clipboard', plugins_url('assets/js/vendor/clipboard.min.js', WC_EBANX::DIR));
        wp_enqueue_script('woocommerce_ebanx_order_received', plugins_url('assets/js/order-received.js', WC_EBANX::DIR));
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
        if ($request->status == 'ERROR' || !$request->payment->cip_url) {
            return $this->process_response_error($request, $order);
        }
        $request->redirect_url = $request->payment->cip_url;

        return parent::process_response($request, $order);
    }

    /**
     * Mount the data to send to EBANX API
     *
     * @param  WC_Order $order
     * @return array
     */
    protected function request_data($order)
    {
        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $this->api_name;

        return $data;
    }
}
