namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\Services\NLPAnalysisService;
use Illuminate\Support\Facades\Log;

class NLPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard.nlp.index');
    }

    public function analyze(Request $request, NLPAnalysisService $service)
    {
        $request->validate([
            'content' => 'required|string',
            'target_keyword' => 'nullable|string'
        ]);

        try {
            $analysis = $service->analyze($request->content, $request->target_keyword);
            return response()->json(['status' => 'success', 'data' => $analysis]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nlpentitiesanalysis::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('nlpentitiesanalysis::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nlpentitiesanalysis::edit');
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
