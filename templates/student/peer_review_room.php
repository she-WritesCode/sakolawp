<?php
defined('ABSPATH') || exit;

global $wpdb;

$homework_table = $wpdb->prefix . 'sakolawp_homework';
$deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';
$peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';


get_header();
do_action('sakolawp_before_main_content');

$running_year = get_option('running_year');

$student_id = get_current_user_id();

$enroll = $wpdb->get_row("SELECT class_id, section_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");

if (!empty($enroll)) :

	$user_info = get_userdata($student_id);
	$student_name = $user_info->display_name;

	$delivery_id = $_GET['delivery_id'];

	$current_delivery = $wpdb->get_results("SELECT * 
		FROM $deliveries_table d
		JOIN $homework_table h ON d.homework_code = h.homework_code
		WHERE d.delivery_id = '$delivery_id';", ARRAY_A);

	foreach ($current_delivery as $row) :

		$peer_id = $row['student_id'];
		$peer_info = get_userdata($peer_id);
		$peer_name = $peer_info->display_name;
		$peer_enroll = $wpdb->get_row("SELECT class_id, section_id, accountability_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");

		$peer_section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $peer_enroll->section_id");
		$peer_accountability = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_accountability WHERE accountability_id = $peer_enroll->accountability_id");

?>
		<div class="homeworkroom-inner homeworkroom-page skwp-content-inner skwp-clearfix">

			<div class="back skwp-back hidden-sm-down">
				<a href="<?php echo esc_url(site_url('peer_review')); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php esc_html_e('Back', 'sakolawp'); ?></a>
			</div>

			<div class="skwp-row gap-4">
				<div class="skwp-column skwp-column-1">
					<div class="pipeline white lined-primary shadow diskusi-desc">
						<div class="pipeline-header">
							<h5 class="pipeline-name">
								<?php
								echo esc_html($row['title']);

								echo '<br/>' . ' <i>Submitted by: ' . esc_html($peer_name) . '</i>';
								if (isset($peer_section) && isset($peer_accountability)) {
								?>
									<i class="skwp-subtitle"> <?php echo  esc_html($peer_section->name) . ' - ' . $peer_accountability->name; ?> </i>
								<?php } ?>

							</h5>
							<div class="pipeline-header-numbers">
								<div class="pipeline-count">
									<i class="os-icon picons-thin-icon-thin-0024_calendar_month_day_planner_events"></i>
									<?php echo esc_html($row['date_end']); ?> <br>
									<i class="os-icon picons-thin-icon-thin-0025_alarm_clock_ringer_time_morning"></i>
									<?php echo esc_html($row['time_end']); ?>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="skwp-column skwp-column-1">
					<div class="skwp-sidebar-title">
						<h5 class="skwp-title">
							<?php esc_html_e('Homework Response', 'sakolawp'); ?>
						</h5>
					</div>
					<p>
						<?php echo esc_html($row['homework_reply']); ?>
					</p>
					<?php if ($row['file_name'] != "") :
						$url_file = site_url() . '/wp-content/uploads/sakolawp/homework/' . $row['file_name'];
						$url_file = str_replace(' ', '-', $url_file); ?>
						<div class="b-t padded-v-big homework-attachment">
							<?php esc_html_e('Files : ', 'sakolawp'); ?><a class="btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo esc_url($url_file); ?>" target="_blank"><i class="os-icon picons-thin-icon-thin-0042_attachment"></i><?php esc_html_e('Download Attachment', 'sakolawp'); ?></a>
						</div>
					<?php endif; ?>
				</div>

				<?php

				$current_peer_review = $wpdb->get_results("SELECT * FROM $peer_reviews_table
					WHERE delivery_id = '$delivery_id'
					AND reviewer_id = '$student_id';", ARRAY_A);

				error_log("current_peer_review is " . json_encode($current_peer_review));

				if (count($current_peer_review) == 0) :
				?>
					<div class="skwp-column skwp-column-1">
						<form id="<?php echo $row['peer_review_template'] . '_form'; ?>" method="POST">
							<input type="hidden" name="action" value="submit_peer_review" />

							<input type="hidden" name="delivery_id" value="<?php echo esc_attr($delivery_id); ?>">
							<input type="hidden" name="homework_id" value="<?php echo esc_attr($row['homework_id']); ?>">
							<input type="hidden" name="reviewer_id" value="<?php echo esc_attr($student_id); ?>">
							<input type="hidden" name="peer_id" value="<?php echo esc_attr($peer_id); ?>">
							<input type="hidden" name="class_id" value="<?php echo esc_attr($peer_enroll->class_id); ?>">
							<input type="hidden" name="section_id" value="<?php echo esc_attr($peer_enroll->section_id); ?>">
							<input type="hidden" name="accountability_id" value="<?php echo esc_attr($peer_enroll->accountability_id); ?>">
							<input type="hidden" name="subject_id" value="<?php echo esc_attr($row['subject_id']); ?>">
							<input type="hidden" name="reviewer_type" value="<?php echo esc_attr($user_info->roles[0]); ?>">

							<?php do_action('sakolawp_form_' . $row['peer_review_template'] . '_assessment'); ?>

							<div class="flex flex-col gap-2 p-4 border bg-gray-50 mt-4">
								<div class="flex flex-col gap-2">
									<label class="font-medium"><?php echo esc_html("Any comments or observation"); ?></label>

									<textarea name="reviewer_comment"></textarea>
								</div>
							</div>
							<button class="btn btn-success skwp-btn" type="submit" name="submit" value="submit">Submit</button>
						</form>
					</div>

				<?php else : ?>
					<div class="skwp-column skwp-column-1">
						<div class="skwp-sidebar-title">
							<h5 class="skwp-title btn btn-rounded btn-sm skwp-btn btn-primary">
								<?php esc_html_e('You already reviewed this submission', 'sakolawp'); ?>
							</h5>
						</div>
					</div>
				<?php endif; ?>

				<div class="skwp-column skwp-column-1 homework-info">
					<div class="skwp-content-sidebar">
						<div class="skwp-sidebar-title">
							<h5 class="skwp-title">
								<?php esc_html_e('Home Information', 'sakolawp'); ?>
							</h5>
						</div>
						<div class="table-responsive">
							<table class="table table-lightbor table-lightfont">
								<tr>
									<th>
										<?php esc_html_e('Subject:', 'sakolawp'); ?>
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
										<?php esc_html_e('Faculty:', 'sakolawp'); ?>
									</th>
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
										echo esc_html($user_name);
										?>
									</td>
								</tr>
								<tr>
									<th>
										<?php esc_html_e('Class:', 'sakolawp'); ?>
									</th>
									<td>
										<?php
										$class_id = $row["class_id"];
										$class = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = '$class_id'", ARRAY_A);
										echo esc_html($class['name']);
										?>
									</td>
								</tr>
								<tr>
									<?php
									$section_id = $row["section_id"];
									$section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = '$section_id'", ARRAY_A);
									if (isset($section)) {
									?>
										<th>
											<?php esc_html_e('Parent Group:', 'sakolawp'); ?>
										</th>
										<td>
											<?php echo esc_html($section['name']); ?>
										</td>
									<?php } ?>
								</tr>
								<tr>
									<th>
										<?php esc_html_e('Due Date:', 'sakolawp'); ?>
									</th>
									<td>
										<a class="btn nc btn-rounded btn-sm skwp-btn btn-success">
											<?php echo esc_html($row['date_end']); ?>
											<?php echo esc_html($row['time_end']); ?>
										</a>
									</td>
								</tr>
								<tr>
									<th>
										<?php esc_html_e('Status:', 'sakolawp'); ?>
									</th>
									<td>
										<?php if (empty($current_peer_review)) : ?>
											<a class="btn nc btn-rounded btn-sm skwp-btn btn-danger"><?php esc_html_e('Not Reviewed', 'sakolawp'); ?></a>
										<?php endif; ?>
										<?php if (!empty($current_peer_review)) : ?>
											<a class="btn nc btn-rounded btn-sm skwp-btn btn-success"><?php esc_html_e('Reviewed', 'sakolawp'); ?></a>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th>
										<?php esc_html_e('Mark:', 'sakolawp'); ?>
									</th>
									<td>
										<?php if (empty($current_peer_review)) : ?>
											<a class="btn btn-rounded btn-sm skwp-btn btn-danger"><?php esc_html_e('Not Marked', 'sakolawp'); ?></a>
										<?php endif; ?>
										<?php if (!empty($current_peer_review)) : ?>
											<?php if ($current_peer_review['mark'] != NULL) { ?>
												<a class="btn btn-rounded btn-sm skwp-btn btn-primary"><?php echo esc_html($current_peer_review['mark']); ?></a>
											<?php } else {
												esc_html_e('In Review', 'sakolawp');
											} ?>
										<?php endif; ?>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>

			</div>
		</div>

<?php
	endforeach;

else :
	esc_html_e('No homework has been created for your class yet', 'sakolawp');
endif;

do_action('sakolawp_after_main_content');
get_footer();
