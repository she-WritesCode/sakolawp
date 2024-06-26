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
<div class="manage-subject skwp-content-inner">

	<nav class="skwp-tabs-menu">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<div class="skwp-logo">
				<img src="<?php echo plugin_dir_url(__DIR__); ?>img/swp-logo.png" alt="<?php echo esc_attr('Sakola Logo'); ?>">
			</div>
			<a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><?php esc_html_e('Subjects', 'sakolawp'); ?></a>
			<a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><?php esc_html_e('Add Subject', 'sakolawp'); ?></a>
		</div>
	</nav>
	<div class="skwp-tab-content tab-content" id="nav-tabContent">
		<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
			<div id="run-listsubject"></div>
			<!-- start of class table -->
			<!-- <div class="table-responsive">
				<table id="dataTable1" width="100%" class="table table-striped table-lightfont">
					<thead>
						<tr>
							<th>
								<?php esc_html_e('Parent Group', 'sakolawp'); ?>
							</th>
							<th>
								<?php esc_html_e('Class', 'sakolawp'); ?>
							</th>
							<th>
								<?php esc_html_e('Faculty', 'sakolawp'); ?>
							</th>
							<th class="text-center">
								<?php esc_html_e('Action', 'sakolawp'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						global $wpdb;
						$subjects = $wpdb->get_results("SELECT name, class_id, section_id, teacher_id, subject_id FROM {$wpdb->prefix}sakolawp_subject", OBJECT);
						foreach ($subjects as $subject) :
						?>
							<tr>
								<td>
									<?php echo esc_html($subject->name); ?>
								</td>
								<td>
									<?php
									global $wpdb;
									$classes = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $subject->class_id");
									$section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $subject->section_id");
									if ($section) {
										echo esc_html($classes->name) . '-' . esc_html($section->name);
									} else {
										echo esc_html($classes->name);
									}
									?>
								</td>
								<td>
									<?php
									$user_info = get_userdata($subject->teacher_id);
									echo $user_info ? esc_html($user_info->display_name) : '<i class="text-gray-500">No faculty assigned</i>'; ?>
								</td>
								<td>
									<a class="btn skwp-btn btn-sm btn-primary" href="<?php echo add_query_arg(array('edit' => $subject->subject_id), admin_url('admin.php?page=sakolawp-manage-subject')); ?>">
										<i class="os-icon picons-thin-icon-thin-0056_bin_trash_recycle_delete_garbage_empty"></i>
										<?php esc_html_e('Edit', 'sakolawp'); ?>
									</a>
									<a class="btn skwp-btn btn-sm btn-danger" href="<?php echo add_query_arg(array('delete' => $subject->subject_id), admin_url('admin.php?page=sakolawp-manage-subject')); ?>">
										<i class="os-icon picons-thin-icon-thin-0056_bin_trash_recycle_delete_garbage_empty"></i>
										<?php esc_html_e('Delete', 'sakolawp'); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div> -->
			<!-- end of class table -->

		</div>
		<div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
			<div id="run-addsubject"></div>
			<?php
			$args = array(
				'role'    => 'teacher',
				'orderby' => 'user_nicename',
				'order'   => 'ASC'
			);
			$teachers = get_users($args);
			?>

			<!-- start of class form -->
			<form id="myForm" name="myform" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
				<input type="hidden" name="action" value="save_subject_setting" />
				<h5 class="skwp-form-header">
					<?php esc_html_e('Add New Subject', 'sakolawp'); ?>
				</h5>
				<div class="skwp-form-group skwp-clearfix">

					<div class="skwp-row">
						<div class="skwp-column skwp-column-5">
							<label for=""> <?php esc_html_e('Subject Name', 'sakolawp'); ?></label>
							<div class="input-group">
								<input class="skwp-form-control" placeholder="Subject Name" name="name" required="" type="text">
							</div>
						</div>

						<div class="skwp-column skwp-column-5">
							<label for=""> <?php esc_html_e('Class', 'sakolawp'); ?></label>
							<div class="input-group">
								<select class="skwp-form-control" name="class_id" id="class_holder">
									<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
									<?php
									global $wpdb;
									$classes = $wpdb->get_results("SELECT class_id, name FROM {$wpdb->prefix}sakolawp_class", OBJECT);
									foreach ($classes as $class) :
									?>
										<option value="<?php echo esc_attr($class->class_id); ?>"><?php echo esc_html($class->name); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="skwp-column skwp-column-5">
							<label for=""> <?php esc_html_e('Parent Group', 'sakolawp'); ?></label>
							<div class="input-group">
								<select class="skwp-form-control" name="section_id" id="section_holder">
									<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
								</select>
							</div>
						</div>

						<div class="skwp-column skwp-column-5">
							<label for=""> <?php esc_html_e('Faculty', 'sakolawp'); ?></label>
							<div class="input-group">
								<select class="skwp-form-control" name="teacher_id">
									<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
									<?php

									foreach ($teachers as $teacher) :
									?>
										<option value="<?php echo esc_attr($teacher->ID); ?>"><?php echo esc_html($teacher->display_name); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="skwp-column skwp-column-5">
							<label for=""> <?php esc_html_e('Total Lab', 'sakolawp'); ?></label>
							<div class="input-group">
								<input class="skwp-form-control" placeholder="Total Lab" name="total_lab" type="number" min="1" max="24">
							</div>
						</div>
					</div>
				</div>
				<div class="skwp-form-button">
					<button class="btn skwp-btn btn-rounded btn-primary" type="submit"> <?php esc_html_e('Add', 'sakolawp'); ?></button>
				</div>
			</form>
			<!-- end of class form -->

		</div>
	</div>
</div>