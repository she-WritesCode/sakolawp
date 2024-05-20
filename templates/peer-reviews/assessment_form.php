<?php
defined('ABSPATH') || exit;

get_header();
do_action('sakolawp_before_main_content');

$form = $prophetic_word_assessment_form;
?>

<div class="assessment_form" id="assessment_form1">
    <h2><?php echo esc_html($form['title']); ?></h2>
    <p><?php echo $form['description']; ?></p>

    <?php foreach ($form['questions'] as $question) : ?>
        <div>
            <label><?php echo esc_html($question['question']); ?></label>
            <?php if (!empty($question['description'])) : ?>
                <p><?php echo esc_html($question['description']); ?></p>
            <?php endif; ?>

            <?php if ($question['type'] === 'linear-scale') : ?>
                <input type="range" name="<?php echo esc_attr($question['question']); ?>" min="<?php echo esc_attr($question['linear_scale_options']['min']); ?>" max="<?php echo esc_attr($question['linear_scale_options']['max']); ?>" step="<?php echo esc_attr($question['linear_scale_options']['step']); ?>" value="<?php echo esc_attr($question['linear_scale_options']['min']); ?>">
                <div>
                    <?php foreach ($question['linear_scale_options']['labels'] as $value => $label) : ?>
                        <span><?php echo esc_html($value); ?>: <?php echo esc_html($label); ?>
                        <?php endforeach; ?>
                </div>
            <?php elseif (!empty($question['options'])) : ?>
                <?php foreach ($question['options'] as $option) : ?>
                    <div>
                        <input type="radio" name="<?php echo esc_attr($question['question']); ?>" value="<?php echo esc_attr($option['value']); ?>" id="<?php echo esc_attr($option['value']); ?>">
                        <label for="<?php echo esc_attr($option['value']); ?>"><?php echo esc_html($option['label']); ?></label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <button type="submit">Submit</button>
</div>

<div class="assessment_form" id="assessment_form1">
    <h2><?php echo esc_html($form['title']); ?></h2>
    <p><?php echo $form['description']; ?></p>

    <?php foreach ($form['questions'] as $question) : ?>
        <div>
            <label><?php echo esc_html($question['question']); ?></label>
            <?php if (!empty($question['description'])) : ?>
                <p><?php echo esc_html($question['description']); ?></p>
            <?php endif; ?>

            <?php if ($question['type'] === 'linear-scale') : ?>
                <?php
                $min = $question['linear_scale_options']['min'];
                $max = $question['linear_scale_options']['max'];
                $step = $question['linear_scale_options']['step'];
                $labels = $question['linear_scale_options']['labels'];
                for ($value = $min; $value <= $max; $value += $step) :
                    $label = isset($labels[$value]) ? $labels[$value] : $value;
                ?>
                    <div>
                        <input type="radio" name="<?php echo esc_attr($question['question']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($question['question'] . '_' . $value); ?>">
                        <label for="<?php echo esc_attr($question['question'] . '_' . $value); ?>"><?php echo esc_html($label); ?></label>
                    </div>
                <?php endfor; ?>
            <?php elseif (!empty($question['options'])) : ?>
                <?php foreach ($question['options'] as $option) : ?>
                    <div>
                        <input type="radio" name="<?php echo esc_attr($question['question']); ?>" value="<?php echo esc_attr($option['value']); ?>" id="<?php echo esc_attr($option['value']); ?>">
                        <label for="<?php echo esc_attr($option['value']); ?>"><?php echo esc_html($option['label']); ?></label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <button type="submit">Submit</button>
</div>

<?php

do_action('sakolawp_after_main_content');
get_footer();
