<?php
class RunDeliveryRepo
{
    protected $homework_table = 'sakolawp_homework';
    protected $enroll_table = 'sakolawp_enroll';
    protected $deliveries_table = 'sakolawp_deliveries';
    protected $users_table = 'users';
    // protected $subject_table = 'sakolawp_subject';
    protected $courses_repo = null;

    public function __construct()
    {
        $this->courses_repo = new RunCourseRepo();
    }



    /** List Deliveries */
    function list($args = [])
    {
        global $wpdb;

        $subject_id = isset($args['subject_id']) ? $args['subject_id'] : '';
        $student_id = isset($args['student_id']) ? $args['student_id'] : '';
        $homework_code = isset($args['homework_code']) ? $args['homework_code'] : '';

        $sql = "SELECT d.*, st.display_name as student_name
            FROM {$wpdb->prefix}{$this->deliveries_table} d
            LEFT JOIN {$wpdb->prefix}{$this->users_table} st ON d.student_id = st.ID
            WHERE 1=1"; // Start with a tautology

        // Add subject_id condition
        if (!empty($subject_id)) {
            $sql .= $wpdb->prepare(" AND d.subject_id = %d", $subject_id);
        }

        // Add student_id condition
        if (!empty($student_id)) {
            $sql .= $wpdb->prepare(" AND d.student_id = %d", $student_id);
        }

        // Add homework_code condition
        if (!empty($homework_code)) {
            $sql .= $wpdb->prepare(" AND d.homework_code = %s", $homework_code);
        }

        $sql .= " GROUP BY d.delivery_id";

        $result = $wpdb->get_results($sql);

        if ($result) {
            // get courses 
            foreach ($result as $key => $row) {
                $result[$key]->course = $this->courses_repo->single($row->subject_id);
                $result[$key]->responses = json_decode($row->responses);
            }
        }

        return $result;
    }

    function peer_reviews($args = [])
    {
        global $wpdb;

        $section_id = isset($args['section_id']) ? $args['section_id'] : '';
        $reviewer_id = isset($args['reviewer_id']) ? $args['reviewer_id'] : '';
        $class_id = isset($args['class_id']) ? $args['class_id'] : '';
        $interval_in_days = isset($args['interval_in_days']) ? $args['interval_in_days'] : '';

        $sql = "SELECT d.*, h.title as homework_title, h.section_id, h.homework_code, s.name as subject_name
		FROM {$wpdb->prefix}{$this->homework_table} h
		LEFT JOIN {$wpdb->prefix}{$this->deliveries_table} d ON h.homework_code = d.homework_code
		LEFT JOIN {$wpdb->prefix}{$this->enroll_table} e ON d.reviewer_id = e.reviewer_id
		WHERE h.allow_peer_review = 1  
		AND h.peer_review_who = 'student'";



        // Add reviewer_id condition
        if (!empty($reviewer_id)) {
            $sql .= $wpdb->prepare(" AND d.reviewer_id != %s", $reviewer_id);
        }

        // Add section_id condition
        if (!empty($section_id)) {
            $sql .= $wpdb->prepare(" AND e.section_id = %s", $section_id);
        }

        // Add class_id condition
        if (!empty($class_id)) {
            $sql .= $wpdb->prepare(" AND d.class_id = %s", $class_id);
        }

        // Add interval_in_days condition
        if (!empty($interval_in_days)) {
            $sql .= $wpdb->prepare(" AND created_at >= NOW() - INTERVAL %d DAY", $interval_in_days);
        }

        // $sql .= " GROUP BY d.delivery_id";
        $sql .= " ORDER BY h.created_at DESC, d.date DESC;";

        $result = $wpdb->get_results($sql);

        if ($result) {
            // get courses 
            foreach ($result as $key => $row) {
                $result[$key]->course = $this->courses_repo->single($row->subject_id);
                $result[$key]->responses = json_decode($row->responses);
            }
        }

        return $result;
    }

    /** Read a single delivery */
    function single($delivery_id)
    {
        global $wpdb;

        $sql = "SELECT d.*, st.display_name as student_name
            FROM {$wpdb->prefix}{$this->deliveries_table} d
            LEFT JOIN {$wpdb->prefix}{$this->users_table} st ON d.student_id = st.ID
         WHERE d.delivery_id = '$delivery_id'
         GROUP BY d.delivery_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        if ($result) {
            // get courses 
            $result->course = $this->courses_repo->single($result->subject_id);
            $result->responses = json_decode($result->responses);
        }

        return $result;
    }

    /** Create a new delivery */
    function create($delivery_data)
    {
        global $wpdb;

        $result = skwp_insert_or_update_record(
            "{$wpdb->prefix}{$this->deliveries_table}",
            $delivery_data,
            ['homework_code', 'student_id'],
            'delivery_id'
        );
        return $result;
    }

    /** Update an existing homework */
    function update($delivery_id, $homework_data)
    {
        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->deliveries_table}",
            $homework_data,
            array('delivery_id' => $delivery_id)
        );

        if (!($result)) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Delete a homework */
    function delete($delivery_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->deliveries_table} WHERE delivery_id = %d", $delivery_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
