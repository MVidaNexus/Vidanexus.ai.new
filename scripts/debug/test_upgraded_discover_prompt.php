<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\AI\AIManager;
use Modules\DiscoverHeadlines\Services\HeadlineService;
use App\Models\Setting;
use App\Models\User;

$aiManager = app(AIManager::class);
$service = app(HeadlineService::class);
$user = User::first() ?: User::factory()->create();

echo "=== TESTING UPGRADED DISCOVER HEADLINES PROMPT ===\n";

$upgradedPromptTemplate = <<<'PROMPT'
You are a Senior Editor and Google Discover Algorithm Specialist at a premier digital publishing network.
Your mission is to craft [variants] high-CTR, algorithm-favored headlines designed to rank on Google Discover, strictly adhering to Google's Helpful Content Update (HCU), E-E-A-T standards, and strict journalistic factuality.

🔹 TARGET SUBJECT / KEYWORD: [Keyword]

🔹 VERIFIED NEWS CONTEXT & REAL-TIME SOURCES:
[NewsContext]

══════════════════════════════════════════════════════════════
🚨 STRICT FACTUAL INTEGRITY & ZERO HALLUCINATION (حظر التأليف والتلفيق):
══════════════════════════════════════════════════════════════
1. 100% FACT-BASED & REAL (ممنوع الفبركة أو اختلاق الوقائع):
   - Every fact, number, score, club, official statement, or event in the headline MUST be strictly true, real, and grounded in the verified context or real-world facts.
   - NEVER invent fake transfers, imaginary price changes, fake celebrity deaths, or fabricated quotes.
   - Do NOT confuse entities (e.g. if a news title mentions a commentator or another person with the same name, do NOT confuse them with the famous athlete or entity).
2. NO DECEPTIVE CLICKBAIT (لا للتضليل):
   - Never promise information that is untrue or exaggerated. Google Discover strictly demotes clickbait that misleads readers.
   - The headline must deliver real substance and accurate context.

══════════════════════════════════════════════════════════════
🔥 GOOGLE DISCOVER ALGORITHM RANKING PILLARS (معايير ترشيح ديسكوفر):
══════════════════════════════════════════════════════════════
1. ENTITY-PROMINENT (الكيان أولاً):
   - Start the headline or lead immediately with the core Knowledge Graph Entity (اسم اللاعب، النادي، الشركة، الوزارة، السلعة، الشخصية).
   - This enables Google Discover to instantly map the article to user interest feeds.
2. INTELLIGENT CURIOSITY GAP (فجوة فضول ذكية مبنية على حقيقة):
   - State the core real event/news clearly, while sparking natural interest to click and discover the details, reasons, official decisions, or backstage facts without deception.
3. DYNAMIC ACTIVE VERBS (أفعال خبرية حاسمة):
   - Use strong, active verbs (يكشف، يحسم، يعلن، يحدد، يفاجئ، يوضح، يصدر، يتراجع، يوجه، يطلق / reveals, decides, announces, clarifies, unveils, sets).
4. EDITORIAL DIVERSITY ACROSS VARIANTS:
   - Provide [variants] distinct editorial angles:
     • Variant 1: Breaking / Direct Impact Hook (الخبر المباشر مع عنصر المفاجأة أو الحسم).
     • Variant 2: Backstage & Reason Hook (الكواليس والأسباب الحقيقية).
     • Variant 3: Practical Reader Value (الموعد، الشروط، الأسعار، أو الأثر المباشر).
     • Variant 4: Official Statement & Decisive Stance (الموقف الرسمي أو التصريح الحاسم).
     • Variant 5+: Analysis & Future Outlook (التطورات القادمة والسيناريو المتوقع).
5. IDEAL LENGTH SWEET SPOT:
   - Between 45 and 85 characters in Arabic / 50 to 90 characters in English. Perfectly formatted for mobile Discover cards without truncation.
6. HIGH-CTR VISUAL CONCEPTS:
   - For every headline, provide 2 tailored visual angles (visual_concepts) describing realistic, high-contrast, emotive photo ideas to maximize Discover CTR.

🔹 OUTPUT FORMAT REQUIREMENTS (STRICT JSON ONLY):
- All headlines, entities, LSI keywords, and visual concepts MUST be in the same language as the input (Arabic for Arabic, English for English).
PROMPT;

// Set the setting in database for testing
Setting::set('discover-headlines_prompt', $upgradedPromptTemplate, 'textarea', 'tool_settings');

$tests = [
    'سعر الذهب اليوم',
    'محمد صلاح',
    'الذكاء الاصطناعي',
];

foreach ($tests as $kw) {
    echo "\n----------------------------------------------------\n";
    echo "🔍 Testing Upgraded Prompt on: {$kw}\n";
    echo "----------------------------------------------------\n";

    $res = $service->generate($user->id, [
        'keyword' => $kw,
        'type' => 'keyword',
        'country' => 'EG',
        'variants' => 5,
    ]);

    if (!empty($res['scored'])) {
        foreach ($res['scored'] as $idx => $item) {
            $num = $idx + 1;
            $hl = $item['headline'];
            $score = $item['score'];
            $len = mb_strlen($hl);
            echo "  {$num}. [Score: {$score}% | {$len} chars] {$hl}\n";
            if (!empty($item['visual_concepts'])) {
                echo "     📸 Visual 1: " . ($item['visual_concepts'][0]['description'] ?? '') . "\n";
            }
        }
    } else {
        echo "  ⚠️ No scored headlines returned.\n";
        echo "  Raw: " . json_encode($res) . "\n";
    }
}
