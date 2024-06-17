<?php

global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$sakolawp_db_version = '2.0.0';
$table_name = $wpdb->prefix . 'sakolawp_settings';
$class_table = $wpdb->prefix . 'sakolawp_class';
$section_table = $wpdb->prefix . 'sakolawp_section';
$accountability_table = $wpdb->prefix . 'sakolawp_accountability';
$subject_table = $wpdb->prefix . 'sakolawp_subject';
$routine_table = $wpdb->prefix . 'sakolawp_class_routine';
$enroll_table = $wpdb->prefix . 'sakolawp_enroll';
$lessons_table = $wpdb->prefix . 'sakolawp_lessons';
$homework_table = $wpdb->prefix . 'sakolawp_homework';
$question_table = $wpdb->prefix .  'sakolawp_homework_questions'; // Table to store the main question data
$linear_scale_options_table = $wpdb->prefix .  'sakolawp_linear_scale_options'; // Table to store linear scale options
$linear_scale_labels_table = $wpdb->prefix . 'sakolawp_linear_scale_labels'; // Table to store linear scale labels
$options_table = $wpdb->prefix . 'sakolawp_question_options'; // Table to store options for radio, checkbox, and dropdown questions
$deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';
$peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';
$questions_bank_table = $wpdb->prefix . 'sakolawp_questions_bank';
$exams_table = $wpdb->prefix . 'sakolawp_exams';
$mark_table = $wpdb->prefix . 'sakolawp_mark';
$exam_table = $wpdb->prefix . 'sakolawp_exam';
$questions_table = $wpdb->prefix . 'sakolawp_questions';
$attendance_table = $wpdb->prefix . 'sakolawp_attendance';
$attendance_log_table = $wpdb->prefix . 'sakolawp_attendance_log';
$student_answer_table = $wpdb->prefix . 'sakolawp_student_answer';

$sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	type text NULL,
	description longtext NULL,
	UNIQUE KEY id (id)
) $charset_collate;

CREATE TABLE $class_table (
	class_id mediumint(9) NOT NULL AUTO_INCREMENT,
	name text NULL,
	name_numeric text NULL,
	teacher_id text NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (class_id)
) $charset_collate;

CREATE TABLE $section_table (
	section_id mediumint(11) NOT NULL AUTO_INCREMENT,
	name longtext NOT NULL,
	class_id int(11) NOT NULL,
	teacher_id text NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (section_id)
) $charset_collate;

CREATE TABLE $accountability_table (
	accountability_id mediumint(11) NOT NULL AUTO_INCREMENT,
	name longtext NOT NULL,
	class_id int(11) NOT NULL,
	section_id int(11) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (accountability_id)
) $charset_collate;

CREATE TABLE $subject_table (
	subject_id mediumint(11) NOT NULL AUTO_INCREMENT,
	name longtext NOT NULL,
	description longtext NULL,
	class_id int(11) NOT NULL,
	section_id int(11) NOT NULL,
	teacher_id text NOT NULL,
	total_lab int(11) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (subject_id)
) $charset_collate;

CREATE TABLE $routine_table (
	class_routine_id mediumint(11) NOT NULL AUTO_INCREMENT,
	class_id int(11) NOT NULL,
	section_id int(11) NOT NULL,
	subject_id int(11) NOT NULL,
	time_start int(11) NULL,
	time_end int(11) NULL,
	time_start_min text NULL,
	time_end_min text NULL,
	day text NULL,
	year text NULL,
	teacher_id text NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (class_routine_id)
) $charset_collate;

CREATE TABLE $enroll_table (
	enroll_id int(11) NOT NULL AUTO_INCREMENT,
	enroll_code varchar(110) NOT NULL,
	student_id varchar(110) NOT NULL,
	class_id int(11) NOT NULL,
	section_id int(11) NOT NULL,
	accountability_id int(11) NOT NULL,
	roll varchar(110) NOT NULL,
	date_added text NULL,
	year text NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (enroll_id)
) $charset_collate;

