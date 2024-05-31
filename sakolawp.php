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
 * Plugin Name:       SakolaWP (Modified For RUN)
 * Plugin URI:        demosakolawp.themesawesome.com
 * Description:       School Management System to manage the school activity like school routine, attendance, exam, homework, etc.
 * Version:           2.0.0
 * Author:            Themes Awesome, Busola Okeowo
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
require_once SAKOLAWP_PLUGIN_DIR . '/sakolawp-riguniversity.php';

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
	$selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : NULL;
	$sections = $wpdb->get_results("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE class_id = '$class_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($sections as $row) {
		$isSelected = $row['section_id'] == $selected ? 'selected' : '';
		// echo '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
		echo '<option ' .  $isSelected . ' value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
	}
	/*
<?php if ($class->class_id == $accountabilityGroup->class_id) {
	echo "selected";
} ?>
*/
	exit();
}
add_action('wp_ajax_sakolawp_select_section', 'sakolawp_select_section_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_section', 'sakolawp_select_section_f');


function sakolawp_select_section2_f()
{
	// Implement ajax function here
	global $wpdb;
	$class_id = $_REQUEST['class_id'];
	$selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : NULL;
	$sections = $wpdb->get_results("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE class_id = '$class_id'", ARRAY_A);
	echo '<option value="">All</option>';
	foreach ($sections as $row) {
		$isSelected = $row['section_id'] == $selected ? 'selected' : '';
		// echo '<option value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
		echo '<option ' .  $isSelected . ' value="' . $row['section_id'] . '">' . $row['name'] . '</option>';
	}
	/*
<?php if ($class->class_id == $accountabilityGroup->class_id) {
	echo "selected";
} ?>
*/
	exit();
}
add_action('wp_ajax_sakolawp_select_section2', 'sakolawp_select_section2_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_section2', 'sakolawp_select_section2_f');

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
	$class_id = $_REQUEST['class_id'];
	$subjects = $wpdb->get_results("SELECT subject_id, name FROM {$wpdb->prefix}sakolawp_subject WHERE class_id = '$class_id'", ARRAY_A);
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

function sakolawp_select_all_subjects_f()
{
	// Implement ajax function here
	global $wpdb;
	$class_id = $_REQUEST['class_id'];
	// $teacher_id = $_REQUEST['teacher_id'];
	$subjects = $wpdb->get_results("SELECT subject_id, name FROM {$wpdb->prefix}sakolawp_subject WHERE class_id = '$class_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($subjects as $row) {
		echo '<option value="' . $row['subject_id'] . '">' . $row['name'] . '</option>';
	}

	exit();
}
add_action('wp_ajax_sakolawp_select_all_subjects', 'sakolawp_select_all_subjects_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_all_subjects', 'sakolawp_select_all_subjects_f');

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


/*************************************
 * Busola's Additions Starts Here
 ***************************************/

/**
 * Inserts a new record into the specified table or updates an existing record if a unique key constraint is violated.
 * Returns the ID of the inserted/updated record.
 *
 * @param string $table_name      The name of the table to insert/update records into.
 * @param array  $data            An associative array where keys are column names and values are the corresponding values to be inserted/updated.
 * @param array  $unique_columns  An array containing the names of the columns that define uniqueness in the table.
 * @param string $id_column		  the id column of table
 * @return int|false The ID of the inserted/updated record, or false on failure.
 */
function skwp_insert_or_update_record($table_name, $data, $unique_columns, $id_column = 'id')
{
	global $wpdb;

	// Filter $data array with keys from $unique_columns and extract values
	$whereValues = array_map(function ($key) use ($data) {
		return $data[$key] ?? null; // return null if key does not exist
	}, $unique_columns);

	// Prepare the WHERE clause for checking existing records
	$whereClause = implode(' AND ', array_map(function ($key) {
		return "{$key} = %s";
	}, $unique_columns));

	// Prepare the SQL query to check for existing records
	$findSql = $wpdb->prepare("SELECT {$id_column} FROM {$table_name} WHERE {$whereClause}", $whereValues);

	// Log the find query for debugging
	error_log("Find SQL Query: " . $findSql);

	// Execute the find query
	$existing_record_id = $wpdb->get_var($findSql);

	if ($existing_record_id !== null) {
		// If the record exists, update it
		$update_result = $wpdb->update($table_name, $data, array($id_column => $existing_record_id));

		// Log any MySQL error messages for debugging
		if ($update_result === false) {
			error_log("`skwp_insert_or_update_record` MySQL Error on Update: " . $wpdb->last_error);
			return false;
		}

		return $existing_record_id;
	} else {
		// If the record does not exist, insert it
		$insert_result = $wpdb->insert($table_name, $data);

		// Log any MySQL error messages for debugging
		if ($insert_result === false) {
			error_log("`skwp_insert_or_update_record` MySQL Error on Insert: " . $wpdb->last_error);
			return false;
		}

		return $wpdb->insert_id;
	}
}



