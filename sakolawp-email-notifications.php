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
	"subject" => "New Assessment Available: {homework_title}",
	"template" => "Hi {student_name},\n\nA new homework has been assigned to you.\n\n{homework_details}\n{link_to_login}\n\nBest Regards,\nRIG University Admin"
];
const SKWP_HOMEWORK_REMINDER_TEMPLATE = 'sakolawp_homework_reminder_email_template';
const SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE =  [
	"subject" => "Reminder: Assessment Deadline is Near: {homework_title}",
	"template" => "Hi {student_name},\n\nYou have a homework is due on {due_date}. \n\n{homework_details}\n{link_to_login}\n\nBest Regards,\nRIG University Admin"
];
const SKWP_AVAILABLE_HOMEWORK_PLACEHOLDERS = ['student_name', 'homework_details', 'due_date', 'homework_title', 'homework_description', "link_to_login"];

const SKWP_DAILY_DIGEST_TEMPLATE = 'sakolawp_daily_digest_email_template';
const SKWP_DEFAULT_DAILY_DIGEST_TEMPLATE =  [
	"subject" => "RIG University Daily Digest | {current_date}",
	"template" => "Hi {student_name},\n\nI hope you are are having a great day! I bring you the latest happenings at RIG University.\n\n{peer_reviews}\n{notifications}\n{events}\n\n{link_to_login}\n\nBest Regards,\nRIG University Admin"
];
const SKWP_AVAILABLE_DAILY_DIGEST_PLACEHOLDERS = ['student_name', 'current_date', 'peer_reviews', 'notifications', 'events', "link_to_login"];

function sakolawp_add_email_template_settings()
{
	add_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE));
	add_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE));
	add_option(SKWP_DAILY_DIGEST_TEMPLATE, encode_email_template(SKWP_DEFAULT_DAILY_DIGEST_TEMPLATE));
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

function get_homework_args($homework, $class_id)
{
	global $wpdb;
	// Check if $homework is an array
	if (is_array($homework)) {
		// Convert array to object for consistent access
		$homework = (object)$homework;
	}
	$class = $wpdb->get_row("SELECT name, start_date FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $class_id");

	$programStartDate = $class->start_date;
	// Initialize variables to hold extracted values
	$homework_id = isset($homework->homework_id) ? $homework->homework_id : '';
	$homework_title = isset($homework->title) ? $homework->title : '';
	$homework_description = isset($homework->description) ? $homework->description : '';
	$uploader_name = isset($homework->teacher_name) ? $homework->teacher_name : '';
	$homework_schedule = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}sakolawp_class_schedule WHERE class_id = class_id AND content_type = 'homework' AND content_id = '$homework_id'");

	if (!$homework_schedule) {
		$release_date = date('F j, Y', strtotime($programStartDate));
		$due_date = 'No deadline';
	} else {
		if ($homework_schedule->drip_method == 'specific_dates') {
			$release_date = date('F j, Y', strtotime($homework_schedule->release_date));
			$due_date = date('F j, Y', strtotime($homework_schedule->deadline_date));
		} else {
			$release_date = date('F j, Y', strtotime($programStartDate . " +{$homework_schedule->release_days} days"));
			$due_date = date('F j, Y', strtotime($release_date . " +{$homework_schedule->deadline_days} days"));
		}
	}

	// Construct HTML string for basic homework details
	$homework_details = '<div class="homework-details">
        <div class="item-heading">{homework_title}</div>
        <div><strong>Description:</strong> {homework_description}</div>
        <div><strong>Due Date:</strong> {due_date}</div>
        <div><strong>Uploaded By:</strong> {uploader_name}</div>
	</div>
    ';

	$args  = [
		'homework_id' => $homework_id,
		'due_date' => $due_date,
		'release_date' => $release_date,
		'homework_title' => $homework_title,
		'homework_description' => $homework_description,
		'uploader_name' => $uploader_name,
		'current_date' => date('F j, Y'),
	];
	$args['homework_details'] = replace_placeholders($homework_details, $args);
	return $args;
}

