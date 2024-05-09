<?php
defined( 'ABSPATH' ) || exit;

global $wpdb;

$teacher_id = get_current_user_id();

$running_year = get_option('running_year');

if(isset($_POST['submit'])) {
	
	$exam_id = sanitize_text_field($_POST['exam_id']);
	$class_id = sanitize_text_field($_POST['class_id']);
	$section_id = sanitize_text_field($_POST['section_id']);
	$subject_id = sanitize_text_field($_POST['subject_id']);

	$students = $wpdb->get_results( "SELECT student_id, year FROM {$wpdb->prefix}sakolawp_enroll WHERE section_id = $section_id AND class_id = $class_id AND year = '$running_year'", ARRAY_A );

	foreach($students as $row)
	{
		$stud_id = $row['student_id'];

		$verify_data = $wpdb->get_results( "SELECT subject_id FROM {$wpdb->prefix}sakolawp_mark WHERE section_id = $section_id AND class_id = $class_id AND year = '$running_year' AND subject_id = $subject_id AND student_id = $stud_id AND exam_id = $exam_id");
		$total_result_rows = $wpdb->num_rows;
		if($total_result_rows === 0)
		{
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

	wp_redirect(home_url('marks?exam_id='.$exam_id.'&class_id='.$class_id.'&section_id='.$section_id.'&subject_id='.$subject_id));

	die;
}

if(isset($_POST['marks_uploads'])) {

	$exam_id_tar = $_GET['exam_id'];
	$class_id_tar = $_GET['class_id'];
	$section_id_tar = $_GET['section_id'];
	$subject_id_tar = $_GET['subject_id'];

	$marks_of_students = $wpdb->get_results( "SELECT mark_id FROM {$wpdb->prefix}sakolawp_mark WHERE section_id = $section_id_tar AND class_id = $class_id_tar AND year = '$running_year' AND exam_id = $exam_id_tar AND subject_id = $subject_id_tar", ARRAY_A );
	foreach($marks_of_students as $row) {
		if(!empty($_POST['marks_obtained_'.$row['mark_id']])) {
			$obtained_marks = sanitize_text_field($_POST['marks_obtained_'.$row['mark_id']]);
		}
		else {
			$obtained_marks = NULL;
		}

		if($_POST['lab_2_'.$row['mark_id']] != NULL) {
			$lab2 = sanitize_text_field($_POST['lab_2_'.$row['mark_id']]);
		}
		else {
			$lab2 = NULL;
		}

		if($_POST['lab_3_'.$row['mark_id']] != NULL) {
			$lab3 = sanitize_text_field($_POST['lab_3_'.$row['mark_id']]);
		}
		else {
			$lab3 = NULL;
		}

		if($_POST['lab_4_'.$row['mark_id']] != NULL) {
			$lab4 = sanitize_text_field($_POST['lab_4_'.$row['mark_id']]);
		}
		else {
			$lab4 = NULL;
		}

		if($_POST['lab_5_'.$row['mark_id']] != NULL) {
			$lab5 = sanitize_text_field($_POST['lab_5_'.$row['mark_id']]);
		}
		else {
			$lab5 = NULL;
		}

		if($_POST['lab_6_'.$row['mark_id']] != NULL) {
			$lab6 = sanitize_text_field($_POST['lab_6_'.$row['mark_id']]);
		}
		else {
			$lab6 = NULL;
		}

		if($_POST['lab_7_'.$row['mark_id']] != NULL) {
			$lab7 = sanitize_text_field($_POST['lab_7_'.$row['mark_id']]);
		}
		else {
			$lab7 = NULL;
		}

		if($_POST['lab_8_'.$row['mark_id']] != NULL) {
			$lab8 = sanitize_text_field($_POST['lab_8_'.$row['mark_id']]);
		}
		else {
			$lab8 = NULL;
		}

		if($_POST['lab_9_'.$row['mark_id']] != NULL) {
			$lab9 = sanitize_text_field($_POST['lab_9_'.$row['mark_id']]);
		}
		else {
			$lab9 = NULL;
		}

		if($_POST['lab_10_'.$row['mark_id']] != NULL) {
			$lab10 = sanitize_text_field($_POST['lab_10_'.$row['mark_id']]);
		}
		else {
			$lab10 = NULL;
		}

		$lab_total = $obtained_marks + $lab2 + $lab3 + $lab4 + $lab5 + $lab6 + $lab7 + $lab8 + $lab9 + $lab10;

		$wpdb->update(
			$wpdb->prefix . 'sakolawp_mark',
			array( 
				'mark_obtained' => $obtained_marks,
				'lab2' => $lab2,
				'lab3' => $lab3,
				'lab4' => $lab4,
				'lab5' => $lab5,
				'lab6' => $lab6,
				'lab7' => $lab7,
				'lab8' => $lab8,
				'lab9' => $lab9,
				'lab10' => $lab10,
				'lab_total' => $lab_total,
			),
			array(
				'mark_id' => $row['mark_id']
			)
		);
	}

	wp_redirect(home_url('marks?exam_id='.$exam_id_tar.'&class_id='.$class_id_tar.'&section_id='.$section_id_tar.'&subject_id='.$subject_id_tar));

	die;
}

get_header(); 
do_action( 'sakolawp_before_main_content' ); 

?>

<input id="teacher_id_sel" type="hidden" name="teacher_id_target" value="<?php echo esc_attr($teacher_id); ?>">

<?php if(isset($_GET['exam_id']) == '' || isset($_GET['class_id']) == '' || isset($_GET['section_id']) == '' || isset($_GET['subject_id']) == '') { ?>
<div class="marks-page skwp-content-inner">
	<div class="skwp-page-title">
		<h5>
			<?php echo esc_html__('Marks','sakolawp'); ?>
		</h5>
	</div>
	<form id="ws" name="marks_selector" action="" method="POST">
		<div class="skwp-clearfix skwp-row">
			<div class="skwp-column skwp-column-5">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Semester', 'sakolawp' ); ?></label>
					<select name="exam_id" class="skwp-form-control" required="">
						<option value=""><?php echo esc_html__( 'Select', 'sakolawp' ); ?></option>
						<?php 
						global $wpdb;
						$exams = $wpdb->get_results( "SELECT exam_id,name FROM {$wpdb->prefix}sakolawp_exam", OBJECT );
						foreach($exams as $exam):
						?>
						<option value="<?php echo $exam->exam_id;?>"><?php echo esc_html($exam->name);?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-6">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Class', 'sakolawp' ); ?></label>
					<select class="skwp-form-control" name="class_id" id="class_holder" required="">
						<option value=""><?php echo __( 'Select', 'sakolawp' ); ?></option>
						<?php 
						global $wpdb;
						$classes = $wpdb->get_results( "SELECT class_id,name FROM {$wpdb->prefix}sakolawp_class", OBJECT );
						foreach($classes as $class):
						?>
						<option value="<?php echo $class->class_id;?>"><?php echo esc_html( $class->name ); ?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-6">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Section', 'sakolawp' ); ?></label>
					<select class="skwp-form-control teacher-section" name="section_id" id="section_holder" required="">
						<option value=""><?php echo __( 'Select', 'sakolawp' ); ?></option>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-4">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Subject', 'sakolawp' ); ?></label>
					<select class="skwp-form-control" id="subject_holder" required="" name="subject_id">
						<option value=""><?php echo esc_html__( 'Select', 'sakolawp' ); ?></option>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-5 skwp-mt-20">
				<div class="skwp-form-group">
				   <button id="submit-tugas" class="btn btn-rounded btn-success skwp-btn" name="submit" type="submit" value="submit"> <?php echo esc_html__( 'View', 'sakolawp' ); ?></button>
			</div>
		</div>
	</form>
</div>
<?php } ?>

<?php if(isset($_GET['exam_id']) != '' && isset($_GET['class_id']) != '' && isset($_GET['section_id']) != '' && isset($_GET['subject_id']) != '') {
$exam_id_tar = $_GET['exam_id'];
$class_id_tar = $_GET['class_id'];
$section_id_tar = $_GET['section_id'];
$subject_id_tar = $_GET['subject_id']; ?>

<div class="marks-page skwp-content-inner">
	<div class="skwp-page-title">
		<h5>
			<?php echo esc_html__('Marks','sakolawp'); ?>
		</h5>
	</div>
	<form id="ws" name="marks_selector" action="" method="POST">
		<div class="skwp-clearfix skwp-row">
			<div class="skwp-column skwp-column-5">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Semester', 'sakolawp' ); ?></label>
					<select name="exam_id" class="skwp-form-control" required="">
						<option value=""><?php echo esc_html__( 'Select', 'sakolawp' ); ?></option>
						<?php 
						global $wpdb;
						$exams = $wpdb->get_results( "SELECT exam_id,name FROM {$wpdb->prefix}sakolawp_exam", OBJECT );
						foreach($exams as $exam):
						?>
						<option value="<?php echo $exam->exam_id;?>" <?php if($exam->exam_id==$exam_id_tar){echo "selected";} ?>><?php echo esc_html($exam->name);?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-5">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Class', 'sakolawp' ); ?></label>
					<select class="skwp-form-control" name="class_id" id="class_holder" required="" onchange="select_section(this.value)">
						<option value=""><?php echo __( 'Select', 'sakolawp' ); ?></option>
						<?php 
						global $wpdb;
						$classes = $wpdb->get_results( "SELECT name,class_id FROM {$wpdb->prefix}sakolawp_class", OBJECT );
						foreach($classes as $class):
						?>
						<option value="<?php echo $class->class_id;?>" <?php if($class->class_id==$class_id_tar){echo "selected";} ?>><?php echo esc_html( $class->name ); ?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-5">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Section', 'sakolawp' ); ?></label>
					<select class="skwp-form-control" name="section_id" id="section_holder" required="" onchange="select_subject(this.value)">
						<option value=""><?php echo __( 'Select', 'sakolawp' ); ?></option>
						<?php 
						global $wpdb;
						$sections = $wpdb->get_results( "SELECT name,section_id FROM {$wpdb->prefix}sakolawp_section", OBJECT );
						foreach($sections as $section):
						?>
						<option value="<?php echo $section->section_id;?>" <?php if($section->section_id==$section_id_tar){echo "selected";} ?>><?php echo esc_html( $section->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-5">
				<div class="skwp-form-group">
					<label for=""><?php echo esc_html__( 'Subject', 'sakolawp' ); ?></label>
					<select class="skwp-form-control" id="subject_selector_holder" required="" name="subject_id">
						<option value=""><?php echo esc_html__( 'Select', 'sakolawp' ); ?></option>
						<?php 
						$subjects = $wpdb->get_results( "SELECT subject_id,name FROM {$wpdb->prefix}sakolawp_subject", OBJECT );
						foreach($subjects as $subject):
							?>
							<option value="<?php echo $subject->subject_id;?>" <?php if($subject->subject_id==$subject_id_tar){echo "selected";} ?>><?php echo $subject->name;?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="skwp-column skwp-column-5 skwp-mt-20">
				<div class="skwp-form-group">
				   <button id="submit-tugas" class="btn btn-rounded btn-success skwp-btn" name="submit" type="submit" value="submit"> <?php echo esc_html__( 'View', 'sakolawp' ); ?></button>
			</div>
		</div>
	</form>
</div>
<div class="table-responsive  skwp-mt-20">

	<form id="ws" name="marks_uploads" action="" method="POST" class="table-marks-upload">
		<table class="table table table-bordered">
			<thead>
				<tr>
					<?php $subject = $wpdb->get_row( "SELECT total_lab FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = $subject_id_tar");
					$total_kd = $subject->total_lab; ?>
					<th><?php echo esc_html__( 'Student', 'sakolawp' ); ?></th>
					<?php 
					if(empty($total_kd)) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
					<?php }
					else {
						if($total_kd == 1) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 2) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 3) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 4) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 4', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 5) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 4', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 5', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 6) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 4', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 5', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 6', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 7) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 4', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 5', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 6', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 7', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 8) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 4', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 5', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 6', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 7', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 8', 'sakolawp' ); ?></th>
						<?php }
						elseif($total_kd == 9) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 4', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 5', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 6', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 7', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 8', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 9', 'sakolawp' ); ?></th>
						<?php } 
						elseif($total_kd == 10) { ?>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 1', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 2', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 3', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 4', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 5', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 6', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 7', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 8', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 9', 'sakolawp' ); ?></th>
						<th style="text-align: center;"><?php echo esc_html__( 'Lab 10', 'sakolawp' ); ?></th>
						<?php }
					} ?>
					<th><?php echo esc_html__( 'Total', 'sakolawp' ); ?></th>
					<!-- <th>Rata-rata</th> -->
				</tr>
			</thead>
			<tbody>
			<?php 
			$marks_of_students = $wpdb->get_results( "SELECT student_id,mark_id,mark_obtained,lab2,lab3,lab4,lab5,lab6,lab7,lab8,lab9,lab10 FROM {$wpdb->prefix}sakolawp_mark WHERE section_id = $section_id_tar AND class_id = $class_id_tar AND year = '$running_year' AND exam_id = $exam_id_tar AND subject_id = $subject_id_tar", ARRAY_A );
			foreach($marks_of_students as $row) { ?>
				<tr>
					<td><?php 
					$student_id = $row['student_id'];
					$student = get_userdata($student_id);
					$user_name = $student->display_name;
					echo $user_name; ?></td>
					
					<?php 
					if(empty($total_kd)) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
					<?php }
					else {
						if($total_kd == 1) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<?php }
						elseif($total_kd == 2) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<?php }
						elseif($total_kd == 3) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<?php }
						elseif($total_kd == 4) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<td><input type="number" name="lab_4_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab4'];?>"></td>
						<?php }
						elseif($total_kd == 5) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<td><input type="number" name="lab_4_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab4'];?>"></td>
						<td><input type="number" name="lab_5_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab5'];?>"></td>
						<?php }
						elseif($total_kd == 6) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<td><input type="number" name="lab_4_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab4'];?>"></td>
						<td><input type="number" name="lab_5_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab5'];?>"></td>
						<td><input type="number" name="lab_6_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab6'];?>"></td>
						<?php }
						elseif($total_kd == 7) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<td><input type="number" name="lab_4_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab4'];?>"></td>
						<td><input type="number" name="lab_5_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab5'];?>"></td>
						<td><input type="number" name="lab_6_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab6'];?>"></td>
						<td><input type="number" name="lab_7_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab7'];?>" ></td>
						<?php }
						elseif($total_kd == 8) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<td><input type="number" name="lab_4_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab4'];?>"></td>
						<td><input type="number" name="lab_5_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab5'];?>"></td>
						<td><input type="number" name="lab_6_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab6'];?>"></td>
						<td><input type="number" name="lab_7_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab7'];?>" ></td>
						<td><input type="number" name="lab_8_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab8'];?>"></td>
						<?php }
						elseif($total_kd == 9) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<td><input type="number" name="lab_4_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab4'];?>"></td>
						<td><input type="number" name="lab_5_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab5'];?>"></td>
						<td><input type="number" name="lab_6_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab6'];?>"></td>
						<td><input type="number" name="lab_7_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab7'];?>" ></td>
						<td><input type="number" name="lab_8_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab8'];?>"></td>
						<td><input type="number" name="lab_9_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab9'];?>"></td>
						<?php } 
						elseif($total_kd == 10) { ?>
						<td><input type="number" name="marks_obtained_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['mark_obtained'];?>"></td>
						<td><input type="number" name="lab_2_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab2'];?>"></td>
						<td><input type="number" name="lab_3_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab3'];?>"></td>
						<td><input type="number" name="lab_4_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab4'];?>"></td>
						<td><input type="number" name="lab_5_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab5'];?>"></td>
						<td><input type="number" name="lab_6_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab6'];?>"></td>
						<td><input type="number" name="lab_7_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab7'];?>" ></td>
						<td><input type="number" name="lab_8_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab8'];?>"></td>
						<td><input type="number" name="lab_9_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab9'];?>"></td>
						<td><input type="number" name="lab_10_<?php echo $row['mark_id'];?>" min="0" max="100" placeholder="0" value="<?php echo $row['lab10'];?>"></td>
						<?php } 
					} ?>

					<?php 
					if(empty($total_kd)) {
						$labtotal = $row['mark_obtained'];
					}
					else {
						$total_nol = array();
						if($total_kd == 1) {
							$labtotal = $row['mark_obtained'];

							if($row['mark_obtained'] == NULL) {
								$total_nol = array($row['mark_obtained']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 2) {
							$labtotal = $row['mark_obtained'] + $row['lab2'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL) {
								$total_nol = array($row['mark_obtained']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 3) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 4) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'] + $row['lab4'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL || $row['lab4'] == NULL) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3'], $row['lab4']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 5) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'] + $row['lab4'] + $row['lab5'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL || $row['lab4'] == NULL || $row['lab5'] == NULL) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3'], $row['lab4'], $row['lab5']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 6) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'] + $row['lab4'] + $row['lab5'] + $row['lab6'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL || $row['lab4'] == NULL || $row['lab5'] == NULL || $row['lab6'] == NULL) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3'], $row['lab4'], $row['lab5'], $row['lab6']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 7) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'] + $row['lab4'] + $row['lab5'] + $row['lab6'] + $row['lab7'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL || $row['lab4'] == NULL || $row['lab5'] == NULL || $row['lab6'] == NULL || $row['lab7'] == NULL ) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3'], $row['lab4'], $row['lab5'], $row['lab6'], $row['lab7']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 8) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'] + $row['lab4'] + $row['lab5'] + $row['lab6'] + $row['lab7'] + $row['lab8'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL || $row['lab4'] == NULL || $row['lab5'] == NULL || $row['lab6'] == NULL || $row['lab7'] == NULL || $row['lab8'] == NULL) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3'], $row['lab4'], $row['lab5'], $row['lab6'], $row['lab7'], $row['lab8']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 9) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'] + $row['lab4'] + $row['lab5'] + $row['lab6'] + $row['lab7'] + $row['lab8'] + $row['lab9'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL || $row['lab4'] == NULL || $row['lab5'] == NULL || $row['lab6'] == NULL || $row['lab7'] == NULL || $row['lab8'] == NULL || $row['lab9'] == NULL) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3'], $row['lab4'], $row['lab5'], $row['lab6'], $row['lab7'], $row['lab8'], $row['lab9']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
						elseif($total_kd == 10) {
							$labtotal = $row['mark_obtained'] + $row['lab2'] + $row['lab3'] + $row['lab4'] + $row['lab5'] + $row['lab6'] + $row['lab7'] + $row['lab8'] + $row['lab9'] + $row['lab10'];

							if($row['mark_obtained'] == NULL || $row['lab2'] == NULL || $row['lab3'] == NULL || $row['lab4'] == NULL || $row['lab5'] == NULL || $row['lab6'] == NULL || $row['lab7'] == NULL || $row['lab8'] == NULL || $row['lab9'] == 0 || $row['lab10'] == 0) {
								$total_nol = array($row['mark_obtained'], $row['lab2'], $row['lab3'], $row['lab4'], $row['lab5'], $row['lab6'], $row['lab7'], $row['lab8'], $row['lab9'], $row['lab10']);

								$array_null = array();
								foreach ($total_nol as $val) {
									if($val == NULL) {
										$array_null[] = $val;
									}
								}
								$varvar = $total_kd - count($array_null);
								if($varvar != 0) {
									$total_kd2 = $total_kd - count($array_null);
								}
								else {
									$total_kd2 = $total_kd;
								}
							}
							else {
								$total_kd2 = $total_kd;
							}
						}
					} ?>

					<td><?php
					if(empty($total_kd)) {
						echo $row['mark_obtained'];
					}
					else {
						echo round($labtotal / $total_kd2, 1);
					} ?></td>
					<!-- <td></td> -->
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="skwp-button-wrap text-center">
			<button type="submit" value="marks_uploads" name="marks_uploads" class="btn btn-rounded btn-success skwp-btn"><?php echo esc_html__( 'Update', 'sakolawp' ); ?></button>
		</div>
	</form>
</div>
<?php } ?>

<?php
do_action( 'sakolawp_after_main_content' );
get_footer();