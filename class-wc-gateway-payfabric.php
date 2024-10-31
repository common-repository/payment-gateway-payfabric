<?php

/**
 * WC_PayFabric_Gateway class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_PayFabric_Gateway extends WC_Payment_Gateway_CC
{
    public function __construct()
    {
        // Set gateway info
        $this->init_gateway();
        // Load the form fields
        $this->init_form_fields();
        // Load the settings.
        $this->init_settings();
        // Init the PayFabric API
        $this->init_api();
        // Update options on save
        add_action( "woocommerce_update_options_payment_gateways_{$this->id}", [ $this, 'process_admin_options' ] );
    }
    
    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     *
     * @since 3.4.0
     * @return bool
     */
    public function needs_setup()
    {
        return !!!$this->setup_id;
    }
    
    /**
     * Init the gateway info.
     */
    public function init_gateway()
    {
        $this->id = 'payfabric';
        $this->method_title = 'PayFabric';
        $this->method_description = 'Accept credit card payments via the PayFabric payment processing platform.';
        $this->icon = WP_PLUGIN_URL . "/" . plugin_basename( dirname( __FILE__ ) ) . '/assets/images/cards.png';
        $this->has_fields = true;
        $this->supports = [
            'products',
            'refunds',
            'tokenization',
            'add_payment_method'
        ];
    }
    
    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled'            => [
            'title'       => 'Enable/Disable',
            'label'       => 'Enable PayFabric',
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no',
        ],
            'title'              => [
            'title'       => 'Title',
            'type'        => 'text',
            'description' => 'This controls the title which the user sees during checkout.',
            'default'     => 'Credit Card',
            'desc_tip'    => true,
        ],
            'description'        => [
            'title'       => 'Description',
            'type'        => 'text',
            'description' => 'This controls the description which the user sees during checkout.',
            'default'     => 'Pay with your credit card.',
            'desc_tip'    => true,
        ],
            'setup_id'           => [
            'title'       => 'Gateway Profile Name/Setup ID',
            'type'        => 'text',
            'description' => 'Gateway account profile name. This name is configurable and is defined by the client on the PayFabric web portal under Settings > Gateway Account Configuration.',
            'default'     => '',
            'desc_tip'    => true,
        ],
            'device_id'          => [
            'title'       => 'Device ID',
            'label'       => 'PayFabric Device ID authentication credentials.',
            'type'        => 'text',
            'description' => 'PayFabric clients require PayFabric Device ID and Password to authenticate with APIs.',
            'default'     => '',
            'desc_tip'    => true,
        ],
            'device_password'    => [
            'title'       => 'Device Password',
            'label'       => 'PayFabric Device Password authentication credentials.',
            'type'        => 'password',
            'description' => 'PayFabric clients require PayFabric Device ID and Password to authenticate with APIs.',
            'default'     => '',
            'desc_tip'    => true,
        ],
            'testmode'           => [
            'title'       => 'Test Mode',
            'label'       => 'Enable PayFabric Sandbox/Test Mode',
            'type'        => 'checkbox',
            'description' => 'Use PayFabric\'s sandbox API with your sandbox authentication credentials.',
            'default'     => 'no',
            'desc_tip'    => true,
        ],
            'transaction_type'   => [
            'title'   => 'Transaction Type',
            'type'    => 'select',
            'options' => [
            'Sale'          => 'Sale (Authorize & Capture)',
            'Authorization' => 'Authorization Only (Book)',
        ],
            'default' => 'Sale',
        ],
            'customer_id_prefix' => [
            'title'       => 'Customer ID Prefix',
            'type'        => 'text',
            'description' => 'Optionally add a prefix to the PayFabric customer ID for registered users. Customer ID will be passed to PayFabric using the WooCommerce customer ID as {PREFIX}{CUSTOMER_ID}.',
            'default'     => 'WOOCOMMERCE',
            'desc_tip'    => true,
        ],
            'guest_id_prefix'    => [
            'title'       => 'Guest ID Prefix',
            'type'        => 'text',
            'description' => 'Optionally add a prefix to the PayFabric customer ID for guest orders. Customer ID will be passed to PayFabric using the WooCommerce order ID as {PREFIX}{ORDER_ID}.',
            'default'     => 'GUEST',
            'desc_tip'    => true,
        ],
        ];
        
        if ( !wppg_fs()->is_premium() ) {
            unset( $this->form_fields['testmode'] );
            $this->form_fields['sandbox'] = [
                'type'              => 'text',
                'title'             => 'Sandbox Mode',
                'default'           => 'Sandbox mode is enabled',
                'custom_attributes' => [
                'readonly' => 'readonly',
            ],
                'description'       => 'This gateway works in SANDBOX mode only. Please upgrade to process live payments with PayFabric.',
                'desc_tip'          => true,
            ];
        }
    
    }
    
    /**
     * Init the gateway settings.
     */
    public function init_settings()
    {
        parent::init_settings();
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->testmode = true;
        $this->setup_id = $this->get_option( 'setup_id' );
        $this->device_id = $this->get_option( 'device_id' );
        $this->device_password = $this->get_option( 'device_password' );
        $this->transaction_type = $this->get_option( 'transaction_type' );
        $this->customer_id_prefix = $this->get_option( 'customer_id_prefix' );
        $this->guest_id_prefix = $this->get_option( 'guest_id_prefix' );
    }
    
    /**
     * Init the API
     */
    public function init_api()
    {
        PayFabric_Api::init( $this->device_id, $this->device_password );
        if ( $this->testmode ) {
            PayFabric_Api::enable_sandbox_mode();
        }
    }
    
    /**
     * Output field name HTML
     *
     *
     * @since  2.6.0
     * @param  string $name Field name.
     * @return string
     */
    public function field_name( $name )
    {
        return ' name="' . esc_attr( $this->id . '-' . $name ) . '" ';
    }
    
    /**
     * Validate frontend fields.
     *
     * Validate payment fields on the frontend.
     *
     * @return bool
     */
    public function validate_fields()
    {
        return true;
    }
    
    /**
     * Add payment method via account screen.
     *
     * @return array
     */
    public function add_payment_method()
    {
        $customer = WC()->customer;
        $pf_customer_id = $this->get_pf_customer_id( $customer->id );
        $card = $this->get_posted_card_fields();
        try {
            $wallet = $this->prepare_wallet( $pf_customer_id, $customer, $card );
            $this->save_customer_card( $customer, $card, $wallet );
        } catch ( Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
            return [
                'result'   => 'failure',
                'redirect' => wc_get_endpoint_url( 'payment-methods' ),
            ];
        }
        return [
            'result'   => 'success',
            'redirect' => wc_get_endpoint_url( 'payment-methods' ),
        ];
    }
    
    /**
     * Process the payment
     */
    public function process_payment( $order_id )
    {
        $order = new WC_Order( $order_id );
        $card = $this->prepare_card( $order );
        $cvc = $this->get_cvc();
        $customer_id = $this->get_pf_customer_id_for_order( $order );
        $data = [
            'Amount'   => wc_format_decimal( $order->get_total(), 2 ),
            'Currency' => 'USD',
            'Customer' => $customer_id,
            'Card'     => $card,
            'SetupId'  => $this->setup_id,
            'Shipto'   => [
            'Customer' => $customer_id,
            'Email'    => $order->get_billing_email(),
            'Line1'    => $order->get_shipping_address_1(),
            'Line2'    => $order->get_shipping_address_2(),
            'City'     => $order->get_shipping_city(),
            'State'    => $order->get_shipping_state(),
            'Country'  => $order->get_shipping_country(),
            'Zip'      => $order->get_shipping_postcode(),
            'Phone'    => $order->get_billing_phone(),
        ],
            'Tender'   => 'CreditCard',
            'Type'     => $this->transaction_type,
        ];
        try {
            $transaction = PayFabric_Api::create_transaction( apply_filters( 'payfabric_transaction_data', $data ) );
            $txn_id = $transaction['Key'];
            PayFabric_Api::process_transaction( $txn_id, $cvc );
            $order->add_order_note( "PayFabric payment authorized: {$txn_id}" );
            $order->payment_complete( $txn_id );
            // Remove cart.
            WC()->cart->empty_cart();
            $redirect = $order->get_checkout_order_received_url();
            return [
                'result'   => 'success',
                'redirect' => $redirect,
            ];
        } catch ( Exception $e ) {
            do_action( 'payfabric_process_payment_error', $order, $e );
            wc_add_notice( $e->getMessage(), 'error' );
            return;
        }
    }
    
    /**
     * Process refund.
     *
     * If the gateway declares 'refunds' support, this will allow it to refund.
     * a passed in amount.
     *
     * @param  int        $order_id Order ID.
     * @param  float|null $amount Refund amount.
     * @param  string     $reason Refund reason.
     * @return boolean True or false based on success, or a WP_Error object.
     */
    public function process_refund( $order_id, $amount = null, $reason = '' )
    {
        $order = new WC_Order( $order_id );
        $customer_id = $this->get_pf_customer_id_for_order( $order );
        try {
            $transaction = PayFabric_Api::get_transaction( $order->get_transaction_id() );
            $refund = PayFabric_Api::refund_transaction( [
                'SetupId'  => $this->setup_id,
                'Type'     => 'Refund',
                'Customer' => $customer_id,
                'Amount'   => $amount,
                'Currency' => $order->get_currency(),
                'Card'     => [
                'ID' => $transaction['Card']['ID'],
            ],
            ] );
            $order->update_meta_data( 'payfabric_refund', $refund );
            $order->add_order_note( sprintf(
                'PayFabric Refund ID %s at %s for %s',
                $refund['TrxKey'],
                $refund['TrxDate'],
                $refund['TrxAmount']
            ) );
            return true;
        } catch ( Exception $e ) {
            do_action( 'payfabric_process_refund_error', $order, $e );
            wc_add_notice( $e->getMessage(), 'error' );
            return false;
        }
    }
    
    protected function prepare_card( $order )
    {
        $customer = WC()->customer;
        // check if new card or saved token.
        
        if ( $this->is_using_saved_payment_method() ) {
            $token_id = wc_clean( $_POST['wc-payfabric-payment-token'] );
            $token = WC_Payment_Tokens::get( $token_id );
            // Token user ID does not match the current user... bail out of payment processing.
            
            if ( $token->get_user_id() !== $customer->id ) {
                wc_add_notice( 'Could not complete transation using the selected payment method.' );
                return;
            }
            
            return [
                'ID' => $token->get_token(),
            ];
        }
        
        $pf_customer_id = $this->get_pf_customer_id_for_order( $order );
        $card = $this->get_posted_card_fields();
        $wallet = $this->prepare_wallet( $pf_customer_id, $order, $card );
        if ( $this->should_save_card() ) {
            $this->save_customer_card( $customer, $card, $wallet );
        }
        return [
            'ID' => $wallet['ID'],
        ];
    }
    
    protected function get_cvc()
    {
        $customer = WC()->customer;
        // check if new card or saved token.
        
        if ( $this->is_using_saved_payment_method() ) {
            $token_id = wc_clean( $_POST['wc-payfabric-payment-token'] );
            $token = new WC_Payment_Token_PayFabric( $token_id );
            // Token user ID does not match the current user... bail out of payment processing.
            
            if ( $token->get_user_id() !== $customer->id ) {
                wc_add_notice( 'Could not complete transation using the selected payment method.' );
                return;
            }
            
            return $token->get_cvc();
        }
        
        $card = $this->get_posted_card_fields();
        return $card['cvc'];
    }
    
    protected function prepare_wallet( $pf_customer_id, $customer, $card )
    {
        $wallet = ( is_user_logged_in() ? $this->get_wallet_for_customer_card( $pf_customer_id, $card ) : null );
        if ( !$wallet ) {
            $wallet = $this->create_customer_wallet( $pf_customer_id, $customer, $card );
        }
        return $wallet;
    }
    
    protected function get_wallet_for_customer_card( $pf_customer_id, $card )
    {
        $wallets = PayFabric_Api::get_customer_wallets( $pf_customer_id );
        foreach ( $wallets as $key => $wallet ) {
            $wallet_exists = substr( $wallet['Account'], -4 ) == $card['last_four'] && $wallet['ExpDate'] == $card['expiry'];
            if ( $wallet_exists ) {
                return $wallet;
            }
        }
        return null;
    }
    
    /**
     * Checks if payment is via saved payment source.
     *
     * @since 4.1.0
     * @return bool
     */
    protected function is_using_saved_payment_method()
    {
        return isset( $_POST['wc-' . $this->id . '-payment-token'] ) && 'new' !== $_POST['wc-' . $this->id . '-payment-token'];
    }
    
    protected function should_save_card()
    {
        return isset( $_POST['wc-' . $this->id . '-new-payment-method'] ) && $_POST['wc-' . $this->id . '-new-payment-method'] == true;
    }
    
    protected function get_posted_card_fields()
    {
        $number = ( isset( $_POST['payfabric-card-number'] ) ? wc_clean( $_POST['payfabric-card-number'] ) : '' );
        $expiry = ( isset( $_POST['payfabric-card-expiry'] ) ? wc_clean( $_POST['payfabric-card-expiry'] ) : '' );
        $cvc = ( isset( $_POST['payfabric-card-cvc'] ) ? wc_clean( $_POST['payfabric-card-cvc'] ) : '' );
        // format values
        $number = str_replace( [ ' ', '-' ], '', $number );
        $last_four = substr( $number, -4 );
        $expiry = array_map( 'trim', explode( '/', $expiry ) );
        $expiry_month = str_pad(
            $expiry[0],
            2,
            "0",
            STR_PAD_LEFT
        );
        $expiry_year = $expiry[1];
        if ( strlen( $expiry_year ) == 4 ) {
            $expiry_year = $expiry_year - 2000;
        }
        $expiry = $expiry_month . $expiry_year;
        return compact(
            'number',
            'last_four',
            'expiry',
            'expiry_month',
            'expiry_year',
            'cvc'
        );
    }
    
    protected function create_customer_wallet( $pf_customer_id, $customer, $card )
    {
        return PayFabric_Api::create_wallet( [
            'Customer'   => $pf_customer_id,
            'Tender'     => 'CreditCard',
            'Account'    => $card['number'],
            'ExpDate'    => $card['expiry'],
            'Cvc'        => $card['cvc'],
            'CardHolder' => [
            'FirstName' => ( $customer->get_billing_first_name() ?: 'First' ),
            'LastName'  => ( $customer->get_billing_last_name() ?: 'Last' ),
        ],
            'Billto'     => [
            'Email'   => $customer->get_billing_email(),
            'Line1'   => $customer->get_billing_address_1(),
            'Line2'   => $customer->get_billing_address_2(),
            'City'    => $customer->get_billing_city(),
            'State'   => $customer->get_billing_state(),
            'Country' => $customer->get_billing_country(),
            'Zip'     => $customer->get_billing_postcode(),
            'Phone'   => $customer->get_billing_phone(),
        ],
        ] );
    }
    
    protected function save_customer_card( $customer, $card, $wallet )
    {
        $token = new WC_Payment_Token_PayFabric();
        $token->set_gateway_id( $this->id );
        $token->set_token( $wallet['ID'] );
        $token->set_card_type( $wallet['CardName'] );
        $token->set_last4( $card['last_four'] );
        $token->set_expiry_month( $card['expiry_month'] );
        $token->set_expiry_year( $card['expiry_year'] + 2000 );
        $token->set_cvc( $card['cvc'] );
        $token->set_user_id( $customer->id );
        $token->save();
        // Set this token as the users new default token
        WC_Payment_Tokens::set_users_default( $customer->id, $token->get_id() );
        return true;
    }
    
    public function get_pf_customer_id_for_order( $order )
    {
        if ( is_int( $order ) ) {
            $order = new WC_Order( $order );
        }
        if ( $order->get_customer_id() > 0 ) {
            return $this->get_pf_customer_id( $order->get_customer_id() );
        }
        return $this->get_pf_guest_id( $order->get_order_number() );
    }
    
    public function get_pf_customer_id( $customer_id )
    {
        return $this->customer_id_prefix . $customer_id;
    }
    
    public function get_pf_guest_id( $guest_id )
    {
        return $this->guest_id_prefix . $guest_id;
    }

}