<?php
class RunSectionRepo
{
    protected $section_table = 'sakolawp_section';
    protected $enroll_table = 'sakolawp_enroll';
    protected $accountability_table = 'sakolawp_accountability';
    protected $users_table = 'users';


    /** List Section */
    function list($args = [])
    {
        global $wpdb;

        $class_id = isset($args['class_id']) ? $args['class_id'] : '';
        $search = isset($args['search']) ? $args['search'] : '';

        $sql = "SELECT s.*, COUNT(acc.accountability_id) as accountability_count
            FROM {$wpdb->prefix}{$this->section_table} s
            LEFT JOIN {$wpdb->prefix}{$this->accountability_table} acc ON s.section_id = acc.section_id
            WHERE 1=1"; // Start with a tautology

        // Add class_id condition
        if (!empty($class_id)) {
            $sql .= $wpdb->prepare(" AND s.class_id = %d", $class_id);
        }

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND s.name LIKE %$search%";
        }

        $sql .= " GROUP BY s.section_id";

        $result = $wpdb->get_results($sql);

        foreach ($result as $section) {
            $user = get_userdata($section->teacher_id);
            if (!empty($user)) {
                $section->teacher_name = $user->display_name;
            }
            $accountabilityRepo = new RunAccountabilityRepo();
            $section->accountabilities = $accountabilityRepo->list(['section_id' => $section->section_id]);
        }

        return $result;
    }

    /** Read a single section */
    function single($section_id)
    {
        global $wpdb;

        $sql = "SELECT s.*, COUNT(acc.accountability_id) as accountability_count
            FROM {$wpdb->prefix}{$this->section_table} s
            LEFT JOIN {$wpdb->prefix}{$this->accountability_table} acc ON s.section_id = acc.section_id
         WHERE s.section_id = '$section_id'
         GROUP BY s.section_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new section */
    function create($section_data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->section_table}",
            $section_data
        );
        return $result;
    }

    /** Update an existing homework */
    function update($section_id, $section_data)
    {
        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->section_table}",
            $section_data,
            array('section_id' => $section_id)
        );

        if (!($result)) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Delete a homework */
    function delete($section_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->section_table} WHERE section_id = %d", $section_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
