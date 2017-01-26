<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_EBANX_Tef_Gateway extends WC_EBANX_Redirect_Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id           = 'ebanx-tef';
        $this->method_title = __('EBANX - TEF', 'woocommerce-gateway-ebanx');

        $this->title       = __('Débito Online', 'woocommerce-gateway-ebanx');
        $this->description = __('Selecione o seu banco. A seguir, você será redirecionado para concluir o pagamento pelo seu internet banking.', 'woocommerce-gateway-ebanx');

        parent::__construct();

        $this->enabled = is_array($this->configs->settings['brazil_payment_methods']) ? in_array($this->id, $this->configs->settings['brazil_payment_methods']) ? 'yes' : false : false;
    }

    /**
     * Check if the method is available to show to the users
     *
     * @return boolean
     */
    public function is_available()
    {
        return parent::is_available() && $this->getTransactionAddress('country') == WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL;
    }

    /**
     * The HTML structure on checkout page
     */
    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        $cart_total = $this->get_order_total();

        wc_get_template(
            'tef/payment-form.php',
            array(
                'language' => $this->language,
                'title'       => $this->title,
                'description' => $this->description,
            ),
            'woocommerce/ebanx/',
            WC_EBANX::get_templates_path()
        );
    }

    /**
     * The page of order received, we call them as "Thank you pages"
     *
     * @param  WC_Order $order The order created
     * @return void
     */
    public static function thankyou_page($order)
    {
        $data = array(
            'data' => array(
                'bank_name' => get_post_meta($order->id, '_ebanx_tef_bank', true),
                'customer_name' => get_post_meta($order->id, '_billing_first_name', true)
            ),
            'order_status' => $order->get_status(),
            'method' => 'tef'
        );

        parent::thankyou_page($data);
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
        update_post_meta($order->id, '_ebanx_tef_bank', sanitize_text_field($_POST['tef']));

        parent::save_order_meta_fields($order, $request);
    }

    /**
     * Mount the data to send to EBANX API
     *
     * @param  WC_Order $order
     * @return array
     */
    protected function request_data($order)
    {
        if (!isset($_POST['tef']) || !in_array($_POST['tef'], WC_EBANX_Gateway_Utils::$BANKS_TEF_ALLOWED[WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL])) {
            throw new Exception('MISSING-BANK-NAME');
        }

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $_POST['tef'];

        return $data;
    }
}
