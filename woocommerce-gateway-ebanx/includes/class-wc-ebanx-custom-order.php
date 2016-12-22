<?php

add_action('add_meta_boxes', function() {
    global $woocommerce, $order, $post;

    add_meta_box('mv_other_fields', __('My Field','woocommerce'), function () {
        global $woocommerce, $order, $post;

        $meta_field_data = get_post_meta($post->ID, '_my_choice', true); //? get_post_meta($post->ID, '_my_choice', true) : '';

        $gateways = WC()->payment_gateways->get_available_payment_gateways();

        $gateways = array_filter($gateways, function($gateway) {
            return preg_match('/(ebanx)/', $gateway->id);
        });

        $options = "<option value=''>EBANX Gateway</option>";

        foreach ($gateways as $gateway)
        {
            if ($gateway->api_name) {
                $options .= "<option value='{$gateway->id}'>{$gateway->title}</option>";
            }
        }

        echo '<input type="hidden" name="ebanx-payment-nonce" value="' . wp_create_nonce() . '">';
        echo "<select name='ebanx-payment-type'>{$options}</select>";
        //$meta_field_data
    }, 'shop_order', 'side', 'core');
});

add_action('save_post', function ($orderId) {
    if (!is_admin() || !isset($_POST['ebanx-payment-nonce']) ||
        !wp_verify_nonce($_REQUEST['ebanx-payment-nonce']) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !isset($_POST['ebanx-payment-type']) || empty($_POST['ebanx-payment-type'])
    ) {
        return $orderId;
    }

    $hash     = get_post_meta($orderId, '_ebanx_payment_hash', true);
    $order    = wc_get_order($orderId);
    $gateway  = 'WC_EBANX_'.(join(array_splice(array_map('ucfirst', explode('-', $_POST['ebanx-payment-type'])), 1, 2), '_')).'_Gateway';
    $customer = get_user_by('id', $order->customer_user);

    if (!$order->post_status || $order->post_status !=	"wc-pending" || $order->post->post_type != 'shop_order' ||
        !empty(trim($hash)) || empty($customer) || empty(trim($customer->data->user_nicename)) ||
        empty(trim($customer->data->user_email)) || !class_exists($gateway) || !$order->get_total() > 0
    ) {
        return $orderId;
    }

    $gateway = new $gateway;

    $data = array(
        'name' =>  $customer->data->user_nicename,
        'email' => $customer->data->user_email,
        'amount' => $order->get_total(),
        'currency_code' => 'USD',
        'merchant_payment_code' => $orderId . '-' . md5(rand(123123, 9999999)),
        'payment_type_code' => $gateway->api_name
    );

    $config = [
        'integrationKey' => $gateway->private_key,
        'testMode'       => $gateway->is_test_mode,
    ];

    \Ebanx\Config::set($config);

    $request = \Ebanx\EBANX::doRequest($data);

    if ($request->status != 'SUCCESS') {
        return $orderId;
    }

    $order->add_order_note('Payment created |EBANX|');

    update_post_meta($orderId, 'ebanx-payment-type', $gateway->title);
    update_post_meta($orderId, '_ebanx_payment_hash', $request->payment->hash);
    update_post_meta($orderId, 'Payment\'s Hash', $request->payment->hash);
    update_post_meta($orderId, 'Checkout url', $request->redirect_url);
});