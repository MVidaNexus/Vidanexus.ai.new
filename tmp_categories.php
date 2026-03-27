<?php

$configFile = __DIR__ . '/config/tools.php';
$content = file_get_contents($configFile);

$replacements = [
    // Content
    "'slug' => 'faq-generator',
            'color' => '#f59e0b',
            'required_tier' => 'beginner',
            'category' => 'seo'," => "'slug' => 'faq-generator',
            'color' => '#f59e0b',
            'required_tier' => 'beginner',
            'category' => 'content',",
            
    "'slug' => 'video-script',
            'color' => '#f97316',
            'required_tier' => 'growth',
            'category' => 'marketing'," => "'slug' => 'video-script',
            'color' => '#f97316',
            'required_tier' => 'growth',
            'category' => 'content',",
            
    "'slug' => 'article-writer',
            'color' => '#14b8a6',
            'required_tier' => 'pro',
            'category' => 'seo'," => "'slug' => 'article-writer',
            'color' => '#14b8a6',
            'required_tier' => 'pro',
            'category' => 'content',",
            
    "'slug' => 'money-printer',
            'color' => '#f59e0b',
            'required_tier' => 'ultimate',
            'category' => 'marketing'," => "'slug' => 'money-printer',
            'color' => '#f59e0b',
            'required_tier' => 'ultimate',
            'category' => 'content',",
            
    // Intelligence
    "'slug' => 'competitor-xray',
            'color' => '#ff0055',
            'required_tier' => 'pro',
            'category' => 'seo'," => "'slug' => 'competitor-xray',
            'color' => '#ff0055',
            'required_tier' => 'pro',
            'category' => 'intelligence',",

    "'slug' => 'market-research',
            'color' => '#6366f1',
            'required_tier' => 'pro',
            'category' => 'marketing'," => "'slug' => 'market-research',
            'color' => '#6366f1',
            'required_tier' => 'pro',
            'category' => 'intelligence',",
            
    "'slug' => 'buyer-persona',
            'color' => '#f43f5e',
            'required_tier' => 'growth',
            'category' => 'marketing'," => "'slug' => 'buyer-persona',
            'color' => '#f43f5e',
            'required_tier' => 'growth',
            'category' => 'intelligence',",

    "'slug' => 'swot-analysis',
            'color' => '#06b6d4',
            'required_tier' => 'growth',
            'category' => 'marketing'," => "'slug' => 'swot-analysis',
            'color' => '#06b6d4',
            'required_tier' => 'growth',
            'category' => 'intelligence',",
            
    "'slug' => 'drama-trends',
            'color' => '#8b5cf6',
            'required_tier' => 'pro',
            'category' => 'marketing'," => "'slug' => 'drama-trends',
            'color' => '#8b5cf6',
            'required_tier' => 'pro',
            'category' => 'intelligence',",
            
    "'slug' => 'nlp-entities-analysis',
            'color' => '#a855f7',
            'required_tier' => 'pro',
            'category' => 'seo'," => "'slug' => 'nlp-entities-analysis',
            'color' => '#a855f7',
            'required_tier' => 'pro',
            'category' => 'intelligence',",
            
    // Tools (Power Tools)
    "'slug' => 'word-counter',
            'color' => '#10b981',
            'required_tier' => 'beginner',
            'category' => 'seo'," => "'slug' => 'word-counter',
            'color' => '#10b981',
            'required_tier' => 'beginner',
            'category' => 'tools',",
            
    "'slug' => 'folio-ocr',
            'color' => '#3b82f6',
            'required_tier' => 'beginner',
            'category' => 'seo'," => "'slug' => 'folio-ocr',
            'color' => '#3b82f6',
            'required_tier' => 'beginner',
            'category' => 'tools',",
            
    "'slug' => 'img-compress',
            'color' => '#10b981',
            'required_tier' => 'beginner',
            'category' => 'seo'," => "'slug' => 'img-compress',
            'color' => '#10b981',
            'required_tier' => 'beginner',
            'category' => 'tools',",
            
    "'slug' => 'seo-auditor',
            'color' => '#ec4899',
            'required_tier' => 'growth',
            'category' => 'seo'," => "'slug' => 'seo-auditor',
            'color' => '#ec4899',
            'required_tier' => 'growth',
            'category' => 'tools',",
            
    "'slug' => 'web-to-app',
            'color' => '#6366f1',
            'required_tier' => 'agency',
            'category' => 'marketing'," => "'slug' => 'web-to-app',
            'color' => '#6366f1',
            'required_tier' => 'agency',
            'category' => 'tools',",
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);
file_put_contents($configFile, $content);
echo "Done!\n";
