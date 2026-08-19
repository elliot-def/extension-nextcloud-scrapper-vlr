<?php
return [
    'routes' => [
        // 1. La nouvelle interface graphique (Page web)
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        
        // 2. Ton flux WebCal (Le calendrier)
        ['name' => 'feed#index', 'url' => '/feed', 'verb' => 'GET'],
    ]
];