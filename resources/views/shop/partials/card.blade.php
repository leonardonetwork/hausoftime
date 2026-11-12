@php
  /** @var \App\Models\Watch $watch */
  $brandRow = $brandMap[$watch->brand] ?? null;
  $isFav = in_array($watch->id, $favoriteIds ?? [], true);
  $isCmp = in_array($watch->id, $compareIds ?? \App\Http\Controllers\Shop\CompareController::ids(), true);
  $urls = $watch->image_urls;
  $sold = $watch->status === 'sold';
  $priceFmt = fn ($v) => \App\Models\Watch::display($v);
@endphp
<article class="hs-card {{ $sold ? 'hs-card--sold' : '' }}">
  @if($watch->is_certified)
    <img class="hs-seal" src="/images/shop/seal.png" alt="{{ __('shop.card.certified') }}" title="{{ __('shop.card.certified') }}" width="80" height="80" loading="lazy">
  @endif
  <a href="{{ route('shop.show', $watch->slug) }}" class="hs-card__media" aria-label="{{ $watch->title }}">
    <span class="hs-card__brand">
      @if($brandRow?->logo_url)<img src="{{ $brandRow->logo_url }}" alt="{{ $watch->brand }}" loading="lazy">@else{{ $brandRow?->name ?? $watch->brand }}@endif
    </span>
    @if($urls)
      <img class="hs-card__img" src="{{ $urls[0] }}" alt="{{ $watch->title }}" loading="lazy" decoding="async">
      @if(isset($urls[1]))<img class="hs-card__img alt" src="{{ $urls[1] }}" alt="" loading="lazy" decoding="async" aria-hidden="true">@endif
    @endif
    @if($sold)<span class="hs-card__tag">{{ __('shop.card.sold') }}</span>
    @elseif($watch->isReserved())<span class="hs-card__tag">{{ __('shop.card.reserved') }}</span>@endif
  </a>
  <div class="hs-card__body">
    <a href="{{ route('shop.show', $watch->slug) }}" class="hs-card__info">
      <h3 class="hs-card__title">{{ $watch->model }}@if($watch->reference)<span>Ref. {{ $watch->reference }}</span>@endif</h3>
      <div class="hs-card__lines">
        @foreach(array_slice(array_values(array_diff($watch->card_lines, ['Ref. ' . $watch->reference])), 0, 2) as $line)<div>{{ $line }}</div>@endforeach
      </div>
      <div class="hs-card__price">
        @if($watch->has_discount)<s>{{ $priceFmt($watch->price) }}</s>@endif{{ $priceFmt($watch->effective_price) }}
      </div>
    </a>
    <div class="hs-card__side">
      <button type="button" class="hs-heart {{ $isFav ? 'on' : '' }}" data-fav="{{ route('favorites.toggle', $watch) }}" aria-pressed="{{ $isFav ? 'true' : 'false' }}" aria-label="{{ $isFav ? __('shop.card.unfavorite') : __('shop.card.favorite') }}">
        <svg viewBox="0 0 28 26"><path d="M14 24.2S2.6 17.4 1.6 9.7C.9 4.6 4.4 1.5 8 1.5c2.6 0 4.6 1.5 6 3.6 1.4-2.1 3.4-3.6 6-3.6 3.6 0 7.1 3.1 6.4 8.2-1 7.7-12.4 14.5-12.4 14.5z"/></svg>
      </button>
      <button type="button" class="hs-cmpbtn {{ $isCmp ? 'on' : '' }}" data-compare="{{ route('compare.toggle', $watch) }}" aria-pressed="{{ $isCmp ? 'true' : 'false' }}" aria-label="{{ $isCmp ? __('shop.compare.remove') : __('shop.compare.add') }}" title="{{ $isCmp ? __('shop.compare.remove') : __('shop.compare.add') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h15M15 4l4 4-4 4M20 16H5M9 12l-4 4 4 4"/></svg>
      </button>
      @if($watch->location_label)
        <button type="button" class="hs-pin" aria-label="{{ __('shop.card.location') }}: {{ $watch->location_label }}">
          <svg viewBox="0 0 22 28"><path fill="currentColor" d="M11 0C4.9 0 0 4.8 0 10.8 0 18.6 11 28 11 28s11-9.4 11-17.2C22 4.8 17.1 0 11 0zm0 15.2a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9z"/><circle cx="11" cy="10.7" r="2.2" fill="currentColor"/></svg>
          <span class="hs-pin__tip">{{ $watch->location_label }}</span>
        </button>
      @endif
    </div>
  </div>
</article>