/*************************************
 * NEW HOMEWORK NOTIFICATION
 ************************************/
function sakolawp_send_homework_email($homework_data, $class_id)
{
	$sakolawp_env = get_option('sakolawp_env', 'dev');
	if ($sakolawp_env === 'dev') {
		return;
	}

	try {
		$headers = array('Content-Type: text/html; charset=UTF-8');

		// Check if $homework_data is an array
		if (is_array($homework_data)) {
			// Convert array to object for consistent access
			$homework_data = (object)$homework_data;
		}


		$args = get_homework_args($homework_data, $class_id);
		$args["link_to_login"] =  '<div><a href="' . site_url('/myaccount') . '" style="background-color: #4e80df;color: white;padding: 10px 20px;border: none;border-radius: 5px;font-size: 16px;cursor: pointer;text-decoration:none;">Login to your Dashboard</a></div>';
		// error_log(print_r($args, true));
		// error_log(print_r($homework_data, true));

		// GET home work template
		$template = decode_email_template(get_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE)));


		// GET students assigned to this homework and send email to each student
		$repo = new RunEnrollRepo();
		$students =  $repo->list(['class_id' => $homework_data->class_id, 'section_id' => $homework_data->section_id]);
		$to = "";

		foreach ($students as $key => $student) {
			# code...
			$to = $student->student_email;
			$args["student_name"] = $student->student_name;
			// Replace placeholders with actual data	
			$subject = replace_placeholders($template->subject, $args);
			$message = replace_placeholders($template->template, $args);
			wp_mail($to, $subject, html_wrap_template($message), $headers);
		}
	} catch (\Throwable $th) {
		error_log(print_r($th, true));
	}
}

/*************************************
 * HOMEWORK REMINDERS
 ************************************/
function sakolawp_send_homework_reminder($homework_data, $class_id)
{
	try {
		$headers = array('Content-Type: text/html; charset=UTF-8');

		$args = get_homework_args($homework_data, $class_id);
		$args["link_to_login"] =  '<div><a href="' . site_url('/myaccount') . '" style="background-color: #4e80df;color: white;padding: 10px 20px;border: none;border-radius: 5px;font-size: 16px;cursor: pointer;text-decoration:none;">Login to your Dashboard</a></div>';


		// GET home work template
		$template = decode_email_template(get_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE)));

		// GET students assigned to this homework and send email to each student
		$repo = new RunEnrollRepo();
		$students =  $repo->list(['class_id' => $homework_data->class_id, 'section_id' => $homework_data->section_id]);
		$to = "";

		foreach ($students as $key => $student) {
			$to = $student->student_email;
			$args["student_name"] = $student->student_name;

			// Replace placeholders with actual data	
			$subject = replace_placeholders($template->subject, $args);
			$message = replace_placeholders($template->template, $args);
			wp_mail($to, $subject, html_wrap_template($message), $headers);
		}
	} catch (\Throwable $th) {
		error_log(print_r($th, true));
	}
}
add_action('sakolawp_send_homework_reminder', 'sakolawp_send_homework_reminder');

/************************************
 * WP AJAX EMAIL TEMPLATES HANDLERS
 ************************************/
function sakolawp_fetch_email_templates()
{
	// check_ajax_referer('fetch_templates_nonce', 'security');

	$templates = [
		[
			'id' => SKWP_NEW_HOMEWORK_TEMPLATE,
			'title' => 'New Assessment Email',
			'placeholders' => SKWP_AVAILABLE_HOMEWORK_PLACEHOLDERS,
			'content' => decode_email_template(get_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE))),
		],
		[
			'id' => SKWP_HOMEWORK_REMINDER_TEMPLATE,
			'title' => 'Assessment Reminder Email',
			'placeholders' => SKWP_AVAILABLE_HOMEWORK_PLACEHOLDERS,
			'content' => decode_email_template(get_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE))),
		],
		[
			'id' => SKWP_DAILY_DIGEST_TEMPLATE,
			'title' => 'Daily Digest Email',
			'placeholders' => SKWP_AVAILABLE_DAILY_DIGEST_PLACEHOLDERS,
			'content' => decode_email_template(get_option(SKWP_DAILY_DIGEST_TEMPLATE, encode_email_template(SKWP_DEFAULT_DAILY_DIGEST_TEMPLATE))),
		],
	];

	wp_send_json_success(['templates' => $templates], 200);
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


