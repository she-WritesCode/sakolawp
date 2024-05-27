<?php
defined('ABSPATH') || exit;

global $wpdb;
$peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';
$homework_table = $wpdb->prefix . 'sakolawp_homework';
$deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';

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
		$message = " Successful";
		die('<div class="alert ' . $alert_class . '" role="alert">' . $message . '</div>');
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
				'student_comment' => $student_comment,
				'homework_reply' => $homework_reply
			),
			array(
				'homework_code' => $homework_code,
				'student_id' => $student_id,
			)
		);

		wp_redirect(add_query_arg(array('homework_code' => $homework_code), home_url('homeworkroom')));
		$message = " Successful";
		$alert_class = "success";
		die('<div class="alert ' . $alert_class . '" role="alert">' . $message . '</div>');
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
	$current_homework = $wpdb->get_results("SELECT title, date_end, time_end, description, file_name, class_id, section_id, subject_id, uploader_id, homework_id, allow_peer_review,word_count_min,word_count_max FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);

	$homework_deliveries = $wpdb->get_results("SELECT mark, teacher_comment FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code' AND student_id = '$student_id'", ARRAY_A);
	if ($wpdb->num_rows > 0) {
		$mark = $homework_deliveries[0]['mark'];
	} else {
		$mark = NULL;
	}
	foreach ($current_homework as $row) :
		$homework_id = $row['homework_id'];

		// Get peer reviews for the current user and the specific homework
		$peer_reviews = $wpdb->get_results($wpdb->prepare(
			"SELECT pr.*, d.delivery_id, h.title, h.peer_review_template
				FROM $peer_reviews_table pr 
				JOIN $deliveries_table d ON pr.delivery_id = d.delivery_id 
				JOIN $homework_table h ON pr.homework_id = h.homework_id 
				WHERE pr.peer_id = %d AND pr.homework_id = %d",
			$student_id,
			$homework_id
		));
?>
		<?php

		$query = $wpdb->get_results("SELECT homework_code,student_comment,homework_reply,file_name, date FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homework_code' AND student_id = '$student_id'", ARRAY_A);
		$time_end = $row['time_end'];
		$date_end = $row['date_end'];
		$homework_due_date = $row['date_end'] . ' ' . $row['time_end'];
		$today = date('d-m-Y', time());
		$is_late =  strtotime($today) > strtotime($homework_due_date);
		$has_been_marked = $mark !== NULL || count($peer_reviews) > 0;
		$word_count_min = $row['word_count_min'];
		$word_count_max = $row['word_count_max'];
		$should_calculate_word_count = $word_count_min && $word_count_max;

		?>
		<div class="homeworkroom-inner homeworkroom-page skwp-content-inner skwp-clearfix">

			<?php
			// Only display menu for peer reviewable homeworks
			$allow_peer_review = $row['allow_peer_review'];
			if ($allow_peer_review) :
			?>
				<div class="skwp-tab-menu">
					<ul class="skwp-tab-wrap">
						<li class="skwp-tab-items active">
							<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $homework_code, home_url('homeworkroom')); ?>">
								<span><?php echo esc_html__('Homework', 'sakolawp'); ?></span>
							</a>
						</li>
						<li class="skwp-tab-items">
							<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $homework_code, home_url('homeworkroom_details')); ?>">
								<span><?php echo esc_html__('Peer Reviews Reports', 'sakolawp') . ' (' . count($peer_reviews) . ')'; ?></span>
							</a>
						</li>
					</ul>
				</div>
			<?php endif; ?>

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
									<b>
										<i class="os-icon picons-thin-icon-thin-0024_calendar_month_day_planner_events"></i>
										Due Date:
										<?php echo esc_html($row['date_end']); ?> <?php echo esc_html($row['time_end']); ?></b><br>

								</div>
							</div>

							<?php
							if ($is_late && !$has_been_marked) :
								$action = (count($query) > 0) ? 'update' : 'submit';
							?>
								<div class="">
									<div class="btn btn-danger skwp-btn">
										<?php esc_html_e('Sorry you cannot ' . $action . ' this homework any longer. The deadline was', 'sakolawp'); ?>
										<span class="skwp-date" data-end-date="<?php echo esc_html($date_end); ?>" data-end-time="<?php echo esc_html($time_end); ?>"></span>
									</div>
								</div>
							<?php endif; ?>

							<?php if ($has_been_marked) :
								$reason = count($peer_reviews) > 0 ? "Your peers have started reviewing" : "Your assignment has been graded.";
							?>
								<div class="">
									<div class="btn btn-warning skwp-btn">
										<?php esc_html_e('Sorry you cannot update this homework any longer. ' . $reason, 'sakolawp'); ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
						<?php if (isset($row['description'])) : ?>
							<div class="my-4">
								<h6 class="mb-0">Instructions:</h6>
								<div>
									<?php echo esc_html($row['description']); ?>
								</div>
							</div>
						<? endif; ?>
						<?php if ($row['file_name'] != "") :
							$url_file = site_url() . '/wp-content/uploads/sakolawp/homework/' . $row['file_name'];
							$url_file = str_replace(' ', '-', $url_file); ?>
							<div class="b-t padded-v-big homework-attachment">
								<?php esc_html_e('Files : ', 'sakolawp'); ?><a class="btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo esc_url($url_file); ?>" target="_blank"><i class="os-icon picons-thin-icon-thin-0042_attachment"></i><?php esc_html_e('Download Attachment', 'sakolawp'); ?></a>
							</div>
						<?php endif; ?>
						<?php if (count($query) <= 0) : ?>
							<form id="myForm" name="myform" action="" method="POST" enctype="multipart/form-data">
								<?php if (!$should_calculate_word_count) : ?>
									<textarea cols="80" id="editordatamurid" required="" name="reply" rows="10" <?php echo ($is_late) ? 'readonly' : ''; ?>></textarea>
								<?php else : ?>
									<textarea class="word-count" data-min-word-count="<?php echo $word_count_min; ?>" data-max-word-count="<?php echo $word_count_max; ?>" cols="80" id="editordatamurid" required="" name="reply" rows="10" <?php echo ($is_late) ? 'readonly' : ''; ?>></textarea>
									<div class="flex justify-between">
										<p id="word-count">Word Count: 0</p>
										<p>Min: <?php echo $word_count_min; ?> | Max: <?php echo $word_count_max; ?> </p>
									</div>
								<?php endif; ?>
								<br>
								<br>

								<input class="form-control" id="file-3" name="file_name" type="file" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" multiple="false" <?php echo ($is_late) ? 'readonly' : ''; ?> />
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
											<textarea class="form-control" placeholder="<?php esc_html_e('Your Comment', 'sakolawp'); ?>" name="comment" rows="1" <?php echo ($is_late) ? 'readonly' : ''; ?>></textarea>
										</div>
									</div>
									<div class="skwp-column skwp-column-20">
										<?php if (!$is_late) : ?>
											<div class="form-buttons skwp-form-button">
												<button class="btn btn-primary skwp-btn" type="submit" name="submit" value="submit"><?php esc_html_e('Send', 'sakolawp'); ?></button>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</form>
						<?php endif; ?>

						<!-- Tugas Sudah Dikirim -->
						<?php
						$tugas_ada = $query;
						if (count($query) > 0) : ?>

							<form class="update-delivery" id="myForm" name="myform" action="" method="POST" enctype="multipart/form-data">
								<?php if (!$should_calculate_word_count) : ?>
									<textarea cols="80" id="editordatamurid" required="" name="reply" rows="10" <?php echo ($is_late) ? 'readonly' : ''; ?>><?php echo $tugas_ada[0]["homework_reply"]; ?></textarea>
								<?php else : ?>
									<textarea class="word-count" data-min-word-count="<?php echo $word_count_min; ?>" data-max-word-count="<?php echo $word_count_max; ?>" cols="80" id="editordatamurid" required="" name="reply" rows="10" <?php echo ($is_late) ? 'readonly' : ''; ?>><?php echo $tugas_ada[0]["homework_reply"]; ?></textarea>
									<div class="flex justify-between">
										<p id="word-count">Word Count: 0</p>
										<p>Min: <?php echo $word_count_min; ?> | Max: <?php echo $word_count_max; ?> </p>
									</div>
								<?php endif; ?>
								<?php if ($tugas_ada[0]["file_name"] != "") {
									$url_file = site_url() . '/wp-content/uploads/sakolawp/deliveries/' . $tugas_ada[0]["file_name"];
									$url_file = str_replace(' ', '-', $url_file); ?>
									<a class="download-delivery-attachment btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo esc_url($url_file); ?>" target="_blank">
										<?php esc_html_e('Download File', 'sakolawp'); ?>
									</a>
								<?php } ?>
								<input class="form-control" id="file-3" name="file_name" type="file" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" multiple="false" <?php echo ($is_late) ? 'readonly' : ''; ?> />
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
									<div class="skwp-column skwp-column-60">
										<div class="form-group">
											<textarea class="form-control" placeholder="<?php esc_html_e('Your Comment', 'sakolawp'); ?>" name="comment" rows="4" <?php echo ($is_late) ? 'readonly' : ''; ?>><?php echo esc_html($tugas_ada[0]["student_comment"]); ?></textarea>
										</div>
									</div>
									<?php if ($mark === NULL) { ?>
										<div class="skwp-column skwp-column-40">
											<?php if (!$is_late) : ?>
												<div class="form-buttons skwp-form-button">
													<button class="btn btn-rounded btn-primary skwp-btn" type="submit" name="submit" value="submit"><?php esc_html_e('Update', 'sakolawp'); ?></button>
												</div>
											<?php endif; ?>
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
											<?php if ($mark != NULL) { ?>
												<a class="btn btn-rounded btn-sm skwp-btn btn-primary"><?php echo esc_html($mark); ?></a>
											<?php } elseif ($row['allow_peer_review']) {
												$score = 0;
												foreach ($peer_reviews as $review) {
													$score += $review->mark;
												}
												$mean_score = $score / count($peer_reviews);
												echo esc_attr(round($mean_score, 2));
												echo esc_html_e(' (Peer Reviewed)', 'sakolawp');
											} else {
												esc_html_e('On Review', 'sakolawp');
											} ?>
										<?php endif; ?>
									</td>
								</tr>
								<?php
								if (isset($homework_deliveries) && isset($homework_deliveries[0]) && $homework_deliveries[0]["teacher_comment"] != NULL) { ?>
									<tr>
										<th>
											<?php esc_html_e('Faculty Comment', 'sakolawp'); ?>
										</th>
										<td>
											<?php echo esc_html($homework_deliveries[0]["teacher_comment"]); ?>
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
	esc_html_e('No homework has been assigned for your class yet', 'sakolawp');
endif;

do_action('sakolawp_after_main_content');
get_footer();
