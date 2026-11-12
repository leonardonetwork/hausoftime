@extends('layouts.shop')
@section('title', __('shop.compare.title') . ' — HAUS OF TIME')
@section('content')
@php
  $max = \App\Http\Controllers\Shop\CompareController::MAX;
  $priceFmt = fn ($v) => \App\Models\Watch::display($v);
  $brandName = fn ($w) => ($brandMap[$w->brand] ?? null)?->name ?? $w->brand;
  $rows = [
    'price'      => [__('shop.compare.price'), fn ($w) => $priceFmt($w->effective_price)],
    'condition'  => [__('shop.product.condition'), fn ($w) => $w->condition_label],
    'box_papers' => [__('shop.product.box_papers'), fn ($w) => $w->box_papers_label],
    'year'       => [__('shop.product.year'), fn ($w) => $w->manufacture_year],
    'reference'  => [__('shop.product.reference'), fn ($w) => $w->reference],
    'case_size'  => [__('shop.product.case_size'), fn ($w) => $w->case_size],
    'material'   => [__('shop.product.material'), fn ($w) => $w->material],
    'category'   => [__('shop.product.category'), fn ($w) => $w->category],
    'certified'  => [__('shop.compare.certified'), fn ($w) => $w->is_certified ? __('shop.compare.yes') : __('shop.compare.no')],
    'location'   => [__('shop.product.location'), fn ($w) => $w->location_label],
  ];
  $n = $watches->count();
  $cols = $n + ($n < $max ? 1 : 0);
@endphp
<div class="hs-wrap hs-cmp-page">
  <div class="hs-cmp-head">
    <h1 class="hs-page-title"><small>HAUS OF TIME</small>{{ __('shop.compare.title') }}</h1>
    @if($n)
      <div class="hs-cmp-tools">
        @if($n > 1)
          <label class="hs-switch"><input type="checkbox" id="hsCmpDiff"><span class="hs-switch__track"></span>{{ __('shop.compare.only_diff') }}</label>
        @endif
        <form method="POST" action="{{ route('compare.clear') }}">@csrf @method('DELETE')
          <button type="submit" class="hs-btn hs-btn--ghost hs-btn--sm">{{ __('shop.compare.clear') }}</button>
        </form>
      </div>
    @endif
  </div>

  @if(!$n)
    <div class="hs-empty"><p>{{ __('shop.compare.empty') }}</p>
      <a href="{{ route('shop.index') }}" class="hs-btn">{{ __('shop.nav.all_watches') }}</a></div>
  @else
    <p class="hs-cmp-intro">{{ $n === 1 ? __('shop.compare.one') : __('shop.compare.intro', ['max' => $max]) }}</p>

    <div class="hs-cmp" id="hsCmp">
      <div class="hs-cmp__grid" style="--cols:{{ $cols }}">
        {{-- Intestazioni: una colonna per reloj --}}
        <div class="hs-cmp__corner" aria-hidden="true"><strong>{{ $n }}<span>/{{ $max }}</span></strong>@if($n > 1)<em>{{ app()->getLocale() === 'en' ? 'Swipe' : 'Desliza' }} →</em>@endif</div>
        @foreach($watches as $w)
          @php $brandRow = $brandMap[$w->brand] ?? null; @endphp
          <div class="hs-cmp__item">
            <form method="POST" action="{{ route('compare.toggle', $w) }}" class="hs-cmp__x">@csrf
              <button type="submit" aria-label="{{ __('shop.compare.remove') }}: {{ $w->title }}" title="{{ __('shop.compare.remove') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 6l12 12M18 6L6 18"/></svg>
              </button>
            </form>
            <a href="{{ route('shop.show', $w->slug) }}" class="hs-cmp__media">
              @if($w->is_certified)<img class="hs-cmp__seal" src="/images/shop/seal.png" alt="{{ __('shop.card.certified') }}" width="46" height="46">@endif
              @if($w->cover_url)<img src="{{ $w->cover_url }}" alt="{{ $w->title }}" loading="lazy" decoding="async">@endif
              @if($w->status === 'sold')<span class="hs-card__tag">{{ __('shop.card.sold') }}</span>
              @elseif($w->isReserved())<span class="hs-card__tag">{{ __('shop.card.reserved') }}</span>@endif
            </a>
            <p class="hs-cmp__brand">
              @if($brandRow?->logo_url)<img src="{{ $brandRow->logo_url }}" alt="{{ $brandName($w) }}">@else{{ $brandName($w) }}@endif
            </p>
            <h2 class="hs-cmp__model"><a href="{{ route('shop.show', $w->slug) }}">{{ $w->model }}</a></h2>
            <p class="hs-cmp__price">@if($w->has_discount)<s>{{ $priceFmt($w->price) }}</s>@endif{{ $priceFmt($w->effective_price) }}</p>
            <a href="{{ route('shop.show', $w->slug) }}" class="hs-btn hs-btn--sm hs-cmp__cta">{{ __('shop.compare.view') }}</a>
          </div>
        @endforeach
        @if($n < $max)
          <a href="{{ route('shop.index') }}" class="hs-cmp__add">
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 5v14M5 12h14"/></svg></span>
            {{ __('shop.compare.add_more') }}
          </a>
        @endif

        {{-- Righe della ficha técnica --}}
        @foreach($rows as $key => [$label, $get])
          @php
            $vals = $watches->map(fn ($w) => filled($v = $get($w)) ? (string) $v : null);
            $same = $n > 1 && $vals->unique()->count() === 1;
          @endphp
          @continue($vals->filter()->isEmpty())
          <div class="hs-cmp__row {{ $same ? 'is-same' : '' }}" role="row">
            <div class="hs-cmp__label" role="rowheader">{{ $label }}</div>
            @foreach($vals as $v)
              <div class="hs-cmp__cell {{ $key === 'price' ? 'is-price' : '' }}" role="cell">{{ $v ?? '—' }}</div>
            @endforeach
            @if($n < $max)<div class="hs-cmp__cell is-pad" aria-hidden="true"></div>@endif
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var t = document.getElementById('hsCmpDiff'), c = document.getElementById('hsCmp');
    if (t && c) t.addEventListener('change', function () { c.classList.toggle('only-diff', t.checked); });
  })();
</script>
@endpush
