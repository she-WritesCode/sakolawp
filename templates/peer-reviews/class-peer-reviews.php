<?php
defined('ABSPATH') || exit;

class SakolawpPeerReview
{

    private $peer_review_templates = [
        "prophetic_word" => SAKOLAWP_PLUGIN_DIR . '/templates/peer-reviews/prophetic_word_assessment.php',
        "bible_teaching" => SAKOLAWP_PLUGIN_DIR . '/templates/peer-reviews/bible_teaching_assessment.php',
    ];

    public function __construct()
    {
        add_action('wp_ajax_sakolawp_peer_review_templates_select_options', array($this, 'peer_review_templates_select_options'));    // If called from admin panel
        add_action('wp_ajax_nopriv_sakolawp_peer_review_templates_select_options', array($this, 'peer_review_templates_select_options'));

        add_action('wp_ajax_sakolawp_peer_review_results', array($this, 'peer_review_results'));
        add_action('wp_ajax_nopriv_sakolawp_peer_review_results', array($this, 'peer_review_results'));

        add_action('sakolawp_form_prophetic_word_assessment',  array($this, 'output_prophetic_word_assessment'));
        add_action('sakolawp_form_bible_teaching_assessment',  array($this, 'output_bible_teaching_assessment'));
    }

    function peer_review_templates_select_options()
    {
        $peer_review_templates_select_options = array_keys($this->peer_review_templates);
        $selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : NULL;

        echo '<option value="">Select</option>';
        foreach ($peer_review_templates_select_options as $row) {
            $isSelected = $row == $selected ? 'selected' : '';
            echo '<option ' . $isSelected . ' value="' . $row . '">' . $row . '</option>';
        }

        exit();
    }

    function peer_review_results()
    {
        // Check if the user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in.');
        }

        // Get current user ID
        $current_user_id = isset($_REQUEST['student_id']) ? (int)sanitize_text_field($_REQUEST['student_id']) : get_current_user_id();

        // Get homework_code from GET request
        $homework_code = isset($_REQUEST['homework_code']) ? sanitize_text_field($_REQUEST['homework_code']) : '';

        if (empty($homework_code)) {
            wp_send_json_error('No homework code provided.');
        }

        global $wpdb;
        $peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';
        $homework_table = $wpdb->prefix . 'sakolawp_homework';
        $deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';

        // Get homework ID from homework_code
        $homework = $wpdb->get_row($wpdb->prepare(
            "SELECT homework_id FROM $homework_table WHERE homework_code = %s",
            $homework_code
        ));

        if (!$homework) {
            wp_send_json_error('Invalid homework code.');
        }

        $homework_id = $homework->homework_id;

        // Get peer reviews for the current user and the specific homework
        $peer_reviews = $wpdb->get_results($wpdb->prepare(
            "SELECT pr.*, d.*, h.title, h.peer_review_template
                FROM $peer_reviews_table pr 
                JOIN $deliveries_table d ON pr.delivery_id = d.delivery_id 
                JOIN $homework_table h ON pr.homework_id = h.homework_id 
                WHERE pr.peer_id = %d AND pr.homework_id = %d",
            $current_user_id,
            $homework_id
        ));

        if (!$peer_reviews) {
            wp_send_json_error('No peer reviews found for the current user and specified homework.');
        }

        $responses = [];
        foreach ($peer_reviews as $peer_review) {
            $responses[] = json_decode($peer_review->assessment, true); // Decode JSON as associative array
        }

        try {
            require_once plugin_dir_path(__FILE__) . $peer_review->peer_review_template . '_assessment.php';

            $dataSets = [];
            $labels = [];
            $summary = [];
            $totalScores = [];
            $totalCounts = [];

            foreach ($form['questions'] as $question) {
                $labels[] = strip_tags($question['question']);
                $totalScores[$question['question_id']] = 0;
                $totalCounts[$question['question_id']] = 0;
            }

            foreach ($responses as $response) {
                $dataPoints = [];
                $summaryItem = [];

                foreach ($form['questions'] as $question) {
                    $questionId = $question['question_id'];
                    $answer = $response[$questionId];

                    if ($question['type'] === 'linear-scale') {
                        $points = (float)$answer / $question['expected_points'] * 100;
                        $dataPoints[] = $points;
                        $summaryItem[] = strip_tags($question['question']) . ": " . $answer;
                        $totalScores[$questionId] += (float)$answer;
                        $totalCounts[$questionId] += 1;
                    } else if ($question['type'] === 'radio') {
                        $option = array_filter($question['options'], function ($opt) use ($answer) {
                            return $opt['value'] === $answer;
                        });
                        $option = reset($option);
                        $points = $option ? ($option['points'] / $question['expected_points'] * 100) : 0;
                        $dataPoints[] = $points;
                        $summaryItem[] = strip_tags($question['question']) . ": " . $answer;
                        if ($option) {
                            $totalScores[$questionId] += $option['points'];
                            $totalCounts[$questionId] += 1;
                        }
                    }
                }

                $dataSets[] = $dataPoints;
                $summary[] = $summaryItem;
            }

            // Calculate the mean scores
            $meanScores = [];
            foreach ($totalScores as $questionId => $totalScore) {
                $meanScore = $totalScore / $totalCounts[$questionId];
                $meanScores[$questionId] = $meanScore;
            }

            wp_send_json_success([
                'labels' => $labels,
                'dataSets' => $dataSets,
                'summary' => $summary,
                'meanScores' => $meanScores
            ]);
        } catch (Throwable $th) {
            error_log($th->getMessage());
            wp_send_json_error('An error occurred while processing the peer reviews.');
        }
    }

    function output_prophetic_word_assessment()
    {
        require_once plugin_dir_path(__FILE__) . 'prophetic_word_assessment.php';
        require_once plugin_dir_path(__FILE__) . 'assessment_form.php';
    }

    function output_bible_teaching_assessment()
    {
        require_once plugin_dir_path(__FILE__) . 'bible_teaching_assessment.php';
        require_once plugin_dir_path(__FILE__) . 'assessment_form.php';
    }
}
