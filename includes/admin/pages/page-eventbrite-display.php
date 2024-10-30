<?php
/**
 * Eventon ExIm - Eventbrite WordPress Display
 *
 * @author MoMo Themes
 * @package mmt-eo-exim
 * @since v1.0
 */

$enable_eventbrite_widget = isset( $mmt_eo_exim_options['enable_eventbrite_widget'] ) ? $mmt_eo_exim_options['enable_eventbrite_widget'] : '';
$eb_display_organizer     = isset( $mmt_eo_exim_options['eb_display_organizer'] ) ? $mmt_eo_exim_options['eb_display_organizer'] : '';

$org_list = array();
$org_list = $mmt_eo_exim->fn->mmt_eo_exim_eb_org_list();
?>
<div class="mmt-eo-ei-admin-content-box">
	<div class="mmt-be-table-header">
		<h3><?php esc_html_e( 'Display', 'mmt-eo-exim' ); ?></h3>
	</div>
	<div class="mmt-eo-exim-admin-content-main eb_display_main_block" id="mmt-eo-ei-eventbrite-display-form">
		<div class="mmt-be-section">
			<span class="mmt-be-toggle-container">
				<label class="switch">
				<?php $checked = ( 'on' === $enable_eventbrite_widget ) ? 'checked' : ''; ?>
					<input type="checkbox" class="switch-input" name="enable_eventbrite_widget" <?php echo esc_html( $checked ); ?>>
					<span class="switch-label" data-on="Yes" data-off="No"></span>
					<span class="switch-handle"></span>
				</label>
			</span>
			<span class="mmt-be-toggle-container-label">
				<?php esc_html_e( 'Enable Eventbrite Widget', 'mmt-eo-exim' ); ?>
			</span>
		</div>
		<em class="mmt-be-hr-line"></em>
		<div class="mmt-be-section">
			<label class="regular inline">
				<?php
				esc_html_e( 'Select organization list to display events', 'mmt-eo-exim' );
				?>
			</label>
			<select class="inline" name="eb_display_organizer">
				<?php
				foreach ( $org_list as $slug => $name ) :
					$selected = ( $eb_display_organizer === $slug ) ? 'selected' : '';
					echo '<option value="' . $slug . '" ' . $selected . '>' . $name . '</option>';
				endforeach;
				?>
			</select>
		</div>
		<em class="mmt-be-hr-line-full"></em>
		<a href="#" class="mmt-be-btn mmt-be-btn-secondary mmt_eo_exim_save_display">
			<?php esc_html_e( 'Save Widget Settings', 'mmt-eo-exim' ); ?>
		</a>
	</div>
</div>
