<?php
/**
 * Eventon Imports functions
 *
 * @package mmt-eo-exim
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_ExIm_Functions {
	/**
	 * Fetch Eventbrite Organization List
	 */
	public function mmt_eo_exim_eb_org_list() {
		global $mmt_eo_exim;
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
		$token               = isset( $mmt_eo_exim_options['eb_private_token'] ) ? $mmt_eo_exim_options['eb_private_token'] : '';
		if ( empty( $token ) ) {
			return array();
		}
		$eventbrite_url = 'https://www.eventbriteapi.com/v3/users/me/organizations/';
		$args           = array(
			'headers' => array(
				'Content-Type'  => 'application/x-www-form-urlencoded\r\n',
				'Authorization' => 'Bearer ' . $token,
			),
		);
		$response       = wp_remote_get( $eventbrite_url, $args );
		$json           = wp_remote_retrieve_body( $response );
		$details        = json_decode( $json );
		$org_list       = array();
		if ( isset( $details->organizations ) ) {
			foreach ( $details->organizations as $organization ) {
				$org_list[ $organization->id ] = $organization->name;
			}
		}
		return $org_list;
	}
	/**
	 * Fetch and Import from EventBrite
	 *
	 * @param int $event_id EventBrite Event ID.
	 */
	public function mmt_fetch_and_import_eventbrite( $event_id ) {
		global $mmt_eo_exim;
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
		$token               = $mmt_eo_exim_options['eb_private_token'];
		$eventbrite_url      = 'https://www.eventbriteapi.com/v3/events/' . $event_id . '/?token=' . $token;
		$response            = wp_remote_get( $eventbrite_url );
		$json                = wp_remote_retrieve_body( $response );
		$details             = json_decode( $json );
		$event_arr           = array();
		if ( isset( $details->id ) && $event_id === $details->id ) {
			$event_arr['eb_id'] = $details->id;
			$event_arr['srow']  = strtotime( $details->start->local );
			$event_arr['erow']  = strtotime( $details->end->local );
			$event_arr['title'] = $details->name->text;
			if ( isset( $details->logo->url ) && ! empty( $details->logo->url ) ) {
				$event_arr['logo_url'] = $details->logo->original->url;
			}
			$event_arr['description'] = $details->description->text;
			if ( isset( $details->category_id ) && ! empty( $details->category_id ) ) {
				$category_url = 'https://www.eventbriteapi.com/v3/categories/' . $details->category_id . '/?token=' . $token;
				$response     = wp_remote_get( $category_url );
				$json         = wp_remote_retrieve_body( $response );
				$details_c    = json_decode( $json );
				if ( isset( $details_c->name ) ) {
					$event_arr['category'] = $details_c->name;
				}
			}
			if ( isset( $details->venue_id ) && ! empty( $details->venue_id ) ) {
				$venue_url = 'https://www.eventbriteapi.com/v3/venues/' . $details->venue_id . '/?token=' . $token;
				$response  = wp_remote_get( $venue_url );
				$json      = wp_remote_retrieve_body( $response );
				$details_v = json_decode( $json );
				if ( isset( $details_v->name ) ) {
					$event_arr['venue']['name']      = $details_v->name;
					$event_arr['venue']['address_1'] = $details_v->address->address_1;
					$event_arr['venue']['address_2'] = $details_v->address->address_2;
					$event_arr['venue']['city']      = $details_v->address->city;
					$event_arr['venue']['region']    = $details_v->address->region;
					$event_arr['venue']['country']   = $details_v->address->country;
					$event_arr['venue']['longitude'] = $details_v->address->longitude;
					$event_arr['venue']['latitude']  = $details_v->address->latitude;
				}
			}
			if ( isset( $details->organizer_id ) && ! empty( $details->organizer_id ) ) {
				$organizer_url = 'https://www.eventbriteapi.com/v3/organizers/' . $details->organizer_id . '/?token=' . $token;
				$response      = wp_remote_get( $organizer_url );
				$json          = wp_remote_retrieve_body( $response );
				$details_o     = json_decode( $json );

				$event_arr['organizer']['name']        = $details_o->name;
				$event_arr['organizer']['url']         = $details_o->url;
				$event_arr['organizer']['description'] = $details_o->description->text;
			}
			if ( isset( $details->is_series ) && true === $details->is_series ) {
				$event_arr['repeat']['series'] = true;
				if ( $details->is_series_parent ) {
					$event_arr['repeat']['parent'] = $event_id;
				} else {
					$event_arr['repeat']['parent'] = $details->series_id;
				}
			}
			$new_event_id = $this->mmt_eo_exim_create_event( $event_arr, 'EB' );
			return $new_event_id;
		}
		return false;
	}

	/**
	 * Create Event form Array
	 *
	 * @param array  $event_arr Event Array data.
	 * @param string $source Source Event.
	 */
	public function mmt_eo_exim_create_event( $event_arr, $source ) {
		$opt_draft = 'publish';
		$type      = 'ajde_events';
		$new_post  = array(
			'post_title'   => stripslashes( $event_arr['title'] ),
			'post_content' => ( ! empty( $event_arr['description'] ) ? wpautop( convert_chars( stripslashes( $event_arr['description'] ) ) ) : '' ),
			'post_status'  => $opt_draft,
			'post_type'    => $type,
			'post_name'    => $event_arr['title'],
			'post_author'  => $this->get_author_id(),
		);
		if ( $this->mmt_eo_exim_check_eventbrite_id_exist( $event_arr['eb_id'] ) ) {
			return false;
		}
		$new_event_id = wp_insert_post( $new_post );
		update_post_meta( $new_event_id, 'mmt_eo_exim_eventbrite_id', $event_arr['eb_id'] );
		if ( isset( $event_arr['venue'] ) ) {
			$this->new_event_location( $event_arr['venue'], $new_event_id );
		}
		if ( isset( $event_arr['organizer'] ) ) {
			$this->new_event_organizer( $event_arr['organizer'], $new_event_id );
		}
		update_post_meta( $new_event_id, 'evcal_srow', $event_arr['srow'] );
		update_post_meta( $new_event_id, 'evcal_erow', $event_arr['erow'] );
		if ( isset( $event_arr['logo_url'] ) ) {
			$image = $this->mmt_eo_exim_upload_event_image( $event_arr['logo_url'], $event_arr['title'], $new_event_id );
			if ( $image && is_array( $image ) ) {
				$thumbnail = set_post_thumbnail( $new_event_id, $image[0] );
			}
		}
		if ( isset( $event_arr['category'] ) ) {
			for ( $x = 1; $x <= 1; $x++ ) {
				$ab  = ( 1 === $x ) ? '' : '_' . $x;
				$tax = 'event_type' . $ab;
				$this->new_event_category( $event_arr['category'], $new_event_id, $tax );
			}
		}
		if ( isset( $event_arr['repeat']['series'] ) && true === $event_arr['repeat']['series'] ) {
			$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
		}
		return $new_event_id;
	}

	/**
	 * Repeat Event
	 *
	 * @param int   $new_event_id New Event ID.
	 * @param array $repeat Repeat Data.
	 * @param int   $srow Start Date.
	 * @param int   $erow End Date.
	 */
	public function mmt_eo_exim_create_repeating_data( $new_event_id, $repeat, $srow, $erow ) {
		$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );

		$token      = $mmt_eo_exim_options['eb_private_token'];
		$series_url = 'https://www.eventbriteapi.com/v3/series/' . $repeat['parent'] . '/events/?token=' . $token;
		$response   = wp_remote_get( $series_url );
		$json       = wp_remote_retrieve_body( $response );
		$details    = json_decode( $json );
		if ( isset( $details->events ) && is_array( $details->events ) ) {
			$i = 0;
			update_post_meta( $new_event_id, 'evcal_repeat', 'yes' );
			update_post_meta( $new_event_id, 'evcal_rep_freq', 'custom' );
			$repeat = array();
			foreach ( $details->events as $event ) {
				$repeat[ $i ][0] = strtotime( $event->start->local );
				$repeat[ $i ][1] = strtotime( $event->end->local );
				$i++;
			}
			update_post_meta( $new_event_id, 'repeat_intervals', $repeat );
		}
	}
	/**
	 * Insert and save Event Category
	 *
	 * @param string $category Category name..
	 * @param int    $new_event_id New Event ID.
	 * @param string $tt Taxonomy Name.
	 */
	public function new_event_category( $category, $new_event_id, $tt ) {
		$base_name = esc_attr( stripslashes( $category ) );
		$term      = term_exists( $base_name, $tt );
		if ( 0 !== $term && null !== $term ) {
			wp_set_object_terms( $new_event_id, $base_name, $tt );
		} else {
			$slug         = str_replace( ' ', '-', $base_name );
			$new_taxonomy = wp_insert_term(
				$base_name,
				$tt,
				array(
					'slug' => $slug,
				)
			);
			if ( ! is_wp_error( $new_taxonomy ) ) {
				$term_id   = (int) $new_taxonomy['term_id'];
				$term_meta = array();
				wp_set_object_terms( $new_event_id, $term_id, $tt, true );
			}
		}
	}
	/**
	 * Upload Image for Event
	 *
	 * @param string $url Image URL.
	 * @param string $event_name Event Name.
	 * @param int    $new_event_id Event ID.
	 */
	public function mmt_eo_exim_upload_event_image( $url, $event_name, $new_event_id ) {
		if ( empty( $url ) ) {
			return false;
		}
		$no_extension = false;
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$event_image = urldecode( $url );
		$image       = explode( '?s=', $event_image );
		$image_url   = esc_url( urldecode( str_replace( 'https://img.evbuc.com/', '', $image[0] ) ) );
		$tmp         = download_url( $image_url );

		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );
		if ( ! $matches ) {
			if ( strpos( $image_url, 'https://cdn.evbuc.com' ) === 0 || strpos( $image_url, 'https://img.evbuc.com' ) === 0 ) {
				$no_extension = true;
			} else {
				return false;
			}
		}
		$file_array['name']     = $new_event_id . '_eventbrite_image';
		$file_array['tmp_name'] = $tmp;
		if ( true === $no_extension ) {
			$file_array['name'] .= '.jpg';
		} else {
			$file_array['name'] .= '_' . basename( $matches[0] );
		}
		if ( is_wp_error( $tmp ) ) {
			unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
			return;
		}

		$desc = "Featured image for '$event_name'";
		$id   = media_handle_sideload( $file_array, $new_event_id, $desc );
		if ( is_wp_error( $id ) ) {
			unlink( $file_array['tmp_name'] );
			return false;
		}

		$src = wp_get_attachment_url( $id );
		return array(
			0 => $id,
			1 => $src,
		);

	}
	/**
	 * Insert and save Event Organizer
	 *
	 * @param array $organizer Organizer Array.
	 * @param int   $new_event_id New Event ID.
	 */
	public function new_event_organizer( $organizer, $new_event_id ) {
		$base_name = esc_attr( stripslashes( $organizer['name'] ) );
		$term      = term_exists( $base_name, 'event_organizer' );
		if ( 0 !== $term && null !== $term ) {
			wp_set_object_terms( $new_event_id, $base_name, 'event_organizer' );
		} else {
			$slug         = str_replace( ' ', '-', $base_name );
			$new_taxonomy = wp_insert_term(
				$base_name,
				'event_organizer',
				array(
					'slug' => $slug,
				)
			);
			if ( ! is_wp_error( $new_taxonomy ) ) {
				$term_id   = (int) $new_taxonomy['term_id'];
				$term_meta = array();

				$term_meta['evcal_org_exlink'] = $organizer['url'];
				$term_meta['description']      = $organizer['description'];
				$this->mmt_eo_exim_save_term_meta( 'event_organizer', $term_id, $term_meta );
				wp_set_object_terms( $new_event_id, $term_id, 'event_organizer', true );
			}
		}
	}
	/**
	 * Insert and save Event Location
	 *
	 * @param array $venue Location Array.
	 * @param int   $new_event_id New Event ID.
	 */
	public function new_event_location( $venue, $new_event_id ) {
		$base_name = esc_attr( stripslashes( $venue['name'] ) );
		$term      = term_exists( $base_name, 'event_location' );
		if ( 0 !== $term && null !== $term ) {
			wp_set_object_terms( $new_event_id, $base_name, 'event_location' );
		} else {
			$slug         = str_replace( ' ', '-', $base_name );
			$new_taxonomy = wp_insert_term(
				$base_name,
				'event_location',
				array(
					'slug' => $slug,
				)
			);
			if ( ! is_wp_error( $new_taxonomy ) ) {
				$term_id   = (int) $new_taxonomy['term_id'];
				$term_meta = array();

				$term_meta['location_address'] = $venue['address_1'] . ', ' . $venue['city'] . ', ' . $venue['region'] . ', ' . $venue['country'];
				$term_meta['location_lon']     = $venue['longitude'];
				$term_meta['location_lat']     = $venue['latitude'];
				$this->mmt_eo_exim_save_term_meta( 'event_location', $term_id, $term_meta );
				wp_set_object_terms( $new_event_id, $term_id, 'event_location', true );
			}
		}
	}
	/**
	 * Save TermMeta
	 *
	 * @param string $term_name Term Name.
	 * @param int    $term_id Term ID.
	 * @param array  $term_meta Term Meta.
	 */
	public function mmt_eo_exim_save_term_meta( $term_name, $term_id, $term_meta ) {
		if ( empty( $term_id ) ) {
			return false;
		}
		if ( ! is_array( $term_meta ) ) {
			return false;
		}
		$term_metas = get_option( 'evo_tax_meta' );

		if ( ! empty( $term_metas ) && is_array( $term_metas ) && ! empty( $term_metas[ $term_name ][ $term_id ] ) ) {
			$oldvals = $term_metas[ $term_name ][ $termid ];
			$newvals = array_merge( $oldvals, $term_meta );
			$newvals = array_filter( $newvals );

			$term_metas[ $term_name ][ $term_id ] = $newvals;
		} else {
			$term_metas[ $term_name ][ $term_id ] = $term_meta;
		}
		return update_option( 'evo_tax_meta', $term_metas );
	}
	/**
	 * Get Current Author ID
	 */
	public function get_author_id() {
		$current_user = wp_get_current_user();
		return ( ( $current_user instanceof WP_User ) ) ? $current_user->ID : 0;
	}

	/**
	 * Check if Eventbrite Event already listed
	 *
	 * @param integer $eventbrite_id Eventbrite ID.
	 */
	public function mmt_eo_exim_check_eventbrite_id_exist( $eventbrite_id ) {
		$args  = array(
			'numberposts' => -1,
			'post_type'   => 'ajde_events',
			'meta_query'  => array(
				array(
					'key'     => 'mmt_eo_exim_eventbrite_id',
					'value'   => $eventbrite_id,
					'compare' => '=',
				),
			),
		);
		$query = new WP_Query( $args );
		if ( $query->post_count > 0 ) {
			return true;
		}
		return false;
	}

	/**
	 * Get Tax Terms
	 *
	 * @param Object $tax Tax Object.
	 */
	public function get_tax_terms( $tax ) {
		$terms = get_terms(
			$tax,
			array(
				'orderby'    => 'name',
				'hide_empty' => false,
			)
		);

		return ! empty( $terms ) ? $terms : false;
	}
	/**
	 * Custom Excerpt
	 *
	 * @param string $text Text.
	 * @param int    $limit Limit number.
	 */
	public function mmt_eo_exim_excerpt( $text, $limit ) {
		$excerpt = wp_trim_words( $text, $limit );
		return $excerpt;
	}

}
