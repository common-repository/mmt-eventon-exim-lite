<?php
/**
 * Eventbrite Search Page
 *
 * @author MoMo Themes
 */

?>
<div class="mmt-eo-ei-admin-content-box">
	<div class="mmt-eo-ei-admin-content-header">
		<h3><?php esc_html_e( 'Search', 'mmt-eo-exim' ); ?></h3>
	</div>
	<div class="mmt-ms-admin-content-main" id="mmt-eo-ei-eventbrite-search-form">
		<div class="eb_search_result_block">
			<?php esc_html_e( 'Result', 'mmt-eo-exim' ); ?>
		</div>
		<div class="mmt-eo-ei-admin-block">
			<div class="mmt-eo-ei-section-header">
				<?php esc_html_e( 'Search for Organization ID', 'mmt-eo-exim' ); ?>
			</div>
			<div class="mmt-eo-ei-block">
				<label class="regular inline"><?php esc_html_e( 'Event ID', 'mmt-eo-exim' ); ?></label>
				<input type="text" class="inline" name="eb_id_for_org_id"/>
			</div>
			<div class="mmt-eo-ei-block">
				<span class="mmt_eo_exim_btn_secondary eb_search_organizer_id">
					<?php esc_html_e( 'Search Organization ID', 'mmt-eo-exim' ); ?>
				</span>
			</div>
		</div>
		<div class="mmt-eo-ei-admin-block">
			<div class="mmt-eo-ei-section-header">
				<?php esc_html_e( 'Search for Venue ID', 'mmt-eo-exim' ); ?>
			</div>
			<div class="mmt-eo-ei-block">
				<label class="regular inline"><?php esc_html_e( 'Event ID', 'mmt-eo-exim' ); ?></label>
				<input type="text" class="inline" name="eb_id_for_venue_id"/>
			</div>
			<div class="mmt-eo-ei-block">
				<span class="mmt_eo_exim_btn_secondary eb_search_venue_id">
					<?php esc_html_e( 'Search Venue ID', 'mmt-eo-exim' ); ?>
				</span>
			</div>
		</div>
	</div>
</div>
