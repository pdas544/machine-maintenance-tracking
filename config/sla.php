<?php

return [
    'thresholds' => [
        1 => (int) env('SLA_LEVEL_1_MINUTES', 1),
        2 => (int) env('SLA_LEVEL_2_MINUTES', 60),
        3 => (int) env('SLA_LEVEL_3_MINUTES', 1440),
        4 => (int) env('SLA_LEVEL_4_MINUTES', 2880),
    ],
];
