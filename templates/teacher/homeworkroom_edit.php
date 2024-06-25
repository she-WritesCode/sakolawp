<?php
defined('ABSPATH') || exit;

global $wpdb;

if (isset($_POST['submit'])) {
	//$_POST = array_map( 'stripslashes_deep', $_POST );
	$title = sakolawp_sanitize_html($_POST['title']);
	$description = sakolawp_sanitize_html($_POST['description']);
	$allow_peer_review = isset($_POST['allow_peer_review']);
	$peer_review_who = sanitize_text_field($_POST['peer_review_who']);
	$peer_review_template = sanitize_text_field($_POST['peer_review_template']);
	$limit_word_count = isset($_POST['limit_word_count']);
	$word_count_min = $limit_word_count ? sanitize_text_field($_POST['word_count_min']) : NULL;
	$word_count_max = $limit_word_count ? sanitize_text_field($_POST['word_count_max']) : NULL;
	$time_end = sanitize_text_field($_POST['time_end']);
	$date_end = sanitize_text_field($_POST['date_end']);

	$datetime = strtotime(date('d-m-Y', strtotime(sanitize_text_field($_POST['date_end']))));
	$uploader_type  = 'teacher';
	$uploader_id  = sanitize_text_field($_POST['uploader_id']);
	$homework_code = sanitize_text_field($_POST['homework_code']);

	$wpdb->update(
		$wpdb->prefix . 'sakolawp_homework',
		array(
			'title' => $title,
			'description' => $description,
			'uploader_id' => $uploader_id,
			'uploader_type' => $uploader_type,
			'time_end' => $time_end,
			'date_end' => $date_end,
			'allow_peer_review' => $allow_peer_review,
			'peer_review_who' => $peer_review_who,
			'peer_review_template' => $peer_review_template,
			'word_count_min' => $word_count_min,
			'word_count_max' => $word_count_max,
		),
		array(
			'homework_code' => $homework_code
		)
	);

	wp_redirect(add_query_arg(['form_submitted' => 'true', 'homework_code' => $homework_code], home_url('homeworkroom_edit')));
}

get_header();
do_action('sakolawp_before_main_content');

$teacher_id = get_current_user_id();

$running_year = get_option('running_year');

