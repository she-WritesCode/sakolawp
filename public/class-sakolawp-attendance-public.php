<?php
require __DIR__ . '/../vendor/autoload.php';

class SakolawpAttendancePublic
{
    public function __construct()
    {
        add_shortcode('qr_scanner', [$this, 'display_qr_scanner']);
        add_action('wp_ajax_sakolawp_mark_attendance', [$this, 'mark_attendance']);
        add_action('wp_ajax_nopriv_sakolawp_mark_attendance', [$this, 'mark_attendance']);
    }

    function mark_attendance()
    {
        $event_id = $_POST['event_id'];
        $student_id = get_current_user_id();
        $result = [];

        if (empty($event_id) || empty($student_id)) {
            $result["message"] = 'Invalid details';
            wp_send_json_error($result, 400);
        }

        // Check if attendance already marked
        global $wpdb;
        $table_name = $wpdb->prefix . 'sakolawp_attendance';

        // Get event date and time
        $event_date = esc_attr(get_post_meta((int)$event_id, '_sakolawp_event_date', true));
        $event_time = esc_attr(get_post_meta((int)$event_id, '_sakolawp_event_date_clock', true));
        $event_class_id = sanitize_text_field($_POST['sakolawp_event_class_id']);
        $event_late_deadline = isset($_POST['sakolawp_event_late_deadline']) ? sanitize_text_field($_POST['sakolawp_event_late_deadline']) : NULL;

        $event_starts_at = strtotime("$event_date $event_time");

        // Current time
        $current_time = current_time('timestamp');

        // Calculate status
        $late_threshold = (int)$event_late_deadline * 60; // 15 minutes in seconds
        $status = ($current_time > $event_starts_at + $late_threshold) ? 'Late' : 'Present';

        // Get running year and enrollment details
        $running_year = get_option('running_year');
        $enroll = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sakolawp_enroll WHERE student_id = %d",
            $student_id
        ), OBJECT);

        // Mark attendance
        skwp_insert_or_update_record($table_name, [
            'event_id' => $event_id,
            'student_id' => $student_id,
            'section_id' => $enroll->section_id,
            'class_id' => $enroll->class_id,
            'status' => $status,
            'year' => $running_year,
            'timestamp' => $event_date,
            'time' => $event_time,
            'updated_by' => $student_id,
        ], ["student_id", "event_id", "timestamp"], 'attendance_id');

        $result["message"] = 'Attendance marked successfully';
        wp_send_json_success($result, 201);
    }


    function display_qr_scanner()
    {
        ob_start();
?>
        <div id="reader" style="width:100%;min-height:300px;"></div>
<?php
        return ob_get_clean();
    }
}
