<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class WC_EBANX_One_Click {
    private $cards;
    private $userId;
    private $gateway;
    private $orderAction = 'ebanx_create_order';

    /**
     * Constructor
     */
    public function __construct() {
        $this->userId  = get_current_user_id();
        $this->userCountry = trim(strtolower(get_user_meta( $this->userId, 'billing_country', true )));

        $this->gateway = $this->userCountry ? ($this->userCountry === WC_EBANX_Gateway_Utils::COUNTRY_BRAZIL ? new WC_EBANX_Credit_Card_BR_Gateway() : new WC_EBANX_Credit_Card_MX_Gateway()) : false;

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );
        add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'add_button' ) );

        if ( isset( $_REQUEST['ebanx_one_click'] ) && $_REQUEST['ebanx_one_click'] == 'is_one_click' ) {

            add_action( 'wp_loaded', array( $this, 'empty_cart' ), 1 );

            add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'one_click_url' ), 99, 1 );
        }

        add_action( 'wp_loaded', array( $this, 'one_click_handler' ), 99 );

        $cards = get_user_meta( $this->userId, '_ebanx_credit_card_token', true );

        $this->cards = is_array( $cards ) ? array_filter( $cards ) : array();
    }

    /**
     * It generates the URL for one click process the order
     *
     * @param string  $url The default URL
     * @return string      The new URL
     */
    public function one_click_url( $url ) {
        if ( !isset( $_REQUEST['add-to-cart'] ) || !isset( $_REQUEST['ebanx_one_click_cvv'] )
            || !isset( $_REQUEST['ebanx_one_click_token'] )
        ) {
            return $url;
        }

        $product_id = intval( $_REQUEST['add-to-cart'] );

        $instalments = '1';

        if (!empty($_REQUEST['ebanx-credit-card-installments'])) {
            $instalments = $_REQUEST['ebanx-credit-card-installments'];
        }

        // create nonce
        $nonce = wp_create_nonce( $this->orderAction );
        $args = apply_filters( 'ebanx_one_click_url_args', array(
            '_ebanx_one_click_action' => $this->orderAction,
            '_ebanx_nonce' => $nonce,
            '_ebanx_one_click_token' => $_REQUEST['ebanx_one_click_token'],
            '_ebanx_one_click_cvv' => $_REQUEST['ebanx_one_click_cvv'],
            '_ebanx_one_click_installments' => $instalments
        ));

        return esc_url_raw( add_query_arg( $args, get_permalink( $product_id ) ) );
    }

    /**
     * It cleans the cart after generate a new URL
     *
     * @return void
     */
    public function empty_cart() {
        $cart = WC()->session->get( 'cart' );
        update_user_meta( $this->userId, '_ebanx_persistent_cart', $cart );

        WC()->cart->empty_cart( true );
    }

    /**
     * Process the one click request
     *
     * @return void
     */
    public function one_click_handler() {
        if ( is_admin()
            || ! isset( $_GET['_ebanx_one_click_action'] ) || $_GET['_ebanx_one_click_action'] != $this->orderAction
            || ! isset( $_GET['_ebanx_nonce'] ) || ! wp_verify_nonce( $_GET['_ebanx_nonce'], $this->orderAction )
            || !isset( $_GET['_ebanx_one_click_token'] ) || !isset( $_GET['_ebanx_one_click_cvv'] ) || !isset( $_GET['_ebanx_one_click_installments'] )
            || ! $this->customerCan() || ! $this->customerHasEBANXRequiredData()
        ) {
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
                ) );

            if ( is_wp_error( $order ) ) {
                throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'woocommerce-gateway-ebanx' ), 400 ) );
            } else {
                $order_id = $order->id;
                do_action( 'woocommerce_new_order', $order_id );
            }

            $billing_address = apply_filters( 'ebanx_filter_billing_address', $this->get_user_billing_address(), $this->userId );

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
                    throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'woocommerce-gateway-ebanx' ), 402 ) );
                }

                do_action( 'woocommerce_add_order_item_meta', $item_id, $item, $item_cart_key );
            }

            foreach ( WC()->cart->get_fees() as $fee_key => $fee ) {
                $item_id = $order->add_fee( $fee );

                if ( ! $item_id ) {
                    throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'woocommerce-gateway-ebanx' ), 403 ) );
                }
                // Allow plugins to add order item meta to fees
                do_action( 'woocommerce_add_order_fee_meta', $order_id, $item_id, $fee, $fee_key );
            }

            if ( WC()->cart->needs_shipping() ) {
                if ( ! in_array( WC()->customer->get_shipping_country(), array_keys( WC()->countries->get_shipping_countries() ) ) ) {
                    throw new Exception( sprintf( __( 'Unfortunately <strong>we do not ship to %s</strong>. Please enter an alternative shipping address.', 'woocommerce-gateway-ebanx' ), WC()->countries->shipping_to_prefix() . ' ' . WC()->customer->get_shipping_country() ) );
                }

                $packages        = WC()->shipping->get_packages();
                $shipping_method = apply_filters( 'ebanx_filter_shipping_methods', WC()->session->get( 'chosen_shipping_methods' ) );

                foreach ( $packages as $package_key => $package ) {

                    if ( isset( $package['rates'][ $shipping_method [ $package_key ] ] ) ) {

                        $item_id = $order->add_shipping( $package['rates'][ $shipping_method[ $package_key ] ] );

                        if ( ! $item_id ) {
                            throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'woocommerce-gateway-ebanx' ), 404 ) );
                        }

                        do_action( 'woocommerce_add_shipping_order_item', $order_id, $item_id, $package_key );
                    }
                    else {
                        throw new Exception( __( 'Sorry, invalid shipping method.', 'woocommerce-gateway-ebanx' ) );
                    }
                }
            }

            foreach ( array_keys( WC()->cart->taxes + WC()->cart->shipping_taxes ) as $tax_rate_id ) {
                if ( $tax_rate_id && ! $order->add_tax( $tax_rate_id, WC()->cart->get_tax_amount( $tax_rate_id ), WC()->cart->get_shipping_tax_amount( $tax_rate_id ) ) && apply_filters( 'woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated' ) !== $tax_rate_id ) {
                    throw new Exception( sprintf( __( 'Error %d: Unable to create the order. Please try again.', 'woocommerce-gateway-ebanx' ), 405 ) );
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
            $order->set_payment_method( $this->gateway );

            $data = $this->gateway->process_payment( $order->id );

            if ( $data['result'] !== 'success' ) {
                throw new Exception( 'Error.' );
            }

            $wpdb->query( 'COMMIT' );

            $this->restore_cart();
            wp_redirect( $order->get_checkout_order_received_url() );
            exit;
        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );

            $order = false;
        }

        $this->restore_cart();

        return;
    }

    /**
     * Restore the items of the cart until the last request
     *
     * @return void
     */
    public function restore_cart() {
        // delete current cart
        WC()->cart->empty_cart( true );

        // update user meta with saved persistent
        $saved_cart = get_user_meta( $this->userId, '_ebanx_persistent_cart', true );

        // then reload cart
        WC()->session->set( 'cart', $saved_cart );
        WC()->cart->get_cart_from_session();
    }

    /**
     * It creates the user's billing data to process the one click response
     *
     * @return array
     */
    public function get_user_billing_address() {
        // Formatted Addresses
        $billing = array(
            'first_name' => get_user_meta( $this->userId, 'billing_first_name', true ),
            'last_name'  => get_user_meta( $this->userId, 'billing_last_name', true ),
            'company'    => get_user_meta( $this->userId, 'billing_company', true ),
            'address_1'  => get_user_meta( $this->userId, 'billing_address_1', true ),
            'address_2'  => get_user_meta( $this->userId, 'billing_address_2', true ),
            'city'       => get_user_meta( $this->userId, 'billing_city', true ),
            'state'      => get_user_meta( $this->userId, 'billing_state', true ),
            'postcode'   => get_user_meta( $this->userId, 'billing_postcode', true ),
            'country'    => get_user_meta( $this->userId, 'billing_country', true ),
            'email'      => get_user_meta( $this->userId, 'billing_email', true ),
            'phone'      => get_user_meta( $this->userId, 'billing_phone', true )
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

    /**
     * Get the user's shipping address by user id
     *
     * @param integer $id User ID
     * @return array
     */
    public function get_user_shipping_address( $id ) {

        if ( ! WC()->cart->needs_shipping_address() ) {
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

    /**
     * Set shipping info by user's informations
     *
     * @param array   $values The user's informations
     */
    public function setShippingInfo( $values ) {

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

    /**
     * Set the assets necessary by one click works
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'woocommerce_ebanx_one_click_script',
            plugins_url( 'assets/js/one-click.js', WC_EBANX::DIR ),
            array(),
            WC_EBANX::VERSION,
            true
        );

        wp_enqueue_style(
            'woocommerce_ebanx_one_click_style',
            plugins_url( 'assets/css/one-click.css', WC_EBANX::DIR )
        );
    }

    /**
     * Add the "One-click Purchase" button below "Add Cart" on Product Page
     */
    public function add_button() {
        global $product;

        if ($this->gateway === false) {
            return;
        }

        if (
            $product->product_type == 'external' ||
            !$this->customerCan() ||
            !$this->gateway->is_available() ||
            $this->gateway->configs->settings['one_click'] !== 'yes'
        ) {
            return;
        }

        if ( $product->product_type == 'variable' ) {
            add_action( 'woocommerce_after_single_variation', array( $this, 'print_button' ) );
        } else {
            add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'print_button' ) );
        }
    }

    /**
     * Check if the custom has all required data required by EBANX
     *
     * @return boolean If the user has all required data, return true
     */
    protected function customerHasEBANXRequiredData() {
        $card = current( array_filter( (array) array_filter( get_user_meta( $this->userId, '_ebanx_credit_card_token', true ) ), function ( $card ) {
                    return $card->token == $_GET['_ebanx_one_click_token'];
                } ) );
       	$names = $this->gateway->names;

        $_POST['ebanx_token'] = $card->token;
        $_POST['ebanx_masked_card_number'] = $card->masked_number;
        $_POST['ebanx_brand'] = $card->brand;
        $_POST['ebanx_billing_cvv'] = $_GET['_ebanx_one_click_cvv'];
        $_POST['ebanx_is_one_click'] = true;
        $_POST['ebanx_billing_instalments'] = $_GET['_ebanx_one_click_installments'];

        $_POST[$names['ebanx_billing_brazil_document']] = get_user_meta( $this->userId, '_ebanx_billing_brazil_document', true );
        $_POST[$names['ebanx_billing_brazil_birth_date']] = get_user_meta( $this->userId, '_ebanx_billing_brazil_birth_date', true );

        $_POST['billing_postcode']  = $this->get_user_billing_address()['postcode'];
        $_POST['billing_address_1'] = $this->get_user_billing_address()['address_1'];
        $_POST['billing_city']      = $this->get_user_billing_address()['city'];
        $_POST['billing_state']     = $this->get_user_billing_address()['state'];

        if ( empty( $_POST['ebanx_token'] ) ||
            empty( $_POST['ebanx_masked_card_number'] ) ||
            empty( $_POST['ebanx_brand'] ) ||
            empty( $_POST['ebanx_billing_instalments'] ) ||
            empty( $_POST['ebanx_billing_cvv'] ) ||
            empty( $_POST['ebanx_is_one_click'] ) ||
            !isset( $_POST[$names['ebanx_billing_brazil_document']] ) ||
            empty( $_POST[$names['ebanx_billing_brazil_document']] ) ||
            empty( $_POST['ebanx_billing_brazil_birth_date'] )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the customer is ready
     *
     * @return [type] [description]
     */
    public function customerCan() {
        $return = true;

        if ( !is_user_logged_in() || !get_user_meta( $this->userId, 'billing_email', true ) && !empty( $this->cards ) ) {
            $return = false;
        }

        return apply_filters( 'ebanx_customerCan', $return );
    }

    /**
     * Render the button "One-Click Purchase" using a template
     *
     * @return void
     */
    public function print_button() {
        global $product;

        switch ( get_locale() ) {
            case 'pt_BR':
                $messages = array(
                    'instalments' => 'Número de parcelas',
                );
                break;
            case 'es_ES':
            case 'es_CO':
            case 'es_CL':
            case 'es_PE':
            case 'es_MX':
                $messages = array(
                    'instalments' => 'Meses sin intereses'
                );
                break;
            default:
                $messages = array(
                    'instalments' => 'Number of installments',
                );
                break;
        }

        $args = apply_filters( 'ebanx_template_args', array(
                'cards' => $this->cards,
                'cart_total' => $product->price,
                'max_installment' => $this->gateway->configs->settings['credit_card_instalments'],
                'label' => __( 'Pay with one click', 'woocommerce-gateway-ebanx' ),
                'instalments' => $messages['instalments']
            ) );

        wc_get_template( 'one-click.php', $args, '', WC_EBANX::get_templates_path() . 'one-click/' );
    }
}

new WC_EBANX_One_Click();
