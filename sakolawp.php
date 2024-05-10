<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://themesawesome.com/
 * @since             1.0.0
 * @package           Sakolawp
 *
 * @wordpress-plugin
 * Plugin Name:       SakolaWP
 * Plugin URI:        demosakolawp.themesawesome.com
 * Description:       School Management System to manage the school activity like school routine, attendance, exam, homework, etc.
 * Version:           1.1.2
 * Author:            Themes Awesome
 * Author URI:        https://themesawesome.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sakolawp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define('SAKOLAWP_PLUGIN', __FILE__);

define('SAKOLAWP_PLUGIN_BASENAME', plugin_basename(SAKOLAWP_PLUGIN));

define('SAKOLAWP_PLUGIN_NAME', trim(dirname(SAKOLAWP_PLUGIN_BASENAME), '/'));

define('SAKOLAWP_PLUGIN_DIR', untrailingslashit(dirname(SAKOLAWP_PLUGIN)));

define('SAKOLAWP_VERSION', '1.1.2');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sakolawp-activator.php
 */
function activate_sakolawp()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-sakolawp-activator.php';
	Sakolawp_Activator::activate();
}

function sakolawp_create_db()
{
	include('sakolawp_create_db.php');
}

function sakolawp_add_roles()
{
	include('sakolawp_add_roles.php');
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sakolawp-deactivator.php
 */
function deactivate_sakolawp()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-sakolawp-deactivator.php';
	Sakolawp_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_sakolawp');
register_activation_hook(__FILE__, 'sakolawp_add_roles');
register_activation_hook(__FILE__, 'sakolawp_create_db');
register_deactivation_hook(__FILE__, 'deactivate_sakolawp');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-sakolawp.php';
require_once SAKOLAWP_PLUGIN_DIR . '/settings.php';
require_once SAKOLAWP_PLUGIN_DIR . '/sakolawp-post-type.php';
require_once SAKOLAWP_PLUGIN_DIR . '/sakolawp-shortcodes.php';
require_once SAKOLAWP_PLUGIN_DIR . '/includes/element-helper.php';

function sakolawp_new_elements()
{
	require_once SAKOLAWP_PLUGIN_DIR . '/elementor-widgets/myaccount/myaccount-control.php';
	require_once SAKOLAWP_PLUGIN_DIR . '/elementor-widgets/register/register-control.php';
	require_once SAKOLAWP_PLUGIN_DIR . '/elementor-widgets/login/login-control.php';
}

add_action('elementor/widgets/widgets_registered', 'sakolawp_new_elements');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sakolawp()
{

	$plugin = new Sakolawp();
	$plugin->run();
}
run_sakolawp();

/* HTML Sanitizing */
if (!function_exists('sakolawp_sanitize_html')) {
	function sakolawp_sanitize_html($input)
	{
		$input = wp_specialchars_decode(stripslashes($input));

		$allowed_html = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'img'    => array(
				'alt'    => array(),
				'src'    => array(),
				'srcset' => array(),
				'title'  => array(),
			),
			'strong' => array(),
		);
		$output       = wp_kses_stripslashes($input, $allowed_html);

		return $output;
	}
}

/* Updater */
require_once 'includes/lb_helper.php';
$sakola_lbapi = new SakolaLicenseBoxAPI();

$sakola_lb_verify_res = get_option('sakola_lb_verify_res');
if ($sakola_lb_verify_res == false) {
	$sakola_lb_verify_res = array('status' => true);
}

// Performs background license check, pass TRUE as 1st parameter to perform periodic verifications only.
//$sakola_lb_verify_res = $sakola_lbapi->verify_license();

// Performs update check, you can easily change the duration of update checks.
if (false === ($lb_update_res = get_transient('licensebox_next_update_check'))) {
	$lb_update_res = $sakola_lbapi->check_update();
	set_transient('licensebox_next_update_check', $lb_update_res, 12 * HOUR_IN_SECONDS);
}

// register page template
add_filter('page_template', 'sakolawp_custom_user_page_template');
function sakolawp_custom_user_page_template($page_template)
{

	if (get_page_template_slug() == 'register-template.php') {
		if (!is_user_logged_in()) {
			$page_template = dirname(__FILE__) . '/template/register-template.php';
		} else {
			wp_redirect(home_url('myaccount'));
			exit;
		}
	}
	if (get_page_template_slug() == 'myaccount-template.php') {
		if (!is_user_logged_in()) {
			$page_template = dirname(__FILE__) . '/template/myaccount-template.php';
		} else {
			wp_redirect(home_url('myaccount'));
			exit;
		}
	}
	return $page_template;
}
add_filter('theme_page_templates', 'sakolawp_custom_user_add_template_to_select', 10, 4);
function sakolawp_custom_user_add_template_to_select($post_templates, $wp_theme, $post, $post_type)
{

	// Add custom template named template-custom.php to select dropdown 
	$post_templates['register-template.php'] = esc_html__('Register', 'sakolawp');
	$post_templates['myaccount-template.php'] = esc_html__('My Account', 'sakolawp');

	return $post_templates;
}