/************************************
 * NEW HOME WORK & LESSON SCHEDULER
 ************************************/
// Add Custom Schedules:
function custom_cron_schedules($schedules)
{
	$schedules['48_hours'] = array(
		'interval' => 172800, // 48 hours in seconds
		'display' => __('Every 48 Hours')
	);
	$schedules['24_hours'] = array(
		'interval' => 86400, // 24 hours in seconds
		'display' => __('Every 24 Hours')
	);
	return $schedules;
}
add_filter('cron_schedules', 'custom_cron_schedules');


// Schedule Assessment and Lesson Reminders:
function schedule_all_class_reminders($schedules)
{
	// error_log("Scheduling --------> schedule_all_class_reminders");
	$schedule_by_class = array_group_by($schedules, 'class_id');
	$classRepo = new RunClassRepo();
	foreach ($schedule_by_class as $class_id => $schedules) {
		$class = $classRepo->single($class_id);
		// error_log("Scheduling --------> Class: " . $class->name);
		foreach ($schedules as $schedule) {
			$content_type = $schedule['content_type'];
			$content_id = $schedule['content_id'];

			if (empty($class)) {
				continue;
			}

			[
				'release_date' => $release_date_local,
				'due_date' => $deadline_date_local
			] = get_schedule_dates($schedule, $class->start_date);

			// Convert local times to UTC
			$release_date = convert_to_utc($release_date_local);
			$deadline_date = convert_to_utc($deadline_date_local);

			$unique_id = $content_type . '_' . $content_id . '_' . $release_date . '_' . $class_id;

			// Schedule release date reminder
			// if ($content_type == 'homework' || $content_type == 'lesson') {
			$hook_release = 'sakolawp_send_release_reminder_' . $unique_id;
			add_action($hook_release, 'sakolawp_send_release_reminder', 10, 3);
			wp_schedule_single_event($release_date, $hook_release, [$content_id, $content_type, $class_id]);
			// }

			// Schedule due date reminders (48 hours and 24 hours before deadline)
			// if ($content_type == 'homework') {
			$unique_id_48 = $content_type . '_' . $content_id . '_' . ($deadline_date - 172800) . '_' . $class_id;
			$unique_id_24 = $content_type . '_' . $content_id . '_' . ($deadline_date - 86400) . '_' . $class_id;

			$hook_48 = 'sakolawp_send_due_reminder_' . $unique_id_48;
			$hook_24 = 'sakolawp_send_due_reminder_' . $unique_id_24;

			add_action($hook_48, 'sakolawp_send_due_reminder', 10, 3);
			add_action($hook_24, 'sakolawp_send_due_reminder', 10, 3);

			wp_schedule_single_event($deadline_date - 172800, $hook_48, array($content_id, $content_type, $class_id));
			wp_schedule_single_event($deadline_date - 86400, $hook_24, array($content_id, $content_type, $class_id));
			// }
		}
	}
}

// Create Callback Functions:
function sakolawp_send_release_reminder($content_id, $content_type, $class_id)
{
	// error_log("=======>>>>>>> Scheduled cron job is running -> sakolawp_send_release_reminder");
	if ($content_type == 'homework') {
		$repo = new RunHomeworkRepo();
		$homework_data = $repo->single($content_id);
		sakolawp_send_homework_email($homework_data, $class_id);
	}
	if ($content_type == 'lesson') {
		$repo = new RunLessonRepo();
		// $lesson_data = $repo->single($content_id);
		// do_action('sakolawp_lesson_added', $lesson_data);
		error_log("WE HAVE NOT SET UP LESSON RELEASE NOTIFICATION");
	}
}
function sakolawp_send_due_reminder($content_id, $content_type, $class_id)
{
	error_log("Scheduled cron job is running -> sakolawp_send_due_reminder");
	if ($content_type == 'homework') {
		$repo = new RunHomeworkRepo();
		$homework_data = $repo->single($content_id);
		sakolawp_send_homework_reminder($homework_data, $class_id);
	}
	if ($content_type == 'lesson') {
		$repo = new RunLessonRepo();
		// $lesson_data = $repo->single($content_id);
		// do_action('sakolawp_lesson_added', $lesson_data);
		error_log("WE HAVE NOT SET UP LESSON DUE DATE NOTIFICATION");
	}
}

