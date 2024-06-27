<?php
class RunHomeworkRepo
{
    protected $homework_table = 'sakolawp_homework';
    protected $deliveries_table = 'sakolawp_deliveries';
    protected $users_table = 'users';
    protected $questions_repo;
    protected $delivery_repo = null;

    public function __construct()
    {
        $this->questions_repo = new RunQuestionsRepo();
        // $this->delivery_repo = new RunDeliveryRepo();
    }

    function migrate($old_subject, $new_subject)
    {
        global $wpdb;

        $batch_size = 10; // Adjust the batch size as needed
        $offset = 0;

        do {
            $homeworks = $this->list(['subject_id' => $old_subject], $batch_size, $offset);

            foreach ($homeworks as $homework) {
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}{$this->homework_table} SET subject_id = %d WHERE homework_id = %d",
                        $new_subject,
                        $homework->homework_id
                    )
                );
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}{$this->deliveries_table} SET subject_id = %d WHERE homework_code = %d",
                        $new_subject,
                        $homework->homework_code
                    )
                );
            }

            $offset += $batch_size;

            // Free memory
            $wpdb->flush();
        } while (!empty($homeworks));
    }

    /** List Homeworks */
    function list($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';
        $subject_id = isset($args['subject_id']) ? $args['subject_id'] : '';
        $uploader_id = isset($args['uploader_id']) ? $args['uploader_id'] : '';
        $homework_code = isset($args['homework_code']) ? $args['homework_code'] : '';

        $sql = "SELECT h.*, COUNT(d.delivery_id) AS delivery_count, t.display_name as teacher_name
         FROM {$wpdb->prefix}{$this->homework_table} h
         LEFT JOIN {$wpdb->prefix}{$this->deliveries_table} d ON h.homework_code = d.homework_code
         LEFT JOIN {$wpdb->prefix}{$this->users_table} t ON h.uploader_id = t.ID
         WHERE 1=1"; // Start with a tautology

        // Add search condition
        if (!empty($search)) {
            $sql .= " AND h.title LIKE '%$search%'";
        }

        // Add subject_id condition
        if (!empty($subject_id)) {
            $sql .= " AND h.subject_id = '$subject_id'";
        }

        // Add uploader_id condition
        if (!empty($uploader_id)) {
            $sql .= " AND h.uploader_id = $uploader_id";
        }

        // Add homework_code condition
        if (!empty($homework_code)) {
            $sql .= " AND h.homework_code = '$homework_code'";
        }

        $sql .= " GROUP BY h.homework_id";

        $result = $wpdb->get_results($sql);

        // Fetch questions for each homework and append to the result
        foreach ($result as &$homework) {
            $homework->questions = $this->questions_repo->get_by_homework($homework->homework_id);
            $homework->allow_peer_review = $homework->allow_peer_review == '1' ? true : false;
        }

        return $result;
    }

    /** count homework */
    function count($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? $args['search'] : '';
        $subject_id = isset($args['subject_id']) ? $args['subject_id'] : '';
        $uploader_id = isset($args['uploader_id']) ? $args['uploader_id'] : '';
        $homework_code = isset($args['homework_code']) ? $args['homework_code'] : '';

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}{$this->homework_table} h WHERE 1=1"; // Start with a tautology

        // Add search condition
        if (!empty($search)) {
            $sql .= $wpdb->prepare(" AND h.title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        // Add subject_id condition
        if (!empty($subject_id)) {
            $sql .= $wpdb->prepare(" AND h.subject_id = %d", $subject_id);
        }

        // Add uploader_id condition
        if (!empty($uploader_id)) {
            $sql .= $wpdb->prepare(" AND h.uploader_id = %d", $uploader_id);
        }

        // Add homework_code condition
        if (!empty($homework_code)) {
            $sql .= $wpdb->prepare(" AND h.homework_code = %s", $homework_code);
        }

        $result = $wpdb->get_var($sql);

        // Ensure result is always an integer
        return (int) $result;
    }

    /** Read a single homework */
    function single($homework_id)
    {
        global $wpdb;

        $sql = "SELECT h.*,  COUNT(d.delivery_id) AS delivery_count, t.display_name as teacher_name 
         FROM {$wpdb->prefix}{$this->homework_table} h
         LEFT JOIN {$wpdb->prefix}{$this->deliveries_table} d ON h.homework_code = d.homework_code
         LEFT JOIN {$wpdb->prefix}{$this->users_table} t ON h.uploader_id = t.ID
         WHERE h.homework_id = '$homework_id'
         GROUP BY h.homework_id";

        $result = $wpdb->get_row($sql);

        if ($result) {
            $result->questions = $this->questions_repo->get_by_homework($homework_id);
            $result->allow_peer_review = $result->allow_peer_review  == '1' ? true : false;
        } else {
            error_log($wpdb->last_error);
        }

        return $result;
    }
    /** Read a single homework */
    function single_by_homework_code($homework_code)
    {
        global $wpdb;

        $sql = "SELECT h.*,  COUNT(d.delivery_id) AS delivery_count, t.display_name as teacher_name 
         FROM {$wpdb->prefix}{$this->homework_table} h
         LEFT JOIN {$wpdb->prefix}{$this->deliveries_table} d ON h.homework_code = d.homework_code
         LEFT JOIN {$wpdb->prefix}{$this->users_table} t ON h.uploader_id = t.ID
         WHERE h.homework_code = '$homework_code'
         GROUP BY h.homework_id";

        $result = $wpdb->get_row($sql);

        if ($result) {
            $result->questions = $this->questions_repo->get_by_homework($homework_code);
            $result->allow_peer_review = $result->allow_peer_review  == '1' ? true : false;
        } else {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new homework */
    function create($homework_data)
    {
        global $wpdb;

        // Extract questions from homework data
        $questions = isset($homework_data['questions']) ? $homework_data['questions'] : [];
        unset($homework_data['questions']);

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->homework_table}",
            $homework_data
        );

        if ($result) {
            $homework_id = $wpdb->insert_id;

            $this->questions_repo->bulk_update($homework_id, $questions);
        }

        return $result;
    }

    /** Update an existing homework */
    function update($homework_id, $homework_data)
    {
        global $wpdb;

        // Extract questions from homework data
        $questions = isset($homework_data['questions']) ? $homework_data['questions'] : [];
        unset($homework_data['questions']);

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->homework_table}",
            $homework_data,
            array('homework_id' => $homework_id)
        );

        if (isset($result)) {
            // error_log(print_r($questions, true));
            $this->questions_repo->bulk_update($homework_id, $questions);
        } else {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Delete a homework */
    function delete($homework_id)
    {
        global $wpdb;

        $questions = $this->questions_repo->get_by_homework($homework_id);

        foreach ($questions as $question) {
            $this->questions_repo->delete($question->question_id);
        }

        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->homework_table} WHERE homework_id = %d", $homework_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