/**
 * Sakolawp custom function
 */

// Sakolawp Users Restriction
function sakolawp_admin_init()
{
	if (!defined('DOING_AJAX') && !current_user_can('administrator')) {
		wp_redirect(home_url());
		exit();
	}
}
add_action('admin_init', 'sakolawp_admin_init');

add_action('admin_init', 'sakolawp_redirect_non_admin_users');
function sakolawp_redirect_non_admin_users()
{
	if (!current_user_can('manage_options') && !defined('DOING_AJAX') && !current_user_can('administrator')) {
		wp_redirect(home_url('myaccount'));
		exit;
	}
}

function sakolawp_redirect_after_login_per_role($redirect_to, $requested_redirect_to, $user)
{
	//retrieve current user info 
	global $wp_roles;

	$roles = $wp_roles->roles;
	$setting = get_option('sakolawp_settings');

	//is there a user to check?
	foreach ($roles as $role_slug => $role_options) {
		if (isset($user->roles) && is_array($user->roles)) {
			//check for admins
			if (in_array($role_slug, $user->roles)) {

				$admin_pages = $setting['sakolawp_field_' . $role_slug];
				$admin_custom_pages = $setting['sakolawp_field_custom_url_' . $role_slug];
				$redirect = (empty($admin_custom_pages)) ? get_admin_url() . $admin_pages : $admin_custom_pages;

				// redirect them to the default place
				return $redirect;
			}
		}
	}
}
add_filter("login_redirect", "sakolawp_redirect_after_login_per_role", 10, 3);

function sakolawp_manage_classes()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_class',
		array(
			'name' => $class_name,
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-class')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_save_classes_setting', 'sakolawp_manage_classes');
add_action('admin_post_save_classes_setting', 'sakolawp_manage_classes');

