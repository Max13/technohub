<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exam details
    |--------------------------------------------------------------------------
    */

    'initial_timer' => 3,

    'available_timers' => [0, 5, 10, 15, 20, 30, 60, 120, 240, 300],

    'rules' => [
        'From the moment you start an exam, you cannot pause it.',
        'You can make a pause between each question.',
        'If you have an internet issue during a question, you will automatically fail this question.',
        'You can come back to the exam, you will resume it at the next question.',
        'If you are ready, continue. If not, please go back.',
    ],

];
