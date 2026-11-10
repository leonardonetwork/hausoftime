{{-- ============================================================
     resources/views/pages/early-access-success.blade.php
============================================================ --}}
@extends('layouts.app')
@section('title', __('messages.early_access.success_title'))
@section('content')
<div class="page-hero" style="min-height:80vh;display:flex;align-items:center;">
  <div class="container" style="text-align:center;">
    <div style="font-size:3rem;margin-bottom:24px;">✓</div>
    <div class="page-hero__label">{{ __('messages.early_access.step_label') }}</div>
    <h1 class="page-hero__title">{{ __('messages.early_access.success_title') }}</h1>
    <p class="page-hero__desc" style="margin:20px auto 0;max-width:480px;">
      @if($type === 'seller')
        {{ __('messages.early_access.success_seller') }}
      @else
        {{ __('messages.early_access.success_buyer') }}
      @endif
    </p>
    @if($email)
    <p style="margin-top:20px;font-size:.85rem;color:rgba(255,255,255,.6);">
      {{ __('messages.early_access.success_email') }}
      <strong style="color:var(--gold);">{{ $email }}</strong>
    </p>
    @endif
    <a href="{{ route('home') }}" class="btn btn--outline" style="margin-top:36px;">
      {{ __('messages.early_access.success_back') }}
    </a>
  </div>
</div>
@endsection
