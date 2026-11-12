{{-- resources/views/admin/products/index.blade.php --}}
@extends('layouts.admin')
@section('title', __('messages.admin.products'))

@section('admin-content')
<div class="panel-header">
  <div>
    <div class="page-title">{{ __('messages.admin.products') }}</div>
    <p class="page-subtitle">{{ $watches->total() }} relojes en total</p>
  </div>
  @if($pendingIds->count())
    <button type="button" class="btn btn--primary" data-review="{{ $pendingIds->first() }}">
      Revisar pendientes <span class="rvq-count" id="rvqCount">{{ $pendingIds->count() }}</span>
    </button>
  @endif
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar" style="margin-bottom:24px;">
  <select name="status" class="form-control form-control--select" style="width:160px;" onchange="this.form.submit()">
    <option value="">Todos los estados</option>
    @foreach(['pending' => 'Pendiente','approved' => 'Aprobado','rejected' => 'Rechazado','sold' => 'Vendido'] as $v => $l)
      <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
    @endforeach
  </select>
  <input type="text" name="brand" class="form-control" placeholder="Marca..." value="{{ request('brand') }}" style="width:160px;">
  <input type="text" name="seller" class="form-control" placeholder="Vendedor..." value="{{ request('seller') }}" style="width:160px;">
  <button type="submit" class="btn btn--ghost btn--sm">Filtrar</button>
  @if(request()->hasAny(['status','brand','seller']))
    <a href="{{ route('admin.watches.index') }}" class="btn btn--ghost btn--sm">✕ Limpiar</a>
  @endif
