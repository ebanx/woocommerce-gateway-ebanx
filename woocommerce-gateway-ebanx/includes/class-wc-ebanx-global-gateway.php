<?php

if (!defined('ABSPATH')) {
    exit;
}

final class WC_EBANX_Global_Gateway extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id                 = 'ebanx-global';
        $this->method_title       = __('EBANX', 'woocommerce-gateway-ebanx');
        $this->method_description = __('EBANX allows you to offer local payment methods.', 'woocommerce-gateway-ebanx');

        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_settings_start', array($this, 'disable_ebanx_gateways'));
    }

    public function disable_ebanx_gateways()
    {
        echo "
        <script>
          jQuery(document).ready(function () {
            var subsub = jQuery('.subsubsub > li:contains(EBANX - )');

            for (var i = 0, t = subsub.length; i < t; ++i) {
              subsub[i].remove();
            }

            jQuery('.ebanx-select').select2();
          });
        </script>
      ";
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'integration_title' => array(
                'title' => __('Integration', 'woocommerce-gateway-ebanx'),
                'type' => 'title',
                'description' => sprintf(__('You can get your Secret Test Key <a href="%s">here</a>.', 'woocommerce-gateway-ebanx'), 'https://google.com'),
            ),
            'sandbox_private_key'       => array(
                'title'       => __('Secret Test Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'sandbox_public_key'        => array(
                'title'       => __('Public Test Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'production_private_key'    => array(
                'title'       => __('Secret Live Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'production_public_key'     => array(
                'title'       => __('Public Live Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'test_mode_enabled'         => array(
                'title'       => __('Test Mode', 'woocommerce-gateway-ebanx'),
                'type'        => 'checkbox',
                'label'       => __('Enable Test Mode', 'woocommerce-gateway-ebanx'),
                'description' => __('Check to enable the test mode.'),
                'desc_tip'    => true,
                'default'     => 'yes',
            ),
            'debug_enabled'             => array(
                'title'       => __('Debug Log', 'woocommerce-gateway-ebanx'),
                'label'       => __('Enable logging', 'woocommerce-gateway-ebanx'),
                'description' => sprintf(__('Log events, such as API requests. You can check the log in <a href="%s">here.</a>', 'woocommerce-gateway-ebanx'), 'http://google.com'),
                'type'        => 'checkbox',
            ),
            'display_methods_title'     => array(
                'title' => __('Display Payment Methods', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
                'description' => sprintf(__('EBANX offers local payment methos for 5 countries in Latin America. Discover more about the methods <a href="%s">here</a>.', 'woocommerce-gateway-ebanx'), 'http://google.com')
            ),
            'brazil_payment_methods'    => array(
                'title'       => __('Brazil', 'woocommerce-gateway-ebanx'),
                'description' => sprintf(__('Know more about Brazil payment methods <a href="%s">here.</a>', 'woocommerce-gateway-ebanx'), 'http://google.com'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-credit-card'    => 'Credit Card',
                    'ebanx-banking-ticket' => 'Boleto',
                    'ebanx-tef'            => 'TEF',
                    'ebanx-account'        => 'EBANX e-wallet',
                ),
            ),
            'mexico_payment_methods'    => array(
                'title'       => __('Mexico', 'woocommerce-gateway-ebanx'),
                'description' => __('Know more about Mexico payment methods <a href="%s">here.</a>', 'woocommerce-gateway-ebanx'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-credit-card' => 'Credit Card',
                    'ebanx-debit-card'  => 'Debit Card',
                    'ebanx-oxxo'        => 'OXXO',
                ),
            ),
            'chile_payment_methods'     => array(
                'title'       => __('Chile', 'woocommerce-gateway-ebanx'),
                'description' => __('Know more about Chile payment methods <a href="%s">here.</a>', 'woocommerce-gateway-ebanx'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-sencillito'    => 'Sencillito',
                    'ebanx-baking-ticket' => 'ServiPag',
                ),
            ),
            'colombia_payment_methods'  => array(
                'title'       => __('Colombia', 'woocommerce-gateway-ebanx'),
                'description' => __('Know more about Colombia payment methods <a href="%s">here.</a>', 'woocommerce-gateway-ebanx'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-eft' => 'EFT',
                ),
            ),
            'peru_payment_methods'      => array(
                'title'       => __('Peru', 'woocommerce-gateway-ebanx'),
                'description' => __('Know more about Peru payment methods <a href="%s">here.</a>', 'woocommerce-gateway-ebanx'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-safetypay'    => 'SafetyPay',
                    'ebanx-pagoefectivo' => 'Pago Efectivo',
                ),
            ),
            'payment_options_title'     => array(
                'title' => __('Payment Options', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'credit_card_options_title' => array(
                'title' => __('Credit Card', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'save_credit_card_enabled' => array(
                'title' => __('Save Credit Card', 'woocommerce-gateway-ebanx'),
                'type' => 'checkbox',
                'label' => __('Enable the option to save card for next time', 'woocommerce-gateway-ebanx')
            ),
            'one_click' => array(
                'type'        => 'checkbox',
                'title'       => __('One-Click Payment', 'woocommerce-gateway-ebanx'),
                'label'       => __('Enable save card for next time', 'woocommerce-gateway-ebanx'),
                'default'     => 'no',
            ),
            'one_click_label_button'    => array(
                'title'       => __('One-Click Payment label button', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
                'description' => __('Label to One-Click payment button', 'woocommerce-gateway-ebanx'),
                'default'     => 'One-Click Payment',
            ),
            'capture_enabled'           => array(
                'type'    => 'checkbox',
                'title'   => __('Capture', 'woocommerce-gateway-ebanx'),
                'label'   => __('Enable auto capture.', 'woocommerce-gateway-ebanx'),
                'default' => 'no',
            ),
            'enable_place_order'        => array(
                'title' => __('Place order', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Enable place order.', 'woocommerce-gateway-ebanx'),
            ),
            'credit_card_instalments'   => array(
                'title'       => __('Maximum nº of Installments', 'woocommerce-gateway-ebanx'),
                'type'        => 'select',
                'default'     => '12',
                'class'       => 'ebanx-select',
                'options'     => array(
                    '1'  => '1',
                    '2'  => '2',
                    '3'  => '3',
                    '4'  => '4',
                    '5'  => '5',
                    '6'  => '6',
                    '7'  => '7',
                    '8'  => '8',
                    '9'  => '9',
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ),
            ),
            'one_click_enabled'         => array(
                'type'    => 'checkbox',
                'title'   => __('One-Click Payment', 'woocommerce-gateway-ebanx'),
                'label'   => __('Enable save card for next time.', 'woocommerce-gateway-ebanx'),
                'default' => 'no',
            ),
            'capture_enabled'           => array(
                'type'    => 'checkbox',
                'title'   => __('Capture', 'woocommerce-gateway-ebanx'),
                'label'   => __('Capture the payment immediatly.', 'woocommerce-gateway-ebanx'),
                'default' => 'yes',
            ),
            'avoid_duplication_enabled' => array(
                'title' => __('Avoid duplication', 'woocommerce-gateway-ebanx'),
                'type' => 'checkbox',
                'label' => __('Keep only EBANX on credit card checkout.', 'woocommerce-gateway-ebanx')
            ),
            'cash_options_title'        => array(
                'title' => __('Cash', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'due_date_days'             => array(
                'title'   => __('Due Date in days', 'woocommerce-gateway-ebanx'),
                'type'    => 'select',
                'default' => '3',
                'class'   => 'ebanx-select',
                'options' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                ),
            )
        );
    }

    public function is_available()
    {
        return false;
    }
}
