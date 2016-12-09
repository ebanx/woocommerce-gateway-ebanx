<?php

if (!defined('ABSPATH')) {
    exit;
}

final class WC_Ebanx_Global_Gateway extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id           = 'ebanx-global';
        $this->method_title = __('EBANX', 'woocommerce-ebanx');

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
                'title'       => __('Production Public Key', 'woocommerce-ebanx'),
                'description' => __('The key that will be used for read your informations.', 'woocommerce-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'production_private_key'  => array(
                'title'       => __('Production Private Key', 'woocommerce-ebanx'),
                'description' => __('Your secret and unique key to create payments only sandbox mode.', 'woocommerce-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'sandbox_public_key'      => array(
                'title'       => __('Sandbox Public Key', 'woocommerce-ebanx'),
                'description' => __('The key that will be used for read your informations only sandbox mode.', 'woocommerce-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'sandbox_private_key'     => array(
                'title'       => __('Sandbox Private Key', 'woocommerce-ebanx'),
                'description' => __('Your secret and unique key to create payments.', 'woocommerce-ebanx'),
                'desc_tip'    => true,
                'type'        => 'text',
            ),
            'development_title'       => array(
                'title' => __('Development', 'woocommerce-ebanx'),
                'type'  => 'title',
            ),
            'sandbox_enabled'         => array(
                'title' => __('Enable/Disable Sandbox', 'woocommerce-ebanx'),
                'label' => __('Check to enable the sandbox mode', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
            ),
            'debug_enabled'           => array(
                'title' => __('Enable/Disable Debug', 'woocommerce-ebanx'),
                'label' => __('Check to enable the debug logging.', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
            ),
            'general_title'           => array(
                'title' => __('General Payment\'s Settings', 'woocommerce-ebanx'),
                'type'  => 'title',
            ),
            'credit_card_title'       => array(
                'title' => __('Credit Card', 'woocommerce-ebanx'),
                'type'  => 'title',
            ),
            'ebanx-credit-card'       => array(
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Credit Card', 'woocommerce-ebanx'),
            ),
            'credit_card_countries'       => array(
                'title' => __('Countries', 'woocommerce-ebanx'),
                'type'  => 'multiselect',
                'label' => __('Choose countries', 'woocommerce-ebanx'),
                'options' => WC_Ebanx_Gateway_Utils::CREDIT_CARD_COUNTRIES
            ),
            'enable_one_click'        => array(
                'title' => __('One click', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Enable one-click.', 'woocommerce-ebanx'),
            ),
            'enable_instant_payment' => array(
                'type'        => 'checkbox',
                'title'       => __('instant payment', 'woocommerce-ebanx'),
                'description' => __('Enable instant payment.', 'woocommerce-ebanx'),
                'label'       => __('Enable instant payment', 'woocommerce-ebanx'),
                'default'     => 'no',
                'custom_attributes' => array('required' => 'required')
            ),
            'instant_payment_label_button' => array(
                'title'             => __('Instant payment label button', 'woocommerce-ebanx'),
                'type'              => 'text',
                'description'       => __('Label to instant payment button', 'woocommerce-ebanx'),
                'default'           => 'Instant payment',
                'custom_attributes' => array('required' => 'required')
            ),
            'credit_card_instalments' => array(
                'title'       => __('Number of Installment', 'woocommerce-ebanx'),
                'type'        => 'select',
                'default'     => '12',
                'description' => __('Maximum number of installments possible with payments by credit card.', 'woocommerce-ebanx'),
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
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Banking Ticket', 'woocommerce-ebanx'),
            ),
            'baking_ticket_due_date'  => array(
                'title'       => __('Due Date in Days', 'woocommerce-ebanx'),
                'type'        => 'number',
                'description' => __('The number of days to the due date of baking ticket.', 'woocommerce-ebanx'),
                'desc_tip'    => true,
            ),
            'oxxo_title'              => array(
                'title' => __('Oxxo'),
                'type'  => 'title',
            ),
            'ebanx-oxxo'              => array(
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Oxxo', 'woocommerce-ebanx'),
            ),
            'servipag_title'          => array(
                'title' => __('Servipag'),
                'type'  => 'title',
            ),
            'ebanx-servipag'          => array(
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX Servipag', 'woocommerce-ebanx'),
            ),
            'pago_efectivo_title'     => array(
                'title' => __('PagoEfectivo'),
                'type'  => 'title',
            ),
            'ebanx-pagoefectivo'      => array(
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX PagoEfectivo', 'woocommerce-ebanx'),
            ),
            'safetypay_title'         => array(
                'title' => __('SafetyPay'),
                'type'  => 'title',
            ),
            'ebanx-safetypay'         => array(
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX SafetyPay', 'woocommerce-ebanx'),
            ),
            'tef_title'               => array(
                'title' => __('TEF Brazil'),
                'type'  => 'title',
            ),
            'ebanx-tef'               => array(
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX TEF Brazil', 'woocommerce-ebanx'),
            ),
            'eft_title'               => array(
                'title' => __('EFT'),
                'type'  => 'title',
            ),
            'ebanx-eft'               => array(
                'title' => __('Enable/Disabled', 'woocommerce-ebanx'),
                'type'  => 'checkbox',
                'label' => __('Check to enable EBANX EFT', 'woocommerce-ebanx'),
            ),
        );
    }

    public function is_available()
    {
        return false;
    }
}
