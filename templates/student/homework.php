<?php

defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

global $wpdb;

$running_year = get_option('running_year');

$student_id = get_current_user_id();

$enroll = $wpdb->get_row("SELECT class_id, section_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");
$class = $wpdb->get_row("SELECT name, start_date FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $enroll->class_id");
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
			<table id="tableini" class="table dataTable responsive homework-table">
				<thead>
					<tr>
						<th class="title-homework"><?php esc_html_e('Title', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Due Date', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Subject', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Class', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Faculty', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Options', 'sakolawp'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$courseRepo = new RunCourseRepo();
					$courses = $courseRepo->list(['key' => 'sakolawp_class_id', 'value' => $enroll->class_id, 'compare' => '=']);
					$course_ids = array_column($courses, 'ID');
					$course_ids_string = implode(',', $course_ids);
					$homeworks = $wpdb->get_results("SELECT homework_id, title, class_id, section_id, subject_id, date_end, time_end, homework_code, uploader_id, allow_peer_review, peer_review_who FROM {$wpdb->prefix}sakolawp_homework WHERE subject_id IN($course_ids_string) ORDER BY created_at desc;", ARRAY_A);

					$programStartDate = $class->start_date;
					foreach ($homeworks as $row) :
						$homework_code = $row['homework_code'];
						$homework_id = $row['homework_id'];
						$current_user_has_submitted = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code' AND student_id = $student_id");
						$homework_schedule = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sakolawp_class_schedule WHERE class_id = $enroll->class_id AND content_type = 'homework' AND content_id = '$homework_id'");

						if (!$homework_schedule) {
							$release_date = '0000-00-00 00:00:00';
							$due_date = '0000-00-00 00:00:00';
						} else {
							if ($homework_schedule->drip_method == 'specific_dates') {
								$release_date = $homework_schedule->release_date;
								$due_date = $homework_schedule->deadline_date;
							} else {
								$release_date = date('Y-m-d H:i:s', strtotime($programStartDate . " +{$homework_schedule->release_days} days"));
								$due_date = date('Y-m-d H:i:s', strtotime($release_date . " +{$homework_schedule->deadline_days} days"));
							}
						}
						// echo '<pre>' . esc_html($row['title']) . ' $release_date' . $release_date . ' $due_date' . $due_date . '</pre>';
						$homeWorkIsDueForRelease = strtotime($release_date) <= max(strtotime($programStartDate), time());
					?>
						<!-- ONLY DISPLAY HOMEWORKS DUE For release -->
						<?php if ($homeWorkIsDueForRelease || $due_date == '0000-00-00 00:00:00') : ?>
							<tr class="clickable-row" data-href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom')); ?>">
								<td><?php echo esc_html($row['title']); ?></td>
								<td><?php echo $due_date == '0000-00-00 00:00:00' ? 'No deadline' : date('F j, Y', strtotime($due_date)); ?>
									<div>
										<?php if (!empty($current_user_has_submitted)) : ?>
											<div class="badge badge-info badge-plain flex gap-2 justify-center items-center">
												<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
												</svg>
												<span>Submitted</span>
											</div>
										<?php else : ?>
											<?php if ($due_date !== '0000-00-00 00:00:00') : ?>
												<span class="skwp-date italic" data-end-date="<?php echo date('Y-m-d', strtotime($due_date)); ?>" data-end-time="<?php echo date('H:i:s', strtotime($due_date)); ?>"></span>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<?php
									$subject_id = $row['subject_id'];
									$subject = get_post((int)$subject_id);
									echo esc_html($subject->post_title);
									$allow_peer_review = $row['allow_peer_review'];
									$peer_review_who = $row["peer_review_who"] == "teacher" ? "Faculty" : "Peer";
									echo $allow_peer_review ? '<br/> <span class="badge badge-' . ($peer_review_who == 'Faculty' ? 'warning' : 'info') . ' badge-light ">' . $peer_review_who . ' reviewed</span>' : "";
									?>
								</td>
								<td><?php echo esc_html($class->name); ?></td>
								<td>
									<?php
									$user_info = get_user_meta($row['uploader_id']);
									$first_name = $user_info["first_name"][0] ?? '';
									$last_name = $user_info["last_name"][0] ?? '';

									$user_name = $first_name . ' ' . $last_name;

									if (empty($first_name)) {
										$user_info = get_userdata($row['uploader_id']);
										$user_name = $user_info->display_name;
									}
									echo esc_html($user_name);
									?>
								</td>
								<td class="row-actions">
									<a href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom')); ?>" class="btn btn-primary btn-rounded btn-sm skwp-btn">
										<?php echo esc_html__('View Detail', 'sakolawp'); ?>
									</a>
								</td>
							</tr>
						<?php endif; ?>
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
