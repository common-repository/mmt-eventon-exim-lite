<?php
/**
 * MMT ExIm - Admin Settings Page
 *
 * @author MoMo Themes
 * @package mmt-eo-exim
 * @since v1.0
 */

global $mmt_eo_exim;
$mmt_eo_exim_options = get_option( 'mmt_eo_exim_options' );
?>
<div id="mmt-be-form">
	<div class="mmt-be-wrapper">
		<h2 class="nav-tab-wrapper">  
			<div class="nav-tab nav-tab-active">
				<?php esc_html_e( 'MMT - ExIm Lite', 'mmt-eo-exim' ); ?>
			</div>  
		</h2>

		<table class="mmt-be-tab-table">
			<tbody>
				<tr>
					<td valign="top">
						<ul class="mmt-be-main-tab">
							<li><a class="mmt-eo-ei-tablinks active" href="#mmt-eo-ei-eventbrite"><i class="dashicons dashicons-admin-generic"></i><?php esc_html_e( 'Settings', 'mmt-eo-exim' ); ?></a></li>
							<li><a class="mmt-eo-ei-tablinks" href="#mmt-eo-ei-import"><i class="dashicons dashicons-download"></i><?php esc_html_e( 'EventON Import', 'mmt-eo-exim' ); ?></a></li>
							<li><a class="mmt-eo-ei-tablinks" href="#mmt-eo-ei-display"><i class="dashicons dashicons-media-code"></i><?php esc_html_e( 'Widget', 'mmt-eo-exim' ); ?></a></li>
						</ul>
					</td>
					<td class="mmt-be-main-tabcontent" width="100%" valign="top">
						<div class="mmt_be_working"></div>	
						<div id="mmt-eo-ei-eventbrite" class="mmt-eo-ei-admin-content active">
							<?php require_once 'pages/page-eventbrite-settings.php'; ?>
						</div>
						<div id="mmt-eo-ei-import" class="mmt-eo-ei-admin-content">
							<?php require_once 'pages/page-eventbrite-import.php'; ?>
						</div>
						<div id="mmt-eo-ei-display" class="mmt-eo-ei-admin-content">
							<?php require_once 'pages/page-eventbrite-display.php'; ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
