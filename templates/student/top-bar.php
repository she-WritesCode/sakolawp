<div class="skwp-user-area skwp-clearfix">
	<?php
	global $wp;
	$current_id = get_current_user_id();
	$user_info = get_user_meta($current_id);
	$first_name = $user_info["first_name"][0];
	$last_name = $user_info["last_name"][0];

	$user_name = $first_name . ' ' . $last_name;

	if (empty($first_name)) {
		$user_info = get_userdata($current_id);
		$user_name = $user_info->display_name;
	} ?>
	<div class="skwp-user-img">
		<?php
		$user_img = wp_get_attachment_image_src(get_user_meta($current_id, '_user_img', array('80', '80'), true, true));
		if (!empty($user_img)) { ?>
			<img class="profile_img" src="<?php echo esc_url($user_img[0]); ?>" alt="<?php echo esc_attr($user_name); ?>">
		<?php } else {
			echo get_avatar($current_id, 60);
		} ?>
	</div>
	<div class="skwp-user-name-area">
		<h5 class="skwp-user"><?php echo esc_html($user_name); ?></h5>
		<a href="<?php echo esc_url(home_url('edit_profile')); ?>" class="skwp-edit-profile-link skwp-prof-side">
			<i class="sakolawp2-icon-edit"></i> Edit Profile
		</a>
		<a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="skwp-edit-profile-logout skwp-prof-side">
			<i class="sakolawp2-icon-logout"></i> Logout
		</a>
	</div>
</div>