add_action('sakolawp_send_release_reminder', 'sakolawp_send_release_reminder', 10, 3);
add_action('sakolawp_send_due_reminder', 'sakolawp_send_due_reminder', 10, 3);

// Handle Schedule Updates:
function sakolawp_update_schedules($new_schedules)
{
	try {
		// Clear existing cron jobs
		clear_existing_cron_jobs($new_schedules);

		// Schedule new reminders
		schedule_all_class_reminders($new_schedules);
	} catch (\Throwable $th) {
		wp_mail(
			get_option('admin_email'),
			'Error scheduling homework reminders',
			$th->getMessage() . "\n" . $th->getTraceAsString()
		);
	}
}

function clear_existing_cron_jobs($criteria)
{
	// Ensure criteria is an array
	if (!is_array($criteria)) {
		return;
	}

	// Get all scheduled events
	$crons = _get_cron_array();

	// Loop through events and unschedule those related to the specified criteria
	foreach ($crons as $timestamp => $cron) {
		foreach ($cron as $hook => $dings) {
			foreach ($dings as $sig => $data) {
				// Check if the hook is related to release or due reminders
				if (strpos($hook, 'sakolawp_send_release_reminder_') === 0 || strpos($hook, 'sakolawp_send_due_reminder_') === 0) {
					// Check if the content_id and content_type in the args match the specified criteria
					foreach ($criteria as $criterion) {
						if (
							isset($data['args'][0]) && $data['args'][0] == $criterion['content_id'] &&
							isset($data['args'][1]) && $data['args'][1] == $criterion['content_type']
						) {
							wp_unschedule_event($timestamp, $hook, $data['args']);
							break; // No need to check other criteria if one matches
						}
					}
				}
			}
		}
	}
}


/************************************
 * DAILY DIGEST
 ************************************/

function sakolawp_schedule_daily_digest()
{
	// $timestamp = wp_next_scheduled('sakolawp_daily_digest_hook');
	// wp_unschedule_event($timestamp, 'sakolawp_daily_digest_hook');
	if (!wp_next_scheduled('sakolawp_daily_digest_hook')) {
		// Schedule the event to run at 5 PM UTC daily & 6 PM GMT+1
		$time = convert_to_utc(strtotime('18:00:00'));
		wp_schedule_event($time, 'daily', 'sakolawp_daily_digest_hook');
	}
}
register_activation_hook(__FILE__, 'sakolawp_schedule_daily_digest');

function sakolawp_clear_scheduled_digest()
{
	$timestamp = wp_next_scheduled('sakolawp_daily_digest_hook');
	wp_unschedule_event($timestamp, 'sakolawp_daily_digest_hook');
	// Also clear the scheduled event for sending homework reminders
	$timestamp = wp_next_scheduled('sakolawp_send_homework_reminder');
	wp_unschedule_event($timestamp, 'sakolawp_daily_digest_hook');
}
register_deactivation_hook(__FILE__, 'sakolawp_clear_scheduled_digest');
// add_action('init', 'sakolawp_schedule_daily_digest');
add_action('sakolawp_daily_digest_hook', 'sakolawp_send_daily_digest');

