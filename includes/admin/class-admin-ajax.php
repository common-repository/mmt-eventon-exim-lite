<?php
/**
 * Eventon ExIm - Amin AJAX functions
 *
 * @package mmt-eo-exim
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_CI_Admin_Ajax {
	/**
	 * Constructor
	 */
	public function __construct() {
		$ajax_events = array(
			'mmt_eo_exim_save_eb_settings'       => 'mmt_eo_exim_save_eb_settings', // Main.
			'mmt_eo_exim_fetch_by_event_id_eb'   => 'mmt_eo_exim_fetch_by_event_id_eb', // One.
			'mmt_eo_exim_import_single_event_eb' => 'mmt_eo_exim_import_single_event_eb', // Two.
			'mmt_eo_exim_save_eb_display'        => 'mmt_eo_exim_save_eb_display', // Three.
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Save Eventbrite Settings (Main Settings)
	 */
	public function mmt_eo_exim_save_eb_settings() {
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
		$res                 = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'mmt_eo_exim_save_eb_settings' !== $_POST['action'] ) {
			return;
		}
		$eb_private_token    = isset( $_POST['eb_private_token'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_private_token'] ) ) : '';
		$eb_export_organizer = isset( $_POST['eb_export_organizer'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_export_organizer'] ) ) : '';
		$eb_default_currency = isset( $_POST['eb_default_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_default_currency'] ) ) : '';

		$mmt_eo_exim_options['eb_private_token']    = $eb_private_token;
		$mmt_eo_exim_options['eb_export_organizer'] = $eb_export_organizer;
		$mmt_eo_exim_options['eb_default_currency'] = $eb_default_currency;
		update_option( 'mmt_eo_exim_options', $mmt_eo_exim_options );
		echo wp_json_encode(
			array(
				'status'  => 'good',
				'message' => __( 'Options saved successfully', 'mmt-eo-exim' ),
			)
		);
		exit;
	}
	/**
	 * Fetch Event by Event ID ( One )
	 */
	public function mmt_eo_exim_fetch_by_event_id_eb() {
		global $mmt_eo_exim;
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
		$res                 = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'mmt_eo_exim_fetch_by_event_id_eb' !== $_POST['action'] ) {
			return;
		}
		if ( ! isset( $_POST['event_id'] ) && empty( $_POST['event_id'] ) ) {
			return;
		}
		if ( ! isset( $mmt_eo_exim_options['eb_private_token'] ) || empty( $mmt_eo_exim_options['eb_private_token'] ) ) {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => __( 'Eventbrite private token not found.', 'mmt-eo-exim' ),
				)
			);
			exit;
		}
		$token            = $mmt_eo_exim_options['eb_private_token'];
		$eventbrite_token = 'https://www.eventbriteapi.com/v3/users/me/?token=' . $token;
		$response         = wp_remote_get( $eventbrite_token );
		$json             = wp_remote_retrieve_body( $response );
		$details          = json_decode( $json );
		if ( isset( $details->status_code ) && 401 === $details->status_code ) {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => __( 'The private token you provided was invalid.', 'mmt-eo-exim' ),
				)
			);
			exit;
		}
		$event_id     = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 0;
		$current_list = isset( $_POST['current_list'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['current_list'] ) ) ) : array();
		if ( is_array( $current_list ) && in_array( $event_id, $current_list, true ) ) {
			echo wp_json_encode(
				array(
					'status'   => 'good',
					'msg'      => __( 'Event fetched successfully.', 'mmt-eo-exim' ),
					'info'     => __( 'Event already in list.', 'mmt-eo-exim' ),
					'event_id' => $details->id,
					'elist'    => implode( ',', $current_list ),
					'html'     => '',
				)
			);
			exit;
		}
		$current_list[] = $event_id;
		$eventbrite_url = 'https://www.eventbriteapi.com/v3/events/' . $event_id . '/?token=' . $token;
		$response       = wp_remote_get( $eventbrite_url );
		$json           = wp_remote_retrieve_body( $response );
		$details        = json_decode( $json );
		if ( isset( $details->status_code ) && 404 === $details->status_code ) {
			echo wp_json_encode(
				array(
					'status'   => 'bad',
					'event_id' => $event_id,
					'msg'      => __( 'Provided event does not exist.', 'mmt-eo-exim' ),
				)
			);
			exit;
		}
		if ( isset( $details->status_code ) && 403 === $details->status_code ) {
			echo wp_json_encode(
				array(
					'status'   => 'bad',
					'event_id' => $event_id,
					'msg'      => __( 'You do not have permission to access the resource you requested.', 'mmt-eo-exim' ),
				)
			);
			exit;
		}
		if ( isset( $details->id ) && $event_id === $details->id ) {
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			$srow        = strtotime( $details->start->local );
			$erow        = strtotime( $details->end->local );
			ob_start();
			?>
			<tr data-eb_eid="<?php echo esc_attr( $details->id ); ?>" data-status="<?php echo $mmt_eo_exim->fn->mmt_eo_exim_check_eventbrite_id_exist( $details->id ) ? 'imported' : ''; ?>">
				<td>
					<?php
					if ( isset( $details->logo->url ) && ! empty( $details->logo->url ) ) {
						echo '<img src="' . esc_url( $details->logo->url ) . '" height=100 width=100/>';
					}
					?>
				</td>
				<td>
					<?php echo esc_html( $details->name->text ); ?>
				</td>
				<td>
					<?php echo esc_html( date( $date_format, $srow ) . ' ' . date( $time_format, $srow ) ); ?>
				</td>
				<td>
					<?php echo esc_html( date( $date_format, $erow ) . ' ' . date( $time_format, $erow ) ); ?>
				</td>
				<td>
					<?php echo wp_kses_post( $details->summary ); ?>
				</td>
				<td class="status">
					<?php echo $mmt_eo_exim->fn->mmt_eo_exim_check_eventbrite_id_exist( $details->id ) ? 'Imported' : '-'; ?>
				</td>
			</tr>
			<?php
			echo wp_json_encode(
				array(
					'status'   => 'good',
					'msg'      => __( 'Event fetched successfully.', 'mmt-eo-exim' ),
					'info'     => __( 'Fetched 1 event successfully.', 'mmt-eo-exim' ),
					'event_id' => $details->id,
					'elist'    => implode( ',', $current_list ),
					'html'     => ob_get_clean(),
				)
			);
			exit;
		}
	}
	/**
	 * Import Single Eventbrite Event ( Two )
	 */
	public function mmt_eo_exim_import_single_event_eb() {
		global $mmt_eo_exim;
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
		$res                 = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'mmt_eo_exim_import_single_event_eb' !== $_POST['action'] ) {
			return;
		}
		if ( ! isset( $_POST['event_id'] ) && empty( $_POST['event_id'] ) ) {
			return;
		}
		if ( ! isset( $mmt_eo_exim_options['eb_private_token'] ) || empty( $mmt_eo_exim_options['eb_private_token'] ) ) {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => __( 'Import Error (No Token).', 'mmt-eo-exim' ),
				)
			);
			exit;
		}
		$event_id = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 0;
		$response = $mmt_eo_exim->fn->mmt_fetch_and_import_eventbrite( $event_id );
		if ( $response ) {
			echo wp_json_encode(
				array(
					'status' => 'good',
					'msg'    => __( 'Event(s) imported successfully.', 'mmt-eo-exim' ),
				)
			);
			exit;
		} else {
			echo wp_json_encode(
				array(
					'status' => 'bad',
					'msg'    => __( 'Import Error.', 'mmt-eo-exim' ),
				)
			);
			exit;
		}
	}
	/**
	 * Save Display Settings (Three)
	 */
	public function mmt_eo_exim_save_eb_display() {
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
		$res                 = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
		if ( isset( $_POST['action'] ) && 'mmt_eo_exim_save_eb_display' !== $_POST['action'] ) {
			return;
		}
		$enable_eventbrite_widget = isset( $_POST['enable_eventbrite_widget'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_eventbrite_widget'] ) ) : '';
		$eb_display_organizer     = isset( $_POST['eb_display_organizer'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_display_organizer'] ) ) : '';

		$mmt_eo_exim_options['enable_eventbrite_widget'] = $enable_eventbrite_widget;
		$mmt_eo_exim_options['eb_display_organizer']     = $eb_display_organizer;
		update_option( 'mmt_eo_exim_options', $mmt_eo_exim_options );
		echo wp_json_encode(
			array(
				'status'  => 'good',
				'message' => __( 'Options saved successfully', 'mmt-eo-exim' ),
			)
		);
		exit;
	}
}
new MMT_EO_CI_Admin_Ajax();
