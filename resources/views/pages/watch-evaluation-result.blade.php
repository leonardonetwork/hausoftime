@extends('layouts.app')
@section('title', __('messages.evaluation.result_title'))

@section('content')

<div class="page-hero">
  <div class="page-hero__inner">
    <div class="page-hero__label">{{ __('messages.evaluation.subtitle') }}</div>
    <h1 class="page-hero__title">{{ __('messages.evaluation.result_title') }}</h1>
    <p class="page-hero__desc">
      {{ $evaluation->brand }} {{ $evaluation->model }}
      @if($evaluation->reference) · {{ $evaluation->reference }}@endif
    </p>
  </div>
</div>

<div class="form-page">
  <div class="container" style="max-width:760px;">

    @if(!empty($aiResult['error']))
      <div class="alert alert--error">
        <strong>{{ __('messages.evaluation.error_title') }}</strong><br>
        {{ __('messages.evaluation.error_desc') }}
      </div>
      <a href="{{ !empty($fromPanel) ? route('seller.dashboard') : route('evaluation.index') }}" class="btn btn--form" style="width:auto;min-height:auto;padding:14px 32px;display:inline-flex;">
        {{ !empty($fromPanel)
            ? (app()->getLocale() === 'es' ? '← Volver al panel' : '← Back to dashboard')
            : __('messages.evaluation.back_btn') }}
      </a>

    @else

      {{-- Estimated value --}}
      <div style="background:#fff;border:1px solid var(--light-border);padding:48px 40px;text-align:center;margin-bottom:20px;">
        <div style="font-family:var(--font-body);font-size:10px;font-weight:600;letter-spacing:.35em;text-transform:uppercase;color:var(--light-subtle);margin-bottom:14px;">
          {{ __('messages.evaluation.estimated_range') }}
        </div>
        <div style="font-family:var(--font-display);font-size:clamp(2rem,5vw,3.5rem);font-weight:300;color:var(--light-text);line-height:1;">
          @if(!empty($aiResult['value_min']) && !empty($aiResult['value_max']))
            {{ number_format($aiResult['value_min'], 0, ',', '.') }}
            —
            {{ number_format($aiResult['value_max'], 0, ',', '.') }} €
          @else N/A
          @endif
        </div>

        @if(!empty($aiResult['market_trend']))
        <div style="margin-top:20px;display:inline-flex;align-items:center;gap:8px;padding:7px 18px;font-family:var(--font-body);font-size:12px;font-weight:500;letter-spacing:.08em;
          {{ $aiResult['market_trend'] === 'rising' ? 'background:#eaf7ef;color:#2d6a46' : ($aiResult['market_trend'] === 'falling' ? 'background:#fdf5f5;color:#922b21' : 'background:#eaf2fb;color:#1a5276') }}">
          {{ __('messages.evaluation.trend_' . $aiResult['market_trend']) }}
          @if(!empty($aiResult['trend_percentage']))
            <span style="opacity:.7">{{ $aiResult['trend_percentage'] > 0 ? '+' : '' }}{{ $aiResult['trend_percentage'] }}%</span>
          @endif
        </div>
        @endif
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

        {{-- Market summary --}}
        @if(!empty($aiResult['summary']))
        <div style="background:#fff;border:1px solid var(--light-border);padding:28px;">
          <div style="font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:var(--light-subtle);margin-bottom:12px;">
            {{ __('messages.evaluation.summary') }}
          </div>
          <p style="font-family:var(--font-body);font-size:14px;font-weight:300;line-height:1.75;color:var(--light-muted);">{{ $aiResult['summary'] }}</p>
        </div>
        @endif

        {{-- Recommendation --}}
        @if(!empty($aiResult['recommendation']))
        <div style="background:#fff;border:1px solid var(--light-border);padding:28px;">
          <div style="font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:var(--light-subtle);margin-bottom:12px;">
            {{ __('messages.evaluation.recommendation') }}
          </div>
          <p style="font-family:var(--font-body);font-size:14px;font-weight:300;line-height:1.75;color:var(--light-muted);">{{ $aiResult['recommendation'] }}</p>
        </div>
        @endif
      </div>

      {{-- Factors --}}
      @if(!empty($aiResult['factors']))
      <div style="background:#fff;border:1px solid var(--light-border);padding:28px;margin-bottom:20px;">
        <div style="font-family:var(--font-body);font-size:10px;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:var(--light-subtle);margin-bottom:16px;">
          {{ __('messages.evaluation.factors') }}
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;">
          @foreach($aiResult['factors'] as $factor)
          <div style="display:flex;gap:12px;align-items:flex-start;font-family:var(--font-body);font-size:14px;font-weight:300;color:var(--light-muted);line-height:1.6;">
            <span style="color:var(--light-text);flex-shrink:0;margin-top:2px;">·</span>
            {{ $factor }}
          </div>
          @endforeach
        </div>
        @if(!empty($aiResult['confidence']))
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--light-border);font-family:var(--font-body);font-size:11px;color:var(--light-subtle);">
          {{ __('messages.evaluation.confidence') }}:
          <strong style="color:var(--light-text);">{{ __('messages.evaluation.conf_' . $aiResult['confidence']) }}</strong>
        </div>
        @endif
      </div>
      @endif

      {{-- Disclaimer --}}
      <p style="font-family:var(--font-body);font-size:11px;font-weight:300;color:var(--light-subtle);line-height:1.7;margin-bottom:28px;">
        {{ __('messages.evaluation.disclaimer') }}
      </p>

      {{-- CTA — fondo nero, scritta bianca, pieno --}}
      @if(!empty($fromPanel))
        <a href="{{ route('seller.watches.create') }}"
           style="display:flex;align-items:center;justify-content:center;width:100%;min-height:72px;background:#111111;color:#ffffff;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.35em;text-transform:uppercase;text-decoration:none;transition:background .25s ease;">
          {{ app()->getLocale() === 'es' ? 'Subir este reloj' : 'List this watch' }}
        </a>
        <a href="{{ route('seller.dashboard') }}"
           style="display:flex;align-items:center;justify-content:center;width:100%;min-height:56px;margin-top:12px;border:1px solid var(--light-border);color:var(--light-muted);font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.28em;text-transform:uppercase;text-decoration:none;">
          ← {{ app()->getLocale() === 'es' ? 'Volver al panel' : 'Back to dashboard' }}
        </a>
      @else
        <a href="{{ route('early-access.index') }}"
           style="display:flex;align-items:center;justify-content:center;width:100%;min-height:72px;background:#111111;color:#ffffff;font-family:var(--font-body);font-size:11px;font-weight:400;letter-spacing:.35em;text-transform:uppercase;text-decoration:none;transition:background .25s ease;">
          {{ __('messages.evaluation.cta_sell') }}
        </a>
      @endif

    @endif
  </div>
</div>

@endsection
