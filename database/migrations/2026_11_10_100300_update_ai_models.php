<?php

use App\Services\AiClient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Porta i modelli IA salvati (gpt-4o, claude-*-4-x…) ai modelli attuali dei menu. */
    public function up(): void
    {
        foreach (['openai_model' => AiClient::OPENAI_MODELS, 'anthropic_model' => AiClient::ANTHROPIC_MODELS] as $key => $models) {
            $row = DB::table('settings')->where('key', $key)->first();
            $default = $key === 'openai_model' ? AiClient::DEFAULT_OPENAI : AiClient::DEFAULT_ANTHROPIC;
            if (!$row) {
                DB::table('settings')->insert(['key' => $key, 'value' => $default, 'group' => 'ai', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()]);
            } elseif (!array_key_exists((string) $row->value, $models)) {
                DB::table('settings')->where('key', $key)->update(['value' => $default, 'updated_at' => now()]);
            }
        }
        if (!DB::table('settings')->where('key', 'watch_approval_mode')->exists()) {
            DB::table('settings')->insert(['key' => 'watch_approval_mode', 'value' => 'manual', 'group' => 'shop', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach (['openai_model', 'anthropic_model', 'watch_approval_mode'] as $k) {
            Cache::forget("setting_{$k}");
        }
    }

    public function down(): void {}
};
