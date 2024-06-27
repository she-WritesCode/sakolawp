<?php
class RunPeerReviewRepo
{
    protected $homework_table = 'sakolawp_homework';
    protected $enroll_table = 'sakolawp_enroll';
    protected $deliveries_table = 'sakolawp_deliveries';
    protected $peer_reviews_table = 'sakolawp_peer_reviews';
    protected $users_table = 'users';
    // protected $subject_table = 'sakolawp_subject';
    protected $courses_repo = null;

    public function __construct()
    {
        $this->courses_repo = new RunCourseRepo();
    }



    /** List Peer reviews */
    function list($args = [])
    {
        global $wpdb;

        $peer_id = isset($args['peer_id']) ? $args['peer_id'] : '';
        $reviewer_id = isset($args['reviewer_id']) ? $args['reviewer_id'] : '';
        $homework_id = isset($args['homework_id']) ? $args['homework_id'] : '';
        $delivery_id = isset($args['delivery_id']) ? $args['delivery_id'] : '';
        $section_id = isset($args['section_id']) ? $args['section_id'] : '';
        $class_id = isset($args['class_id']) ? $args['class_id'] : '';

        $sql = "SELECT 
                pr.*, 
                d.delivery_id, 
                d.responses AS delivery_responses, 
                h.title AS homework_title, 
                h.peer_review_template, 
                reviewer.display_name as reviewer_name, 
                peer.display_name as peer_name
            FROM {$wpdb->prefix}{$this->peer_reviews_table} pr
			LEFT JOIN {$wpdb->prefix}{$this->deliveries_table} d ON pr.delivery_id = d.delivery_id 
			LEFT JOIN {$wpdb->prefix}{$this->homework_table} h ON pr.homework_id = h.homework_id 
            LEFT JOIN {$wpdb->prefix}{$this->users_table} reviewer ON pr.reviewer_id = reviewer.ID
            LEFT JOIN {$wpdb->prefix}{$this->users_table} peer ON pr.peer_id = peer.ID
            WHERE 1=1"; // Start with a tautology

        // Add peer_id condition
        if (!empty($peer_id)) {
            $sql .= $wpdb->prepare(" AND pr.peer_id = %d", $peer_id);
        }

        // Add reviewer_id condition
        if (!empty($reviewer_id)) {
            $sql .= $wpdb->prepare(" AND pr.reviewer_id = %d", $reviewer_id);
        }

        // Add homework_id condition
        if (!empty($homework_id)) {
            $sql .= $wpdb->prepare(" AND pr.homework_id = %s", $homework_id);
        }
        // Add delivery_id condition
        if (!empty($delivery_id)) {
            $sql .= $wpdb->prepare(" AND pr.delivery_id = %s", $delivery_id);
        }

        // Add section_id condition
        if (!empty($section_id)) {
            $sql .= $wpdb->prepare(" AND pr.section_id = %s", $section_id);
        }

        // Add class_id condition
        if (!empty($class_id)) {
            $sql .= $wpdb->prepare(" AND pr.class_id = %s", $class_id);
        }

        $sql .= " GROUP BY pr.peer_review_id";

        $result = $wpdb->get_results($sql);

        if ($result) {
            foreach ($result as $key => $row) {
                $result[$key]->delivery_responses = json_decode($row->delivery_responses);
            }
        }

        return $result;
    }

    /** Read a single delivery */
    function single($peer_review_id)
    {
        global $wpdb;

        $sql = "SELECT 
            pr.*, 
            d.delivery_id, 
            d.responses AS delivery_responses, 
            h.title AS homework_title, 
            h.peer_review_template, 
            reviewer.display_name as reviewer_name, 
            peer.display_name as peer_name
        FROM {$wpdb->prefix}{$this->peer_reviews_table} pr
        LEFT JOIN {$wpdb->prefix}{$this->deliveries_table} d ON pr.delivery_id = d.delivery_id 
        LEFT JOIN {$wpdb->prefix}{$this->homework_table} h ON pr.homework_id = h.homework_id 
        LEFT JOIN {$wpdb->prefix}{$this->users_table} reviewer ON pr.reviewer_id = reviewer.ID
        LEFT JOIN {$wpdb->prefix}{$this->users_table} peer ON pr.peer_id = peer.ID
        WHERE pr.peer_review_id = '$peer_review_id'
        GROUP BY pr.peer_review_id";

        $result = $wpdb->get_row($sql);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        if ($result) {
            // get courses 
            $result->course = $this->courses_repo->single($result->subject_id);
        }

        return $result;
    }

    /** Create a new delivery */
    function create($delivery_data)
    {
        global $wpdb;

        $result = skwp_insert_or_update_record(
            "{$wpdb->prefix}{$this->peer_reviews_table}",
            $delivery_data,
            ['delivery_id', 'peer_id', 'reviewer_id'],
            'peer_review_id'
        );
        return $result;
    }

    /** Update an existing homework */
    function update($peer_review_id, $homework_data)
    {
        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->peer_reviews_table}",
            $homework_data,
            array('peer_review_id' => $peer_review_id)
        );

        if (!($result)) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Delete a homework */
    function delete($peer_review_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->peer_reviews_table} WHERE peer_review_id = %pr", $peer_review_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
