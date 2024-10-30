<?php
/**
 * Eventon ExIm - Currency List Array.
 *
 * @package mmt-eo-exim
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_ExIm_Currency_List {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->clist = $this->get_currency_array();
	}
	/**
	 * Get Currency Array
	 */
	public function get_currency_array() {
		return array(
			'ARS' => 'Argentina Peso',
			'AUD' => 'Australia Dollar',
			'BRL' => 'Brazil Real',
			'CAD' => 'Canada Dollar',
			'DKK' => 'Denmark Krone',
			'EUR' => 'Euro Member Countries',
			'HKD' => 'Hong Kong Dollar',
			'HUF' => 'Hungary Forint',
			'ILS' => 'Israel Shekel',
			'JPY' => 'Japan Yen',
			'MYR' => 'Malaysia Ringgit',
			'MXN' => 'Mexico Peso',
			'NZD' => 'New Zealand Dollar',
			'NOK' => 'Norway Krone',
			'PHP' => 'Philippines Peso',
			'PLN' => 'Poland Zloty',
			'SGD' => 'Singapore Dollar',
			'SEK' => 'Sweden Krona',
			'CHF' => 'Switzerland Franc',
			'TWD' => 'Taiwan New Dollar',
			'THB' => 'Thailand Baht',
			'GBP' => 'United Kingdom Pound',
			'USD' => 'United States Dollar',
		);
	}
}
