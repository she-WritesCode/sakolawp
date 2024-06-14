<?php

global $wpdb;

$running_year = get_option('running_year');
$table_name = $wpdb->prefix . 'sakolawp_attendance';

$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : "";
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : "";
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : "";
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : "";
$current_user_id = get_current_user_id();


// Handle form submission for updating attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
	$updated_attendance = isset($_POST['attendance']) ? $_POST['attendance'] : [];

	foreach ($updated_attendance as $student_id => $row) {
		$event_id = $row['event_id'];
		$status = $row['status'];
		// Get the event details
		$event_date = esc_attr(get_post_meta((int)$event_id, '_sakolawp_event_date', true));
		$event_time = esc_attr(get_post_meta((int)$event_id, '_sakolawp_event_date_clock', true));

		// Check if the student attendance record already exists for the event
		$existing_record = $wpdb->get_row($wpdb->prepare("SELECT attendance_id FROM {$table_name} WHERE student_id = %d AND event_id = %d", $student_id, $event_id));

		// Get enrollment details
		$enroll = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = %d",
			$student_id
		), OBJECT);


		// Mark attendance
		if ($existing_record) {
			// Update the existing record
			$wpdb->update(
				$table_name,
				[
					'status' => $status,
					'section_id' => $enroll->section_id,
					'class_id' => $enroll->class_id,
					'year' => $running_year,
					'timestamp' => $event_date,
					'time' => $event_time,
					'event_id' => $event_id,
					'updated_by' => $current_user_id,
				],
				['attendance_id' => $existing_record->attendance_id]
			);
		} else {
			// Insert a new record
			$wpdb->insert(
				$table_name,
				[
					'event_id' => $event_id,
					'student_id' => $student_id,
					'section_id' => $enroll->section_id,
					'class_id' => $enroll->class_id,
					'status' => $status,
					'year' => $running_year,
					'timestamp' => $event_date,
					'time' => $event_time,
					'updated_by' => $current_user_id,
				]
			);
		}
	}

	// Redirect to the same page to avoid form resubmission
	// wp_redirect(add_query_arg(['class_id' => $class_id, 'event_id' => $event_id, 'from_date' => $from_date, 'to_date' => $to_date], 'admin.php?page=sakolawp-attendance-records'));
	// exit;
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="manage-attendance skwp-content-inner">
	<nav class="skwp-tabs-menu">
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<div class="skwp-logo">
				<img src="<?php echo plugin_dir_url(__DIR__); ?>img/swp-logo.png" alt="<?php echo esc_attr('Sakola Logo'); ?>">
			</div>
			<!-- <a class="nav-item nav-link" href="admin.php?page=sakolawp-manage-attendance"><?php esc_html_e('Student', 'sakolawp'); ?></a> -->
			<a class="nav-item nav-link active" href="admin.php?page=sakolawp-attendance-records"><?php esc_html_e('Attendance Report', 'sakolawp'); ?></a>
		</div>
	</nav>
	<div class="skwp-tab-content tab-content" id="nav-tabContent">
		<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
			<form id="myForm" name="save_student_attendance" action="" method="GET">
				<div class="flex gap-4 items-center my-4">
					<input type="hidden" name="page" value="sakolawp-attendance-records">
					<div class="flex gap-4 items-center">
						<label class="g" style="display: inline;" for=""><?php echo esc_html__('Class', 'sakolawp'); ?></label>
						<select id="class_id" class="skwp-form-contro" name="class_id" value="<?php echo esc_attr($class_id); ?>" required>
							<option value=""><?php echo esc_html__('Select', 'sakolawp'); ?></option>
							<?php
							$classes = $wpdb->get_results("SELECT class_id, name FROM {$wpdb->prefix}sakolawp_class", OBJECT);
							foreach ($classes as $class) :
							?>
								<option <?= $class->class_id == $class_id ? 'selected' : '' ?> value="<?php echo esc_attr($class->class_id); ?>"><?php echo esc_html($class->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="flex gap-4 items-center">
						<label class="g" style="display: inline;" for=""><?php echo esc_html__('Event', 'sakolawp'); ?></label>
						<select id="event_id" class="skwp-form-contro" name="event_id" value="<?php echo esc_attr($event_id); ?>">
							<option value=""><?php echo esc_html__('Select', 'sakolawp'); ?></option>
							<?php
							$events = get_posts([
								'post_type' => 'sakolawp-event',
								'posts_per_page' => -1,
								'ignore_sticky_posts' => true,
								'meta_key' => '_sakolawp_event_class_id',
								'meta_value' => $class_id,
							]);
							foreach ($events as $event) :
							?>
								<option <?= $event->ID == $event_id ? 'selected' : '' ?> value="<?php echo esc_attr($event->ID); ?>"><?php echo esc_html($event->post_title); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="flex gap-4 items-center">
						<div class="form-group skwp-mt-20">
							<button class="btn btn-rounded btn-info btn-upper skwp-btn" type="submit" name="submit" value="submit">
								<span><?php echo esc_html__('Search', 'sakolawp'); ?></span>
							</button>
						</div>
					</div>
				</div>
			</form>

			<?php if ($class_id != '') :
				$from_date = !empty($from_date) ? DateTime::createFromFormat('Y-m-d', $from_date)->setTime(0, 0)->format('Y-m-d H:i:s') : "";
				$to_date = !empty($to_date) ? DateTime::createFromFormat('Y-m-d', $to_date)->setTime(23, 59, 59, 999999)->format('Y-m-d H:i:s') : "";

				$where_clauses = [];

				if (!empty($class_id)) {
					$where_clauses[] = $wpdb->prepare("class_id = %s", $class_id);
				}

				if (!empty($event_id)) {
					$where_clauses[] = $wpdb->prepare("event_id = %s", $event_id);
				}

				if (!empty($from_date) && !empty($to_date)) {
					$where_clauses[] = $wpdb->prepare("created_at BETWEEN %s AND %s", $from_date, $to_date);
				} elseif (!empty($from_date)) {
					$where_clauses[] = $wpdb->prepare("created_at >= %s", $from_date);
				} elseif (!empty($to_date)) {
					$where_clauses[] = $wpdb->prepare("created_at <= %s", $to_date);
				}

				$where_sql = "";
				if (!empty($where_clauses)) {
					$where_sql = "WHERE " . implode(" AND ", $where_clauses);
				}

				$attendance_sql = "SELECT * FROM $table_name $where_sql;";
				$attendances = $wpdb->get_results($attendance_sql, ARRAY_A);

				// Retrieve the list of students in the selected class
				$students = $wpdb->get_results($wpdb->prepare("SELECT student_id FROM {$wpdb->prefix}sakolawp_enroll WHERE class_id = %s", $class_id), ARRAY_A);

				// Group attendance records by event
				$group_attendances_by_event = array_group_by($attendances, 'event_id');
			?>

				<div class="skwp-tabs-menu">
					<ul class="nav nav-tabs">
						<?php foreach (array_keys($group_attendances_by_event) as $current_event_id) :
							$event = get_post((int)$current_event_id); ?>
							<li class="nav-item">
								<a class="nav-link nav-light" data-toggle="tab" href="#rombel-<?= esc_attr($current_event_id); ?>"><?php esc_attr_e($event->post_title); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="skwp-tab-content tab-content">
					<?php
					foreach ($group_attendances_by_event as $current_event_id => $event_attendance) {
						$event = get_post($current_event_id);
						$event_date = esc_attr(get_post_meta((int)$current_event_id, '_sakolawp_event_date', true));
						$event_time = esc_attr(get_post_meta((int)$current_event_id, '_sakolawp_event_date_clock', true));
					?>
						<div class="tab-pane" id="rombel-<?php echo esc_attr($current_event_id); ?>">
							<div class="my-">
								<form method="POST" id="event-attendance-<?= $current_event_id; ?>" action="">

									<div class="flex justify-between">
										<div>
											<h4 class="text-lg mb-2">
												<?php
												esc_attr_e($event->post_title);
												echo ' <i>(' . date("F j, Y, g:i a", strtotime($event_date . ' ' . $event_time)) . ')</i>';
												?>
												<small>
													<a class="btn btn-info skwp-btn btn-sm" id="generate_qr_code" data-event_id="<?php echo $current_event_id; ?>">Download QR Code</a>
												</small>
											</h4>
											<div id="qr_code_holder">
												<!-- <img src="<?php echo esc_attr(get_post_meta($current_event_id, 'attendance_qr_code', true)); ?>" /> -->
											</div>
										</div>
										<div>
											<button class="btn btn-primary text-sm skwp-btn ml-2" type="submit" name="submit" value="submit">Update Attendance</button>
										</div>
									</div>
									<input type="hidden" name="update_attendance" value="1">
									<table id="dataTable-<?= $current_event_id; ?>" class="table table-responsive" style="width:100%">
										<thead>
											<tr>
												<th>Student</th>
												<th>Status</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$recorded_student_ids = array_column($event_attendance, 'student_id');

											// Display attendance records for students who attended
											foreach ($event_attendance as $attendance) {
											?>
												<tr>
													<td>
														<?php
														$student_id = $attendance['student_id'];
														$user_info = get_userdata($student_id);
														$student_enroll = $wpdb->get_row("SELECT class_id, section_id, accountability_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");

														$student_section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $student_enroll->section_id");
														$student_accountability = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_accountability WHERE accountability_id = $student_enroll->accountability_id");

														echo esc_html($user_info->display_name);
														if (isset($student_section) && isset($student_accountability)) {
														?>
															<br /><i class="skwp-subtitle"> <?php echo  esc_html($student_section->name) . ' - ' . $student_accountability->name; ?> </i>
														<?php } ?>
													</td>
													<td>
														<?php $attendance_status = htmlspecialchars($attendance['status']);
														$radio_id = $current_event_id . '-' . $student_id;
														?>
														<div class="flex gap-2 items-center">
															<input type="hidden" name="attendance[<?= $student_id; ?>][event_id]" value="<?= htmlspecialchars($current_event_id); ?>">
															<fieldset class="skwp-form-group flex gap-4 items-center">
																<div class="form-check flex gap-2 items-center">
																	<input class="form-check-input" <?= $attendance_status == 'Present' ? 'checked' : ''; ?> type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusPresent-<?= $radio_id; ?>" value="Present" />
																	<label class="form-check-label badge badge-ligh badge-success mb-0" for="statusPresent-<?= $radio_id; ?>"><?= esc_attr_e('Present'); ?></label>
																</div>
																<div class="form-check flex gap-2 items-center">
																	<input class="form-check-input" <?= $attendance_status == 'Late' ? 'checked' : ''; ?> type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusLate-<?= $radio_id; ?>" value="Late" />
																	<label class="form-check-label badge badge-light badge-warning mb-0" for="statusLate-<?= $radio_id; ?>"><?= esc_attr_e('Late'); ?></label>
																</div>
																<div class="form-check flex gap-2 items-center">
																	<input class="form-check-input" <?= $attendance_status == 'Absent' ? 'checked' : ''; ?> type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusAbsent-<?= $radio_id; ?>" value="Absent" />
																	<label class="form-check-label badge badge-light badge-danger mb-0" for="statusAbsent-<?= $radio_id; ?>"><?= esc_attr_e('Absent'); ?></label>
																</div>
																<div class="form-check flex gap-2 items-center">
																	<input class="form-check-input" <?= $attendance_status == 'Permitted' ? 'checked' : ''; ?> type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusPermitted-<?= $radio_id; ?>" value="Permitted" />
																	<label class="form-check-label badge badge-light badge-info mb-0" for="statusPermitted-<?= $radio_id; ?>"><?= esc_attr_e('Permitted'); ?></label>
																</div>
															</fieldset>
															<!-- <button class="btn btn-primary btn-sm ml-2" style="padding:5px;" type="submit" name="submit" value="submit">Save</button> -->
														</div>
													</td>
												</tr>
												<?php
											}

											// Display "Absent" for students who did not attend
											foreach ($students as $student) {
												if (!in_array($student['student_id'], $recorded_student_ids)) {
													$student_id = $student['student_id'];
													$user_info = get_userdata($student_id);
													$student_enroll = $wpdb->get_row("SELECT class_id, section_id, accountability_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");
													if ($student_enroll) :
														$student_section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = $student_enroll->section_id");
														$student_accountability = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_accountability WHERE accountability_id = $student_enroll->accountability_id");
												?>
														<tr>
															<td>
																<?php
																echo esc_html($user_info->display_name);
																if (isset($student_section) && isset($student_accountability)) {
																?>
																	<br /><i class="skwp-subtitle"> <?php echo  esc_html($student_section->name) . ' - ' . $student_accountability->name; ?> </i>
																<?php } ?>
															</td>
															<td>
																<div>
																	<input hidden name="attendance[<?php echo esc_attr($student_id); ?>][event_id]" value="<?= $current_event_id ?>">
																	<fieldset class="skwp-form-group flex gap-4 items-center">
																		<div class="form-check flex gap-2 items-center">
																			<input class="form-check-input" type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusPresent-<?= $radio_id; ?>" value="Present" />
																			<label class="form-check-label badge badge-ligh badge-success mb-0" for="statusPresent-<?= $radio_id; ?>"><?= esc_attr_e('Present'); ?></label>
																		</div>
																		<div class="form-check flex gap-2 items-center">
																			<input class="form-check-input" type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusLate-<?= $radio_id; ?>" value="Late" />
																			<label class="form-check-label badge badge-light badge-warning mb-0" for="statusLate-<?= $radio_id; ?>"><?= esc_attr_e('Late'); ?></label>
																		</div>
																		<div class="form-check flex gap-2 items-center">
																			<input class="form-check-input" checked type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusAbsent-<?= $radio_id; ?>" value="Absent" />
																			<label class="form-check-label badge badge-light badge-danger mb-0" for="statusAbsent-<?= $radio_id; ?>"><?= esc_attr_e('Absent'); ?></label>
																		</div>
																		<div class="form-check flex gap-2 items-center">
																			<input class="form-check-input" type="radio" name="attendance[<?= htmlspecialchars($student_id); ?>][status]" id="statusPermitted-<?= $radio_id; ?>" value="Permitted" />
																			<label class="form-check-label badge badge-light badge-info mb-0" for="statusPermitted-<?= $radio_id; ?>"><?= esc_attr_e('Permitted'); ?></label>
																		</div>
																	</fieldset>
																	<!-- <button class="btn btn-primary skwp-btn btn-sm ml-2" style="padding:5px;" type="submit" value="submit">save</button> -->
																</div>
															</td>
														</tr>
											<?php
													endif;
												}
											}
											?>
										</tbody>
									</table>
								</form>
							</div>
						</div>
					<?php
					}
					if (count($attendances) == 0) {
					?>
						<div class="flex items-center justify-center text-lg p-4">No attendance recorded yet</div>
					<?php } ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">

		</div>
	</div>
</div>