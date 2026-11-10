@extends('layouts.admin')
@section('title', $order->number)
@php $m = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €'; @endphp
@section('admin-content')
<a href="{{ route('admin.orders.index') }}" style="font-size:11px;color:var(--a-subtle);letter-spacing:.08em;text-transform:uppercase">← Pedidos</a>
<div class="panel-header" style="margin-top:12px">
  <div>
    <div class="page-title">{{ $order->number }} <span class="badge {{ $order->status_class }}" style="vertical-align:middle">{{ $order->status_label }}</span></div>
    <p class="page-subtitle" style="margin-bottom:0">Creado {{ $order->created_at->format('d/m/Y H:i') }} @if($order->paid_at)· pagado {{ $order->paid_at->format('d/m/Y H:i') }} ({{ $order->payment_method ?? 'stripe' }})@endif · idioma {{ strtoupper($order->locale) }}</p>
  </div>
  <div style="display:flex;gap:8px">
    @if($order->stripe_payment_intent)
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://dashboard.stripe.com/{{ \App\Models\Setting::get('stripe_mode') === 'live' ? '' : 'test/' }}payments/{{ $order->stripe_payment_intent }}">Ver en Stripe ↗</a>
    @endif
    @if($order->status === 'pending_payment')
      <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('¿Cancelar el pedido y liberar los relojes?')">@csrf<button class="btn btn--danger btn--sm">Cancelar pedido</button></form>
    @endif
  </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);gap:20px;align-items:start;margin-top:20px">
  <div style="display:grid;gap:20px">
    @foreach($order->items as $item)
    <div class="card">
      <div style="display:flex;gap:18px;align-items:flex-start">
        @if($item->image_url)<img src="{{ $item->image_url }}" style="width:84px;height:100px;object-fit:cover;border-radius:3px;flex:none">@endif
        <div style="flex:1;min-width:0">
          <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div><div style="font-weight:600;color:var(--a-text)">{{ $item->title }}</div>
              <div style="font-size:12px;color:var(--a-subtle)">{{ $item->reference ? 'Ref. ' . $item->reference . ' · ' : '' }}@if($item->watch)<a href="{{ route('admin.watches.show', $item->watch) }}" style="color:var(--a-gold)">ficha #{{ $item->watch_id }}</a>@endif</div></div>
            <span class="badge {{ $item->status_class }}" style="height:fit-content">{{ $item->status_label }}</span>
          </div>
          <dl class="kv" style="margin-top:14px">
            <dt>Vendedor</dt><dd>{{ $item->seller?->name }} · {{ $item->seller?->email }} @if($item->seller)<span class="badge {{ $item->seller->hasPayoutsReady() ? 'badge-success' : 'badge-warning' }}">{{ $item->seller->hasPayoutsReady() ? 'Stripe activo' : 'Stripe no activo' }}</span>@endif</dd>
            <dt>Precio</dt><dd>{{ $m($item->price) }} @if($item->discount > 0)· descuento −{{ $m($item->discount) }}@endif</dd>
            <dt>Envío + seguro</dt><dd>{{ $m($item->shipping) }} + {{ $m($item->insurance) }} <span style="color:var(--a-subtle)">({{ $item->shipping_rate_name }})</span></dd>
            <dt>Comisión</dt><dd>{{ rtrim(rtrim(number_format($item->commission_rate, 2, ',', '.'), '0'), ',') }} % · {{ $m($item->commission_amount) }}</dd>
            <dt>A liquidar</dt><dd style="font-weight:600">{{ $m($item->seller_payout) }} @if($item->stripe_transfer_id)<span style="color:var(--a-subtle);font-size:11px">({{ $item->stripe_transfer_id }})</span>@endif</dd>
            @if($item->ship_by && !$item->shipped_at)<dt>Enviar antes de</dt><dd style="{{ $item->ship_by->isPast() ? 'color:var(--a-danger)' : '' }}">{{ $item->ship_by->format('d/m/Y') }}</dd>@endif
            @if($item->tracking_number)<dt>Seguimiento</dt><dd>{{ strtoupper($item->carrier) }} {{ $item->tracking_number }} @if($item->tracking_link)<a href="{{ $item->tracking_link }}" target="_blank" style="color:var(--a-gold)">↗</a>@endif · {{ $item->shipped_at?->format('d/m/Y') }}</dd>@endif
            @if($item->release_due_at && !$item->released_at)<dt>Liberación auto.</dt><dd>{{ $item->release_due_at->format('d/m/Y') }}</dd>@endif
            @if($item->released_at)<dt>Liberado</dt><dd>{{ $item->released_at->format('d/m/Y H:i') }}</dd>@endif
            @if($item->refunded_at)<dt>Reembolsado</dt><dd>{{ $m($item->refunded_amount) }} · {{ $item->refunded_at->format('d/m/Y') }}</dd>@endif
            @if($item->dispute_reason)<dt>Incidencia</dt><dd style="color:var(--a-warning)">{{ $item->dispute_reason }}</dd>@endif
          </dl>

          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px">
            @if($item->status === 'awaiting_shipment')
              <details><summary class="btn btn--ghost btn--sm">Registrar envío</summary>
                <form method="POST" action="{{ route('admin.orders.ship', [$order, $item]) }}" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">@csrf
                  <select name="carrier" class="form-control" style="width:110px"><option value="fedex">FedEx</option><option value="dhl">DHL</option><option value="ups">UPS</option><option value="other">Otro</option></select>
                  <input name="tracking_number" class="form-control" style="width:200px" placeholder="Nº seguimiento" required>
                  <button class="btn btn--primary btn--sm">Guardar</button>
                </form>
              </details>
            @endif
            @if(in_array($item->status, ['shipped', 'received', 'disputed'], true) && !$item->released_at)
              <form method="POST" action="{{ route('admin.orders.release', [$order, $item]) }}" onsubmit="return confirm('¿Transferir {{ $m($item->seller_payout) }} al vendedor ahora?')">@csrf<button class="btn btn--success btn--sm">Liberar fondos</button></form>
            @endif
            @if($item->status === 'disputed')
              <details><summary class="btn btn--ghost btn--sm">Cerrar incidencia</summary>
                <form method="POST" action="{{ route('admin.orders.close-dispute', [$order, $item]) }}" style="margin-top:10px">@csrf
                  <textarea name="note" class="form-control" placeholder="Resolución (queda en el historial)" required></textarea>
                  <button class="btn btn--primary btn--sm" style="margin-top:8px">Cerrar y reanudar plazo</button>
                </form>
              </details>
            @endif
            @if($order->paid_at && !in_array($item->status, ['refunded', 'cancelled'], true))
              <details><summary class="btn btn--danger btn--sm">Reembolsar</summary>
                <form method="POST" action="{{ route('admin.orders.refund', [$order, $item]) }}" style="margin-top:10px;display:grid;gap:8px;max-width:420px" onsubmit="return confirm('¿Emitir el reembolso en Stripe? Esta acción no se puede deshacer.')">@csrf
                  <input name="amount" type="number" step="0.01" min="0.5" max="{{ $item->line_total }}" class="form-control" placeholder="Importe (vacío = total {{ $m($item->line_total) }})">
                  <input name="note" class="form-control" placeholder="Motivo interno">
                  <label style="font-size:12px;color:var(--a-muted);display:flex;gap:8px"><input type="checkbox" name="relist" value="1"> Volver a publicar el reloj</label>
                  @if($item->stripe_transfer_id)<div class="alert alert--warning" style="margin:0">Los fondos ya se transfirieron: se revertirá la transferencia al vendedor.</div>@endif
                  <button class="btn btn--danger btn--sm">Confirmar reembolso</button>
                </form>
              </details>
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach

    <div class="card">
      <div class="card__num">Historial</div>
      <ul class="timeline">
        @foreach($order->events as $e)
          <li><time>{{ $e->created_at->format('d/m/Y H:i') }} · {{ $e->type }}@if($e->user) · {{ $e->user->name }}@endif</time>{{ $e->message }}</li>
        @endforeach
      </ul>
    </div>
  </div>

  <div style="display:grid;gap:20px">
    <div class="card">
      <div class="card__num">Importes</div>
      <dl class="kv">
        <dt>Subtotal</dt><dd>{{ $m($order->subtotal) }}</dd>
        @if($order->discount_total > 0)<dt>Descuento</dt><dd>−{{ $m($order->discount_total) }} ({{ $order->coupon_code }})</dd>@endif
        <dt>Envío</dt><dd>{{ $m($order->shipping_total) }}</dd>
        <dt style="color:var(--a-text)">Total</dt><dd style="font-weight:600">{{ $m($order->total) }}</dd>
        <dt>Comisión HOT</dt><dd>{{ $m($order->items->sum('commission_amount') - $order->discount_total) }} <span style="color:var(--a-subtle);font-size:11px">(neta de cupón, antes de tarifas Stripe)</span></dd>
      </dl>
    </div>
    <div class="card">
      <div class="card__num">Cliente</div>
      <p style="font-size:13px;color:var(--a-text)">{{ $order->user?->name }}<br><a href="mailto:{{ $order->email }}" style="color:var(--a-gold)">{{ $order->email }}</a></p>
      <div class="card__num" style="margin-top:18px">Dirección de envío</div>
      <p style="white-space:pre-line;font-size:13px;line-height:1.6">{{ $order->shipping_address_text }}</p>
      @if($order->customer_notes)<div class="card__num" style="margin-top:18px">Notas del cliente</div><p style="font-size:13px">{{ $order->customer_notes }}</p>@endif
    </div>
    <div class="card">
      <div class="card__num">Notas internas</div>
      <form method="POST" action="{{ route('admin.orders.notes', $order) }}">@csrf @method('PUT')
        <textarea name="admin_notes" class="form-control">{{ $order->admin_notes }}</textarea>
        <button class="btn btn--ghost btn--sm" style="margin-top:8px">Guardar notas</button>
      </form>
    </div>
  </div>
</div>
@endsection
