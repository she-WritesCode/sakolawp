<?php
defined('ABSPATH') || exit;

global $wpdb;


if (isset($_POST['submit'])) {
	//$_POST = array_map( 'stripslashes_deep', $_POST );
	$title = sanitize_text_field($_POST['title']);
	$description = sakolawp_sanitize_html($_POST['description']);
	$time_end = sanitize_text_field($_POST['time_end']);
	$date_end = sanitize_text_field($_POST['date_end']);

	$post_id = sanitize_text_field($_POST['post_id']);
	$date_end = sanitize_text_field($_POST['date_end']);
	$datetime = strtotime(date('d-m-Y', strtotime($date_end)));

	$class_id = sanitize_text_field($_POST['class_id']);
	$file_name = $_FILES["file_name"]["name"];
	$section_id = sanitize_text_field($_POST['section_id']);
	$subject_id = sanitize_text_field($_POST['subject_id']);
	$allow_peer_review = isset($_POST['allow_peer_review']);
	$peer_review_template = sanitize_text_field($_POST['peer_review_template']);
	$peer_review_who = sanitize_text_field($_POST['peer_review_who']);
	$word_count_min = sanitize_text_field($_POST['word_count_min']);
	$word_count_max = sanitize_text_field($_POST['word_count_max']);
	$uploader_type  = 'teacher';
	$uploader_id  = sanitize_text_field($_POST['uploader_id']);
	$homework_code = substr(md5(rand(100000000, 200000000)), 0, 10);

	$result = $wpdb->insert(
		$wpdb->prefix . 'sakolawp_homework',
		array(
			'homework_code' => $homework_code,
			'title' => $title,
			'description' => $description,
			'class_id' => $class_id,
			'section_id' => $section_id,
			'subject_id' => $subject_id,
			'uploader_id' => $uploader_id,
			'uploader_type' => $uploader_type,
			'time_end' => $time_end,
			'date_end' => $date_end,
			'file_name' => $file_name,
			'allow_peer_review' => $allow_peer_review,
			'peer_review_template' => $peer_review_template,
			'peer_review_who' => $peer_review_who,
			'word_count_min' => (int)$word_count_min,
			'word_count_max' => (int)$word_count_max,
		)
	);

	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');

	add_filter('upload_dir', 'sakolawp_custom_dir_homework');
	$attach_id = media_handle_upload('file_name', $post_id);
	if (is_numeric($attach_id)) {
		update_option('homework_file_name', $attach_id);
		update_post_meta($post_id, '_file_name', $attach_id);
	}
	remove_filter('upload_dir', 'sakolawp_custom_dir_homework');

	$repo = new RunHomeworkRepo();
	$homework_data = $repo->list(['homework_code' => $homework_code]);
	if (count($homework_data)) {
		do_action('sakolawp_homework_added', $homework_data[0]);
	}

	wp_redirect(add_query_arg(['form_submitted' => 'true'], home_url('homework')));
}

if (isset($_GET['action']) == 'delete') {
	$homework_code = $_GET['homework_code'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_homework',
		array(
			'homework_code' => $homework_code
		)
	);

	wp_redirect(home_url('homework'));
}


if (isset($_POST['action']) == 'select_class' && isset($_POST['class_id'])) {
	$class_id = sanitize_text_field($_POST['class_id']);
	wp_redirect(add_query_arg(['class_id' => $class_id], home_url('homework')));
}

$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : 1;

get_header();
do_action('sakolawp_before_main_content');

$running_year = get_option('running_year');

$teacher_id = get_current_user_id();

$user_info = get_userdata($teacher_id);
$teacher_name = $user_info->display_name;
$user_is_admin = in_array('administrator',  $user_info->roles);

$classes_sql = $user_is_admin
	? "SELECT class_id, name FROM {$wpdb->prefix}sakolawp_class ORDER BY class_id ASC;"
	: "SELECT c.class_id, c.name 
       FROM {$wpdb->prefix}sakolawp_section s
       JOIN {$wpdb->prefix}sakolawp_class c ON s.class_id = c.class_id
       WHERE s.teacher_id = $teacher_id 
       ORDER BY c.class_id ASC;";

