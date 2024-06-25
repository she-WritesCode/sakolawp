<?php
defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

global $wpdb;

$running_year = get_option('running_year');

$homework_code = sanitize_text_field($_GET['homework_code']);
$class_id = sanitize_text_field($_GET['class_id']);
$current_homework = $wpdb->get_results("SELECT homework_id, homework_code, title, peer_review_who, description, file_name, subject_id, class_id, section_id, peer_review_template, allow_peer_review, created_at FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);

foreach ($current_homework as $row) :
	$homework_id = $row['homework_id'];

	$schedule = get_homework_schedule($class_id, $homework_id);

	if (!$schedule || !$schedule['homework_is_due_for_release']) {
		continue; // Skip if no valid schedule or not due for release
	}

	$release_date = $schedule['release_date'];
	$due_date = $schedule['due_date'];

?>
	<div class="homeworkroom-page skwp-content-inner">
		<div class="skwp-tab-menu">
			<ul class="skwp-tab-wrap">
				<li class="skwp-tab-items active">
					<a class="skwp-tab-item" href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom')); ?>">
						<span><?php echo esc_html__('Homework', 'sakolawp'); ?></span>
					</a>
				</li>
				<li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom_details')); ?>">
						<span><?php echo esc_html__('Assessment Reports', 'sakolawp'); ?></span>
					</a>
				</li>
				<!-- <li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom_edit')); ?>">
						<span><?php echo esc_html__('Edit', 'sakolawp'); ?></span>
					</a>
				</li> -->
			</ul>
		</div>
		<div class="back skwp-back hidden-sm-down">
			<a href="<?php echo site_url('homework'); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
		</div>
		<div class="skwp-clearfix skwp-row skwp-mt-10">
			<div class="skwp-column skwp-column-1">
				<div class="tugas-wrap">
					<div class="pipeline white lined-primary diskusi-desc">
						<div class="pipeline-header">
							<h5 class="pipeline-name">
								<?php echo $row['title']; ?>

								<a class="skwp-tab-item ml-2 btn btn-small btn-primary skwp-btn" href="<?php echo add_query_arg(['homework_code' => $row['homework_code'], "class_id" => $class_id], home_url('homeworkroom_edit')); ?>">
									<span><?php echo esc_html__('Edit Assessment', 'sakolawp'); ?></span>
								</a>
							</h5>
							<div class="pipeline-header-numbers">
								<div class="pipeline-count">
									Due Date:
									<span class="btn nc btn-rounded btn-sm btn-danger skwp-btn">
										<?php
										if ($due_date !== '0000-00-00 00:00:00' && $due_date !== '1970-01-01 00:00:00') {
											echo date("F j, Y, g:i a", strtotime($due_date));
										} else {
											echo "No deadline";
										}
										?>
									</span> <br>
								</div>
							</div>
						</div>
						<?php if (isset($row['description'])) : ?>
							<div class="my-4">
								<h6 class="mb-0">Instructions:</h6>
								<div>
									<?php echo esc_html($row['description']); ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($row['file_name'] != "") :
							$url_file = site_url() . '/wp-content/uploads/sakolawp/homework/' . $row['file_name']; ?>
							<div class="b-t padded-v-big homework-attachment">
								<?php echo esc_html__('Attachment', 'sakolawp'); ?>: <a class="btn btn-rounded btn-sm btn-primary skwp-btn" href="<?php echo esc_url($url_file); ?>"> <?php esc_html_e('Download Attachment', 'sakolawp'); ?></a>
							</div>
						<?php endif; ?>
						<div class="b-t padded-v-big homework-attachment">
							<?php echo esc_html__('Created On', 'sakolawp'); ?>: <a><?php echo date("d/m/Y H:i", strtotime($row['created_at'])); ?></a>
						</div>
					</div>
				</div>
			</div>

			<div class="skwp-column skwp-column-1 homework-info">
				<h5>
					<?php echo esc_html__('Assessment Information', 'sakolawp'); ?>
				</h5>
				<div class="table-responsive">
					<table class="table table-lightbor table-lightfont">
						<tr>
							<th>
								<?php echo esc_html__('Subject', 'sakolawp'); ?>
							</th>
							<td>
								<?php
								$subject_id = $row["subject_id"];
								$subject = get_post((int)$subject_id);
								echo esc_html($subject->post_title);
								?>
							</td>
						</tr>
						<?php
						$section_id = $row["section_id"];
						$section = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_section WHERE section_id = '$section_id'", ARRAY_A);

						if (isset($section)) {
						?>
							<tr>
								<th>
									<?php echo esc_html__('Parent Group', 'sakolawp'); ?>
								</th>
								<td>
									<?php
									echo $section['name'];
									?>
								</td>
							</tr>
						<?php } ?>
						<?php
						$allow_peer_review = $row["allow_peer_review"];
						$peer_review_template = $row["peer_review_template"];
						$peer_review_who = $row["peer_review_who"] == "teacher" ? "Faculty" : "Peer";

						if ($allow_peer_review) {
						?>
							<tr>
								<th>
									<?php echo esc_html__('Review Template', 'sakolawp'); ?>
								</th>
								<td>
									<?php
									echo $peer_review_template;
									?>
									<?php
									echo '<span class="badge badge-' . ($peer_review_who == 'Faculty' ? 'warning' : 'info') . ' badge-light ">' . $peer_review_who . ' reviewed</span>';
									?>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>

			</div>
		</div>
	</div>

<?php
endforeach;
?>

<?php
do_action('sakolawp_after_main_content');
get_footer();
