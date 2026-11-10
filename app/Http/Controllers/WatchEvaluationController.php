<?php

namespace App\Http\Controllers;

use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Setting;
use App\Models\WatchEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WatchEvaluationController extends Controller
{
    public function index()
    {
        /* Un vendedor autenticato ha gia' dato questi dati in fase di registrazione:
           riempiamo la sessione e lo mandiamo direttamente ai detalles del reloj. */
        if ($u = auth()->user()) {
            request()->session()->put('evaluation_step1', [
                'name'         => trim(($u->first_name ?: $u->name) . ' ' . ($u->last_name ?? '')) ?: $u->name,
                'email'        => $u->email,
                'phone_prefix' => $u->phone_prefix ?? null,
                'phone'        => $u->phone ?? null,
                'country'      => $u->country ?? null,
                'city'         => null,
                'from_panel'   => true,
            ]);
            return redirect()->route('evaluation.step2-view');
        }

        return view('pages.watch-evaluation');
    }

    public function step1(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:200',
            'email'        => 'required|email|max:255',
            'phone_prefix' => 'nullable|string|max:10',
            'phone'        => 'nullable|string|max:30',
            'country'      => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
        ]);
        $request->session()->put('evaluation_step1', $validated);
        return redirect()->route('evaluation.step2-view');
    }

    public function step2View()
    {
        if (!session()->has('evaluation_step1')) {
            return redirect()->route('evaluation.index');
        }
        return view('pages.watch-evaluation-step2');
    }

    public function step2(Request $request)
    {
        $step1 = $request->session()->get('evaluation_step1');
        if (!$step1) return redirect()->route('evaluation.index');

        $validated = $request->validate([
            'brand'            => 'required|string|max:200',
            'model'            => 'required|string|max:200',
            'reference'        => 'nullable|string|max:100',
            'manufacture_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'condition'        => 'nullable|string|max:50',
            'box_papers'       => 'nullable|string|max:50',
            'accept_terms'     => 'accepted',
        ]);

        $aiResult = $this->evaluateWithAI($validated, app()->getLocale());

        $cols = array_column(\DB::select('SHOW COLUMNS FROM watch_evaluations'), 'Field');
        $data = [
            'name'             => $step1['name'],
            'email'            => $step1['email'],
            'brand'            => $validated['brand'],
            'model'            => $validated['model'],
            'reference'        => $validated['reference'] ?? null,
            'manufacture_year' => $validated['manufacture_year'] ?? null,
            'condition'        => $validated['condition'] ?? null,
            'box_papers'       => $validated['box_papers'] ?? null,
            'ai_response'      => $aiResult,
            'estimated_value_min' => $aiResult['value_min'] ?? null,
            'estimated_value_max' => $aiResult['value_max'] ?? null,
            'locale'           => app()->getLocale(),
        ];
        if (in_array('phone_prefix', $cols)) $data['phone_prefix'] = $step1['phone_prefix'] ?? null;
        if (in_array('phone', $cols))        $data['phone']        = $step1['phone'] ?? null;
        if (in_array('country', $cols))      $data['country']      = $step1['country'] ?? null;
        if (in_array('city', $cols))         $data['city']         = $step1['city'] ?? null;

        $evaluation = WatchEvaluation::create($data);

        /* Chi arriva dal pannello e' gia' un utente registrato: non va
           trattato come lead ne' reiscritto alla lista valutazioni. */
        if (empty($step1['from_panel'])) {
            try {
                $slug = Setting::get('list_id_evaluacion');
                $listId = null;
                if ($slug) {
                    $nl = NewsletterList::where('slug', $slug)->orWhere('id', (int)$slug)->first();
                    $listId = $nl?->id;
                }
                NewsletterSubscriber::subscribe($step1['email'], $step1['name'], $listId, 'general', app()->getLocale());
            } catch (\Exception $e) {
                Log::warning('Newsletter subscribe failed: ' . $e->getMessage());
            }
        }

        $fromPanel = !empty($step1['from_panel']);
        $request->session()->forget('evaluation_step1');

        return view('pages.watch-evaluation-result', [
            'evaluation' => $evaluation,
            'aiResult'   => $aiResult,
            'fromPanel'  => $fromPanel,
        ]);
    }

    private function evaluateWithAI(array $data, string $locale): array
    {
        try {
            $key   = Setting::get('openai_key');
            $model = Setting::get('openai_model', 'gpt-4o');
            if (!$key) return ['error' => 'No API key'];

            $lang = $locale === 'es' ? 'Spanish' : 'English';
            $prompt = "You are a luxury watch market expert. Evaluate this watch and respond ONLY with valid JSON.

Watch: {$data['brand']} {$data['model']}
Reference: " . ($data['reference'] ?? 'Unknown') . "
Year: " . ($data['manufacture_year'] ?? 'Unknown') . "
Condition: " . ($data['condition'] ?? 'Unknown') . "
Box & Papers: " . ($data['box_papers'] ?? 'Unknown') . "

Respond in {$lang} with this exact JSON:
{
  \"value_min\": <integer EUR>,
  \"value_max\": <integer EUR>,
  \"market_trend\": \"rising|stable|falling\",
  \"trend_percentage\": <integer>,
  \"summary\": \"<2-3 sentences in {$lang}>\",
  \"recommendation\": \"<1-2 sentences in {$lang}>\",
  \"factors\": [\"<factor 1>\", \"<factor 2>\", \"<factor 3>\"],
  \"confidence\": \"high|medium|low\"
}";

            $response = Http::withToken($key)->timeout(45)->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $model,
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'max_tokens'  => 800,
                'temperature' => 0.3,
            ]);

            $content = preg_replace('/```json|```/', '', $response->json('choices.0.message.content', ''));
            return json_decode(trim($content), true) ?? ['error' => 'Parse error'];

        } catch (\Exception $e) {
            Log::error('AI evaluation failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
