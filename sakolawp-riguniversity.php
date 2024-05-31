<?php

require_once SAKOLAWP_PLUGIN_DIR . '/repositories/class-subject-repo.php';

/** List Subjects */
function run_list_subjects()
{
	$repo = new RunSubjectRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$result = $repo->list($_POST['search']);

	wp_send_json_success($result, 200);
	die();
}

/** Read a single subject */
function run_single_subject()
{
	$repo = new RunSubjectRepo();
	// $_POST = array_map('stripslashes_deep', $_POST);

	$subject_id = $_POST['subject_id'];
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
	$subject_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->create($subject_data);

	wp_send_json_success($result, 201);
	die();
}

/** Update an existing subject */
function run_update_subject()
{
	$repo = new RunSubjectRepo();
	$subject_id = $_POST['subject_id'];
	$subject_data = array_map('stripslashes_deep', $_POST);

	$result = $repo->update($subject_id, $subject_data);

	wp_send_json_success($result, 200);
	die();
}

/** Delete a subject */
function run_delete_subject($subject_id)
{
	$repo = new RunSubjectRepo();
	$_POST = array_map('stripslashes_deep', $_POST);

	$subject_id = $_POST['subject_id'];
	$result = $repo->delete($subject_id);

	wp_send_json_success($result, 200);
	die();
}

add_action('wp_ajax_run_list_subjects', 'run_list_subjects');
add_action('wp_ajax_run_single_subject', 'run_single_subject');
add_action('wp_ajax_run_create_subject', 'run_create_subject');
add_action('wp_ajax_run_update_subject', 'run_update_subject');
add_action('wp_ajax_run_delete_subject', 'run_delete_subject');
