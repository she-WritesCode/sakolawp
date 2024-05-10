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
<div class="student-area-admin skwp-content-inner">


	<nav class="skwp-tabs-menu">
		<ul class="nav nav-tabs">
			<div class="skwp-logo">
				<img src="<?php echo plugin_dir_url(__DIR__); ?>img/swp-logo.png" alt="<?php echo esc_attr('Sakola Logo'); ?>">
			</div>

			<a class="nav-link nav-item" href="admin.php?page=sakolawp-student-area">
				<span><?php esc_html_e('Students Area', 'sakolawp'); ?></span>
			</a>

			<a class="nav-item nav-link" href="admin.php?page=sakolawp-assign-student">
				<span><?php esc_html_e('Assign Student', 'sakolawp'); ?></span>
			</a>

			<a class="nav-item nav-link active" href="admin.php?page=sakolawp-add-student">
				<span><?php esc_html_e('Add Student', 'sakolawp'); ?></span>
			</a>

		</ul>
	</nav>

	<div class="skwp-tab-content">

		<!-- start of class form -->
		<form id="myForm" name="myform" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
			<input type="hidden" name="action" value="add_student_user" />
			<h5 class="skwp-form-header">
				<?php esc_html_e('Add or Upload Students', 'sakolawp'); ?>
			</h5>

			<div>
				<input name="fileToUpload" type="file" accept=".csv" />
				<a class="button upload-students-csv" href="<?php echo add_query_arg(array('create' => 'create'), admin_url('admin.php?page=sakolawp-settings')); ?>"><?php esc_html_e('Upload CSV', 'sakolawp'); ?></a>

			</div>

		</form>
		<!-- end of class form -->

	</div>
</div>