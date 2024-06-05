<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://themesawesome.com/
 * @since      1.0.0
 *
 * @package    Sakolawp
 * @subpackage Sakolawp/admin/partials
 */

global $wpdb;

$running_year = get_option('running_year');
$table_name = $wpdb->prefix . 'sakolawp_attendance';
$results = $wpdb->get_results("SELECT * FROM $table_name");
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="manage-attendance skwp-content-inner">


	<nav class="skwp-tabs-menu">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<div class="skwp-logo">
				<img src="<?php echo plugin_dir_url(__DIR__); ?>img/swp-logo.png" alt="<?php echo esc_attr('Sakola Logo'); ?>">
			</div>
			<a class="nav-item nav-link active" href="#"><?php esc_html_e('Student', 'sakolawp'); ?></a>
			<a class="nav-item nav-link" href="admin.php?page=sakolawp-manage-report-attendance"><?php esc_html_e('Attendance Report', 'sakolawp'); ?></a>
		</div>
	</nav>
	<div class="skwp-tab-content tab-content" id="nav-tabContent">
		<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
			<table>
				<tr>
					<th>User ID</th>
					<th>Meeting ID</th>
					<th>Time</th>
				</tr>
				<?php foreach ($results as $row) : ?>
					<tr>
						<td><?php esc_html($row->student_id); ?></td>
						<td><?php esc_html($row->event_id); ?></td>
						<td><?php esc_html($row->time); ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">

		</div>
	</div>
</div>