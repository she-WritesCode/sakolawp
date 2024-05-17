<?php

defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

global $wpdb;

$running_year = get_option('running_year');

$parent_id = get_current_user_id();

$student_id = get_user_meta($parent_id, 'related_student', true);

$homework_code = $_GET['homework_code'];

$enroll = $wpdb->get_row("SELECT class_id, section_id  FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");

if (!empty($enroll)) :

	$user_info = get_userdata($student_id);
	$student_name = $user_info->display_name;

?>

	<div class="homeworkroom-inner skwp-content-inner skwp-clearfix">
		<?php
		$theDate = 'date';
		$current_homework = $wpdb->get_results("SELECT title, date_end, time_end, description, file_name, subject_id, class_id, section_id, uploader_id FROM {$wpdb->prefix}sakolawp_homework 
	WHERE homework_code = '$homework_code'", ARRAY_A);
		foreach ($current_homework as $row) : ?>
			<?php $query = $wpdb->get_row("SELECT mark, date, homework_reply, file_name FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code' AND student_id = '$student_id'", ARRAY_A);
			$ada_nilai = $wpdb->get_results("SELECT mark FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code' AND student_id = '$student_id'", ARRAY_A);
			if ($wpdb->num_rows > 0) {
				$ada_nilai = $ada_nilai[0]['mark'];
			} else {
				$ada_nilai = NULL;
			} ?>

			<div class="back skwp-back hidden-sm-down">
				<a href="<?php echo esc_url(site_url('homework')); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php esc_html_e('Back', 'sakolawp'); ?></a>
			</div>

			<div class="skwp-row">
				<div class="skwp-column skwp-column-60">
					<div class="pipeline white lined-primary diskusi-desc">
						<div class="pipeline-header">
							<h5 class="pipeline-name">
								<?php echo esc_html($row['title']); ?>
							</h5>
							<div class="pipeline-header-numbers">
								<div class="pipeline-count">
									<?php echo esc_html($row['date_end']); ?> <br>
									<?php echo esc_html($row['time_end']); ?>
								</div>
							</div>
						</div>
						<p>
							<?php echo esc_html($row['description']); ?>
						</p>
						<?php if ($row['file_name'] != "") :
							$url_file = site_url() . '/wp-content/uploads/sakolawp/homework/' . $row['file_name']; ?>
							<div class="b-t padded-v-big homework-attachment">
								<?php esc_html_e('Files : ', 'sakolawp'); ?>
								<a class="btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo esc_url($url_file); ?>" target="_blank">
									<?php esc_html_e('Download Attachment', 'sakolawp'); ?>
								</a>
							</div>
						<?php endif; ?>

						<?php if (!empty($query)) : ?>
							<div class="homework-stat-button skwp-row no-margin">
								<span class="btn skwp-btn btn-success"><strong></strong><?php echo esc_html__('Homework Delivered', 'sakolawp'); ?></span>
							</div>
						<?php else : ?>
							<div class="homework-stat-button skwp-row no-margin">
								<span class="btn skwp-btn btn-danger"><strong></strong> <?php echo esc_html__('Homework Not Delivered', 'sakolawp'); ?></span>
							</div>
						<?php endif; ?>

						<?php if (!empty($query)) { ?>
							<div class="student-homework-ans skwp-clearfix skwp-mt-20">
								<?php
								$tugas_ada = $query;
								if (count($query) > 0) : ?>
									<h6 class="homework-detail-title">
										<?php esc_html_e('Student Answer', 'sakolawp'); ?>
									</h6>
									<div class="student-homework-text">
										<?php echo esc_html($tugas_ada["homework_reply"]); ?>
									</div>

									<?php if ($tugas_ada["file_name"] != "") {
										$url_file = site_url() . '/wp-content/uploads/sakolawp/deliveries/' . $tugas_ada["file_name"];
										$url_file = str_replace(' ', '-', $url_file); ?>
										<a class="download-delivery-attachment btn btn-rounded btn-sm btn-primary skwp-btn skwp-mt-20" href="<?php echo esc_url($url_file); ?>" target="_blank">
											<?php esc_html_e('Download File', 'sakolawp'); ?>
										</a>
									<?php } ?>
								<?php endif; ?>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="skwp-column skwp-column-40">
					<div class="pipeline white lined-secondary">
						<div class="pipeline-header">
							<h5 class="skwp-title">
								<?php esc_html_e('Information', 'sakolawp'); ?>
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
										echo esc_html($user_name); ?>
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
									<th>
										<?php esc_html_e('Parent Group:', 'sakolawp'); ?>
									</th>
									<td>
										<?php
										$section_id = $row["section_id"];
										$section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = '$section_id'", ARRAY_A);
										echo esc_html($section['name']);
										?>
									</td>
									</td>
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
										<?php if (count($query) <= 0) : ?>
											<a class="btn nc btn-rounded btn-sm skwp-btn btn-danger"><?php esc_html_e('Not Delivered', 'sakolawp'); ?></a>
										<?php endif; ?>
										<?php if (count($query) > 0) : ?>
											<a class="btn nc btn-rounded btn-sm skwp-btn btn-success"><?php esc_html_e('Delivered', 'sakolawp'); ?></a>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th>
										<?php esc_html_e('Mark:', 'sakolawp'); ?>
									</th>
									<td>
										<?php if (count($query) < 0) : ?>
											<a class="btn btn-rounded btn-sm skwp-btn btn-danger"><?php esc_html_e('Not Marked', 'sakolawp'); ?></a>
										<?php endif; ?>
										<?php if (count($query) > 0) : ?>
											<?php if ($ada_nilai != NULL) { ?>
												<a class="btn btn-rounded btn-sm skwp-btn btn-primary"><?php echo esc_html($ada_nilai); ?></a>
											<?php } else {
												esc_html_e('On Review', 'sakolawp');
											} ?>
										<?php
										endif; ?>
									</td>
								</tr>
								<?php if (!empty($ada_nilai_main[0]["teacher_comment"])) { ?>
									<tr>
										<th>
											<?php esc_html_e('Faculty Comment:', 'sakolawp'); ?>
										</th>
										<td>
											<?php echo esc_html($ada_nilai_main[0]["teacher_comment"]); ?>
										</td>
									</tr>
								<?php } ?>
							</table>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php
else :
	esc_html_e('Your child not assign to a class yet', 'sakolawp');
endif;

do_action('sakolawp_after_main_content');
get_footer();
