<?php
defined('ABSPATH') || exit;

if (isset($_POST['submit'])) {

	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$from_date = sanitize_text_field($_POST['from_date']);
	$to_date = sanitize_text_field($_POST['to_date']);

	wp_redirect(add_query_arg(array('class_id' => $class_id, 'section_id' => $section_id, 'from' => $from_date, 'to' => $to_date), home_url('report_attendance_view')));
	die;
}

get_header();
do_action('sakolawp_before_main_content');

global $wpdb;

$running_year = get_option('running_year');

$student_id = get_current_user_id();

$user_info = get_userdata($student_id);
$student_name = $user_info->display_name;

$class_id = $_GET['class_id'];
$section_id = $_GET['section_id'];
$from_date = $_GET['from'];
$to_date = $_GET['to'];

$enroll = $wpdb->get_row("SELECT class_id, section_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");
if (!empty($enroll)) :

	$class = $wpdb->get_row("SELECT class_id FROM {$wpdb->prefix}sakolawp_class WHERE class_id = $enroll->class_id");
	$section = $wpdb->get_row("SELECT section_id FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $enroll->section_id"); ?>

	<style>
		.attendance-page form {
			margin-bottom: 30px;
		}
	</style>

	<div class="attendance-page skwp-content-inner skwp-clearfix">

		<div class="skwp-page-title no-border">
			<h5><?php esc_html_e('Attendance', 'sakolawp'); ?></h5>
		</div>


		<form id="myForm" name="save_student_attendance" action="" method="POST">
			<div class="skwp-row">
				<input type="hidden" name="class_id" value="<?php echo esc_attr($enroll->class_id); ?>">
				<input type="hidden" name="section_id" value="<?php echo esc_attr($enroll->section_id); ?>">
				<input type="hidden" name="operation" value="selection">
				<div class="skwp-column skwp-column-3">
					<div class="form-group">
						<label class="gi" for=""><?php echo esc_html__('From', 'sakolawp'); ?></label>
						<input value="<?php echo esc_attr($from_date); ?>" name="from_date" id="from_date" type="date" />
					</div>
				</div>
				<div class="skwp-column skwp-column-3">
					<div class="form-group">
						<label><?php echo esc_html__('To', 'sakolawp'); ?></label>
						<input value="<?php echo esc_attr($to_date); ?>" name="to_date" id="to_date" type="date" />
					</div>
				</div>
				<div class="skwp-column skwp-column-3">
					<div class="form-group skwp-mt-20">
						<button class="btn btn-rounded btn-success btn-upper skwp-btn" type="submit" name="submit" value="submit">
							<span><?php echo esc_html__('Search Attendance', 'sakolawp'); ?></span>
						</button>
					</div>
				</div>
			</div>
		</form>

		<?php if ($class_id != '' && $section_id != '' && $from_date != '') : ?>
			<div class="sakolawp-report-attendances skwp-clearfix">
				<div class="skwp-page-title">
					<h5 class="skwp-title"><?php echo esc_html__('Attendance Report', 'sakolawp') . ' ' . esc_html(date("F j, Y", strtotime($from_date))) . ' to ' . esc_html(date("F j, Y", strtotime($to_date))); ?></h5>
				</div>

				<div class="skwp-table table-responsive">
					<table id="dataTableNot2" class="table attendance attendance-table">
						<thead>
							<tr class="text-center" height="50px">
								<th class="text-left"><?php echo esc_html__('Event', 'sakolawp'); ?></th>
								<th class="text-left"><?php echo esc_html__('Status', 'sakolawp'); ?></th>
								<th class="text-left"><?php echo esc_html__('Arrived At', 'sakolawp'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$from_date = DateTime::createFromFormat('Y-m-d', $from_date)->setTime(0, 0)->format('Y-m-d H:i:s');
							$to_date = DateTime::createFromFormat('Y-m-d', $to_date)->setTime(23, 59, 59, 999999)->format('Y-m-d H:i:s');
							$attendances = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sakolawp_attendance WHERE class_id = '$class_id' AND section_id = '$section_id' AND created_at BETWEEN '$from_date' AND '$to_date' AND student_id = '$student_id'", ARRAY_A);

							$status = 0;
							foreach ($attendances as $attendance) {
							?>
								<tr>
									<td>
										<?php
										$event_id = $attendance['event_id'];
										$event = get_post($event_id);
										$event_date = esc_attr(get_post_meta((int)$event_id, '_sakolawp_event_date', true));
										$event_time = esc_attr(get_post_meta((int)$event_id, '_sakolawp_event_date_clock', true));
										esc_attr_e($event->post_title);
										echo '<br/>';
										echo '<i>' . date("F j, Y, g:i a", strtotime($event_date . ' ' . $event_time)) . '</i>';
										?>
									</td>
									<td>
										<?php
										$attendance_status = $attendance['status'];
										$status_class = 'danger';
										if ($attendance_status == "Late") {
											$status_class = 'warning';
										} elseif ($attendance_status == "Present") {
											$status_class = 'success';
										} elseif ($attendance_status == "Absent") {
											$status_class = 'danger';
										} elseif ($attendance_status == "Permitted") {
											$status_class = 'info';
										}
										?>
										<span class="badge badge-light badge-<?= $status_class; ?>"><?php esc_attr_e($attendance_status); ?></span>
									</td>
									<td>
										<?php esc_attr_e(date("F j, Y, g:i a", strtotime($attendance['created_at']))); ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>
	</div>

<?php

else :
	esc_html_e('You are not assigned to a class yet', 'sakolawp');
endif;

do_action('sakolawp_after_main_content');
get_footer();