// for testing
// add_action('init', 'sakolawp_send_daily_digest');
function sakolawp_send_daily_digest()
{
	$repo = new RunEnrollRepo();
	// Get all peer_reviews added in the last 24 hours
	$enroll = $repo->list();

	if (count($enroll) === 0) {
		return []; // No enrollments found
	}

	// Fetch the email template from options
	$template = decode_email_template(get_option(SKWP_DAILY_DIGEST_TEMPLATE, encode_email_template(SKWP_DEFAULT_DAILY_DIGEST_TEMPLATE)));

	foreach ($enroll as $key => $current_enrollment) {
		// TODO: Skip inactive enrollments
		// if (!$current_enrollment->isActive) {
		// 	continue;
		// }

		$args = [];
		$args["student_name"] = $current_enrollment->student_name;
		$args["student_email"] = $current_enrollment->student_email;
		$args["peer_reviews"] = sakolawp_get_peer_reviews($current_enrollment);
		$args["notifications"] = '';
		$args["current_date"] = date('M d, Y');
		$args["events"] = sakolawp_get_events($current_enrollment);
		$args["link_to_login"] = '<div><a href="' . site_url('/myaccount') . '" style="background-color: #4e80df;color: white;padding: 10px 20px;border: none;border-radius: 5px;font-size: 16px;cursor: pointer;text-decoration:none;">Login to your Dashboard</a></div>';

		if (empty($args["peer_reviews"]) && empty($args["notifications"]) && empty($args["events"])) {
			continue; // Skip if no peer reviews, notifications, or events found
		}

		// Replace placeholders with actual data
		$subject = replace_placeholders($template->subject, $args);
		$message = replace_placeholders($template->template, $args);

		$to = $args["student_email"];
		wp_mail($to, $subject, html_wrap_template($message), ['Content-Type: text/html; charset=UTF-8']);
	}
}

function sakolawp_get_peer_reviews($enrollment)
{
	$class_id = $enrollment->class_id;
	$section_id = $enrollment->section_id;
	$student_id = $enrollment->student_id;
	$deliveryRepo = new RunDeliveryRepo();
	$peer_reviews = $deliveryRepo->peer_reviews([
		'reviewer_id' => $student_id,
		'section_id' => $section_id,
		'class_id' => $class_id,
		// 'interval_in_days' => 5,
	]);

	if (count($peer_reviews) === 0) {
		return ''; // No peer reviews found
	}
	ob_start();
?>
	<div class="no-pre-line">
		<div class="heading">Homeworks Awaiting Your Review</div>
		<?php
		$grouped_by_homework = array_group_by($peer_reviews, 'homework_code');
		foreach ($grouped_by_homework as $deliveries) :
		?>
			<div class="my-8 list-item">
				<div class="item-heading"><?php echo esc_html($deliveries[0]->homework_title); ?></div>
				<ul class="list-disc">
					<?php
					foreach ($deliveries as $row) :
						$delivery_id = $row->delivery_id;
						$peer_id = $row->student_id;
						$repo = new RunEnrollRepo();
						$peer_enroll = $repo->list(['class_id' => $class_id, 'section_id' => $section_id, 'student_id' => $peer_id]);
						$peer_enroll  = count($peer_enroll) > 0 ? $peer_enroll[0] : null;

						if (!$peer_enroll) continue;
					?>
						<li class="">
							<a href="<?php echo add_query_arg('delivery_id', $delivery_id, home_url('peer_review_room')); ?>" class="btn btn-primary btn-rounded btn-sm skwp-btn">

								<div class="">
									<?php
									echo esc_html($peer_enroll->student_name);
									if (isset($peer_enroll->section_name) && isset($peer_enroll->accountability_name)) {
									?>
										<i class="text-sm"> <?php echo esc_html($peer_enroll->section_name) . ' - ' . $peer_enroll->accountability_name; ?> </i>
									<?php } ?>
								</div>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php endforeach; ?>
	</div>
<?php
	$output = ob_get_clean();
	return $output;
}

