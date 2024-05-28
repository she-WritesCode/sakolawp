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

if (!empty($_POST['action']) && $_POST['action'] == 'update-user') {

	/* Update user password. */
	if (!empty($_POST['pass1']) && !empty($_POST['pass2'])) {
		if ($_POST['pass1'] == $_POST['pass2'])
			wp_update_user(array('ID' => $current_user->ID, 'user_pass' => esc_attr($_POST['pass1'])));
		else
			$error[] = esc_html__('The passwords you entered do not match.  Your password was not updated.', 'sakolawp');
	}

	/* Update user information. */
	if (!empty($_POST['mobile_number']))
		update_user_meta($current_user->ID, 'mobile_number', esc_attr($_POST['mobile_number']));
	if (!empty($_POST['next_of_kin']))
		update_user_meta($current_user->ID, 'next_of_kin', esc_attr($_POST['next_of_kin']));
	if (!empty($_POST['gender']))
		update_user_meta($current_user->ID, 'gender', esc_attr($_POST['gender']));
	if (!empty($_POST['user_city']))
		update_user_meta($current_user->ID, 'user_city', esc_attr($_POST['user_city']));
	if (!empty($_POST['user_state']))
		update_user_meta($current_user->ID, 'user_state', esc_attr($_POST['user_state']));

	if (!empty($_POST['url']))
		wp_update_user(array('ID' => $current_user->ID, 'user_url' => esc_url($_POST['url'])));
	if (!empty($_POST['email'])) {
		if (!is_email(esc_attr($_POST['email'])))
			$error[] = esc_html__('The Email you entered is not valid.  please try again.', 'sakolawp');
		elseif (email_exists(esc_attr($_POST['email'])) != $current_user->ID)
			$error[] = esc_html__('This email is already used by another user.  try a different one.', 'sakolawp');
		else {
			wp_update_user(array('ID' => $current_user->ID, 'user_email' => esc_attr($_POST['email'])));
		}
	}

	if (!empty($_POST['first-name']))
		update_user_meta($current_user->ID, 'first_name', esc_attr($_POST['first-name']));
	if (!empty($_POST['last-name']))
		update_user_meta($current_user->ID, 'last_name', esc_attr($_POST['last-name']));
	if (!empty($_POST['description']))
		update_user_meta($current_user->ID, 'description', esc_attr($_POST['description']));

	if (!empty($_FILES['user_img'])) {
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		$attach_id = media_handle_upload('user_img', $current_user->ID);
		if (is_numeric($attach_id)) {
			update_option('user_img', $attach_id);
			update_user_meta($current_user->ID, '_user_img', $attach_id);
		}
	}

	/* Redirect so the page will show updated info.*/
	/*I am not Author of this Code- i dont know why but it worked for me after changing below line to if ( count($error) == 0 ){ */
	if (count($error) == 0) {
		//action hook for plugins and extra fields saving
		//do_action('edit_user_profile_update', $current_user->ID);
		wp_safe_redirect(add_query_arg(['form_submitted' => 'true'], home_url('edit_profile')));
		exit;
	}
}
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

				<?php do_action('sakolawp_show_alert_dialog') ?>

				<form class="flex flex-col gap-4 mt-4" method="post" name="update_profile" action="" method="POST" enctype="multipart/form-data">
					<div class="form-username">
						<label for="first-name"><?php esc_html_e('First Name', 'sakolawp'); ?></label>
						<input class="text-input" name="first-name" type="text" id="first-name" value="<?php the_author_meta('first_name', $current_user->ID); ?>" />
					</div><!-- .form-username -->
					<div class="form-username">
						<label for="last-name"><?php esc_html_e('Last Name', 'sakolawp'); ?></label>
						<input class="text-input" name="last-name" type="text" id="last-name" value="<?php the_author_meta('last_name', $current_user->ID); ?>" />
					</div><!-- .form-username -->
					<div class="form-email">
						<label for="email"><?php esc_html_e('E-mail *', 'sakolawp'); ?></label>
						<input readonly class="text-input" name="email" type="text" id="email" value="<?php the_author_meta('user_email', $current_user->ID); ?>" />
					</div><!-- .form-email -->
					<div class="form-url">
						<label for="url"><?php esc_html_e('Website', 'sakolawp'); ?></label>
						<input class="text-input" name="url" type="text" id="url" value="<?php the_author_meta('user_url', $current_user->ID); ?>" />
					</div><!-- .form-url -->
					<div class="form-mobile_number">
						<label for="mobile_number"><?php esc_html_e('Phone number', 'sakolawp'); ?></label>
						<input class="text-input" name="mobile_number" type="text" id="mobile_number" value="<?php the_author_meta('mobile_number', $current_user->ID); ?>" />
					</div><!-- .form-mobile_number -->
					<div class="form-next_of_kin">
						<label for="next_of_kin"><?php esc_html_e('Next of Kin', 'sakolawp'); ?></label>
						<input class="text-input" name="next_of_kin" type="text" id="next_of_kin" value="<?php the_author_meta('next_of_kin', $current_user->ID); ?>" />
					</div><!-- .form-next_of_kin -->
					<div class="form-gender">
						<label for="gender"><?php esc_html_e('Gender', 'sakolawp'); ?></label>
						<div>
							<input type="radio" name="gender" value="male" id="gender_male" <?php checked(get_the_author_meta('gender', $current_user->ID), 'male'); ?>>
							<label for="gender_male">Male</label>
						</div>
						<div>
							<input type="radio" name="gender" value="female" id="gender_female" <?php checked(get_the_author_meta('gender', $current_user->ID), 'female'); ?>>
							<label for="gender_female">Female</label>
						</div>
					</div><!-- .form-gender -->
					<div class="form-user_city">
						<label for="user_city"><?php esc_html_e('City', 'sakolawp'); ?></label>
						<input class="text-input" name="user_city" type="text" id="user_city" value="<?php the_author_meta('user_city', $current_user->ID); ?>" />
					</div><!-- .form-user_city -->
					<div class="form-user_state">
						<label for="user_state"><?php esc_html_e('State', 'sakolawp'); ?></label>
						<input class="text-input" name="user_state" type="text" id="user_state" value="<?php the_author_meta('user_state', $current_user->ID); ?>" />
					</div><!-- .form-user_state -->
					<div class="form-password">
						<label for="pass1"><?php esc_html_e('Password *', 'sakolawp'); ?> </label>
						<input class="text-input" name="pass1" type="password" id="pass1" />
					</div><!-- .form-password -->
					<div class="form-password">
						<label for="pass2"><?php esc_html_e('Repeat Password *', 'sakolawp'); ?></label>
						<input class="text-input" name="pass2" type="password" id="pass2" />
					</div><!-- .form-password -->
					<div class="form-textarea">
						<label for="description"><?php esc_html_e('Biographical Information', 'sakolawp') ?></label>
						<textarea name="description" id="description" rows="3" cols="50"><?php the_author_meta('description', $current_user->ID); ?></textarea>
					</div><!-- .form-textarea -->

					<div class="skwp-form-group">
						<label class="col-form-label" for=""> <?php esc_html_e('Profile Image', 'sakolawp'); ?></label>
						<div class="input-group skwp-form-control mb-2">
							<input type="file" name="user_img" id="file-3" class="inputfile inputfile-3" style="display:none" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" />
							<label for="file-3"><i class="os-icon picons-thin-icon-thin-0042_attachment"></i> <span><?php esc_html_e('Upload a File', 'sakolawp'); ?></span></label>
						</div>
					</div>

					<div class="form-submit skwp-submit-profile">
						<?php //echo $referer; 
						?>
						<input name="updateuser" type="submit" id="updateuser" class="submit btn button skwp-form-btn btn-primary skwp-btn" value="<?php esc_html_e('Update', 'sakolawp'); ?>" />
						<?php wp_nonce_field('update-user') ?>
						<input name="action" type="hidden" id="action" value="update-user" />
					</div><!-- .form-submit -->
				</form><!-- #adduser -->
			<?php endif; ?>
			</div><!-- .hentry .post -->
	</div>
	<?php

	do_action('sakolawp_after_main_content');
	get_footer();
	?>