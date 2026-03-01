<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Face Recognition – Confidence Threshold
    |--------------------------------------------------------------------------
    | Minimum cosine-similarity score required to accept a face match.
    */
    'face_confidence_threshold' => env('FACE_CONFIDENCE_THRESHOLD', 0.60),

    /*
    |--------------------------------------------------------------------------
    | Liveness Detection – Score Threshold
    |--------------------------------------------------------------------------
    | Minimum liveness score to consider a frame as a real (live) face.
    */
    'liveness_score_threshold' => env('LIVENESS_SCORE_THRESHOLD', 0.50),

    /*
    |--------------------------------------------------------------------------
    | GPS Radius Default (meters)
    |--------------------------------------------------------------------------
    | Maximum distance in meters a user may be from the attendance location.
    */
    'gps_radius_default' => env('GPS_RADIUS_DEFAULT', 100),

    /*
    |--------------------------------------------------------------------------
    | Auto Logout (minutes)
    |--------------------------------------------------------------------------
    | Number of idle minutes before the system automatically logs a user out.
    */
    'auto_logout_minutes' => env('AUTO_LOGOUT_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Embedding Cipher Key
    |--------------------------------------------------------------------------
    | AES-256-CBC key used to encrypt / decrypt stored face embeddings.
    | Set EMBEDDING_CIPHER_KEY in your .env file – do NOT commit the value.
    */
    'embedding_cipher_key' => env('EMBEDDING_CIPHER_KEY'),

];
