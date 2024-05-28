<?php
defined('ABSPATH') || exit;

global $wpdb;

get_header();
do_action('sakolawp_before_main_content');

$teacher_id = get_current_user_id();

$running_year = get_option('running_year');

$homework_code = sanitize_text_field($_GET['homework_code']);
$current_homework = $wpdb->get_results("SELECT homework_code, title, date_end, time_end, description, file_name, subject_id, class_id, section_id, peer_review_template, allow_peer_review,peer_review_who FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);

foreach ($current_homework as $row) :

?>
	<div class="homeworkroom-page skwp-content-inner">
		<div class="skwp-tab-menu">
			<ul class="skwp-tab-wrap">
				<li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom')); ?>">
						<span><?php echo esc_html__('Homework', 'sakolawp'); ?></span>
					</a>
				</li>
				<li class="skwp-tab-items active">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom_details')); ?>">
						<span><?php echo esc_html__('Homework Reports', 'sakolawp'); ?></span>
					</a>
				</li>
				<!-- <li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom_edit')); ?>">
						<span><?php echo esc_html__('Edit', 'sakolawp'); ?></span>
					</a>
				</li> -->
			</ul>
		</div>
		<div class="back skwp-back hidden-sm-down">
			<a href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom')); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
		</div>
		<div class="homework-top">
			<div class="tugas-wrap">
				<table id="tableini" class="table table-lightborder">
					<thead>
						<tr>
							<th><?php echo esc_html__('Name', 'sakolawp'); ?></th>
							<th><?php echo esc_html__('Delivery Status', 'sakolawp'); ?></th>
							<th><?php echo esc_html__('Homework Detail', 'sakolawp'); ?></th>
							<th><?php echo esc_html__('Mark', 'sakolawp'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$time1 = $row['date_end'];
						$time2 = $row['time_end'];
						$time = $time1 . " " . $time2;
						$homework_details = $wpdb->get_results("SELECT student_id, student_comment, date, file_name, teacher_comment, mark, delivery_id FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code'", ARRAY_A);
						foreach ($homework_details as $row2) :
						?>
							<tr>
								<td>
									<?php
									$student_id = $row2["student_id"];
									$student = get_userdata($student_id);
									$user_name = $student->display_name;
									echo $user_name;
									?>
								</td>
								<td>
									<?php if (strtotime($row2['date']) > strtotime($time)) : ?>
										<a class="btn nc btn-rounded btn-sm btn-danger skwp-btn"><?php echo esc_html__('Late', 'sakolawp'); ?></a>
									<?php endif; ?>
									<?php if (strtotime($row2['date']) <= strtotime($time)) : ?>
										<a class="btn nc btn-rounded btn-sm btn-success skwp-btn"><?php echo esc_html__('On Time', 'sakolawp'); ?></a>
									<?php endif; ?>
								</td>
								<td>
									<a class="btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo add_query_arg(array('homework_code' => $row['homework_code'], 'student_id' => $row2['student_id']), home_url('view_homework_student')); ?>">
										<?php echo esc_html__('View Detail', 'sakolawp'); ?>
									</a>
								</td>
								<td>
									<input class="form-control nilai" disabled required name="mark[]" type="number" min="1" max="100" maxlength="3" value="<?php echo esc_attr($row2['mark']); ?>">
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="homework-info skwp-mt-20">
			<h5>
				<?php echo esc_html__('Homework Information', 'sakolawp'); ?>
			</h5>
			<div class="table-responsive">
				<table class="table table-lightbor table-lightfont">
					<tr>
						<th>
							<?php echo esc_html__('Subject', 'sakolawp'); ?>
						</th>
						<td>
							<?php
							$subject_id = $row["subject_id"];
							$subject_name = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = '$subject_id'", ARRAY_A);
							echo esc_html($subject_name['name']);
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php echo esc_html__('Class', 'sakolawp'); ?>
						</th>
						<td>
							<?php
							$class_id = $row["class_id"];
							$class = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = '$class_id'", ARRAY_A);
							echo esc_html($class['name']);
							?>
						</td>
					</tr>
					<?php
					$section_id = $row["section_id"];
					$section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = '$section_id'", ARRAY_A);

					if (isset($section)) {
					?>
						<tr>
							<th>
								<?php echo esc_html__('Parent Group', 'sakolawp'); ?>
							</th>
							<td>
								<?php
								echo $section['name'];
								?>
							</td>
						</tr>
					<?php } ?>
					<?php
					$peer_review_template = $row["peer_review_template"];
					$peer_review_who = $row["peer_review_who"] == "teacher" ? "Faculty" : "Peer";

					if (isset($peer_review_template)) {
					?>
						<tr>
							<th>
								<?php echo esc_html__('Review Template', 'sakolawp'); ?>
							</th>
							<td>
								<?php
								echo $peer_review_template;
								?>
								<?php
								echo '<span class="badge badge-' . ($peer_review_who == 'Faculty' ? 'warning' : 'info') . ' badge-light ">' . $peer_review_who . ' reviewed</span>';
								?>
							</td>
						</tr>
					<?php } ?>
				</table>
			</div>

		</div>
	</div>

<?php
endforeach;
?>

<?php
do_action('sakolawp_after_main_content');
get_footer();
