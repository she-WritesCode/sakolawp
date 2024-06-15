<?php
class RunClassRepo
{
    protected $class_table = 'sakolawp_class';
    protected $users_table = 'users';

    /** List Class */
    function list($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';

        $sql = "SELECT c.* FROM {$wpdb->prefix}{$this->class_table} c
            WHERE 1=1"; // Start with a tautology

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND c.name LIKE '%$search%'";
        }

        // $sql .= " GROUP BY c.class_id";

        $result = $wpdb->get_results($sql);
        return $result;
    }

    /** Read a single class */
    function single($class_id)
    {
        global $wpdb;

        $sql = "SELECT c.* FROM {$wpdb->prefix}{$this->class_table} c
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
