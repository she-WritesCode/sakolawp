<?php
defined('ABSPATH') || exit;

?>

<div class="assessment_form">
    <div>
        <h5><?php echo esc_html($form['title']); ?></h5>
        <div class="text-sm"><?php echo $form['description']; ?></div>
    </div>
    <div class="questions flex flex-col gap-4">
        <?php
        foreach ($form['questions'] as $question) :
            $name = esc_attr("assessment[" . $question['question_id'] . "]");
            $id = $name;
        ?>
            <div class="flex flex-col gap-2 p-4 border bg-gray-50">
                <div class="">
                    <label class="font-medium"><?php echo ($question['question']); ?> <?php echo $question['required'] ? '<span class="text-red-500">*</span>' : ''; ?></label>
                    <?php if (!empty($question['description'])) : ?>
                        <div class="text-sm"><?php echo ($question['description']); ?></div>
                    <?php endif; ?>
                </div>

                <?php if (isset($question['type']) && $question['type'] === 'linear-scale') : ?>
                    <fieldset class="radio-group flex flex-col lg:flex-row gap-2 lg:gap-4 lg:items-center">
                        <?php
                        $min = $question['linear_scale_options']['min'];
                        $max = $question['linear_scale_options']['max'];
                        $step = $question['linear_scale_options']['step'];
                        $labels = $question['linear_scale_options']['labels'];
                        ?>
                        <div class="text-left"><?php echo $labels[$min]; ?></div>
                        <?php
                        for ($value = $min; $value <= $max; $value += $step) :
                            $label = $value;
                            $id = esc_attr("assessment[" . $name . '_' . $value . "]");
                        ?>
                            <div class="flex lg:flex-col items-center gap-2">
                                <label for="<?php echo $id; ?>"><?php echo esc_html($label); ?></label>
                                <input type="radio" name="<?php echo $name; ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo $id; ?>" <?php echo $question['required'] ? "required" : ''; ?>>
                            </div>
                        <?php endfor; ?>
                        <div><?php echo $labels[$max]; ?></div>
                    </fieldset>
                <?php elseif (isset($question['type']) && $question['type'] === 'radio' && isset($question['options'])) : ?>
                    <fieldset>
                        <?php
                        foreach ($question['options'] as $option) :
                            $id = esc_attr("assessment[" . $name . '_' . $option['value'] . "]");
                        ?>
                            <div>
                                <input type="radio" name="<?php echo $name; ?>" value="<?php echo esc_attr($option['value']); ?>" id="<?php echo $id; ?>" <?php echo $question['required'] ? "required" : ''; ?> />
                                <label for="<?php echo $id; ?>"><?php echo esc_html($option['label']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                <?php elseif (isset($question['type']) && $question['type'] === 'select') : ?>
                    <select name="<?php echo $name; ?>" id="<?php echo $id; ?>" <?php echo $question['required'] ? "required" : ''; ?>>
                        <?php foreach ($question['options'] as $option) : ?>
                            <option value="<?php echo esc_attr($option['value']); ?>">
                                <?php echo esc_html($option['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <input <?php echo isset($question['type']) ? 'type="' . $question['type'] . '"' : ""  ?> name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr($value); ?>" <?php echo $question['required'] ? "required" : ''; ?> />
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
