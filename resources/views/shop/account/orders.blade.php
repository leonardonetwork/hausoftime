@extends('layouts.shop')
@section('title', __('shop.account.orders') . ' — HAUS OF TIME')
@section('content')
<div class="hs-wrap">
  <h1 class="hs-page-title"><small>{{ __('shop.account.hello', ['name' => auth()->user()->first_name ?: auth()->user()->name]) }}</small>{{ __('shop.account.orders') }}</h1>
  <div class="hs-acc">
    @include('shop.account._nav')
    <div class="hs-panel">
      @if($orders->count())
        <table class="hs-table">
          <thead><tr><th>{{ __('shop.account.order') }}</th><th>{{ __('shop.account.date') }}</th><th></th><th>{{ __('shop.account.status') }}</th><th style="text-align:right">{{ __('shop.account.total') }}</th></tr></thead>
          <tbody>
          @foreach($orders as $o)
            <tr onclick="location='{{ route('account.order', $o) }}'" style="cursor:pointer">
              <td><a href="{{ route('account.order', $o) }}"><strong>{{ $o->number }}</strong></a></td>
              <td>{{ $o->created_at->format('d/m/Y') }}</td>
              <td style="color:var(--hs-muted);font-size:12.5px">{{ $o->items->map->title->implode(', ') }}</td>
              <td><span class="hs-pill {{ $o->status_class }}">{{ $o->status_label }}</span></td>
              <td style="text-align:right;font-weight:600">{{ \App\Models\Watch::money($o->total, true) }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
        <div style="margin-top:20px">{{ $orders->links() }}</div>
      @else
        <div style="text-align:center;padding:30px 0">
          <p style="color:var(--hs-muted);margin-bottom:18px">{{ __('shop.account.no_orders') }}</p>
          <a href="{{ route('shop.index') }}" class="hs-btn">{{ __('shop.bag.continue') }}</a>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
