<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Ebanx_Credit_Card_Gateway extends WC_Ebanx_Gateway {

	public function __construct() {
		$this->id                   = 'ebanx-credit-card';
		$this->icon                 = apply_filters( 'wc_ebanx_credit_card_icon', false );
		$this->has_fields           = true;
		$this->method_title         = __( 'EBANX - Credit Card', 'woocommerce-ebanx' );
		$this->method_description   = __( 'Accept credit card payments using EBANX.', 'woocommerce-ebanx' );
		$this->view_transaction_url = 'https://dashboard.ebanx.com/#/transactions/%s';

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->api_key              = $this->get_option( 'api_key' );
		$this->encryption_key       = $this->get_option( 'encryption_key' );
		$this->checkout             = $this->get_option( 'checkout' );
		$this->max_installment      = $this->get_option( 'max_installment' );
		$this->smallest_installment = $this->get_option( 'smallest_installment' );
		$this->interest_rate        = $this->get_option( 'interest_rate', '0' );
		$this->free_installments    = $this->get_option( 'free_installments', '1' );
        $this->debug                = $this->get_option( 'debug' );

        if ( 'yes' === $this->debug ) {
            $this->log = new WC_Logger();
        }

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-ebanx' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable EBANX Credit Card', 'woocommerce-ebanx' ),
				'default' => 'no',
			),
			'integration' => array(
				'title'       => __( 'Integration Settings', 'woocommerce-ebanx' ),
				'type'        => 'title',
				'description' => '',
			),
			'api_key' => array(
				'title'             => __( 'EBANX API Key', 'woocommerce-ebanx' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Please enter your EBANX API Key. This is needed to process the payment and notifications. Is possible get your API Key in %s.', 'woocommerce-ebanx' ), '<a href="https://dashboard.ebanx.com/">' . __( 'EBANX Dashboard > My Account page', 'woocommerce-ebanx' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'encryption_key' => array(
				'title'             => __( 'EBANX Encryption Key', 'woocommerce-ebanx' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Please enter your EBANX Encryption key. This is needed to process the payment. Is possible get your Encryption Key in %s.', 'woocommerce-ebanx' ), '<a href="https://dashboard.ebanx.com/">' . __( 'EBANX Dashboard > My Account page', 'woocommerce-ebanx' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'max_installment' => array(
				'title'       => __( 'Number of Installment', 'woocommerce-ebanx' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'default'     => '12',
				'description' => __( 'Maximum number of installments possible with payments by credit card.', 'woocommerce-ebanx' ),
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
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'woocommerce-ebanx' ),
				'type'        => 'title',
				'description' => '',
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'woocommerce-ebanx' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-ebanx' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log EBANX events, such as API requests. You can check the log in %s', 'woocommerce-ebanx' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woocommerce-ebanx' ) . '</a>' ),
			),
		);
	}

    public function checkout_scripts() {
        if ( is_checkout() ) {
            wp_enqueue_script( 'wc-credit-card-form' );
            wp_enqueue_script('ebanx', 'http://downloads.ebanx.com/poc-checkout/src/ebanx-new.js', '', '1.0', true);
            wp_enqueue_script('woocommerce_ebanx', plugins_url('assets/js/credit-card.js', WC_Ebanx::DIR), array('jquery-payment', 'ebanx'), WC_Ebanx::VERSION, true);

            $ebanx_params = array(
                'key' => $this->encryption_key,
                'i18n_terms' => __('Please accept the terms and conditions first', 'woocommerce-gateway-ebanx'),
                'i18n_required_fields' => __('Please fill in required checkout fields first', 'woocommerce-gateway-ebanx'),
            );

            // If we're on the pay page we need to pass ebanx.js the address of the order.
            if (is_checkout_pay_page() && isset($_GET['order']) && isset($_GET['order_id'])) {
                $order_key = urldecode($_GET['order']);
                $order_id = absint($_GET['order_id']);
                $order = wc_get_order($order_id);

                if ($order->id === $order_id && $order->order_key === $order_key) {
                    $ebanx_params['billing_first_name'] = $order->billing_first_name;
                    $ebanx_params['billing_last_name'] = $order->billing_last_name;
                    $ebanx_params['billing_address_1'] = $order->billing_address_1;
                    $ebanx_params['billing_address_2'] = $order->billing_address_2;
                    $ebanx_params['billing_state'] = $order->billing_state;
                    $ebanx_params['billing_city'] = $order->billing_city;
                    $ebanx_params['billing_postcode'] = $order->billing_postcode;
                    $ebanx_params['billing_country'] = $order->billing_country;
                }
            }

            wp_localize_script('woocommerce_ebanx', 'wc_ebanx_params', apply_filters('wc_ebanx_params', $ebanx_params));
        }
    }

	public function payment_fields() {
		if ( $description = $this->get_description() ) {
			echo wp_kses_post( wpautop( wptexturize( $description ) ) );
		}

		$cart_total = $this->get_order_total();

		if ( 'no' === $this->checkout ) {
			$installments = 1; // TODO: see $this->api->get_installments( $cart_total );

			wc_get_template(
				'credit-card/payment-form.php',
				array(
					'cart_total'           => $cart_total,
					'max_installment'      => $this->max_installment,
					'smallest_installment' => 1, // TODO: see $this->api->get_smallest_installment(),
					'installments'         => $installments,
				),
				'woocommerce/ebanx/',
				WC_Ebanx::get_templates_path()
			);
		} else {
			echo '<div id="ebanx-checkout-params" ';
			echo 'data-total="' . esc_attr( $cart_total * 100 ) . '" ';
			echo 'data-max_installment="' . esc_attr( apply_filters( 'wc_ebanx_checkout_credit_card_max_installments', 1 /* TODO: $this->api->get_max_installment( $cart_total )*/ ) ) . '"';
			echo '></div>';
		}
	}

	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		$data  = get_post_meta( $order_id, '_wc_ebanx_transaction_data', true );

		if ( isset( $data['installments'] ) && in_array( $order->get_status(), array( 'processing', 'on-hold' ), true ) ) {
			wc_get_template(
				'credit-card/payment-instructions.php',
				array(
					'card_brand'   => $data['card_brand'],
					'installments' => $data['installments'],
				),
				'woocommerce/ebanx/',
				WC_Ebanx::get_templates_path()
			);
		}
	}

	protected function request_data($order) {
	    if (empty($_POST['ebanx_token'])) {
	        throw new Exception("Missing token.");
        }
        $data = parent::request_data($order);
        $data['payment']['payment_type_code'] = 'visa'; // TODO: Dynamic
        $data['payment']['creditcard'] = array(
            'token' => $_POST['ebanx_token'] // TODO: get from ?
        );
        return $data;
    }

    protected function process_response($request, $order) {
        if ($request->status == 'ERROR'||!$request->payment->pre_approved) {
            return $this->process_response_error($request, $order);
        }

        parent::process_response($request, $order);
    }

    protected function save_order_meta_fields( $id, $request ) {
        // TODO: Make this?
        /*
        if ( ! empty( $data['card_brand'] ) ) {
            update_post_meta( $id, __( 'Credit Card', 'woocommerce-pagarme' ), $this->get_card_brand_name( sanitize_text_field( $data['card_brand'] ) ) );
        }
        if ( ! empty( $data['installments'] ) ) {
            update_post_meta( $id, __( 'Installments', 'woocommerce-pagarme' ), sanitize_text_field( $data['installments'] ) );
        }
        if ( ! empty( $data['amount'] ) ) {
            update_post_meta( $id, __( 'Total paid', 'woocommerce-pagarme' ), number_format( intval( $data['amount'] ) / 100, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ) );
        }
        if ( ! empty( $data['antifraud_score'] ) ) {
            update_post_meta( $id, __( 'Anti Fraud Score', 'woocommerce-pagarme' ), sanitize_text_field( $data['antifraud_score'] ) );
        }*/
    }
}
