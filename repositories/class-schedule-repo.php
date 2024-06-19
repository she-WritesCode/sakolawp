<?php

class RunClassScheduleRepo
{
    protected $class_schedule_table = 'sakolawp_class_schedule';
    // protected $class_table = 'sakolawp_class';
    // protected $subject_table = 'sakolawp_subject';
    // protected $class_subject_table = 'sakolawp_class_subject';
    // protected $section_table = 'sakolawp_section';
    // protected $accountability_table = 'sakolawp_accountability';
    // protected $enroll_table = 'sakolawp_enroll';
    // protected $homework_table = 'sakolawp_homework';
    // protected $lesson_table = 'sakolawp_lessons';
    // protected $users_table = 'users';

    /** List Schedules */
    public function list($args = [])
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}{$this->class_schedule_table} WHERE 1=1";

        if (isset($args['subject_id'])) {
            $sql .= $wpdb->prepare(" AND subject_id = %d", $args['subject_id']);
        }

        if (isset($args['class_id'])) {
            $sql .= $wpdb->prepare(" AND class_id = %d", $args['class_id']);
        }

        if (isset($args['content_type'])) {
            $sql .= $wpdb->prepare(" AND content_type = %s", $args['content_type']);
        }

        error_log("RunClassScheduleRepo =>" . $sql);

        $results = $wpdb->get_results($sql);
        return $results;
    }

    /** Get a single schedule */
    public function single($id)
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->class_schedule_table} WHERE id = %d", $id);
        $result = $wpdb->get_row($sql);
        return $result;
    }

    /** Create a new schedule */
    public function create($data_array)
    {
        global $wpdb;

        $results = [];

        foreach ($data_array as $data) {
            $result[] = skwp_insert_or_update_record(
                "{$wpdb->prefix}{$this->class_schedule_table}",
                $data,
                ['content_type', 'content_id', 'subject_id', 'class_id']
            );
        }

        return $results;
    }

    /** Update an existing schedule */
    public function update($id, $data)
    {
        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->class_schedule_table}",
            $data,
            ['id' => $id],
        );

        if ($result === false) {
            return $wpdb->last_error;
        }

        return true;
    }

    /** Delete a schedule */
    public function delete($id)
    {
        global $wpdb;

        $result = $wpdb->delete(
            "{$wpdb->prefix}{$this->class_schedule_table}",
            ['id' => $id],
        );

        if ($result === false) {
            return $wpdb->last_error;
        }

        return true;
    }
}
