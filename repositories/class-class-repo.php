<?php
class RunClassRepo
{
    protected $class_table = 'sakolawp_class';
    protected $subject_table = 'sakolawp_subject';
    protected $class_subject_table = 'sakolawp_class_subject';
    protected $section_table = 'sakolawp_section';
    protected $accountability_table = 'sakolawp_accountability';
    protected $enroll_table = 'sakolawp_enroll';
    protected $homework_table = 'sakolawp_homework';
    protected $lesson_table = 'sakolawp_lessons';
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

        $eventRepo = new RunEventRepo();
        foreach ($result as $class) {
            $class->event_count = $eventRepo->count_by_meta_query([
                'relation' => 'AND',
                [
                    'key'     => 'class_id',
                    'value'   => $class->class_id,
                    'compare' => '='
                ],
            ]);
        }
        return $result;
    }

    /** List Class Subjects */
    function list_subjects($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';
        $class_id = isset($args['class_id']) ? $args['class_id'] : '0';

        $sql = "SELECT s.*, COUNT(h.homework_id) AS homework_count, COUNT(l.lesson_id) AS lesson_count, t.display_name as teacher_name
        FROM {$wpdb->prefix}{$this->subject_table} s
        JOIN {$wpdb->prefix}{$this->class_subject_table} cs ON s.subject_id = cs.subject_id
        LEFT JOIN {$wpdb->prefix}{$this->homework_table} h ON s.subject_id = h.subject_id
        LEFT JOIN {$wpdb->prefix}{$this->lesson_table} l ON s.subject_id = l.subject_id
        LEFT JOIN {$wpdb->prefix}{$this->users_table} t ON s.teacher_id = t.ID
        WHERE s.name LIKE '%$search%'
        AND cs.class_id = $class_id
        GROUP BY s.subject_id";

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

        $eventRepo = new RunEventRepo();
        $result->event_count = $eventRepo->count_by_meta_query([
            'relation' => 'AND',
            [
                'key'     => 'class_id',
                'value'   => $result->class_id,
                'compare' => '='
            ],
        ]);
        $subjects = $this->list_subjects(['class_id' => $result->class_id]);
        $result->subjects = array_map(function ($subject) {
            return $subject->subject_id;
        }, $subjects);

        return $result;
    }

    /** Create a new class */
    function create($class_data)
    {
        global $wpdb;

        $subjects = $class_data['subjects'];
        unset($class_data['subjects']);

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->class_table}",
            $class_data
        );

        if ($result) {
            $class_id = $wpdb->insert_id;
            foreach ($subjects as $subject_id) {
                skwp_insert_or_update_record(
                    "{$wpdb->prefix}{$this->class_subject_table}",
                    array(
                        'class_id' => $class_id,
                        'subject_id' => $subject_id
                    ),
                    ['class_id', 'subject_id'],
                    'id'
                );
            }
        }
        return $result;
    }

    /** Update an existing class */
    function update($class_id, $class_data)
    {
        global $wpdb;

        $subjects = $class_data['subjects'];
        unset($class_data['subjects']);

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->class_table}",
            $class_data,
            array('class_id' => $class_id)
        );

        if ($result) {
            foreach ($subjects as $subject_id) {
                skwp_insert_or_update_record(
                    "{$wpdb->prefix}{$this->class_subject_table}",
                    array(
                        'class_id' => $class_id,
                        'subject_id' => $subject_id
                    ),
                    ['class_id', 'subject_id'],
                    'id'
                );
            }
        }
        return $result;
    }

    /** Delete a class */
    function delete($class_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->class_table} WHERE class_id = %d", $class_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
