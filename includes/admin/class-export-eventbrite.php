<?php
/**
 * Eventon ExIm - Export event to Eventbrite.
 *
 * @package mmt-eo-exim
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_ExIm_Export_Eventbrite {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'save_post_ajde_events', array( $this, 'mmt_eo_exim_export_to_eventbrite' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'mmt_eo_exim_export_meta_box' ) );
	}

	/**
	 * Add Export Metabox
	 */
	public function mmt_eo_exim_export_meta_box() {
		add_meta_box(
			'mmt_eo_exim_export_mb',
			__( 'Export Event', 'mmt-eo-exim' ),
			array(
				$this,
				'mmt_eo_exim_export_meta_box_content',
			),
			'ajde_events',
			'side',
			'high'
		);
	}

	/**
	 * Export MetaBox Content
	 */
	public function mmt_eo_exim_export_meta_box_content() {
		global $post, $eventon;
		$event_pmv                        = ( ! empty( $post ) ) ? get_post_custom( $post->ID ) : null;
		$mmt_eo_exim_export_to_eventbrite = ( ! empty( $event_pmv['mmt_eo_exim_export_to_eventbrite'] ) ) ? $event_pmv['mmt_eo_exim_export_to_eventbrite'][0] : null;
		wp_nonce_field( basename( __FILE__ ), 'mmt_eo_exim_export_to_eventbrite_nonce' );
		if ( 'on' === $mmt_eo_exim_export_to_eventbrite ) :
			?>
			<p style='text-align: center;opacity: 0.5;padding: 5px;background-color: #F0F0F0;border-radius:6px'>
				<?php esc_html_e( 'Already exported ot Eventbrite!', 'mmt-eo-exim' ); ?>
			</p>
			<?php
			else :
				?>

		<p class='mmt-be-mb-side'>
			<span class="mmt-be-toggle-container">
				<label class="switch">
				<?php $checked = ( 'on' === $mmt_eo_exim_export_to_eventbrite ) ? 'checked' : ''; ?>
					<input type="checkbox" class="switch-input" name="mmt_eo_exim_export_to_eventbrite" <?php echo esc_html( $checked ); ?>>
					<span class="switch-label" data-on="Yes" data-off="No"></span>
					<span class="switch-handle"></span>
				</label>
			</span>
			<span class="mmt-be-toggle-container-label">
				<?php esc_html_e( 'Export to EventBrite.', 'mmt-eo-exim' ); ?>
			</span>
		</p>
			<?php
		endif;
	}
	/**
	 * Export Event to Eventbrite
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post WP Post.
	 */
	public function mmt_eo_exim_export_to_eventbrite( $post_ID, $post ) {
		global $mmt_eo_exim;
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );

		$token        = isset( $mmt_eo_exim_options['eb_private_token'] ) ? $mmt_eo_exim_options['eb_private_token'] : '';
		$organizer_id = isset( $mmt_eo_exim_options['eb_export_organizer'] ) ? $mmt_eo_exim_options['eb_export_organizer'] : '';

		$event_id = $post_ID;
		if ( empty( $event_id ) ) {
			return;
		}
		$epmv = get_post_custom( $post_ID );
		if (
			! isset( $_POST['mmt_eo_exim_export_to_eventbrite'] )
			||
			! isset( $_POST['mmt_eo_exim_export_to_eventbrite_nonce'] )
			||
			! wp_verify_nonce( $_POST['mmt_eo_exim_export_to_eventbrite_nonce'], basename( __FILE__ ) )
		) {
			return $post_ID;
		}
		// If already Posted.
		if ( isset( $epmv['mmt_eo_exim_export_to_eventbrite'][0] ) && 'on' === $epmv['mmt_eo_exim_export_to_eventbrite'][0] ) {
			return;
		}
		$mmt_eo_exim_export_to_eventbrite = isset( $_POST['mmt_eo_exim_export_to_eventbrite'] ) ? sanitize_text_field( wp_unslash( $_POST['mmt_eo_exim_export_to_eventbrite'] ) ) : 'off';
		if ( 'on' === $mmt_eo_exim_export_to_eventbrite ) {
			if ( empty( $organizer_id ) ) {
				return;
			}
			$new_event   = get_post( $event_id );
			$event       = array();
			$srow        = $epmv['evcal_srow'][0];
			$erow        = isset( $epmv['evcal_erow'][0] ) ? $epmv['evcal_erow'][0] : $srow + 60;
			$date_format = get_option( 'date_format' );
			$start_date  = date( 'Y-m-d\TH:i:s', $srow );
			$end_date    = date( 'Y-m-d\TH:i:s', $erow );

			$event['event.name.html']        = get_the_title( $event_id );
			$event['event.summary']          = 'Test Excerpt';
			$event['event.description.html'] = get_the_content( $event_id );
			$event['event.start.timezone']   = empty( get_option( 'timezone_string' ) ) ? 'Asia/Kathmandu' : get_option( 'timezone_string' );
			$event['event.end.timezone']     = empty( get_option( 'timezone_string' ) ) ? 'Asia/Kathmandu' : get_option( 'timezone_string' );
			$event['event.start.utc']        = $start_date . 'Z';
			$event['event.end.utc']          = $end_date . 'Z';
			$event['event.currency']         = isset( $mmt_eo_exim_options['evo_eb_default_currency'] ) ? $mmt_eo_exim_options['evo_eb_default_currency'] : 'USD';
			if ( has_post_thumbnail( $event_id ) ) {
				$image_url   = get_the_post_thumbnail_url( $event_id );
				$image_id    = get_post_thumbnail_id( $event_id );
				$mime_type   = get_post_mime_type( $image_id );
				$uploads_dir = wp_upload_dir();
				$image_src   = str_replace( $uploads_dir['baseurl'], $uploads_dir['basedir'], $image_url );
				$image_arr   = $this->mmt_eo_exim_upload_image( $image_src, $token, $mime_type );
				if ( is_wp_error( $image_arr ) ) {
					$error_string = $image_arr->get_error_message();
					return $event_id;
				}
				$event['event.logo_id'] = $image_arr['id'];
				$event['event.logo.id'] = $image_arr['id'];
			}
			// For repeating events.
			$repeat_intervals = isset( $epmv['repeat_intervals'][0] ) ? $epmv['repeat_intervals'][0] : '';
			$freq             = isset( $epmv['evcal_rep_freq'][0] ) ? $epmv['evcal_rep_freq'][0] : '';
			$repeat           = isset( $epmv['evcal_repeat'][0] ) ? $epmv['evcal_repeat'][0] : '';
			if ( isset( $epmv['evcal_repeat'][0] ) && 'yes' === $epmv['evcal_repeat'][0] && 'custom' !== $freq ) {
				if ( ! empty( $repeat_intervals ) ) {
					$repeat_intervals         = unserialize( $repeat_intervals );
					$event['event.is_series'] = 'true';
				}
			}
			$eventbrite_create_event = 'https://www.eventbriteapi.com/v3/organizations/' . $organizer_id . '/events/';
			$args                    = array(
				'headers' => array(
					'Content-Type'  => 'application/x-www-form-urlencoded\r\n',
					'Authorization' => 'Bearer ' . $token,
				),
				'body'    => $event,
			);
			if ( 'custom' === $freq && 'yes' === $repeat ) {
				// Do not create event, conitue on repeat events.
				$freq = 'custom';
			} else {
				$response = wp_remote_post( $eventbrite_create_event, $args );
				$json     = wp_remote_retrieve_body( $response );
				$details  = json_decode( $json );
			}
			if ( ! empty( $repeat_intervals ) && isset( $epmv['evcal_repeat'][0] ) && 'yes' === $epmv['evcal_repeat'][0] ) {
				// For Repeat Events.
				if ( 'daily' === $freq ) {
					$freq    = 'DAILY';
					$seconds = 86400;
				} elseif ( 'monthly' === $freq ) {
					$freq    = 'MONTHLY';
					$seconds = 86400 * 30;
				} elseif ( 'weekly' === $freq ) {
					$seconds = 86400 * 7;
				} elseif ( 'yearly' === $freq ) {
					$freq    = 'YEARLY';
					$seconds = 86400 * 365;
				} else {
					$freq = 'custom';
				}
				if ( is_array( $repeat_intervals ) && 'custom' !== $freq ) {
					$parent_event_id = $details->id;

					$revent['schedule.occurrence_duration'] = $seconds;
					$revent['schedule.recurrence_rule']     = 'DTSTART:' . $start_date . 'Z' . "\nRRULE:FREQ=" . $freq . ';COUNT=' . count( $repeat_intervals );
					$eventbrite_create_event                = 'https://www.eventbriteapi.com/v3/events/' . $parent_event_id . '/schedules/';
					$args                                   = array(
						'headers' => array(
							'Content-Type'  => 'application/x-www-form-urlencoded\r\n',
							'Authorization' => 'Bearer ' . $token,
						),
						'body'    => $revent,
					);

					$response = wp_remote_post( $eventbrite_create_event, $args );
					$json     = wp_remote_retrieve_body( $response );
					$details  = json_decode( $json );

				} elseif ( ! empty( $repeat_intervals ) && 'custom' === $freq ) {
					$repeat_intervals = unserialize( $repeat_intervals );
					foreach ( $repeat_intervals as $ri ) {
						$srow       = (int) $ri[0];
						$erow       = (int) $ri[1];
						$start_date = date( 'Y-m-d\TH:i:s', $srow );
						$end_date   = date( 'Y-m-d\TH:i:s', $erow );

						$event['event.start.utc'] = $start_date . 'Z';
						$event['event.end.utc']   = $end_date . 'Z';
						$eventbrite_create_event  = 'https://www.eventbriteapi.com/v3/organizations/' . $organizer_id . '/events/';
						$args                     = array(
							'headers' => array(
								'Content-Type'  => 'application/x-www-form-urlencoded\r\n',
								'Authorization' => 'Bearer ' . $token,
							),
							'body'    => $event,
						);

						$response = wp_remote_post( $eventbrite_create_event, $args );
						$json     = wp_remote_retrieve_body( $response );
						$details  = json_decode( $json );
					}
				}
			}
			update_post_meta( $event_id, 'mmt_eo_exim_export_to_eventbrite', 'on' );
		}
	}
	/**
	 * Upload image to Eventbrite
	 *
	 * @param string $image_src Image Source.
	 * @param string $token Eventbrite Token.
	 * @param string $mime_type MIME Type.
	 */
	public function mmt_eo_exim_upload_image( $image_src, $token, $mime_type ) {
		global $mmt_eo_exim;

		$upload_token_url = 'https://www.eventbriteapi.com/v3/media/upload/';
		$args             = array(
			'headers' => array(
				'Content-Type'  => 'application/x-www-form-urlencoded\r\n',
				'Authorization' => 'Bearer ' . $token,
			),
			'body'    => array(
				'type' => 'image-event-logo',
			),
		);

		$response = wp_remote_get( $upload_token_url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$json    = wp_remote_retrieve_body( $response );
		$details = json_decode( $json );
		if ( isset( $details->upload_token ) ) {
			$upload_token = $details->upload_token;
			$upload_url   = $details->upload_url;
			$data         = $details->upload_data;
			$fpn          = $details->file_parameter_name;

			$file      = fopen( $image_src, 'r' );
			$file_size = filesize( $image_src );
			$file_data = fread( $file, $file_size );

			$body = array(
				'AWSAccessKeyId' => $data->AWSAccessKeyId,
				'bucket'         => $data->bucket,
				'acl'            => $data->acl,
				'key'            => $data->key,
				'signature'      => $data->signature,
				'policy'         => $data->policy,
			);

			$boundary = wp_generate_password( 24, false );
			$payload  = '';
			foreach ( $body as $name => $value ) {
				$payload .= '--' . $boundary;
				$payload .= "\r\n";
				$payload .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
				$payload .= $value;
				$payload .= "\r\n";
			}
			$payload .= '--' . $boundary;
			$payload .= "\r\n";
			$payload .= 'Content-Disposition: form-data; name="file"; filename="' . basename( $image_src ) . '"' . "\r\n";
			$payload .= 'Content-Type: ' . $mime_type . "\r\n";
			$payload .= "\r\n";
			$payload .= $file_data;
			$payload .= "\r\n";
			$payload .= '--' . $boundary;
			$payload .= 'Content-Disposition: form-data; name="submit"' . "\r\n";
			$payload .= "\r\n";
			$payload .= "Upload\r\n";
			$payload .= '--' . $boundary . '--';

			$args = array(
				'sslverify' => 'false',
				'headers'   => array(
					'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
					'timeout'      => 20,
					'httpversion'  => '1.0',
					'blocking'     => true,
					'User-Agent'   => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17',
				),
				'body'      => $payload,
			);
			fclose( $file );
			$response = wp_remote_post( $upload_url, $args );
			if ( is_wp_error( $response ) ) {
				return $response;
			}
			$response_code = (int) wp_remote_retrieve_response_code( $response );
			if ( 204 === $response_code ) {
				$notify_url = 'https://www.eventbriteapi.com/v3/media/upload/';
				$args       = array(
					'headers' => array(
						'Content-Type'  => 'application/x-www-form-urlencoded\r\n',
						'Authorization' => 'Bearer ' . $token,
					),
					'body'    => array(
						'upload_token' => $upload_token,
					),
				);
				$response   = wp_remote_post( $notify_url, $args );
				if ( is_wp_error( $response ) ) {
					return $response;
				}
				$json         = wp_remote_retrieve_body( $response );
				$details      = json_decode( $json );
				$image_id     = $details->id;
				$image_url    = $details->url;
				$image        = array();
				$image['id']  = $image_id;
				$image['url'] = $image_url;
				return $image;
			}
		}
		return false;
	}
}
new MMT_EO_ExIm_Export_Eventbrite();
