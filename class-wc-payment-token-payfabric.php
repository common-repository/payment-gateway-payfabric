<?php

/**
 * WC_Payment_Token_PayFabric class.
 *
 * @extends WC_Payment_Token_CC
 */
class WC_Payment_Token_PayFabric extends WC_Payment_Token_CC
{
    /**
	 * Returns the cvc.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return string CVC
	 */
	public function get_cvc( $context = 'view' ) {
		return $this->get_meta( 'cvc', $context );
	}

    /**
	 * Set the cvc.
	 *
	 * @param string $cvc Credit card cvc.
	 */
	public function set_cvc( $cvc ) {
		$this->add_meta_data( 'cvc', $cvc );
	}
}