<?php

class RunClassScheduleRepo
{
    protected $class_schedule_table = 'sakolawp_class_schedule';
    protected $classRepo = null;

    public function __construct()
    {
        $this->classRepo = new RunClassRepo();
    }

    /** List Schedules */
    public function list($args = [])
    {
        global $wpdb;

        $subject_id = isset($args['subject_id']) ? $args['subject_id'] : '';
        $content_type = isset($args['content_type']) ? $args['content_type'] : '';
        $content_id = isset($args['content_id']) ? $args['content_id'] : '';
        $class_id = isset($args['class_id']) ? $args['class_id'] : '';

        $sql = "SELECT * FROM {$wpdb->prefix}{$this->class_schedule_table} WHERE 1=1";

        if (!empty($subject_id)) {
            $sql .= $wpdb->prepare(" AND subject_id = %d", $subject_id);
        }

        if (!empty($content_type)) {
            $sql .= $wpdb->prepare(" AND content_type = %d", $content_type);
        }

        if (!empty($content_id)) {
            $sql .= $wpdb->prepare(" AND content_id = %s", $content_id);
        }

        if (!empty($class_id)) {
            $sql .= $wpdb->prepare(" AND class_id = %s", $class_id);
        }

        error_log("RunClassScheduleRepo =>" . $sql);

        $results = $wpdb->get_results($sql);

        foreach ($results as $key => $schedule) {
            $results[$key]->release_days = (float)$results[$key]->release_days;
            $results[$key]->deadline_days = (float)$results[$key]->deadline_days;
            $class = $this->classRepo->single($schedule->class_id);
            [
                'release_date' => $results[$key]->actual_release_date,
                'due_date' => $results[$key]->actual_deadline_date,
                'release_date_is_past' => $results[$key]->actual_release_date_is_past,
                'deadline_date_is_past' => $results[$key]->actual_deadline_date_is_past,
            ] = get_schedule_dates($schedule, $class->start_date, $class->drip_method);
        }

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

        foreach ($data_array as $key => $data) {
            $results[$key] = skwp_insert_or_update_record(
                "{$wpdb->prefix}{$this->class_schedule_table}",
                $data,
                ['content_type', 'content_id', 'subject_id', 'class_id']
            );
        }

        if (count($results) > 0) {
            $results_string = implode(',', $results);

            $schedules = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$this->class_schedule_table} WHERE id IN($results_string)", ARRAY_A);

            return $schedules;
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
