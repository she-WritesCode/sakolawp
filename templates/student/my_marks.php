<?php
defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

$running_year = get_option('running_year');

?>

<div class="sakolawp-marks-page skwp-content-inner skwp-clearfix">

	<div class="skwp-container">
		<div class="skwp-table table-responsive">
			<?php

			$exams = $wpdb->get_results("SELECT exam_id, name FROM {$wpdb->prefix}sakolawp_exam WHERE year = '$running_year'", ARRAY_A);
			foreach ($exams as $exam) :

				$student_id = get_current_user_id();
				$enroll = $wpdb->get_results("SELECT class_id, section_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id AND year = '$running_year'", ARRAY_A); ?>
				<div class="title-marks">
					<h3><?php echo esc_html($exam['name']); ?></h3>
				</div>
				<table id="tabbles" class="table table-marks table-lightborder">
					<thead>
						<tr>
							<th><?php echo esc_html__('Subject', 'sakolawp'); ?></th>
							<th><?php echo esc_html__('Faculty', 'sakolawp'); ?></th>
							<th><?php echo esc_html__('Marks', 'sakolawp'); ?></th>
							<th><?php echo esc_html__('Options', 'sakolawp'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$class_id = $enroll[0]['class_id'];
						$section_id = $enroll[0]['section_id'];

						$subjects = $wpdb->get_results("SELECT subject_id, teacher_id, name FROM {$wpdb->prefix}sakolawp_subject WHERE class_id = $class_id AND section_id = $section_id", ARRAY_A);

						foreach ($subjects as $subject) :
							$obtained_mark_query = $wpdb->get_results("SELECT mark_obtained FROM {$wpdb->prefix}sakolawp_mark WHERE class_id = $class_id AND section_id = $section_id AND subject_id = {$subject['subject_id']} AND student_id = $student_id AND year = '$running_year' AND exam_id = {$exam['exam_id']}", ARRAY_A);

							foreach ($obtained_mark_query as $row) :

								$subject2 = $wpdb->get_row("SELECT total_lab FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = {$subject['subject_id']}");
								$total_kd = $subject2->total_lab;
						?>
								<tr>
									<td><?php echo esc_html($subject['name']); ?></td>
									<td><?php
										$user_info = get_user_meta($subject['teacher_id']);
										$first_name = $user_info["first_name"][0];
										$last_name = $user_info["last_name"][0];
										$user_name = $first_name . ' ' . $last_name;

										if (empty($first_name)) {
											$user_info = get_userdata($current_id);
											$user_name = $user_info->display_name;
										}

										echo esc_html($user_name); ?>
									</td>
									<td>
										<?php
										if (empty($total_kd) || $total_kd == 1) {
											echo esc_html($row['mark_obtained']);
										} else {

											$lab_columns = '';
											for ($i = 1; $i <= $total_kd; $i++) {
												$lab_columns .= ",lab{$i}";
											}
											$mark2 = $wpdb->get_results("SELECT mark_obtained{$lab_columns} FROM {$wpdb->prefix}sakolawp_mark WHERE class_id = $class_id AND section_id = $section_id AND subject_id = {$subject['subject_id']} AND student_id = $student_id AND year = '$running_year' AND exam_id = {$exam['exam_id']}", ARRAY_A);
											$nilai = $mark2[0];

											$labtotal = 0;
											$total_nol = array();

											for ($i = 0; $i < $total_kd; $i++) {
												$key = $i == 0 ? 'mark_obtained' : 'lab' . ($i + 1);
												if ($nilai[$key] !== NULL) {
													$labtotal += $nilai[$key];
												} else {
													$total_nol[] = $nilai[$key];
												}
											}

											$total_kd2 = $total_kd - count($total_nol);
											if ($total_kd2 == 0) {
												$total_kd2 = $total_kd;
											}

											echo round($labtotal / $total_kd2, 1);
										} ?>
									</td>
									<td>
										<a href="<?php echo add_query_arg(array('exam_id' => $exam['exam_id'], 'student_id' => $student_id, 'subject_id' => $subject['subject_id']), home_url('view_mark')); ?>" class="btn btn-rounded btn-success skwp-btn"><?php echo esc_html__('View', 'sakolawp'); ?></a>
									</td>
								</tr>
						<?php
							endforeach;
						endforeach; ?>

					</tbody>
				</table>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php
do_action('sakolawp_after_main_content');
get_footer();