CREATE TABLE $lessons_table (
	lesson_id int(11) NOT NULL AUTO_INCREMENT,
	lesson_code varchar(110) NOT NULL,
	title longtext NOT NULL,
	description longtext NULL,
	subject_id int(11) NOT NULL,
	uploader_id int(11) NOT NULL,
	uploader_type varchar(100) NOT NULL,
	file_name longtext NULL,
	file_date longtext NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (lesson_id)
) $charset_collate;

CREATE TABLE $homework_table (
	homework_id int(11) NOT NULL AUTO_INCREMENT,
	homework_code varchar(110) NOT NULL,
	title longtext NOT NULL,
	description longtext NULL,
	class_id int(11) NOT NULL,
	section_id int(11) NULL,
	subject_id int(11) NOT NULL,
	uploader_id int(11) NOT NULL,
	uploader_type varchar(100) NOT NULL,
	time_end varchar(100) NOT NULL,
	date_end varchar(100) NOT NULL,
	file_name longtext NULL,
	file_date longtext NULL,
	allow_peer_review BOOLEAN DEFAULT 0,
	peer_review_who VARCHAR(100) NOT NULL DEFAULT 'student',
	peer_review_template VARCHAR(255) NULL,
	word_count_min int(11) NULL,
	word_count_max int(11) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (homework_id)
) $charset_collate;

CREATE TABLE $question_table (
    question_id INT NOT NULL AUTO_INCREMENT,
    homework_id INT NOT NULL,
    question TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    accepts VARCHAR(255),
    multiple BOOLEAN,
    required BOOLEAN,
    text_options JSON,
    linear_scale_options JSON,
    score_percentage INT,
    expected_points INT,
    UNIQUE KEY id (question_id)
) $charset_collate;

CREATE TABLE $linear_scale_options_table (
    question_id INT NOT NULL,
    min INT NOT NULL,
    max INT NOT NULL,
    step INT NOT NULL,
    UNIQUE KEY id (question_id)
) $charset_collate;

CREATE TABLE $linear_scale_labels_table (
    id INT NOT NULL AUTO_INCREMENT,
    question_id INT NOT NULL,
    scale_value INT NOT NULL,
    label VARCHAR(255) NOT NULL,
    UNIQUE KEY id (id)
) $charset_collate;

CREATE TABLE $options_table (
    id INT NOT NULL AUTO_INCREMENT,
    question_id INT NOT NULL,
    label VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL,
    points INT,
    UNIQUE KEY id (id)
) $charset_collate;

CREATE TABLE $deliveries_table (
	delivery_id int(11) NOT NULL AUTO_INCREMENT,
	homework_code varchar(110) NOT NULL,
	student_id int(11) NOT NULL,
	date varchar(110) NOT NULL,
	class_id int(11) NOT NULL,
	section_id int(11) NOT NULL,
	file_name longtext NULL,
	file_date longtext NULL,
	student_comment longtext NULL,
	teacher_comment longtext NULL,
	subject_id int(11) NOT NULL,
	status int(11) NOT NULL,
	homework_reply longtext NULL,
	mark varchar(110) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (delivery_id)
) $charset_collate;

CREATE TABLE $peer_reviews_table (
	peer_review_id int(11) NOT NULL AUTO_INCREMENT,
	date varchar(110) NOT NULL,
	delivery_id INT NOT NULL,
	homework_id INT NOT NULL,
	reviewer_id INT NOT NULL,
	peer_id INT NOT NULL,
	class_id INT NOT NULL,
	section_id INT NOT NULL,
	accountability_id INT NOT NULL,
	subject_id INT NOT NULL,
	homework_reply JSON NULL,
	assessment JSON NOT NULL,
	mark varchar(110) NULL,
	reviewer_comment longtext NULL,
	reviewer_type varchar(110) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (peer_review_id)
) $charset_collate;

