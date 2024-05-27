<?php

defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

global $wpdb;

$running_year = get_option('running_year');

$student_id = get_current_user_id();

$enroll = $wpdb->get_row("SELECT class_id, section_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");

if (!empty($enroll)) :

	$user_info = get_userdata($student_id);
	$student_name = $user_info->display_name;

?>

	<div class="homework-inner skwp-content-inner skwp-clearfix">

		<div class="skwp-page-title">
			<h5><?php esc_html_e('My Class Homeworks', 'sakolawp'); ?>
				<span class="skwp-subtitle">
					<?php echo esc_html($student_name); ?>
				</span>
			</h5>
		</div>

		<div class="skwp-table table-responsive">
			<table id="tableini" class="table dataTable homework-table">
				<thead>
					<tr>
						<th class="title-homework"><?php esc_html_e('Title', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Class', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Subject', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Due Date', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Faculty', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Options', 'sakolawp'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$counter = 1;
					$homeworks = $wpdb->get_results("SELECT title, class_id, section_id, subject_id, date_end,time_end, homework_code, uploader_id, allow_peer_review FROM {$wpdb->prefix}sakolawp_homework 
					WHERE (class_id = '$enroll->class_id'
					AND section_id = '$enroll->section_id') OR (class_id = '$enroll->class_id' AND section_id = 0) ORDER BY created_at desc;", ARRAY_A);

					foreach ($homeworks as $row) :
					?>
						<tr>
							<td>
								<?php echo esc_html($row['title']); ?>
							</td>
							<td>
								<?php
								$class_id = $row['class_id'];
								$section_id = $row['section_id'];
								$class = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $class_id");
								echo esc_html($class->name);

								$section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $section_id");
								if (isset($section)) {
									echo esc_html__(' - ', 'sakolawp');
									echo esc_html($section->name);
								}
								?>
							</td>
							<td>
								<?php $subject_id = $row['subject_id'];
								$subject = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = $subject_id");
								echo esc_html($subject->name);
								$allow_peer_review = $row['allow_peer_review'];
								echo $allow_peer_review ? '<br/> <span class="btn nc btn-rounded btn-sm btn-success skwp-btn">peer reviewable</span>' : "";
								?>
							</td>
							<td>
								<a class="btn nc btn-rounded btn-sm btn-danger skwp-btn">
									<?php echo esc_html($row['date_end']) . ' ' . esc_html($row['time_end']); ?>
								</a>
								<br />
								<span class="skwp-date" data-end-date="<?php echo esc_html($row['date_end']); ?>" data-end-time="<?php echo esc_html($row['time_end']); ?>"></span>
							</td>
							<td>
								<?php
								$user_info = get_user_meta($row['uploader_id']);
								$first_name = $user_info["first_name"][0];
								$last_name = $user_info["last_name"][0];

								$user_name = $first_name . ' ' . $last_name;

								if (empty($first_name)) {
									$user_info = get_userdata($row['uploader_id']);
									$user_name = $user_info->display_name;
								}
								echo esc_html($user_name); ?>
							</td>
							<td class="row-actions">
								<a href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom')); ?>" class="btn btn-primary btn-rounded btn-sm skwp-btn">
									<?php echo esc_html__('View Detail', 'sakolawp'); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<?php
else :
	esc_html_e('You are not assigned to a class yet', 'sakolawp');
endif;

do_action('sakolawp_after_main_content');
get_footer();
