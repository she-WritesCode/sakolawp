<?php
defined('ABSPATH') || exit;

global $wpdb;

$teacher_id = get_current_user_id();

$running_year = get_option('running_year');

if (isset($_POST['submit'])) {

	$exam_id = sanitize_text_field($_POST['exam_id']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$subject_id = sanitize_text_field($_POST['subject_id']);

	$students = $wpdb->get_results("SELECT student_id, year FROM {$wpdb->prefix}sakolawp_enroll WHERE section_id = $section_id AND class_id = $class_id AND year = '$running_year'", ARRAY_A);

	foreach ($students as $row) {
		$stud_id = $row['student_id'];

		$verify_data = $wpdb->get_results("SELECT subject_id FROM {$wpdb->prefix}sakolawp_mark WHERE section_id = $section_id AND class_id = $class_id AND year = '$running_year' AND subject_id = $subject_id AND student_id = $stud_id AND exam_id = $exam_id");
		$total_result_rows = $wpdb->num_rows;
		if ($total_result_rows === 0) {
			$wpdb->insert(
				$wpdb->prefix . 'sakolawp_mark',
				array(
					'student_id' => $stud_id,
					'exam_id' => $exam_id,
					'class_id' => $class_id,
					'section_id' => $section_id,
					'subject_id' => $subject_id,
					'year' => $running_year
				)
			);
		}
	}

	wp_redirect(home_url('marks?exam_id=' . $exam_id . '&class_id=' . $class_id . '&section_id=' . $section_id . '&subject_id=' . $subject_id));

	die;
}

if (isset($_POST['marks_uploads'])) {

	$exam_id_tar = $_GET['exam_id'];
	$class_id_tar = $_GET['class_id'];
	$section_id_tar = $_GET['section_id'];
	$subject_id_tar = $_GET['subject_id'];
	$subject = $wpdb->get_row("SELECT total_lab FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = $subject_id_tar");
	$total_kd = $subject->total_lab;

	$marks_of_students = $wpdb->get_results("SELECT mark_id FROM {$wpdb->prefix}sakolawp_mark WHERE section_id = $section_id_tar AND class_id = $class_id_tar AND year = '$running_year' AND exam_id = $exam_id_tar AND subject_id = $subject_id_tar", ARRAY_A);
	foreach ($marks_of_students as $row) {
		$mark_id = $row['mark_id'];

		$obtained_marks = !empty($_POST['marks_obtained_' . $mark_id]) ? sanitize_text_field($_POST['marks_obtained_' . $mark_id]) : 0;

		$lab_fields = [];
		$lab_total = $obtained_marks;

		for ($i = 1; $i <= $total_kd; $i++) {
			$lab_field = 'lab_' . $i . '_' . $mark_id;
			if (isset($_POST[$lab_field])) {
				$lab_value = sanitize_text_field($_POST[$lab_field]);
				$lab_fields['lab' . $i] = $lab_value;
				$lab_total += (int)$lab_value;
			} else {
				$lab_fields['lab' . $i] = NULL;
			}
		}

		$lab_fields['mark_obtained'] = $obtained_marks;
		$lab_fields['lab_total'] = $lab_total;

		$wpdb->update(
			$wpdb->prefix . 'sakolawp_mark',
			$lab_fields,
			array('mark_id' => $mark_id)
		);
	}

	wp_redirect(home_url('marks?exam_id=' . $exam_id_tar . '&class_id=' . $class_id_tar . '&section_id=' . $section_id_tar . '&subject_id=' . $subject_id_tar));
	die;
}


get_header();
do_action('sakolawp_before_main_content');

?>

<input id="teacher_id_sel" type="hidden" name="teacher_id_target" value="<?php echo esc_attr($teacher_id); ?>">

<?php if (isset($_GET['exam_id']) == '' || isset($_GET['class_id']) == '' || isset($_GET['section_id']) == '' || isset($_GET['subject_id']) == '') { ?>
	<div class="marks-page skwp-content-inner">
		<div class="skwp-page-title">
			<h5>
				<?php echo esc_html__('Marks', 'sakolawp'); ?>
			</h5>
		</div>
		<form id="ws" name="marks_selector" action="" method="POST">
			<div class="skwp-clearfix skwp-row">
				<div class="skwp-column skwp-column-5">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Semester', 'sakolawp'); ?></label>
						<select name="exam_id" class="skwp-form-control" required="">
							<option value=""><?php echo esc_html__('Select', 'sakolawp'); ?></option>
							<?php
							global $wpdb;
							$exams = $wpdb->get_results("SELECT exam_id,name FROM {$wpdb->prefix}sakolawp_exam", OBJECT);
							foreach ($exams as $exam) :
							?>
								<option value="<?php echo $exam->exam_id; ?>"><?php echo esc_html($exam->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-6">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Class', 'sakolawp'); ?></label>
						<select class="skwp-form-control" name="class_id" id="class_holder" required="">
							<option value=""><?php echo __('Select', 'sakolawp'); ?></option>
							<?php
							global $wpdb;
							$classes = $wpdb->get_results("SELECT class_id,name FROM {$wpdb->prefix}sakolawp_class", OBJECT);
							foreach ($classes as $class) :
							?>
								<option value="<?php echo $class->class_id; ?>"><?php echo esc_html($class->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-6">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Parent Group', 'sakolawp'); ?></label>
						<select class="skwp-form-control teacher-section" name="section_id" id="section_holder" required="">
							<option value=""><?php echo __('Select', 'sakolawp'); ?></option>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-4">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Subject', 'sakolawp'); ?></label>
						<select class="skwp-form-control" id="subject_holder" required="" name="subject_id">
							<option value=""><?php echo esc_html__('Select', 'sakolawp'); ?></option>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-5 skwp-mt-20">
					<div class="skwp-form-group">
						<button id="submit-tugas" class="btn btn-rounded btn-success skwp-btn" name="submit" type="submit" value="submit"> <?php echo esc_html__('View', 'sakolawp'); ?></button>
					</div>
				</div>
		</form>
	</div>
<?php } ?>

<?php if (isset($_GET['exam_id']) != '' && isset($_GET['class_id']) != '' && isset($_GET['section_id']) != '' && isset($_GET['subject_id']) != '') {
	$exam_id_tar = $_GET['exam_id'];
	$class_id_tar = $_GET['class_id'];
	$section_id_tar = $_GET['section_id'];
	$subject_id_tar = $_GET['subject_id']; ?>

	<div class="marks-page skwp-content-inner">
		<div class="skwp-page-title">
			<h5>
				<?php echo esc_html__('Marks', 'sakolawp'); ?>
			</h5>
		</div>
		<form id="ws" name="marks_selector" action="" method="POST">
			<div class="skwp-clearfix skwp-row">
				<div class="skwp-column skwp-column-5">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Semester', 'sakolawp'); ?></label>
						<select name="exam_id" class="skwp-form-control" required="">
							<option value=""><?php echo esc_html__('Select', 'sakolawp'); ?></option>
							<?php
							global $wpdb;
							$exams = $wpdb->get_results("SELECT exam_id,name FROM {$wpdb->prefix}sakolawp_exam", OBJECT);
							foreach ($exams as $exam) :
							?>
								<option value="<?php echo $exam->exam_id; ?>" <?php if ($exam->exam_id == $exam_id_tar) {
																					echo "selected";
																				} ?>><?php echo esc_html($exam->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-5">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Class', 'sakolawp'); ?></label>
						<select class="skwp-form-control" name="class_id" id="class_holder" required="" onchange="select_section(this.value)">
							<option value=""><?php echo __('Select', 'sakolawp'); ?></option>
							<?php
							global $wpdb;
							$classes = $wpdb->get_results("SELECT name,class_id FROM {$wpdb->prefix}sakolawp_class", OBJECT);
							foreach ($classes as $class) :
							?>
								<option value="<?php echo $class->class_id; ?>" <?php if ($class->class_id == $class_id_tar) {
																					echo "selected";
																				} ?>><?php echo esc_html($class->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-5">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Parent Group', 'sakolawp'); ?></label>
						<select class="skwp-form-control" name="section_id" id="section_holder" required="" onchange="select_subject(this.value)">
							<option value=""><?php echo __('Select', 'sakolawp'); ?></option>
							<?php
							global $wpdb;
							$sections = $wpdb->get_results("SELECT name,section_id FROM {$wpdb->prefix}sakolawp_section", OBJECT);
							foreach ($sections as $section) :
							?>
								<option value="<?php echo $section->section_id; ?>" <?php if ($section->section_id == $section_id_tar) {
																						echo "selected";
																					} ?>><?php echo esc_html($section->name); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-5">
					<div class="skwp-form-group">
						<label for=""><?php echo esc_html__('Subject', 'sakolawp'); ?></label>
						<select class="skwp-form-control" id="subject_selector_holder" required="" name="subject_id">
							<option value=""><?php echo esc_html__('Select', 'sakolawp'); ?></option>
							<?php
							$subjects = $wpdb->get_results("SELECT subject_id,name FROM {$wpdb->prefix}sakolawp_subject", OBJECT);
							foreach ($subjects as $subject) :
							?>
								<option value="<?php echo $subject->subject_id; ?>" <?php if ($subject->subject_id == $subject_id_tar) {
																						echo "selected";
																					} ?>><?php echo $subject->name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="skwp-column skwp-column-5 skwp-mt-20">
					<div class="skwp-form-group">
						<button id="submit-tugas" class="btn btn-rounded btn-success skwp-btn" name="submit" type="submit" value="submit"> <?php echo esc_html__('View', 'sakolawp'); ?></button>
					</div>
				</div>
		</form>
	</div>
	<div class="table-responsive  skwp-mt-20">

		<form id="ws" name="marks_uploads" action="" method="POST" class="table-marks-upload">
			<table class="table table table-bordered">
				<thead>
					<tr>
						<?php $subject = $wpdb->get_row("SELECT total_lab FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = $subject_id_tar");
						$total_kd = $subject->total_lab; ?>
						<th><?php echo esc_html__('Student', 'sakolawp'); ?></th>
						<?php
						if (empty($total_kd) || $total_kd >= 1) {
							$total_kd = $total_kd ?: 1; // Set $total_kd to 1 if it's empty
							for ($i = 1; $i <= $total_kd; $i++) {
						?>
								<th style="text-align: center;"><?php echo esc_html__('Lab ' . $i, 'sakolawp'); ?></th>
						<?php
							}
						}
						?>
						<th><?php echo esc_html__('Total', 'sakolawp'); ?></th>
						<!-- <th>Rata-rata</th> -->
					</tr>
				</thead>
				<tbody>
					<?php
					$lab_columns = '';
					for ($i = 1; $i <= $total_kd; $i++) {
						$lab_columns .= ",lab{$i}";
					}
					$marks_of_students = $wpdb->get_results("SELECT student_id,mark_id,mark_obtained{$lab_columns} FROM {$wpdb->prefix}sakolawp_mark WHERE section_id = $section_id_tar AND class_id = $class_id_tar AND year = '$running_year' AND exam_id = $exam_id_tar AND subject_id = $subject_id_tar", ARRAY_A);
					foreach ($marks_of_students as $row) { ?>
						<tr>
							<td><?php echo get_userdata($row['student_id'])->display_name; ?></td>
							<?php for ($i = 1; $i <= $total_kd; $i++) { ?>
								<td><input type="number" name="lab_<?php echo $i; ?>_<?php echo $row['mark_id']; ?>" min="0" max="100" placeholder="0" value="<?php echo ($i == 1) ? $row['mark_obtained'] : $row['lab' . $i]; ?>"></td>
							<?php } ?>
							<td><?php echo empty($total_kd) ? $row['mark_obtained'] : round(array_sum(array_filter(array_slice($row, 2, $total_kd))), 1); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<div class="skwp-button-wrap text-center">
				<button type="submit" value="marks_uploads" name="marks_uploads" class="btn btn-rounded btn-success skwp-btn"><?php echo esc_html__('Update', 'sakolawp'); ?></button>
			</div>
		</form>
	</div>
<?php } ?>

<?php
do_action('sakolawp_after_main_content');
get_footer();
