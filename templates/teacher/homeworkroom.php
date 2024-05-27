<?php
defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

global $wpdb;

$running_year = get_option('running_year');

$homework_code = sanitize_text_field($_GET['homework_code']);
$current_homework = $wpdb->get_results("SELECT homework_code, title, date_end, time_end, description, file_name, subject_id, class_id, section_id, peer_review_template, allow_peer_review, created_at FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);

foreach ($current_homework as $row) :

?>
	<div class="homeworkroom-page skwp-content-inner">
		<div class="skwp-tab-menu">
			<ul class="skwp-tab-wrap">
				<li class="skwp-tab-items active">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom')); ?>">
						<span><?php echo esc_html__('Homework', 'sakolawp'); ?></span>
					</a>
				</li>
				<li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom_details')); ?>">
						<span><?php echo esc_html__('Homework Reports', 'sakolawp'); ?></span>
					</a>
				</li>
				<!-- <li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom_edit')); ?>">
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

								<a class="skwp-tab-item ml-2 btn btn-small btn-primary skwp-btn" href="<?php echo add_query_arg('homework_code', $row['homework_code'], home_url('homeworkroom_edit')); ?>">
									<span><?php echo esc_html__('Edit Homework', 'sakolawp'); ?></span>
								</a>
							</h5>
							<div class="pipeline-header-numbers">
								<div class="pipeline-count">
									Due Date:
									<span class="btn nc btn-rounded btn-sm btn-danger skwp-btn">
										<?php echo $row['date_end']; ?>
										<?php echo $row['time_end']; ?>
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
					<?php echo esc_html__('Homework Information', 'sakolawp'); ?>
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
								$subject_name = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = '$subject_id'", ARRAY_A);
								echo $subject_name['name'];
								?>
							</td>
						</tr>
						<tr>
							<th>
								<?php echo esc_html__('Class', 'sakolawp'); ?>
							</th>
							<td>
								<?php
								$class_id = $row["class_id"];
								$class = $wpdb->get_row("SELECT name FROM {$wpdb->prefix}sakolawp_class WHERE class_id = '$class_id'", ARRAY_A);
								echo $class['name'];
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
						$peer_review_template = $row["peer_review_template"];

						if (isset($peer_review_template)) {
						?>
							<tr>
								<th>
									<?php echo esc_html__('Peer Review Template', 'sakolawp'); ?>
								</th>
								<td>
									<?php
									echo $peer_review_template;
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
