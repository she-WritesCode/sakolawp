<?php
class RunQuestionsRepo
{
    protected $questions_table = 'sakolawp_questions';
    protected $linear_scale_options_table = 'sakolawp_linear_scale_options';
    protected $linear_scale_labels_table = 'sakolawp_linear_scale_labels';
    protected $options_table = 'sakolawp_question_options';

    /** Create a new question */
    function create($question_data)
    {
        global $wpdb;

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

    /** Update an existing question */
    function update($question_id, $question_data)
    {
        global $wpdb;

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

                $wpdb->delete(
                    "{$wpdb->prefix}{$this->linear_scale_labels_table}",
                    array('question_id' => $question_id)
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
                $wpdb->delete(
                    "{$wpdb->prefix}{$this->options_table}",
                    array('question_id' => $question_id)
                );

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
        }

        return $questions;
    }
}
