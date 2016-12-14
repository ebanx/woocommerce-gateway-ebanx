<?php

class WC_Ebanx_Refunds
{

    public static function init()
    {
        add_action('woocommerce_refund_deleted', __CLASS__ . '::refundDeletedAction', 10, 2);
    }

    public static function refundDeletedAction($refund_id, $order_id) {
        $refunds = current(get_post_meta($order_id, "_ebanx_payment_refunds"));

        $refundKey = null;

        foreach ($refunds as $k => $r) {
            if ($r->wc_refund->id == $refund_id) {
                $refundKey = $k;
            }
        }

        $order = wc_get_order($order_id);
        $hash  = get_post_meta($order->id, '_ebanx_payment_hash', true);

        $paymentMethod = 'WC_'.preg_replace('/\s+/', '_', ucwords(preg_replace('/\-/', ' ', $order->payment_method))).'_Gateway';

        if (!$refundKey || !$order || !$hash || !class_exists($paymentMethod)){
            return;
        }

        $data = array(
            'refund_id' => $refunds[$refundKey]->id,
            'operation' => 'cancel'
        );

        $paymentMethod = new $paymentMethod();

        $config = [
            'integrationKey' => $paymentMethod->private_key,
            'testMode'       => $paymentMethod->is_test_mode,
        ];

        \Ebanx\Config::set($config);

        $request = \Ebanx\Ebanx::doRefund($data);

        if ($request->status !== 'SUCCESS') {
            return false; // TODO: log this
        }

        $order->add_order_note(sprintf('Refund canceled to EBANX - Refund ID: %s', $request->refund->id));

        unset($refunds[$refundKey]);

        update_post_meta($order->id, "_ebanx_payment_refunds", $refunds);

        return true;
    }
}

WC_Ebanx_Refunds::init();
