@extends('layouts.seller')
@php $es = app()->getLocale() === 'es'; $m = fn ($v) => \App\Models\Watch::money($v, true); $order = $item->order; @endphp
@section('title', $order->number)
@section('seller-content')
<a href="{{ route('seller.sales') }}" class="table-link">← {{ __('shop.seller.sales') }}</a>
<div class="panel-header" style="margin:14px 0 24px">
  <div><h1 class="page-title">{{ $item->title }}</h1>
  <p class="page-subtitle" style="margin-bottom:0">{{ $order->number }} · {{ $es ? 'pagado el' : 'paid on' }} {{ $order->paid_at?->format('d/m/Y H:i') }}</p></div>
  <span class="badge {{ $item->status_class }}" style="font-size:11px;padding:5px 12px">{{ $item->status_label }}</span>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;align-items:start">
  <div>
    @if($item->status === 'awaiting_shipment')
    <div class="card" style="border-color:var(--s-gold)">
      <div class="table-title" style="margin-bottom:6px">{{ $es ? 'Registrar envío' : 'Record shipment' }}</div>
      <p style="font-size:13px;color:var(--s-muted);margin-bottom:18px">{{ $es ? 'Envía antes del' : 'Ship before' }} <strong style="color:var(--s-text)">{{ $item->ship_by?->format('d/m/Y') }}</strong>. {{ $es ? 'Usa FedEx con seguro por el valor íntegro del reloj.' : 'Use FedEx insured for the full value of the watch.' }}</p>
      <form method="POST" action="{{ route('seller.sales.ship', $item) }}">
        @csrf
        <div style="display:grid;grid-template-columns:140px 1fr;gap:12px">
          <div class="form-group"><label class="form-label">{{ $es ? 'Transportista' : 'Carrier' }}</label>
            <select name="carrier" class="form-control">@foreach(['fedex' => 'FedEx', 'dhl' => 'DHL', 'ups' => 'UPS', 'other' => $es ? 'Otro' : 'Other'] as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
          <div class="form-group"><label class="form-label">{{ $es ? 'Número de seguimiento' : 'Tracking number' }}</label><input name="tracking_number" class="form-control" required minlength="6" value="{{ old('tracking_number') }}"></div>
        </div>
        <div class="form-group"><label class="form-label">{{ $es ? 'URL de seguimiento (solo si no es FedEx/DHL/UPS)' : 'Tracking URL (only if not FedEx/DHL/UPS)' }}</label><input name="tracking_url" type="url" class="form-control" value="{{ old('tracking_url') }}"></div>
        <label style="display:flex;gap:10px;font-size:13px;color:var(--s-muted);margin:6px 0 18px;cursor:pointer"><input type="checkbox" name="confirm_insured" value="1" required> {{ $es ? 'Confirmo que el envío está asegurado por el valor íntegro del reloj.' : 'I confirm the shipment is insured for the full value of the watch.' }}</label>
        <button class="btn btn--dark btn--lg">{{ $es ? 'Confirmar envío' : 'Confirm shipment' }}</button>
      </form>
    </div>
    @elseif($item->tracking_number)
    <div class="card">
      <div class="table-title" style="margin-bottom:10px">{{ $es ? 'Envío' : 'Shipment' }}</div>
      <p style="font-size:13px">{{ strtoupper($item->carrier) }} · <strong>{{ $item->tracking_number }}</strong> @if($item->tracking_link)· <a href="{{ $item->tracking_link }}" target="_blank" rel="noopener" style="color:var(--s-gold)">{{ $es ? 'Seguir' : 'Track' }}</a>@endif</p>
      <p style="font-size:12px;color:var(--s-muted);margin-top:6px">{{ $es ? 'Enviado el' : 'Shipped on' }} {{ $item->shipped_at?->format('d/m/Y') }}
        @if(!$item->released_at && $item->release_due_at) · {{ $es ? 'liberación automática el' : 'automatic release on' }} {{ $item->release_due_at->format('d/m/Y') }}@endif</p>
    </div>
    @endif

    <div class="card">
      <div class="table-title" style="margin-bottom:10px">{{ $es ? 'Dirección de envío del comprador' : 'Buyer shipping address' }}</div>
      <p style="white-space:pre-line;font-size:14px;line-height:1.6;color:var(--s-text)">{{ $order->shipping_address_text }}</p>
      @if($order->customer_notes)<p style="font-size:12px;color:var(--s-muted);margin-top:12px"><strong>{{ $es ? 'Notas:' : 'Notes:' }}</strong> {{ $order->customer_notes }}</p>@endif
    </div>
  </div>

  <div class="card">
    <div class="table-title" style="margin-bottom:14px">{{ $es ? 'Liquidación' : 'Payout' }}</div>
    <table class="data-table" style="font-size:13px">
      <tr><td>{{ $es ? 'Precio de venta' : 'Sale price' }}</td><td style="text-align:right">{{ $m($item->price) }}</td></tr>
      <tr><td>{{ $es ? 'Comisión' : 'Commission' }} ({{ rtrim(rtrim(number_format($item->commission_rate, 2, ',', '.'), '0'), ',') }} %)</td><td style="text-align:right">−{{ $m($item->commission_amount) }}</td></tr>
      <tr><td>{{ $es ? 'Envío + seguro (pagado por el comprador)' : 'Shipping + insurance (paid by buyer)' }}</td><td style="text-align:right">{{ $m($item->shipping + $item->insurance) }}</td></tr>
      <tr><td class="td-strong">{{ $es ? 'Recibirás' : 'You receive' }}</td><td class="td-strong" style="text-align:right">{{ $m($item->seller_payout) }}</td></tr>
    </table>
    <p style="font-size:12px;color:var(--s-muted);margin-top:14px;line-height:1.6">
      @if($item->released_at)
        {{ $es ? 'Transferido a tu cuenta Stripe el' : 'Transferred to your Stripe account on' }} {{ $item->released_at->format('d/m/Y') }}.
      @else
        {{ $es ? 'Los fondos se liberan cuando el comprador confirma la recepción o al vencer el periodo de protección, sin incidencias abiertas.' : 'Funds are released when the buyer confirms receipt or when the protection period ends with no open issues.' }}
      @endif
    </p>
  </div>
</div>
@endsection
