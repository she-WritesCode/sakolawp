<?php
defined('ABSPATH') || exit;

class SakolawpPeerReview
{

    private $peer_review_templates = [
        "prophetic_word" => SAKOLAWP_PLUGIN_DIR . '/templates/peer-reviews/prophetic_word_assessment.php',
        "bible_teaching" => SAKOLAWP_PLUGIN_DIR . '/templates/peer-reviews/biblical_teaching_assessment.php',
    ];

    public function __construct()
    {
        add_action('wp_ajax_sakolawp_peer_review_templates_select_options', array($this, 'peer_review_templates_select_options'));    // If called from admin panel
        add_action('wp_ajax_nopriv_sakolawp_peer_review_templates_select_options', array($this, 'peer_review_templates_select_options'));

        add_action('wp_ajax_sakolawp_peer_review_results', array($this, 'peer_review_results'));
        add_action('wp_ajax_nopriv_sakolawp_peer_review_results', array($this, 'peer_review_results'));

        add_action('sakolawp_form_prophetic_word_assessment',  array($this, 'output_prophetic_word_assessment'));
        add_action('sakolawp_form_biblical_teaching_assessment',  array($this, 'output_biblical_teaching_assessment'));
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
            "SELECT pr.*, d.*, h.title 
         FROM $peer_reviews_table pr 
         JOIN $deliveries_table d ON pr.delivery_id = d.delivery_id 
         JOIN $homework_table h ON pr.homework_id = h.homework_id 
         WHERE pr.reviewer_id = %d AND pr.homework_id = %d",
            $current_user_id,
            $homework_id
        ));

        if (!$peer_reviews) {
            wp_send_json_error('No peer reviews found for the current user and specified homework.');
        }

        $results = [];
        foreach ($peer_reviews as $peer_review) {
            $results[] = json_decode($peer_review->assessment);
        }

        wp_send_json_success($results);
    }



    function output_prophetic_word_assessment()
    {
        require_once plugin_dir_path(__FILE__) . 'prophetic_word_assessment.php';
        require_once plugin_dir_path(__FILE__) . 'assessment_form.php';
    }


    function output_biblical_teaching_assessment()
    {
        require_once plugin_dir_path(__FILE__) . 'biblical_teaching_assessment.php';
        require_once plugin_dir_path(__FILE__) . 'assessment_form.php';
    }
}
