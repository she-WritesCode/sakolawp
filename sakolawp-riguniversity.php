<?php

require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-course-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-subject-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-homework-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-questions-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-deliveries-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-class-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-lesson-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-enroll-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-user-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-event-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-section-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-accountability-repo.php';
require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-schedule-repo.php';

/** List Courses */
function run_list_courses()
{
	$repo = new RunCourseRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list(sanitize_text_field($_POST['search']));

	wp_send_json_success($result, 200);
	die();
}

/** Read a single course */
function run_single_course()
{
	$repo = new RunCourseRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$course_id = sanitize_text_field($_POST['course_id']);
	$result = $repo->single($course_id);

	if (!$result) {
		wp_send_json_error('Course not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new course */
function run_create_course()
{
	$repo = new RunCourseRepo();
	$_POST = array_map('stripslashes_deep', $_POST);
	$course_data = [
		'name' => sanitize_text_field($_POST['name']),
		'teacher_id' => sanitize_text_field($_POST['teacher_id']),
	];

	$result = $repo->create($course_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing course */
function run_update_course()
{
	$repo = new RunCourseRepo();
	$course_id = sanitize_text_field($_POST['course_id']);
	$_POST = array_map('stripslashes_deep', $_POST);
	$course_data = [
		'name' => sanitize_text_field($_POST['name']),
		'teacher_id' => sanitize_text_field($_POST['teacher_id']),
	];
	$result = $repo->update($course_id, $course_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a course */
function run_delete_course($course_id)
{
	$repo = new RunCourseRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$course_id = sanitize_text_field($_POST['course_id']);
	$result = $repo->delete($course_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_courses', 'run_list_courses');
add_action('wp_ajax_run_single_course', 'run_single_course');
add_action('wp_ajax_run_create_course', 'run_create_course');
add_action('wp_ajax_run_update_course', 'run_update_course');
add_action('wp_ajax_run_delete_course', 'run_delete_course');

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

// add_action('wp_ajax_run_list_subjects', 'run_list_subjects');
// add_action('wp_ajax_run_single_subject', 'run_single_subject');
// add_action('wp_ajax_run_create_subject', 'run_create_subject');
// add_action('wp_ajax_run_update_subject', 'run_update_subject');
// add_action('wp_ajax_run_delete_subject', 'run_delete_subject');



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

	$homework_id = (int)($_POST['homework_id']);
	$result = $repo->single($homework_id);

	if (!$result) {
		wp_send_json_error('Assessment not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new homework */
function run_create_homework()
{
	$error = [];
	$repo = new RunHomeworkRepo();
	$courseRepo = new RunCourseRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$title = sanitize_text_field($_POST['title']);
	$description = sakolawp_sanitize_html($_POST['description']);
	// $class_id = sanitize_text_field($_POST['class_id']);
	// $section_id = sanitize_text_field($_POST['section_id']);
	$file_name = $_FILES["file_name"]["name"];
	$subject_id = sanitize_text_field($_POST['subject_id']);
	$allow_peer_review = ($_POST['allow_peer_review']);
	$peer_review_template = sanitize_text_field($_POST['peer_review_template']);
	$peer_review_who = sanitize_text_field($_POST['peer_review_who']);
	$word_count_min = sanitize_text_field($_POST['word_count_min']);
	$word_count_max = sanitize_text_field($_POST['word_count_max']);
	$questions = $_POST['questions'];
	$uploader_type  = 'teacher';
	$uploader_id  = get_current_user_id();
	$homework_code = substr(md5(rand(100000000, 200000000)), 0, 10);

	$post_id = $homework_code;

	$course = $courseRepo->single($subject_id);

	if (!$course) {
		wp_send_json_error('Course not found', 404);
		die();
	}
	$args = [];

	if (isset($_FILES["file_name"])) {
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');

		add_filter('upload_dir', 'sakolawp_custom_dir_homework');
		$attach_id = media_handle_upload('file_name', $post_id);
		if (is_numeric($attach_id)) {
			update_option('homework_file_name', $attach_id);
			update_post_meta($post_id, '_file_name', $attach_id);
			$file_url = get_attachment_link($attach_id);
			$file_date = date("Y-m-d H:i:s");
			$args = array_merge($args, [
				'file_name' => $file_name,
				'file_id' => $attach_id,
				'file_url' => $file_url,
				'file_date' => $file_date,
			]);
		}
		remove_filter('upload_dir', 'sakolawp_custom_dir_homework');
		if (!is_numeric($attach_id)) {
			// If the file was not uploaded successfully
			$file_name = NULL;
			$error['message'][] = 'File was not uploaded successfully';
		}
	}

	$result = $repo->create(array_merge($args, [
		'homework_code' => $homework_code,
		'title' => $title,
		'description' => $description,
		// homework has no class_id
		// 'class_id' => get_post_meta($course['ID'], 'sakolawp_class_id'),
		// 'section_id' => $section_id,
		'subject_id' => $subject_id,
		'uploader_id' => $uploader_id,
		'uploader_type' => $uploader_type,
		'allow_peer_review' => $allow_peer_review,
		'peer_review_template' => $peer_review_template,
		'peer_review_who' => $peer_review_who,
		'word_count_min' => (int)$word_count_min,
		'word_count_max' => (int)$word_count_max,
		'questions' => $questions,
	]));

	if ($result) { // If the homework was created successfully
		wp_send_json_success(['result' => $result, 'error' => $error], 201);
		die();
	}
	// If the homework was not created successfully
	$error['message'][] = 'Failed to create homework';
	wp_send_json_error($error, 500);
	die();
}

/** Duplicate an existing homework */
function run_duplicate_homework()
{
	$repo = new RunHomeworkRepo();
	$_POST = array_map('stripslashes_deep', $_POST);


	$homework_id = isset($_POST['homework_id']) ? sanitize_text_field($_POST['homework_id']) : '';
	$homework = $repo->single($homework_id);

	$title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : $homework->title  . ' - Copy';
	$homework_code = substr(md5(rand(100000000, 200000000)), 0, 10);

	$result = $repo->create(array_merge([
		'title' => $title,
		'description' => $homework->description,
		'section_id' => $homework->section_id,
		'subject_id' => $homework->subject_id,
		'uploader_id' => $homework->uploader_id,
		'uploader_type' => $homework->uploader_type,
		'time_end' => $homework->time_end,
		'date_end' => $homework->date_end,
		'file_name' => $homework->file_name,
		'file_id' => $homework->file_id,
		'file_url' => $homework->file_url,
		'file_date' => $homework->file_date,
		'allow_peer_review' => $homework->allow_peer_review,
		'peer_review_template' => $homework->peer_review_template,
		'peer_review_who' => $homework->peer_review_who,
		'word_count_min' => (int)$homework->word_count_min,
		'word_count_max' => (int)$homework->word_count_max,
	], [
		'homework_code' => $homework_code,
		'questions' => array_map(function ($question, $key) {
			$question = (array)$question;
			return array_merge($question, [
				'question_id' => 'q' . (string)$key,
				'options' => array_map(function ($option) {
					return (array)$option;
				}, $question['options'])
			]);
		}, $homework->questions, array_keys($homework->questions)),
	]));

	if ($result) { // If the homework was created successfully
		// do_action('sakolawp_homework_added', $repo->single($result));
		wp_send_json_success($result, 201);
		die();
	}

	// If the homework was not created successfully
	$error = [];
	$error['message'] = 'Failed to create homework';
	wp_send_json_error($error, 500);
	die();
}

/** Update an existing homework */
function run_update_homework()
{
	$error = [];
	$repo = new RunHomeworkRepo();
	$courseRepo = new RunCourseRepo();

	$homework_id = sanitize_text_field($_POST['homework_id']);
	$_POST = array_map('stripslashes_deep', $_POST);
	$title = sakolawp_sanitize_html($_POST['title']);
	$description = sakolawp_sanitize_html($_POST['description']);
	$allow_peer_review = ($_POST['allow_peer_review']);
	$peer_review_who = sanitize_text_field($_POST['peer_review_who']);
	$peer_review_template = sanitize_text_field($_POST['peer_review_template']);
	$limit_word_count = ($_POST['limit_word_count']);
	$word_count_min = $limit_word_count ? sanitize_text_field($_POST['word_count_min']) : NULL;
	$word_count_max = $limit_word_count ? sanitize_text_field($_POST['word_count_max']) : NULL;
	$time_end = sanitize_text_field($_POST['time_end']);
	$date_end = sanitize_text_field($_POST['date_end']);
	$questions = $_POST['questions'];
	$file_name = isset($_FILES["file_name"]) ? $_FILES["file_name"]["name"] : NULL;

	$uploader_type  = 'teacher';
	$uploader_id  = sanitize_text_field($_POST['uploader_id']);

	$homework = $repo->single($homework_id);
	$course = $courseRepo->single($homework->subject_id);

	if (!$course) {
		wp_send_json_error('Course not found', 404);
		die();
	}
	$post_id = $homework->homework_code;

	$args = [];

	if (isset($_FILES["file_name"])) {
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');

		add_filter('upload_dir', 'sakolawp_custom_dir_homework');
		$attach_id = media_handle_upload('file_name', $post_id);
		if (is_numeric($attach_id)) {
			update_option('homework_file_name', $attach_id);
			update_post_meta($post_id, '_file_name', $attach_id);
			$file_url = get_attachment_link($attach_id);
			$file_date = date("Y-m-d H:i:s");
			$args = array_merge($args, [
				'file_name' => $file_name,
				'file_id' => $attach_id,
				'file_url' => $file_url,
				'file_date' => $file_date,
			]);
		}
		remove_filter('upload_dir', 'sakolawp_custom_dir_homework');
		if (!is_numeric($attach_id)) {
			// If the file was not uploaded successfully
			$file_name = NULL;
			$error['message'][] = 'File was not uploaded successfully';
		}
	}

	$result = $repo->update($homework_id, array_merge($args, [
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
		'questions' => $questions,
	]));



	if ($result) { // If the homework was created successfully
		wp_send_json_success(['result' => $result, 'error' => $error], 200);
		die();
	}

	// If the homework was not created successfully
	$error['message'][] = 'Failed to update homework';
	wp_send_json_error($error, 500);
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
add_action('wp_ajax_run_duplicate_homework', 'run_duplicate_homework');
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

/** List Enrolls */
function run_list_current_user_enrolls()
{
	$repo = new RunEnrollRepo();
	$_POST = array_map('stripslashes_deep', $_POST);
	$student_id = get_current_user_id();

	$result = $repo->list(array_merge($_POST, ['student_id' => $student_id]));

	wp_send_json_success($result, 200);
	die();
}

/** List Enrolls */
function run_list_enrolls()
{
	$repo = new RunEnrollRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single enroll */
function run_single_enroll()
{
	$repo = new RunEnrollRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$enroll_id = sanitize_text_field($_POST['enroll_id']);
	$result = $repo->single($enroll_id);

	if (!$result) {
		wp_send_json_error('Enroll not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new enroll */
function run_create_enroll()
{
	$repo = new RunEnrollRepo();
	$enroll_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->create($enroll_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing enroll */
function run_update_enroll()
{
	$repo = new RunEnrollRepo();
	$enroll_id = sanitize_text_field($_POST['enroll_id']);
	$enroll_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($enroll_id, $enroll_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a enroll */
function run_delete_enroll($enroll_id)
{
	$repo = new RunEnrollRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$enroll_id = sanitize_text_field($_POST['enroll_id']);
	$result = $repo->delete($enroll_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_enrolls', 'run_list_enrolls');
add_action('wp_ajax_run_list_current_user_enrolls', 'run_list_current_user_enrolls');
add_action('wp_ajax_run_single_enroll', 'run_single_enroll');
add_action('wp_ajax_run_create_enroll', 'run_create_enroll');
add_action('wp_ajax_run_update_enroll', 'run_update_enroll');
add_action('wp_ajax_run_delete_enroll', 'run_delete_enroll');

/** List Class */
function run_list_class()
{
	$repo = new RunClassRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** List Class */
function run_list_class_subjects()
{
	$repo = new RunClassRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list_subjects($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single class */
function run_single_class()
{
	$repo = new RunClassRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$class_id = sanitize_text_field($_POST['class_id']);
	$result = $repo->single($class_id);

	if (!$result) {
		wp_send_json_error('Class not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new class */
function run_create_class()
{
	$repo = new RunClassRepo();
	$_POST = array_map('stripslashes_deep', $_POST);
	$class_data['name'] = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : "";
	$class_data['drip_method'] = isset($_POST['drip_method']) ? sanitize_text_field($_POST['drip_method']) : "";
	$class_data['start_date'] = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : "";
	$class_data['subjects'] = isset($_POST['subjects']) ? array_map('stripslashes_deep', $_POST['subjects']) : [];

	$result = $repo->create($class_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing class */
function run_update_class()
{
	$repo = new RunClassRepo();
	$_POST = array_map('stripslashes_deep', $_POST);
	$class_id = isset($_POST['name']) ? sanitize_text_field($_POST['class_id']) : NULL;
	if (!$class_id) {
		wp_send_json_error('Class not found', 404);
		die();
	}
	$class_data['name'] = isset($_POST['name']) ? sakolawp_sanitize_html($_POST['name']) : "";
	$class_data['drip_method'] = isset($_POST['drip_method']) ? sakolawp_sanitize_html($_POST['drip_method']) : "";
	$class_data['start_date'] = isset($_POST['start_date']) ? sakolawp_sanitize_html($_POST['start_date']) : "";
	$class_data['subjects'] = isset($_POST['subjects']) ? array_map('stripslashes_deep', $_POST['subjects']) : [];

	$result = $repo->update($class_id, $class_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a class */
function run_delete_class($class_id)
{
	$repo = new RunClassRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$class_id = sanitize_text_field($_POST['class_id']);
	$result = $repo->delete($class_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_class', 'run_list_class');
add_action('wp_ajax_run_list_class_subjects', 'run_list_class_subjects');
add_action('wp_ajax_run_single_class', 'run_single_class');
add_action('wp_ajax_run_create_class', 'run_create_class');
add_action('wp_ajax_run_update_class', 'run_update_class');
add_action('wp_ajax_run_delete_class', 'run_delete_class');

/** List Schedule */
function run_list_schedules()
{
	$repo = new RunClassScheduleRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single schedule */
function run_single_schedule()
{
	$repo = new RunClassScheduleRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$schedule_id = sanitize_text_field($_POST['schedule_id']);
	$result = $repo->single($schedule_id);

	if (!$result) {
		wp_send_json_error('Schedule not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new schedule */
function run_create_schedule()
{
	$repo = new RunClassScheduleRepo();
	$schedules = array_map('stripslashes_deep', $_POST['schedules']);
	$schedule_data = [];

	foreach ($schedules as $key => $schedule) {
		$schedule_data[$key]['class_id'] = isset($schedule['class_id']) ? sanitize_text_field($schedule['class_id']) : "";
		$schedule_data[$key]['subject_id'] = isset($schedule['subject_id']) ? sanitize_text_field($schedule['subject_id']) : "";
		$schedule_data[$key]['content_id'] = isset($schedule['content_id']) ? sanitize_text_field($schedule['content_id']) : "";
		$schedule_data[$key]['content_type'] = isset($schedule['content_type']) ? sanitize_text_field($schedule['content_type']) : "";
		$schedule_data[$key]['drip_method'] = isset($schedule['drip_method']) ? sanitize_text_field($schedule['drip_method']) : "";
		$schedule_data[$key]['release_date'] = isset($schedule['release_date']) ? sanitize_text_field($schedule['release_date']) : NULL;
		$schedule_data[$key]['deadline_date'] = isset($schedule['deadline_date']) ? sanitize_text_field($schedule['deadline_date']) : 0;
		$schedule_data[$key]['release_days'] = isset($schedule['release_days']) ? sanitize_text_field($schedule['release_days']) : NULL;
		$schedule_data[$key]['release_days_time'] = isset($schedule['release_days_time']) ? sanitize_text_field($schedule['release_days_time']) : NULL;
		$schedule_data[$key]['deadline_days'] = isset($schedule['deadline_days']) ? sanitize_text_field($schedule['deadline_days']) : 0;
		$schedule_data[$key]['deadline_days_time'] = isset($schedule['deadline_days_time']) ? sanitize_text_field($schedule['deadline_days_time']) : 0;

		// Validate schedule data
		if (!$schedule_data[$key]['class_id'] || !$schedule_data[$key]['subject_id'] || !$schedule_data[$key]['content_id'] || !$schedule_data[$key]['content_type']) {
			wp_send_json_error('All field are required pleae', 400);
			die();
		}
	}

	// error_log(print_r($schedule_data, true));

	$result = $repo->create(array_values($schedule_data));

	sakolawp_update_schedules($result);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing schedule */
function run_update_schedule()
{
	$repo = new RunClassScheduleRepo();
	$_POST = array_map('stripslashes_deep', $_POST);
	$schedule_id = sanitize_text_field($_POST['schedule_id']);
	$schedule_data['name'] = isset($_POST['name']) ? sakolawp_sanitize_html($_POST['name']) : "";
	$schedule_data['drip_method'] = isset($_POST['drip_method']) ? sakolawp_sanitize_html($_POST['drip_method']) : "";
	$schedule_data['start_date'] = isset($_POST['start_date']) ? sakolawp_sanitize_html($_POST['start_date']) : "";
	$schedule_data['subjects'] = isset($_POST['subjects']) ? array_map('stripslashes_deep', $_POST['subjects']) : [];

	$result = $repo->update($schedule_id, $schedule_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a schedule */
function run_delete_schedule($schedule_id)
{
	$repo = new RunClassScheduleRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$schedule_id = sanitize_text_field($_POST['schedule_id']);
	$result = $repo->delete($schedule_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_schedules', 'run_list_schedules');
add_action('wp_ajax_run_single_schedule', 'run_single_schedule');
add_action('wp_ajax_run_create_schedule', 'run_create_schedule');
add_action('wp_ajax_run_update_schedule', 'run_update_schedule');
add_action('wp_ajax_run_delete_schedule', 'run_delete_schedule');

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

/** List Events */
function run_list_events()
{
	$repo = new RunEventRepo();
	$_POST = array_map('stripslashes_deep', $_POST);
	$meta_query = isset($_POST['meta_query']) ? $_POST['meta_query'] : [];
	$search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : "";

	$result = $repo->list($meta_query, $search);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single event */
function run_single_event()
{
	$repo = new RunEventRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$event_id = sanitize_text_field($_POST['event_id']);
	$result = $repo->single($event_id);

	if (!$result) {
		wp_send_json_error('Event not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new event */
function run_create_event()
{
	$repo = new RunEventRepo();
	$event_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->create($event_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing event */
function run_update_event()
{
	$repo = new RunEventRepo();
	$event_id = sanitize_text_field($_POST['ID']);
	$event_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($event_id, $event_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a event */
function run_delete_event($event_id)
{
	$repo = new RunEventRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$event_id = sanitize_text_field($_POST['event_id']);
	$result = $repo->delete($event_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_events', 'run_list_events');
add_action('wp_ajax_run_single_event', 'run_single_event');
add_action('wp_ajax_run_create_event', 'run_create_event');
add_action('wp_ajax_run_update_event', 'run_update_event');
add_action('wp_ajax_run_delete_event', 'run_delete_event');

/** List Deliveries */
function run_list_deliveries()
{
	$repo = new RunDeliveryRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single delivery */
function run_single_delivery()
{
	$repo = new RunDeliveryRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$delivery_id = sanitize_text_field($_POST['delivery_id']);
	$result = $repo->single($delivery_id);

	if (!$result) {
		wp_send_json_error('Delivery not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new delivery */
function run_create_delivery()
{
	$repo = new RunDeliveryRepo();
	$homeworkRepo = new RunHomeworkRepo();
	$enrollRepo = new RunEnrollRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$delivery_data['date']         = sanitize_text_field(date('Y-m-d H:i:s'));
	$delivery_data['homework_reply'] =  isset($_POST['homework_reply']) ? sakolawp_sanitize_html($_POST['homework_reply']) : '';
	$delivery_data['responses'] =  isset($_POST['responses']) ? json_encode($_POST['responses']) : NULL;
	$delivery_data['student_comment'] = isset($_POST['student_comment']) ? sakolawp_sanitize_html($_POST['student_comment']) : '';
	$delivery_data['status'] = sanitize_text_field('1');
	$delivery_data['homework_code'] = isset($_POST['homework_code']) ? sanitize_text_field($_POST['homework_code']) : '';
	$delivery_data['class_id']      = isset($_POST['class_id']) ? sanitize_text_field($_POST['class_id']) : '';
	$delivery_data['student_id']    = get_current_user_id();

	if (!$delivery_data['responses']) {
		wp_send_json_error('Responses are required', 400);
		die();
	}
	if (!$delivery_data['class_id']) {
		wp_send_json_error('Class is required', 400);
		die();
	}
	if (!$delivery_data['homework_code']) {
		wp_send_json_error('Homework is required', 400);
		die();
	}

	$enrollment = $enrollRepo->single_by(['student_id' => $delivery_data['student_id'], 'class_id' => $delivery_data['class_id']]);
	if (!$enrollment) {
		wp_send_json_error('Enrollment not found', 404);
		die();
	}
	$homework = $homeworkRepo->single_by_homework_code($delivery_data['homework_code']);
	if (!$homework) {
		wp_send_json_error('Homework not found', 404);
		die();
	}

	$delivery_data['section_id']    = $enrollment->section_id;
	$delivery_data['subject_id'] = $homework->subject_id;

	$post_id = $delivery_data['homework_code']; // homework code

	$result = $repo->create($delivery_data);

	if (!$result) {
		wp_send_json_error('Failed to submit assessment', 500);
		die();
	}

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing delivery */
function run_update_delivery()
{
	$repo = new RunDeliveryRepo();
	$delivery_id = sanitize_text_field($_POST['delivery_id']);
	$delivery_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($delivery_id, $delivery_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a delivery */
function run_delete_delivery($delivery_id)
{
	$repo = new RunDeliveryRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$delivery_id = sanitize_text_field($_POST['delivery_id']);
	$result = $repo->delete($delivery_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_deliveries', 'run_list_deliveries');
add_action('wp_ajax_run_single_delivery', 'run_single_delivery');
add_action('wp_ajax_run_create_delivery', 'run_create_delivery');
add_action('wp_ajax_run_update_delivery', 'run_update_delivery');
add_action('wp_ajax_run_delete_delivery', 'run_delete_delivery');

/** List Sections */
function run_list_sections()
{
	$repo = new RunSectionRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single section */
function run_single_section()
{
	$repo = new RunSectionRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$section_id = sanitize_text_field($_POST['section_id']);
	$result = $repo->single($section_id);

	if (!$result) {
		wp_send_json_error('Section not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new section */
function run_create_section()
{
	$repo = new RunSectionRepo();
	$section_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->create($section_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing section */
function run_update_section()
{
	$repo = new RunSectionRepo();
	$section_id = sanitize_text_field($_POST['section_id']);
	$section_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($section_id, $section_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a section */
function run_delete_section($section_id)
{
	$repo = new RunSectionRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$section_id = sanitize_text_field($_POST['section_id']);
	$result = $repo->delete($section_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_sections', 'run_list_sections');
add_action('wp_ajax_run_single_section', 'run_single_section');
add_action('wp_ajax_run_create_section', 'run_create_section');
add_action('wp_ajax_run_update_section', 'run_update_section');
add_action('wp_ajax_run_delete_section', 'run_delete_section');

/** List Accountabilities */
function run_list_accountabilities()
{
	$repo = new RunAccountabilityRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single accountability */
function run_single_accountability()
{
	$repo = new RunAccountabilityRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$accountability_id = sanitize_text_field($_POST['accountability_id']);
	$result = $repo->single($accountability_id);

	if (!$result) {
		wp_send_json_error('Accountability not found', 404);
		die();
	}
	wp_send_json_success($result, 200);
	die();
}

/** Create a new accountability */
function run_create_accountability()
{
	$repo = new RunAccountabilityRepo();
	$accountability_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->create($accountability_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing accountability */
function run_update_accountability()
{
	$repo = new RunAccountabilityRepo();
	$accountability_id = sanitize_text_field($_POST['accountability_id']);
	$accountability_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($accountability_id, $accountability_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a accountability */
function run_delete_accountability($accountability_id)
{
	$repo = new RunAccountabilityRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$accountability_id = sanitize_text_field($_POST['accountability_id']);
	$result = $repo->delete($accountability_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_accountabilities', 'run_list_accountabilities');
add_action('wp_ajax_run_single_accountability', 'run_single_accountability');
add_action('wp_ajax_run_create_accountability', 'run_create_accountability');
add_action('wp_ajax_run_update_accountability', 'run_update_accountability');
add_action('wp_ajax_run_delete_accountability', 'run_delete_accountability');