</form>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Reloj</th>
        <th>Vendedor</th>
        <th>Precio</th>
        <th>Estado</th>
        <th>Condición</th>
        <th>Fecha</th>
        <th>{{ __('messages.admin.actions') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($watches as $watch)
      <tr class="rv-row" data-review="{{ $watch->id }}" id="rv-row-{{ $watch->id }}">
        <td>
          <div style="display:flex;align-items:center;gap:12px;">
            @if(!empty($watch->images[0]))
              <img src="{{ asset('storage/'.$watch->images[0]) }}" style="width:44px;height:44px;border-radius:6px;object-fit:cover;">
            @else
              <div style="width:44px;height:44px;border-radius:6px;background:var(--bg-elevated);display:flex;align-items:center;justify-content:center;color:var(--text-muted);">⌚</div>
            @endif
            <div>
              <div>{{ $watch->brand }} {{ $watch->model }}</div>
              <div style="font-size:.75rem;color:var(--text-muted);">{{ $watch->reference }}</div>
            </div>
          </div>
        </td>
        <td style="font-size:.85rem;">{{ $watch->seller?->name }}<div style="font-size:.75rem;color:var(--text-muted);">{{ $watch->seller?->email }}</div></td>
        <td>{{ $watch->formatted_price }}</td>
        <td><span class="badge badge-{{ str_replace('badge-','',$watch->status_badge['class']) }}" data-badge>{{ $watch->status_badge['label'] }}</span></td>
        <td style="font-size:.82rem;color:var(--text-secondary);">{{ $watch->condition_label }}</td>
        <td style="font-size:.8rem;color:var(--text-muted);">{{ $watch->created_at->format('d/m/Y') }}</td>
        <td>
          <div style="display:flex;gap:6px;">
            <button type="button" class="btn btn--sm {{ $watch->status === 'pending' ? 'btn--primary' : 'btn--ghost' }}" data-review="{{ $watch->id }}">{{ $watch->status === 'pending' ? 'Revisar' : 'Ver' }}</button>
            <a href="{{ route('admin.watches.show',$watch) }}" class="btn btn--ghost btn--sm" title="Ficha completa">↗</a>
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Sin resultados</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:20px;">{{ $watches->withQueryString()->links() }}</div>

{{-- Popup di revisione rapida --}}
<div class="rvm" id="rvm" hidden role="dialog" aria-modal="true" aria-label="Revisión del reloj">
  <div class="rvm__backdrop" data-act="close"></div>
  <div class="rvm__panel">
    <button type="button" class="rvm__close" data-act="close" aria-label="Cerrar (Esc)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <div class="rvm__body" id="rvmBody"></div>
    <div class="rvm__toast" id="rvmToast" hidden></div>
  </div>
</div>
@endsection

@push('styles')
<style>
.rv-row{cursor:pointer}
.rv-row:hover td{background:var(--a-gold-soft)}
.rvq-count{display:inline-grid;place-items:center;min-width:22px;height:22px;padding:0 6px;margin-left:6px;border-radius:11px;background:var(--a-on-gold);color:var(--a-gold2);font-size:11px;font-weight:700}
.rvm{position:fixed;inset:0;z-index:1000;display:grid;place-items:center;padding:24px}
.rvm[hidden]{display:none}
.rvm__backdrop{position:absolute;inset:0;background:rgba(20,16,10,.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);animation:rvFade .2s ease}
.rvm__panel{position:relative;width:min(1240px,100%);max-height:calc(100vh - 48px);overflow:auto;background:var(--a-bg2);border:1px solid var(--a-border);border-radius:22px;box-shadow:0 40px 120px -30px rgba(0,0,0,.6);animation:rvIn .28s cubic-bezier(.2,.8,.2,1)}
@keyframes rvFade{from{opacity:0}}
@keyframes rvIn{from{opacity:0;transform:translateY(16px) scale(.985)}}
.rvm__close{position:absolute;top:14px;right:14px;z-index:3;width:38px;height:38px;border-radius:50%;border:1px solid var(--a-border);background:var(--a-bg2);color:var(--a-text);display:grid;place-items:center;cursor:pointer}
.rvm__close svg{width:18px;height:18px}
.rvm__body.is-loading{min-height:420px;opacity:.5;pointer-events:none;transition:opacity .15s}
.rvm__toast{position:absolute;left:50%;bottom:20px;transform:translateX(-50%);padding:10px 18px;border-radius:30px;background:var(--a-text);color:var(--a-bg2);font-size:13px;font-weight:500;z-index:4}
.rv{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(0,1fr)}
.rv__verify{padding:26px;border-right:1px solid var(--a-border);background:var(--a-bg3);border-radius:22px 0 0 22px}
.rv__label{font-size:10.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--a-muted);margin-bottom:12px}
.rv__stage{position:relative;border-radius:16px;overflow:hidden;background:#111;aspect-ratio:4/3.3;display:grid;place-items:center}
.rv__stage img{width:100%;height:100%;object-fit:contain;cursor:zoom-in}
.rv__stage.zoom img{object-fit:cover;cursor:zoom-out;transform:scale(1.8);transition:transform .2s}
.rv__empty{color:#bbb;font-size:13px;padding:30px;text-align:center}
.rv__time{position:absolute;left:14px;bottom:14px;display:flex;align-items:center;gap:12px;padding:10px 16px 10px 10px;border-radius:16px;background:rgba(255,255,255,.94);color:#1a1a1a;box-shadow:0 10px 30px -10px rgba(0,0,0,.5)}
.rv__time svg{width:58px;height:58px;color:#1a1a1a}
.rv__time small{display:block;font-size:9.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#7a6a50}
.rv__time strong{display:block;font-size:30px;font-weight:600;letter-spacing:.02em;line-height:1.05}
.rv__time em{display:block;font-style:normal;font-size:10.5px;color:#777;margin-top:2px}
.rv__thumbs{display:flex;gap:8px;margin-top:12px;overflow-x:auto;padding-bottom:4px}
.rv__thumbs button{position:relative;flex:none;width:70px;height:70px;border-radius:10px;overflow:hidden;border:2px solid transparent;padding:0;background:#000;cursor:pointer;opacity:.7;transition:all .15s}
.rv__thumbs button.on,.rv__thumbs button:hover{opacity:1;border-color:var(--a-gold)}
.rv__thumbs img{width:100%;height:100%;object-fit:cover}
.rv__thumbs span{position:absolute;left:0;right:0;bottom:0;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;background:var(--a-gold);color:var(--a-on-gold);text-align:center;padding:2px 0}
.rv__info{padding:30px 30px 24px;display:flex;flex-direction:column}
.rv__head{display:flex;gap:6px;margin-bottom:14px;padding-right:44px}
.rv__brand{font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--a-gold)}
.rv__model{font-size:26px;font-weight:600;letter-spacing:-.02em;line-height:1.15;margin:4px 0 8px;color:var(--a-text)}
.rv__price{font-size:20px;font-weight:600;color:var(--a-text);margin-bottom:18px}
.rv__price s{font-size:14px;font-weight:400;color:var(--a-subtle);margin-right:8px}
.rv__facts{display:grid;grid-template-columns:1fr 1fr;gap:0 18px;margin:0 0 14px}
.rv__facts div{padding:9px 0;border-top:1px solid var(--a-border2)}
.rv__facts dt{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--a-subtle)}
.rv__facts dd{margin:2px 0 0;font-size:13.5px;color:var(--a-text)}
.rv__desc{font-size:13px;line-height:1.6;color:var(--a-muted);margin:0 0 16px;max-height:110px;overflow:auto}
.rv__seller{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:14px;background:var(--a-bg3);margin-bottom:16px}
.rv__avatar{width:38px;height:38px;border-radius:50%;background:var(--a-gold);color:var(--a-on-gold);display:grid;place-items:center;font-weight:700}
.rv__seller strong{display:block;font-size:13.5px;color:var(--a-text)}
.rv__seller span{font-size:12px;color:var(--a-muted)}
.rv__note{font-size:12.5px;padding:10px 12px;border-radius:10px;background:var(--a-err-bg);color:var(--a-danger);margin-bottom:14px}
.rv__actions[hidden]{display:none}
.rv__actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:auto}
.rv__actions .btn{height:52px;justify-content:center;font-size:14px;gap:8px;display:flex;align-items:center}
.rv__actions svg{width:18px;height:18px}
.rv__actions .btn:disabled{opacity:.35;cursor:not-allowed}
.rv__no{color:var(--a-danger)!important;border-color:var(--a-danger)!important}
kbd{font:600 10px/1 ui-monospace,monospace;padding:3px 5px;border-radius:4px;border:1px solid currentColor;opacity:.6;margin-left:4px}
.rv__reject{margin-top:auto;margin-bottom:10px}
.rv__reject[hidden]{display:none}
.rv__chips{display:flex;flex-wrap:wrap;gap:6px;margin:6px 0 8px}
.rv__chips button{font-size:11.5px;padding:6px 10px;border-radius:20px;border:1px solid var(--a-border);background:var(--a-bg2);color:var(--a-text);cursor:pointer}
.rv__chips button:hover{border-color:var(--a-danger);color:var(--a-danger)}
.rv__row{display:flex;justify-content:flex-end;gap:8px;margin-top:10px}
.rv__foot{display:flex;gap:18px;align-items:center;margin-top:14px;font-size:12.5px}
.rv__foot a,.rv__foot button{color:var(--a-muted);background:none;border:0;padding:0;cursor:pointer;font:inherit}
.rv__foot a:hover,.rv__foot button:hover{color:var(--a-gold)}
.rv__foot button{margin-left:auto;color:var(--a-text);font-weight:600}
@media (max-width:900px){.rv{grid-template-columns:1fr}.rv__verify{border-right:0;border-bottom:1px solid var(--a-border);border-radius:22px 22px 0 0}.rvm{padding:0}.rvm__panel{max-height:100vh;border-radius:0}}
</style>
@endpush

@push('scripts')
<script>
(function () {
  var modal = document.getElementById('rvm'), body = document.getElementById('rvmBody'), toastEl = document.getElementById('rvmToast');
  var csrf = document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').content : '{{ csrf_token() }}';
  var base = @json(url('admin/productos'));
  var current = null, busy = false;

  function toast(msg) { toastEl.textContent = msg; toastEl.hidden = false; clearTimeout(toast.t); toast.t = setTimeout(function () { toastEl.hidden = true; }, 1800); }

  function open(id) {
    modal.hidden = false; document.body.style.overflow = 'hidden';
    body.classList.add('is-loading');
    fetch(base + '/' + id + '/revision', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (!r.ok) throw r; return r.text(); })
      .then(function (html) { body.innerHTML = html; current = body.querySelector('.rv'); body.classList.remove('is-loading'); body.parentNode.scrollTop = 0; })
      .catch(function () { body.classList.remove('is-loading'); toast('No se pudo cargar el reloj'); });
  }
  function close() { modal.hidden = true; document.body.style.overflow = ''; current = null; }

  function decide(kind) {
    if (!current || busy) return;
    var reason = kind === 'reject' ? (document.getElementById('rvReason').value || '').trim() : '';
    busy = true;
    var fd = new FormData(); if (reason) fd.append('admin_notes', reason);
    fetch(current.dataset[kind === 'approve' ? 'approve' : 'reject'], { method: 'POST', body: fd,
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (!r.ok) throw r; return r.json(); })
      .then(function (d) {
        var row = document.getElementById('rv-row-' + d.id);
        if (row) { var b = row.querySelector('[data-badge]'); if (b) { b.className = 'badge ' + d.badge['class']; b.textContent = d.badge.label; }
          var btn = row.querySelector('button[data-review]'); if (btn) { btn.textContent = 'Ver'; btn.className = 'btn btn--sm btn--ghost'; } }
        var c = document.getElementById('rvqCount'); if (c) c.textContent = d.pending;
        toast(kind === 'approve' ? 'Reloj aprobado y publicado' : 'Reloj rechazado · vendedor avisado');
        if (d.next) setTimeout(function () { open(d.next); }, 450); else setTimeout(function () { close(); toast('No quedan relojes pendientes'); }, 700);
      })
      .catch(function () { toast('Error: no se ha guardado'); })
      .finally(function () { busy = false; });
  }

  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-review]');
    if (t && !e.target.closest('a')) { e.preventDefault(); open(t.dataset.review); return; }
    if (!current && !e.target.closest('.rvm')) return;
    var act = e.target.closest('[data-act]');
    var thumb = e.target.closest('#rvThumbs button');
    if (thumb) {
      document.querySelectorAll('#rvThumbs button').forEach(function (b) { b.classList.toggle('on', b === thumb); });
      var main = document.getElementById('rvMain'), st = document.getElementById('rvStage');
      if (main) main.src = thumb.dataset.src; else st.insertAdjacentHTML('afterbegin', '<img id="rvMain" src="' + thumb.dataset.src + '">');
      st.classList.remove('zoom');
      var tm = st.querySelector('.rv__time'); if (tm) tm.style.display = thumb.classList.contains('is-verif') ? '' : 'none';
      return;
    }
    if (e.target.id === 'rvMain') { document.getElementById('rvStage').classList.toggle('zoom'); return; }
    var chip = e.target.closest('[data-reason]');
    if (chip) { var ta = document.getElementById('rvReason'); ta.value = chip.dataset.reason; ta.focus(); return; }
    if (!act) return;
    switch (act.dataset.act) {
      case 'close': close(); break;
      case 'approve': decide('approve'); break;
      case 'reject': document.getElementById('rvReject').hidden = false; document.getElementById('rvActions').hidden = true; document.getElementById('rvReason').focus(); break;
      case 'cancel-reject': document.getElementById('rvReject').hidden = true; document.getElementById('rvActions').hidden = false; break;
      case 'confirm-reject': decide('reject'); break;
      case 'next': if (current && current.dataset.next) open(current.dataset.next); break;
    }
  });

  document.addEventListener('keydown', function (e) {
    if (modal.hidden) return;
    if (e.key === 'Escape') { close(); return; }
    if (/input|textarea/i.test(e.target.tagName)) { if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) decide('reject'); return; }
    var k = e.key.toLowerCase();
    if (k === 'a') { var b = current && current.querySelector('[data-act=approve]'); if (b && !b.disabled) decide('approve'); }
    else if (k === 'r') { var r = current && current.querySelector('[data-act=reject]'); if (r && !r.disabled) r.click(); }
    else if (e.key === 'ArrowRight' && current && current.dataset.next) open(current.dataset.next);
  });

  // apertura diretta: /admin/productos?revisar=ID
  var q = new URLSearchParams(location.search).get('revisar'); if (q) open(q);
})();
</script>
@endpush
