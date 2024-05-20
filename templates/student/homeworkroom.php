<?php
defined('ABSPATH') || exit;

global $wpdb;

if (isset($_POST['submit'])) {
	$homework_act = $_POST['action'];
	$homework_type = $_POST['homework_type'];

	if ($homework_act == "insert_homework") {
		//$_POST = array_map( 'stripslashes_deep', $_POST );
		$homework_code = sanitize_text_field($_POST['homework_code']);
		$student_id    = sanitize_text_field($_POST['student_id']);
		$date          = sanitize_text_field(current_time('m/d/Y H:i'));
		$class_id      = sanitize_text_field($_POST['class_id']);
		$section_id    = sanitize_text_field($_POST['section_id']);
		$homework_reply =  sakolawp_sanitize_html($_POST['reply']);
		$file_name =  $_FILES['file_name']['name'];
		$student_comment = sakolawp_sanitize_html($_POST['comment']);
		$subject_id = sanitize_text_field($_POST['subject_id']);
		$post_id = sanitize_text_field($_POST['post_id']);
		$status = sanitize_text_field('1');

		$wpdb->insert(
			$wpdb->prefix . 'sakolawp_deliveries',
			array(
				'homework_code' => $homework_code,
				'student_id' => $student_id,
				'date' => $date,
				'class_id' => $class_id,
				'section_id' => $section_id,
				'file_name' => $file_name,
				'homework_reply' => $homework_reply,
				'student_comment' => $student_comment,
				'subject_id' => $subject_id,
				'status' => $status
			)
		);

		add_filter('upload_dir', 'sakolawp_custom_dir_deliveries');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		$attach_id = media_handle_upload('file_name', $post_id);
		if (is_numeric($attach_id)) {
			update_option('deliveries_homework_student', $attach_id);
			update_post_meta($post_id, '_file_name', $attach_id);
		}
		remove_filter('upload_dir', 'sakolawp_custom_dir_deliveries');

		wp_redirect(add_query_arg(array('homework_code' => $homework_code), home_url('homeworkroom')));
		die;
	}

	if ($homework_act == "update_homework") {
		//$_POST = array_map( 'stripslashes_deep', $_POST );
		$homework_code = sanitize_text_field($_POST['homework_code']);
		$student_id    = sanitize_text_field($_POST['student_id']);
		$homework_reply =  sakolawp_sanitize_html($_POST['reply']);
		$file_name =  $_FILES['file_name']['name'];
		$student_comment = sakolawp_sanitize_html($_POST['comment']);
		$post_id = sanitize_text_field($_POST['post_id']);

		add_filter('upload_dir', 'sakolawp_custom_dir_deliveries');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		$attach_id = media_handle_upload('file_name', $post_id);
		if (is_numeric($attach_id)) {
			update_option('deliveries_homework_student', $attach_id);
			update_post_meta($post_id, '_file_name', $attach_id);
		}
		remove_filter('upload_dir', 'sakolawp_custom_dir_deliveries');

		$wpdb->update(
			$wpdb->prefix . 'sakolawp_deliveries',
			array(
				'file_name' => $file_name,
				'student_comment' => $student_comment
			),
			array(
				'homework_code' => $homework_code,
				'student_id' => $student_id,
			)
		);

		wp_redirect(add_query_arg(array('homework_code' => $homework_code), home_url('homeworkroom')));
		die;
	}
}

get_header();
do_action('sakolawp_before_main_content');

$running_year = get_option('running_year');

$student_id = get_current_user_id();

$enroll = $wpdb->get_row("SELECT class_id, section_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");

