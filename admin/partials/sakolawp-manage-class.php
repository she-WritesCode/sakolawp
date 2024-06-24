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

global $wpdb;
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="manage-class skwp-content-inner">

	<!-- <nav class="skwp-tabs-menu">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<div class="skwp-logo">
				<img src="<?php echo plugin_dir_url(__DIR__); ?>img/swp-logo.png" alt="<?php echo esc_attr('Sakola Logo'); ?>">
			</div>
			<a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><?php echo esc_html__('Classes', 'sakolawp'); ?></a>
			<a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><?php echo esc_html__('Add Class', 'sakolawp'); ?></a>
		</div>
	</nav> -->
	<div class="skwp-tab-content tab-content" id="nav-tabContent">
		<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
			<div id="run-listprogram"></div>


		</div>
		<div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">

			<!-- start of class form -->
			<form id="myForm" name="myform" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
				<input type="hidden" name="action" value="save_classes_setting" />
				<h5 class="skwp-form-header">
					<?php echo esc_html__('Add New Class', 'sakolawp'); ?>
				</h5>
				<div class="skwp-clearfix skwp-row">
					<div class="skwp-column skwp-column-75">
						<div class="input-group">
							<input class="skwp-form-control" placeholder="<?php echo esc_html__('Class Name', 'sakolawp'); ?>" name="name" required="" type="text">
						</div>
					</div>
				</div>
				<div class="skwp-form-button">
					<button class="btn skwp-btn btn-rounded btn-primary" type="submit"> <?php echo esc_html__('Add', 'sakolawp'); ?></button>
				</div>
			</form>
			<!-- end of class form -->

		</div>
	</div>
</div>