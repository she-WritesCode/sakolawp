<?php

/**
 * Template Name: User Profile
 *
 * Allow users to update their profiles from Frontend.
 *
 */

/* Get user info. */
global $current_user, $wp_roles;
//get_currentuserinfo(); //deprecated since 3.1

/* Load the registration file. */
//require_once( ABSPATH . WPINC . '/registration.php' ); //deprecated since 3.1
$error = array();


$enroll_table = $wpdb->prefix . 'sakolawp_enroll';

get_header();
do_action('sakolawp_before_main_content');
?>
<div class="exams-online-page skwp-content-inner skwp-clearfix">
	<div id="post-<?php the_ID(); ?>">
		<?php //the_content(); 
		?>
		<?php if (!is_user_logged_in()) : ?>
			<div class="warning">
				<?php _e('You must be logged in to edit your profile.', 'sakolawp'); ?>
			</div><!-- .warning -->
		<?php else : ?>
			<?php if (count($error) > 0) echo '<div class="error">' . implode("<br />", $error) . '</div>'; ?>

			<div>
				<!-- Brief Profile of the user -->
				<div class="skwp-user-area">
					<div class="skwp-profile-brief flex gap-2 items-center">
						<div class="skwp-profile-img">
							<?php
							$current_id = $current_user->ID;

							$enroll = $wpdb->get_row("SELECT class_id, section_id, accountability_id, enroll_code FROM {$enroll_table} WHERE student_id = $current_id");

							$student_class = $wpdb->get_row("SELECT class_id, name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = '$enroll->class_id'");
							$student_section = $wpdb->get_row("SELECT section_id, name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $enroll->section_id");
							$student_accountability = $wpdb->get_row("SELECT accountability_id, name FROM {$wpdb->prefix}sakolawp_accountability WHERE accountability_id = $enroll->accountability_id");

							$user_info = get_userdata($current_id);
							$user_name = $user_info->display_name;
							$user_img = wp_get_attachment_image_src(get_user_meta($current_id, '_user_img', array('80', '80'), true, true));
							if (!empty($user_img)) { ?>
								<img class="profile_img" src="<?php echo esc_url($user_img[0]); ?>" alt="<?php echo esc_attr($user_name); ?>">
							<?php } else {
								echo get_avatar($current_id, 60);
							} ?>
						</div>
						<div class="skwp-profile-name">
							<h3 class="mb-0"><?= esc_html($user_name) ?></h3>
							<div><?php echo $current_user->user_email; ?></div>
						</div>
					</div>
					<div class="skwp-profile-brief flex gap-2 items-center">
						<div class="skwp-enrollment">
							<?php

							echo '<b>Student ID:</b> ' . esc_html($enroll->enroll_code);
							if (isset($student_class)) {
								echo '<br/><b>Class:</b> ' . esc_html($student_class->name);
							}
							if (isset($student_section)) {
								echo '<br/><b>Parent Group:</b> ' . esc_html($student_section->name);
							}
							if (isset($student_accountability)) {
								echo '<br/><b>Accountability Group:</b> ' . esc_html($student_accountability->name);
							}
							?>
						</div>
					</div>
				</div>

			<?php endif; ?>
			</div><!-- .hentry .post -->
	</div>
	<?php

	do_action('sakolawp_after_main_content');
	get_footer();
	?>