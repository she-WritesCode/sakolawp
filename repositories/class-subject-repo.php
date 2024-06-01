<?php
class RunSubjectRepo
{
    protected $subject_table = 'sakolawp_subject';
    protected $homework_table = 'sakolawp_homework';
    protected $lesson_table = 'sakolawp_lessons';
    protected $users_table = 'users';

    /** List Subjects */
    function list($search = "")
    {
        global $wpdb;

        $sql = "SELECT s.*, COUNT(h.homework_id) AS homework_count, COUNT(l.lesson_id) AS lesson_count, t.display_name as teacher_name
        FROM {$wpdb->prefix}{$this->subject_table} s
        LEFT JOIN {$wpdb->prefix}{$this->homework_table} h ON s.subject_id = h.subject_id
        LEFT JOIN {$wpdb->prefix}{$this->lesson_table} l ON s.subject_id = l.subject_id
        LEFT JOIN {$wpdb->prefix}{$this->users_table} t ON s.teacher_id = t.ID
        WHERE s.name LIKE '%$search%'
        GROUP BY s.subject_id";

        $result = $wpdb->get_results($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Read a single subject */
    function single($subject_id)
    {
        global $wpdb;

        $sql = "SELECT s.*, COUNT(h.homework_code) AS homework_count, COUNT(l.lesson_id) AS lesson_count, t.display_name as teacher_name 
        FROM {$wpdb->prefix}{$this->subject_table} s
        LEFT JOIN {$wpdb->prefix}{$this->homework_table} h ON s.subject_id = h.subject_id
        LEFT JOIN {$wpdb->prefix}{$this->lesson_table} l ON s.subject_id = l.subject_id
        LEFT JOIN {$wpdb->prefix}{$this->users_table} t ON s.teacher_id = t.ID
        WHERE s.subject_id = '$subject_id'
        GROUP BY s.subject_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new subject */
    function create($subject_data)
    {
        global $wpdb;
        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->subject_table}",
            $subject_data
        );
        return $result;
    }

    /** Update an existing subject */
    function update($subject_id, $subject_data)
    {
        global $wpdb;
        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->subject_table}",
            $subject_data,
            array('subject_id' => $subject_id)
        );
        return $result;
    }

    /** Delete a subject */
    function delete($subject_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->subject_table} WHERE subject_id = %d", $subject_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
