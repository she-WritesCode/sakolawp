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
		'name'                => _x('Event', 'Post Type General Name', 'sakolawp'),
		'singular_name'       => _x('Event', 'Post Type Singular Name', 'sakolawp'),
		'menu_name'           => esc_html__('Event', 'sakolawp'),
		'parent_item_colon'   => esc_html__('Parent Event:', 'sakolawp'),
		'all_items'           => esc_html__('All Event', 'sakolawp'),
		'view_item'           => esc_html__('View Event', 'sakolawp'),
		'add_new_item'        => esc_html__('Add New Event', 'sakolawp'),
		'add_new'             => esc_html__('Add New', 'sakolawp'),
		'edit_item'           => esc_html__('Edit Event', 'sakolawp'),
		'update_item'         => esc_html__('Update Event', 'sakolawp'),
		'search_items'        => esc_html__('Search Event', 'sakolawp'),
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
		'menu_position'       => 7,
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
			<h4 class="text-lg font-medium">QR Code For Attendance CODE</h4>
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
