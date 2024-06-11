<?php

require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-subject-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-homework-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-lesson-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-user-repo.php';

/** List Subjects */
function run_list_subjects()
{
	$repo = new RunSubjectRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list(sanitize_text_field($_POST['search']));

	wp_send_json_success($result, 200);
	die();
}

/** Read a single subject */
function run_single_subject()
{
	$repo = new RunSubjectRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$subject_id = sanitize_text_field($_POST['subject_id']);
	$result = $repo->single($subject_id);

	if (!$result) {
		wp_send_json_error('Subject not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new subject */
function run_create_subject()
{
	$repo = new RunSubjectRepo();
	$_POST = array_map('stripslashes_deep', $_POST);
	$subject_data = [
		'name' => sanitize_text_field($_POST['name']),
		'teacher_id' => sanitize_text_field($_POST['teacher_id']),
	];

	$result = $repo->create($subject_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing subject */
function run_update_subject()
{
	$repo = new RunSubjectRepo();
	$subject_id = sanitize_text_field($_POST['subject_id']);
	$_POST = array_map('stripslashes_deep', $_POST);
	$subject_data = [
		'name' => sanitize_text_field($_POST['name']),
		'teacher_id' => sanitize_text_field($_POST['teacher_id']),
	];
	$result = $repo->update($subject_id, $subject_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a subject */
function run_delete_subject($subject_id)
{
	$repo = new RunSubjectRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$subject_id = sanitize_text_field($_POST['subject_id']);
	$result = $repo->delete($subject_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_subjects', 'run_list_subjects');
add_action('wp_ajax_run_single_subject', 'run_single_subject');
add_action('wp_ajax_run_create_subject', 'run_create_subject');
add_action('wp_ajax_run_update_subject', 'run_update_subject');
add_action('wp_ajax_run_delete_subject', 'run_delete_subject');



/** List Homeworks */
function run_list_homeworks()
{
	$repo = new RunHomeworkRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single homework */
function run_single_homework()
{
	$repo = new RunHomeworkRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$homework_id = sanitize_text_field($_POST['homework_id']);
	$result = $repo->single($homework_id);

	if (!$result) {
		wp_send_json_error('Homework not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new homework */
function run_create_homework()
{
	$repo = new RunHomeworkRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	//$_POST = array_map( 'stripslashes_deep', $_POST );
	$title = sanitize_text_field($_POST['title']);
	$description = sakolawp_sanitize_html($_POST['description']);
	$date_end = sanitize_text_field($_POST['date_end']);
	$time_end = sanitize_text_field($_POST['time_end']);
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

	$post_id = $homework_code;

	$result = $repo->create([
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
	]);

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

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing homework */
function run_update_homework()
{
	$repo = new RunHomeworkRepo();
	$homework_id = sanitize_text_field($_POST['homework_id']);
	$homework_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($homework_id, $homework_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a homework */
function run_delete_homework($homework_id)
{
	$repo = new RunHomeworkRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$homework_id = sanitize_text_field($_POST['homework_id']);
	$result = $repo->delete($homework_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_homeworks', 'run_list_homeworks');
add_action('wp_ajax_run_single_homework', 'run_single_homework');
add_action('wp_ajax_run_create_homework', 'run_create_homework');
add_action('wp_ajax_run_update_homework', 'run_update_homework');
add_action('wp_ajax_run_delete_homework', 'run_delete_homework');


/** List Lessons */
function run_list_lessons()
{
	$repo = new RunLessonRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single lesson */
function run_single_lesson()
{
	$repo = new RunLessonRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$lesson_id = sanitize_text_field($_POST['lesson_id']);
	$result = $repo->single($lesson_id);

	if (!$result) {
		wp_send_json_error('Lesson not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new lesson */
function run_create_lesson()
{
	$repo = new RunLessonRepo();
	$lesson_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->create($lesson_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing lesson */
function run_update_lesson()
{
	$repo = new RunLessonRepo();
	$lesson_id = sanitize_text_field($_POST['lesson_id']);
	$lesson_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($lesson_id, $lesson_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a lesson */
function run_delete_lesson($lesson_id)
{
	$repo = new RunLessonRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$lesson_id = sanitize_text_field($_POST['lesson_id']);
	$result = $repo->delete($lesson_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_lessons', 'run_list_lessons');
add_action('wp_ajax_run_single_lesson', 'run_single_lesson');
add_action('wp_ajax_run_create_lesson', 'run_create_lesson');
add_action('wp_ajax_run_update_lesson', 'run_update_lesson');
add_action('wp_ajax_run_delete_lesson', 'run_delete_lesson');

/** List Users */
function run_list_users()
{
	$repo = new RunUserRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single user */
function run_single_user()
{
	$repo = new RunUserRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$user_id = sanitize_text_field($_POST['user_id']);
	$result = $repo->single($user_id);

	if (!$result) {
		wp_send_json_error('User not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new user */
function run_create_user()
{
	$repo = new RunUserRepo();
	$user_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->create($user_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing user */
function run_update_user()
{
	$repo = new RunUserRepo();
	$user_id = sanitize_text_field($_POST['user_id']);
	$user_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($user_id, $user_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a user */
function run_delete_user($user_id)
{
	$repo = new RunUserRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$user_id = sanitize_text_field($_POST['user_id']);
	$result = $repo->delete($user_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_users', 'run_list_users');
add_action('wp_ajax_run_single_user', 'run_single_user');
add_action('wp_ajax_run_create_user', 'run_create_user');
add_action('wp_ajax_run_update_user', 'run_update_user');
add_action('wp_ajax_run_delete_user', 'run_delete_user');
