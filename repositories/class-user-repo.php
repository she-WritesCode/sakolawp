<?php
class RunUserRepo
{
    protected $users_table = 'users';

    /** List Users */
    function list($args = [])
    {
        global $wpdb;

        $search = isset($args['search']) ? '*' .  $args['search'] . '*' : '';
        $role = isset($args['role']) ? $args['role'] : '';

        $args = array(
            'role' => $role,
            'search' => $search,
            'search_columns' => ['user_login', 'user_email', 'user_nicename', 'user_display_name']
        );

        $result = get_users($args);
        return $result;
    }

    /** Read a single user */
    function single($user_id)
    {
        global $wpdb;

        $result = get_userdata($user_id);

        if (!$result) {
            error_log($wpdb->last_error);
        }

        return $result;
    }

    /** Create a new user */
    function create($user_data)
    {
        global $wpdb;
        $result = $wpdb->insert(
            "{$wpdb->prefix}sakolawp_users",
            $user_data
        );
        return $result;
    }

    /** Update an existing user */
    function update($user_id, $user_data)
    {
        global $wpdb;
        $result = $wpdb->update(
            "{$wpdb->prefix}sakolawp_users",
            $user_data,
            array('user_id' => $user_id)
        );
        return $result;
    }

    /** Delete a user */
    function delete($user_id)
    {
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}sakolawp_users WHERE user_id = %d", $user_id);
        $result = $wpdb->query($sql);

        return $result;
    }
}
