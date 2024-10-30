<?php
/**
 * Eventon ExIm - Eventbrite Import Page
 *
 * @author MoMo Themes
 * @package mmt-eo-exim
 * @since v1.0
 */

?>
<div class="mmt-eo-ei-admin-content-box">
	<div class="mmt-be-table-header">
		<h3><?php esc_html_e( 'Import', 'mmt-eo-exim' ); ?></h3>
	</div>
	<div class="mmt-eo-exim-admin-content-main eb_imports_main_block" id="mmt-eo-ei-eventbrite-import-form">
		<div class="mmt-be-msg-block"></div>
		<div class="mmt-be-section-block-normal">
			<p>
				<label class="regular inline">
					<?php
					esc_html_e( 'Import Event(s) by ', 'mmt-eo-exim' );
					?>
				</label>
				<select class="inline" name="eb_imports_import_by">
					<option value="event_id"><?php esc_html_e( 'Event ID', 'mmt-eo-exim' ); ?></option>
				</select>
			</p>
		</div>
		<div class="mmt-be-option-block show" id="by_event_id">
			<div class="mmt-eo-ei-block">
				<label class="regular inline"><?php esc_html_e( 'Event ID', 'mmt-eo-exim' ); ?></label>
				<input type="text" class="inline" name="eb_imports_by_event_id_eid"/>
			</div>
			<em class="hr_line"></em>
			<div class="mmt-eo-ei-block">
				<span class="mmt-be-note">
					<?php esc_html_e( 'e.g. : ', 'mmt-eo-exim' ); ?>
					<i>
					https://www.eventbrite.com/e/mokchya-live-concert-tickets-<b>84093713561</b>
					</i>
				</span>
			</div>
			<div class="mmt-eo-ei-block">
				<span class="mmt-be-note">
					<?php esc_html_e( 'Event ID is the end number of the URL.' , 'mmt-eo-ei' ); ?>
				</span>
			</div>
			<div class="mmt-eo-ei-block">
				<a href="#" class="mmt-be-btn mmt-be-btn-secondary eb_fetch_by" data-by="event_id">
					<?php esc_html_e( 'Fetch Event(s)', 'mmt-eo-exim' ); ?>
				</a>
			</div>
			<div class="mmt-be-back-to-list-block">
				<span class="mmt-be-float-right mmt-be-btn mmt-be-btn-extra  eb_back_to_fetch_list">
					<?php esc_html_e( 'Back to event(s) list', 'mmt-eo-ei' ); ?><i class="fa fa-angle-right"></i>
				</span>
			</div>
		</div>
	</div>
	<div class="mmt-eo-exim-admin-content-main mmt-be-result-block">
		<div class="mmt-be-msg-block"></div>
		<input name="eb_generated_events" id="eb_generated_events" type="hidden" value=""/>
		<div class="mmt-be-imports-table">
			<table>
				<thead>
					<tr>
						<th>
						</th>
						<th>
							<?php esc_html_e( 'Event Name', 'mmt-eo-exim' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Start Date', 'mmt-eo-exim' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'End Date', 'mmt-eo-exim' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Summary', 'mmt-eo-exim' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Status', 'mmt-eo-exim' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
			<em class="hr_line_blank"></em>
			<span class="mmt-be-btn mmt-be-btn-primary mmt-eo-exim-cb-ebrite-imports">
				<?php esc_html_e( 'Import Event(s)', 'mmt-eo-exim' ); ?>
			</span>
		</div>
		<div class="mmt-be-fetch-more-box">
			<span class="mmt-be-btn mmt-be-btn-secondary mmt-eo-exim-cb-ebrite-fetch-more">
				<?php esc_html_e( 'Fetch More Event(s)', 'mmt-eo-exim' ); ?>
			</span>
		</div>
	</div>
</div>
