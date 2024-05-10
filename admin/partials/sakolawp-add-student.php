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

if (isset($_POST['submit'])) {
	$class_id = $_POST['class_id'];
}
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
				<span><?php esc_html_e('Add Students', 'sakolawp'); ?></span>
			</a>

		</ul>
	</nav>

	<div class="skwp-tab-content">
		<?php sakolawp_show_error_messages(); ?>

		<?php
		if (isset($_GET['success']) && $_GET['success'] == 1) {
		?>
			<div class="alert alert-success alert-dismissible flex justify-between" role="alert">
				<div><?php esc_html_e('Successfully Added All Students', 'sakolawp'); ?></div>
				<a href="<?php echo add_query_arg(array('success' => '0'), admin_url('admin.php?page=sakolawp-add-student')); ?>" type="button" class="btn btn-close" data-bs-dismiss="alert" aria-label="Close">close</a>
			</div>
		<?php
		}
		?>
		<!-- start of class form -->
		<form id="myForm" name="myform" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="action" value="add_student_user" />
			<h5 class="skwp-form-header">
				<?php esc_html_e('Add or Upload Students', 'sakolawp'); ?>
			</h5>


			<div class="skwp-row skwp-clearfix">

				<div class="skwp-column skwp-column-2">
					<div class="skwp-form-group">
						<label class="gi" for="class_holder"><?php echo esc_html__('Class :', 'sakolawp'); ?></label>
						<select required name="class_id" class="skwp-form-control" id="class_holder">
							<option value=""><?php echo esc_html__('Select', 'sakolawp'); ?></option>
							<?php
							$classes = $wpdb->get_results("SELECT class_id, name FROM {$wpdb->prefix}sakolawp_class", OBJECT);
							foreach ($classes as $class) :
							?>
								<option value="<?php echo esc_attr($class->class_id); ?>"><?php echo esc_html($class->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="skwp-column skwp-column-2">
					<div class="skwp-form-group"> <label class="gi" for="file_to_upload"><?php echo esc_html__('CSV File :', 'sakolawp'); ?></label>
						<input required class="skwp-form-control" id="file_to_upload" name="filetoupload" type="file" accept=".csv" />
					</div>
				</div>

				<div class="skwp-column skwp-column-1">
					<div class="skwp-form-group skwp-mt-10">
						<input class="inline" id="should_send_email" name="should_send_email" type="checkbox" accept=".csv" />
						<label class="gi inline" for="should_send_email"><?php echo esc_html__('Check the box below to send login email to all participants', 'sakolawp'); ?></label>
					</div>
				</div>
				<div class="skwp-column skwp-column-1">
					<div class="skwp-form-group skwp-mt-10"> <button class="btn skwp-btn btn-rounded btn-primary" type="submit" value="submit" name="submit"><span><?php echo esc_html__('Upload CSV', 'sakolawp'); ?></span></button></div>
				</div>
			</div>
		</form>
		<!-- end of class form -->

	</div>
</div>