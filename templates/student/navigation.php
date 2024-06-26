<?php 
$site_name = get_bloginfo( 'name' );

?>

<div class="skwp-site-name">
<?php if ( has_custom_logo() ) : ?>
				<div class="skwp-site-logo">
					<?php the_custom_logo(); ?>
				</div>
<?php endif;

if ( $site_name  ) : ?>
				<div class="skwp-site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php echo esc_html( $site_name ); ?>
					</a>
				</div>
			<?php endif; ?>
</div>
<div class="skwp-menu-wrap">
	<div class="skwp-menu-item <?php if ($wp->request === "myaccount") echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/myaccount')); ?>">
			<i class="sakolawp-icon sakolawp-icon-web-page-home"></i>
			<?php echo esc_html__('Dashboard', 'sakolawp'); ?>

		</a>
	</div>
	<div class="skwp-menu-item <?php if ($wp->request === "class_routine") echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/class_routine')); ?>">
			<i class="sakolawp-icon sakolawp-icon-calendar"></i>
			<?php echo esc_html__('Class Routines', 'sakolawp'); ?>

		</a>

	</div>
	<div class="skwp-menu-item <?php if ($wp->request === "homework" || $wp->request === "homeworkroom" || $wp->request === "homeworkroom_details") echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/homework')); ?>">
			<i class="sakolawp-icon sakolawp-icon-shopping-list"></i>
			<?php echo esc_html__('Homeworks', 'sakolawp'); ?>
		</a>
	</div>
	<div class="skwp-menu-item <?php if ($wp->request === "peer_review" || $wp->request === "peer_review_room") echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/peer_review')); ?>">
			<i class="sakolawp-icon sakolawp-icon-shopping-list"></i>
			<?php echo esc_html__('Peer Reviews', 'sakolawp'); ?>
		</a>
	</div>
	<div class="skwp-menu-item <?php if ($wp->request === "attendance_report" || $wp->request === "report_attendance_view") echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/attendance_report')); ?>">
			<i class="sakolawp-icon sakolawp-icon-biodata"></i>
			<?php echo esc_html__('Attendance', 'sakolawp'); ?>
		</a>
	</div>
	<!-- <div class="skwp-menu-item <?php if ($wp->request === "online_exams" || $wp->request === "examroom" || $wp->request === "view_exam_result") echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/online_exams')); ?>">
				<i class="sakolawp-icon sakolawp-icon-blogging"></i>
			<?php echo esc_html__('Online Exams', 'sakolawp'); ?>
		</a>
	</div> -->
	<div class="skwp-menu-item <?php if ($wp->request === "my_marks" || $wp->request === "view_mark") echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/my_marks')); ?>">
			<i class="sakolawp-icon sakolawp-icon-checkmark"></i>
			<?php echo esc_html__('Marks', 'sakolawp'); ?>
		</a>
	</div>
	<div class="skwp-menu-item <?php if ($wp->request === "news_post" || 'sakolawp-news' == get_post_type()) echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/news_post')); ?>">
			<i class="sakolawp-icon sakolawp-icon-newspaper"></i>
			<?php echo esc_html__('News', 'sakolawp'); ?>
		</a>
	</div>
	<div class="skwp-menu-item <?php if ($wp->request === "event_post" || 'sakolawp-event' == get_post_type()) echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/event_post')); ?>">
			<i class="sakolawp-icon sakolawp-icon-printed"></i>
			<?php echo esc_html__('Event', 'sakolawp'); ?>
		</a>
	</div>
	<div class="skwp-menu-item <?php if ($wp->request === "profile_post" || 'sakolawp-event' == get_post_type()) echo esc_attr('active'); ?>">
		<a href="<?php echo esc_url(home_url('/profile_post')); ?>">
			<i class="sakolawp-icon sakolawp-icon-printed"></i>
			<?php echo esc_html__('My Account', 'sakolawp'); ?>
		</a>
	</div>
</div>