<?php

// function sakolawp_add_homework($homework_data)
// {
// 	// Your code to add homework
// 	do_action('sakolawp_homework_added', $homework_data);
// }

/************************************
 * EMAIL TEMPLATES
 ************************************/

// DEFINE CONSTANTS TO HOLD EMAIL TEMPLATES
const SKWP_NEW_HOMEWORK_TEMPLATE = 'sakolawp_new_homework_email_template';
const SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE = [
	'subject' => "New Homework Available: {homework_title}",
	'template' => "Hi {student_name},\nA new homework has been assigned to you.\nHere are the details: {homework_details}",
];
const SKWP_HOMEWORK_REMINDER_TEMPLATE = 'sakolawp_homework_reminder_email_template';
const SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE =  [
	'subject' => "Homework Deadline Is Near: {homework_title}",
	'template' => "Hi {student_name},\nReminder: Homework is due soon. \nDetails: {homework_details}",
];
const SKWP_AVAILABLE_HOMEWORK_PLACEHOLDERS = ['homework_details', 'due_date', 'homework_title', 'homework_description'];

function sakolawp_add_email_template_settings()
{
	add_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE));
	add_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE));
}
register_activation_hook(__FILE__, 'sakolawp_add_email_template_settings');


/************************************
 * UTILITY FUNCTIONS
 ************************************/

function encode_email_template($template = [])
{
	return json_encode($template);
}

function decode_email_template($template = '[]')
{
	return json_decode($template);
}

function replace_placeholders($template, $data)
{
	if (!is_array($data) && !is_object($data)) {
		return $template;
	}

	// Convert object to array if necessary
	if (is_object($data)) {
		$data = (array) $data;
	}

	// Use a regex to find all placeholders in the template
	preg_match_all('/\{(\w+)\}/', $template, $matches);

	foreach ($matches[1] as $placeholder) {
		$replace = isset($data[$placeholder]) ? $data[$placeholder] : '';
		$template = str_replace('{' . $placeholder . '}', $replace, $template);
	}

	return $template;
}

function get_homework_args($homework_data)
{
	// Check if $homework_data is an array
	if (is_array($homework_data)) {
		// Convert array to object for consistent access
		$homework_data = (object)$homework_data;
	}

	// Initialize variables to hold extracted values
	$student_name = ''; // Assuming student name is not provided directly in $homework_data
	$homework_title = isset($homework_data->title) ? $homework_data->title : '';
	$homework_description = isset($homework_data->description) ? $homework_data->description : '';
	$due_date = isset($homework_data->date_end) ? $homework_data->date_end : '';
	$uploader_name = isset($homework_data->teacher_name) ? $homework_data->teacher_name : '';

	// Construct HTML string for basic homework details
	$homework_details = "
        <h2>{$homework_title}</h2>
        <p><strong>Description:</strong> {$homework_description}</p>
        <p><strong>Due Date:</strong> {$due_date}</p>
        <p><strong>Uploaded By:</strong> {$uploader_name}</p>
    ";

	return [
		'homework_details' => $homework_details,
		'due_date' => $due_date,
		'homework_title' => $homework_title,
		'homework_description' => $homework_description,
		'uploader_name' => $uploader_name,
	];
}

/*************************************
 * NEW HOMEWORK NOTIFICATION
 ************************************/
function sakolawp_send_homework_email($homework_data)
{
	$headers = array('Content-Type: text/html; charset=UTF-8');

	$args = get_homework_args($homework_data);
	error_log(print_r($args, true));
	error_log(print_r($homework_data, true));

	// GET home work template
	$template = decode_email_template(get_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE)));


	// Replace placeholders with actual data	
	$subject = replace_placeholders($template->subject, $args);
	$message = replace_placeholders($template->template, $args);
	$message = str_replace("\n", "<br/>", $message);

	// TODO: GET students assigned to this homework and send email to each student
	$to = get_option('admin_email'); // Send to the site admin or customize as needed

	wp_mail($to, $subject, $message, $headers);
}
add_action('sakolawp_homework_added', 'sakolawp_send_homework_email');


/*************************************
 * HOMEWORK REMINDERS
 ************************************/
function sakolawp_schedule_homework_reminder($homework_data)
{
	// Check if $homework_data is an array
	if (is_array($homework_data)) {
		// Convert array to object for consistent access
		$homework_data = (object)$homework_data;
	}

	if (!wp_next_scheduled('sakolawp_send_homework_reminder', array($homework_data))) {
		$timestamp = strtotime($homework_data->date_end . ' ' . $homework_data->time_end) - 86400; // 1 day before deadline
		wp_schedule_single_event($timestamp, 'sakolawp_send_homework_reminder', array($homework_data));
	}
}
add_action('sakolawp_homework_added', 'sakolawp_schedule_homework_reminder');

function sakolawp_send_homework_reminder($homework_data)
{
	$headers = array('Content-Type: text/html; charset=UTF-8');

	$args = get_homework_args($homework_data);

	// GET home work template
	$template = decode_email_template(get_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE)));

	// Replace placeholders with actual data	
	$subject = replace_placeholders($template->subject, $args);
	$message = replace_placeholders($template->template, $args);
	$message = str_replace("\n", "<br/>", $message);


	$to = get_option('admin_email'); // Send to the site admin or customize as needed

	wp_mail($to, $subject, $message, $headers);
}
add_action('sakolawp_send_homework_reminder', 'sakolawp_send_homework_reminder');

/************************************
 * WP AJAX EMAIL TEMPLATES HANDLERS
 ************************************/
function sakolawp_fetch_email_templates()
{
	// check_ajax_referer('fetch_templates_nonce', 'security');

	$availablePlaceholders = SKWP_AVAILABLE_HOMEWORK_PLACEHOLDERS;

	$templates = [
		[
			'id' => SKWP_NEW_HOMEWORK_TEMPLATE,
			'title' => 'New Homework Email',
			'content' => decode_email_template(get_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE))),
		],
		[
			'id' => SKWP_HOMEWORK_REMINDER_TEMPLATE,
			'title' => 'Homework Reminder Email',
			'content' => decode_email_template(get_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE))),
		]
	];

	wp_send_json_success(['templates' => $templates, 'placeholders' => $availablePlaceholders], 200);
}
add_action('wp_ajax_run_fetch_email_templates', 'sakolawp_fetch_email_templates');

function sakolawp_save_email_templates()
{
	// check_ajax_referer('save_templates_nonce', 'security');

	$templates = $_POST['templates'];

	foreach ($templates as $template) {
		update_option($template['id'], encode_email_template($template['content']));
	}

	wp_send_json_success();
}
add_action('wp_ajax_run_save_email_templates', 'sakolawp_save_email_templates');
