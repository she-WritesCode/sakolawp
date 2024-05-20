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
    }

    public function peer_review_templates_select_options()
    {
        $peer_review_templates_select_options = array_keys($this->peer_review_templates);

        echo '<option value="">Select</option>';
        foreach ($peer_review_templates_select_options as $row) {
            echo '<option value="' . $row . '">' . $row . '</option>';
        }

        exit();
    }
}
