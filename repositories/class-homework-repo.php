<?php
class RunHomeworkRepo
{
    protected $homework_table = 'sakolawp_homework';
    protected $deliveries_table = 'sakolawp_deliveries';
    protected $users_table = 'users';

    /** List Homeworks */
    function list($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';
        $subject_id = isset($args['subject_id']) ? $args['subject_id'] : '';
        $uploader_id = isset($args['uploader_id']) ? $args['uploader_id'] : '';

        $sql = "SELECT h.*, COUNT(d.delivery_id) AS delivery_count, t.display_name as teacher_name
            FROM {$wpdb->prefix}{$this->homework_table} h
            JOIN {$wpdb->prefix}{$this->deliveries_table} d ON h.homework_code = d.homework_code
            JOIN {$wpdb->prefix}{$this->users_table} t ON h.uploader_id = t.ID
            WHERE 1=1"; // Start with a tautology

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND h.title LIKE '%$search%'";
        }

        // Add subject_id condition
        if (!empty($subject_id)) {
            $sql .= " AND h.subject_id = $subject_id";
        }

        // Add uploader_id condition
        if (!empty($uploader_id)) {
            $sql .= " AND h.uploader_id = $uploader_id";
        }

        $sql .= " GROUP BY h.homework_id";

        $result = $wpdb->get_results($sql);
        return $result;
    }

    /** Read a single homework */
    function single($homework_id)
    {
        global $wpdb;

        $sql = "SELECT h.*,  COUNT(d.delivery_id) AS delivery_count, t.display_name as teacher_name 
            FROM {$wpdb->prefix}{$this->homework_table} h
            JOIN {$wpdb->prefix}{$this->deliveries_table} d ON h.homework_code = d.homework_code
            JOIN {$wpdb->prefix}{$this->users_table} t ON h.uploader_id = t.ID
            WHERE s.homework_id = '$homework_id'
            GROUP BY s.homework_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new homework */
    function create($homework_data)
    {
        global $wpdb;
        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->homework_table}",
            $homework_data
        );
        return $result;
    }

    /** Update an existing homework */
    function update($homework_id, $homework_data)
    {
        global $wpdb;
        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->homework_table}",
            $homework_data,
            array('homework_id' => $homework_id)
        );
        return $result;
    }

    /** Delete a homework */
    function delete($homework_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->homework_table} WHERE homework_id = %d", $homework_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
