<?php
class RunClassRepo
{
    protected $class_table = 'sakolawp_class';
    protected $class_subject_table = 'sakolawp_class_subject';
    protected $section_table = 'sakolawp_section';
    protected $accountability_table = 'sakolawp_accountability';
    protected $enroll_table = 'sakolawp_enroll';
    protected $homework_table = 'sakolawp_homework';
    protected $lesson_table = 'sakolawp_lessons';
    protected $users_table = 'users';
    protected $courses_repo = null;

    public function __construct()
    {
        $this->courses_repo = new RunCourseRepo();
    }

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
        $class_id = isset($args['class_id']) ? intval($args['class_id']) : 0;

        $sql = $wpdb->prepare("SELECT cs.subject_id FROM {$wpdb->prefix}{$this->class_subject_table} cs WHERE cs.class_id = %d", $class_id);

        $classSubjects = $wpdb->get_results($sql);
        $classSubjectsIds = array_map(function ($subject) {
            return $subject->subject_id;
        }, $classSubjects);

        $result = $this->courses_repo->list([], $search, $classSubjectsIds);

        $homeworkRepo = new RunHomeworkRepo();

        if ($result) {
            foreach ($result as &$course) {
                $course['homeworks'] = (array) $homeworkRepo->list(['subject_id' => $course['ID']]);
            }
        }
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
            return $subject['ID'];
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
            $wpdb->delete(
                "{$wpdb->prefix}{$this->class_subject_table}",
                [
                    'class_id' => $class_id,
                ]
            );
            foreach ($subjects as $subject_id) {
                // Delete from class_subject table
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
            $wpdb->delete(
                "{$wpdb->prefix}{$this->class_subject_table}",
                [
                    'class_id' => $class_id,
                ]
            );
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
