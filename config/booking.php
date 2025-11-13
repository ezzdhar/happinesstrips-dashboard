<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Child Age Threshold
    |--------------------------------------------------------------------------
    |
    | This value determines the age threshold (in years) at which a child
    | is charged as an adult. Children below this age are free, while
    | children at or above this age are charged the full adult rate.
    |
    | Default: 12 years
    |
    */

    'child_age_threshold' => env('CHILD_AGE_THRESHOLD', 12),

];