CREATE TABLE $questions_bank_table (
	question_id int(11) NOT NULL AUTO_INCREMENT,
	question longtext NULL,
	question_excerpt longtext NULL,
	audio_file longtext NULL,
	file_date longtext NULL,
	optiona longtext NULL,
	optionb longtext NULL,
	optionc longtext NULL,
	optiond longtext NULL,
	optione longtext NULL,
	correct_answer longtext NULL,
	owner_id int(11) NULL,
	class_id int(11) NULL,
	subject_id int(11) NULL,
	added varchar(110) NULL,
	question_code varchar(110) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (question_id)
) $charset_collate;

CREATE TABLE $exams_table (
	exam_id int(11) NOT NULL AUTO_INCREMENT,
	title longtext NULL,
	description longtext NULL,
	availablefrom longtext NULL,
	availableto longtext NULL,
	class_id int(11) NULL,
	section_id int(11) NULL,
	teacher_id int(11) NULL,
	subject_id int(11) NULL,
	duration longtext NULL,
	questions int(11) NULL,
	pass longtext NULL,
	clock_start longtext NULL,
	exam_code varchar(110) NULL,
	clock_end longtext NULL,
	view_mark varchar(50) NULL,
	random varchar(50) NULL,
	year varchar(110) NULL,
	mark varchar(110) NULL,
	participant longtext NULL,
	availfromtime longtext NULL,
	availtotime longtext NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (exam_id)
) $charset_collate;

CREATE TABLE $mark_table (
	mark_id int(11) NOT NULL AUTO_INCREMENT,
	class_id int(11) NOT NULL,
	student_id int(11) NOT NULL,
	section_id int(11) NOT NULL,
	subject_id int(11) NOT NULL,
	exam_id int(11) NOT NULL,
	mark_obtained int(11) NULL,
	mark_total int(11) NOT NULL DEFAULT '100',
	comment longtext NULL,
	lab1 int(11) NULL,
	lab2 int(11) NULL,
	lab3 int(11) NULL,
	lab4 int(11) NULL,
	lab5 int(11) NULL,
	lab6 int(11) NULL,
	lab7 int(11) NULL,
	lab8 int(11) NULL,
	lab9 int(11) NULL,
	lab10 int(11) NULL,
	lab11 int(11) NULL,
	lab12 int(11) NULL,
	lab13 int(11) NULL,
	lab14 int(11) NULL,
	lab15 int(11) NULL,
	lab16 int(11) NULL,
	lab17 int(11) NULL,
	lab18 int(11) NULL,
	lab19 int(11) NULL,
	lab20 int(11) NULL,
	lab21 int(11) NULL,
	lab22 int(11) NULL,
	lab23 int(11) NULL,
	lab24 int(11) NULL,
	lab_total int(11) NULL,
	year longtext NOT NULL,
	final varchar(200) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (mark_id)
) $charset_collate;

CREATE TABLE $exam_table (
	exam_id int(11) NOT NULL AUTO_INCREMENT,
	name longtext NOT NULL,
	year longtext NOT NULL,
	start_exam longtext NULL,
	end_exam longtext NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (exam_id)
) $charset_collate;

CREATE TABLE $questions_table (
	question_id int(11) NOT NULL AUTO_INCREMENT,
	question longtext NOT NULL,
	question_excerpt longtext NULL,
	audio_file longtext NULL,
	file_date longtext NULL,
	optiona longtext NOT NULL,
	optionb longtext NOT NULL,
	optionc longtext NOT NULL,
	optiond longtext NULL,
	optione longtext NULL,
	correct_answer longtext NULL,
	marks longtext NULL,
	question_code longtext NOT NULL,
	exam_code longtext NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (question_id)
) $charset_collate;

CREATE TABLE $attendance_table (
	attendance_id int(11) NOT NULL AUTO_INCREMENT,
	timestamp longtext NOT NULL,
	time varchar(200) NULL,
	year varchar(200) NULL,
	class_id varchar(20) NULL,
	section_id varchar(20) NULL,
	student_id varchar(110) NULL,
	event_id varchar(110) NULL,
	status varchar(20) NULL,
	updated_by varchar(110) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (attendance_id)
) $charset_collate;

