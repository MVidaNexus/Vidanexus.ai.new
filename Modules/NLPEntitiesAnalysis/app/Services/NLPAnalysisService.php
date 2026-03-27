<?php

namespace Modules\NLPEntitiesAnalysis\Services;

use App\Core\AI\AIManager;
use Illuminate\Support\Facades\Log;

class NLPAnalysisService
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    public function analyze($content, $targetKeyword = null)
    {
        $prompt = "Analyze the following content for SEO and NLP optimization:\n\n";
        $prompt .= "--- CONTENT START ---\n" . $content . "\n--- CONTENT END ---\n\n";
        
        if ($targetKeyword) {
            $prompt .= "Target Keyword: " . $targetKeyword . "\n\n";
        }

        $prompt .= "Please provide the analysis in JSON format with the following keys:\n";
        $prompt .= "1. 'search_intent': A detailed description of the inferred search intent.\n";
        $prompt .= "2. 'entities': An array of key entities found (Names, Organizations, Concepts) with their relevance scores.\n";
        $prompt .= "3. 'missing_entities': An array of entities that should be included to improve topical authority for the niche.\n";
        $prompt .= "4. 'eeat_score': A score from 1-10 on Expertise, Experience, Authoritativeness, and Trustworthiness.\n";
        $prompt .= "5. 'eeat_suggestions': Specific points to add to improve the E-E-A-T score.\n";
        $prompt .= "6. 'content_gaps': Key topics or angles missing from the current draft.\n";
        $prompt .= "7. 'nlp_readability': A professional assessment of the NLP-based readability and semantic flow.\n";

        try {
            $response = $this->aiManager->generateResponse($prompt, 'nlp-entities-analysis');
            
            // Clean response to handle potential markdown wrappers
            $json = preg_replace('/^```json\s*|```$/m', '', trim($response));
            $data = json_decode($json, true);

            if (!$data) {
                // Fallback parsing if JSON fails
                Log::error("NLP Analysis JSON Parse Failed: " . $response);
                return [
                    'raw_response' => $response,
                    'error' => 'Failed to parse AI response as JSON.'
                ];
            }

            return $data;

        } catch (\Exception $e) {
            Log::error("NLP Analysis Error: " . $e->getMessage());
            throw $e;
        }
    }
}
