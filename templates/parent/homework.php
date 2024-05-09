<?php

defined( 'ABSPATH' ) || exit;

get_header(); 
do_action( 'sakolawp_before_main_content' ); 

global $wpdb;

$running_year = get_option('running_year');

$parent_id = get_current_user_id();

$student_id = get_user_meta( $parent_id, 'related_student' , true );

$enroll = $wpdb->get_row( "SELECT class_id, section_id FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = $student_id");

if(!empty($enroll)) :

$user_info = get_userdata($student_id);
$student_name = $user_info->display_name;

?>

<div class="homework-inner skwp-content-inner">

	<div class="skwp-page-title">
		<h5><?php echo esc_html_e('My Class Homeworks', 'sakolawp'); ?>
			<span class="skwp-subtitle">
				<?php echo esc_html($student_name); ?>
			</span>
		</h5>
	</div>	

	<div class="skwp-table table-responsive">
		<table id="tableini" width="100%" class="table table-lightborder table-lightfont">
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
					$homeworks = $wpdb->get_results( "SELECT title, class_id, section_id, subject_id, date_end, homework_code FROM {$wpdb->prefix}sakolawp_homework 
						WHERE class_id = '$enroll->class_id'
                        AND section_id = '$enroll->section_id'", ARRAY_A );
                        
					foreach ($homeworks as $row):
				?>
					<tr>
						<td>
							<?php echo esc_html($row['title']); ?>
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
						<td class="row-actions">
							<a href="<?php echo add_query_arg( 'homework_code', $row['homework_code'], home_url( 'homeworkroom' ) );?>">
								<button class="btn btn-primary btn-rounded btn-sm skwp-btn">
									<?php echo esc_html__('View', 'sakolawp'); ?>
								</button>
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
	esc_html_e('Your child not assign to a class yet', 'sakolawp' );
endif;

do_action( 'sakolawp_after_main_content' );
get_footer();