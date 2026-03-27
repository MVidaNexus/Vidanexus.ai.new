<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Arab Countries Map for Keywords & Trends Service
    |--------------------------------------------------------------------------
    */
    
    'countries' => [
        'EG' => ['name' => 'Egypt', 'flag' => '🇪🇬', 'lang' => 'ar', 'timezone' => 'Africa/Cairo'],
        'SA' => ['name' => 'Saudi Arabia', 'flag' => '🇸🇦', 'lang' => 'ar', 'timezone' => 'Asia/Riyadh'],
        'AE' => ['name' => 'United Arab Emirates', 'flag' => '🇦🇪', 'lang' => 'ar', 'timezone' => 'Asia/Dubai'],
        'KW' => ['name' => 'Kuwait', 'flag' => '🇰🇼', 'lang' => 'ar', 'timezone' => 'Asia/Kuwait'],
        'MA' => ['name' => 'Morocco', 'flag' => '🇲🇦', 'lang' => 'ar', 'timezone' => 'Africa/Casablanca'],
        'DZ' => ['name' => 'Algeria', 'flag' => '🇩🇿', 'lang' => 'ar', 'timezone' => 'Africa/Algiers'],
        'TN' => ['name' => 'Tunisia', 'flag' => '🇹🇳', 'lang' => 'ar', 'timezone' => 'Africa/Tunis'],
        'IQ' => ['name' => 'Iraq', 'flag' => '🇮🇶', 'lang' => 'ar', 'timezone' => 'Asia/Baghdad'],
        'JO' => ['name' => 'Jordan', 'flag' => '🇯🇴', 'lang' => 'ar', 'timezone' => 'Asia/Amman'],
        'QA' => ['name' => 'Qatar', 'flag' => '🇶🇦', 'lang' => 'ar', 'timezone' => 'Asia/Qatar'],
        'OM' => ['name' => 'Oman', 'flag' => '🇴🇲', 'lang' => 'ar', 'timezone' => 'Asia/Muscat'],
        'BH' => ['name' => 'Bahrain', 'flag' => '🇧🇭', 'lang' => 'ar', 'timezone' => 'Asia/Bahrain'],
        'YE' => ['name' => 'Yemen', 'flag' => '🇾🇪', 'lang' => 'ar', 'timezone' => 'Asia/Aden'],
        'LB' => ['name' => 'Lebanon', 'flag' => '🇱🇧', 'lang' => 'ar', 'timezone' => 'Asia/Beirut'],
        'LY' => ['name' => 'Libya', 'flag' => '🇱🇾', 'lang' => 'ar', 'timezone' => 'Africa/Tripoli'],
        'PS' => ['name' => 'Palestine', 'flag' => '🇵🇸', 'lang' => 'ar', 'timezone' => 'Asia/Gaza'],
        'SY' => ['name' => 'Syria', 'flag' => '🇸🇾', 'lang' => 'ar', 'timezone' => 'Asia/Damascus'],
        'US' => ['name' => 'United States', 'flag' => '🇺🇸', 'lang' => 'en', 'timezone' => 'America/New_York'],
        'PL' => ['name' => 'Poland', 'flag' => '🇵🇱', 'lang' => 'pl', 'timezone' => 'Europe/Warsaw'],
    ],
    
    'default_region_ar' => 'EG',
    'default_region_en' => 'US',
    
    'news_topics' => [
        'GENERAL' => ['name' => 'Top Stories', 'icon' => 'fas fa-newspaper'],
        'WORLD' => ['name' => 'World', 'icon' => 'fas fa-globe'],
        'BUSINESS' => ['name' => 'Business', 'icon' => 'fas fa-chart-line'],
        'TECHNOLOGY' => ['name' => 'Technology', 'icon' => 'fas fa-microchip'],
        'ENTERTAINMENT' => ['name' => 'Entertainment', 'icon' => 'fas fa-film'],
        'SPORTS' => ['name' => 'Sports', 'icon' => 'fas fa-running'],
        'SCIENCE' => ['name' => 'Science', 'icon' => 'fas fa-flask'],
        'HEALTH' => ['name' => 'Health', 'icon' => 'fas fa-heartbeat'],
    ],
    
    'cache' => [
        'trends_ttl' => 600,
        'news_ttl' => 600,
        'competitors_ttl' => 86400,
    ],
];
