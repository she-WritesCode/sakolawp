<?php

if (!defined('ABSPATH')) {
    die('You are not allowed to call this page directly.');
}

if (!class_exists('SakolaWpEndpoints')) {
    class SakolaWpEndpoints
    {
        private $namespace = 'sakolawp/v1';



        function __construct()
        {
            // include_once PVA_PLUGIN_DIR . 'includes/class-bank-transactions-repo.php';
            // include_once PVA_PLUGIN_DIR . 'includes/class-providus-api.php';

            add_action('rest_api_init', [$this, 'register_endpoints']);
        }

        function register_endpoints()
        {

            register_rest_route($this->namespace, 'assessments/pr-prophetic-word-assessment', [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'create_virtual_account'],
                    'permission_callback' => '__return_true',
                    // 'permission_callback' => function () {
                    //     return current_user_can('edit_post');
                    // }
                ],
            ]);
        }


        function save_peer_reviewed_prophetic_word_assessment(WP_REST_Request $request)
        {

            $result = [];

            return $result;
        }
    }
}
