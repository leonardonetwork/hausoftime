<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente IA único (OpenAI / Anthropic) según Ajustes › IA.
 * Los modelos actuales razonan antes de responder: no se envía temperature
 * y el límite de tokens incluye margen para el razonamiento.
 */
class AiClient
{
    public const OPENAI_MODELS = [
        'gpt-6.1-sol'   => 'GPT-6.1 Sol (Recomendado)',
        'gpt-6-astra'   => 'GPT-6 Astra (máxima calidad)',
        'gpt-6.1-terra' => 'GPT-6.1 Terra (rápido)',
        'gpt-6-luna'    => 'GPT-6 Luna (económico)',
        'gpt-5.6-sol'   => 'GPT-5.6 Sol',
        'gpt-5.5'       => 'GPT-5.5',
    ];

    public const ANTHROPIC_MODELS = [
        'claude-opus-5-5'   => 'Claude Opus 5.5 (Recomendado)',
        'claude-fable-5-2'  => 'Claude Fable 5.2 (máxima calidad)',
        'claude-sonnet-5-5' => 'Claude Sonnet 5.5 (rápido)',
        'claude-haiku-5-5'  => 'Claude Haiku 5.5 (económico)',
    ];

    public const DEFAULT_OPENAI = 'gpt-6.1-sol';
    public const DEFAULT_ANTHROPIC = 'claude-opus-5-5';

    public static function provider(): ?string
    {
        $pref = Setting::get('ai_provider', 'openai');
        $order = $pref === 'anthropic' ? ['anthropic', 'openai'] : ['openai', 'anthropic'];
        foreach ($order as $p) {
            if (Setting::get("{$p}_key")) return $p;
        }
        return null;
    }

    public static function available(): bool
    {
        return self::provider() !== null;
    }

    /** Devuelve el texto de la respuesta o '' si falla. */
    public static function ask(string $prompt, int $maxTokens = 1000, int $timeout = 60): string
    {
        $provider = self::provider();
        if (!$provider) return '';
        $budget = max(2048, $maxTokens * 4);

        try {
            if ($provider === 'anthropic') {
                $r = Http::withHeaders([
                        'x-api-key'         => Setting::get('anthropic_key'),
                        'anthropic-version' => '2023-06-01',
                    ])->timeout($timeout)->post('https://api.anthropic.com/v1/messages', [
                        'model'      => Setting::get('anthropic_model', self::DEFAULT_ANTHROPIC) ?: self::DEFAULT_ANTHROPIC,
                        'max_tokens' => $budget,
                        'messages'   => [['role' => 'user', 'content' => $prompt]],
                    ]);
                if ($r->failed()) {
                    Log::warning('Anthropic API error', ['status' => $r->status(), 'body' => mb_substr($r->body(), 0, 500)]);
                    return '';
                }
                return trim(collect($r->json('content', []))->where('type', 'text')->pluck('text')->implode(''));
            }

            $r = Http::withToken(Setting::get('openai_key'))->timeout($timeout)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'                 => Setting::get('openai_model', self::DEFAULT_OPENAI) ?: self::DEFAULT_OPENAI,
                    'messages'              => [['role' => 'user', 'content' => $prompt]],
                    'max_completion_tokens' => $budget,
                ]);
            if ($r->failed()) {
                Log::warning('OpenAI API error', ['status' => $r->status(), 'body' => mb_substr($r->body(), 0, 500)]);
                return '';
            }
            return trim((string) $r->json('choices.0.message.content', ''));
        } catch (\Throwable $e) {
            Log::error('AI request failed: '.$e->getMessage());
            return '';
        }
    }

    /** Respuesta JSON decodificada (tolera ```json ... ```). */
    public static function askJson(string $prompt, int $maxTokens = 1000, int $timeout = 90): ?array
    {
        $text = self::ask($prompt, $maxTokens, $timeout);
        if ($text === '') return null;
        $text = trim(preg_replace('/```(?:json)?|```/', '', $text));
        if (($a = strpos($text, '{')) !== false && ($b = strrpos($text, '}')) !== false) {
            $text = substr($text, $a, $b - $a + 1);
        }
        $data = json_decode($text, true);
        return is_array($data) ? $data : null;
    }

    public static function stripQuotes(string $s): string
    {
        return trim($s, " \t\n\r\"'“”«»");
    }
}
