<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Ebanx_Banking_Ticket_Gateway extends WC_Ebanx_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-banking-ticket';
        $this->method_title = __('EBANX - Banking Ticket', 'woocommerce-ebanx');

        $this->title       = __('Banking Ticket');
        $this->description = __('Pay with EBANX Baking Ticket');

        parent::__construct();
    }

    public function is_available()
    {
        return parent::is_available() && $this->getTransactionAddress('country') == WC_Ebanx_Gateway_Utils::COUNTRY_BRAZIL;
    }

    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop(wptexturize($description)));
        }

        wc_get_template(
            'banking-ticket/checkout-instructions.php',
            array(),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

    protected function request_data($order)
    {
        $data                                 = parent::request_data($order);
        $data['payment']['payment_type_code'] = 'boleto';
        // TODO: needs due_date ??
        return $data;
    }

    protected function save_order_meta_fields($order, $request)
    {
        parent::save_order_meta_fields($order, $request);

        update_post_meta($order->id, 'Payment\'s Due Date', $request->payment->due_date);
        update_post_meta($order->id, 'Banking Ticket URL', $request->payment->boleto_url);
        update_post_meta($order->id, 'Banking Ticket Barcode', $request->payment->boleto_barcode);
    }

    public static function thankyou_page($order_id)
    {
        $order = new WC_Order($order_id);

        if (in_array($order->get_status(), array('pending', 'on-hold'))) {
            $data = array(
                'url'      => get_post_meta($order->id, 'Banking Ticket URL', true),
                'barcode'  => get_post_meta($order->id, 'Banking Ticket Barcode', true),
                'due_date' => get_post_meta($order->id, 'Payment\'s Due Date', true),
            );

            wc_get_template(
                'banking-ticket/payment-instructions.php',
                $data,
                'woocommerce/ebanx/',
                WC_Ebanx::get_templates_path()
            );
        }
    }
}
