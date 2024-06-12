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

        // Retrieve existing questions associated with the homework ID
        $existing_questions = $this->get_by_homework($homework_id);

        // IDs of existing questions
        $existing_question_ids = array_map(function ($question) {
            return $question->question_id;
        }, $existing_questions);

        // IDs of new questions
        $new_question_ids = array_map(function ($question) {
            return str_starts_with($question['question_id'], 'q');
        }, $questions_list);

        // Questions to be inserted
        $questions_to_insert = array_filter($questions_list, function ($question) use ($existing_question_ids) {
            return !in_array($question['question_id'], $existing_question_ids);
        });

        // Questions to be updated
        $questions_to_update = array_filter($questions_list, function ($question) use ($existing_question_ids) {
            return in_array($question['question_id'], $existing_question_ids);
        });

        // Delete questions not present in the new list
        $questions_to_delete = array_diff($existing_question_ids, $new_question_ids);

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
            $this->update($question['question_id'], $question);
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
    private function create($question_data)
    {
        global $wpdb;

        // Convert arrays to JSON strings
        if (isset($question_data['text_options'])) {
            $question_data['text_options'] = json_encode($question_data['text_options']);
        }

        // Extract linear scale options and labels
        $linear_scale_options = isset($question_data['linear_scale_options']) ? $question_data['linear_scale_options'] : null;
        unset($question_data['linear_scale_options']);

        // Extract options
        $options = isset($question_data['options']) ? $question_data['options'] : null;
        unset($question_data['options']);

        $result = $wpdb->insert(
            "{$wpdb->prefix}{$this->questions_table}",
            $question_data
        );

        if ($result) {
            $question_id = $wpdb->insert_id;

            if ($linear_scale_options) {
                $linear_scale_data = [
                    'question_id' => $question_id,
                    'min' => $linear_scale_options['min'],
                    'max' => $linear_scale_options['max'],
                    'step' => $linear_scale_options['step']
                ];
                $wpdb->insert(
                    "{$wpdb->prefix}{$this->linear_scale_options_table}",
                    $linear_scale_data
                );

                foreach ($linear_scale_options['labels'] as $scale_value => $label) {
                    $label_data = [
                        'question_id' => $question_id,
                        'scale_value' => $scale_value,
                        'label' => $label
                    ];
                    $wpdb->insert(
                        "{$wpdb->prefix}{$this->linear_scale_labels_table}",
                        $label_data
                    );
                }
            }

            if ($options) {
                foreach ($options as $option) {
                    $option_data = [
                        'question_id' => $question_id,
                        'label' => $option['label'],
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
        global $wpdb;

        // Convert arrays to JSON strings
        if (isset($question_data['text_options'])) {
            $question_data['text_options'] = json_encode($question_data['text_options']);
        }

        // Extract linear scale options and labels
        $linear_scale_options = isset($question_data['linear_scale_options']) ? $question_data['linear_scale_options'] : null;
        unset($question_data['linear_scale_options']);

        // Extract options
        $options = isset($question_data['options']) ? $question_data['options'] : null;
        unset($question_data['options']);

        $result = $wpdb->update(
            "{$wpdb->prefix}{$this->questions_table}",
            $question_data,
            array('question_id' => $question_id)
        );

        if ($result) {
            // Update linear scale options if present
            if ($linear_scale_options) {
                $linear_scale_data = [
                    'min' => $linear_scale_options['min'],
                    'max' => $linear_scale_options['max'],
                    'step' => $linear_scale_options['step']
                ];
                $wpdb->update(
                    "{$wpdb->prefix}{$this->linear_scale_options_table}",
                    $linear_scale_data,
                    array('question_id' => $question_id)
                );

                // Delete existing linear scale labels
                $wpdb->delete(
                    "{$wpdb->prefix}{$this->linear_scale_labels_table}",
                    array('question_id' => $question_id)
                );

                // Insert updated linear scale labels
                foreach ($linear_scale_options['labels'] as $scale_value => $label) {
                    $label_data = [
                        'question_id' => $question_id,
                        'scale_value' => $scale_value,
                        'label' => $label
                    ];
                    $wpdb->insert(
                        "{$wpdb->prefix}{$this->linear_scale_labels_table}",
                        $label_data
                    );
                }
            }

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
                        'label' => $option['label'],
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
                $question->linear_scale_labels = $wpdb->get_results($labels_sql);

                $options_sql = "SELECT min, max, step
                                FROM {$wpdb->prefix}{$this->linear_scale_options_table}
                                WHERE question_id = '{$question->question_id}'";
                $question->linear_scale_options = $wpdb->get_row($options_sql);
            }

            $options_sql = "SELECT label, value, points
                            FROM {$wpdb->prefix}{$this->options_table}
                            WHERE question_id = '{$question->question_id}'";
            $question->options = $wpdb->get_results($options_sql);

            $question->text_options = json_decode($question->text_options);
            if (isset($question->text_options->add_word_count)) {
                $question->text_options->add_word_count = $question->text_options->add_word_count == 'true' ? true : false;
            }

            $question->multiple = $question->multiple == '1' ? true : false;
            $question->required = $question->required == '1' ? true : false;

            $question->score_percentage = (int)$question->score_percentage;
            $question->expected_points = (int)$question->expected_points;
        }

        return $questions;
    }
}
