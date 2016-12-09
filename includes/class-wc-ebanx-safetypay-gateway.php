<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Safetypay_Gateway extends WC_Ebanx_Redirect_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-safetypay';
        $this->method_title = __('EBANX - Safetypay', 'woocommerce-ebanx');

        $this->title       = __('SafetyPay');
        $this->description = __('SafetyPay Description');

        parent::__construct();
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_Ebanx_Gateway_Utils::COUNTRY_PERU);
    }

    /**
     * TODO: ??
     * Admin page.
     */
    /*public function admin_options() {
    include dirname( __FILE__ ) . '/admin/views/notices/html-notice-country-not-supported.php';
    }*/

    public static function thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);
        $data  = get_post_meta($order_id, '_wc_ebanx_transaction_data', true);

        // TODO: how do this?

        if (isset($data['installments']) && in_array($order->get_status(), array('processing', 'on-hold'), true)) {
            wc_get_template(
                'safetypay/payment-instructions.php',
                array(
                    'title'       => $this->title,
                    'description' => $this->description,
                ),
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        }
    }

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        $cart_total = $this->get_order_total();

        wc_get_template(
            'safetypay/payment-form.php',
            array(
                'title'       => $this->title,
                'description' => $this->description,
            ),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

    protected function request_data($order)
    {
        if (!isset($_POST['safetypay']) || !in_array($_POST['safetypay'], WC_Ebanx_Gateway_Utils::$TYPES_SAFETYPAY_ALLOWED)) {
            throw new Exception("Invalid safetypay type.");
        }

        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = 'safetypay-' . $_POST['safetypay'];

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        // TODO: What are the fields necessaries by this payment method?
    }
}
