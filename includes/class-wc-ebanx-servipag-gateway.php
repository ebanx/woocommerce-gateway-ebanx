<?php
/**
 * Ebanx.com Servipag gateway
 *
 * @package WooCommerce_Ebanx/Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Ebanx_Servipag_Gateway class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Ebanx_Servipag_Gateway extends WC_Ebanx_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
        parent::__construct();

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
		// TODO: $this->debug                = $this->get_option( 'debug' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        // TODO: Make ??
		// add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		// add_action( 'woocommerce_api_wc_ebanx_credit_card_gateway', array( $this, 'ipn_handler' ) );
	}

    /**
     * Check if the gateway is available to take payments.
     *
     * @return bool
     */
    public function is_available() {
        return parent::is_available() && (strtolower(wc_get_base_location()['country']) == WC_Ebanx_Gateway_Utils::COUNTRY_CHILE);
    }

    /**
     * TODO: ??
     * Admin page.
     */
    /*public function admin_options() {
        include dirname( __FILE__ ) . '/admin/views/notices/html-notice-country-not-supported.php';
    }*/

    /**
	 * Settings fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-ebanx' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable EBANX Servipag', 'woocommerce-ebanx' ),
				'default' => 'no',
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-ebanx' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-ebanx' ),
				'desc_tip'    => true,
				'default'     => __( 'Servipag', 'woocommerce-ebanx' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-ebanx' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-ebanx' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay with Servipag', 'woocommerce-ebanx' ),
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
			)
		);
	}

    /**
     * Payment fields.
     */
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

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
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

	/**
	 * Add content to the WC emails.
	 *
	 * @param  object $order         Order object.
	 * @param  bool   $sent_to_admin Send to admin.
	 * @param  bool   $plain_text    Plain text or HTML.
	 *
	 * @return string                Payment instructions.
	 */
	/*public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $sent_to_admin || ! in_array( $order->get_status(), array( 'processing', 'on-hold' ), true ) || $this->id !== $order->payment_method ) {
			return;
		}

		$data = get_post_meta( $order->id, '_wc_ebanx_transaction_data', true );

		if ( isset( $data['installments'] ) ) {
			$email_type = $plain_text ? 'plain' : 'html';

			wc_get_template(
				'credit-card/emails/' . $email_type . '-instructions.php',
				array(
					'card_brand'   => $data['card_brand'],
					'installments' => $data['installments'],
				),
				'woocommerce/ebanx/',
				WC_Ebanx::get_templates_path()
			);
		}
	}*/
}
