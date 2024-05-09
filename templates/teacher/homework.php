<?php
defined( 'ABSPATH' ) || exit;

global $wpdb;

if(isset($_POST['submit'])) {
	//$_POST = array_map( 'stripslashes_deep', $_POST );
	$title = sanitize_text_field($_POST['title']);
	$description = sakolawp_sanitize_html($_POST['description']);
	$time_end = sanitize_text_field($_POST['time_end']);
	$date_end = sanitize_text_field($_POST['date_end']);

	$post_id= sanitize_text_field($_POST['post_id']);
	$date_end = sanitize_text_field($_POST['date_end']);
	$datetime = strtotime(date('d-m-Y', strtotime($date_end)));
	
	$class_id = sanitize_text_field($_POST['class_id']);
	$file_name = $_FILES["file_name"]["name"];
	$section_id = sanitize_text_field($_POST['section_id']);
	$subject_id = sanitize_text_field($_POST['subject_id']);
	$uploader_type  = 'teacher';
	$uploader_id  = sanitize_text_field($_POST['uploader_id']);
	$homework_code = substr(md5(rand(100000000, 200000000)), 0, 10);
	
	$wpdb->insert(
		$wpdb->prefix . 'sakolawp_homework',
		array( 
			'homework_code' => $homework_code,
			'title' => $title,
			'description' => $description,
			'class_id' => $class_id,
			'section_id' => $section_id,
			'subject_id' => $subject_id,
			'uploader_id' => $uploader_id,
			'uploader_type' => $uploader_type,
			'time_end' => $time_end,
			'date_end' => $date_end,
			'file_name' => $file_name
		)
	);
	
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	
	add_filter( 'upload_dir', 'sakolawp_custom_dir_homework' );
	$attach_id = media_handle_upload('file_name', $post_id);
	if (is_numeric($attach_id)) {
		update_option('homework_file_name', $attach_id);
		update_post_meta($post_id, '_file_name', $attach_id);
	} 
	remove_filter( 'upload_dir', 'sakolawp_custom_dir_homework' );
	
	wp_redirect(home_url('homework'));
	
}

if(isset($_GET['action']) == 'delete') {
	$homework_code = $_GET['homework_code'];
	$wpdb->delete(
		$wpdb->prefix . 'sakolawp_homework',
		array( 
			'homework_code' => $homework_code
		)
	);
	
	wp_redirect(home_url('homework'));
}

get_header(); 
do_action( 'sakolawp_before_main_content' ); 

$running_year = get_option('running_year');

$teacher_id = get_current_user_id();

$my_homework = $wpdb->get_row( "SELECT uploader_id FROM {$wpdb->prefix}sakolawp_homework WHERE uploader_id = $teacher_id"); ?>

<input id="teacher_id_sel" type="hidden" name="teacher_id_target" value="<?php echo esc_attr($teacher_id); ?>">

<?php if(!empty($my_homework)) :

	$user_info = get_userdata($teacher_id);
	$teacher_name = $user_info->display_name;