function calculate_assessment_total_score($responses, $form)
{
	try {
		$total_score = 0;
		$total_weight = 0;

		foreach ($form['questions'] as $question) {
			$question_id = $question['question_id'];
			if (!isset($responses[$question_id])) {
				continue;
			}

			$response = $responses[$question_id];
			$score_percentage = $question['score_percentage'];
			$expected_points = $question['expected_points'];

			switch ($question['type']) {
				case 'linear-scale':
					$points = (float)$response;
					$score = ($points / $expected_points) * $score_percentage;
					break;

				case 'radio':
					$points = 0;
					foreach ($question['options'] as $option) {
						if ($option['value'] == $response) {
							$points = $option['points'];
							break;
						}
					}
					$score = ($points / $expected_points) * $score_percentage;
					break;

				default:
					$score = 0;
					break;
			}

			$total_score += $score;
			$total_weight += $score_percentage;
		}

		// Handle the case where the total weight is zero
		if ($total_weight === 0) {
			wp_die(__('Error: Total weight of the form is zero. Please check the form configuration.', 'sakolawp'), __('Form Calculation Error', 'sakolawp'), array('back_link' => true));
		}

		// Normalize the total score to a percentage out of 100
		$final_score = ($total_score / $total_weight) * 100;

		return $final_score;
	} catch (\Throwable $th) {
		error_log("failed at calculate_assessment_total_score" . $th);
		return 0;
	}
}


