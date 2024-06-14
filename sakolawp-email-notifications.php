<?php

// function sakolawp_add_homework($homework_data)
// {
// 	// Your code to add homework
// 	do_action('sakolawp_homework_added', $homework_data);
// }

/*************************************
 * NEW HOMEWORK NOTIFICATION
 ************************************/
function sakolawp_send_homework_email($homework_data)
{
	$headers = array('Content-Type: text/html; charset=UTF-8');

	// GET home work template and replace placeholders with data
	$subject = 'New Homework Assigned';
	$template = decode_email_template(get_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE)));
	$template = str_replace('{homework_details}', print_r($homework_data, true), $template);
	$message = $template;

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
	if (!wp_next_scheduled('send_homework_reminder', array($homework_data))) {
		$timestamp = strtotime($homework_data['deadline']) - 86400; // 1 day before deadline
		wp_schedule_single_event($timestamp, 'send_homework_reminder', array($homework_data));
	}
}
add_action('sakolawp_homework_added', 'sakolawp_schedule_homework_reminder');

function sakolawp_send_homework_reminder($homework_data)
{
	$subject = 'Homework Deadline Reminder';
	$message = 'Reminder: Homework is due soon. Details: ' . print_r($homework_data, true);
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$to = get_option('admin_email'); // Send to the site admin or customize as needed

	wp_mail($to, $subject, $message, $headers);
}
add_action('sakolawp_send_homework_reminder', 'sakolawp_send_homework_reminder');

/************************************
 * EMAIL TEMPLATES
 ************************************/

// DEFINE CONSTANTS TO HOLD EMAIL TEMPLATES
const SKWP_NEW_HOMEWORK_TEMPLATE = 'sakolawp_new_homework_email_template';
const SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE = [
	'subject' => 'New Homework Available',
	'template' => 'A new homework has been assigned. Details: {homework_details}',
];
const SKWP_HOMEWORK_REMINDER_TEMPLATE = 'sakolawp_homework_email_template';
const SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE =  [
	'subject' => 'New Homework Available',
	'template' => 'Reminder: Homework is due soon. Details: {homework_details}',
];
function encode_email_template($template = [])
{
	return json_encode($template);
}

function decode_email_template($template = '[]')
{
	return json_decode($template);
}

function sakolawp_add_email_template_settings()
{
	add_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE));
	add_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE));
}
register_activation_hook(__FILE__, 'sakolawp_add_email_template_settings');


/************************************
 * WP AJAX EMAIL TEMPLATES HANDLERS
 ************************************/
function sakolawp_fetch_email_templates()
{
	check_ajax_referer('fetch_templates_nonce', 'security');

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

	wp_send_json_success(['templates' => $templates]);
}
add_action('wp_ajax_run_fetch_email_templates', 'sakolawp_fetch_email_templates');

function sakolawp_save_email_templates()
{
	check_ajax_referer('save_templates_nonce', 'security');

	$templates = $_POST['templates'];

	foreach ($templates as $template) {
		update_option($template['id'], encode_email_template($template['content']));
	}

	wp_send_json_success();
}
add_action('wp_ajax_run_save_email_templates', 'sakolawp_save_email_templates');
