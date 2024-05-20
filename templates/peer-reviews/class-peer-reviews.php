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

        add_action('sakolawp_form_prophetic_word_assessment',  array($this, 'output_prophetic_word_assessment'));
        add_action('sakolawp_form_biblical_teaching_assessment',  array($this, 'output_biblical_teaching_assessment'));
    }

    public function peer_review_templates_select_options()
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
