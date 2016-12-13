<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Pagoefectivo_Gateway extends WC_Ebanx_Redirect_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-pagoefectivo';
        $this->method_title = __('EBANX - Pagoefectivo', 'woocommerce-ebanx');

        $this->title       = __('Pagoefectivo');
        $this->description = __('Pagoefectivo description');

        parent::__construct();

        $this->enabled = in_array($this->id, $this->configs->settings['peru_payment_methods']) ? 'yes' : false;
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

        if (isset($data['installments']) && in_array($order->get_status(), array('processing', 'on-hold'), true)) {
            wc_get_template(
                'pagoefectivo/payment-instructions.php',
                array(
                    'title'       => $this->title,
                    'description' => $this->description,
                ),
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        }
    }

    protected function process_response($request, $order)
    {
        if ($request->status == 'ERROR' || !$request->payment->cip_url) {
            return $this->process_response_error($request, $order);
        }
        $request->redirect_url = $request->payment->cip_url;

        return parent::process_response($request, $order);
    }

    protected function request_data($order)
    {
        $data = parent::request_data($order);

        $data['payment']['payment_type_code'] = 'pagoefectivo';

        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        // TODO: What are the fields necessaries by this payment method?
    }
}
