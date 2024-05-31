<?php
class RunSubjectRepo
{
    protected $subject_table = 'sakolawp_subject';
    protected $homework_table = 'sakolawp_homework';
    protected $users_table = 'users';

    /** List Subjects */
    function list($search = "")
    {
        global $wpdb;

        $sql = "SELECT s.*, COUNT(h.homework_id) AS homework_count, t.display_name as teacher_name
        FROM {$wpdb->prefix}{$this->subject_table} s
        JOIN {$wpdb->prefix}{$this->homework_table} h ON s.subject_id = h.subject_id
        JOIN {$wpdb->prefix}{$this->users_table} t ON s.teacher_id = t.ID
        WHERE s.name LIKE '%$search%'
        GROUP BY s.subject_id";

        $result = $wpdb->get_results($sql);
        return $result;
    }

    /** Read a single subject */
    function single($subject_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}sakolawp_subjects WHERE id = %d", $subject_id);
        $result = $wpdb->get_row($sql);

        return $result;
    }

    /** Create a new subject */
    function create($subject_data)
    {
        global $wpdb;
        $result = $wpdb->insert(
            "{$wpdb->prefix}sakolawp_subjects",
            $subject_data
        );
        return $result;
    }

    /** Update an existing subject */
    function update($subject_id, $subject_data)
    {
        global $wpdb;
        $result = $wpdb->update(
            "{$wpdb->prefix}sakolawp_subjects",
            $subject_data,
            array('id' => $subject_id)
        );
        return $result;
    }

    /** Delete a subject */
    function delete($subject_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}sakolawp_subjects WHERE id = %d", $subject_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
