<nav class="hs-acc__nav" aria-label="{{ __('shop.account.title') }}">
  <a href="{{ route('account.orders') }}" class="{{ request()->routeIs('account.orders', 'account.order') ? 'on' : '' }}">{{ __('shop.account.orders') }}</a>
  <a href="{{ route('favorites.index') }}">{{ __('shop.account.favorites') }}</a>
  <a href="{{ route('account.addresses') }}" class="{{ request()->routeIs('account.addresses') ? 'on' : '' }}">{{ __('shop.account.addresses') }}</a>
  <a href="{{ route('account.profile') }}" class="{{ request()->routeIs('account.profile') ? 'on' : '' }}">{{ __('shop.account.profile') }}</a>
  @if(auth()->user()->isSeller())<a href="{{ route('seller.dashboard') }}">{{ __('shop.nav.seller_area') }} ↗</a>@endif
  <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" style="width:100%">{{ __('shop.nav.logout') }}</button></form>
</nav>
