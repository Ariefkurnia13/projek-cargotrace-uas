<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CargoTrace Group Scenario & Business Rules Configuration
    |--------------------------------------------------------------------------
    |
    | Group scenarios define specific routes, delay thresholds, and KPIs:
    | - Group A: Southeast Asia -> Europe | Threshold: 12h | Exception: Customs Hold | KPI: On-time delivery rate
    | - Group B: Asia -> North America   | Threshold: 18h | Exception: Weather Delay | KPI: Average delay duration
    | - Group C: Southeast Asia -> Australia | Threshold: 24h | Exception: Documentation Issue | KPI: Exception resolution time
    |
    */

    'scenario_group' => env('CARGOTRACE_SCENARIO_GROUP', 'Group A'),

    'delay_threshold_hours' => (int) env('CARGOTRACE_DELAY_THRESHOLD_HOURS', 12),

    /*
    |--------------------------------------------------------------------------
    | Delay Severity Classification Rules (Hours)
    |--------------------------------------------------------------------------
    */
    'severity_thresholds' => [
        'low_max' => 6,      // 0 - 6 hours
        'medium_max' => 24,  // >6 - 24 hours
        // > 24 hours = HIGH
    ],

    /*
    |--------------------------------------------------------------------------
    | Valid Shipment Lifecycle Status Transitions
    |--------------------------------------------------------------------------
    | CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED
    | Terminal status 'DELIVERED' cannot be reverted without administrative override.
    */
    'lifecycle_transitions' => [
        'CREATED' => ['BOOKED'],
        'BOOKED' => ['PICKED_UP'],
        'PICKED_UP' => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED' => [], // Terminal state
    ],

    /*
    |--------------------------------------------------------------------------
    | External API Service Integration
    |--------------------------------------------------------------------------
    */
    'external_api' => [
        'weather' => [
            'enabled' => env('WEATHER_API_ENABLED', true),
            'timeout' => (int) env('WEATHER_API_TIMEOUT', 3),
            'base_url' => env('WEATHER_API_BASE_URL', 'https://api.open-meteo.com/v1/forecast'),
            'disclaimer' => 'Weather data is retrieved for situational visibility only and is not deemed predictive proof of shipment delays.',
        ],
        'geocoding' => [
            'enabled' => env('GEOCODING_API_ENABLED', true),
            'timeout' => (int) env('GEOCODING_API_TIMEOUT', 3),
            'base_url' => env('GEOCODING_API_BASE_URL', 'https://nominatim.openstreetmap.org/search'),
        ],
    ],
];