function sakolawp_manage_edit_classes()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$wpdb->update(
		$wpdb->prefix . 'sakolawp_class',
		array(
			'name' => $class_name,
		),
		array(
			'class_id' => $class_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-class')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_edit_classes_setting', 'sakolawp_manage_edit_classes');
add_action('admin_post_edit_classes_setting', 'sakolawp_manage_edit_classes');

function sakolawp_manage_delete_classes()
{
	global $wpdb;

	$class_id = $_POST['class_id'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_class',
		array(
			'class_id' => $class_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-class')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_delete_classes_setting', 'sakolawp_manage_delete_classes');
add_action('admin_post_delete_classes_setting', 'sakolawp_manage_delete_classes');

function sakolawp_manage_section()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$teacher_id = sanitize_text_field($_POST['teacher_id']);
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_section',
		array(
			'name' => $class_name,
			'class_id' => $class_id,
			'teacher_id' => $teacher_id,

		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-section')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_save_section_setting', 'sakolawp_manage_section');
add_action('admin_post_save_section_setting', 'sakolawp_manage_section');

function sakolawp_manage_edit_section()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$teacher_id = sanitize_text_field($_POST['teacher_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$wpdb->update(
		$wpdb->prefix . 'sakolawp_section',
		array(
			'name' => $class_name,
			'class_id' => $class_id,
			'teacher_id' => $teacher_id
		),
		array(
			'section_id' => $section_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-section')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_edit_section_setting', 'sakolawp_manage_edit_section');
add_action('admin_post_edit_section_setting', 'sakolawp_manage_edit_section');

function sakolawp_manage_delete_section()
{
	global $wpdb;

	$section_id = $_POST['section_id'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_section',
		array(
			'section_id' => $section_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-section')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_delete_section_setting', 'sakolawp_manage_delete_section');
add_action('admin_post_delete_section_setting', 'sakolawp_manage_delete_section');

function sakolawp_manage_subject()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$teacher_id = sanitize_text_field($_POST['teacher_id']);
	$total_lab = sanitize_text_field($_POST['total_lab']);
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_subject',
		array(
			'name' => $class_name,
			'class_id' => $class_id,
			'section_id' => $section_id,
			'teacher_id' => $teacher_id,
			'total_lab' => $total_lab,

		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-subject')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_save_subject_setting', 'sakolawp_manage_subject');
add_action('admin_post_save_subject_setting', 'sakolawp_manage_subject');

function sakolawp_manage_edit_subject()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$teacher_id = sanitize_text_field($_POST['teacher_id']);
	$subject_id = sanitize_text_field($_POST['subject_id']);
	$total_lab = sanitize_text_field($_POST['total_lab']);
	$wpdb->update(
		$wpdb->prefix . 'sakolawp_subject',
		array(
			'name' => $class_name,
			'class_id' => $class_id,
			'section_id' => $section_id,
			'teacher_id' => $teacher_id,
			'total_lab' => $total_lab,
		),
		array(
			'subject_id' => $subject_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-subject')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_edit_subject_setting', 'sakolawp_manage_edit_subject');
add_action('admin_post_edit_subject_setting', 'sakolawp_manage_edit_subject');

function sakolawp_manage_delete_subject()
{
	global $wpdb;

	$subject_id = $_POST['subject_id'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_subject',
		array(
			'subject_id' => $subject_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-subject')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_delete_subject_setting', 'sakolawp_manage_delete_subject');
add_action('admin_post_delete_subject_setting', 'sakolawp_manage_delete_subject');

function sakolawp_manage_routine()
{
	global $wpdb;

	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$subject_id = sanitize_text_field($_POST['subject_id']);

	$time_start     = sanitize_text_field($_POST['time_start'] + (12 * ($_POST['starting_ampm'] - 1)));
	$time_end       = sanitize_text_field($_POST['time_end'] + (12 * ($_POST['ending_ampm'] - 1)));
	$time_start_min = sanitize_text_field($_POST['time_start_min']);
	$time_end_min   = sanitize_text_field($_POST['time_end_min']);
	$day            = sanitize_text_field($_POST['day']);

	$running_year = get_option('running_year');
	$year           = sanitize_text_field($running_year);

	$teacher = $wpdb->get_row("SELECT teacher_id FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = $subject_id");
	$teacher_id = sanitize_text_field($teacher->teacher_id);

	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_class_routine',
		array(
			'class_id' => $class_id,
			'section_id' => $section_id,
			'subject_id' => $subject_id,
			'time_start' => $time_start,
			'time_end' => $time_end,
			'time_start_min' => $time_start_min,
			'time_end_min' => $time_end_min,
			'day' => $day,
			'year' => $year,
			'teacher_id' => $teacher_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-routine')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_save_routine_setting', 'sakolawp_manage_routine');
add_action('admin_post_save_routine_setting', 'sakolawp_manage_routine');

function sakolawp_edit_routine()
{
	global $wpdb;

	$class_routine_id = sanitize_text_field($_POST['class_routine_id']);

	$time_start     = sanitize_text_field($_POST['time_start'] + (12 * ($_POST['starting_ampm'] - 1)));
	$time_end       = sanitize_text_field($_POST['time_end'] + (12 * ($_POST['ending_ampm'] - 1)));
	$time_start_min = sanitize_text_field($_POST['time_start_min']);
	$time_end_min   = sanitize_text_field($_POST['time_end_min']);
	$day            = sanitize_text_field($_POST['day']);

	$wpdb->update(
		$wpdb->prefix . 'sakolawp_class_routine',
		array(
			'time_start' => $time_start,
			'time_end' => $time_end,
			'time_start_min' => $time_start_min,
			'time_end_min' => $time_end_min,
			'day' => $day,
		),
		array(
			'class_routine_id' => $class_routine_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-routine')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_sakolawp_edit_routine', 'sakolawp_edit_routine');
add_action('admin_post_sakolawp_edit_routine', 'sakolawp_edit_routine');

function sakolawp_manage_delete_routine()
{
	global $wpdb;

	$class_routine_id = $_POST['class_routine_id'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_class_routine',
		array(
			'class_routine_id' => $class_routine_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-routine')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_delete_routine_setting', 'sakolawp_manage_delete_routine');
add_action('admin_post_delete_routine_setting', 'sakolawp_manage_delete_routine');

function sakolawp_assign_student()
{
	global $wpdb;

	$student_id = sanitize_text_field($_POST['student_id']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$date_added = sanitize_text_field(time());
	$running_year = get_option('running_year');
	$year           = sanitize_text_field($running_year);
	$random_code = sanitize_text_field(substr(md5(rand(0, 1000000)), 0, 7));
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_enroll',
		array(
			'enroll_code' => $random_code,
			'student_id' => $student_id,
			'class_id' => $class_id,
			'section_id' => $section_id,
			'roll' => $random_code,
			'date_added' => $date_added,
			'year' => $year
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-student-area')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_add_student_enroll', 'sakolawp_assign_student');
add_action('admin_post_add_student_enroll', 'sakolawp_assign_student');

function sakolawp_create_homework()
{
	global $wpdb;

	$title = sanitize_text_field($_POST['title']);
	$description = sanitize_textarea_field($_POST['description']);
	$time_end = sanitize_text_field($_POST['time_end']);
	$date_end = sanitize_text_field($_POST['date_end']);

	$datetime = sanitize_text_field(strtotime(date('d-m-Y', strtotime($_POST['date_end']))));

	$type = sanitize_text_field($_POST['type']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$file_name         = $_FILES["file_name"]["name"];
	$section_id = sanitize_text_field($_POST['section_id']);
	$subject_id = sanitize_text_field($_POST['subject_id']);
	$uploader_type  = sanitize_text_field('teacher');
	$uploader_id  = sanitize_text_field($_POST['uploader_id']);
	$homework_code = sanitize_text_field(substr(md5(rand(100000000, 200000000)), 0, 10));

	$wpdb->insert(
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
			'datetime' => $datetime,
			'type' => $type,
			'file_name' => $file_name
		)
	);

	wp_redirect(home_url()); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('awp_ajax_nopriv_save_create_homework', 'sakolawp_create_homework');
add_action('awp_ajax_save_create_homework', 'sakolawp_create_homework');

function sakolawp_manage_create_exam()
{
	global $wpdb;

	$name = sanitize_text_field($_POST['name']);
	$start_exam = sanitize_text_field($_POST['start']);
	$end_exam = sanitize_text_field($_POST['end']);
	$running_year = get_option('running_year');
	$year           = sanitize_text_field($running_year);
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_exam',
		array(
			'name' => $name,
			'start_exam' => $start_exam,
			'end_exam' => $end_exam,
			'year' => $year
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-settings')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_save_exam_setting', 'sakolawp_manage_create_exam');
add_action('admin_post_save_exam_setting', 'sakolawp_manage_create_exam');

function sakolawp_manage_edit_exam()
{
	global $wpdb;

	$name = sanitize_text_field($_POST['name']);
	$start_exam = sanitize_text_field($_POST['start']);
	$end_exam = sanitize_text_field($_POST['end']);
	$exam_id = sanitize_text_field($_POST['exam_id']);
	$wpdb->update(
		$wpdb->prefix . 'sakolawp_exam',
		array(
			'name' => $name,
			'start_exam' => $start_exam,
			'end_exam' => $end_exam,
		),
		array(
			'exam_id' => $exam_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-settings')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_edit_exam_setting', 'sakolawp_manage_edit_exam');
add_action('admin_post_edit_exam_setting', 'sakolawp_manage_edit_exam');

function sakolawp_manage_delete_exam()
{
	global $wpdb;

	$exam_id = $_POST['exam_id'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_exam',
		array(
			'exam_id' => $exam_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-settings')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_delete_exam_setting', 'sakolawp_manage_delete_exam');
add_action('admin_post_delete_exam_setting', 'sakolawp_manage_delete_exam');


// register a new user
function sakolawp_add_new_member()
{
	if (isset($_POST["sakolawp_user_login"]) && wp_verify_nonce($_POST['sakolawp_register_nonce'], 'sakolawp-register-nonce')) {
		$user_login		= sanitize_text_field($_POST["sakolawp_user_login"]);
		$user_email		= sanitize_email($_POST["sakolawp_user_email"]);
		$user_first 	= sanitize_text_field($_POST["sakolawp_user_first"]);
		$user_last	 	= sanitize_text_field($_POST["sakolawp_user_last"]);
		$user_pass		= sanitize_text_field($_POST["sakolawp_user_pass"]);
		$pass_confirm 	= sanitize_text_field($_POST["sakolawp_user_pass_confirm"]);
		$user_roles 	= sanitize_text_field($_POST["sakolawp_user_roles"]);

		// this is required for username checks
		require_once(ABSPATH . WPINC . '/registration.php');

		if (username_exists($user_login)) {
			// Username already registered
			sakolawp_errors()->add('username_unavailable', esc_html__('Username already taken', 'sakolawp'));
		}
		if (!validate_username($user_login)) {
			// invalid username
			sakolawp_errors()->add('username_invalid', esc_html__('Invalid username', 'sakolawp'));
		}
		if ($user_login == '') {
			// empty username
			sakolawp_errors()->add('username_empty', esc_html__('Please enter a username', 'sakolawp'));
		}
		if (!is_email($user_email)) {
			//invalid email
			sakolawp_errors()->add('email_invalid', esc_html__('Invalid email', 'sakolawp'));
		}
		if (email_exists($user_email)) {
			//Email address already registered
			sakolawp_errors()->add('email_used', esc_html__('Email already registered', 'sakolawp'));
		}
		if ($user_pass == '') {
			// passwords do not match
			sakolawp_errors()->add('password_empty', esc_html__('Please enter a password', 'sakolawp'));
		}
		if ($user_pass != $pass_confirm) {
			// passwords do not match
			sakolawp_errors()->add('password_mismatch', esc_html__('Passwords do not match', 'sakolawp'));
		}

		if ($user_roles == '') {
			// passwords do not match
			sakolawp_errors()->add('roles_empty', esc_html__('Must select a role', 'sakolawp'));
		}

		$errors = sakolawp_errors()->get_error_messages();

		// only create the user in if there are no errors
		if (empty($errors)) {

			$new_user_id = wp_insert_user(
				array(
					'user_login'		=> $user_login,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'first_name'		=> $user_first,
					'last_name'			=> $user_last,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> $user_roles
				)
			);

			update_user_meta($new_user_id, 'user_active', 0);

			if ($new_user_id) {
				// send an email to the admin alerting them of the registration
				wp_new_user_notification($new_user_id);

				// log the new user in
				wp_set_current_user($new_user_id);
				wp_set_auth_cookie($new_user_id, true);
				$user = get_user_by('ID', $new_user_id);
				do_action('wp_login', $user->user_login, $user);

				// send the newly created user to the home page after logging them in
				wp_redirect(home_url('waiting'));
				exit;
			}
		}
	}
}
add_action('init', 'sakolawp_add_new_member');

function sakolawp_login_member()
{

	if (isset($_POST['sakolawp_user_login']) && wp_verify_nonce($_POST['sakolawp_login_nonce'], 'sakolawp-login-nonce')) {

		// this returns the user ID and other info from the user name
		$user = get_user_by(is_email($_POST['sakolawp_user_login']) ? 'email' : 'login', $_POST['sakolawp_user_login']);;

		if (!$user) {
			// if the user name doesn't exist
			sakolawp_errors()->add('empty_username', esc_html__('Invalid username', 'sakolawp'));
		}

		if (!isset($_POST['sakolawp_user_pass']) || $_POST['sakolawp_user_pass'] == '') {
			// if no password was entered
			sakolawp_errors()->add('empty_password', esc_html__('Please enter a password', 'sakolawp'));
		}

		// check the user's login with their password
		if (!wp_check_password($_POST['sakolawp_user_pass'], $user->user_pass, $user->ID)) {
			// if the password is incorrect for the specified user
			sakolawp_errors()->add('empty_password', esc_html__('Incorrect password', 'sakolawp'));
		}

		// retrieve all error messages
		$errors = sakolawp_errors()->get_error_messages();

		// only log the user in if there are no errors
		if (empty($errors)) {
			wp_set_current_user($user->ID);
			wp_set_auth_cookie($user->ID, isset($_POST['rememberme']));
			do_action('wp_login', $_POST['sakolawp_user_login'], $user);

			if (!current_user_can('manage_options') && !defined('DOING_AJAX') && !current_user_can('administrator')) {
				wp_safe_redirect(home_url('myaccount'));
				exit;
			} else {
				wp_safe_redirect(admin_url());
				exit;
			}
		}
	}
}
add_action('init', 'sakolawp_login_member');

function sakolawp_errors()
{
	static $wp_error; // Will hold global variable safely
	return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

// displays error messages from form submissions
function sakolawp_show_error_messages()
{
	if ($codes = sakolawp_errors()->get_error_codes()) {
		echo '<div class="sakolawp_errors">';
		// Loop error codes and display errors
		foreach ($codes as $code) {
			$message = sakolawp_errors()->get_error_message($code);
			echo '<span class="error" style="color:green;"><strong>' . esc_html__('Error', 'sakolawp') . '</strong>: ' . $message . '</span><br/>';
		}
		echo '</div>';
	}
}

function sakolawp_select_section_f()
{
	// Implement ajax function here
	global $wpdb;
	$class_id = $_REQUEST['class_id'];
	$sections = $wpdb->get_results("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE class_id = '$class_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($sections as $row) {
		echo '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
	}

	exit();
}
add_action('wp_ajax_sakolawp_select_section', 'sakolawp_select_section_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_section', 'sakolawp_select_section_f');

function sakolawp_select_section_spe()
{
	// Implement ajax function here
	global $wpdb;
	$teacher_id = get_current_user_id();
	$class_id = sanitize_text_field($_REQUEST['class_id']);

	$section_teached = $wpdb->get_results("SELECT class_id,name,section_id FROM {$wpdb->prefix}sakolawp_section WHERE teacher_id = $teacher_id AND class_id = '$class_id'");
	$selected_section = array();
	foreach ($section_teached as $the_class) {
		$selected_section[] = $the_class->section_id;
	}
	$listofclass = array_unique($selected_section);
	$sellistofclass = implode(', ', $listofclass);

	$sections = $wpdb->get_results("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE section_id IN ($sellistofclass)", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($sections as $row) {
		echo '<option value="' . esc_attr($row['section_id']) . '">' . esc_html($row['name']) . '</option>';
	}

	exit();
}
add_action('wp_ajax_sakolawp_select_section_spe', 'sakolawp_select_section_spe');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_section_spe', 'sakolawp_select_section_spe');

function sakolawp_select_subject_f()
{
	// Implement ajax function here
	global $wpdb;
	$section_id = $_REQUEST['section_id'];
	$subjects = $wpdb->get_results("SELECT subject_id, name FROM {$wpdb->prefix}sakolawp_subject WHERE section_id = '$section_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($subjects as $row) {
		echo '<option value="' . $row['subject_id'] . '">' . $row['name'] . '</option>';
	}

	exit();
}
add_action('wp_ajax_sakolawp_select_subject', 'sakolawp_select_subject_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_subject', 'sakolawp_select_subject_f');

function sakolawp_select_section_first_f()
{
	// Implement ajax function here
	global $wpdb;
	$class_id = $_REQUEST['class_id'];
	$subject_id = $_REQUEST['subject_id'];

	$section_id = $wpdb->get_results("SELECT section_id FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = '$subject_id'", ARRAY_A);

	$sections = $wpdb->get_results("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE class_id = '$class_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($sections as $row) { ?>
		<option value="<?php echo esc_attr($row['section_id']); ?>" <?php if ($row['section_id'] == $section_id[0]['section_id']) {
																		echo esc_html("selected");
																	} ?>><?php echo esc_html($row['name']); ?></option>
	<?php }
}
add_action('wp_ajax_sakolawp_select_section_first', 'sakolawp_select_section_first_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_section_first', 'sakolawp_select_section_first_f');

function sakolawp_first_ajax_request()
{
	global $wpdb;
	$class_id = $_REQUEST['class_id'];
	$sections = $wpdb->get_results("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE class_id = '$class_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($sections as $row) {
		echo '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
	}
}

add_action('wp_ajax_sakolawp_first_ajax_request', 'sakolawp_first_ajax_request');
add_action('wp_ajax_nopriv_sakolawp_first_ajax_request', 'sakolawp_first_ajax_request');

function sakolawp_select_subject_teacher_f()
{
	// Implement ajax function here
	global $wpdb;
	$section_id = $_REQUEST['section_id'];
	$teacher_id = $_REQUEST['teacher_id'];
	$subjects = $wpdb->get_results("SELECT subject_id, name FROM {$wpdb->prefix}sakolawp_subject WHERE section_id = '$section_id' AND teacher_id = '$teacher_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($subjects as $row) {
		echo '<option value="' . $row['subject_id'] . '">' . $row['name'] . '</option>';
	}

	exit();
}
add_action('wp_ajax_sakolawp_select_subject_teacher', 'sakolawp_select_subject_teacher_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_subject_teacher', 'sakolawp_select_subject_teacher_f');

add_filter('single_template', 'sakolawp_single_news_template');
function sakolawp_single_news_template($single)
{
	global $post;

	// news
	if ($post->post_type == 'sakolawp-news') {
		if (file_exists(SAKOLAWP_PLUGIN_DIR . '/single-sakolawp-news.php')) {
			return SAKOLAWP_PLUGIN_DIR . '/single-sakolawp-news.php';
		}
	}
	// event
	if ($post->post_type == 'sakolawp-event') {
		if (file_exists(SAKOLAWP_PLUGIN_DIR . '/single-sakolawp-event.php')) {
			return SAKOLAWP_PLUGIN_DIR . '/single-sakolawp-event.php';
		}
	}

	return $single;
}

function sakolawp_upload_dir_questions($dir)
{
	return array(
		'path'   => $dir['basedir'] . '/sakolawp/questions',
		'url'    => $dir['baseurl'] . '/sakolawp/questions',
		'subdir' => '/sakolawp/questions',
	) + $dir;
}

function sakolawp_custom_dir_homework($dir)
{
	return array(
		'path'   => $dir['basedir'] . '/sakolawp/homework',
		'url'    => $dir['baseurl'] . '/sakolawp/homework',
		'subdir' => '/sakolawp/homework',
	) + $dir;
}

function sakolawp_custom_dir_deliveries($dir)
{
	return array(
		'path'   => $dir['basedir'] . '/sakolawp/deliveries',
		'url'    => $dir['baseurl'] . '/sakolawp/deliveries',
		'subdir' => '/sakolawp/deliveries',
	) + $dir;
}

add_action('after_setup_theme', 'sakolawp_theme_overdrive_img_sice');
function sakolawp_theme_overdrive_img_sice()
{
	add_image_size('skwp-user-img', 60, 60, true); //mobile
}

// Custom Dashboard Item
function sakolawp_statistic_widget()
{
	global $wp_meta_boxes;

	wp_add_dashboard_widget(
		'sakola_dashboard_widget',
		esc_html__('SakolaWP Statistics', 'sakolawp'),
		'sakola_dashboard_widget_display'
	);

	$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

	$my_widget = array('sakola_dashboard_widget' => $dashboard['sakola_dashboard_widget']);
	unset($dashboard['sakola_dashboard_widget']);

	$sorted_dashboard = array_merge($my_widget, $dashboard);
	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}
add_action('wp_dashboard_setup', 'sakolawp_statistic_widget');

function sakola_dashboard_widget_display()
{
	?>
	<table id="dataTable1" width="100%" class="table table-striped table-lightfont">
		<thead>
			<tr>
				<th>
					<?php esc_html_e('Item Name', 'sakolawp'); ?>
				</th>
				<th>
					<?php esc_html_e('Total', 'sakolawp'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?php esc_html_e('Exams Created', 'sakolawp'); ?>
				</td>
				<td>
					<?php
					global $wpdb;
					$total_exams = $wpdb->get_results("SELECT exam_code FROM {$wpdb->prefix}sakolawp_exams", ARRAY_A);
					$total_total_exams = $wpdb->num_rows;

					echo esc_html($total_total_exams);
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php esc_html_e('Exams Taken By Student', 'sakolawp'); ?>
				</td>
				<td>
					<?php
					global $wpdb;
					$total_exams_done = $wpdb->get_results("SELECT exam_code FROM {$wpdb->prefix}sakolawp_student_answer", ARRAY_A);
					$total_total_exams_done = $wpdb->num_rows;

					echo esc_html($total_total_exams_done);
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php esc_html_e('Homeworks Created', 'sakolawp'); ?>
				</td>
				<td>
					<?php
					global $wpdb;
					$total_homework = $wpdb->get_results("SELECT homework_code FROM {$wpdb->prefix}sakolawp_homework", ARRAY_A);
					$total_total_homework = $wpdb->num_rows;

					echo esc_html($total_total_homework);
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php esc_html_e('Homeworks Taken By Student', 'sakolawp'); ?>
				</td>
				<td>
					<?php
					global $wpdb;
					$homeworks_done = $wpdb->get_results("SELECT homework_code FROM {$wpdb->prefix}sakolawp_deliveries", ARRAY_A);
					$total_homeworks_done = $wpdb->num_rows;

					echo esc_html($total_homeworks_done);
					?>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}


if (($lb_update_res['status']) && ($sakola_lb_verify_res['status'])) {
	function sakolawp_licensebox_show_update_notice()
	{
		global $lb_update_res;
		$lb_update_message = esc_html($lb_update_res['message']);
		$update_notification = <<<LB_UPDATE
<tr class="active">
	<td colspan="3">
		<div class="update-message notice inline notice-warning notice-alt" style="margin: 5px 20px 10px 20px">
			<p>
				<b>$lb_update_message</b>
				<a href="admin.php?page=sakolawp-licensebox" style="text-decoration: underline;">Update now</a>.
			</p>
		</div>
	</td>
</tr>
LB_UPDATE;
		echo wp_specialchars_decode($update_notification);
	}
	add_action("after_plugin_row_" . plugin_basename(__FILE__), 'sakolawp_licensebox_show_update_notice', 10, 3);
}

if (!$sakola_lb_verify_res['status']) {
	function sakolawp_licensebox_show_license_notice()
	{
		$license_notification = <<<LB_LICENSE
	<tr class="active">
		<td colspan="3">
			<div class="notice notice-error inline notice-alt" style="margin: 5px 20px 10px 20px">
				<p>
					<b>License is not set yet, Please enter your license code to use the plugin.</b>
					<a href="admin.php?page=sakolawp-settings" style="text-decoration: underline;">Enter License Code</a>.
				</p>
			</div>
		</td>
	</tr>
LB_LICENSE;
		echo wp_specialchars_decode($license_notification);
	}
	add_action("after_plugin_row_" . plugin_basename(__FILE__), 'sakolawp_licensebox_show_license_notice', 10, 3);
}


/* Database Update */
function sakolawp_plugin_update()
{
	$plugin_version = SAKOLAWP_VERSION;

	if ($plugin_version >= '1.0.4' && $plugin_version < '1.1.2') {
		sakolawp_plugin_updates();
	}
}

add_action('plugins_loaded', 'sakolawp_plugin_update');

function sakolawp_plugin_updates()
{
	global $wpdb, $plugin_version;

	$table_name = $wpdb->prefix . 'sakolawp_class_routine';

	$wpdb->query(
		"ALTER TABLE $table_name
        	MODIFY COLUMN `time_start` int(11) NULL,
        	MODIFY COLUMN `time_end` int(11) NULL
        "
	);

	// update option
}

// new user default active
add_action('user_register', 'sakolawp_custom_registration_save', 10, 1);
function sakolawp_custom_registration_save($user_id)
{

	update_user_meta($user_id, 'user_active', 1);
}

function sakolawp_docs_link_menus_script()
{
?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("ul#adminmenu a[href$='https://themesawesome.zendesk.com/hc/en-us/categories/360003331032-SakolaWP']").attr('target', '_blank');
		});
	</script>
<?php
}
add_action('admin_head', 'sakolawp_docs_link_menus_script');

add_action('delete_user', 'sakolawp_student_wpdocs_delete_user');
function sakolawp_student_wpdocs_delete_user($user_id)
{
	global $wpdb;

	// start to removing
	$user_obj = get_userdata($user_id);

	$id_user = $user_obj->ID;
	$idsss = $user_obj->user_id;
	$prefix = $wpdb->prefix;

	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_attendance',
		array(
			'student_id' => $id_user,
		)
	);

	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_attendance_log',
		array(
			'student_id' => $id_user,
		)
	);

	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_deliveries',
		array(
			'student_id' => $id_user,
		)
	);

	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_enroll',
		array(
			'student_id' => $id_user,
		)
	);

	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_homework',
		array(
			'uploader_id'   => $id_user,
			'uploader_type' => 'student',
		)
	);

	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_mark',
		array(
			'student_id' => $id_user,
		)
	);

	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_student_answer',
		array(
			'student_id' => $id_user,
		)
	);
}

function sakolawp_remove_empty_area()
{
	global $wpdb;

	$excludeIds = $wpdb->get_results("SELECT student_id FROM {$wpdb->prefix}sakolawp_enroll", ARRAY_A);
	$useridEx = array();
	foreach ($excludeIds as $ex) {
		$useridEx[] = $ex["student_id"];
	}
	$args = array(
		//'exclude' => $useridEx,
		'role'    => 'student',
		'orderby' => 'user_nicename',
		'order'   => 'ASC'
	);
	$students = get_users($args);
	$student_ids = array();
	foreach ($students as $student) {
		$student_ids[] = $student->ID;
	}

	foreach ($useridEx as $ids) {
		if (!in_array($ids, $student_ids)) {
			$wpdb->delete(
				$wpdb->prefix . 'sakolawp_enroll',
				array(
					'student_id' => $ids,
				)
			);
		}
	}

	wp_redirect(admin_url('admin.php?page=sakolawp-settings')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_remove_empty_area', 'sakolawp_remove_empty_area');
add_action('admin_post_remove_empty_area', 'sakolawp_remove_empty_area');


function skwp_get_page_by_title($page_title, $output = OBJECT, $post_type = 'page')
{
	$args  = array(
		'title'                  => $page_title,
		'post_type'              => $post_type,
		// 'post_status'            => get_post_stati(),
		'posts_per_page'         => 1,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'no_found_rows'          => true,
		'orderby'                => 'post_date ID',
		'order'                  => 'ASC',
	);
	$query = new WP_Query($args);
	$pages = $query->posts;

	if (empty($pages)) {
		return null;
	}

	return get_post($pages[0], $output);
}


/**
 * Busola's Additions Starts Here
 */


function sakolawp_add_student()
{
	global $wpdb;

	$target_dir = "uploads/";
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
	$student_id = sanitize_file_name($_POST['csvFile']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$date_added = sanitize_text_field(time());
	$running_year = get_option('running_year');
	$year           = sanitize_text_field($running_year);
	$random_code = sanitize_text_field(substr(md5(rand(0, 1000000)), 0, 7));
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_enroll',
		array(
			'enroll_code' => $random_code,
			'student_id' => $student_id,
			'class_id' => $class_id,
			'section_id' => $section_id,
			'roll' => $random_code,
			'date_added' => $date_added,
			'year' => $year
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-student-area')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_add_student_user', 'sakolawp_add_student');
add_action('admin_post_add_student_user', 'sakolawp_add_student');
