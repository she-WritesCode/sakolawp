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


        add_action('admin_post_nopriv_submit_peer_review', array($this, 'submit_peer_review'));
        add_action('admin_post_submit_peer_review', array($this, 'submit_peer_review'));
    }

    function peer_review_templates_select_options()
    {
        $peer_review_templates_select_options = array_keys($this->peer_review_templates);
        $selected = $_REQUEST['selected'];

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
        $current_user_id = get_current_user_id();

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
            // require_once $peer_review_templates[$peer_review->peer_review_template];
            require_once plugin_dir_path(__FILE__) . $peer_review->peer_review_template . '_assessment.php';

            $dataSets = [];
            $labels = [];
            $summary = [];
            $totalScores = [];
            $totalCounts = [];

            foreach ($form['questions'] as $question) {
                $labels[] = $question['question'];
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
                        $summaryItem[] = $question['question'] . ": " . $answer;
                        $totalScores[$questionId] += (float)$answer;
                        $totalCounts[$questionId] += 1;
                    } else if ($question['type'] === 'radio') {
                        $option = array_filter($question['options'], function ($opt) use ($answer) {
                            return $opt['value'] === $answer;
                        });
                        $option = reset($option);
                        $points = $option ? ($option['points'] / $question['expected_points'] * 100) : 0;
                        $dataPoints[] = $points;
                        $summaryItem[] = $question['question'] . ": " . $answer;
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

    function submit_peer_review()
    {
        global $wpdb;

        $homework_table = $wpdb->prefix . 'sakolawp_homework';
        $deliveries_table = $wpdb->prefix . 'sakolawp_deliveries';
        $peer_reviews_table = $wpdb->prefix . 'sakolawp_peer_reviews';

        if (isset($_POST['submit'])) {
            error_log("We submitted " . json_encode($_POST));
            $homework_act = $_POST['action'];

            if ($homework_act == "add_peer_review") {
                $_POST     = array_map('stripslashes_deep', $_POST);
                $date   =  date("Y-m-d H:i:s");;
                $delivery_id =  sakolawp_sanitize_html($_POST['delivery_id']);
                $homework_id = sakolawp_sanitize_html($_POST['homework_id']);
                $peer_id = sanitize_text_field($_POST['peer_id']);
                $reviewer_id = sanitize_text_field($_POST['reviewer_id']);
                $class_id = sanitize_text_field($_POST['class_id']);
                $section_id    = sanitize_text_field($_POST['section_id']);
                $accountability_id =  sakolawp_sanitize_html($_POST['accountability_id']);
                $subject_id = sakolawp_sanitize_html($_POST['subject_id']);
                $assessment = array_map('stripslashes_deep', $_POST['assessment']);
                $reviewer_comment = sakolawp_sanitize_html($_POST['reviewer_comment']);
                $reviewer_type = sanitize_text_field($_POST['reviewer_type']);

                $current_delivery = $wpdb->get_row("SELECT * 
                FROM $deliveries_table d
                JOIN $homework_table h ON d.homework_code = h.homework_code
                WHERE d.delivery_id = '$delivery_id';", ARRAY_A);

                require_once plugin_dir_path(__FILE__) . '../peer-reviews/' . $current_delivery['peer_review_template'] . '_assessment.php';
                $mark =  calculate_assessment_total_score($assessment, $form); // form is gotten from the require once file


                skwp_insert_or_update_record(
                    $peer_reviews_table,
                    [
                        'date' => $date,
                        'delivery_id' => $delivery_id,
                        'homework_id' => $homework_id,
                        'peer_id' => $peer_id,
                        'reviewer_id' => $reviewer_id,
                        'class_id' => $class_id,
                        'section_id' => $section_id,
                        'accountability_id' => $accountability_id,
                        'subject_id' => $subject_id,
                        'assessment' => json_encode($assessment),
                        'mark' => $mark,
                        'reviewer_comment' => $reviewer_comment,
                        'reviewer_type' => $reviewer_type,
                    ],
                    ['delivery_id', 'reviewer_id'],
                    "peer_review_id"
                );

                wp_redirect(add_query_arg(array('delivery_id' => $delivery_id), home_url('peer_review_room')));
                die;
            }
        }
    }
}