function sakolawp_add_student()
{
	global $wpdb;

	if (isset($_FILES["filetoupload"]['tmp_name'])) {

		$tmpName = $_FILES["filetoupload"]["tmp_name"];
		$class_id = sanitize_text_field($_POST['class_id']);
		// $teacher_id = sanitize_text_field($_POST['teacher_id']);
		$should_send_email = ($_POST['should_send_email']);

		if ($_FILES["filetoupload"]["error"] > 0) {
			sakolawp_errors()->add('csv_upload_failed', esc_html__($_FILES["file"]["error"], 'sakolawp'));
		}

		$csv_data = array_map('str_getcsv', file($tmpName));

		array_walk($csv_data, function (&$x) use ($csv_data) {
			$x = array_combine($csv_data[0], $x);
		});

		// array_shift = remove first value of array in csv file header was the first value
		array_shift($csv_data);

		foreach ($csv_data as $row) {
			$username				= sanitize_text_field($row["Username"]);
			$user_email				= sanitize_email($row["Email"]);
			$user_first 			= sanitize_text_field($row["First Name"]);
			$user_last	 			= sanitize_text_field($row["Last Name"]);
			$user_mobile_number	 	= sanitize_text_field($row["Mobile Number"]);
			$user_next_of_kin	 	= sanitize_text_field($row["Next of Kin"]);
			$user_gender	 		= sanitize_text_field($row["Gender"]);
			$user_city	 			= sanitize_text_field($row["City"]);
			$user_state	 			= sanitize_text_field($row["State"]);
			$user_matriculation_code = sanitize_text_field($row["Student ID"]);
			$user_parent_group 		= trim(sanitize_text_field($row["Parent Group"]));
			$user_accountability_group 		= trim(sanitize_text_field($row["Accountability Group"]));
			$user_pass				= sanitize_text_field($row["Password"]);
			$user_roles 			= 'student';

			if (!validate_username($username)) {
				// invalid username
				sakolawp_errors()->add('username_invalid', esc_html__('Invalid username', 'sakolawp'));
			}
			if ($username == '') {
				// empty username
				sakolawp_errors()->add('username_empty', esc_html__('Please enter a username', 'sakolawp'));
			}
			if (!is_email($user_email)) {
				//invalid email
				sakolawp_errors()->add('email_invalid', esc_html__('Invalid email', 'sakolawp'));
			}

			$user_by_username = username_exists($username);
			$user_by_email = email_exists($user_email);

			if (isset($user_by_username) && isset($user_by_email) && $user_by_username !== $user_by_email) {
				// Username already registered
				sakolawp_errors()->add('username_unavailable', esc_html__('Username already taken ', 'sakolawp'));
			}


			if (isset($user_by_username) && !isset($user_by_email)) {
				// Username already registered but email is new so go ahead with a different username
				$username = $username . sanitize_text_field(substr(md5(rand(0, 10000)), 0, 5));
			}


			$errors = sakolawp_errors()->get_error_messages();

			// only create the user in if there are no errors
			if (empty($errors)) {

				$new_user_id = (isset($user_by_email) ? $user_by_email : isset($user_by_username)) ? $user_by_username : wp_insert_user(
					array(
						'user_login'		=> $username,
						'user_pass'	 		=> $user_pass,
						'user_email'		=> $user_email,
						'first_name'		=> $user_first,
						'last_name'			=> $user_last,
						'user_registered'	=> date('Y-m-d H:i:s'),
						'role'				=> $user_roles
					)
				);

				if ($new_user_id) {

					update_user_meta($new_user_id, 'mobile_number', $user_mobile_number);
					update_user_meta($new_user_id, 'next_of_kin', $user_next_of_kin);
					update_user_meta($new_user_id, 'gender', $user_gender);
					update_user_meta($new_user_id, 'user_city', $user_city);
					update_user_meta($new_user_id, 'user_state', $user_state);
					update_user_meta($new_user_id, 'user_active', 1);

					// find or create section
					$table_name = $wpdb->prefix . 'sakolawp_section';
					$data = array(
						'name' => $user_parent_group,
						'class_id' => $class_id,
						'teacher_id' => null,
					);
					$unique_columns = array('class_id', 'name');
					$section_id = skwp_insert_or_update_record($table_name, $data, $unique_columns, "section_id");


					// find or create accountability group
					$table_name = $wpdb->prefix . 'sakolawp_accountability';
					$data = array(
						'name' => $user_accountability_group,
						'class_id' => $class_id,
						'section_id' => $section_id,
					);
					$unique_columns = array('class_id', 'name');
					$accountability_id = skwp_insert_or_update_record($table_name, $data, $unique_columns, "accountability_id");


					// enroll user in a class
					$student_id 	= $new_user_id;
					$date_added 	= sanitize_text_field(time());
					$running_year 	= get_option('running_year');
					$year           = sanitize_text_field($running_year);
					skwp_insert_or_update_record(
						$wpdb->prefix . 'sakolawp_enroll',
						array(
							'enroll_code' => $user_matriculation_code,
							'student_id' => $student_id,
							'class_id' => $class_id,
							'section_id' => $section_id,
							'roll' => $user_matriculation_code,
							'accountability_id' => $accountability_id,
							'date_added' => $date_added,
							'year' => $year
						),
						array('student_id', 'section_id', 'class_id', "year", 'enroll_code', 'accountability_id'),
						"enroll_id"
					);

					if ($should_send_email) {
						// send an email to the user alerting them of the registration
						wp_new_user_notification($new_user_id, null, 'user');
					}
				}
			}
			// else {
			// 	var_dump("failed", $errors);
			// 	die;
			// }
		}
	} else {
		sakolawp_errors()->add('csv_upload_failed', esc_html__('Failed to upload csv file', 'sakolawp'));
	}
	// $errors = sakolawp_errors()->get_error_messages();
	// var_dump($errors, $_FILES, $_POST);
	wp_redirect(admin_url('admin.php?page=sakolawp-add-student&success=1')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_add_student_user', 'sakolawp_add_student');
add_action('admin_post_add_student_user', 'sakolawp_add_student');


function sakolawp_manage_accountability()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_accountability',
		array(
			'name' => $class_name,
			'class_id' => $class_id,
			'section_id' => $section_id,

		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-accountability')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_save_accountability_setting', 'sakolawp_manage_accountability');
add_action('admin_post_save_accountability_setting', 'sakolawp_manage_accountability');


function sakolawp_manage_edit_accountability()
{
	global $wpdb;

	$class_name = sanitize_text_field($_POST['name']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$accountability_id = sanitize_text_field($_POST['accountability_id']);
	$wpdb->update(
		$wpdb->prefix . 'sakolawp_accountability',
		array(
			'name' => $class_name,
			'class_id' => $class_id,
			'section_id' => $section_id
		),
		array(
			'accountability_id' => $accountability_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-accountability')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_edit_accountability_setting', 'sakolawp_manage_edit_accountability');
add_action('admin_post_edit_accountability_setting', 'sakolawp_manage_edit_accountability');

function sakolawp_manage_delete_accountability()
{
	global $wpdb;

	$accountability_id = $_POST['accountability_id'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_accountability',
		array(
			'accountability_id' => $accountability_id
		)
	);

	wp_redirect(admin_url('admin.php?page=sakolawp-manage-accountability')); // <-- here goes address of site that user should be redirected after submitting that form
	die;
}

add_action('admin_post_nopriv_delete_accountability_setting', 'sakolawp_manage_delete_accountability');
add_action('admin_post_delete_accountability_setting', 'sakolawp_manage_delete_accountability');


function sakolawp_select_accountability_f()
{
	// Implement ajax function here
	global $wpdb;
	$section_id = $_REQUEST['section_id'];
	$accountabilityGroups = $wpdb->get_results("SELECT accountability_id, name FROM {$wpdb->prefix}sakolawp_accountability WHERE section_id = '$section_id'", ARRAY_A);
	echo '<option value="">Select</option>';
	foreach ($accountabilityGroups as $row) {
		echo '<option value="' . $row['accountability_id'] . '">' . $row['name'] . '</option>';
	}

	exit();
}
add_action('wp_ajax_sakolawp_select_accountability', 'sakolawp_select_accountability_f');    // If called from admin panel
add_action('wp_ajax_nopriv_sakolawp_select_accountability', 'sakolawp_select_accountability_f');



require_once plugin_dir_path(__FILE__) . 'templates/peer-reviews/class-peer-reviews.php';
$sakolawp_peer_review = new SakolawpPeerReview();


if (!function_exists('array_group_by')) {
	/**
	 * Groups an array by a given key.
	 *
	 * Groups an array into arrays by a given key, or set of keys, shared between all array members.
	 *
	 * Based on {@author Jake Zatecky}'s {@link https://github.com/jakezatecky/array_group_by array_group_by()} function.
	 * This variant allows $key to be closures.
	 *
	 * @param array $array   The array to have grouping performed on.
	 * @param mixed $key,... The key to group or split by. Can be a _string_,
	 *                       an _integer_, a _float_, or a _callable_.
	 *
	 *                       If the key is a callback, it must return
	 *                       a valid key from the array.
	 *
	 *                       If the key is _NULL_, the iterated element is skipped.
	 *
	 *                       ```
	 *                       string|int callback ( mixed $item )
	 *                       ```
	 *
	 * @return array|null Returns a multidimensional array or `null` if `$key` is invalid.
	 */
	function array_group_by(array $array, $key)
	{
		if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
			trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
			return null;
		}

		$func = (!is_string($key) && is_callable($key) ? $key : null);
		$_key = $key;

		// Load the new array, splitting by the target key
		$grouped = [];
		foreach ($array as $value) {
			$key = null;

			if (is_callable($func)) {
				$key = call_user_func($func, $value);
			} elseif (is_object($value) && property_exists($value, $_key)) {
				$key = $value->{$_key};
			} elseif (isset($value[$_key])) {
				$key = $value[$_key];
			}

			if ($key === null) {
				continue;
			}

			$grouped[$key][] = $value;
		}

		// Recursively build a nested grouping if more parameters are supplied
		// Each grouped array value is grouped according to the next sequential key
		if (func_num_args() > 2) {
			$args = func_get_args();

			foreach ($grouped as $key => $value) {
				$params = array_merge([$value], array_slice($args, 2, func_num_args()));
				$grouped[$key] = call_user_func_array('array_group_by', $params);
			}
		}

		return $grouped;
	}
}