$classes = $wpdb->get_results($classes_sql);

$subjects_in_class = get_posts([
	'post_type' => 'sakolawp-course',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'meta_query' => [
		[
			'key' => 'sakolawp_class_id',
			'compare' => '=',
			'terms' => $class_id,
		],
	],
]);

$subjects_in_class_ids = array_map(function ($subject) {
	return $subject->ID;
}, $subjects_in_class);
$subjects_in_class_id_string = count($subjects_in_class_ids) > 0 ? implode(',', $subjects_in_class_ids) : "0";

$homework_sql = "SELECT homework_id,title,class_id,section_id,subject_id,homework_code,uploader_id,allow_peer_review,peer_review_template,peer_review_who,created_at FROM {$wpdb->prefix}sakolawp_homework ORDER BY created_at desc;";
$my_homework = $wpdb->get_row($homework_sql);
?>

<input id="teacher_id_sel" type="hidden" name="teacher_id_target" value="<?php echo esc_attr($teacher_id); ?>">

<?php if (!empty($my_homework)) :

?>
	<div class="homework-inner skwp-content-inner">

		<div class="skwp-page-title skwp-clearfix">
			<h5 class="pull-left"><?php esc_html_e('My Class Homeworks', 'sakolawp'); ?>
				<span class="skwp-subtitle">
					<?php echo esc_html($teacher_name); ?>
				</span>
			</h5>
			<div class="pull-right">
				<a class="btn btn-primary btn-rounded btn-upper skwp-btn" data-target="#exampleModal1" data-toggle="modal"><?php esc_html_e('Add New', 'sakolawp'); ?></a>
			</div>
		</div>

		<?php do_action('sakolawp_show_alert_dialog') ?>

		<div>
			<!-- form to select a class_id -->
			<form action="/homework" method="POST" id="class_id_form">
				<input type="hidden" name="action" value="select_class">
				<div class="flex gap-4 items-base">
					<div class="form-group w-full">
						<label for="class_id"><?php esc_html_e('Class', 'sakolawp'); ?></label>
						<select class="form-control" id="class_id" name="class_id">
							<option>Select</option>
							<?php
							foreach ($classes as $cls) :
							?>
								<option <?= $cls->class_id == $class_id ? "selected" : '' ?> value="<?= $cls->class_id ?>"><?= $cls->name ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<div class="p-3 px-4"></div>
						<button type="submit" name="search" class="btn skwp-btn btn-primary"><?php esc_html_e('Select', 'sakolawp'); ?></button>
					</div>
				</div>
			</form>
		</div>

		<div class="skwp-table table-responsive skwp-mt-20">
			<table id="tableini" class="table dataTable responsive homework-table">
				<thead>
					<tr>
						<th hidden class="title-homework"><?php esc_html_e('Date Added', 'sakolawp'); ?></th>
						<th class="title-homework"><?php esc_html_e('Title', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Subject', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Due Date', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Class', 'sakolawp'); ?></th>
						<th><?php esc_html_e('Options', 'sakolawp'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$counter = 1;
					$homeworks = $wpdb->get_results($homework_sql, ARRAY_A);
					error_log($wpdb->last_error);
					foreach ($homeworks as $row) :
						$class = $wpdb->get_row("SELECT name, start_date FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $class_id");
						$homework_code = $row['homework_code'];
						$homework_id = $row['homework_id'];
						$schedule = get_homework_schedule($class_id, $homework_id);

						if (!$schedule || !$schedule['homework_is_due_for_release']) {
							continue; // Skip if no valid schedule or not due for release
						}

						$release_date = $schedule['release_date'];
						$due_date = $schedule['due_date'];
					?>
						<tr data-href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom')); ?>">
							<td hidden><?php echo $row['created_at']; ?></td>
							<td>
								<?php
								echo esc_html($row['title']);
								if ($user_is_admin) {
									$uploader = get_user_by('id', $row['uploader_id']);
									if ($uploader) {
										echo '<br/><i class="text-gray-500">Uploaded by: ' . $uploader->display_name, '</i>';
									}
								}
								$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '{$row["homework_code"]}'");
								echo '<br/><i class="text-gray-500">' . $count . ' submission(s)</i>';
								?>
							</td>
							<td>
								<?php
								$subject_id = $row['subject_id'];
								$subject = get_post((int)$subject_id);
								echo esc_html($subject->post_title);
								$allow_peer_review = $row['allow_peer_review'];
								$peer_review_who = $row["peer_review_who"] == "teacher" ? "Faculty" : "Peer";
								echo $allow_peer_review ? '<br/> <span class="badge badge-' . ($peer_review_who == 'Faculty' ? 'warning' : 'info') . ' badge-light ">' . $peer_review_who . ' reviewed</span>' : "";
								?>
							</td>
							<td>
								<a class="">
									<?php
									if ($due_date !== '0000-00-00 00:00:00') :
										echo date("l F j, Y, g:i a", strtotime($due_date));
									else :
										echo "No deadline";
									endif;
									?>
								</a>
								<br />
								<?php if ($due_date !== '0000-00-00 00:00:00') : ?>
									<span class="skwp-date italic" data-end-date="<?php echo date('Y-m-d', strtotime($due_date)); ?>" data-end-time="<?php echo date('H:i:s', strtotime($due_date)); ?>"></span>
								<?php endif; ?>
							</td>
							<td>
								<?php
								// $class_id = $row['class_id'];
								$section_id = $row['section_id'];
								// $class = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $class_id");
								echo esc_html($class->name);
								?>
							</td>
							<td>
								<a href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom')); ?>" class="btn btn-primary btn-rounded btn-sm skwp-btn">
									<?php echo esc_html__('View', 'sakolawp'); ?>
								</a>
								<a class="btn btn-danger btn-rounded btn-sm skwp-btn" onClick="return confirm('Confirm Delete?')" href="<?php echo add_query_arg(array('homework_code' => $row['homework_code'], 'action' => 'delete'), home_url('homework')); ?>">
									<?php echo esc_html__('Delete', 'sakolawp'); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<?php
else :
	esc_html_e('No homework has been created for your class yet', 'sakolawp'); ?>
	<div class="button-empty">
		<button class="btn btn-primary btn-rounded btn-upper skwp-btn" data-target="#exampleModal1" data-toggle="modal" type="button"><?php esc_html_e('Add New Homework', 'sakolawp'); ?></button>
	</div>
<?php
endif;
?>

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade bd-example-modal-lg" id="exampleModal1" role="dialog" tabindex="-1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">
					<?php echo esc_html__('Add New Homework', 'sakolawp'); ?>
				</h5>
				<button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> &times;</span></button>
			</div>
			<div class="modal-body">
				<div id="run-addhomework"></div>
				<form id="myForm" class="flex flex-col gap-2" name="save_create_homework" action="" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="action" value="save_create_homework" />
					<input type="hidden" name="uploader_id" value="<?php echo esc_attr($teacher_id); ?>" />
					<input type="hidden" class="skwp-form-control" name="post_id" value="<?php echo $homework_code; ?>" />
					<div class="skwp-clearfix skwp-row">
						<div class="skwp-column skwp-column-3">
							<div class="skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Class', 'sakolawp') ?></label>
								<div class="input-group">
									<select class="skwp-form-control select-subjects" name="class_id" id="class_holder" required="">
										<option value=""><?php echo __('Select', 'sakolawp'); ?></option>
										<?php
										global $wpdb;
										$classes = $wpdb->get_results("SELECT class_id,name FROM {$wpdb->prefix}sakolawp_class", OBJECT);
										foreach ($classes as $class) :
										?>
											<option value="<?php echo $class->class_id; ?>"><?php echo esc_html($class->name); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
						<div class="skwp-column skwp-column-3">
							<div class="skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Parent Group', 'sakolawp'); ?></label>
								<div class="input-group">
									<select class="skwp-form-control teacher-section" name="section_id" id="section_holder2">
										<option value=""><?php esc_html_e('All', 'sakolawp'); ?></option>
									</select>
								</div>
							</div>
						</div>
						<div class="skwp-column skwp-column-3">
							<div class="skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Subject', 'sakolawp'); ?></label>
								<div class="input-group">
									<select class="skwp-form-control" name="subject_id" id="subject_holder" required="">
										<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="skwp-form-group">
						<label for=""> <?php esc_html_e('Title', 'sakolawp'); ?></label><input class="skwp-form-control" placeholder="<?php esc_html_e('Title', 'sakolawp'); ?>" name="title" required="" type="text">
					</div>
					<div class="skwp-form-group">
						<label> <?php esc_html_e('Description', 'sakolawp'); ?></label><textarea id="editordatateacher" name="description"></textarea>
					</div>

					<div>
						<div class="skwp-form-group">
							<input value="yes" type="checkbox" name="allow_peer_review" id="allow_peer_review" />
							<label class="row-form-label" for="allow_peer_review"><?php esc_html_e('Use assessment based review', 'sakolawp') ?></label>
						</div>
						<div class="peer-review-template-group skwp-clearfix skwp-row">
							<div class=" skwp-column skwp-column-2 skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Who would be reviewing?', 'sakolawp'); ?></label>
								<div class="input-group">
									<label>
										<input type="radio" name="peer_review_who" value="student" checked>
										<?php esc_html_e('Student', 'sakolawp'); ?>
									</label>
									<label>
										<input type="radio" name="peer_review_who" value="teacher">
										<?php esc_html_e('Faculty', 'sakolawp'); ?>
									</label>
								</div>
							</div>
							<div class="skwp-column skwp-column-2 skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Assessment Template', 'sakolawp'); ?></label>
								<div class="input-group">
									<select class="skwp-form-control teacher-section" name="peer_review_template" id="peer_review_template">
										<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div>
						<div class="skwp-form-group">
							<input value="yes" type="checkbox" name="limit_word_count" id="limit_word_count" />
							<label class="row-form-label" for="limit_word_count"><?php esc_html_e('Limit word count', 'sakolawp') ?></label>
						</div>
						<div class="skwp-clearfix skwp-row word-count-template-group">
							<div class="skwp-column skwp-column-2">
								<div class="skwp-form-group">
									<label for=""> <?php esc_html_e('Minimum Word Count', 'sakolawp'); ?></label>
									<input class="skwp-form-control" type="number" min="0" name="word_count_min">
								</div>
							</div>
							<div class="skwp-column skwp-column-2">
								<div class="skwp-form-group">
									<label for=""> <?php esc_html_e('Maximum Word Count', 'sakolawp'); ?></label>
									<input type="number" name="word_count_max" min="0" class="skwp-form-control">

								</div>
							</div>
						</div>
					</div>

					<div class="skwp-clearfix skwp-row">
						<div class="skwp-column skwp-column-2">
							<div class="skwp-form-group">
								<label for=""> <?php esc_html_e('Due Date', 'sakolawp'); ?></label><input class="single-daterange skwp-form-control" required="" type="text" name="date_end" value="">
							</div>
						</div>
						<div class="skwp-column skwp-column-2">
							<div class="skwp-form-group">
								<label for=""> <?php esc_html_e('Due Time', 'sakolawp'); ?></label>
								<div class="input-group clockpicker" data-align="top" data-autoclose="true">
									<input type="text" required="" name="time_end" class="skwp-form-control" value="23:59">
								</div>
							</div>
						</div>
					</div>

					<div class="skwp-form-group">
						<label class="col-form-label" for=""> <?php esc_html_e('Upload File', 'sakolawp'); ?></label>
						<div class="input-group skwp-form-control mb-2">
							<input type="file" name="file_name" id="file-3" class="inputfile inputfile-3" style="display:none" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" />
							<label for="file-3"><i class="os-icon picons-thin-icon-thin-0042_attachment"></i> <span><?php esc_html_e('Upload File...', 'sakolawp'); ?></span></label>
						</div>
						<span class="warning"><?php esc_html_e('Max file size up to 10MB', 'sakolawp'); ?></span>
					</div>
			</div>
			<div class="modal-footer">
				<button id="submit-tugas" class="btn btn-rounded btn-success skwp-btn" name="submit" type="submit" value="submit"> <?php esc_html_e('Create', 'sakolawp'); ?></button>
			</div>
			</form>
		</div>
	</div>
</div>

<?php
do_action('sakolawp_after_main_content');
get_footer();
