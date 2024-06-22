<?php
/*
$course_repo = new RunCourseRepo();
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

$courses = $course_repo->list($meta_query, $search);

echo '<pre>';
print_r($courses);
echo '</pre>';
 */
class RunCourseRepo
{
    protected $post_type = 'sakolawp-course';
    protected $homework_repo = null;
    protected $lesson_repo = null;

    public function __construct()
    {
        $this->homework_repo = new RunHomeworkRepo();
        $this->lesson_repo = new RunLessonRepo();
    }

    /** List Courses by meta query and search string */
    public function list($meta_query = [], $search = '', $ids = [])
    {
        $args = array(
            'post_type'      => $this->post_type,
            'posts_per_page' => -1,
            'meta_query'     => $meta_query,
        );

        if (!empty($ids)) {
            $args['post__in'] = $ids;
        }

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        $courses = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;
                $courses[] = array(
                    'ID'        => get_the_ID(),
                    'title'     => get_the_title(),
                    'content'   => get_the_content(),
                    'excerpt'   => get_the_excerpt(),
                    'meta'      => get_post_meta(get_the_ID()),
                    'permalink' => get_permalink(),
                    'date'      => get_the_date(),
                    'author'    => get_the_author(),
                    'homework_count' => $this->homework_repo->count(['subject_id' => get_the_ID()]),
                    'lesson_count' => $this->lesson_repo->count_by_meta_query([['key' => 'sakolawp_subject_id', 'value' => (string)get_the_ID(), 'compare' => '=']]),
                );
            }
            wp_reset_postdata();
        }

        return $courses;
    }


    /** Count Courses by meta query */
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

    /** Create a new course */
    public function create($course_data)
    {
        $post_data = array(
            'post_title'   => $course_data['title'],
            'post_content' => $course_data['content'],
            'post_status'  => 'publish',
            'post_type'    => $this->post_type,
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            foreach ($course_data['meta'] as $meta_key => $meta_value) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }

        return $post_id;
    }

    /** Read a single course */
    public function single($course_id)
    {
        $post = get_post($course_id);

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
                'homework_count' => $this->homework_repo->count(['subject_id' => $post->ID]),
                'lesson_count' => $this->lesson_repo->count_by_meta_query([['key' => 'sakolawp_subject_id', 'value' => (string)$post->ID, 'compare' => '=']]),
            );
        }

        return null;
    }

    /** Update an existing course */
    public function update($course_id, $course_data)
    {
        $post_data = array(
            'ID'           => $course_id,
            'post_title'   => $course_data['title'],
            'post_content' => $course_data['content'],
        );

        $post_id = wp_update_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            foreach ($course_data['meta'] as $meta_key => $meta_value) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }

        return $post_id;
    }

    /** Delete an course */
    public function delete($course_id)
    {
        return wp_delete_post($course_id, true);
    }

    public function migrate()
    {
        global $wpdb;

        $batch_size = 2; // Adjust the batch size as needed
        $offset = 0;

        do {
            $subjects = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}sakolawp_subject LIMIT %d OFFSET %d",
                    $batch_size,
                    $offset
                )
            );

            if (empty($subjects)) {
                break;
            }

            foreach ($subjects as $subject) {
                // Process each subject
                $post_data = array(
                    'post_title'   => $subject->name,
                    'post_content' => '',
                    'post_type'    => $this->post_type,
                    'post_status'  => 'publish',
                );
                $post_id = wp_insert_post($post_data);

                // Migrate associated data
                $this->homework_repo->migrate($subject->subject_id, $post_id);
            }

            $offset += $batch_size;

            // Free memory
            $wpdb->flush();

            // delete subject
            $wpdb->delete($wpdb->prefix . 'sakolawp_subject', array('subject_id' => $subject->subject_id));
        } while (!empty($subjects));
    }
}
