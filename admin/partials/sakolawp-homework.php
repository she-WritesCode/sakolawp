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
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="homework-page skwp-content-inner">

	<nav class="skwp-tabs-menu">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<div class="skwp-logo">
				<img src="<?php echo plugin_dir_url(__DIR__); ?>img/swp-logo.png" alt="<?php echo esc_attr('Sakola Logo'); ?>">
			</div>
			<a class="nav-item nav-link active" href="admin.php?page=sakolawp-homework"><?php esc_html_e('Assessment', 'sakolawp'); ?></a>
			<a class="nav-item nav-link" target="__blank" href="<?php echo site_url(); ?>/homework"><?php esc_html_e('Add Assessment', 'sakolawp'); ?></a>

		</div>
	</nav>

	<div class="skwp-tab-content tab-content" id="nav-tabContent">
		<?php if (!isset($_GET['homework_code'])) { ?>
			<!-- start of class table -->
			<div class="table-responsive">
				<table id="dataTable1" width="100%" class="table table-striped table-lightfont">
					<thead>
						<tr>
							<th>
								<?php echo esc_html__('Title', 'sakolawp'); ?>
							</th>
							<th>
								<?php echo esc_html__('Subject', 'sakolawp'); ?>
							</th>
							<th>
								<?php echo esc_html__('Class', 'sakolawp'); ?>
							</th>
							<th>
								<?php echo esc_html__('Faculty', 'sakolawp'); ?>
							</th>
							<th>
								<?php echo esc_html__('Due Date', 'sakolawp'); ?>
							</th>
							<th class="text-center">
								<?php echo esc_html__('Action', 'sakolawp'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						global $wpdb;
						$homework = $wpdb->get_results("SELECT title, subject_id, class_id, section_id, uploader_id, date_end, homework_code, allow_peer_review, peer_review_template FROM {$wpdb->prefix}sakolawp_homework", OBJECT);
						foreach ($homework as $row) :
						?>
							<tr>
								<td>
									<?php echo esc_html($row->title); ?>
								</td>
								<td>
									<?php
									$subject = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = $row->subject_id");
									echo esc_html($subject->name);
									$allow_peer_review = $row->allow_peer_review;
									echo $allow_peer_review ? '<br/> <span class="btn nc btn-rounded btn-sm btn-success skwp-btn">peer reviewable</span>' : ""; ?>
								</td>
								<td>
									<?php
									global $wpdb;
									$classes = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $row->class_id");
									echo esc_html($classes->name);
									$section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $row->section_id");
									if ($section) {
										echo '-' . esc_html($section->name);
									} ?>
								</td>
								<td>
									<?php
									$user_info = get_userdata($row->uploader_id);
									echo esc_html($user_info->display_name); ?>
								</td>
								<td>
									<?php
									echo esc_html($row->date_end); ?>
								</td>
								<td>
									<a class="btn skwp-btn btn-sm btn-info" href="<?php echo add_query_arg(array('homework_code' => $row->homework_code, 'action' => 'homeworkroom'), admin_url('admin.php?page=sakolawp-homework')); ?>">
										<span><?php echo esc_html__('View', 'sakolawp'); ?></span>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<!-- end of class table -->
		<?php } ?>

		<?php if (isset($_GET['homework_code'])) {
			$action = $_GET['action'];
			$homework_code = $_GET['homework_code']; ?>

			<?php if ($action == 'homeworkroom') {

				$current_homework = $wpdb->get_results("SELECT title, date_end, time_end, description, file_name, subject_id, class_id, section_id, uploader_id,peer_review_template,allow_peer_review FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);
				foreach ($current_homework as $row) : ?>

					<div class="skwp-tab-menu">
						<ul class="nav nav-tabs upper">
							<li class="nav-item">
								<a class="nav-link <?php if ($action == 'homeworkroom') {
														echo 'active';
													} ?>" href="<?php echo add_query_arg(array('homework_code' => $homework_code, 'action' => 'homeworkroom'), admin_url('admin.php?page=sakolawp-homework')); ?>"><?php echo esc_html__('Assessment', 'sakolawp'); ?></span></a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php if ($action == 'homeworkroom_details') {
														echo 'active';
													} ?>" href="<?php echo add_query_arg(array('homework_code' => $homework_code, 'action' => 'homeworkroom_details'), admin_url('admin.php?page=sakolawp-homework')); ?>"><span><?php echo esc_html__('Submitted Assessment', 'sakolawp'); ?></span></a>
							</li>
						</ul>
					</div>

					<div class="back hidden-sm-down">
						<a href="<?php echo esc_url(admin_url('admin.php?page=sakolawp-homework')); ?>"><i class="os-icon os-icon-common-07"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
					</div>

					<div class="skwp-row skwp-clearfix">
						<div class="skwp-column skwp-column-1">
							<div class="homework-wrap">
								<h5 class="homework-name">
									<?php echo esc_html($row['title']); ?>
								</h5>
								<div class="homework-header-numbers">
									<div class="homework-count">
										<i class="os-icon picons-thin-icon-thin-0024_calendar_month_day_planner_events"></i> <?php echo esc_html($row['date_end']); ?> <br>
										<i class="os-icon picons-thin-icon-thin-0025_alarm_clock_ringer_time_morning"></i> <?php echo esc_html($row['time_end']); ?>
									</div>
								</div>
								<p>
									<?php echo esc_html($row['description']); ?>
								</p>
								<?php if ($row['file_name'] != "") :
									$url_file = site_url() . '/wp-content/uploads/sakolawp/homework/' . $row['file_name']; ?>
									<div class="b-t padded-v-big homework-attachment">
										<?php echo esc_html__('File:', 'sakolawp'); ?> <a class="btn btn-rounded btn-sm skwp-btn btn-primary" href="<?php echo esc_url($url_file); ?>"><i class="os-icon picons-thin-icon-thin-0042_attachment"></i> <?php echo esc_html__('Download Attachment', 'sakolawp'); ?></a>
									</div>
								<?php endif; ?>
								<div class="b-t padded-v-big homework-attachment">
									<?php echo esc_html__('Send At:', 'sakolawp'); ?> <a class="btn nc btn-rounded btn-sm skwp-btn btn-success"><?php echo esc_html($row['date_end']); ?></a>
								</div>
							</div>
						</div>

						<div class="skwp-column skwp-column-1 homework-info">
							<div class="skwp-header">
								<h5 class="skwp-form-header">
									<?php echo esc_html__('Assessment Information', 'sakolawp'); ?>
								</h5>
							</div>
							<div class="table-responsive">
								<table class="table table-lightbor text-left table-lightfont">
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
											echo esc_html($user_name); ?>
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

									if (isset($peer_review_template)) {
									?>
										<tr>
											<th>
												<?php echo esc_html__('Peer Review Template', 'sakolawp'); ?>
											</th>
											<td>
												<?php
												echo $peer_review_template;
												?>
											</td>
										</tr>
									<?php } ?>
								</table>
							</div>
						</div>
					</div>
			<?php endforeach;
			} ?>

			<?php if ($action == 'homeworkroom_details') {

				$current_homework = $wpdb->get_results("SELECT date_end, time_end, subject_id, class_id, section_id, uploader_id,peer_review_template,allow_peer_review FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);
				foreach ($current_homework as $row) : ?>

					<div class="skwp-tab-menu">
						<ul class="nav nav-tabs upper">
							<li class="nav-item">
								<a class="nav-link <?php if ($action == 'homeworkroom') {
														echo 'active';
													} ?>" href="<?php echo add_query_arg(array('homework_code' => $homework_code, 'action' => 'homeworkroom'), admin_url('admin.php?page=sakolawp-homework')); ?>"><?php echo esc_html__('Assessment', 'sakolawp'); ?></span></a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php if ($action == 'homeworkroom_details') {
														echo 'active';
													} ?>" href="<?php echo add_query_arg(array('homework_code' => $homework_code, 'action' => 'homeworkroom_details'), admin_url('admin.php?page=sakolawp-homework')); ?>"><span><?php echo esc_html__('Submitted Assessment', 'sakolawp'); ?></span></a>
							</li>
						</ul>
					</div>

					<div class="back hidden-sm-down">
						<a href="<?php echo esc_url(admin_url('admin.php?page=sakolawp-homework')); ?>"><i class="os-icon os-icon-common-07"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
					</div>

					<div class="skwp-row skwp-clearfix">
						<div class="skwp-column skwp-column-1">
							<table id="dataTable1" class="table table-lightborder">
								<thead>
									<tr>
										<th><?php echo esc_html__('Student', 'sakolawp'); ?></th>
										<th><?php echo esc_html__('Student Comment', 'sakolawp'); ?></th>
										<th><?php echo esc_html__('Delivery Status', 'sakolawp'); ?></th>
										<th><?php echo esc_html__('Assessment Detail', 'sakolawp'); ?></th>
										<th><?php echo esc_html__('Faculty Comment', 'sakolawp'); ?></th>
										<th style="width:50px"><?php echo esc_html__('Mark', 'sakolawp'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$time1 = $row['date_end'];
									$time2 = $row['time_end'];
									$time = $time1 . " " . $time2;
									$homework_details = $wpdb->get_results("SELECT student_id, student_comment, date, file_name, teacher_comment, delivery_id, mark FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code'", ARRAY_A);
									foreach ($homework_details as $row2) :
									?>
										<tr>
											<td style="min-width:170px">
												<?php
												$student_id = $row2["student_id"];
												$student = $wpdb->get_row("SELECT display_name FROM wp_users WHERE id = '$student_id'", ARRAY_A);
												echo esc_html($student['display_name']);
												?>
											</td>
											<td><?php echo esc_html($row2['student_comment']); ?></td>
											<td>
												<?php if (strtotime($row2['date']) > strtotime($time)) : ?>
													<a class="btn nc btn-rounded btn-sm skwp-btn btn-danger"><?php echo esc_html__('Late', 'sakolawp'); ?></a>
												<?php endif; ?>
												<?php if (strtotime($row2['date']) <= strtotime($time)) : ?>
													<a class="btn nc btn-rounded btn-sm skwp-btn btn-success"><?php echo esc_html__('On Time', 'sakolawp'); ?></a>
												<?php endif; ?>
											</td>
											<td>
												<a class="btn btn-rounded btn-sm skwp-btn btn-primary" href="<?php echo add_query_arg(array('homework_code' => $homework_code, 'action' => 'homeworkroom_details', 'student_id' => $row2['student_id'], 'read' => 'read'), admin_url('admin.php?page=sakolawp-homework')); ?>"><?php echo esc_html__('View Details', 'sakolawp'); ?></a>
											</td>
											<td>
												<textarea class="form-control" name="comment[]" rows="1" disabled><?php echo esc_textarea($row2['teacher_comment']); ?></textarea>
												<input type="hidden" name="answer_id[]" value="<?php echo esc_attr($row2['delivery_id']); ?>" disabled>
											</td>
											<td width="7%" class="mark-homework">
												<input class="form-control" required name="mark[]" type="text" value="<?php echo esc_attr($row2['mark']); ?>" disabled>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<div class="skwp-column skwp-column-1 homework-info">
							<div class="skwp-header">
								<h5 class="skwp-form-header">
									<?php echo esc_html__('Assessment Information', 'sakolawp'); ?>
								</h5>
							</div>
							<div class="table-responsive">
								<table class="table table-lightbor text-left table-lightfont">
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
											echo esc_html($user_name); ?>
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

									if (isset($peer_review_template)) {
									?>
										<tr>
											<th>
												<?php echo esc_html__('Peer Review Template', 'sakolawp'); ?>
											</th>
											<td>
												<?php
												echo $peer_review_template;
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
			}
		} ?>
	</div>
</div>