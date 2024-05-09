<?php
defined( 'ABSPATH' ) || exit;


$homeworkCode = $_GET['homework_code'];
$studentId = $_GET['student_id'];

global $wpdb;


if(isset($_POST['submit'])) {
	$homework_code = sanitize_text_field($_POST['homework_code']);
	$delivery_id = sanitize_text_field($_POST['answer_id']);
	$mark = sanitize_text_field($_POST['mark']);
	$comment = sanitize_text_field($_POST['comment']);
	$wpdb->update(
		$wpdb->prefix . 'sakolawp_deliveries',
		array( 
			'mark' => $mark,
			'teacher_comment' => $comment,
		),
		array(
			'delivery_id' => $delivery_id
		)
	);

	wp_redirect(add_query_arg(array('homework_code' => $homework_code, 'student_id' => $studentId), home_url( 'view_homework_student' ) ));

	die;
}

get_header(); 

do_action( 'sakolawp_before_main_content' ); 

$running_year = get_option('running_year');

$current_homework = $wpdb->get_results( "SELECT homework_code,homework_reply, file_name,student_comment,delivery_id,teacher_comment,mark FROM {$wpdb->prefix}sakolawp_deliveries WHERE homework_code = '$homeworkCode' AND student_id = $studentId", ARRAY_A );

foreach ($current_homework as $row):

	$user_info = get_user_meta($studentId);
	$first_name = $user_info["first_name"][0];
	$last_name = $user_info["last_name"][0];

	$user_name = $first_name .' '. $last_name;

	if(empty($first_name)) {
		$user_info = get_userdata($studentId);
		$user_name = $user_info->display_name;
	}

?>
<div class="homeworkroom-page skwp-content-inner">
	<div class="skwp-tab-menu">
		<ul class="skwp-tab-wrap">
			<li class="skwp-tab-items">
				<a class="skwp-tab-item" href="<?php echo add_query_arg( 'homework_code', $row['homework_code'], home_url( 'homeworkroom' ) );?>">
					<span><?php echo esc_html__( 'Homework', 'sakolawp' ); ?></span>
				</a>
			</li>
			<li class="skwp-tab-items active">
				<a class="skwp-tab-item" href="<?php echo add_query_arg( 'homework_code', $row['homework_code'], home_url( 'homeworkroom_details' ) );?>">
					<span><?php echo esc_html__( 'Homework Reports', 'sakolawp' ); ?></span>
				</a>
			</li>
			<li class="skwp-tab-items">
				<a class="skwp-tab-item" href="<?php echo add_query_arg( 'homework_code', $row['homework_code'], home_url( 'homeworkroom_edit' ) );?>">
					<span><?php echo esc_html__( 'Edit', 'sakolawp' ); ?></span>
				</a>
			</li>
		</ul>
	</div>
	<div class="skwp-clearfix skwp-row">
		<div class="skwp-column skwp-column-1">
			<div class="tugas-wrap">
				<div class="back skwp-back hidden-sm-down">
					<a href="<?php echo add_query_arg( 'homework_code', $row['homework_code'], home_url( 'homeworkroom_details' ) );?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
				</div>
				<div class="student-info">
					<?php 
					$user_img = wp_get_attachment_image_src( get_user_meta($studentId,'_user_img', array('80','80'), true, true ));
					if(!empty($user_img)) { ?>
					<img class="profile_img" src="<?php echo esc_url($user_img[0]); ?>" alt="<?php echo esc_attr($user_name); ?>">
					<?php }
					else {
						echo get_avatar( $studentId, 60 );
					} ?>
					<span><?php echo esc_html($user_name); ?></span>
				</div>
				<div class="homework-text">
					<h4><?php echo esc_html__('Student Answer :', 'sakolawp'); ?></h4>
					<p>
					  	<?php echo esc_html($row['homework_reply']);?>
					</p>
				</div>
				<?php if($row['file_name'] !== "") {
				$url_file = site_url().'/wp-content/uploads/sakolawp/deliveries/'.$row['file_name'];
				$url_file = str_replace(' ', '-', $url_file); ?>
				<div class="homework-file">
					<h4><?php echo esc_html__('Student File :', 'sakolawp'); ?></h4>
					<a href="<?php echo esc_url($url_file); ?>" class="btn btn-download skwp-btn" target="_blank">
						<span><?php echo esc_html__('Download File', 'sakolawp'); ?></span>
						<span class="file-download"><?php echo esc_html($row['file_name']); ?></span>
					</a>
				</div>
				<?php } ?>
				<div class="homework-comment">
					<h4><?php echo esc_html__('Student Comment :', 'sakolawp'); ?></h4>
					<p>
					  	<?php echo esc_html($row['student_comment']);?>
					</p>
				</div>
				<div class="teacher-comment">
					<form id="myForm" name="save_marks_homework" action="" method="POST">
						<input type="hidden" name="answer_id" value="<?php echo esc_attr($row['delivery_id']); ?>">
						<input type="hidden" name="homework_code" value="<?php echo esc_attr($row['homework_code']); ?>">
						<div class="add-comment">
							<h4><?php echo esc_html__('Add Comment :', 'sakolawp'); ?></h4>
							<textarea name="comment" id="" cols="30" rows="10"><?php echo esc_html($row['teacher_comment']); ?></textarea>
						</div>
						<div class="add-mark">
							<h4><?php echo esc_html__('Mark :', 'sakolawp'); ?></h4>
							<input type="number" name="mark" min="1" max="100" maxlength="3" required value="<?php echo esc_attr($row['mark']); ?>">
						</div>
						<button id="submit-tugas" class="btn btn-rounded btn-primary skwp-btn" name="submit" type="submit" value="submit"> <?php echo esc_html__( 'Save', 'sakolawp' ); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php 
endforeach;
?>

<?php
do_action( 'sakolawp_after_main_content' );
get_footer();