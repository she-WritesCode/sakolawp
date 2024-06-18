<?php
class RunAccountabilityRepo
{
    protected $accountability_table = 'sakolawp_accountability';
    protected $enroll_table = 'sakolawp_enroll';
    protected $section_table = 'sakolawp_section';
    protected $users_table = 'users';


    /** List Accountability */
    function list($args = [])
    {
        global $wpdb;

        $class_id = isset($args['class_id']) ? $args['class_id'] : '';
        $section_id = isset($args['section_id']) ? $args['section_id'] : '';
        $search = isset($args['search']) ? $args['search'] : '';

        $sql = "SELECT acc.*
            FROM {$wpdb->prefix}{$this->accountability_table} acc
            WHERE 1=1"; // Start with a tautology

        // Add class_id condition
        if (!empty($class_id)) {
            $sql .= $wpdb->prepare(" AND acc.class_id = %d", $class_id);
        }

        // Add section_id condition
        if (!empty($section_id)) {
            $sql .= $wpdb->prepare(" AND acc.section_id = %d", $section_id);
        }

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND acc.name LIKE %$search%";
        }

        $sql .= " GROUP BY acc.accountability_id";

        $result = $wpdb->get_results($sql);

        return $result;
    }

    /** Read a single accountability */
    function single($accountability_id)
    {
        global $wpdb;

        $sql = "SELECT acc.*
            FROM {$wpdb->prefix}{$this->accountability_table} acc
         WHERE acc.accountability_id = '$accountability_id'
         GROUP BY acc.accountability_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new accountability */
    function create($accountability_data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->accountability_table}",
            $accountability_data
        );
        return $result;
    }

    /** Update an existing homework */
    function update($accountability_id, $accountability_data)
    {
        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->accountability_table}",
            $accountability_data,
            array('accountability_id' => $accountability_id)
        );

        if (!($result)) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Delete a homework */
    function delete($accountability_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->accountability_table} WHERE accountability_id = %d", $accountability_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
