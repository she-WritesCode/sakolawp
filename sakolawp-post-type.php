<?php
/*-----------------------------------------------------------------------------------*/
/* The News custom post type
/*-----------------------------------------------------------------------------------*/

add_action('init', 'sakolawp_news_register');
function sakolawp_news_register()
{

	$labels = array(
		'name'                => _x('News', 'Post Type General Name', 'sakolawp'),
		'singular_name'       => _x('News', 'Post Type Singular Name', 'sakolawp'),
		'menu_name'           => esc_html__('News', 'sakolawp'),
		'parent_item_colon'   => esc_html__('Parent News:', 'sakolawp'),
		'all_items'           => esc_html__('All News', 'sakolawp'),
		'view_item'           => esc_html__('View News', 'sakolawp'),
		'add_new_item'        => esc_html__('Add New News', 'sakolawp'),
		'add_new'             => esc_html__('Add New', 'sakolawp'),
		'edit_item'           => esc_html__('Edit News', 'sakolawp'),
		'update_item'         => esc_html__('Update News', 'sakolawp'),
		'search_items'        => esc_html__('Search News', 'sakolawp'),
		'not_found'           => esc_html__('Not found', 'sakolawp'),
		'not_found_in_trash'  => esc_html__('Not found in Trash', 'sakolawp'),
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'query_var'          => 'news',
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'rewrite'            => array('slug' => 'news'),
		'supports'           => array('title', 'editor', 'thumbnail'),
		'menu_position'       => 7,
		'show_in_menu' 		 => 'sakolawp-settings', // show under Rig university menu

	);
	register_post_type('sakolawp-news', $args);

	register_taxonomy(
		"news-category",
		array("sakolawp-news"),
		array(
			"hierarchical"    => true,
			"label"       => "Categories",
			"singular_label"  => "Categories",
			"rewrite"     => true
		)
	);

	register_taxonomy_for_object_type('news-category', 'sakolawp-news');

	register_taxonomy(
		"news-tags",
		array("sakolawp-news"),
		array(
			"hierarchical"    => true,
			"label"       => "Tags",
			"singular_label"  => "Tags",
			"rewrite"     => true
		)
	);

	register_taxonomy_for_object_type('news-tags', 'sakolawp-news');
}

/*-----------------------------------------------------------------------------------*/
/* The Event custom post type
/*-----------------------------------------------------------------------------------*/

add_action('init', 'sakolawp_event_register');
function sakolawp_event_register()
{
	$labels = array(
		'name'                => _x('Meeting', 'Post Type General Name', 'sakolawp'),
		'singular_name'       => _x('Meeting', 'Post Type Singular Name', 'sakolawp'),
		'menu_name'           => esc_html__('Meeting', 'sakolawp'),
		'parent_item_colon'   => esc_html__('Parent Meeting:', 'sakolawp'),
		'all_items'           => esc_html__('All Meetings', 'sakolawp'),
		'view_item'           => esc_html__('View Meeting', 'sakolawp'),
		'add_new_item'        => esc_html__('Add New Meeting', 'sakolawp'),
		'add_new'             => esc_html__('Add New', 'sakolawp'),
		'edit_item'           => esc_html__('Edit Meeting', 'sakolawp'),
		'update_item'         => esc_html__('Update Meeting', 'sakolawp'),
		'search_items'        => esc_html__('Search Meetings', 'sakolawp'),
		'not_found'           => esc_html__('Not found', 'sakolawp'),
		'not_found_in_trash'  => esc_html__('Not found in Trash', 'sakolawp'),
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'query_var'          => 'event',
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'rewrite'            => array('slug' => 'event'),
		'supports'           => array('title', 'editor', 'thumbnail'),
		'menu_position'      => 7,
		'show_in_menu' 		 => 'sakolawp-settings', // show under Rig university menu
		'register_meta_box_cb' => 'sakolawp_event_date_meta_box'

	);
	register_post_type('sakolawp-event', $args);

	register_taxonomy(
		"event-category",
		array("sakolawp-event"),
		array(
			"hierarchical"    => true,
			"label"       => "Categories",
			"singular_label"  => "Categories",
			"rewrite"     => true
		)
	);

	register_taxonomy_for_object_type('event-category', 'sakolawp-event');

	register_taxonomy(
		"event-tags",
		array("sakolawp-event"),
		array(
			"hierarchical"    => true,
			"label"       => "Tags",
			"singular_label"  => "Tags",
			"rewrite"     => true
		)
	);

	register_taxonomy_for_object_type('event-tags', 'sakolawp-event');

	register_taxonomy_for_object_type('event-category', 'sakolawp-event');
}


