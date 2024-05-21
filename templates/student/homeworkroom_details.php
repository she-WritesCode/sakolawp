<?php
defined('ABSPATH') || exit;

global $wpdb;

get_header();
do_action('sakolawp_before_main_content');

$student_id = get_current_user_id();

$running_year = get_option('running_year');
$peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';
$homework_table = $wpdb->prefix . 'sakolawp_homework';
$deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';

$homework_code = sanitize_text_field($_GET['homework_code']);
$current_homework = $wpdb->get_results("SELECT homework_code, homework_id, title, date_end, time_end, description, file_name, subject_id, class_id, section_id, peer_review_template, allow_peer_review FROM {$wpdb->prefix}sakolawp_homework WHERE homework_code = '$homework_code'", ARRAY_A);

foreach ($current_homework as $row) :

	$homework_id = $row['homework_id'];

	// Get peer reviews for the current user and the specific homework
	$peer_reviews = $wpdb->get_results($wpdb->prepare(
		"SELECT pr.*, d.*, h.title, h.peer_review_template
			FROM $peer_reviews_table pr 
			JOIN $deliveries_table d ON pr.delivery_id = d.delivery_id 
			JOIN $homework_table h ON pr.homework_id = h.homework_id 
			WHERE pr.reviewer_id = %d AND pr.homework_id = %d",
		$student_id,
		$homework_id
	));
?>
	<div class="homeworkroom-page skwp-content-inner">
		<div class="skwp-tab-menu">
			<ul class="skwp-tab-wrap">
				<li class="skwp-tab-items">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $homework_code, home_url('homeworkroom')); ?>">
						<span><?php echo esc_html__('Homework', 'sakolawp'); ?></span>
					</a>
				</li>
				<li class="skwp-tab-items active">
					<a class="skwp-tab-item" href="<?php echo add_query_arg('homework_code', $homework_code, home_url('homeworkroom_details')); ?>">
						<span><?php echo esc_html__('Peer Reviews Reports', 'sakolawp') . ' (' . count($peer_reviews) . ')'; ?></span>
					</a>
				</li>
			</ul>
		</div>
		<div class="back skwp-back hidden-sm-down">
			<a href="<?php echo site_url('homework'); ?>"><i class="sakolawp-icon sakolawp-icon-arrow"></i><?php echo esc_html__('Back', 'sakolawp'); ?></a>
		</div>

		<div class="homework-top">
			<?php if (count($peer_reviews) > 0) : ?>
				<div style="width: 100%;"><canvas id="peer_review_chart2"></canvas></div>
				<div style="width: 100%;"><canvas id="peer_review_chart"></canvas></div>
			<?php else : ?>
				<div> <?php echo '<div class="btn skwp-btn btn-small btn-primary">' . esc_html('No peer reviews yet') . '</div>'; ?> </div>
			<?php endif; ?>
		</div>

		<div class="homework-info skwp-mt-20">
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
							echo esc_html($subject_name['name']);
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
							echo esc_html($class['name']);
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

<?php
endforeach;
?>

<?php
do_action('sakolawp_after_main_content');
get_footer();
