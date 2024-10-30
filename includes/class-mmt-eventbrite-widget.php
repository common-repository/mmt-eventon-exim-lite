<?php
/**
 * Eventon ExIm - Eventbrite Widget
 *
 * @author MoMo Themes
 * @package mmt-eo-exim
 * @since v1.1
 */
class MMT_Eventbrite_Widget extends WP_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_id       = 'mmt-eventbrite-widget';
		$this->widget_name     = __( 'MMT Eventbrite Widget', 'mmt-eo-exim' );
		$this->option_name     = 'widget_mmt_eventbrite';
		$this->widget_options  = array(
			'classname'                   => $this->option_name,
			'customize_selective_refresh' => false,
			'description'                 => __( 'Widget to display Eventbrite upcoming events', 'mmt-eo-exim' ),
		);
		$this->control_options = array(
			'width'  => 400,
			'height' => 350,
		);
		parent::__construct(
			$this->widget_id,
			$this->widget_name,
			$this->widget_options,
			$this->control_options
		);
	}
	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		wp_enqueue_style( 'mmt_eo_exim_style' );
		$before_widget = ! empty( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = ! empty( $args['after_widget'] ) ? $args['after_widget'] : '';
		$before_title  = ! empty( $args['before_title'] ) ? $args['before_title'] : '';
		$after_title   = ! empty( $args['after_title'] ) ? $args['after_title'] : '';

		$title                 = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Upcoming Events', 'mmt-eo-exim' );
		$no_of_events          = ! empty( $instance['no_of_events'] ) ? $instance['no_of_events'] : 5;
		$open_event_new_window = ! empty( $instance['open_event_new_window'] ) ? $instance['open_event_new_window'] : '';
		$title                 = apply_filters( 'widget_title', $title );

		$target = '';
		if ( 'on' === $open_event_new_window ) {
			$target = 'target="_blank"';
		}

		$query = array(
			'type' => 'upcoming',
			'noe'  => $no_of_events,
		);
		$eb_api = new MMT_Eventbrite_API();
		$events = $eb_api->mmt_get_organizations_events();
		ob_start();
		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		?>
		<div class="mmt-widget-events">
		<div class="mmt-eb-events-list">
		<?php
		$i = 1;
		if ( is_array( $events ) && count( $events ) > 0 ) {
			foreach ( $events as $event ) {
				if ( 'draft' === $event->status ) {
					continue;
				}
				if ( $i > $no_of_events ) {
					break;
				}
				$i++;
				$time_zone   = $event->start->timezone;
				$start_local = $event->start->local;
				$start_utc   = $event->start->utc;
				$unix_start  = strtotime( $start_local );
				$day         = date( 'd', $unix_start );
				$month       = date( 'M', $unix_start );
				$year        = date( 'Y', $unix_start );
				$sdate       = date( 'd-m-Y', $unix_start );
				$time        = date( 'H:i:s', $unix_start );
				$venue       = ( isset( $event->venue->name ) ) ? $event->venue->name : '';
				$title       = ( isset( $event->name->text ) ) ? $event->name->text : '';
				$image       = ( isset( $event->logo->url ) ) ? $event->logo->url : '';
				$url         = ( isset( $event->url ) ) ? $event->url : '';
				?>
				<a href="<?php echo esc_attr( $url ); ?>" class="single-event-link" <?php echo esc_attr( $target ); ?>>
					<div class="mmt-eb-single-event">
						<time datetime="<?php echo esc_attr( $sdate ); ?>">
							<span class="day"><?php echo esc_html( $day ); ?></span>
							<span class="month"><?php echo esc_html( $month ); ?></span>
							<span class="year"><?php echo esc_html( $year ); ?></span>
							<span class="time"><?php echo esc_html( $time ); ?></span>
						</time>
						<?php if ( ! empty( $image ) ) { ?>
							<span class="ft_image" style="background-image:url('<?php echo esc_url( $image ); ?>');"></span>
						<?php } ?>
						<div class="information">
							<span class="title"><?php echo esc_html( $title ); ?></span>
							<?php if ( ! empty( $venue ) ) { ?>
								<span class="venue"><?php echo esc_html( $venue ); ?></span>
							<?php } ?>
						</div>
					</div>
				</a>
				<?php
			}
		} else {
			esc_html_e( 'No Events', 'mmt-eo-exim' );
		}
		?>
		</div>
		</div>
		<?php
		echo $after_widget;
		$output = ob_get_clean();
		echo $output;
	}
	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		global $mmt_eo_exim;
		$total_noe             = 20;
		$title                 = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Upcoming Events', 'mmt-eo-exim' );
		$no_of_events          = ! empty( $instance['no_of_events'] ) ? $instance['no_of_events'] : 5;
		$open_event_new_window = ! empty( $instance['open_event_new_window'] ) ? $instance['open_event_new_window'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Widget Title:', 'mmt-eo-exim' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'no_of_events' ) ); ?>"><?php esc_attr_e( 'No. of events:', 'mmt-eo-exim' ); ?></label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'no_of_events' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_of_events' ) ); ?>">
				<?php for ( $i = 1; $i <= $total_noe; $i++ ) { ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $no_of_events, $i ); ?>><?php echo esc_html( $i ); ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<span class="mmt-be-toggle-container">
				<label class="switch">
					<input type="checkbox" class="switch-input" name="<?php echo esc_attr( $this->get_field_name( 'open_event_new_window' ) ); ?>" <?php checked( $open_event_new_window, 'on' ); ?>>
					<span class="switch-label" data-on="Yes" data-off="No"></span>
					<span class="switch-handle"></span>
				</label>
			</span>
			<span class="mmt-be-toggle-container-label">
				<?php esc_html_e( 'Open event in new window', 'mmt-eo-exim' ); ?>
			</span>
		</p>
		<?php
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                          = array();
		$instance['title']                 = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['no_of_events']          = ( ! empty( $new_instance['no_of_events'] ) ) ? sanitize_text_field( $new_instance['no_of_events'] ) : 5;
		$instance['open_event_new_window'] = ( ! empty( $new_instance['open_event_new_window'] ) ) ? sanitize_text_field( $new_instance['open_event_new_window'] ) : '';

		return $instance;
	}
}
