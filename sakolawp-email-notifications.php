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
	"subject" => "New Homework Available: {homework_title}",
	"template" => "Hi {student_name},\n\nA new homework has been assigned to you.\n\nHere are the details: {homework_details}\n\nBest Regards,\nRIG University Admin"
];
const SKWP_HOMEWORK_REMINDER_TEMPLATE = 'sakolawp_homework_reminder_email_template';
const SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE =  [
	"subject" => "Reminder: Homework Deadline is Near: {homework_title}",
	"template" => "Hi {student_name},\n\nYou have a homework is due on {due_date}. \n\nDetails: {homework_details}\n\nBest Regards,\nRIG University Admin"
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

function get_homework_args($homework_data)
{
	// Check if $homework_data is an array
	if (is_array($homework_data)) {
		// Convert array to object for consistent access
		$homework_data = (object)$homework_data;
	}

	// Initialize variables to hold extracted values
	$homework_title = isset($homework_data->title) ? $homework_data->title : '';
	$homework_description = isset($homework_data->description) ? $homework_data->description : '';
	$uploader_name = isset($homework_data->teacher_name) ? $homework_data->teacher_name : '';
	$due_date = isset($homework_data->date_end) ? $homework_data->date_end  : '';
	$due_date .= isset($homework_data->time_end) ? ' ' . $homework_data->time_end  : '';

	// Construct HTML string for basic homework details
	$homework_details = "
        <h4>{$homework_title}</h4>
        <div><strong>Description:</strong> {$homework_description}</div>
        <div><strong>Due Date:</strong> {$due_date}</div>
        <div><strong>Uploaded By:</strong> {$uploader_name}</div>
    ";

	return [
		'homework_details' => $homework_details,
		'due_date' => $due_date,
		'homework_title' => $homework_title,
		'homework_description' => $homework_description,
		'uploader_name' => $uploader_name,
		'current_date' => date('F j, Y'),
	];
}

/*************************************
 * NEW HOMEWORK NOTIFICATION
 ************************************/
function sakolawp_send_homework_email($homework_data)
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


		$args = get_homework_args($homework_data);
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
add_action('sakolawp_homework_added', 'sakolawp_send_homework_email');


/*************************************
 * HOMEWORK REMINDERS
 ************************************/
function sakolawp_schedule_homework_reminder($homework_data)
{
	$sakolawp_env = get_option('sakolawp_env', 'dev');
	if ($sakolawp_env === 'dev') {
		return;
	}

	try {
		// Check if $homework_data is an array
		if (is_array($homework_data)) {
			// Convert array to object for consistent access
			$homework_data = (object)$homework_data;
		}

		if (!wp_next_scheduled('sakolawp_send_homework_reminder', array($homework_data))) {
			$due_timestamp = strtotime($homework_data->date_end . ' ' . $homework_data->time_end);
			$current_timestamp = time();
			$time_difference = $due_timestamp - $current_timestamp;

			// If the homework is due in less than 24 hours, skip scheduling the reminder
			$skip_reminder = $time_difference < 86400;

			if (!$skip_reminder) {
				$timestamp = $due_timestamp  - 86400; // 1 day before deadline 
				wp_schedule_single_event($timestamp, 'sakolawp_send_homework_reminder', array($homework_data));
			}
		}
	} catch (\Throwable $th) {
		error_log(print_r($th, true));
	}
}
add_action('sakolawp_homework_added', 'sakolawp_schedule_homework_reminder');

function sakolawp_send_homework_reminder($homework_data)
{
	try {
		$headers = array('Content-Type: text/html; charset=UTF-8');

		$args = get_homework_args($homework_data);
		$args["link_to_login"] =  '<div><a href="' . site_url('/myaccount') . '" style="background-color: #4e80df;color: white;padding: 10px 20px;border: none;border-radius: 5px;font-size: 16px;cursor: pointer;text-decoration:none;">Login to your Dashboard</a></div>';


		// GET home work template
		$template = decode_email_template(get_option(SKWP_HOMEWORK_REMINDER_TEMPLATE, encode_email_template(SKWP_DEFAULT_HOMEWORK_REMINDER_TEMPLATE)));

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
			'title' => 'New Homework Email',
			'placeholders' => SKWP_AVAILABLE_HOMEWORK_PLACEHOLDERS,
			'content' => decode_email_template(get_option(SKWP_NEW_HOMEWORK_TEMPLATE, encode_email_template(SKWP_DEFAULT_NEW_HOMEWORK_TEMPLATE))),
		],
		[
			'id' => SKWP_HOMEWORK_REMINDER_TEMPLATE,
			'title' => 'Homework Reminder Email',
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
 * DAILY DIGEST
 ************************************/

function sakolawp_schedule_daily_digest()
{
	// $timestamp = wp_next_scheduled('sakolawp_daily_digest_hook');
	// wp_unschedule_event($timestamp, 'sakolawp_daily_digest_hook');
	if (!wp_next_scheduled('sakolawp_daily_digest_hook')) {
		// Schedule the event to run at 6 PM UTC daily & 7 PM GMT+1
		$time = strtotime('18:00:00');
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
		'student_id' => $student_id,
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
				background-color: #4e80df;
				color: white;
				text-align: center;
				padding: 10px 0;
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
		</style>
	</head>

	<body>
		<div class="email-container">
			<div class="email-header">
				<h1>LOGO</h1>
			</div>
			<div class="content pre-line">
				<?php echo $content; ?>
			</div>
			<div class="email-footer">
				<p>© 2024 Your Company. All rights reserved.</p>
			</div>
		</div>
	</body>

	</html>
<?php
	return ob_get_clean();
}