if (!empty($enroll)) :

	$user_info = get_userdata($student_id);
	$student_name = $user_info->display_name;

	$homework_code = $_GET['homework_code'];
	$current_homework = $wpdb->get_results("SELECT title, date_end, time_end, description, file_name, class_id, section_id, subject_id, uploader_id FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);

	$ada_nilai_main = $wpdb->get_results("SELECT mark, teacher_comment FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code' AND student_id = '$student_id'", ARRAY_A);
	if ($wpdb->num_rows > 0) {
		$ada_nilai = $ada_nilai_main[0]['mark'];
	} else {
		$ada_nilai = NULL;
	}
	foreach ($current_homework as $row) :

?>
		<?php $query = $wpdb->get_results("SELECT homework_code,student_comment,homework_reply,file_name FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code' AND student_id = '$student_id'", ARRAY_A); ?>
		<div class="homeworkroom-inner homeworkroom-page skwp-content-inner skwp-clearfix">

			<div class="back skwp-back hidden-sm-down">
				<a href="<?php echo esc_url(site_url('homework')); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php esc_html_e('Back', 'sakolawp'); ?></a>
			</div>

			<div class="skwp-row">
				<div class="skwp-column skwp-column-1">
					<div class="pipeline white lined-primary shadow diskusi-desc">
						<div class="pipeline-header">
							<h5 class="pipeline-name">
								<?php echo esc_html($row['title']); ?>
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
						<p>
							<?php echo esc_html($row['description']); ?>
						</p>
						<?php if ($row['file_name'] != "") :
							$url_file = site_url() . '/wp-content/uploads/sakolawp/homework/' . $row['file_name'];
							$url_file = str_replace(' ', '-', $url_file); ?>
							<div class="b-t padded-v-big homework-attachment">
								<?php esc_html_e('Files : ', 'sakolawp'); ?><a class="btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo esc_url($url_file); ?>" target="_blank"><i class="os-icon picons-thin-icon-thin-0042_attachment"></i><?php esc_html_e('Download Attachment', 'sakolawp'); ?></a>
							</div>
						<?php endif; ?>
						<?php if (count($query) <= 0) : ?>
							<form id="myForm" name="myform" action="" method="POST" enctype="multipart/form-data">
								<textarea cols="80" id="editordatamurid" required="" name="reply" rows="10"></textarea>
								<br>
								<br>

								<input class="form-control" id="file-3" name="file_name" type="file" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" multiple="false" />
								<input type="hidden" name="post_id" id="post_id" value="<?php echo esc_attr($homework_code); ?>" />
								<?php wp_nonce_field('file_name', 'file_name_nonce'); ?>
								<p style="font-style: italic; margin: 20px 0;"><?php esc_html_e('Max file size up to 10MB', 'sakolawp'); ?></p>
								<input type="hidden" name="action" value="insert_homework" />
								<input type="hidden" name="homework_type" value="file">
								<input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
								<input type="hidden" name="homework_code" value="<?php echo esc_attr($homework_code); ?>">
								<input type="hidden" name="class_id" value="<?php echo esc_attr($row['class_id']); ?>">
								<input type="hidden" name="section_id" value="<?php echo esc_attr($row['section_id']); ?>">
								<input type="hidden" name="subject_id" value="<?php echo esc_attr($row['subject_id']); ?>">
								<div class="skwp-row">
									<div class="skwp-column skwp-column-80">
										<div class="form-group">
											<textarea class="form-control" placeholder="<?php esc_html_e('Your Comment', 'sakolawp'); ?>" name="comment" rows="1"></textarea>
										</div>
									</div>
									<div class="skwp-column skwp-column-20">
										<div class="form-buttons skwp-form-button">
											<button class="btn btn-primary skwp-btn" type="submit" name="submit" value="submit"><?php esc_html_e('Send', 'sakolawp'); ?></button>
										</div>
									</div>
								</div>
							</form>
						<?php endif; ?>

						<!-- Tugas Sudah Dikirim -->
						<?php
						$tugas_ada = $query;
						if (count($query) > 0) : ?>
							<form class="update-delivery" id="myForm" name="myform" action="" method="POST" enctype="multipart/form-data">
								<textarea cols="80" id="editordatamurid" required="" name="reply" rows="10"><?php echo $tugas_ada[0]["homework_reply"]; ?></textarea>
								<?php if ($tugas_ada[0]["file_name"] != "") {
									$url_file = site_url() . '/wp-content/uploads/sakolawp/deliveries/' . $tugas_ada[0]["file_name"];
									$url_file = str_replace(' ', '-', $url_file); ?>
									<a class="download-delivery-attachment btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo esc_url($url_file); ?>" target="_blank">
										<?php esc_html_e('Download File', 'sakolawp'); ?>
									</a>
								<?php } ?>
								<input class="form-control" id="file-3" name="file_name" type="file" required="" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" multiple="false" />
								<input type="hidden" name="post_id" id="post_id" value="<?php echo esc_attr($homework_code); ?>" />
								<?php wp_nonce_field('file_name', 'file_name_nonce'); ?>
								<p style="font-style: italic; margin: 20px 0;"><?php esc_html_e('Max file size up to 10MB', 'sakolawp'); ?></p>
								<input type="hidden" name="action" value="update_homework" />
								<input type="hidden" name="homework_type" value="file">
								<input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
								<input type="hidden" name="homework_code" value="<?php echo esc_attr($homework_code); ?>">
								<input type="hidden" name="class_id" value="<?php echo esc_attr($row['class_id']); ?>">
								<input type="hidden" name="section_id" value="<?php echo esc_attr($row['section_id']); ?>">
								<input type="hidden" name="subject_id" value="<?php echo esc_attr($row['subject_id']); ?>">
								<div class="skwp-row">
									<div class="skwp-column skwp-column-80">
										<div class="form-group">
											<textarea class="form-control" placeholder="<?php esc_html_e('Your Comment', 'sakolawp'); ?>" name="comment" rows="4"><?php echo esc_html($tugas_ada[0]["student_comment"]); ?></textarea>
										</div>
									</div>
									<?php if ($ada_nilai === NULL) { ?>
										<div class="skwp-column skwp-column-20">
											<div class="form-buttons skwp-form-button">
												<button class="btn btn-rounded btn-primary skwp-btn" type="submit" name="submit" value="submit"><?php esc_html_e('Update', 'sakolawp'); ?></button>
											</div>
										</div>
									<?php } ?>
								</div>
							</form>
						<?php endif; ?>

					</div>
				</div>

				<div class="skwp-column skwp-column-1 homework-info">
					<div class="skwp-content-sidebar">
						<div class="skwp-sidebar-title">
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
										<?php endif; ?>
									</td>
								</tr>
								<?php
								if (isset($ada_nilai_main) && isset($ada_nilai_main[0]) && $ada_nilai_main[0]["teacher_comment"] != NULL) { ?>
									<tr>
										<th>
											<?php esc_html_e('Faculty Comment', 'sakolawp'); ?>
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
		</div>

<?php
	endforeach;

else :
	esc_html_e('You have not create a homework for your class yet', 'sakolawp');
endif;

do_action('sakolawp_after_main_content');
get_footer();
