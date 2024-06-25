<?php
class RunQuestionsRepo
{
    protected $questions_table = 'sakolawp_homework_questions';
    protected $linear_scale_options_table = 'sakolawp_linear_scale_options';
    protected $linear_scale_labels_table = 'sakolawp_linear_scale_labels';
    protected $options_table = 'sakolawp_question_options';

    /** Update an existing questions */
    function bulk_update($homework_id, $questions_list)
    {
        $questions_list = array_map(function ($question) use ($homework_id) {
            $question['homework_id'] = $homework_id;
            return $question;
        }, $questions_list);

        $all_questions_ids =  array_map(function ($question) {
            return $question['question_id'];
        }, $questions_list);

        // Retrieve existing questions associated with the homework ID
        $existing_questions = $this->get_by_homework($homework_id);

        // IDs of existing questions
        $existing_question_ids = array_map(function ($question) {
            return $question->question_id;
        }, $existing_questions);

        // IDs of new questions
        $new_question_ids = array_filter(array_map(function ($question) use ($existing_question_ids) {
            return in_array($question['question_id'], $existing_question_ids) ? null : $question['question_id'];
        }, $questions_list));

        $new_question_ids = array_values(array_filter($new_question_ids)); // Reindex array to avoid gaps


        // Questions to be inserted
        $questions_to_insert = array_filter($questions_list, function ($question) use ($existing_question_ids) {
            return !in_array($question['question_id'], $existing_question_ids);
        });

        // Questions to be updated
        $questions_to_update = array_filter($questions_list, function ($question) use ($existing_question_ids) {
            return in_array($question['question_id'], $existing_question_ids);
        });

        // Delete questions not present in the new list
        $questions_to_delete = array_filter($existing_question_ids, function ($question_id) use ($all_questions_ids) {
            return !in_array($question_id, $all_questions_ids);
        });

        // error_log(print_r(["update" => $questions_to_update, "insert" => $questions_to_insert, "delete" => $questions_to_delete], true));

        $create_result = [];
        // Insert new questions
        foreach ($questions_to_insert as $question) {
            $id = $this->create($question);
            array_push($create_result, $id);
        }

        $update_result = [];
        // Update existing questions
        foreach ($questions_to_update as $question) {
            $id = $this->update($question['question_id'], $question);
            array_push($update_result, $id);
        }

        $delete_result = [];
        // Delete questions not present in the new list
        foreach ($questions_to_delete as $question_id) {
            $this->delete($question_id);
            array_push($delete_result, $id);
        }
        // error_log(print_r(["insert" => $create_result, "update" => $update_result, "delete" => $delete_result], true));

        return true;
    }

