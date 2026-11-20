<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Serve the React application
    |--------------------------------------------------------------------------
    |
    | Document loads for the public site and checkout receive the React app.
    | Requests carrying X-Backlink-Spa still reach the existing controllers.
    |
    */

    'serve' => (bool) env('FRONTEND_SERVE', true),

];
