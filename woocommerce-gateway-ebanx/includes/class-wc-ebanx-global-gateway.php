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
        $this->method_description = __('EBANX easy-to-setup checkout allows your business to accept local payments in Brazil, Mexico, Colombia, Chile & Peru.', 'woocommerce-gateway-ebanx');

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

        <style>
            .form-table th { width: 250px !important; }
        </style>
      ";
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'integration_title' => array(
                'title' => __('Integration', 'woocommerce-gateway-ebanx'),
                'type' => 'title',
                'description' => sprintf(__('You can obtain the integration keys in the settings section, logging in to the <a href="https://www.ebanx.com/business/en/dashboard">EBANX Dashboard.</a>', 'woocommerce-gateway-ebanx'), 'https://google.com'),
            ),
            'sandbox_private_key'       => array(
                'title'       => __('Sandbox Integration Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'sandbox_public_key'        => array(
                'title'       => __('Sandbox Public Integration Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'production_private_key'    => array(
                'title'       => __('Live Integration Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'production_public_key'     => array(
                'title'       => __('Live Public Integration Key', 'woocommerce-gateway-ebanx'),
                'type'        => 'text',
            ),
            'test_mode_enabled'         => array(
                'title'       => __('EBANX Sandbox', 'woocommerce-gateway-ebanx'),
                'type'        => 'checkbox',
                'label'       => __('Enable Sandbox Mode', 'woocommerce-gateway-ebanx'),
                'description' => __('EBANX Sandbox is a testing environment that mimics the live environment. Use it to make payment requests to see how your ecommerce processes them.'),
                'desc_tip'    => true,
                'default'     => 'yes',
            ),
            'debug_enabled'             => array(
                'title'       => __('Debug Log', 'woocommerce-gateway-ebanx'),
                'label'       => __('Enable Debug Log', 'woocommerce-gateway-ebanx'),
                'description' => __('Record all errors that occur when executing a transaction.', 'woocommerce-gateway-ebanx'),
                'type'        => 'checkbox',
                'desc_tip' => true
            ),
            'display_methods_title'     => array(
                'title' => __('Enable Payment Methods', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
                'description' => sprintf(__('Set up payment methods for your checkout. Confirm that method is enabled on your contract.', 'woocommerce-gateway-ebanx'), 'http://google.com')
            ),
            'brazil_payment_methods'    => array(
                'title'       => __('Brazil', 'woocommerce-gateway-ebanx'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-credit-card'    => 'Credit Card',
                    'ebanx-banking-ticket' => 'Boleto EBANX',
                    'ebanx-tef'            => 'Online Banking (TEF)',
                    'ebanx-account'        => 'EBANX Wallet',
                ),
            ),
            'mexico_payment_methods'    => array(
                'title'       => __('Mexico', 'woocommerce-gateway-ebanx'),
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
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-sencillito'    => 'Sencillito',
                    'ebanx-servipag' => 'Servipag',
                ),
            ),
            'colombia_payment_methods'  => array(
                'title'       => __('Colombia', 'woocommerce-gateway-ebanx'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-eft' => 'PSE - Pago Seguros en Línea (EFT)',
                ),
            ),
            'peru_payment_methods'      => array(
                'title'       => __('Peru', 'woocommerce-gateway-ebanx'),
                'type'        => 'multiselect',
                'class'       => 'ebanx-select',
                'options'     => array(
                    'ebanx-safetypay'    => 'SafetyPay',
                    'ebanx-pagoefectivo' => 'PagoEfectivo',
                ),
            ),
            'advanced_options_title'     => array(
                'title' => __('Advanced Options', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'credit_card_options_title' => array(
                'title' => __('Credit Card', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'save_card_data'        => array(
                'title' => __('Save Card Data', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Enable saving card data', 'woocommerce-gateway-ebanx'),
                'description' => __('Allow your customer to save credit card and debit card data for future purchases.', 'woocommerce-gateway-ebanx'),
                'desc_tip' => true
            ),
            'one_click' => array(
                'type'        => 'checkbox',
                'title'       => __('One-Click Payment', 'woocommerce-gateway-ebanx'),
                'label'       => __('Enable one-click-payment', 'woocommerce-gateway-ebanx'),
                'default'     => 'no',
                'description' => __('Allow your customer to complete payments in one-click using credit cards saved.', 'woocommerce-gateway-ebanx'),
                'desc_tip' => true
            ),
            'capture_enabled' => array(
                'type'    => 'checkbox',
                'title'   => __('Enable Auto-Capture', 'woocommerce-gateway-ebanx'),
                'label'   => __('Capture the payment immediatly.', 'woocommerce-gateway-ebanx'),
                'default' => 'yes',
                'description' => __('Automatically capture payments from your customers. Otherwise you will need to capture the payment going to: WooCommerce -> Orders. Not captured payments will be cancelled in 4 days.', 'woocommerce-gateway-ebanx'),
                'desc_tip' => true
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
                'description' => __('Establish the maximum number of installments in which your customer can pay, as consented on your contract.', 'woocommerce-gateway-ebanx'),
                'desc_tip' => true
            ),
            'cash_options_title'        => array(
                'title' => __('Cash Payments', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'due_date_days'             => array(
                'title'   => __('Days to Expiration', 'woocommerce-gateway-ebanx'),
                'type'    => 'select',
                'default' => '3',
                'class'   => 'ebanx-select',
                'options' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                ),
                'description' => __('Define the maximum number of days on which your customer can complete the payment of: Boleto, OXXO, Sencilito, PagoEfectivo and SafetyPay.', 'woocommerce-gateway-ebanx'),
                'desc_tip' => true
            )
        );
    }

    public function is_available()
    {
        return false;
    }
}