    /** Delete a question */
    function delete($question_id)
    {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}{$this->linear_scale_labels_table}",
            array('question_id' => $question_id)
        );

        $wpdb->delete(
            "{$wpdb->prefix}{$this->linear_scale_options_table}",
            array('question_id' => $question_id)
        );

        $wpdb->delete(
            "{$wpdb->prefix}{$this->options_table}",
            array('question_id' => $question_id)
        );

        $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}{$this->questions_table} WHERE question_id = %s", $question_id);
        $result = $wpdb->query($sql);

        return $result;
    }

    /** Helper method to insert a new question */
    private function create(array $question_data)
    {
        unset($question_data['regex']);
        global $wpdb;

        // Convert arrays to JSON strings
        if (isset($question_data['text_options'])) {
            $question_data['text_options'] = json_encode($question_data['text_options']);
        }
        // error_log('Required=>' . print_r($question_data['required'], true));

        // Extract linear scale options and labels
        unset($question_data['linear_scale_labels']);
        if (isset($question_data['linear_scale_options'])) {
            $question_data['linear_scale_options'] = json_encode($question_data['linear_scale_options']);
        } else {
            $question_data['linear_scale_options'] = json_encode([]);
        }
        // error_log(print_r($question_data, true));

        // Extract options
        $options = isset($question_data['options']) ? $question_data['options'] : null;
        unset($question_data['options']);
        unset($question_data['question_id']);

        if (isset($question_data['question_id'])) {
            unset($question_data['question_id']);
        }

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->questions_table}",
            $question_data
        );

        if ($wpdb->last_error) {
            // Log or handle insertion error
            error_log('Database error: ' . $wpdb->last_error);
            return false;
        }


        if ($result) {
            $question_id = $wpdb->insert_id;

            if ($options) {
                foreach ($options as $option) {
                    $option_data = [
                        'question_id' => $question_id,
                        'label' => !empty($option['label']) ? $option['label'] : $option['value'],
                        'value' => $option['value'],
                        'points' => isset($option['points']) ? $option['points'] : null
                    ];
                    $wpdb->insert(
                        "{$wpdb->prefix}{$this->options_table}",
                        $option_data
                    );
                }
            }
        }

        return $result;
    }

    /** Helper method to update an existing question */
    private function update($question_id, $question_data)
    {
        unset($question_data['regex']);
        global $wpdb;

        // Convert arrays to JSON strings
        if (isset($question_data['text_options'])) {
            $question_data['text_options'] = json_encode($question_data['text_options']);
        }

        // Convert arrays to JSON strings
        if (isset($question_data['linear_scale_options'])) {
            $question_data['linear_scale_options'] = json_encode($question_data['linear_scale_options']);
        }

        // Extract options
        $options = isset($question_data['options']) ? $question_data['options'] : null;
        unset($question_data['options']);

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->questions_table}",
            $question_data,
            array('question_id' => $question_id)
        );

        // if ($result) {

        // Delete existing options
        $wpdb->delete(
            "{$wpdb->prefix}{$this->options_table}",
            array('question_id' => $question_id)
        );

        // Insert updated options
        if ($options) {
            foreach ($options as $option) {
                $option_data = [
                    'question_id' => $question_id,
                    'label' => !empty($option['label']) ? $option['label'] : $option['value'],
                    'value' => $option['value'],
                    'points' => isset($option['points']) ? $option['points'] : null
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}{$this->options_table}",
                    $option_data
                );
            }
        }
        // }

        return $result;
    }

    /** Get questions by homework id */
    function get_by_homework($homework_id)
    {
        global $wpdb;

        $sql = "SELECT q.*
                FROM {$wpdb->prefix}{$this->questions_table} q
                WHERE q.homework_id = '$homework_id'";
        $questions = $wpdb->get_results($sql);

        foreach ($questions as $question) {
            if ($question->type == 'linear-scale') {
                $labels_sql = "SELECT scale_value, label
                               FROM {$wpdb->prefix}{$this->linear_scale_labels_table}
                               WHERE question_id = '{$question->question_id}'";
                // $question->linear_scale_labels = $wpdb->get_results($labels_sql);

                $options_sql = "SELECT min, max, step
                                FROM {$wpdb->prefix}{$this->linear_scale_options_table}
                                WHERE question_id = '{$question->question_id}'";
                // $question->linear_scale_options = $wpdb->get_row($options_sql);
            }

            $options_sql = "SELECT label, value, points
                            FROM {$wpdb->prefix}{$this->options_table}
                            WHERE question_id = '{$question->question_id}'";
            $question->options = $wpdb->get_results($options_sql);

            $question->text_options = json_decode($question->text_options);

            if (isset($question->linear_scale_options)) {
                $question->linear_scale_options = json_decode($question->linear_scale_options);
                $question->linear_scale_options->min = (int)$question->linear_scale_options->min;
                $question->linear_scale_options->max = (int)$question->linear_scale_options->max;
                $question->linear_scale_options->step = (int)$question->linear_scale_options->step;
            }

            if (isset($question->text_options->add_word_count)) {
                $question->text_options->add_word_count = $question->text_options->add_word_count == 'true' ? true : false;
                $question->text_options->min = (int)$question->text_options->min;
                $question->text_options->max = (int)$question->text_options->max;
            }

            $question->multiple = $question->multiple == '1' ? true : false;
            $question->required = $question->required == '1' ? true : false;

            $question->score_percentage = (int)$question->score_percentage;
            $question->expected_points = (int)$question->expected_points;
        }

        return $questions;
    }
}
