<?php

if (!defined('ABSPATH')) {
    exit;
}

require('class-wc-ebanx-gateway.php');
require('class-wc-ebanx-credit-card-gateway.php');

class WC_Ebanx_One_Click
{
    private
        $userId,
        $gateway,
        $orderAction = 'ebanx_create_order'
    ;

    public function __construct()
    {
        $this->userId  = get_current_user_id();
        $this->gateway = new WC_Ebanx_Credit_Card_Gateway();

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);
        add_action('woocommerce_before_add_to_cart_form', array($this, 'add_button'));

        if( isset( $_REQUEST['ebanx_one_click'] ) && $_REQUEST['ebanx_one_click'] == 'is_one_click' ) {

            add_action( 'wp_loaded', array( $this, 'empty_cart' ), 1 );

            add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'one_click_url' ), 99, 1 );
        }

        add_action('wp_loaded', array($this, 'one_click_handler'), 99);
    }

    public function one_click_url( $url )
    {
        if( ! isset( $_REQUEST['add-to-cart'] ) ){
            return $url;
        }

        $product_id = intval( $_REQUEST['add-to-cart'] );

        // create nonce
        $nonce = wp_create_nonce( $this->orderAction );
        $args = apply_filters( 'ebanx_one_click_url_args', array( '_ebanx_one_click_action' => $this->orderAction, '_ebanx_nonce' => $nonce ) );

        return esc_url_raw( add_query_arg( $args, get_permalink( $product_id ) ) );
    }

    public function empty_cart()
    {
        $cart = WC()->session->get( 'cart' );
        update_user_meta( $this->userId, '__ebanx_persistent_cart', $cart );

        WC()->cart->empty_cart( true );
    }

    public function one_click_handler()
    {
        if( is_admin()
            || ! isset( $_GET['_ebanx_one_click_action'] ) || $_GET['_ebanx_one_click_action'] != $this->orderAction
            || ! isset( $_GET['_ebanx_nonce'] ) || ! wp_verify_nonce( $_GET['_ebanx_nonce'], $this->orderAction )
            || ! $this->customerCan()
        ){
            return;
        }

        global $wpdb;
        $order = false;
        $url = '';

        wc_clear_notices();

        WC()->session->__unset( 'chosen_shipping_methods' );
        WC()->shipping();

        if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
            define( 'WOOCOMMERCE_CHECKOUT', true );
        }

        try{
            $wpdb->query( 'START TRANSACTION' );

            $order = wc_create_order( array(
                'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
                'customer_id'   => $this->userId
            ));

            if ( is_wp_error( $order ) ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'ebanx-woocommerce-one-click-checkout' ), 400 ) );
            } else {
                $order_id = $order->id;
                do_action( 'woocommerce_new_order', $order_id );
            }

            $billing_address = apply_filters( 'ebanx_filter_billing_address', $this->get_user_billing_address( $this->userId ), $this->userId );

            if ( WC()->cart->needs_shipping() ) {
                $shipping_address = apply_filters( 'ebanx_filter_shipping_address', $this->get_user_shipping_address( $this->userId ), $this->userId );
                $shipping_address = empty( $shipping_address ) ? $billing_address : $shipping_address;

                $this->setShippingInfo( $shipping_address );
            }
            else {
                $shipping_address = array();
            }

            WC()->cart->calculate_totals();

            foreach ( WC()->cart->get_cart() as $item_cart_key => $item ) {
                $url = get_permalink( $item['product_id'] );

                $item_id = $order->add_product(
                    $item['data'],
                    $item['quantity'],
                    array(
                        'variation' => $item['variation'],
                        'totals'    => array(
                            'subtotal'     => $item['line_subtotal'],
                            'subtotal_tax' => $item['line_subtotal_tax'],
                            'total'        => $item['line_total'],
                            'tax'          => $item['line_tax'],
                            'tax_data'     => $item['line_tax_data']
                        )
                    )
                );

                if ( ! $item_id ) {
                    throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'ebanx-woocommerce-one-click-checkout' ), 402 ) );
                }

                do_action( 'woocommerce_add_order_item_meta', $item_id, $item, $item_cart_key );
            }

            foreach ( WC()->cart->get_fees() as $fee_key => $fee ) {
                $item_id = $order->add_fee( $fee );

                if ( ! $item_id ) {
                    throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'ebanx-woocommerce-one-click-checkout' ), 403 ) );
                }
                // Allow plugins to add order item meta to fees
                do_action( 'woocommerce_add_order_fee_meta', $order_id, $item_id, $fee, $fee_key );
            }

            if ( WC()->cart->needs_shipping() ) {
                if ( ! in_array( WC()->customer->get_shipping_country(), array_keys( WC()->countries->get_shipping_countries() ) ) ) {
                    throw new Exception( sprintf( __( 'Unfortunately <strong>we do not ship to %s</strong>. Please enter an alternative shipping address.', 'ebanx-woocommerce-one-click-checkout' ), WC()->countries->shipping_to_prefix() . ' ' . WC()->customer->get_shipping_country() ) );
                }

                $packages        = WC()->shipping->get_packages();
                $shipping_method = apply_filters( 'ebanx_filter_shipping_methods', WC()->session->get( 'chosen_shipping_methods' ) );

                foreach ( $packages as $package_key => $package ) {

                    if ( isset( $package['rates'][ $shipping_method [ $package_key ] ] ) ) {

                        $item_id = $order->add_shipping( $package['rates'][ $shipping_method[ $package_key ] ] );

                        if ( ! $item_id ) {
                            throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'ebanx-woocommerce-one-click-checkout' ), 404 ) );
                        }

                        do_action( 'woocommerce_add_shipping_order_item', $order_id, $item_id, $package_key );
                    }
                    else {
                        throw new Exception( __( 'Sorry, invalid shipping method.', 'ebanx-woocommerce-one-click-checkout' ) );
                    }
                }
            }

            foreach ( array_keys( WC()->cart->taxes + WC()->cart->shipping_taxes ) as $tax_rate_id ) {
                if ( $tax_rate_id && ! $order->add_tax( $tax_rate_id, WC()->cart->get_tax_amount( $tax_rate_id ), WC()->cart->get_shipping_tax_amount( $tax_rate_id ) ) && apply_filters( 'woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated' ) !== $tax_rate_id ) {
                    throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'ebanx-woocommerce-one-click-checkout' ), 405 ) );
                }
            }

            $order->set_address( $billing_address, 'billing' );
            $order->set_address( $shipping_address, 'shipping' );
            $order->set_total( WC()->cart->shipping_total, 'shipping' );
            $order->set_total( WC()->cart->get_cart_discount_total(), 'cart_discount' );
            $order->set_total( WC()->cart->get_cart_discount_tax_total(), 'cart_discount_tax' );
            $order->set_total( WC()->cart->tax_total, 'tax' );
            $order->set_total( WC()->cart->shipping_tax_total, 'shipping_tax' );
            $order->set_total( WC()->cart->total );

            $wpdb->query( 'COMMIT' );

            // TODO: Apply credit card payment method and finish with that

            $message = apply_filters( 'ebanx_success_msg_order_created', __( 'Thank you. Your order has been received and it is now waiting for payment', 'ebanx-woocommerce-one-click-checkout' ) );
            wc_add_notice( $message, 'success' );
        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );

            if ( $e->getMessage() ) {
                wc_add_notice( $e->getMessage(), 'error' );
            }

            $order = false;
        }

         do_action( 'ebanx_handler_before_redirect', $order );

        $this->restore_cart();
        wp_safe_redirect( apply_filters( 'ebanx_redirect_after_create_order', $url, $order ) );
        exit;
    }

    public function restore_cart(){

        // delete current cart
        WC()->cart->empty_cart( true );

        // update user meta with saved persistent
        $saved_cart = get_user_meta( $this->_user_id, '__ebanx_persistent_cart', true );
        // then reload cart
        WC()->session->set( 'cart', $saved_cart );
        WC()->cart->get_cart_from_session();
    }

    // TODO: Migrate to gateway
    public function get_user_billing_address( $id ) {

        // Formatted Addresses
        $billing = array(
            'first_name' => get_user_meta( $id, 'billing_first_name', true ),
            'last_name'  => get_user_meta( $id, 'billing_last_name', true ),
            'company'    => get_user_meta( $id, 'billing_company', true ),
            'address_1'  => get_user_meta( $id, 'billing_address_1', true ),
            'address_2'  => get_user_meta( $id, 'billing_address_2', true ),
            'city'       => get_user_meta( $id, 'billing_city', true ),
            'state'      => get_user_meta( $id, 'billing_state', true ),
            'postcode'   => get_user_meta( $id, 'billing_postcode', true ),
            'country'    => get_user_meta( $id, 'billing_country', true ),
            'email'      => get_user_meta( $id, 'billing_email', true ),
            'phone'      => get_user_meta( $id, 'billing_phone', true )
        );

        if ( ! empty( $billing['country'] ) ) {
            WC()->customer->set_country( $billing['country'] );
        }
        if ( ! empty( $billing['state'] ) ) {
            WC()->customer->set_state( $billing['state'] );
        }
        if ( ! empty( $billing['postcode'] ) ) {
            WC()->customer->set_postcode( $billing['postcode'] );
        }

        return apply_filters( 'ebanx_customer_billing', array_filter( $billing ) );
    }

    // TODO: Migrate to gateway
    public function get_user_shipping_address( $id ) {

        if( ! WC()->cart->needs_shipping_address() ) {
            return array();
        }

        // Formatted Addresses
        $shipping = array(
            'first_name' => get_user_meta( $id, 'shipping_first_name', true ),
            'last_name'  => get_user_meta( $id, 'shipping_last_name', true ),
            'company'    => get_user_meta( $id, 'shipping_company', true ),
            'address_1'  => get_user_meta( $id, 'shipping_address_1', true ),
            'address_2'  => get_user_meta( $id, 'shipping_address_2', true ),
            'city'       => get_user_meta( $id, 'shipping_city', true ),
            'state'      => get_user_meta( $id, 'shipping_state', true ),
            'postcode'   => get_user_meta( $id, 'shipping_postcode', true ),
            'country'    => get_user_meta( $id, 'shipping_country', true )
        );

        return apply_filters( 'ebanx_customer_shipping', array_filter( $shipping ) );
    }

    public function setShippingInfo( $values )
    {

        // Update customer location to posted location so we can correctly check available shipping methods
        if ( ! empty( $values['country'] ) ) {
            WC()->customer->set_shipping_country( $values['country'] );
        }
        if ( ! empty( $values['state'] ) ) {
            WC()->customer->set_shipping_state( $values['state'] );
        }
        if ( ! empty( $values['postcode'] ) ) {
            WC()->customer->set_shipping_postcode( $values['postcode'] );
        }
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'woocommerce_ebanx_one_click_script',
            plugins_url('assets/js/one-click.js', WC_Ebanx::DIR),
            array(),
            WC_Ebanx::VERSION, true
        );

        // TODO: Solved apply css
        wp_enqueue_style(
            'woocommerce_ebanx_one_click_style',
            plugins_url('assets/css/one-click.css', WC_Ebanx::DIR),
            array(),
            WC_Ebanx::VERSION, true
        );

        /* TODO: this?
          $custom_css = "
                .ebanx-button {
                    background-color: " . get_option( 'ebanx-button-background' ) . " !important;
                    color: " . get_option( 'ebanx-button-text' ) . " !important;
                }
                .ebanx-button:hover {
                    background-color: " . get_option( 'ebanx-button-background-hover' ) . " !important;
                    color: " . get_option( 'ebanx-button-text-hover' ) . " !important;
                }";

        wp_add_inline_style( 'ebanx-style', $custom_css );*/
    }

    public function add_button()
    {

        global $product;

        if($product->product_type == 'external' || !$this->customerCan() ||
            !$this->gateway->is_available() || $this->gateway->settings['one_click'] !== 'yes'
        ) {
            return;
        }

        if($product->product_type == 'variable') {
            add_action('woocommerce_after_single_variation', array($this, 'print_button'));
        } else {
            add_action('woocommerce_after_add_to_cart_button', array($this, 'print_button'));
        }
    }

    public function customerCan()
    {
        $return = true;

        if(!is_user_logged_in() || !get_user_meta( $this->userId, 'billing_email', true ))
        {
            $return = false;
        }

        return apply_filters('ebanx_customerCan', $return);
    }

    public function print_button()
    {
        $args = apply_filters('ebanx_template_args', array(
            'label' => $this->gateway->settings['one_click_label_button'],
        ));

        wc_get_template('one-click.php', $args, '', WC_Ebanx::get_templates_path().'credit-card/');
    }
}

new WC_Ebanx_One_Click();
