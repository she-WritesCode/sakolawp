<?php
defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

$running_year = get_option('running_year');

$exam_id = $_GET['exam_id'];
$student_id = $_GET['student_id'];
$subject_id = $_GET['subject_id'];

?>

<div class="sakolawp-marks-page skwp-content-inner skwp-clearfix">
	<div class="skwp-container">
		<div class="skwp-table table-responsive">
			<div class="title-marks">
				<h3>
					<?php
					$subjects = $wpdb->get_row("SELECT name, total_lab FROM {$wpdb->prefix}sakolawp_subject WHERE subject_id = {$subject_id}");
					$subjectName = $subjects->name;
					echo esc_html($subjectName); ?></h3>
			</div>
			<table id="tabbles" class="table table-marks table-lightborder">
				<thead>
					<tr>
						<th><?php echo esc_html('#'); ?></th>
						<th></th>
						<th><?php echo esc_html__('Mark', 'sakolawp'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php

					$total_kd = $subjects->total_lab;
					$lab_columns = ['mark_obtained'];
					for ($i = 2; $i <= $total_kd; $i++) {
						array_push($lab_columns, "lab{$i}");
					}

					for ($i = 0; $i < $total_kd; $i++) {
						$lab_column = $lab_columns[$i];
						$lab_number = $i + 1;
						$lab_name = 'Lab ' . $lab_number;
					?>
						<tr>
							<td><?php echo esc_html($lab_number, 'sakolawp'); ?></td>
							<td><?php echo esc_html($lab_name, 'sakolawp'); ?></td>
							<td><?php $mark = $wpdb->get_row($wpdb->prepare(
									"SELECT $lab_column FROM {$wpdb->prefix}sakolawp_mark WHERE subject_id = %d AND year = %s AND student_id = %d AND exam_id = %d",
									$subject_id,
									$running_year,
									$student_id,
									$exam_id
								));
								echo esc_html($mark->$lab_column); ?></td>
						</tr>
					<?php
					}
					?>
					<tr style="border-top: solid #a5a5a5;">
						<td>
							-
						</td>
						<td>
							<?php echo esc_html_e('Total', 'sakolawp'); ?>
						</td>
						<td>
							<?php
							$select_columns = join(", ", $lab_columns);
							$mark2 = $wpdb->get_results("SELECT {$select_columns} FROM {$wpdb->prefix}sakolawp_mark WHERE subject_id = {$subject_id} AND student_id = {$student_id} AND year = '$running_year' AND exam_id = {$exam_id}", ARRAY_A);
							$nilai = $mark2[0];

							$labtotal = 0;
							$total_nol = array();

							if (empty($total_kd) || $total_kd == 1) {
								$labtotal = $nilai['mark_obtained'];
								if ($nilai['mark_obtained'] === NULL) {
									$total_nol[] = $nilai['mark_obtained'];
								}
							} else {
								for ($i = 0; $i < $total_kd; $i++) {
									$key = $i == 0 ? 'mark_obtained' : 'lab' . ($i + 1);
									if ($nilai[$key] !== NULL) {
										$labtotal += $nilai[$key];
									} else {
										$total_nol[] = $nilai[$key];
									}
								}
							}

							$varvar = $total_kd - count($total_nol);
							$total_kd2 = $varvar != 0 ? $varvar : $total_kd;

							?>
							<a class="btn btn-rounded btn-sm btn-success skwp-btn">
								<?php
								if (empty($total_kd2)) {
									echo esc_html($labtotal ?? 0);
								} else {
									echo round($labtotal ?? 0 / $total_kd2, 1);
								} ?>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
do_action('sakolawp_after_main_content');
get_footer();
