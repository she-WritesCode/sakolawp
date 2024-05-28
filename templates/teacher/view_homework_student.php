<?php
defined('ABSPATH') || exit;


$homeworkCode = $_GET['homework_code'];
$studentId = $_GET['student_id'];
$current_user_id = get_current_user_id();
$homework_table = $wpdb->prefix . 'sakolawp_homework';
$deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';
$peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';

global $wpdb;



if (isset($_POST['submit'])) {
	error_log("We submitted " . json_encode($_POST));
	$homework_act = $_POST['action'];

	if ($homework_act == "add_peer_review") {
		$_POST 	= array_map('stripslashes_deep', $_POST);
		$date   =  date("Y-m-d H:i:s");;
		$delivery_id =  sakolawp_sanitize_html($_POST['delivery_id']);
		$homework_id = sakolawp_sanitize_html($_POST['homework_id']);
		$homework_code = sakolawp_sanitize_html($_POST['homework_code']);
		$peer_id = sanitize_text_field($_POST['peer_id']);
		$reviewer_id = sanitize_text_field($_POST['reviewer_id']);
		$class_id = sanitize_text_field($_POST['class_id']);
		$section_id    = sanitize_text_field($_POST['section_id']);
		$accountability_id =  sakolawp_sanitize_html($_POST['accountability_id']);
		$subject_id = sakolawp_sanitize_html($_POST['subject_id']);
		$assessment = array_map('stripslashes_deep', $_POST['assessment']);
		$reviewer_comment = sakolawp_sanitize_html($_POST['reviewer_comment']);
		$reviewer_type = sanitize_text_field($_POST['reviewer_type']);

		$current_delivery = $wpdb->get_row("SELECT * 
		FROM $deliveries_table d
		JOIN $homework_table h ON d.homework_code = h.homework_code
		WHERE d.delivery_id = '$delivery_id';", ARRAY_A);

		require_once plugin_dir_path(__FILE__) . '../peer-reviews/' . $current_delivery['peer_review_template'] . '_assessment.php';
		$mark =  calculate_assessment_total_score($assessment, $form); // form is gotten from the require once file


		skwp_insert_or_update_record(
			$peer_reviews_table,
			[
				'date' => $date,
				'delivery_id' => (int)$delivery_id,
				'homework_id' => (int)$homework_id,
				'peer_id' => (int)$peer_id,
				'reviewer_id' => (int)$reviewer_id,
				'class_id' => $class_id,
				'section_id' => $section_id,
				'accountability_id' => $accountability_id,
				'subject_id' => $subject_id,
				'assessment' => json_encode($assessment),
				'mark' => $mark,
				'reviewer_comment' => $reviewer_comment,
				'reviewer_type' => $reviewer_type,
			],
			['delivery_id', 'reviewer_id'],
			"peer_review_id"
		);

		$wpdb->update(
			$wpdb->prefix . 'sakolawp_deliveries',
			array(
				'mark' => $mark,
				'teacher_comment' => $reviewer_comment,
			),
			array(
				'delivery_id' => $delivery_id
			)
		);
		wp_redirect(add_query_arg(array('homework_code' => $homework_code, 'student_id' => $studentId,  'form_submitted' => 'true'), home_url('view_homework_student')));
		die;
	}

	if ($homework_act == "add_teacher_comment") {
		$homework_code = sanitize_text_field($_POST['homework_code']);
		$delivery_id = sanitize_text_field($_POST['answer_id']);
		$mark = sanitize_text_field($_POST['mark']);
		$comment = sanitize_text_field($_POST['comment']);
		error_log("mark" . $mark);
		$wpdb->update(
			$wpdb->prefix . 'sakolawp_deliveries',
			array(
				'mark' => (int)$mark,
				'teacher_comment' => $comment,
			),
			array(
				'delivery_id' => $delivery_id
			)
		);

		wp_redirect(add_query_arg(array('homework_code' => $homework_code, 'student_id' => $studentId, 'form_submitted' => 'true'), home_url('view_homework_student')));

		die;
	}
}

get_header();

do_action('sakolawp_before_main_content');

$running_year = get_option('running_year');
$homeworks = $wpdb->get_results("SELECT homework_code, homework_id, title, date_end, time_end, description, file_name, subject_id, class_id, section_id, peer_review_template, allow_peer_review, peer_review_who FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homeworkCode'", ARRAY_A);
$homework_deliveries = $wpdb->get_results("SELECT homework_code,homework_reply, file_name,student_comment,delivery_id,teacher_comment,mark FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homeworkCode' AND student_id = $studentId", ARRAY_A);

foreach ($homework_deliveries as $row) :
	$current_homework = $homeworks[0];
	$user_info = get_user_meta($studentId);
	$first_name = $user_info["first_name"][0];
	$last_name = $user_info["last_name"][0];

	$user_name = $first_name . ' ' . $last_name;

	if (empty($first_name)) {
		$user_info = get_userdata($studentId);
		$user_name = $user_info->display_name;
	}

	$delivery_id = $row['delivery_id'];
	$peer_reviews = $wpdb->get_results("SELECT * FROM $peer_reviews_table WHERE delivery_id = '$delivery_id';", ARRAY_A);

	if ($current_homework['peer_review_template'] && count($peer_reviews) > 0) {
		require_once plugin_dir_path(__FILE__) . '../peer-reviews/' . $current_homework['peer_review_template'] . '_assessment.php';
	}

	$peer_review_who = $current_homework["peer_review_who"] == "teacher" ? "Faculty" : "Peer";

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
		<div class="skwp-clearfix skwp-row">
			<div class="skwp-column skwp-column-1">
				<div class="tugas-wrap">
					<div class="back skwp-back hidden-sm-down">
						<a href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom_details')); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
					</div>


					<?php do_action('sakolawp_show_alert_dialog') ?>
					<div class="bg-gray-50 px-4 py-2 rounded-lg my-4">
						<div class="student-info">
							<?php
							$user_img = wp_get_attachment_image_src(get_user_meta($studentId, '_user_img', array('80', '80'), true, true));
							if (!empty($user_img)) { ?>
								<img class="profile_img" src="<?php echo esc_url($user_img[0]); ?>" alt="<?php echo esc_attr($user_name); ?>">
							<?php } else {
								echo get_avatar($studentId, 60);
							} ?>
							<span><?php echo esc_html($user_name); ?></span>
						</div>
						<div class="homework-text">
							<h4><?php echo esc_html__('Student Answer :', 'sakolawp'); ?></h4>
							<p>
								<?php echo esc_html($row['homework_reply']); ?>
							</p>
						</div>
						<?php if ($row['file_name'] !== "") {
							$url_file = site_url() . '/wp-content/uploads/sakolawp/deliveries/' . $row['file_name'];
							$url_file = str_replace(' ', '-', $url_file); ?>
							<div class="homework-file">
								<h4><?php echo esc_html__('Student File :', 'sakolawp'); ?></h4>
								<a href="<?php echo esc_url($url_file); ?>" class="btn btn-download skwp-btn" target="_blank">
									<span><?php echo esc_html__('Download File', 'sakolawp'); ?></span>
									<span class="file-download"><?php echo esc_html($row['file_name']); ?></span>
								</a>
							</div>
						<?php } ?>
						<div class="homework-comment">
							<h4><?php echo esc_html__('Student Comment :', 'sakolawp'); ?></h4>
							<p>
								<?php echo !empty($row['student_comment']) ? esc_html($row['student_comment']) : "<i>No Comment</i>"; ?>
							</p>
						</div>
					</div>
					<?php if (count($peer_reviews) > 0) : ?>
						<div class="homework-peer-review">
							<h4><?php echo $peer_review_who . esc_html__(' Review :', 'sakolawp'); ?></h4>

							<div>
								<div style="width: 100%;" id="mean_review_summary" data-homework_code="<?php echo $homework_code; ?>" data-student_id="<?php echo $studentId; ?>"></div>
							</div>
							<div class="tugas-wrap">
								<table id="dataTable1" class="table table-lightborder">
									<thead>
										<tr>
											<th><?php echo esc_html__('Reviewer', 'sakolawp'); ?></th>
											<th><?php echo esc_html__('Mark', 'sakolawp'); ?></th>
											<th><?php echo esc_html__('Comment', 'sakolawp'); ?></th>
											<!-- <th><?php echo esc_html__('Action', 'sakolawp'); ?></th> -->
										</tr>
									</thead>
									<tbody>
										<?php
										$total_mark = 0;
										foreach ($peer_reviews as $peer_review) :
											$total_mark += (int)$peer_review['mark'];
											$response = json_decode($peer_review['assessment'], true); // Decode JSON as associative array
										?>
											<tr class="toggle-review">
												<td>
													<?php
													$reviewer_id = $peer_review["reviewer_id"];
													$reviewer = get_userdata($reviewer_id);
													$user_name = $reviewer->display_name;
													echo $user_name;
													?>
												</td>
												<td>
													<input class="form-control nilai" disabled required name="mark[]" type="number" min="1" max="100" maxlength="3" value="<?php echo esc_attr($peer_review['mark']); ?>">
												</td>
												<td>
													<?php echo esc_attr($peer_review['reviewer_comment']); ?>
												</td>
												<!-- <td>
													<a class="btn skwp-btn btn-small btn-primary"><?php echo esc_html__('Details', 'sakolawp'); ?></a>
												</td> -->
											</tr>
											<tr class="toggle-review-handle">
												<td colspan="4">
													<?php
													foreach (array_keys($response) as $question_id) :
														$question = [];
														foreach ($form['questions'] as $q) :
															if ($q['question_id'] == $question_id) {
																$question = $q;
																break;
															}
														endforeach;

													?>
														<div class="flex">
															<div class="w-2/3"><?php echo $question["question"] ?>:</div>
															<div class="w-1/3"> <?php echo $response[$question_id] ?></div>
															<!-- <div class="w-1/4"> <?php echo $question['score_percentage']; ?>%</div> -->
														</div>
													<?php endforeach; ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endif; ?>

					<?php
					if (count($peer_reviews) == 0 && $current_homework['peer_review_who'] == 'teacher') :
						$user_info = get_userdata($current_user_id);
						$current_peer_review = $peer_reviews;
						$peer_enroll = $wpdb->get_row("SELECT class_id, section_id, accountability_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $studentId");
						$peer_section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $peer_enroll->section_id");
						$peer_accountability = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_accountability WHERE accountability_id = $peer_enroll->accountability_id");

					?>
						<div class="skwp-column skwp-column-1">
							<form id="<?php echo $current_homework['peer_review_template'] . '_form'; ?>" method="POST" action="">
								<input type="hidden" name="action" value="add_peer_review" />

								<input type="hidden" name="delivery_id" value="<?php echo esc_attr($delivery_id); ?>">
								<input type="hidden" name="homework_id" value="<?php echo esc_attr($current_homework['homework_id']); ?>">
								<input type="hidden" name="homework_code" value="<?php echo esc_attr($current_homework['homework_code']); ?>">
								<input type="hidden" name="reviewer_id" value="<?php echo esc_attr($current_user_id); ?>">
								<input type="hidden" name="peer_id" value="<?php echo esc_attr($studentId); ?>">
								<input type="hidden" name="class_id" value="<?php echo esc_attr($peer_enroll->class_id); ?>">
								<input type="hidden" name="section_id" value="<?php echo esc_attr($peer_enroll->section_id); ?>">
								<input type="hidden" name="accountability_id" value="<?php echo esc_attr($peer_enroll->accountability_id); ?>">
								<input type="hidden" name="subject_id" value="<?php echo esc_attr($current_homework['subject_id']); ?>">
								<input type="hidden" name="reviewer_type" value="<?php echo esc_attr($user_info->roles[0]); ?>">

								<?php do_action('sakolawp_form_' . $current_homework['peer_review_template'] . '_assessment'); ?>

								<div class="flex flex-col gap-2 p-4 border bg-gray-50 mt-4">
									<div class="flex flex-col gap-2">
										<label class="font-medium"><?php echo esc_html("Any comments or observation"); ?></label>

										<textarea name="reviewer_comment"></textarea>
									</div>
								</div>
								<button class="btn btn-success skwp-btn" type="submit" name="submit" value="submit">Submit</button>
							</form>
						</div>
					<?php endif; ?>

					<?php if ($current_homework['peer_review_who'] !== 'teacher') : ?>
						<div class="teacher-comment">
							<form id="myForm" name="save_marks_homework" action="" method="POST">
								<input type="hidden" name="action" value="add_teacher_comment">
								<input type="hidden" name="answer_id" value="<?php echo esc_attr($row['delivery_id']); ?>">
								<input type="hidden" name="homework_code" value="<?php echo esc_attr($row['homework_code']); ?>">
								<div class="add-comment">
									<h4><?php echo esc_html__('Any comments or observation :', 'sakolawp'); ?></h4>
									<textarea name="comment" id="" cols="30" rows="10"><?php echo esc_html($row['teacher_comment']); ?></textarea>
								</div>
								<div class="add-mark">
									<h4><?php echo esc_html__('Mark :', 'sakolawp'); ?></h4>
									<?php if ($current_homework['peer_review_template']) {
										$mark = count($peer_reviews) > 0 ? $total_mark / count($peer_reviews) : 0;
									?>
										<input type="number" name="mark" min="1" max="100" maxlength="3" required value="<?php echo esc_attr($mark); ?>" hidden />
										<p><?php echo esc_attr($mark); ?></p>
									<?php } else { ?>
										<input type="number" name="mark" min="1" max="100" maxlength="3" required value="<?php echo $row['mark']; ?>">
									<?php } ?>
								</div>
								<button id="submit-tugas" class="btn btn-rounded btn-primary skwp-btn" name="submit" type="submit" value="submit"> <?php echo esc_html__('Save', 'sakolawp'); ?></button>
							</form>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

<?php
endforeach;
?>

<?php
do_action('sakolawp_after_main_content');
get_footer();
