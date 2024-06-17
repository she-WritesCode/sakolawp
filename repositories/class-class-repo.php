<?php
class RunClassRepo
{
    protected $class_table = 'sakolawp_class';
    protected $subject_table = 'sakolawp_subject';
    protected $class_subject_table = 'sakolawp_class_subject';
    protected $section_table = 'sakolawp_section';
    protected $accountability_table = 'sakolawp_accountability';
    protected $enroll_table = 'sakolawp_enroll';
    protected $users_table = 'users';

    /** List Class */
    function list($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';

        $sql = "SELECT c.*, COUNT(DISTINCT cs.subject_id) as subject_count, COUNT(DISTINCT sec.section_id) as section_count, COUNT(DISTINCT acc.accountability_id) as accountability_count, COUNT(DISTINCT sec.teacher_id) as teacher_count, COUNT(DISTINCT en.enroll_id) as student_count 
            FROM {$wpdb->prefix}{$this->class_table} c
            LEFT JOIN {$wpdb->prefix}{$this->class_subject_table} cs ON c.class_id = cs.class_id
            LEFT JOIN {$wpdb->prefix}{$this->section_table} sec ON c.class_id = sec.class_id
            LEFT JOIN {$wpdb->prefix}{$this->accountability_table} acc ON sec.section_id = acc.section_id
            LEFT JOIN {$wpdb->prefix}{$this->enroll_table} en ON c.class_id = en.class_id
            WHERE 1=1"; // Start with a tautology

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND c.name LIKE '%$search%'";
        }

        $sql .= " GROUP BY c.class_id";

        $result = $wpdb->get_results($sql);
        return $result;
    }

    /** Read a single class */
    function single($class_id)
    {
        global $wpdb;

        $sql = "SELECT c.*, COUNT(DISTINCT cs.subject_id) as subject_count, COUNT(DISTINCT sec.section_id) as section_count, COUNT(DISTINCT acc.accountability_id) as accountability_count, COUNT(DISTINCT sec.teacher_id) as teacher_count, COUNT(DISTINCT en.enroll_id) as student_count  
            FROM {$wpdb->prefix}{$this->class_table} c
            LEFT JOIN {$wpdb->prefix}{$this->class_subject_table} cs ON c.class_id = cs.class_id
            LEFT JOIN {$wpdb->prefix}{$this->section_table} sec ON c.class_id = sec.class_id
            LEFT JOIN {$wpdb->prefix}{$this->accountability_table} acc ON sec.section_id = acc.section_id
            LEFT JOIN {$wpdb->prefix}{$this->enroll_table} en ON c.class_id = en.class_id
            WHERE c.class_id = '$class_id'
            GROUP BY c.class_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new class */
    function create($class_data)
    {
        global $wpdb;
        $result = $wpdb->insert(
            "{$wpdb->prefix}sakolawp_classs",
            $class_data
        );
        return $result;
    }

    /** Update an existing class */
    function update($class_id, $class_data)
    {
        global $wpdb;
        $result = $wpdb->update(
            "{$wpdb->prefix}sakolawp_classs",
            $class_data,
            array('class_id' => $class_id)
        );
        return $result;
    }

    /** Delete a class */
    function delete($class_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}sakolawp_classs WHERE class_id = %d", $class_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
