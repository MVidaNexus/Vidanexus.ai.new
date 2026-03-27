<?php

namespace Modules\ArticleWriter\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Core\AI\AIManager;
use Illuminate\Http\Request;

class ArticleWriterController extends Controller
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('articlewriter::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('articlewriter::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canUseTool('article-writer')) {
            $msg = $user->getLimitReachedMessage('كاتب المقالات المحترف', 'pro-article-writer');
            return response()->json([
                'status' => 'error', 
                'message' => $msg
            ], 403);
        }

        if (!$user->wallet || $user->wallet->balance_credits < 1) {
            return response()->json(['status' => 'error', 'message' => 'رصيدك غير كافٍ لتوليد المقالات.'], 402);
        }

        // Fetch dynamic settings
        $provider = \App\Models\Setting::get('pro-article-writer_provider', 'openai');
        $model = \App\Models\Setting::get('pro-article-writer_model', 'gpt-4o');
        $dbPrompt = \App\Models\Setting::get('pro-article-writer_prompt');

        $finalPrompt = $dbPrompt ? str_replace('[prompt]', $request->prompt, $dbPrompt) : $request->prompt;

        $result = $this->aiManager->generate('pro-article-writer', $finalPrompt, [
            'provider' => $provider,
            'model' => $model,
            'max_tokens' => 1500,
        ]);

        // STRICT BILLING: Deduct only if AI generated a valid response
        if (isset($result['text']) && !empty(trim($result['text']))) {
            $user->wallet->decrement('balance_credits', 1);

            \App\Models\AiUsage::create([
                'user_id' => $user->id,
                'tool' => 'pro-article-writer',
                'provider' => $provider,
                'model' => $model,
                'status' => 'success',
            ]);
        } else {
            \App\Models\ToolError::log('pro-article-writer', new \Exception("AI generated empty article text."), 'Article Generation', $user->id);
            return response()->json(['status' => 'error', 'message' => 'Failed to generate article. Please try again or check your prompt.'], 500);
        }

        return response()->json($result);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('articlewriter::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('articlewriter::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
