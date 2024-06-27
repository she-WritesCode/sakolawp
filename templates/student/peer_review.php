<?php

defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

global $wpdb;

$running_year = get_option('running_year');

$student_id = get_current_user_id();
$homework_table = $wpdb->prefix . 'sakolawp_homework';
$deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';
$peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';
$enroll_table = $wpdb->prefix . 'sakolawp_enroll';

$enroll = $wpdb->get_row("SELECT class_id, section_id, accountability_id FROM {$enroll_table} WHERE student_id = $student_id");

if (!empty($enroll)) :

	$user_info = get_userdata($student_id);
	$student_name = $user_info->display_name;

	$student_class = $wpdb->get_row("SELECT class_id, name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = '$enroll->class_id'");
	$student_section = $wpdb->get_row("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $enroll->section_id");
	$student_accountability = $wpdb->get_row("SELECT accountability_id, name FROM {$wpdb->prefix}sakolawp_accountability WHERE accountability_id = $enroll->accountability_id");

	// $courseRepo = new RunCourseRepo();
	// $courses = $courseRepo->list(['key' => 'sakolawp_class_id', 'value' => $enroll->class_id, 'compare' => '=']);
	// $course_ids = array_column($courses, 'ID');
	// $course_ids_string = implode(',', $course_ids);
	// $homeworks = $wpdb->get_results("SELECT homework_id FROM {$homework_table} WHERE subject_id IN($course_ids_string) AND allow_peer_review = 1 AND peer_review_who = 'student' ORDER BY created_at DESC;", ARRAY_A);
	// $homework_ids = array_column($homeworks, 'homework_id');
	// $homework_ids_string = implode(', ', $homework_ids);

	$homework_deliveries = $wpdb->get_results("SELECT d.*, h.title, h.section_id,h.homework_code
		FROM $homework_table h
		JOIN $deliveries_table d ON h.homework_code = d.homework_code
		JOIN $enroll_table e ON d.student_id = e.student_id
		WHERE h.allow_peer_review = 1  
		AND h.peer_review_who = 'student'
		AND d.student_id != '$student_id' 
		AND d.class_id = '$enroll->class_id' 
		AND e.section_id = '$enroll->section_id'
		ORDER BY h.created_at DESC, d.date DESC;", ARRAY_A);
?>

	<div class="homework-inner skwp-content-inner skwp-clearfix">

		<div class="skwp-page-title">
			<h5><?php esc_html_e('My Peer Reviews', 'sakolawp'); ?>
				<span class="skwp-subtitle">
					<?php echo esc_html($student_name); ?>
					<br /><i>
						<?php
						if (isset($student_class)) {
							echo ' ' . esc_html($student_class->name);
						}
						if (isset($student_section)) {
							echo ' ' . esc_html($student_section->name);
						}
						if (isset($student_accountability)) {
							echo ' - ' . esc_html($student_accountability->name);
						}
						?>
					</i>
				</span>
			</h5>
		</div>

		<?php
		$grouped_by_homework = array_group_by($homework_deliveries, 'homework_code');
		foreach ($grouped_by_homework as $deliveries) :
		?>
			<div class="my-8">
				<h6><?php echo esc_html($deliveries[0]['title']); ?></h6>
				<div class="skwp-table table-responsive">
					<table id="tableini" class="homework-table-<?php echo esc_html($deliveries[0]['homework_code']); ?> table dataTable homework-table">
						<thead>
							<tr>
								<th class="title-homework"><?php esc_html_e('Peer', 'sakolawp'); ?></th>
								<!-- <th class="title-homework"><?php esc_html_e('Homework', 'sakolawp'); ?></th> -->
								<th><?php esc_html_e('Subject', 'sakolawp'); ?></th>
								<th><?php esc_html_e('Submitted on', 'sakolawp'); ?></th>
								<th><?php esc_html_e('Options', 'sakolawp'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($deliveries as $row) :
								$delivery_id = $row['delivery_id'];
								$peer_id = $row['student_id'];
								$peer_enroll = $wpdb->get_row("SELECT class_id, section_id, accountability_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $peer_id");

							?>
								<tr>
									<td>
										<?php
										$user_info = get_user_meta($peer_id);
										$first_name = $user_info["first_name"][0];
										$last_name = $user_info["last_name"][0];

										$user_name = $first_name . ' ' . $last_name;

										if (empty($first_name)) {
											$user_info = get_userdata($peer_id);
											$user_name = $user_info->display_name;
										}
										echo esc_html($user_name);

										$peer_section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $peer_enroll->section_id");
										$peer_accountability = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_accountability WHERE accountability_id = $peer_enroll->accountability_id");
										if (isset($peer_section) && isset($peer_accountability)) {
										?>
											<i class="text-sm"> <?php echo '<br/>' . esc_html($peer_section->name) . ' - ' . $peer_accountability->name; ?> </i>
										<?php } ?>
									</td>
									<!-- <td>
										<?php echo esc_html($row['title']); ?>
									</td> -->
									<td>
										<?php
										// going this route because I recently changed subject to courses(a custom post type) and deliveries where not migrated
										$subject_id = $wpdb->get_var("SELECT subject_id FROM {$homework_table} WHERE homework_code = '{$row['homework_code']}'");
										// $subject_id = $row['subject_id'];
										$subject = get_post((int)$subject_id);
										echo esc_html($subject->post_title);
										?>
									</td>
									<td>
										<?php echo esc_html($row['date']); ?>
									</td>
									<td class="row-actions">
										<?php

										$current_peer_review = $wpdb->get_row("SELECT * FROM $peer_reviews_table
											WHERE delivery_id = '$delivery_id'
											AND reviewer_id = '$student_id';", ARRAY_A);
										if (empty($current_peer_review)) :
										?>

											<a href="<?php echo add_query_arg('delivery_id', $delivery_id, home_url('peer_review_room')); ?>" class="btn btn-primary btn-rounded btn-sm skwp-btn">
												<?php echo esc_html__('Review', 'sakolawp'); ?>
											</a>
										<?php else : ?>
											<a href="<?php echo add_query_arg('delivery_id', $delivery_id, home_url('peer_review_room')); ?>" class="btn btn-success btn-rounded btn-sm skwp-btn">
												<?php echo esc_html__('Reviewed', 'sakolawp'); ?>
											</a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php
else :
	esc_html_e('You are not assigned to a class yet', 'sakolawp');
endif;

do_action('sakolawp_after_main_content');
get_footer();
