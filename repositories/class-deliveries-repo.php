<?php
class RunDeliveryRepo
{
    // protected $homework_table = 'sakolawp_homework';
    protected $deliveries_table = 'sakolawp_deliveries';
    protected $users_table = 'users';
    protected $subject_table = 'sakolawp_subject';


    /** List Deliveries */
    function list($args = [])
    {
        global $wpdb;

        $subject_id = isset($args['subject_id']) ? $args['subject_id'] : '';
        $student_id = isset($args['student_id']) ? $args['student_id'] : '';
        $homework_code = isset($args['homework_code']) ? $args['homework_code'] : '';

        $sql = "SELECT d.*, s.name AS subject_name, st.display_name as student_name
            FROM {$wpdb->prefix}{$this->deliveries_table} d
            LEFT JOIN {$wpdb->prefix}{$this->subject_table} s ON d.subject_id = s.subject_id
            LEFT JOIN {$wpdb->prefix}{$this->users_table} st ON d.student_id = st.ID
            WHERE 1=1"; // Start with a tautology

        // Add subject_id condition
        if (!empty($subject_id)) {
            $sql .= $wpdb->prepare(" AND d.subject_id = %d", $subject_id);
        }

        // Add student_id condition
        if (!empty($student_id)) {
            $sql .= $wpdb->prepare(" AND d.student_id = %d", $student_id);
        }

        // Add homework_code condition
        if (!empty($homework_code)) {
            $sql .= $wpdb->prepare(" AND d.homework_code = %s", $homework_code);
        }

        $sql .= " GROUP BY d.delivery_id";

        $result = $wpdb->get_results($sql);

        return $result;
    }

    /** Read a single delivery */
    function single($delivery_id)
    {
        global $wpdb;

        $sql = "SELECT d.*, s.name AS subject_name, st.display_name as student_name
         FROM {$wpdb->prefix}{$this->deliveries_table} d
         LEFT JOIN {$wpdb->prefix}{$this->subject_table} s ON d.homework_code = s.homework_code
         LEFT JOIN {$wpdb->prefix}{$this->users_table} st ON d.student_id = st.ID
         WHERE d.delivery_id = '$delivery_id'
         GROUP BY d.delivery_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new delivery */
    function create($delivery_data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->deliveries_table}",
            $delivery_data
        );
        return $result;
    }

    /** Update an existing homework */
    function update($delivery_id, $homework_data)
    {
        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->deliveries_table}",
            $homework_data,
            array('delivery_id' => $delivery_id)
        );

        if (!($result)) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Delete a homework */
    function delete($delivery_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->deliveries_table} WHERE delivery_id = %d", $delivery_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
