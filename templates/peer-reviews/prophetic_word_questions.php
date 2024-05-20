<?php
defined('ABSPATH') || exit;

// array objects of questions for prophetic word
$prophetic_word_assessment_form = [
    "title" => "Prophetic Word Peer Assessment",
    "description" => "<div><div><div><b>Word of Knowledge:</b> A revelatory&nbsp;word from the Holy Spirit the details knowledge of current or past details</div><div><b>Word of Prophecy:</b> A word that reveals an unknown future event</div><div><b>Word of Wisdom:</b> A word that provides divine insight&nbsp;into to discern the best&nbsp;course of action in various situations</div></div><div><br></div><b>Criteria of Evaluation:</b><div><ol><li>Did not meet expectations</li><li>Demonstrates little to no experience in the area</li><li>Demonstrates some experience in the area</li><li>Able to navigate in the area but needs development</li><li>Competent with a moderate level of experience in the area</li><li>Demonstrates comprehension with potential</li><li>Proficient - comprehends the area</li><li>Great understanding with room for improvement</li><li>Demonstrates outstanding&nbsp;competency&nbsp;</li><li>Expert - demonstrates a&nbsp;deep understanding and mastery&nbsp;<br></li></ol></div><div><br></div></div>",
    'questions' => [
        [
            'question' => 'Did the student provide a word of knowledge?',
            'description' => 'Word of Knowledge: A revelatory word from the Holy Spirit the details knowledge of current or past details',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 0,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '0' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question' => 'Did the student provide a word of prophecy?',
            'description' => 'Word of Prophecy: A word that reveals an unknown future event',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 0,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '0' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question' => 'Did the student provide a word of wisdom?',
            'description' => 'Word of Wisdom: A word that provides divine insight into to discern the best course of action in various situations',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 0,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '0' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question' => 'Did the student deliver the word with authority?',
            'description' => '',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 0,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '0' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question' => "Score the student's prophetic word",
            'description' => '',
            'type' => 'linear-scale',
            'linear_scale_options' => [
                'min' => 0,
                'max' => 10,
                'step' => 1,
                'labels' => [
                    '0' => 'Did not meet expectations',
                    '10' => 'Expert - demonstrates a deep understanding and mastery'
                ],
            ],
        ],
        [
            'question' => 'Did the prophetic word resonate with the individual? ',
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