<?php

namespace App\Services;

class SEOAnalyzerService
{
    /**
     * Analyze a headline for SEO and CTR potential.
     * 
     * @param string $headline
     * @return array
     */
    public function analyzeHeadline($headline)
    {
        $analysis = [
            'score' => 0,
            'grade' => '',
            'issues' => [],
            'strengths' => [],
            'suggestions' => [],
        ];

        if (empty($headline)) return $analysis;

        // 1. Length Check
        $len = mb_strlen($headline);
        if ($len >= 40 && $len <= 70) {
            $analysis['score'] += 30;
            $analysis['strengths'][] = '✓ طول العنوان مثالي (40-70 حرفاً)';
        } elseif ($len < 40) {
            $analysis['issues'][] = '⚠ العنوان قصير جداً - قد لا يظهر بشكل جيد في ديسكوفر';
            $analysis['suggestions'][] = 'حاول إطالة العنوان ليكون أكثر وصفاً';
        } else {
            $analysis['issues'][] = '⚠ العنوان طويل جداً - قد يتم قطعه في محركات البحث';
            $analysis['suggestions'][] = 'حاول اختصار العنوان ليكون أقل من 70 حرفاً';
        }

        // 2. Power Words
        $powerWords = ['عاجل', 'حصري', 'صادم', 'مفاجأة', 'يكشف', 'يفاجئ', 'يزلزل', 'خطير', 'سري', 'شاهد'];
        $foundPowerWords = [];
        foreach ($powerWords as $word) {
            if (mb_stripos($headline, $word) !== false) {
                $foundPowerWords[] = $word;
            }
        }

        if (!empty($foundPowerWords)) {
            $analysis['score'] += 20;
            $analysis['strengths'][] = '✓ يحتوي كلمات قوية: ' . implode('، ', $foundPowerWords);
        } else {
            $analysis['suggestions'][] = 'استخدم كلمات جذابة مثل: شاهد، يكشف، صادم، عاجل';
        }

        // 3. Numbers
        if (preg_match('/\d+/', $headline)) {
            $analysis['score'] += 15;
            $analysis['strengths'][] = '✓ يحتوي أرقاماً - تزيد من نسبة النقر (CTR)';
        }

        // 4. Questions/Curiosity
        if (str_contains($headline, '؟') || str_contains($headline, '?')) {
            $analysis['score'] += 15;
            $analysis['strengths'][] = '✓ صيغة سؤال تثير الفضول';
        }

        // 5. Dead words/Weak words
        $deadWords = ['يؤكد', 'يشيد', 'يستعرض', 'ينطلق', 'يعلن'];
        foreach ($deadWords as $word) {
            if (mb_stripos($headline, $word) !== false) {
                $analysis['score'] -= 10;
                $analysis['issues'][] = '✗ كلمة ضعيفة: "' . $word . '" - استبدلها بفعل أكثر ديناميكية';
            }
        }

        $analysis['score'] = max(0, min(100, $analysis['score']));
        $analysis['grade'] = $this->calculateGrade($analysis['score']);

        return $analysis;
    }

    /**
     * Map score to grade.
     */
    protected function calculateGrade($score)
    {
        if ($score >= 80) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 40) return 'C';
        return 'D';
    }

    /**
     * Analyze content structure and keyword density.
     */
    public function analyzeContent($content, $targetKeyword = '')
    {
        $text = strip_tags($content);
        $wordCount = str_word_count($text);
        
        $analysis = [
            'score' => 0,
            'word_count' => $wordCount,
            'keyword_density' => 0,
            'issues' => [],
            'strengths' => [],
        ];

        // Word count evaluation
        if ($wordCount >= 600) {
            $analysis['score'] += 30;
            $analysis['strengths'][] = '✓ طول المقال ممتاز للأرشفة';
        } elseif ($wordCount >= 300) {
            $analysis['score'] += 20;
            $analysis['strengths'][] = '✓ طول المقال جيد';
        } else {
            $analysis['issues'][] = '⚠ المحتوى قصير جداً - يفضل أن يكون أكثر من 300 كلمة';
        }

        // Heading structure
        if (str_contains($content, '<h2>')) {
            $analysis['score'] += 20;
            $analysis['strengths'][] = '✓ يحتوي على عناوين فرعية H2';
        } else {
            $analysis['issues'][] = '⚠ يفتقر لعناوين فرعية H2 لتنظيم المحتوى';
        }

        if (!empty($targetKeyword)) {
            $count = mb_substr_count(mb_strtolower($text), mb_strtolower($targetKeyword));
            $density = $wordCount > 0 ? ($count / $wordCount) * 100 : 0;
            $analysis['keyword_density'] = round($density, 2);

            if ($density >= 1 && $density <= 3) {
                $analysis['score'] += 30;
                $analysis['strengths'][] = '✓ كثافة الكلمة المفتاحية مثالية (' . $analysis['keyword_density'] . '%)';
            } elseif ($density > 3) {
                $analysis['issues'][] = '⚠ كثافة عالية جداً للكلمة المفتاحية - قد يعتبر حشواً (Stuffing)';
            } else {
                $analysis['issues'][] = '⚠ كثافة منخفضة للكلمة المفتاحية';
            }
        }

        $analysis['score'] = min(100, $analysis['score']);
        
        return $analysis;
    }
}
