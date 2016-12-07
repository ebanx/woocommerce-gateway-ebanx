<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Tef_Gateway extends WC_Ebanx_Redirect_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-tef';
        $this->method_title = __('EBANX - TEF', 'woocommerce-ebanx');

        $this->title       = __('TEF');
        $this->description = __('TEF Description');

        parent::__construct();
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL);
    }

    /**
     * TODO: ??
     * Admin page.
     */
    /*public function admin_options() {
    include dirname( __FILE__ ) . '/admin/views/notices/html-notice-country-not-supported.php';
    }*/

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        $cart_total = $this->get_order_total();

        wc_get_template(
            'tef/payment-form.php',
            array(
                'title'       => $this->title,
                'description' => $this->description,
            ),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

    public static function thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);
        $data  = get_post_meta($order_id, '_wc_ebanx_transaction_data', true);

        if (isset($data['installments']) && in_array($order->get_status(), array('processing', 'on-hold'), true)) {
            wc_get_template(
                'tef/payment-instructions.php',
                array(
                    'title'       => $this->title,
                    'description' => $this->description,
                ),
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        }
    }

    protected function request_data($order)
    {
        if (!isset($_POST['tef']) || !in_array($_POST['tef'], WC_Ebanx_Gateway_Utils::$BANKS_TEF_ALLOWED[WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL])) {
            throw new Exception("Missing a bank name or bank is not valid.");
        }

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = $_POST['tef'];

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        // TODO: What are the fields necessaries by this payment method?
    }
}
