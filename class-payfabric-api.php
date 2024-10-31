<?php

class PayFabric_Api
{
    const LIVE_API_URL = 'https://www.payfabric.com/payment/api';

    const SANDBOX_API_URL = 'https://sandbox.payfabric.com/payment/api';

    public static $sandbox = false;

    public static $device_id;

    public static $device_password;

    public static function init($device_id, $device_password)
    {
        static::$device_id = $device_id;
        static::$device_password = $device_password;
    }

    /**
     * Create and save a transaction to the PayFabric server.
     */
    public static function create_transaction(array $data)
    {
        $response = wp_remote_post(static::get_url('transaction/create'), [
            'data_format' => 'body',
            'body'        => json_encode($data),
            'timeout'     => 60,
            'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => static::get_auth_header(),
                ]
            ] );

        if ( is_wp_error( $response ) ) {
            throw new Exception('There was a problem connecting to the payment gateway.');
        }

        $code = $response['response']['code'];
        $body = $response['body'];

        if ($code >= 300) {
            throw new Exception($body);
        }

        return json_decode($body, true);
    }

    /**
     * Attempt to process the transaction with the payment gateway (via PayFabric).
     */
    public static function process_transaction($transaction_id, $cvc = null)
    {
        $response = wp_remote_get(static::get_url("transaction/process/$transaction_id?cvc=$cvc"), [

            'headers' => [
                    'Authorization' => static::get_auth_header(),
                ]
        ]);

        if ( is_wp_error( $response ) ) {
            throw new Exception('There was a problem connecting to the payment gateway.');
        }

        $code = $response['response']['code'];
        $body = $response['body'];

        if ($code >= 300) {
            throw new Exception($body);
        }

        $body = json_decode($body, true);

        if ($body['PayFabricErrorCode']) {
            throw new Exception($body['Message']);
        }

        return $body;
    }

    public static function refund_transaction(array $data)
    {
        $response = wp_remote_post(static::get_url('transaction/process'), [
            'data_format' => 'body',
            'body'        => json_encode($data),
            'timeout'     => 60,
            'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => static::get_auth_header(),
                ]
            ] );

        if ( is_wp_error( $response ) ) {
            throw new Exception('There was a problem connecting to the payment gateway.');
        }

        $code = $response['response']['code'];
        $body = $response['body'];

        if ($code >= 300) {
            throw new Exception($body);
        }

        $body = json_decode($body, true);

        if ($body['PayFabricErrorCode']) {
            throw new Exception($body['Message']);
        }

        return $body;
    }

    /**
     * Get transaction details.
     *
     * @throws Exception
     *
     * @param string $transaction_id Transaction ID.
     */
    public static function get_transaction($transaction_id)
    {
        $response = wp_remote_get(static::get_url("transaction/$transaction_id"), [
            'headers' => [
                    'Authorization' => static::get_auth_header(),
                ]
        ]);

        if ( is_wp_error( $response ) ) {
            throw new Exception('There was a problem connecting to the payment gateway.');
        }

        $code = $response['response']['code'];
        $body = $response['body'];

        if ($code >= 300) {
            throw new Exception($body);
        }

        return json_decode($body, true);
    }

    public static function create_wallet(array $data)
    {
        $response = wp_remote_post(static::get_url('wallet/create'), [
            'data_format' => 'body',
            'body'        => json_encode($data),
            'timeout'     => 60,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => static::get_auth_header(),
            ]
        ]);

        if ( is_wp_error( $response ) ) {
            throw new Exception('There was a problem connecting to the payment gateway.');
        }

        $code = $response['response']['code'];
        $body = $response['body'];

        if ($code >= 300) {
            throw new Exception($body);
        }

        $body = json_decode($body, true);

        return self::get_wallet($body['Result']);
    }

    public static function get_wallet($wallet_id)
    {
        $response = wp_remote_get(static::get_url("wallet/get/$wallet_id"), [
            'headers' => [
                    'Authorization' => static::get_auth_header(),
                ]
        ]);

        if ( is_wp_error( $response ) ) {
            throw new Exception('There was a problem connecting to the payment gateway.');
        }

        $code = $response['response']['code'];
        $body = $response['body'];

        if ($code >= 300) {
            throw new Exception($body);
        }

        return json_decode($body, true);
    }

    public static function get_customer_wallets($customer_id)
    {
        $response = wp_remote_get(static::get_url("/wallet/getByCustomer?customer=$customer_id&tender=CreditCard"), [
            'headers' => [
                    'Authorization' => static::get_auth_header(),
                ]
        ]);

        if ( is_wp_error( $response ) ) {
            throw new Exception('There was a problem connecting to the payment gateway.');
        }


        $code = $response['response']['code'];
        $body = $response['body'];

        if ($code >= 300) {
            throw new Exception($body);
        }

        return json_decode($body, true);
    }

    public static function enable_sandbox_mode()
    {
        static::$sandbox = true;
    }

    public static function is_sandbox()
    {
        return static::$sandbox;
    }

    /**
     * Get API endpoint URL.
     */
    protected static function get_url($path)
    {
        $base = static::is_sandbox() ? static::SANDBOX_API_URL : static::LIVE_API_URL;

        return $base.'/'.ltrim($path, '/');
    }

    protected static function get_auth_header()
    {
        return sprintf('%s|%s', static::$device_id, static::$device_password);
    }
}