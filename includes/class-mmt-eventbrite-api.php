<?php
/**
 * Eventon ExIm - Eventbrite API functions.
 *
 * @package mmt-eo-exim
 * @author MoMo Themes
 * @since v1.1
 */
class MMT_Eventbrite_API {
	/**
	 * Eventbrite API Token
	 *
	 * @var string
	 */
	private $api_token;
	/**
	 * Eventbrite API URL
	 *
	 * @var [type]
	 */
	private $api_url;
	/**
	 * API Token Organizations List
	 *
	 * @var array
	 */
	private $organization_id;
	/**
	 * Constructor
	 */
	public function __construct() {
		global $mmt_eo_exim;
		$mmt_eo_exim_options   = get_option( 'mmt_eo_exim_options' );
		$this->api_token       = isset( $mmt_eo_exim_options['eb_private_token'] ) ? $mmt_eo_exim_options['eb_private_token'] : '';
		$this->organization_id = isset( $mmt_eo_exim_options['eb_display_organizer'] )
								&&
								! empty( $mmt_eo_exim_options['eb_display_organizer'] )
								? $mmt_eo_exim_options['eb_display_organizer']
								: $this->mmt_get_default_organization();
		$this->api_url         = 'https://www.eventbriteapi.com/v3/';
	}
	/**
	 * Get Organizations Events
	 */
	public function mmt_get_organizations_events() {
		global $mmt_eo_emin;
		$endpoint = 'organizations/' . $this->organization_id . '/events/';
		$url      = $this->api_url . $endpoint . '?token=' . $this->api_token . '&time_filter=current_future&expand=organizer,venue';
		$response = wp_remote_get( $url );
		$json     = wp_remote_retrieve_body( $response );
		$details  = json_decode( $json );
		if ( isset( $details->events ) ) {
			return $details->events;
		}
		return false;
	}
	/**
	 * Get Default Organization
	 */
	public function mmt_get_default_organization() {
		global $mmt_eo_exim;
		$organization_list = $mmt_eo_exim->fn->mmt_eo_exim_eb_org_list();
		if ( ! empty( $organization_list ) ) {
			$org_id = array_values( $organization_list )[0];
		} else {
			$org_id = false;
		}
		return $org_id;
	}
}
