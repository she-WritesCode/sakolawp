<?php
class RunEnrollRepo
{
    protected $enroll_table = 'sakolawp_enroll';
    protected $class_table = 'sakolawp_class';
    protected $section_table = 'sakolawp_section';
    protected $accountability_table = 'sakolawp_accountability';
    protected $users_table = 'users';

    /** List Enrolls */
    public function list($args = [])
    {
        global $wpdb;

        // Initialize variables with default values
        $search = isset($args['search']) ? $args['search'] : '';
        $enroll_code = isset($args['enroll_code']) ? $args['enroll_code'] : '';
        $student_id = isset($args['student_id']) ? $args['student_id'] : '';
        $class_id = isset($args['class_id']) ? $args['class_id'] : '';
        $section_id = isset($args['section_id']) ? $args['section_id'] : '';
        $accountability_id = isset($args['accountability_id']) ? $args['accountability_id'] : '';
        $roll = isset($args['roll']) ? $args['roll'] : '';

        // Build the SQL query
        $sql = "
            SELECT 
                e.*, 
                u.display_name AS student_name, 
                u.user_email AS student_email, 
                c.name AS class_name, 
                s.name AS section_name, 
                a.name AS accountability_name
            FROM {$wpdb->prefix}{$this->enroll_table} e
            LEFT JOIN {$wpdb->prefix}{$this->users_table} u ON e.student_id = u.ID
            LEFT JOIN {$wpdb->prefix}{$this->class_table} c ON e.class_id = c.class_id
            LEFT JOIN {$wpdb->prefix}{$this->section_table} s ON e.section_id = s.section_id
            LEFT JOIN {$wpdb->prefix}{$this->accountability_table} a ON e.accountability_id = a.accountability_id
            WHERE 1=1
        ";

        if (!empty($enroll_code)) {
            $sql .= $wpdb->prepare(" AND e.enroll_code = %s", $enroll_code);
        }

        if (!empty($student_id)) {
            $sql .= $wpdb->prepare(" AND e.student_id = %s", $student_id);
        }

        if (!empty($class_id)) {
            $sql .= $wpdb->prepare(" AND e.class_id = %d", $class_id);
        }

        if (!empty($section_id)) {
            $sql .= $wpdb->prepare(" AND e.section_id = %d", $section_id);
        }

        if (!empty($accountability_id)) {
            $sql .= $wpdb->prepare(" AND e.accountability_id = %d", $accountability_id);
        }

        if (!empty($roll)) {
            $sql .= $wpdb->prepare(" AND e.roll = %s", $roll);
        }

        if (!empty($search)) {
            $sql .= $wpdb->prepare(" AND (e.enroll_code LIKE %s OR u.display_name LIKE %s)", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
        }

        $result = $wpdb->get_results($sql);

        return $result;
    }

    /** Get an enrollment by ID */
    public function single_by($args)
    {
        global $wpdb;

        $result = $this->list($args);

        if (count($result) > 0) {
            return $result[0];
        }

        return $result;
    }

    /** Get an enrollment by ID */
    public function single($enroll_id)
    {
        global $wpdb;

        $sql = "
             SELECT 
                 e.*, 
                 u.display_name AS student_name, 
                 c.name AS class_name, 
                 s.name AS section_name, 
                 a.name AS accountability_name
             FROM {$wpdb->prefix}{$this->enroll_table} e
             LEFT JOIN {$wpdb->prefix}{$this->users_table} u ON e.student_id = u.ID
             LEFT JOIN {$wpdb->prefix}{$this->class_table} c ON e.class_id = c.id
             LEFT JOIN {$wpdb->prefix}{$this->section_table} s ON e.section_id = s.id
             LEFT JOIN {$wpdb->prefix}{$this->accountability_table} a ON e.accountability_id = a.id
             WHERE e.enroll_id = %d
         ";

        $sql = $wpdb->prepare($sql, $enroll_id);

        $result = $wpdb->get_row($sql);

        return $result;
    }



    /** Insert a new enrollment */
    public function create($data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->enroll_table}",
            $data
        );

        if ($result) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /** Update an existing enrollment */
    public function update($enroll_id, $data)
    {
        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->enroll_table}",
            $data,
            ['enroll_id' => $enroll_id]
        );

        return $result !== false;
    }

    /** Delete an enrollment */
    public function delete($enroll_id)
    {
        global $wpdb;

        $result = $wpdb->delete(
            "{$wpdb->prefix}{$this->enroll_table}",
            ['enroll_id' => $enroll_id]
        );

        return $result !== false;
    }
}