CREATE TABLE $attendance_log_table (
	id int(11) NOT NULL AUTO_INCREMENT,
	student_id varchar(110) NULL,
	month varchar(70) NULL,
	year varchar(70) NULL,
	timestamp longtext NULL,
	time longtext NULL,
	class_id varchar(20) NULL,
	section_id varchar(20) NULL,
	day_1 varchar(11) NULL,
	time_1 varchar(110) NULL,
	day_2 varchar(11) NULL,
	time_2 varchar(110) NULL,
	day_3 varchar(11) NULL,
	time_3 varchar(110) NULL,
	day_4 varchar(11) NULL,
	time_4 varchar(110) NULL,
	day_5 varchar(11) NULL,
	time_5 varchar(110) NULL,
	day_6 varchar(11) NULL,
	time_6 varchar(110) NULL,
	day_7 varchar(11) NULL,
	time_7 varchar(110) NULL,
	day_8 varchar(11) NULL,
	time_8 varchar(110) NULL,
	day_9 varchar(11) NULL,
	time_9 varchar(110) NULL,
	day_10 varchar(11) NULL,
	time_10 varchar(110) NULL,
	day_11 varchar(11) NULL,
	time_11 varchar(110) NULL,
	day_12 varchar(11) NULL,
	time_12 varchar(110) NULL,
	day_13 varchar(11) NULL,
	time_13 varchar(110) NULL,
	day_14 varchar(11) NULL,
	time_14 varchar(110) NULL,
	day_15 varchar(11) NULL,
	time_15 varchar(110) NULL,
	day_16 varchar(11) NULL,
	time_16 varchar(110) NULL,
	day_17 varchar(11) NULL,
	time_17 varchar(110) NULL,
	day_18 varchar(11) NULL,
	time_18 varchar(110) NULL,
	day_19 varchar(11) NULL,
	time_19 varchar(110) NULL,
	day_20 varchar(11) NULL,
	time_20 varchar(110) NULL,
	day_21 varchar(11) NULL,
	time_21 varchar(110) NULL,
	day_22 varchar(11) NULL,
	time_22 varchar(110) NULL,
	day_23 varchar(11) NULL,
	time_23 varchar(110) NULL,
	day_24 varchar(11) NULL,
	time_24 varchar(110) NULL,
	day_25 varchar(11) NULL,
	time_25 varchar(110) NULL,
	day_26 varchar(11) NULL,
	time_26 varchar(110) NULL,
	day_27 varchar(11) NULL,
	time_27 varchar(110) NULL,
	day_28 varchar(11) NULL,
	time_28 varchar(110) NULL,
	day_29 varchar(11) NULL,
	time_29 varchar(110) NULL,
	day_30 varchar(11) NULL,
	time_30 varchar(110) NULL,
	day_31 varchar(11) NULL,
	time_31 varchar(110) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY id (id)
) $charset_collate;

CREATE TABLE $student_answer_table (
	answer_id int(11) NOT NULL AUTO_INCREMENT,
	student_id varchar(200) NOT NULL,
	exam_code varchar(200) NOT NULL,
	answered varchar(200) NULL,
	student_answer longtext NULL,
	question_id longtext NULL,
	time longtext NULL,
	total_time longtext NULL,
	point varchar(20) NULL,
	UNIQUE KEY id (answer_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
add_option('sakolawp_db_version', $sakolawp_db_version);

// if (get_option('sakolawp_db_version') != '2.0.0') { // Replace 2.0.0 with your upgrade script version
// 	include_once(ABSPATH . 'wp-admin/includes/upgrade.php');
// 	$upgrade_script = file_get_contents(plugin_dir_path(__FILE__) . 'upgrade/upgrade-2.0.0.php'); // Replace paths accordingly
// 	dbDelta($upgrade_script);
// 	update_option('sakolawp_db_version', '2.0.0'); // Update version number
// }