$homework_code = sanitize_text_field($_GET['homework_code']);
$class_id = sanitize_text_field($_GET['class_id']);
$current_homework = $wpdb->get_results("SELECT homework_code, title, date_end, time_end, description, file_name, subject_id, class_id, section_id, peer_review_template, allow_peer_review, peer_review_who, word_count_min, word_count_max FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);
foreach ($current_homework as $row) :

?>
	<div class="homeworkroom-page skwp-content-inner">
		<div class="skwp-tab-menu">
			<ul class="skwp-tab-wrap">
				<li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom')); ?>">
						<span><?php echo esc_html__('Homework', 'sakolawp'); ?></span>
					</a>
				</li>
				<li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom_details')); ?>">
						<span><?php echo esc_html__('Homework Reports', 'sakolawp'); ?></span>
					</a>
				</li>
				<!-- <li class="skwp-tab-items active">
					<a class="skwp-tab-item href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom_edit')); ?>">
						<span><?php echo esc_html__('Edit', 'sakolawp'); ?></span>
					</a>
				</li> -->
			</ul>
		</div>
		<div class="back skwp-back hidden-sm-down">
			<a href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom')); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
		</div>

		<?php do_action('sakolawp_show_alert_dialog') ?>

		<div class="skwp-clearfix skwp-row skwp-mt-10">
			<div class="skwp-column skwp-column-1">
				<div class="tugas-wrap">
					<form id="myForm" name="save_update_homework" action="" method="POST">
						<input type="hidden" name="action" value="save_update_homework">
						<input type="hidden" name="uploader_id" value="<?php echo esc_attr($teacher_id); ?>">
						<input type="hidden" name="homework_code" value="<?php echo esc_attr($row['homework_code']); ?>">

						<div class="skwp-form-group">
							<label for=""> <?php echo esc_html__('Title', 'sakolawp'); ?></label>
							<input class="skwp-form-control" placeholder="<?php echo esc_html__('Title', 'sakolawp'); ?>" name="title" required="" type="text" value="<?php echo esc_attr($row['title']); ?>">
						</div>
						<div class="skwp-form-group">
							<label> <?php echo esc_html__('Description', 'sakolawp'); ?></label>
							<textarea id="editordatateacher" name="description"><?php echo esc_textarea($row['description']); ?></textarea>
						</div>


						<div>
							<div class="skwp-form-group">
								<input value="yes" <?php echo $row['allow_peer_review'] === '1' ? 'checked' : ""; ?> type="checkbox" name="allow_peer_review" id="allow_peer_review" />
								<label class="row-form-label" for="allow_peer_review"><?php esc_html_e('Use assessment based review', 'sakolawp') ?></label>
							</div>
							<div class="peer-review-template-group skwp-clearfix skwp-row">
								<div class="skwp-column skwp-column-2">
									<div class="skwp-form-group">
										<label class="col-form-label" for=""><?php esc_html_e('Who would be reviewing?', 'sakolawp'); ?></label>
										<div class="input-group">
											<label>
												<input type="radio" name="peer_review_who" value="student" <?php echo $row['peer_review_who'] === 'student' ? 'checked' : ""; ?>>
												<?php esc_html_e('Student', 'sakolawp'); ?>
											</label>
											<label>
												<input type="radio" name="peer_review_who" value="teacher" <?php echo $row['peer_review_who'] === 'teacher' ? 'checked' : ""; ?>>
												<?php esc_html_e('Faculty', 'sakolawp'); ?>
											</label>
										</div>
									</div>
								</div>
								<div class="skwp-column skwp-column-2">
									<div class="skwp-form-group">
										<label class="col-form-label" for=""><?php esc_html_e('Assessment Template', 'sakolawp'); ?></label>
										<div class="input-group">
											<select data-value="<?php echo esc_attr($row['peer_review_template']); ?>" class="skwp-form-control teacher-section" name="peer_review_template" id="peer_review_template" required="">
												<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div>
							<div class="skwp-form-group">
								<input value="yes" <?php echo $row['word_count_min'] && $row['word_count_max']  ? 'checked' : ""; ?> type="checkbox" name="limit_word_count" id="limit_word_count" />
								<label class="row-form-label" for="limit_word_count"><?php esc_html_e('Limit word count', 'sakolawp') ?></label>
							</div>
							<div class="skwp-clearfix skwp-row word-count-template-group">
								<div class="skwp-column skwp-column-2">
									<div class="skwp-form-group">
										<label for=""> <?php esc_html_e('Minimum Word Count', 'sakolawp'); ?></label>
										<input value="<?php echo esc_attr($row['word_count_min']); ?>" class="skwp-form-control" type="number" min="0" name="word_count_min">
									</div>
								</div>
								<div class="skwp-column skwp-column-2">
									<div class="skwp-form-group">
										<label for=""> <?php esc_html_e('Maximum Word Count', 'sakolawp'); ?></label>
										<input value="<?php echo esc_attr($row['word_count_max']); ?>" type="number" name="word_count_max" min="0" class="skwp-form-control">
									</div>
								</div>
							</div>
						</div>

						<div class="skwp-form-group">
							<label for=""> <?php echo esc_html__('Due Date', 'sakolawp'); ?></label>
							<input class="single-daterange skwp-form-control" required="" type="text" name="date_end" value="<?php echo esc_attr($row['date_end']); ?>">
						</div>
						<div class="skwp-form-group">
							<label for=""> <?php echo esc_html__('Time Limit', 'sakolawp'); ?></label>
							<div class="input-group clockpicker" data-align="top" data-autoclose="true">
								<input type="text" required="" name="time_end" class="skwp-form-control" value="<?php echo esc_attr($row['time_end']); ?>">
								<span class="input-group-addon">
									<span class="picons-thin-icon-thin-0029_time_watch_clock_wall"></span>
								</span>
							</div>
						</div>
						<div class="skwp-form-button skwp-mt-20">
							<button id="submit-tugas" class="btn btn-rounded btn-success skwp-btn" name="submit" type="submit" value="submit"> <?php echo esc_html__('Save', 'sakolawp'); ?></button>
						</div>
					</form>

				</div>
			</div>

			<div class="skwp-column skwp-column-1 homework-info skwp-mt-20">
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
								$subject = get_post((int)$subject_id);
								echo esc_html($subject->post_title);
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
						$allow_peer_review = $row["allow_peer_review"];
						$peer_review_template = $row["peer_review_template"];
						$peer_review_who = $row["peer_review_who"] == "teacher" ? "Faculty" : "Peer";

						if ($allow_peer_review) {
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
	</div>

<?php
endforeach;

do_action('sakolawp_after_main_content');
get_footer();
