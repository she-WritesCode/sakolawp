<?php
class RunHomeworkRepo
{
    protected $homework_table = 'sakolawp_homework';
    protected $deliveries_table = 'sakolawp_deliveries';
    protected $users_table = 'users';
    protected $questions_repo;

    public function __construct()
    {
        $this->questions_repo = new RunQuestionsRepo();
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
            $sql .= " AND h.homework_code = $homework_code";
        }

        $sql .= " GROUP BY h.homework_id";

        $result = $wpdb->get_results($sql);

        // Fetch questions for each homework and append to the result
        foreach ($result as &$homework) {
            $homework->questions = $this->questions_repo->get_by_homework($homework->homework_id);
        }

        return $result;
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

        error_log(print_r($questions, true));

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->homework_table}",
            $homework_data
        );

        if ($result) {
            $homework_id = $wpdb->insert_id;

            foreach ($questions as $question) {
                $question['homework_id'] = $homework_id;
                $this->questions_repo->create($question);
            }
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
            foreach ($questions as $question) {
                if (strlen($question['question_id']) !== 2) {
                    error_log("Existing ===>");
                    error_log(print_r($question, true));
                    $this->questions_repo->update($question['question_id'], $question);
                } else {
                    error_log("New ===>");
                    error_log(print_r($question, true));
                    $question['homework_id'] = $homework_id;
                    $this->questions_repo->create($question);
                }
            }
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
