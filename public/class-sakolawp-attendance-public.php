<?php
require __DIR__ . '/../vendor/autoload.php';

class SakolawpAttendancePublic
{
    public function __construct()
    {
        add_shortcode('qr_scanner', [$this, 'display_qr_scanner']);
    }

    function mark_attendance()
    {
        $event_id = $_POST['event_id'];
        $student_id = get_current_user_id();
        $result = [];

        if (empty($event_id) || $student_id) {
            $result["message"] = 'Invalid details';
            wp_send_json_error($result, 400);
        }

        // Check if attendance already marked
        global $wpdb;
        $table_name = $wpdb->prefix . 'sakolawp_attendance';
        $already_marked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE student_id = %d AND event_id = %d",
            $student_id,
            $event_id
        ));

        if ($already_marked) {
            $result["message"] = 'Attendance already marked';
            wp_send_json_success($result, 200);
        }

        // Mark attendance
        $wpdb->insert($table_name, [
            'student_id' => $student_id,
            'event_id' => $event_id,
            'time' => current_time('mysql')
        ]);

        $result["message"] = 'Attendance marked successfully';
        wp_send_json_success($result, 201);
    }

    function display_qr_scanner()
    {
        ob_start();
?>
        <div id="reader" style="width:100%;background:#ffeded;min-height:300px;"></div>
<?php
        return ob_get_clean();
    }
}
