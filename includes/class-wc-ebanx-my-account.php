<?php
/**
 * Ebanx.com My Account actions
 *
 * @package WooCommerce_Ebanx/Frontend
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_Ebanx_My_Account class.
 */
class WC_Ebanx_My_Account
{

    /**
     * Initialize my account actions.
     */
    public function __construct()
    {
        add_filter('woocommerce_my_account_my_orders_actions', array($this, 'my_orders_banking_ticket_link'), 10, 2);
        add_action('woocommerce_order_items_table', array($this, 'order_details'));
    }

    /**
     * Add banking ticket link/button in My Orders section on My Accout page.
     *
     * @param array    $actions Actions.
     * @param WC_Order $order   Order data.
     *
     * @return array
     */
    public function my_orders_banking_ticket_link($actions, $order)
    {
        if ($order->payment_method === 'ebanx-banking-ticket' && in_array($order->get_status(), array('pending', 'on-hold'))) {
          $url = get_post_meta($order->id, 'Banking Ticket URL', true);
          
          if (!empty($url)) {
            $actions[] = array(
              'url' => $url,
              'name' => __('View Banking Ticket', 'woocommerce-ebanx'),
              
            );
          }
        }

        return $actions;
    }
    
    public function order_details($order) {
        switch ($order->payment_method) {
          case 'ebanx-credit-card':
            WC_Ebanx_Credit_Card_Gateway::thankyou_page($order);
            break;
          case 'ebanx-banking-ticket':
            WC_Ebanx_Banking_Ticket_Gateway::thankyou_page($order);
            break;
        }
    }
}

new WC_Ebanx_My_Account();