?>
<div class="homework-inner skwp-content-inner">

	<div class="skwp-page-title skwp-clearfix">
		<h5 class="pull-left"><?php esc_html_e('My Class Homeworks', 'sakolawp'); ?>
			<span class="skwp-subtitle">
				<?php echo esc_html($teacher_name); ?>
			</span>
		</h5>
		<div class="pull-right">
			<a class="btn btn-primary btn-rounded btn-upper skwp-btn" data-target="#exampleModal1" data-toggle="modal"><?php esc_html_e('Add New', 'sakolawp'); ?></a>
		</div>
	</div>
	<div class="skwp-table table-responsive skwp-mt-20">
		<table id="tableini" class="table dataTable homework-table">
			<thead>
				<tr>
					<th class="title-homework"><?php esc_html_e('Title', 'sakolawp'); ?></th>
					<th><?php esc_html_e('Class', 'sakolawp'); ?></th>
					<th><?php esc_html_e('Subject', 'sakolawp'); ?></th>
					<th><?php esc_html_e('Due Date', 'sakolawp'); ?></th>
					<th><?php esc_html_e('Options', 'sakolawp'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$counter = 1;
					$homeworks = $wpdb->get_results( "SELECT title,class_id,section_id,subject_id,date_end,homework_code FROM {$wpdb->prefix}sakolawp_homework WHERE uploader_id = $teacher_id", ARRAY_A );
					foreach ($homeworks as $row):
					?>
					<tr>
						<td>
							<?php echo $row['title']; ?>
						</td>
						<td>
							<?php
								$class_id = $row['class_id'];
								$section_id = $row['section_id'];
								$class = $wpdb->get_row( "SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $class_id");
								echo esc_html($class->name);

								echo esc_html__(' - ', 'sakolawp');

								$section = $wpdb->get_row( "SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $section_id");
								echo esc_html($section->name);
							?>
						</td>
						<td>
							<?php $subject_id = $row['subject_id'];
								$subject = $wpdb->get_row( "SELECT name FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = $subject_id");
								echo esc_html($subject->name);
							?>
						</td>
						<td>
							<a class="btn nc btn-rounded btn-sm btn-danger skwp-btn">
								<?php echo esc_html($row['date_end']); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo add_query_arg( 'homework_code', $row['homework_code'], home_url( 'homeworkroom' ) );?>" class="btn btn-primary btn-rounded btn-sm skwp-btn">
								<?php echo esc_html__('View', 'sakolawp'); ?>
							</a>
							<a class="btn btn-danger btn-rounded btn-sm skwp-btn" onClick="return confirm('Konfirmasi Hapus')" href="<?php echo add_query_arg( array('homework_code' => $row['homework_code'], 'action' => 'delete'), home_url( 'homework' ) );?>">
								<?php echo esc_html__('Delete', 'sakolawp'); ?>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<?php 
else :
	esc_html_e('You are not create a homework for your class yet', 'sakolawp' ); ?>
	<div class="button-empty">
		<button class="btn btn-primary btn-rounded btn-upper skwp-btn" data-target="#exampleModal1" data-toggle="modal" type="button"><?php esc_html_e('Add New Homework', 'sakolawp' ); ?></button>
	</div>
	<?php
endif;
?>

<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade bd-example-modal-lg" id="exampleModal1" role="dialog" tabindex="-1">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">
					<?php echo esc_html__( 'Add New Homework', 'sakolawp' ); ?>
				</h5>
				<button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> &times;</span></button>
			</div>
			<div class="modal-body">
				<form id="myForm" name="save_create_homework" action="" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="action" value="save_create_homework" />
					<input type="hidden" name="uploader_id" value="<?php echo esc_attr($teacher_id); ?>" />
					<input type="hidden" class="skwp-form-control" name="post_id" value="<?php echo $homework_code; ?>" />
					<div class="skwp-clearfix skwp-row">
						<div class="skwp-column skwp-column-3">
							<div class="skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Class', 'sakolawp') ?></label>
								<div class="input-group">
									<select class="skwp-form-control" name="class_id" id="class_holder" required="">
										<option value=""><?php echo __( 'Select', 'sakolawp' ); ?></option>
										<?php 
										global $wpdb;
										$classes = $wpdb->get_results( "SELECT class_id,name FROM {$wpdb->prefix}sakolawp_class", OBJECT );
										foreach($classes as $class):
										?>
										<option value="<?php echo $class->class_id;?>"><?php echo esc_html($class->name);?></option>
										<?php endforeach;?>
									</select>
								</div>
							</div>
						</div>
						<div class="skwp-column skwp-column-3">
							<div class="skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Section', 'sakolawp'); ?></label>
								<div class="input-group">
									<select class="skwp-form-control teacher-section" name="section_id" required="" id="section_holder">
										<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
									</select>
								</div>
							</div>
						</div>
						<div class="skwp-column skwp-column-3">
							<div class="skwp-form-group">
								<label class="col-form-label" for=""><?php esc_html_e('Subject', 'sakolawp'); ?></label>
								<div class="input-group">
									<select class="skwp-form-control" name="subject_id" id="subject_holder" required="">
										<option value=""><?php esc_html_e('Select', 'sakolawp'); ?></option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="skwp-form-group">
						<label for=""> <?php esc_html_e('Title', 'sakolawp'); ?></label><input class="skwp-form-control" placeholder="<?php esc_html_e('Title', 'sakolawp'); ?>" name="title" required="" type="text">
					</div>
					<div class="skwp-form-group">
						<label> <?php esc_html_e('Description', 'sakolawp'); ?></label><textarea id="editordatateacher" name="description"></textarea>
					</div>
					<div class="skwp-form-group">
						<label for=""> <?php esc_html_e('Due End', 'sakolawp'); ?></label><input class="single-daterange skwp-form-control" required="" type="text" name="date_end" value="">
					</div>
					<div class="skwp-form-group">
						<label for=""> <?php esc_html_e('Time Limit', 'sakolawp'); ?></label>
						<div class="input-group clockpicker" data-align="top" data-autoclose="true">
							<input type="text" required="" name="time_end" class="skwp-form-control" value="09:30">
						</div>
					</div>
					<div class="skwp-form-group">
						<label class="col-form-label" for=""> <?php esc_html_e('Upload File', 'sakolawp'); ?></label>
						<div class="input-group skwp-form-control mb-2">
							<input type="file" name="file_name" id="file-3" class="inputfile inputfile-3" style="display:none" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf" />
							<label for="file-3"><i class="os-icon picons-thin-icon-thin-0042_attachment"></i> <span><?php esc_html_e('Upload File...', 'sakolawp'); ?></span></label>
						</div>
						<span class="warning"><?php esc_html_e('Max file size up to 10MB', 'sakolawp'); ?></span>
					</div>
				</div>
				<div class="modal-footer">
					<button id="submit-tugas" class="btn btn-rounded btn-success skwp-btn" name="submit" type="submit" value="submit"> <?php esc_html_e('Create', 'sakolawp'); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php
do_action( 'sakolawp_after_main_content' );
get_footer();