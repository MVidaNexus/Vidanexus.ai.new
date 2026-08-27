<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

echo "=== TESTING WORD BOUNDARY TOPIC MATCHING & MASSIVE POOL ===\n";

$techKeywords = [
    'تكنولوجيا', 'تقنية', 'تقنيات', 'تكنولوجي', 'هاتف', 'هواتف', 'هواتف ذكية', 'موبايل', 'جوال',
    'آيفون', 'ايفون', 'أندرويد', 'اندرويد', 'سامسونج', 'سامسونغ', 'آبل', 'أبل', 'مايكروسوفت',
    'جوجل', 'غوغل', 'ميتا', 'فيسبوك', 'إنستغرام', 'تيك توك', 'يوتيوب', 'واتساب', 'تلغرام',
    'ذكاء اصطناعي', 'الذكاء الاصطناعي', 'تشات جي بي تي', 'شات جي بي تي', 'روبوت', 'روبوتات',
    'أمن سيبراني', 'سايبر', 'اختراق', 'هاكر', 'ثغرة أمنية', 'هجمات إلكترونية', 'حاسوب', 'كمبيوتر',
    'لابتوب', 'معالج', 'معالجات', 'رقائق', 'شريحة إلكترونية', 'إنفيديا', 'انفيديا', 'تسلا',
    'سبيس إكس', 'تطبيقات', 'تطبيق ذكي', 'برمجيات', 'سوفتوير', 'بلوكتشين', 'بيتكوين', 'كريبتو',
    'عملات رقمية', 'أجهزة إلكترونية', 'سماعات', 'كاميرا رقمية', 'شاشات ذكية'
];

$negatives = [
    'أمطار', 'سيول', 'طقس', 'الأرصاد', 'درجات الحرارة', 'وفاة', 'مقتل', 'جثة', 'حادث تصادم',
    'حادث سير', 'قتلى', 'جريمة', 'زلزال', 'سعر الذهب', 'سعر الجرام', 'سعر الدولار', 'مباراة',
    'تشكيل', 'الدوري', 'خفاش', 'جنازة', 'عزاء', 'إعدام', 'محاكمة', 'حبس', 'ضبط', 'النيابة العامة',
    'سرقة', 'مشاجرة'
];

function matchTopicStrict($title, $desc, $keywords, $negatives) {
    $text = mb_strtolower($title . ' ' . $desc);

    // 1. Negative check (whole word)
    foreach ($negatives as $neg) {
        $pattern = '/(?<=^|[^\p{L}\p{N}])' . preg_quote(mb_strtolower($neg), '/') . '(?=[^\p{L}\p{N}]|$)/u';
        if (preg_match($pattern, $text)) {
            return false;
        }
    }

    // 2. Positive check (whole word)
    $hits = 0;
    foreach ($keywords as $kw) {
        $pattern = '/(?<=^|[^\p{L}\p{N}])' . preg_quote(mb_strtolower($kw), '/') . '(?=[^\p{L}\p{N}]|$)/u';
        if (preg_match($pattern, $text)) {
            $hits++;
        }
    }

    return $hits > 0;
}

// Fetch Section URL + Search URL
$urls = [
    GoogleNewsRss::sectionUrl('TECHNOLOGY', 'EG', 'ar'),
    GoogleNewsRss::searchUrl('(تكنولوجيا OR تقنية OR هواتف OR ذكاء اصطناعي OR آبل OR سامسونج OR أمن سيبراني) when:24h', 'EG', 'ar'),
    GoogleNewsRss::searchUrl('(هواتف ذكية OR آيفون OR شات جي بي تي OR روبوتات OR رقائق OR مايكروسوفت) when:24h', 'EG', 'ar'),
];

$articles = [];
foreach ($urls as $u) {
    $res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($u);
    $xml = @simplexml_load_string($res->body());
    if ($xml && isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $mapped = GoogleNewsRss::mapRssItem($item);
            if ($mapped) {
                $articles[$mapped['link']] = $mapped;
            }
        }
    }
}

echo "Total Raw Collected: " . count($articles) . "\n";

$passed = [];
foreach ($articles as $a) {
    if (matchTopicStrict($a['title'], $a['description'], $techKeywords, $negatives)) {
        $passed[] = $a;
    }
}

echo "Total Passed Strict Filter: " . count($passed) . "\n";
echo "\n--- First 10 Passed Articles ---\n";
foreach (array_slice($passed, 0, 10) as $idx => $p) {
    $num = $idx + 1;
    echo "{$num}. [{$p['source']}] {$p['title']} ({$p['pubDate']})\n";
}
