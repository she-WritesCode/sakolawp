<?php
class RunLessonRepo
{
    protected $lesson_table = 'sakolawp_lessons';
    protected $users_table = 'users';

    /** List Lessons */
    function list($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';
        $subject_id = isset($args['subject_id']) ? $args['subject_id'] : '';
        $uploader_id = isset($args['uploader_id']) ? $args['uploader_id'] : '';

        $sql = "SELECT l.*, t.display_name as teacher_name
            FROM {$wpdb->prefix}{$this->lesson_table} l
            JOIN {$wpdb->prefix}{$this->users_table} t ON l.uploader_id = t.ID
            WHERE 1=1"; // Start with a tautology

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND l.title LIKE '%$search%'";
        }

        // Add subject_id condition
        if (!empty($subject_id)) {
            $sql .= " AND l.subject_id = $subject_id";
        }

        // Add uploader_id condition
        if (!empty($uploader_id)) {
            $sql .= " AND l.uploader_id = $uploader_id";
        }

        $sql .= " GROUP BY l.lesson_id";

        $result = $wpdb->get_results($sql);
        return $result;
    }

    /** Read a single lesson */
    function single($lesson_id)
    {
        global $wpdb;

        $sql = "SELECT l.*, COUNT(l.lesson_id) AS lesson_count, t.display_name as teacher_name 
        FROM {$wpdb->prefix}{$this->lesson_table} l
        JOIN {$wpdb->prefix}{$this->users_table} t ON l.teacher_id = t.ID
        WHERE l.lesson_id = '$lesson_id'
        GROUP BY l.lesson_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new lesson */
    function create($lesson_data)
    {
        global $wpdb;
        $result = $wpdb->insert(
            "{$wpdb->prefix}sakolawp_lessons",
            $lesson_data
        );
        return $result;
    }

    /** Update an existing lesson */
    function update($lesson_id, $lesson_data)
    {
        global $wpdb;
        $result = $wpdb->update(
            "{$wpdb->prefix}sakolawp_lessons",
            $lesson_data,
            array('lesson_id' => $lesson_id)
        );
        return $result;
    }

    /** Delete a lesson */
    function delete($lesson_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}sakolawp_lessons WHERE lesson_id = %d", $lesson_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
