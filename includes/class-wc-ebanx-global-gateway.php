<?php

if (!defined('ABSPATH')) {
    exit;
}

final class WC_Ebanx_Global_Gateway extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-global';
        $this->method_title = __('EBANX', 'woocommerce-gateway-ebanx');

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
          });
        </script>
      ";
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'keys_title'              => array(
                'title' => __('Keys'),
                'type'  => 'title',
            ),
            'production_public_key'   => array(
                'title'       => __('Production Public Key', 'woocommerce-gateway-ebanx'),
                'description' => __('The key that will be used for read your informations.', 'woocommerce-gateway-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'production_private_key'  => array(
                'title'       => __('Production Private Key', 'woocommerce-gateway-ebanx'),
                'description' => __('Your secret and unique key to create payments only sandbox mode.', 'woocommerce-gateway-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'sandbox_public_key'      => array(
                'title'       => __('Sandbox Public Key', 'woocommerce-gateway-ebanx'),
                'description' => __('The key that will be used for read your informations only sandbox mode.', 'woocommerce-gateway-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'sandbox_private_key'     => array(
                'title'       => __('Sandbox Private Key', 'woocommerce-gateway-ebanx'),
                'description' => __('Your secret and unique key to create payments.', 'woocommerce-gateway-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'development_title'       => array(
                'title' => __('Development', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'sandbox_enabled'         => array(
                'title' => __('Enable/Disable Sandbox', 'woocommerce-gateway-ebanx'),
                'label' => __('Check to enable the sandbox mode', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
            ),
            'debug_enabled'           => array(
                'title' => __('Enable/Disable Debug', 'woocommerce-gateway-ebanx'),
                'label' => __('Check to enable the debug logging.', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
            ),
            'general_title'           => array(
                'title' => __('General Payment\'s Settings', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'credit_card_title'       => array(
                'title' => __('Credit Card', 'woocommerce-gateway-ebanx'),
                'type'  => 'title',
            ),
            'ebanx-credit-card'       => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Credit Card', 'woocommerce-gateway-ebanx'),
            ),
            'credit_card_countries'       => array(
                'title' => __('Countries', 'woocommerce-gateway-ebanx'),
                'type'  => 'multiselect',
                'label' => __('Choose countries', 'woocommerce-gateway-ebanx'),
                'options' => WC_Ebanx_Gateway_Utils::CREDIT_CARD_COUNTRIES
            ),
            'enable_place_order' => array(
                'title' => __('Place order', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Enable place order.', 'woocommerce-ebanx'),
            ),
            'one_click' => array(
                'type'        => 'checkbox',
                'title'       => __('One click payment', 'woocommerce-ebanx'),
                'description' => __('Enable one-click payment.', 'woocommerce-ebanx'),
                'label'       => __('Enable one-click payment', 'woocommerce-ebanx'),
                'default'     => 'no'
            ),
            'one_click_label_button' => array(
                'title'             => __('one-click payment label button', 'woocommerce-ebanx'),
                'type'              => 'text',
                'description'       => __('Label to one-click payment button', 'woocommerce-ebanx'),
                'default'           => 'one-click payment'
            ),
            'credit_card_instalments' => array(
                'title'       => __('Number of Installment', 'woocommerce-gateway-ebanx'),
                'type'        => 'select',
                'default'     => '12',
                'description' => __('Maximum number of installments possible with payments by credit card.', 'woocommerce-gateway-ebanx'),
                'desc_tip'    => true,
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
            'banking_ticket_title'    => array(
                'title' => __('Banking Ticket'),
                'type'  => 'title',
            ),
            'ebanx-banking-ticket'    => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Banking Ticket', 'woocommerce-gateway-ebanx'),
            ),
            'baking_ticket_due_date'  => array(
                'title'       => __('Due Date in Days', 'woocommerce-gateway-ebanx'),
                'type'        => 'number',
                'description' => __('The number of days to the due date of baking ticket.', 'woocommerce-gateway-ebanx'),
                'desc_tip'    => true,
            ),
            'oxxo_title'              => array(
                'title' => __('Oxxo'),
                'type'  => 'title',
            ),
            'ebanx-oxxo'              => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Oxxo', 'woocommerce-gateway-ebanx'),
            ),
            'servipag_title'          => array(
                'title' => __('Servipag'),
                'type'  => 'title',
            ),
            'ebanx-servipag'          => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Servipag', 'woocommerce-gateway-ebanx'),
            ),
            'pago_efectivo_title'     => array(
                'title' => __('PagoEfectivo'),
                'type'  => 'title',
            ),
            'ebanx-pagoefectivo'      => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX PagoEfectivo', 'woocommerce-gateway-ebanx'),
            ),
            'safetypay_title'         => array(
                'title' => __('SafetyPay'),
                'type'  => 'title',
            ),
            'ebanx-safetypay'         => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX SafetyPay', 'woocommerce-gateway-ebanx'),
            ),
            'tef_title'               => array(
                'title' => __('TEF Brazil'),
                'type'  => 'title',
            ),
            'ebanx-tef'               => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX TEF Brazil', 'woocommerce-gateway-ebanx'),
            ),
            'eft_title'               => array(
                'title' => __('EFT'),
                'type'  => 'title',
            ),
            'ebanx-eft'               => array(
                'title' => __('Enable/Disabled', 'woocommerce-gateway-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX EFT', 'woocommerce-gateway-ebanx'),
            ),
        );
    }

    public function is_available()
    {
        return false;
    }
}