// sakola event metabox
function sakolawp_event_date_meta_box()
{
	add_meta_box(
		'sakolawp-event-metabox',
		esc_html__('Event Date & Attendance', 'sakolawp'),
		'sakolawp_event_date_meta_box_callback'
	);
}
add_action('add_meta_boxes_sakolawp-event', 'sakolawp_event_date_meta_box');

function sakolawp_event_date_meta_box_callback($post)
{
	global $wpdb;
	// sakola date event field
	wp_nonce_field('sakolawp_event_date_nonce', 'sakolawp_event_date_nonce');
	$date_value = get_post_meta($post->ID, '_sakolawp_event_date', true);
	// sakola time event field
	wp_nonce_field('sakolawp_event_date_clock_nonce', 'sakolawp_event_date_clock_nonce');
	$time_value = get_post_meta($post->ID, '_sakolawp_event_date_clock', true);
	// sakola time event field
	$deadline_value = get_post_meta($post->ID, '_sakolawp_event_late_deadline', true);
?>
	<div class="my-4">
		<h4 class="text-lg font-medium"><?= esc_html__('Event Date', 'sakolawp') ?></h4>
		<div class="mb-2">
			<input type="date" id="sakolawp_event_date" name="sakolawp_event_date" value="<?php echo esc_attr($date_value); ?>">

			<input type="time" id="sakolawp_event_date_clock" name="sakolawp_event_date_clock" placeholder="HH:MM" value="<?php echo esc_attr($time_value); ?>">
		</div>
		<div class="my-2">
			<label for="sakolawp_event_late_deadline">Students are late after?</label>
			<select id="sakolawp_event_late_deadline" name="sakolawp_event_late_deadline">
				<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '1' ? 'selected' : '' ?> value="1"><?php esc_html_e('1 min', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '5' ? 'selected' : '' ?> value="5"><?php esc_html_e('5 mins', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '10' ? 'selected' : '' ?> value="10"><?php esc_html_e('10 mins', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '15' ? 'selected' : '' ?> value="15"><?php esc_html_e('15 mins', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '20' ? 'selected' : '' ?> value="20"><?php esc_html_e('20 mins', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '30' ? 'selected' : '' ?> value="30"><?php esc_html_e('30 mins', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '45' ? 'selected' : '' ?> value="45"><?php esc_html_e('45 mins', 'sakolawp'); ?></option>
				<option <?= $deadline_value == '60' ? 'selected' : '' ?> value="60"><?php esc_html_e('1 hr', 'sakolawp'); ?></option>
			</select>
		</div>
	</div>
	<div class="my-4">
		<h4 class="text-lg font-medium"><?= esc_html__('Class', 'sakolawp') ?></h4>
		<?php
		// sakola class field
		$class_value = esc_attr(get_post_meta($post->ID, '_sakolawp_event_class_id', true));
		?>
		<select type="text" id="sakolawp_event_class_id" name="sakolawp_event_class_id" placeholder="HH:MM">
			<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
			<?php
			$classes = $wpdb->get_results("SELECT class_id, name FROM {$wpdb->prefix}sakolawp_class", OBJECT);
			foreach ($classes as $class) :
			?>
				<option <?= $class_value == $class->class_id ? 'selected' : '' ?> value="<?php echo esc_attr($class->class_id); ?>"><?php echo esc_html($class->name); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
	if ($post->ID) {
	?>
		<div class="my-4">
			<h4 class="text-lg font-medium">QR Code For Attendance</h4>
			<div id="qr_code_holder">
				<img src="<?php echo esc_attr(get_post_meta($post->ID, 'attendance_qr_code', true)); ?>" />
			</div>
			<br />
			<div>
				<a class="btn skwp-btn btn-primary btn-large" id="generate_qr_code" data-event_id="<?php echo $post->ID; ?>">Download QR Code</a>
			</div>
		</div>
	<?php
	}
}

function sakolawp_save_event_date_meta_box_data($post_id)
{

	// Check if our nonce is set.
	if (!isset($_POST['sakolawp_event_date_nonce'])) {
		return;
	}

	// Verify that the nonce is valid.
	if (!wp_verify_nonce($_POST['sakolawp_event_date_nonce'], 'sakolawp_event_date_nonce')) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Check the user's permissions.
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}

	// Make sure that it is set.
	if (!isset($_POST['sakolawp_event_date'])) {
		return;
	}

	// Sanitize user input.
	$event_date = sanitize_text_field($_POST['sakolawp_event_date']);
	$event_time = sanitize_text_field($_POST['sakolawp_event_date_clock']);
	$event_class_id = sanitize_text_field($_POST['sakolawp_event_class_id']);
	$event_late_deadline = isset($_POST['sakolawp_event_late_deadline']) ? sanitize_text_field($_POST['sakolawp_event_late_deadline']) : NULL;

	// Update the meta field in the database.
	update_post_meta($post_id, '_sakolawp_event_date', $event_date);
	update_post_meta($post_id, '_sakolawp_event_date_clock', $event_time);
	update_post_meta($post_id, '_sakolawp_event_class_id', $event_class_id);
	update_post_meta($post_id, '_sakolawp_event_late_deadline', $event_late_deadline);
}

add_action('save_post', 'sakolawp_save_event_date_meta_box_data');


/*-----------------------------------------------------------------------------------*/
/* The Course custom post type
/*-----------------------------------------------------------------------------------*/

add_action('init', 'sakolawp_course_register');
function sakolawp_course_register()
{
	$labels = array(
		'name'                => _x('Course', 'Post Type General Name', 'sakolawp'),
		'singular_name'       => _x('Course', 'Post Type Singular Name', 'sakolawp'),
		'menu_name'           => esc_html__('Course', 'sakolawp'),
		'parent_item_colon'   => esc_html__('Parent Course:', 'sakolawp'),
		'all_items'           => esc_html__('Courses', 'sakolawp'),
		'view_item'           => esc_html__('View Course', 'sakolawp'),
		'add_new_item'        => esc_html__('Add New Course', 'sakolawp'),
		'add_new'             => esc_html__('Add New', 'sakolawp'),
		'edit_item'           => esc_html__('Edit Course', 'sakolawp'),
		'update_item'         => esc_html__('Update Course', 'sakolawp'),
		'search_items'        => esc_html__('Search Courses', 'sakolawp'),
		'not_found'           => esc_html__('Not found', 'sakolawp'),
		'not_found_in_trash'  => esc_html__('Not found in Trash', 'sakolawp'),
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'query_var'          => 'course',
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'rewrite'            => array('slug' => 'courses'),
		'supports'           => array('title', 'editor', 'thumbnail'),
		'menu_position'      => 0,
		'show_in_menu' 		 =>  'sakolawp-settings', // show under Rig university menu
		'register_meta_box_cb' => 'sakolawp_course_date_meta_box'

	);
	register_post_type('sakolawp-course', $args);

	register_taxonomy(
		"course-category",
		array("sakolawp-course"),
		array(
			"hierarchical"    => true,
			"label"       => "Categories",
			"singular_label"  => "Categories",
			"rewrite"     => true
		)
	);

	register_taxonomy_for_object_type('course-category', 'sakolawp-course');

	register_taxonomy(
		"course-tags",
		array("sakolawp-course"),
		array(
			"hierarchical"    => true,
			"label"       => "Tags",
			"singular_label"  => "Tags",
			"rewrite"     => true
		)
	);

	register_taxonomy_for_object_type('course-tags', 'sakolawp-course');

	register_taxonomy_for_object_type('course-category', 'sakolawp-course');
	flush_rewrite_rules();
}


// sakola course metabox
function sakolawp_course_date_meta_box()
{
	add_meta_box(
		'sakolawp-course-metabox',
		esc_html__('Course Details', 'sakolawp'),
		'sakolawp_course_meta_box_callback'
	);
}
add_action('add_meta_boxes_sakolawp-course', 'sakolawp_course_date_meta_box');

function sakolawp_course_meta_box_callback($post)
{
	wp_nonce_field('sakolawp_course_nonce', 'sakolawp_course_nonce');
	?>
	<div>
		<div id="run-editcourselessonhomework"></div>
	</div>
<?php
}

function sakolawp_save_course_date_meta_box_data($post_id)
{

	// Check if our nonce is set.
	if (!isset($_POST['sakolawp_course_nonce'])) {
		return;
	}

	// Verify that the nonce is valid.
	if (!wp_verify_nonce($_POST['sakolawp_course_nonce'], 'sakolawp_course_nonce')) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Check the user's permissions.
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}

	// do what you need to do
}

add_action('save_post', 'sakolawp_save_course_date_meta_box_data');

/*-----------------------------------------------------------------------------------*/
/* The Lesson custom post type
/*-----------------------------------------------------------------------------------*/

add_action('init', 'sakolawp_lesson_register');
function sakolawp_lesson_register()
{
	$labels = array(
		'name'                => _x('Lesson', 'Post Type General Name', 'sakolawp'),
		'singular_name'       => _x('Lesson', 'Post Type Singular Name', 'sakolawp'),
		'menu_name'           => esc_html__('Lesson', 'sakolawp'),
		'parent_item_colon'   => esc_html__('Parent Lesson:', 'sakolawp'),
		'all_items'           => esc_html__('All Lessons', 'sakolawp'),
		'view_item'           => esc_html__('View Lesson', 'sakolawp'),
		'add_new_item'        => esc_html__('Add New Lesson', 'sakolawp'),
		'add_new'             => esc_html__('Add New', 'sakolawp'),
		'edit_item'           => esc_html__('Edit Lesson', 'sakolawp'),
		'update_item'         => esc_html__('Update Lesson', 'sakolawp'),
		'search_items'        => esc_html__('Search Lessons', 'sakolawp'),
		'not_found'           => esc_html__('Not found', 'sakolawp'),
		'not_found_in_trash'  => esc_html__('Not found in Trash', 'sakolawp'),
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'query_var'          => 'lesson',
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'rewrite'            => array('slug' => 'lessons'),
		'supports'           => array('title', 'editor', 'thumbnail'),
		'menu_position'      => 0,
		'show_in_menu' 		 => false, // 'sakolawp-settings', // show under Rig university menu
		'register_meta_box_cb' => 'sakolawp_lesson_date_meta_box'

	);
	register_post_type('sakolawp-lesson', $args);
	flush_rewrite_rules();
}


// sakola lesson metabox
function sakolawp_lesson_date_meta_box()
{
	add_meta_box(
		'sakolawp-lesson-metabox',
		esc_html__('Lesson Details', 'sakolawp'),
		'sakolawp_lesson_date_meta_box_callback'
	);
}
add_action('add_meta_boxes_sakolawp-lesson', 'sakolawp_lesson_date_meta_box');

function sakolawp_lesson_date_meta_box_callback($post)
{
	wp_nonce_field('sakolawp_lesson_nonce', 'sakolawp_lesson_nonce');
?>
	<div>
		<div id="run-editlesson"></div>
	</div>
<?php
}

function sakolawp_save_lesson_date_meta_box_data($post_id)
{
	// Check if our nonce is set.
	if (!isset($_POST['sakolawp_lesson_nonce'])) {
		return;
	}

	// Verify that the nonce is valid.
	if (!wp_verify_nonce($_POST['sakolawp_lesson_nonce'], 'sakolawp_lesson_nonce')) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Check the user's permissions.
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}

	// do what you need to do
}

add_action('save_post', 'sakolawp_save_lesson_date_meta_box_data');


function my_custom_template($template)
{
	if (is_post_type_archive('sakolawp-lesson')) {
		// Check if the plugin's template file exists
		$plugin_template = plugin_dir_path(__FILE__) . 'templates/archive-sakolawp-lesson.php';
		if (file_exists($plugin_template)) {
			return $plugin_template;
		}
	}
	if (is_post_type_archive('sakolawp-course')) {
		// Check if the plugin's template file exists
		$plugin_template = plugin_dir_path(__FILE__) . 'templates/archive-sakolawp-course.php';
		if (file_exists($plugin_template)) {
			return $plugin_template;
		}
	}
	return $template;
}
add_filter('template_include', 'my_custom_template', 99);

add_filter('single_template', 'sakolawp_single_course_template');
function sakolawp_single_course_template($single)
{
	global $post;

	// course
	if ($post->post_type == 'sakolawp-course') {
		if (file_exists(SAKOLAWP_PLUGIN_DIR . '/templates/single-sakolawp-course.php')) {
			return SAKOLAWP_PLUGIN_DIR . '/templates/single-sakolawp-course.php';
		}
	}
	// lesson
	if ($post->post_type == 'sakolawp-lesson') {
		if (file_exists(SAKOLAWP_PLUGIN_DIR . '/templates/single-sakolawp-lesson.php')) {
			return SAKOLAWP_PLUGIN_DIR . '/templates/single-sakolawp-lesson.php';
		}
	}

	return $single;
}
