@extends('layouts.shop')

@section('title', $brand ? $brand->name . ' — HAUS OF TIME' : ($search ? __('shop.catalog.search_for', ['q' => $search]) . ' — HAUS OF TIME' : __('shop.meta.title')))
@section('canonical', $isHome ? route('home') : url()->current() . ($brand ? '?marca=' . $brand->slug : ($category ? '?categoria=' . $category->slug : '')))

@section('content')
<div class="hs-wrap">
  @if($search || $brand || $category)
    <div class="hs-heading">
      <h1>{{ $search ? __('shop.catalog.search_for', ['q' => $search]) : ($brand?->name ?? $category?->name) }}</h1>
      <a href="{{ route('shop.index') }}">{{ __('shop.catalog.empty_cta') }}</a>
    </div>
  @else
    <h1 class="hs-sr">{{ __('shop.catalog.title') }}</h1>
  @endif

  @if($watches->total() > 0 || $brand || $category || request('certificado'))
  <div class="hs-filters">
    <nav class="hs-chips" aria-label="{{ __('shop.nav.brands') }}">
      <a href="{{ route('shop.index', array_filter(['orden' => request('orden'), 'certificado' => request('certificado')])) }}" class="hs-chip {{ !$brand && !$category ? 'on' : '' }}">{{ __('shop.catalog.all') }}</a>
      @foreach($brands as $b)
        <a href="{{ route('shop.index', array_filter(['marca' => $b->slug, 'orden' => request('orden'), 'certificado' => request('certificado')])) }}" class="hs-chip {{ $brand?->id === $b->id ? 'on' : '' }}">{{ $b->name }}</a>
      @endforeach
      @foreach($categories as $c)
        <a href="{{ route('shop.index', array_filter(['categoria' => $c->slug, 'orden' => request('orden')])) }}" class="hs-chip {{ $category?->id === $c->id ? 'on' : '' }}">{{ $c->name }}</a>
      @endforeach
    </nav>
    <a href="{{ request()->fullUrlWithQuery(['certificado' => request('certificado') ? null : 1, 'page' => null]) }}" class="hs-chip hs-chip--toggle {{ request('certificado') ? 'on' : '' }}">
      <img src="/images/shop/seal.png" alt="" width="18" height="18">{{ __('shop.nav.certified_only') }}
    </a>
    <form class="hs-sort" method="GET" action="{{ $isHome ? route('home') : route('shop.index') }}">
      @foreach(request()->except(['orden', 'page']) as $k => $v)@if(is_string($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif @endforeach
      <label class="hs-sr" for="hsSort">{{ __('shop.catalog.sort') }}</label>
      <select id="hsSort" name="orden" onchange="this.form.submit()">
        @foreach(['destacados' => 'sort_featured', 'recientes' => 'sort_recent', 'precio-asc' => 'sort_price_asc', 'precio-desc' => 'sort_price_desc'] as $val => $label)
          <option value="{{ $val }}" @selected($sort === $val)>{{ __('shop.catalog.' . $label) }}</option>
        @endforeach
      </select>
    </form>
    <span class="hs-count">{{ $watches->total() === 1 ? __('shop.catalog.result') : __('shop.catalog.results', ['count' => $watches->total()]) }}</span>
  </div>
  @endif

  @if($watches->count())
    <div class="hs-grid" id="hsGrid">
      @include('shop.partials.cards', ['watches' => $watches])
    </div>
    @if($watches->hasMorePages())
      <div class="hs-more"><a href="{{ $watches->nextPageUrl() }}" class="hs-btn hs-btn--ghost" data-load-more data-next="{{ $watches->nextPageUrl() }}">{{ __('shop.catalog.load_more') }}</a></div>
    @endif
  @else
    <div class="hs-empty">
      <p>{{ ($search || $brand || $category) ? __('shop.catalog.empty') : __('shop.catalog.coming') }}</p>
      @if($search || $brand || $category)<a href="{{ route('shop.index') }}" class="hs-btn">{{ __('shop.catalog.empty_cta') }}</a>@endif
    </div>
  @endif
</div>
@endsection
