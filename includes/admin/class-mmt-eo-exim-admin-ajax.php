<?php

/**
 * ExIm Imports Admin AJAX functions
 *
 * @package mmt-eo-exim
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_ExIm_Admin_Ajax
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $ajax_events = array(
            'mmt_eo_exim_save_eb_settings'       => 'mmt_eo_exim_save_eb_settings',
            'mmt_eo_exim_fetch_by_event_id_eb'   => 'mmt_eo_exim_fetch_by_event_id_eb',
            'mmt_eo_exim_import_single_event_eb' => 'mmt_eo_exim_import_single_event_eb',
            'mmt_eo_exim_save_eb_display'        => 'mmt_eo_exim_save_eb_display',
        );
        foreach ( $ajax_events as $ajax_event => $class ) {
            add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
            add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
        }
    }
    
    /**
     * Save Display Settings (Three)
     */
    public function mmt_eo_exim_save_eb_display()
    {
        $mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_save_eb_display' !== $_POST['action'] ) {
            return;
        }
        $enable_eventbrite_widget = ( isset( $_POST['enable_eventbrite_widget'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_eventbrite_widget'] ) ) : '' );
        $eb_display_organizer = ( isset( $_POST['eb_display_organizer'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_display_organizer'] ) ) : '' );
        $mmt_eo_exim_options['enable_eventbrite_widget'] = $enable_eventbrite_widget;
        $mmt_eo_exim_options['eb_display_organizer'] = $eb_display_organizer;
        update_option( 'mmt_eo_exim_options', $mmt_eo_exim_options );
        echo  wp_json_encode( array(
            'status'  => 'good',
            'message' => __( 'Options saved successfully', 'mmt-eo-exim' ),
        ) ) ;
        exit;
    }
    
    /**
     * Save Eventbrite Settings (Main Settings)
     */
    public function mmt_eo_exim_save_eb_settings()
    {
        $mmt_eo_exim_pro_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_save_eb_settings' !== $_POST['action'] ) {
            return;
        }
        $eb_private_token = ( isset( $_POST['eb_private_token'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_private_token'] ) ) : '' );
        $eb_imports_assign_category = ( isset( $_POST['eb_imports_assign_category'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_imports_assign_category'] ) ) : '' );
        $eb_imports_category_1 = ( isset( $_POST['eb_imports_category_1'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_imports_category_1'] ) ) : '' );
        $eb_imports_category_2 = ( isset( $_POST['eb_imports_category_2'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_imports_category_2'] ) ) : '' );
        $eb_imports_enable_repeat = ( isset( $_POST['eb_imports_enable_repeat'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_imports_enable_repeat'] ) ) : '' );
        $eb_export_organizer = ( isset( $_POST['eb_export_organizer'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_export_organizer'] ) ) : '' );
        $eb_exports_enable_custom_repeat = ( isset( $_POST['eb_exports_enable_custom_repeat'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_exports_enable_custom_repeat'] ) ) : '' );
        $eb_default_currency = ( isset( $_POST['eb_default_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['eb_default_currency'] ) ) : '' );
        $mmt_eo_exim_pro_options['eb_private_token'] = $eb_private_token;
        $mmt_eo_exim_pro_options['eb_imports_assign_category'] = $eb_imports_assign_category;
        $mmt_eo_exim_pro_options['eb_imports_category_1'] = $eb_imports_category_1;
        $mmt_eo_exim_pro_options['eb_imports_category_2'] = $eb_imports_category_2;
        $mmt_eo_exim_pro_options['eb_imports_enable_repeat'] = $eb_imports_enable_repeat;
        $mmt_eo_exim_pro_options['eb_export_organizer'] = $eb_export_organizer;
        $mmt_eo_exim_pro_options['eb_exports_enable_custom_repeat'] = $eb_exports_enable_custom_repeat;
        $mmt_eo_exim_pro_options['eb_default_currency'] = $eb_default_currency;
        update_option( 'mmt_eo_exim_options', $mmt_eo_exim_pro_options );
        echo  wp_json_encode( array(
            'status'  => 'good',
            'message' => esc_html__( 'Options saved successfully', 'mmt-eo-exim' ),
        ) ) ;
        exit;
    }
    
    /**
     * Search Organizer ID - ( One )
     */
    public function mmt_eo_exim_search_org_ven_id()
    {
        global  $mmt_eo_exim_pro ;
        $mmt_eo_exim_pro_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_search_org_ven_id' !== $_POST['action'] ) {
            return;
        }
        if ( !isset( $_POST['eid'] ) && empty($_POST['eid']) ) {
            return;
        }
        
        if ( !isset( $mmt_eo_exim_pro_options['eb_private_token'] ) || empty($mmt_eo_exim_pro_options['eb_private_token']) ) {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'html'   => esc_html__( 'Eventbrite private token not found.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $token = $mmt_eo_exim_pro_options['eb_private_token'];
        $eventbrite_token = 'https://www.eventbriteapi.com/v3/users/me/?token=' . $token;
        $response = wp_remote_get( $eventbrite_token );
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        
        if ( isset( $details->status_code ) && 401 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'html'   => esc_html__( 'The private token you provided was invalid.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $event_id = ( isset( $_POST['eid'] ) ? sanitize_text_field( wp_unslash( $_POST['eid'] ) ) : 0 );
        $org_ven = ( isset( $_POST['org_ven'] ) ? sanitize_text_field( wp_unslash( $_POST['org_ven'] ) ) : 'org' );
        $eventbrite_url = 'https://www.eventbriteapi.com/v3/events/' . $event_id . '/?token=' . $token;
        $response = wp_remote_get( $eventbrite_url );
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        
        if ( isset( $details->status_code ) && 404 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status'   => 'bad',
                'event_id' => $event_id,
                'html'     => esc_html__( 'Provided event does not exist.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        if ( isset( $details->id ) && $event_id === $details->id ) {
            
            if ( 'org' === $org_ven ) {
                echo  wp_json_encode( array(
                    'status'          => 'good',
                    'organization_id' => $details->organization_id,
                    'html'            => 'Organization ID : ' . $details->organization_id,
                ) ) ;
                exit;
            } else {
                echo  wp_json_encode( array(
                    'status'   => 'good',
                    'venue_id' => $details->venue_id,
                    'html'     => 'Venue ID : ' . $details->venue_id,
                ) ) ;
                exit;
            }
        
        }
    }
    
    /**
     * Fetch Event by Event ID ( Two )
     */
    public function mmt_eo_exim_fetch_by_event_id_eb()
    {
        global  $mmt_eo_exim_pro ;
        $mmt_eo_exim_pro_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_fetch_by_event_id_eb' !== $_POST['action'] ) {
            return;
        }
        if ( !isset( $_POST['event_id'] ) && empty($_POST['event_id']) ) {
            return;
        }
        
        if ( !isset( $mmt_eo_exim_pro_options['eb_private_token'] ) || empty($mmt_eo_exim_pro_options['eb_private_token']) ) {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Eventbrite private token not found.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $token = $mmt_eo_exim_pro_options['eb_private_token'];
        $eventbrite_token = 'https://www.eventbriteapi.com/v3/users/me/?token=' . $token;
        $response = wp_remote_get( $eventbrite_token );
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        
        if ( isset( $details->status_code ) && 401 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'The private token you provided was invalid.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $event_id = ( isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 0 );
        $current_list = ( isset( $_POST['current_list'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['current_list'] ) ) ) : array() );
        
        if ( in_array( $event_id, $current_list, true ) ) {
            echo  wp_json_encode( array(
                'status'   => 'good',
                'msg'      => esc_html__( 'Event fetched successfully.', 'mmt-eo-exim' ),
                'info'     => esc_html__( 'Event already in list.', 'mmt-eo-exim' ),
                'event_id' => $details->id,
                'elist'    => implode( ',', $current_list ),
                'html'     => '',
            ) ) ;
            exit;
        }
        
        $current_list[] = $event_id;
        $eventbrite_url = 'https://www.eventbriteapi.com/v3/events/' . $event_id . '/?token=' . $token;
        $response = wp_remote_get( $eventbrite_url );
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        
        if ( isset( $details->status_code ) && 404 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status'   => 'bad',
                'event_id' => $event_id,
                'msg'      => esc_html__( 'Provided event does not exist.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        
        if ( isset( $details->status_code ) && 403 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status'   => 'bad',
                'event_id' => $event_id,
                'msg'      => esc_html__( 'You do not have permission to access the resource you requested.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        
        if ( isset( $details->id ) && $event_id === $details->id ) {
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            $srow = strtotime( $details->start->local );
            $erow = strtotime( $details->end->local );
            ob_start();
            ?>
			<tr data-eb_eid="<?php 
            echo  esc_attr( $details->id ) ;
            ?>" data-status="<?php 
            echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $details->id ) ? 'imported' : '' ) ;
            ?>">
				<td>
					<?php 
            
            if ( isset( $details->logo->url ) && !empty($details->logo->url) ) {
                ?>
						<img src="<?php 
                echo  esc_attr( $details->logo->url ) ;
                ?>" height=100 width=100/>
						<?php 
            }
            
            ?>
				</td>
				<td>
					<?php 
            echo  esc_html( $details->name->text ) ;
            ?>
				</td>
				<td>
					<?php 
            echo  esc_html( gmdate( $date_format, $srow ) . ' ' . gmdate( $time_format, $srow ) ) ;
            ?>
				</td>
				<td>
					<?php 
            echo  esc_html( gmdate( $date_format, $erow ) . ' ' . gmdate( $time_format, $erow ) ) ;
            ?>
				</td>
				<td>
					<?php 
            echo  esc_html( $details->summary ) ;
            ?>
				</td>
				<td class="status">
					<?php 
            echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $details->id ) ? 'Imported' : '-' ) ;
            ?>
				</td>
			</tr>
			<?php 
            echo  wp_json_encode( array(
                'status'   => 'good',
                'msg'      => esc_html__( 'Event fetched successfully.', 'mmt-eo-exim' ),
                'info'     => esc_html__( 'Fetched 1 event successfully.', 'mmt-eo-exim' ),
                'event_id' => $details->id,
                'elist'    => implode( ',', $current_list ),
                'html'     => ob_get_clean(),
            ) ) ;
            exit;
        }
    
    }
    
    /**
     * Fetch Event by Organization ID. ( Three )
     */
    public function mmt_eo_exim_fetch_by_organizer_id_eb()
    {
        global  $mmt_eo_exim_pro ;
        $mmt_eo_exim_pro_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_fetch_by_organizer_id_eb' !== $_POST['action'] ) {
            return;
        }
        
        if ( !isset( $mmt_eo_exim_pro_options['eb_private_token'] ) || empty($mmt_eo_exim_pro_options['eb_private_token']) ) {
            echo  wp_json_encode( array(
                'status'   => 'bad',
                'event_id' => $event_id,
                'msg'      => esc_html__( 'Eventbrite private token not found.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $date_format = get_option( 'date_format' );
        $organizer_id = ( isset( $_POST['organizer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['organizer_id'] ) ) : 0 );
        $start_date = gmdate( 'Y-m-d', strtotime( ( isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : time() ) ) );
        $end_date = gmdate( 'Y-m-d', strtotime( ( isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : time() ) ) );
        $start_date = date_parse_from_format( $date_format, sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) );
        $end_date = date_parse_from_format( $date_format, sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) );
        $start_date = $start_date['year'] . '/' . $start_date['month'] . '/' . $start_date['day'];
        $end_date = $end_date['year'] . '/' . $end_date['month'] . '/' . $end_date['day'];
        $start_date = gmdate( 'Y-m-d', strtotime( $start_date ) );
        $end_date = gmdate( 'Y-m-d', strtotime( $end_date ) );
        $token = $mmt_eo_exim_pro_options['eb_private_token'];
        $eventbrite_url = 'https://www.eventbriteapi.com/v3/organizations/' . $organizer_id . '/events/';
        $args = array(
            'headers' => array(
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $token,
        ),
            'body'    => array(
            'start_date.range_start' => $start_date,
            'start_date.range_end'   => $end_date,
        ),
        );
        $response = wp_remote_get( $eventbrite_url, $args );
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        
        if ( isset( $details->status_code ) && 404 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status'       => 'bad',
                'organizer_id' => $organizer_id,
                'msg'          => esc_html__( 'Provided Organizer ID does not exist.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $current_list = ( isset( $_POST['current_list'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['current_list'] ) ) ) : array() );
        
        if ( isset( $details->events ) && count( $details->events ) > 0 ) {
            ob_start();
            $count = 0;
            foreach ( $details->events as $event ) {
                
                if ( in_array( $event->id, $current_list, true ) ) {
                    $count = $count;
                } else {
                    ++$count;
                    $current_list[] = $event->id;
                    $srow = strtotime( $event->start->local );
                    $erow = strtotime( $event->end->local );
                    ?>
					<tr data-eb_eid="<?php 
                    echo  esc_attr( $event->id ) ;
                    ?>" data-status="<?php 
                    echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $event->id ) ? 'imported' : '' ) ;
                    ?>">
						<td>
							<?php 
                    
                    if ( isset( $event->logo->url ) && (!empty($event->logo->url) && 'https://image.com' !== $event->logo->url) ) {
                        ?>
								<img src="<?php 
                        echo  esc_attr( $event->logo->url ) ;
                        ?>" height=100 width=100/>
								<?php 
                    }
                    
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( $event->name->text ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( gmdate( $date_format, $srow ) . ' ' . gmdate( $time_format, $srow ) ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( gmdate( $date_format, $erow ) . ' ' . gmdate( $time_format, $erow ) ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( $event->summary ) ;
                    ?>
						</td>
						<td class="status">
							<?php 
                    echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $event->id ) ? 'Imported' : '-' ) ;
                    ?>
						</td>
					</tr>
					<?php 
                }
            
            }
            echo  wp_json_encode( array(
                'status' => 'good',
                'msg'    => esc_html__( 'Event fetched successfully.', 'mmt-eo-exim' ),
                'info'   => 'Fetched ' . $count . ' event(s) successfully.',
                'html'   => ob_get_clean(),
                'elist'  => implode( ',', $current_list ),
            ) ) ;
            exit;
        } else {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Events not found.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
    
    }
    
    /**
     * Fetch Event by Venue ID. ( Four )
     */
    public function mmt_eo_exim_fetch_by_venue_id_eb()
    {
        global  $mmt_eo_exim_pro ;
        $mmt_eo_exim_pro_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_fetch_by_venue_id_eb' !== $_POST['action'] ) {
            return;
        }
        
        if ( !isset( $mmt_eo_exim_pro_options['eb_private_token'] ) || empty($mmt_eo_exim_pro_options['eb_private_token']) ) {
            echo  wp_json_encode( array(
                'status'   => 'bad',
                'event_id' => $event_id,
                'msg'      => esc_html__( 'Eventbrite private token not found.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $venue_id = ( isset( $_POST['venue_id'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_id'] ) ) : 0 );
        $date_format = get_option( 'date_format' );
        $start_date = gmdate( 'Y-m-d', strtotime( ( isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : time() ) ) );
        $end_date = gmdate( 'Y-m-d', strtotime( ( isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : time() ) ) );
        $start_date = date_parse_from_format( $date_format, sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) );
        $end_date = date_parse_from_format( $date_format, sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) );
        $start_date = $start_date['year'] . '/' . $start_date['month'] . '/' . $start_date['day'];
        $end_date = $end_date['year'] . '/' . $end_date['month'] . '/' . $end_date['day'];
        $start_date = gmdate( 'Y-m-d', strtotime( $start_date ) );
        $end_date = gmdate( 'Y-m-d', strtotime( $end_date ) );
        $token = $mmt_eo_exim_pro_options['eb_private_token'];
        $eventbrite_url = 'https://www.eventbriteapi.com/v3/venues/' . $venue_id . '/events/';
        $args = array(
            'headers' => array(
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $token,
        ),
            'body'    => array(
            'start_date.range_start' => $start_date . 'T00:00:00',
            'start_date.range_end'   => $end_date . 'T00:00:00',
        ),
        );
        $response = wp_remote_get( $eventbrite_url, $args );
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        
        if ( isset( $details->status_code ) && 404 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status'   => 'bad',
                'venue_id' => $venue_id,
                'msg'      => esc_html__( 'Provided Venue ID does not exist.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $current_list = ( isset( $_POST['current_list'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['current_list'] ) ) ) : array() );
        
        if ( isset( $details->events ) && count( $details->events ) > 0 ) {
            ob_start();
            $count = 0;
            foreach ( $details->events as $event ) {
                
                if ( in_array( $event->id, $current_list, true ) ) {
                    $count = $count;
                } else {
                    $srow = strtotime( $event->start->local );
                    $erow = strtotime( $event->end->local );
                    ?>
					<tr data-eb_eid="<?php 
                    echo  esc_attr( $event->id ) ;
                    ?>" data-status="<?php 
                    echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $event->id ) ? 'imported' : '' ) ;
                    ?>">
						<td>
							<?php 
                    
                    if ( isset( $event->logo->url ) && (!empty($event->logo->url) && 'https://image.com' !== $event->logo->url) ) {
                        ?>
								<img src="<?php 
                        echo  esc_attr( $event->logo->url ) ;
                        ?>" height=100 width=100/>
								<?php 
                    }
                    
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( $event->name->text ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( gmdate( $date_format, $srow ) . ' ' . gmdate( $time_format, $srow ) ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( gmdate( $date_format, $erow ) . ' ' . gmdate( $time_format, $erow ) ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( $event->summary ) ;
                    ?>
						</td>
						<td class="status">
							<?php 
                    echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $event->id ) ? 'Imported' : '-' ) ;
                    ?>
						</td>
					</tr>
					<?php 
                }
            
            }
            echo  wp_json_encode( array(
                'status'     => 'good',
                'msg'        => esc_html__( 'Event fetched successfully.', 'mmt-eo-exim' ),
                'info'       => 'Fetched ' . $count . ' event(s) successfully.',
                'venue_id'   => $venue_id,
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'html'       => ob_get_clean(),
                'elist'      => implode( ',', $current_list ),
            ) ) ;
            exit;
        } else {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Events not found.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
    
    }
    
    /**
     * Fetch your Own Events ( Five )
     */
    public function mmt_eo_exim_fetch_by_your_event_eb()
    {
        global  $mmt_eo_exim_pro ;
        $mmt_eo_exim_pro_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_fetch_by_your_event_eb' !== $_POST['action'] ) {
            return;
        }
        $token = $mmt_eo_exim_pro_options['eb_private_token'];
        $eventbrite_token = 'https://www.eventbriteapi.com/v3/users/me/?token=' . $token;
        $response = wp_remote_get( $eventbrite_token );
        $json = wp_remote_retrieve_body( $response );
        $details = json_decode( $json );
        
        if ( isset( $details->status_code ) && 401 === $details->status_code ) {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'The private token you provided was invalid.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        
        if ( isset( $details->id ) ) {
            $user_id = $details->id;
            $eventbrite_url = 'https://www.eventbriteapi.com/v3/events/';
            $args = array(
                'headers' => array(
                'Content-Type'  => 'application/x-www-form-urlencoded\\r\\n',
                'Authorization' => 'Bearer ' . $token,
            ),
                'body'    => array(
                'user.id' => $user_id,
            ),
            );
            $response = wp_remote_get( $eventbrite_url, $args );
            $json = wp_remote_retrieve_body( $response );
            $details = json_decode( $json );
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            
            if ( isset( $details->events ) && count( $details->events ) > 0 ) {
                ob_start();
                foreach ( $details->events as $event ) {
                    $srow = strtotime( $event->start->local );
                    $erow = strtotime( $event->end->local );
                    ?>
					<tr data-eb_eid="<?php 
                    echo  esc_attr( $event->id ) ;
                    ?>"  data-status="<?php 
                    echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $event->id ) ? 'imported' : '' ) ;
                    ?>">>
						<td>
							<?php 
                    
                    if ( isset( $event->logo->url ) && (!empty($event->logo->url) && 'https://image.com' !== $event->logo->url) ) {
                        ?>
								<img src="<?php 
                        echo  esc_attr( $event->logo->url ) ;
                        ?>" height=100 width=100/>
								<?php 
                    }
                    
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( $event->name->text ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( gmdate( $date_format, $srow ) . ' ' . gmdate( $time_format, $srow ) ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( gmdate( $date_format, $erow ) . ' ' . gmdate( $time_format, $erow ) ) ;
                    ?>
						</td>
						<td>
							<?php 
                    echo  esc_html( $event->summary ) ;
                    ?>
						</td>
						<td class="status">
							<?php 
                    echo  ( $mmt_eo_exim_pro->fn->mmt_eo_exim_check_eventbrite_id_exist( $event->id ) ? 'Imported' : '-' ) ;
                    ?>
						</td>
					</tr>
					<?php 
                }
                echo  wp_json_encode( array(
                    'status'  => 'good',
                    'msg'     => esc_html__( 'Event fetched successfully.', 'mmt-eo-exim' ),
                    'info'    => 'Fetched ' . count( $details->events ) . ' event(s) successfully.',
                    'user_id' => $user_id,
                    'html'    => ob_get_clean(),
                ) ) ;
                exit;
            } else {
                echo  wp_json_encode( array(
                    'status' => 'bad',
                    'msg'    => esc_html__( 'Events not found.', 'mmt-eo-exim' ),
                ) ) ;
                exit;
            }
        
        } else {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Something went wrong while connecting. Please try again', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
    
    }
    
    /**
     * Import Single Eventbrite Event ( Six )
     */
    public function mmt_eo_exim_import_single_event_eb()
    {
        global  $mmt_eo_exim_pro ;
        $mmt_eo_exim_pro_options = get_option( 'mmt_eo_exim_options' );
        $res = check_ajax_referer( 'mmt_eo_exim_security_key', 'security' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_exim_import_single_event_eb' !== $_POST['action'] ) {
            return;
        }
        if ( !isset( $_POST['event_id'] ) && empty($_POST['event_id']) ) {
            return;
        }
        
        if ( !isset( $mmt_eo_exim_pro_options['eb_private_token'] ) || empty($mmt_eo_exim_pro_options['eb_private_token']) ) {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Import Error (No Token).', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
        
        $event_id = ( isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : 0 );
        $response = $mmt_eo_exim_pro->fn->mmt_fetch_and_import_eventbrite( $event_id );
        
        if ( $response ) {
            echo  wp_json_encode( array(
                'status' => 'good',
                'msg'    => esc_html__( 'Event(s) imported successfully.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        } else {
            echo  wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Import Error.', 'mmt-eo-exim' ),
            ) ) ;
            exit;
        }
    
    }

}
new MMT_EO_ExIm_Admin_Ajax();