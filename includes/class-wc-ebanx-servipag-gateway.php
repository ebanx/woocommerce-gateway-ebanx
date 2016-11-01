<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Ebanx_Servipag_Gateway extends WC_Ebanx_Gateway {

	public function __construct() {
		$this->id                   = 'ebanx-servipag';
		$this->icon                 = apply_filters( 'wc_ebanx_servipag_icon', false );
		$this->has_fields           = true;
		$this->method_title         = __( 'EBANX - Servipag', 'woocommerce-ebanx' );
		$this->method_description   = __( 'Accept servipag payments using EBANX.', 'woocommerce-ebanx' );
		$this->view_transaction_url = 'https://dashboard.ebanx.com/#/transactions/%s';

		$this->init_form_fields();

		$this->init_settings();

		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->api_key        = $this->get_option( 'api_key' );
		$this->encryption_key = $this->get_option( 'encryption_key' );
        $this->debug          = $this->get_option( 'debug' );

        if ( 'yes' === $this->debug ) {
            $this->log = new WC_Logger();
        }

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

    public function is_available() {
        return parent::is_available() && isset($_POST['country']) && (strtolower($_POST['country']) == WC_Ebanx_Gateway_Utils::COUNTRY_CHILE);
    }

    /**
     * TODO: ??
     * Admin page.
     */
    /*public function admin_options() {
        include dirname( __FILE__ ) . '/admin/views/notices/html-notice-country-not-supported.php';
    }*/

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-ebanx' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable EBANX Servipag', 'woocommerce-ebanx' ),
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

    public function payment_fields() {
        if ( $description = $this->get_description() ) {
            echo wp_kses_post( wpautop( wptexturize( $description ) ) );
        }

        $cart_total = $this->get_order_total();

        wc_get_template(
            'servipag/payment-form.php',
            array(),
            'woocommerce/ebanx/',
            WC_Ebanx::get_templates_path()
        );
    }

	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		$data  = get_post_meta( $order_id, '_wc_ebanx_transaction_data', true );

		if ( isset( $data['installments'] ) && in_array( $order->get_status(), array( 'processing', 'on-hold' ), true ) ) {
			wc_get_template(
				'servipag/payment-instructions.php',
				array(),
				'woocommerce/ebanx/',
				WC_Ebanx::get_templates_path()
			);
		}
	}

	protected function request_data($order) {
	    /*TODO: ? if (empty($_POST['ebanx_servipag_rut'])) {
	        throw new Exception("Missing rut.");
        }*/

        $data = parent::request_data($order);

        $data['payment']['country'] = strtolower(wc_get_base_location()['country']); // TODO: ? CL ? or Billing ?
        $data['payment']['currency_code'] = WC_Ebanx_Gateway_Utils::CURRENCY_CODE_CLP; // TODO: is_available by currency too?
        $data['payment']['payment_type_code'] = 'servipag';

        return $data;
    }

    protected function save_order_meta_fields( $id, $request ) {
        // TODO: Make this?
    }
}
