<?php

/**
 * Plugin Name: Payment Gateway for PayFabric
 * Plugin URI: https://tools.cypressnorth.com/payfabric-plugin/
 * Description: A WooCommerce payment gateway for PayFabric.
 * Version: 1.0.13
 * Author: CypressNorth
 * Author URI: https://cypressnorth.com/
 * Requires at least: 5.0
 * Tested up to: 5.9.3
 * WC requires at least: 4.0
 * WC tested up to: 6.4.1
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wppg_fs' ) ) {
    wppg_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'wppg_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wppg_fs()
        {
            global  $wppg_fs ;
            
            if ( !isset( $wppg_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wppg_fs = fs_dynamic_init( array(
                    'id'             => '7699',
                    'slug'           => 'payment-gateway-payfabric',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_aa442eb5653ed5323cd82972cccf1',
                    'is_premium'     => false,
                    'premium_suffix' => 'Standard',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'           => 'wc-settings',
                    'override_exact' => true,
                    'first-path'     => 'admin.php?page=wc-settings&tab=checkout&section=payfabric',
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => array(
                    'slug' => 'woocommerce',
                ),
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $wppg_fs;
        }
        
        // Init Freemius.
        wppg_fs();
        // Signal that SDK was initiated.
        do_action( 'wppg_fs_loaded' );
        function wppg_fs_settings_url()
        {
            return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payfabric' );
        }
        
        wppg_fs()->add_filter( 'connect_url', 'wppg_fs_settings_url' );
        wppg_fs()->add_filter( 'after_skip_url', 'wppg_fs_settings_url' );
        wppg_fs()->add_filter( 'after_connect_url', 'wppg_fs_settings_url' );
        wppg_fs()->add_filter( 'after_pending_connect_url', 'wppg_fs_settings_url' );
    }
    
    /**
     * Gatway Class
     */
    class WC_PayFabric
    {
        public function __construct()
        {
            
            if ( class_exists( 'WC_Payment_Gateway' ) ) {
                include 'class-payfabric-api.php';
                include 'class-wc-gateway-payfabric.php';
                include 'class-wc-payment-token-payfabric.php';
                add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
            }
        
        }
        
        /**
         * Register the gateway for use
         *
         * @param array $methods Payment methods.
         *
         * @return array Payment methods
         */
        public function register_gateway( $methods )
        {
            $methods[] = 'WC_PayFabric_Gateway';
            return $methods;
        }
    
    }
    /**
     * Return instance of WC_PayFabric_Gateway.
     *
     * @return WC_PayFabric
     */
    function wc_payfabic()
    {
        static  $wc_payfabric ;
        if ( !isset( $wc_payfabric ) ) {
            $wc_payfabric = new WC_PayFabric();
        }
        return $wc_payfabric;
    }
    
    function woocommerce_payfabric_init()
    {
        $GLOBALS['wc_payfabric'] = wc_payfabic();
        return true;
    }
    
    add_action( 'plugins_loaded', 'woocommerce_payfabric_init' );
}
