<?php
/*
$event_repo = new SakolawpEventRepo();
$meta_query = array(
    'relation' => 'AND',
    array(
        'key'     => 'your_meta_key_1',
        'value'   => 'your_meta_value_1',
        'compare' => '='
    ),
    array(
        'key'     => 'your_meta_key_2',
        'value'   => 'your_meta_value_2',
        'compare' => '='
    ),
    // Add more conditions as needed
);

$search = 'search term';

$events = $event_repo->list($meta_query, $search);

echo '<pre>';
print_r($events);
echo '</pre>';
 */
class RunEventRepo
{
    protected $post_type = 'sakolawp-event';

    /** List Events by meta query and search string */
    public function list($meta_query = [], $search = '')
    {
        $args = array(
            'post_type'      => $this->post_type,
            'posts_per_page' => -1,
            'meta_query'     => $meta_query,
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        $events = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $events[] = array(
                    'ID'        => get_the_ID(),
                    'title'     => get_the_title(),
                    'content'   => get_the_content(),
                    'excerpt'   => get_the_excerpt(),
                    'meta'      => get_post_meta(get_the_ID()),
                    'permalink' => get_permalink(),
                    'date'      => get_the_date(),
                    'author'    => get_the_author(),
                );
            }
            wp_reset_postdata();
        }

        return $events;
    }


    /** Count Events by meta query */
    public function count_by_meta_query($meta_query)
    {
        $args = array(
            'post_type'      => $this->post_type,
            'posts_per_page' => -1,
            'meta_query'     => $meta_query,
            'fields'         => 'ids', // We only need the IDs for counting
        );

        $query = new WP_Query($args);
        return $query->found_posts;
    }

    /** Create a new event */
    public function create($event_data)
    {
        $post_data = array(
            'post_title'   => $event_data['title'],
            'post_content' => $event_data['content'],
            'post_status'  => 'publish',
            'post_type'    => $this->post_type,
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            foreach ($event_data['meta'] as $meta_key => $meta_value) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }

        return $post_id;
    }

    /** Read a single event */
    public function single($event_id)
    {
        $post = get_post($event_id);

        if ($post && $post->post_type === $this->post_type) {
            return array(
                'ID'        => $post->ID,
                'title'     => $post->post_title,
                'content'   => $post->post_content,
                'excerpt'   => $post->post_excerpt,
                'meta'      => get_post_meta($post->ID),
                'permalink' => get_permalink($post->ID),
                'date'      => $post->post_date,
                'author'    => get_the_author_meta('display_name', $post->post_author),
            );
        }

        return null;
    }

    /** Update an existing event */
    public function update($event_id, $event_data)
    {
        $post_data = array(
            'ID'           => $event_id,
            'post_title'   => $event_data['title'],
            'post_content' => $event_data['content'],
        );

        $post_id = wp_update_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            foreach ($event_data['meta'] as $meta_key => $meta_value) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }

        return $post_id;
    }

    /** Delete an event */
    public function delete($event_id)
    {
        return wp_delete_post($event_id, true);
    }
}
