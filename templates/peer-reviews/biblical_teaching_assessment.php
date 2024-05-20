<?php
defined('ABSPATH') || exit;

// array objects of questions for prophetic word
$form = [
    "title" => "Biblical Teaching Assessment",
    "description" => "<div>Please assess the student's biblical teaching on a scale of 1-10 for each of the following categories:<br><ul><li><b>Illustration</b>&nbsp;– The teacher provided personal illustrations, biblical stories, allegories, and metaphors to explain the concept/thought to the receiver.</li><li><b>Application</b>&nbsp;– The teacher provided simple steps on how the scripture can be applied to your everyday life. The teacher indicated how they have applied the steps in their life.&nbsp;</li><li><b>Delivery</b>&nbsp;– Assess the delivery of the message: tone, language, body language, eye contact, expression, and responsiveness of the audience.&nbsp; Was the teacher capable of delivering the message well?</li><li><b>Charisma</b>&nbsp;– The teacher has a sensitivity to the Holy Spirit to convey the presence of God, inspire and stir up the spirit of the receiver.&nbsp;</li><li><b>Revelation</b>&nbsp;– The teacher demonstrates that they can decipher the mysteries in the scripture to reveal the hidden/deeper understanding of the word. <b>This component carries the most weight in scoring</b></li></ul><div><b>Criteria of Evaluation:</b><div><ol><li>Did not meet expectations</li><li>Demonstrates little to no experience in the area</li><li>Demonstrates some experience in the area</li><li>Able to navigate in the area but needs development</li><li>Competent with a moderate level of experience in the area</li><li>Demonstrates comprehension with potential</li><li>Proficient - comprehends the area</li><li>Great understanding with room for improvement</li><li>Demonstrates outstanding&nbsp;competency&nbsp;</li><li>Expert - demonstrates a&nbsp;deep understanding and mastery&nbsp;</li></ol></div></div></div>",
    'questions' => [
        [
            'question_id' => 'q1',
            'question' => 'Please rate the student on a scale of 1-10 for <b>Illustration</b>',
            'description' => '<b>Illustration</b> – The teacher provided personal illustrations, biblical stories, allegories, and metaphors to explain the concept/thought to the receiver.',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '1' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question_id' => 'q2',
            'question' => 'Please rate the student on a scale of 1-10 for <b>Application</b>',
            'description' => '<b>Application</b> – The teacher provided simple steps on how the scripture can be applied to your everyday life. The teacher indicated how they have applied the steps in their life.',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '1' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question_id' => 'q3',
            'question' => 'Please rate the student on a scale of 1-10 for <b>Delivery</b>',
            'description' => '<b>Delivery</b> – Assess the delivery of the message: tone, language, body language, eye contact, expression, and responsiveness of the audience.  Was the teacher capable of delivering the message well?',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '1' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question_id' => 'q4',
            'question' => 'Please rate the student on a scale of 1-10 for <b>Charisma</b>',
            'description' => '<b>Charisma</b> – The teacher has a sensitivity to the Holy Spirit to convey the presence of God, inspire and stir up the spirit of the receiver.',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '1' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question_id' => 'q5',
            'question' => "Please rate the student on a scale of 1-10 for <b>Revelation</b>",
            'description' => '<b>Revelation</b> – The teacher demonstrates that they can decipher the mysteries in the scripture to reveal the hidden/deeper understanding of the word. <b>This component carries the most weight in scoring</b>',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '1' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question_id' => 'q6',
            'question' => 'Did the prophetic word resonate with the individual? ',
            'type' => 'select',
            'options' => [
                [
                    'label' => 'Yes',
                    'value' => "yes"
                ],
                [
                    'label' => 'No',
                    'value' => "no"
                ],
                [
                    'label' => "Individual's response was not captured (verbally)",
                    'value' => "Individual's response was not captured (verbally)"
                ],
            ]
        ]
        // TODO: Comments and Feedback on every form
    ]
];