function sakolawp_get_events($enrollment)
{
	$class_id = $enrollment->class_id;
	$sakolawp_event_args = array(
		'post_type' => 'sakolawp-event',
		'posts_per_page' => -1,
		'ignore_sticky_posts' => true,
		'meta_key' => '_sakolawp_event_class_id',
		'meta_value' => $class_id,
	);
	$sakolawp_events = get_posts($sakolawp_event_args);
	if (count($sakolawp_events) === 0) {
		return ''; // No events found
	}
	ob_start(); ?>
	<div class="no-pre-line">
		<div class="heading">Upcoming Events</div>
		<?php
		foreach ($sakolawp_events as $post) :
			// $img_url = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');

			$sakolawp_date_event = esc_attr(get_post_meta($post->ID, '_sakolawp_event_date', true));
			$sakolawp_hour_event = esc_attr(get_post_meta($post->ID, '_sakolawp_event_date_clock', true));
		?>

			<div class="list-item">
				<a href="<?php the_permalink($post->ID); ?>">
					<div class="flex gap-2 items-center">
						<div class="image-news">
							<?php if (has_post_thumbnail($post->ID)) {
								the_post_thumbnail();
							} ?>
						</div>
						<div class="">
							<div class="item-heading">
								<?php $excerpt = get_the_excerpt($post->ID);
								$excerpt = substr($excerpt, 0, 70);
								$result = substr($excerpt, 0, strrpos($excerpt, ' '));
								echo esc_html($result); ?>
							</div>
							<div class="">
								<?php echo date("F j, Y, g:i a", strtotime(esc_html($sakolawp_date_event . '' . esc_html($sakolawp_hour_event)))); ?>

							</div>
						</div>
					</div>
				</a>
			</div>

		<?php endforeach; ?>
	</div>
<?php
	$output = ob_get_clean();
	return $output;
}

function html_wrap_template($content)
{
	ob_start(); ?>
	<!DOCTYPE html>
	<html>

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Email Template</title>
		<style>
			* {
				box-sizing: border-box;
			}

			body {
				font-family: Arial, sans-serif;
				line-height: 1.5;
				background-color: #fafafa;
				font-size: 0.9em;
			}

			.pre-line {
				white-space: pre-line;
			}

			.no-pre-line {
				white-space: normal;
			}

			.email-container {
				width: 100%;
				max-width: 600px;
				margin: 0 auto;
				border: 1px solid #ddd;
				background-color: #ffffff;
			}

			.email-header,
			.email-footer {
				text-align: center;
				padding: 10px 0;
			}

			.email-footer {
				border-top-color: #4e80df;
				border-top-width: 2px;
				border-top-style: solid;
			}

			.email-header {
				border-bottom-color: #4e80df;
				border-bottom-width: 2px;
				border-bottom-style: solid;
			}

			.logo {
				width: 100px;
				height: auto;
			}

			.list-item {
				border-radius: 15px;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
				margin-bottom: 10px;
				padding: 15px;
			}

			.list-item a,
			a.list-item {
				text-decoration: none;
			}

			.content {
				padding: 20px;
			}

			.heading {
				font-size: 1.3em;
				text-decoration: none;
				margin-bottom: 10px;
			}

			.item-heading {
				font-size: 1.1em;
			}

			.flex {
				display: flex !important;
			}

			.flex-col {
				flex-direction: column !important;
			}

			.gap-2 {
				gap: 0.5rem
					/* 8px */
					!important;
			}

			.gap-4 {
				gap: 1rem !important;
			}

			.items-center {
				align-items: center !important;
			}

			.homework-details {
				border-radius: 15px;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
				padding: 15px;
			}
		</style>
	</head>

	<body>
		<div class="email-container">
			<div class="email-header">
				<img class="logo" src="<?php echo esc_url(wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full')[0]); ?>" src="RIG University" />
			</div>
			<div class="content pre-line">
				<?php echo $content; ?>
			</div>
			<div class="email-footer">
				<small>You are receiving this email because you are currently enrolled in a program at RIG University. if you have any concerns or complaints please send an email to [support email]</small>
				<p>© 2024 RIG University. All rights reserved.</p>
			</div>
		</div>
	</body>

	</html>
<?php
	return ob_get_clean();
}
