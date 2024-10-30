<?php
/**
 * Eventon ExIm - Eventbrite Settings Page
 *
 * @author MoMo Themes
 * @package mmt-eo-exim
 * @since v1.0
 */

$eb_private_token    = isset( $mmt_eo_exim_options['eb_private_token'] ) ? $mmt_eo_exim_options['eb_private_token'] : '';
$eb_export_organizer = isset( $mmt_eo_exim_options['eb_export_organizer'] ) ? $mmt_eo_exim_options['eb_export_organizer'] : '';
$eb_default_currency = isset( $mmt_eo_exim_options['eb_default_currency'] ) ? $mmt_eo_exim_options['eb_default_currency'] : '';

$org_list = array();
$org_list = $mmt_eo_exim->fn->mmt_eo_exim_eb_org_list();
$currency = new MMT_EO_ExIm_Currency_List();
?>
<div class="mmt-eo-ei-admin-content-box">
	<div class="mmt-be-table-header">
		<h3><?php esc_html_e( 'Eventbrite Settings', 'mmt-eo-exim' ); ?></h3>
	</div>
	<div class="mmt-ms-admin-content-main" id="mmt-eo-ei-eventbrite-settings-form">
		<div class="mmt-be-section block-eventid">
			<div class="mmt-eo-ei-block">
				<label class="regular inline"><?php esc_html_e( 'Eventbrite Private Token:', 'mmt-eo-exim' ); ?></label>
				<input type="text" class="inline" name="eb_private_token" value="<?php echo esc_html( $eb_private_token ); ?>"/>
			</div>
			<div class="mmt-eo-ei-block">
				<span class="mmt-be-note">
				<?php
				echo sprintf(
					__( 'Note: For private token, Log in to your Eventbrite account and visit your <a href="%s">API Keys page</a>.', 'mmt-eo-exim' ),
					'https://www.eventbrite.com/platform/api-keys'
				);
				?>
				</span>
			</div>
		</div>
		<div class="mmt-be-section">
			<div class="mmt-be-section-header">
				<?php esc_html_e( 'Eventbrite Export Settings', 'mmt-eo-exim' ); ?>
			</div>
			<div class="mmt-eo-ei-block">
				<div class="mmt-be-section-block-normal">
					<span class="mmt-be-note">
						<?php esc_html_e( 'Please save your Private token before selecting organization. Organization dropdown will only be populated after you save your private token.', 'mmt-eo-exim' ); ?>
					</span>
				</div>
				<div class="mmt-be-section-block-normal">
					<label class="regular inline">
						<?php
						esc_html_e( 'Select organization list', 'mmt-eo-exim' );
						?>
					</label>
					<select class="inline" name="eb_export_organizer">
						<?php
						foreach ( $org_list as $slug => $name ) :
							$selected = ( $eb_export_organizer === $slug ) ? 'selected' : '';
							echo '<option value="' . $slug . '" ' . $selected . '>' . $name . '</option>';	
						endforeach;
						?>
					</select>
				</div>
				<div class="mmt-be-section-block-normal">
					<span class="mmt-be-note">
						<?php esc_html_e( 'All other EventON repeat type will be exported as Eventbrite series.', 'mmt-eo-exim' ); ?>
					</span>
				</div>
				<div class="mmt-be-section-block-normal">
					<label class="regular inline">
						<?php
						esc_html_e( 'Select default currency while exporting event', 'mmt-eo-exim' );
						?>
					</label>
					<select class="inline" name="eb_default_currency">
						<?php
						foreach ( $currency->clist as $slug => $name ) :
							$selected = ( $eb_default_currency === $slug ) ? 'selected' : '';
							echo '<option value="' . $slug . '" ' . $selected . '>' . $name . '</option>';	
						endforeach;
						?>
					</select>
				</div>
				<div class="mmt-be-section-block-normal">
					<span class="mmt-be-note">
						<?php esc_html_e( 'Please select your timezone city before exporting your event. Only future events will be exported to eventbrite.', 'mmt-eo-exim' ); ?>
					</span>
				</div>
			</div>
		</div>
	</div>
	<a href="#" class="mmt-be-btn mmt-be-btn-secondary mmt_eo_exim_save_sttings" data-by="venue_id">
		<?php esc_html_e( 'Save Settings', 'mmt-eo-exim' ); ?>
	</a>
</div>
