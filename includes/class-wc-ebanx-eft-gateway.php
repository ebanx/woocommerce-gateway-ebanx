<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Eft_Gateway extends WC_Ebanx_Redirect_Gateway
{

    public function __construct()
    {
        $this->id                   = 'ebanx-eft';
        $this->method_title         = __('EBANX - EFT', 'woocommerce-ebanx');

        $this->title = __('EFT', 'woocommerce-ebanx');
        $this->description = __('EFT Description', 'woocommerce-ebanx');

        parent::__construct();
    }

    public function is_available()
    {
        return parent::is_available() && ($this->getTransactionAddress('country') == WC_Ebanx_Gateway_Utils::COUNTRY_COLOMBIA);
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

        wc_get_template(
            'eft/payment-form.php',
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

        // TODO: How do this

        if (isset($data['installments']) && in_array($order->get_status(), array('processing', 'on-hold'), true)) {
            wc_get_template(
                'eft/payment-instructions.php',
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
        if (!isset($_POST['eft']) || !in_array($_POST['eft'], WC_Ebanx_Gateway_Utils::$BANKS_EFT_ALLOWED[WC_Ebanx_Gateway_Utils::COUNTRY_COLOMBIA])) {
            throw new Exception("Missing a bank name or bank is not valid.");
        }

        $data = parent::request_data($order);

        $data['payment']['eft_code']          = $_POST['eft'];
        $data['payment']['payment_type_code'] = 'eft';

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        // TODO: What are the fields necessaries by this payment method?
    }
}
