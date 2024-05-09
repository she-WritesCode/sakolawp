<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://themesawesome.com/
 * @since      1.0.0
 *
 * @package    Sakolawp
 * @subpackage Sakolawp/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="dashboard-admin skwp-content-inner">

	<div class="admin-home-dashboard skwp-tab-content tab-content" id="nav-tabContent">

		<div class="admin-home-dashboard-spec tab-pane fade show active" id="nav-dashboard" role="tabpanel" aria-labelledby="nav-dashboard-tab">
			<div class="admin-information skwp-clearfix">
				<div class="admin-welcome admin-dash-grid-area">
					<div class="admin-welcome-inner admin-dash-grid-item skwp-clearfix">
						<?php 
						global $wp;
						$current_id = get_current_user_id();
						$user_info = get_user_meta($current_id);
						$first_name = $user_info["first_name"][0];
						$last_name = $user_info["last_name"][0];

						$user_name = $first_name .' '. $last_name;

						if(empty($first_name)) {
							$user_info = get_userdata($current_id);
							$user_name = $user_info->display_name;
						} ?>
						<div class="skwp-user-img">
							<?php 
							$user_img = wp_get_attachment_image_src( get_user_meta($current_id,'_user_img', array('80','80'), true, true ));
							if(!empty($user_img)) { ?>
								<img class="profile_img" src="<?php echo esc_url($user_img[0]); ?>" alt="<?php echo esc_attr($user_name); ?>">
							<?php }
							else {
								echo get_avatar( $current_id, 80 );
							} ?>
						</div>
						<div class="skwp-admin-info">
							<h2 class="welcome-user"><?php esc_html_e('Hello, ', 'sakolawp'); ?><?php echo esc_html( $user_name ); ?></h2>
							<h4 class="welcome-txt"><?php esc_html_e('Welcome Back', 'sakolawp'); ?></h4>
						</div>
					</div>

					<div class="teacher-counter admin-dash-grid-item skwp-user-counter-item skwp-clearfix">
						<?php
							$teacher_query = new WP_User_Query( array( 'role' => 'teacher' ) );
							$teacher_count = (int) $teacher_query->get_total();
						?>
						<div class="skwp-role-info">
							<h2 class="user-item-count"><?php echo esc_html( $teacher_count ); ?></h2>
							<h4 class="user-count-role"><?php esc_html_e('Teachers', 'sakolawp'); ?></h4>
						</div>
					</div>
					<div class="student-counter admin-dash-grid-item skwp-user-counter-item skwp-clearfix">
						<?php
							$student_query = new WP_User_Query( array( 'role' => 'student' ) );
							$student_count = (int) $student_query->get_total();
						?>
						<div class="skwp-role-info">
							<h2 class="user-item-count"><?php echo esc_html( $student_count ); ?></h2>
							<h4 class="user-count-role"><?php esc_html_e('Students', 'sakolawp'); ?></h4>
						</div>
					</div>
					<div class="parent-counter admin-dash-grid-item skwp-user-counter-item skwp-clearfix">
						<?php
							$parent_query = new WP_User_Query( array( 'role' => 'parent' ) );
							$parent_count = (int) $parent_query->get_total();
						?>
						<div class="skwp-role-info">
							<h2 class="user-item-count"><?php echo esc_html( $parent_count ); ?></h2>
							<h4 class="user-count-role"><?php esc_html_e('Parents', 'sakolawp'); ?></h4>
						</div>
					</div>
				</div>
			</div>

			<!-- sakolawp card -->
			<div class="skwp-card-info-wrap skwp-clearfix">
				<div class="skwp-dash-widget-area">
					<div class="skwp-info-card skwp-exam-card">
						<div class="skwp-card-inner">
							<h2 class="card-title"><?php esc_html_e('SakolaWP  Info', 'sakolawp'); ?></h2>
							<!-- start of class table -->
							<p>
								<?php esc_html_e('Please activate your SakolaWP plugin using your purchase code to use this page.', 'sakolawp'); ?>
							</p>
							<!-- end of class table -->
						</div>
					</div>
				</div>
			</div>
			<!-- sakolawp card -->

		</div>

	</div>
</div>