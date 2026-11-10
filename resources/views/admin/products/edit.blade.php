@extends('layouts.admin')
@section('title', 'Editar — ' . $watch->brand . ' ' . $watch->model)

@section('admin-content')

{{-- ── Header ──────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
  <div style="display:flex;align-items:center;gap:14px;">
    <a href="{{ route('admin.watches.show', $watch) }}" class="btn btn--ghost btn--sm">← Volver</a>
    <div>
      <div class="page-title">Editar: {{ $watch->brand }} {{ $watch->model }}</div>
      <p class="page-subtitle" style="margin-bottom:0;">
        ID #{{ $watch->id }} · Vendedor: {{ $watch->seller?->full_name }}
        @if($watch->seller?->is_founding_seller)
          <span class="badge badge-gold" style="margin-left:6px;">⭐ Founding</span>
        @endif
      </p>
    </div>
  </div>
  <button type="submit" form="adminEditForm" class="btn btn--primary" style="min-width:160px;">
    💾 Guardar cambios
  </button>
</div>

<form method="POST" action="{{ route('admin.watches.update', $watch) }}"
      enctype="multipart/form-data" id="adminEditForm">
  @csrf @method('PUT')

  <div class="ae-layout">

    {{-- ══════════════════════════════════════════════════
         COLUMNA IZQUIERDA
    ══════════════════════════════════════════════════ --}}
    <div class="ae-left">

      {{-- Identificación --}}
      <div class="card" style="margin-bottom:20px;">
        <div class="ae-sh"><span class="ae-sl">Identificación</span><div class="ae-sline"></div></div>

        <div class="form-group">
          <label class="form-label">Marca *</label>
          @php
            $savedBrand  = old('brand', $watch->brand ?? '');
            $brandInList = $brands->contains(fn($b) => $b->name_es === $savedBrand);
          @endphp
          <select name="brand" class="form-control" id="brandSelect" onchange="toggleBrand(this)" required>
            <option value="">— Seleccionar —</option>
            @foreach($brands as $b)
              <option value="{{ $b->name_es }}" {{ $savedBrand === $b->name_es ? 'selected' : '' }}>
                {{ $b->name_es }}{{ $b->name_en ? ' / '.$b->name_en : '' }}
              </option>
            @endforeach
            <option value="__custom__" {{ $savedBrand && !$brandInList ? 'selected' : '' }}>+ Otra marca</option>
          </select>
          <input type="text" id="brandCustomInput" class="form-control" style="margin-top:8px;display:none;"
                 placeholder="Escribe la marca"
                 value="{{ $savedBrand && !$brandInList ? $savedBrand : '' }}">
          @error('brand')<p style="color:var(--a-danger);font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Modelo *</label>
            <input type="text" name="model" class="form-control"
                   value="{{ old('model', $watch->model) }}" required>
            @error('model')<p style="color:var(--a-danger);font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Referencia</label>
            <input type="text" name="reference" class="form-control"
                   value="{{ old('reference', $watch->reference ?? '') }}" placeholder="126610LN">
          </div>
        </div>

        <div class="form-row" style="margin-bottom:0;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Año fabricación</label>
            <input type="number" name="manufacture_year" class="form-control"
                   value="{{ old('manufacture_year', $watch->manufacture_year ?? '') }}"
                   min="1900" max="{{ date('Y') }}">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Categoría</label>
            <select name="category" class="form-control">
              <option value="">— Ninguna —</option>
              @foreach($categories as $c)
                @php $catSaved = old('category', $watch->category ?? ''); @endphp
                <option value="{{ $c->name_es }}" {{ $catSaved === $c->name_es ? 'selected' : '' }}>
                  {{ $c->name_es }}{{ $c->name_en ? ' / '.$c->name_en : '' }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      {{-- Descripción --}}
      <div class="card" style="margin-bottom:20px;">
        <div class="ae-sh"><span class="ae-sl">Descripción</span><div class="ae-sline"></div></div>
        <textarea name="description" class="form-control" rows="6"
                  placeholder="Descripción del reloj...">{{ old('description', $watch->description ?? '') }}</textarea>
        <label class="form-label" style="margin-top:14px">Descripción en inglés (sitio EN · opcional)</label>
        <textarea name="description_en" class="form-control" rows="5"
                  placeholder="About this piece…">{{ old('description_en', $watch->description_en ?? '') }}</textarea>
      </div>

      {{-- Fotografías --}}
      <div class="card" style="margin-bottom:20px;">
        <div class="ae-sh"><span class="ae-sl">Fotografías</span><div class="ae-sline"></div></div>

        @php $imgs = is_array($watch->images) ? $watch->images : (json_decode($watch->images ?? '[]', true) ?? []); @endphp
        @if(count($imgs))
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:16px;">
          @foreach($imgs as $ii => $img)
          <div id="imgItem{{ $ii }}" style="position:relative;">
            <img src="{{ Storage::url($img) }}"
                 style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:4px;border:1px solid var(--a-border);display:block;">
            <input type="hidden" name="keep_images[]" value="{{ $img }}" id="keepImg{{ $ii }}">
            <button type="button" class="ae-del-btn" onclick="toggleMedia('imgItem{{ $ii }}','keepImg{{ $ii }}')" title="Eliminar">×</button>
          </div>
          @endforeach
        </div>
        @endif

        <label class="ae-upload-zone">
          <input type="file" name="images[]" id="imgInput" multiple
                 accept="image/jpeg,image/jpg,image/png,image/webp"
                 style="display:none" onchange="previewImages(this)">
          <div style="font-size:1.6rem;margin-bottom:6px;opacity:.4;">📷</div>
          <div style="font-size:13px;color:var(--a-muted);">Añadir fotos · JPG, PNG, WEBP · máx. 10MB</div>
        </label>
        <div id="newImgPreview" style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:10px;"></div>
        @error('images.*')<p style="color:var(--a-danger);font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
      </div>

      {{-- Documentación --}}
      <div class="card">
        <div class="ae-sh"><span class="ae-sl">Documentación</span><div class="ae-sline"></div></div>

        @php $docs = is_array($watch->documentation_files) ? $watch->documentation_files : (json_decode($watch->documentation_files ?? '[]', true) ?? []); @endphp
        @if(count($docs))
        <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
          @foreach($docs as $di => $doc)
          <div id="docItem{{ $di }}" style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:var(--a-bg3);border:1px solid var(--a-border);border-radius:3px;">
            <span style="font-size:12px;flex:1;color:var(--a-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">📄 {{ basename($doc) }}</span>
            <input type="hidden" name="keep_docs[]" value="{{ $doc }}" id="keepDoc{{ $di }}">
            <button type="button" class="ae-del-btn ae-del-inline" onclick="toggleMedia('docItem{{ $di }}','keepDoc{{ $di }}')" title="Eliminar">×</button>
          </div>
          @endforeach
        </div>
        @endif

        <label class="ae-upload-zone" style="padding:14px;">
          <input type="file" name="documentation_files[]" multiple accept=".pdf,image/*"
                 style="display:none" onchange="previewDocs(this)">
          <div style="font-size:1.2rem;margin-bottom:4px;opacity:.4;">📄</div>
          <div style="font-size:12px;color:var(--a-muted);">PDF o imágenes de documentos</div>
        </label>
        <div id="newDocsList" style="margin-top:6px;display:flex;flex-direction:column;gap:3px;"></div>
      </div>

    </div>{{-- /left --}}

    {{-- ══════════════════════════════════════════════════
         COLUMNA DERECHA
    ══════════════════════════════════════════════════ --}}
    <div class="ae-right">

      {{-- Estado --}}
      <div class="card" style="margin-bottom:16px;">
        <div class="ae-sh"><span class="ae-sl">Estado de publicación</span><div class="ae-sline"></div></div>
        <div class="form-group" style="margin-bottom:0;">
          <select name="status" class="form-control" style="font-weight:600;" required>
            @php
              $statuses = ['pending'=>'Pendiente','approved'=>'Aprobado','rejected'=>'Rechazado','sold'=>'Vendido','draft'=>'Borrador'];
            @endphp
            @foreach($statuses as $v => $l)
              <option value="{{ $v }}" {{ old('status', $watch->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group" style="margin-top:14px;margin-bottom:0;">
          <label class="form-label">Nota interna (opcional)</label>
          <textarea name="admin_notes" class="form-control" rows="3"
                    placeholder="Motivo de rechazo o nota para el vendedor...">{{ old('admin_notes', $watch->admin_notes ?? '') }}</textarea>
        </div>
      </div>

      {{-- Precio --}}
      <div class="card" style="margin-bottom:16px;">
        <div class="ae-sh"><span class="ae-sl">Precio</span><div class="ae-sline"></div></div>
        <div class="form-group">
          <label class="form-label">Precio de venta (EUR) *</label>
          <div style="position:relative;">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--a-subtle);font-size:13px;">€</span>
            <input type="number" name="price" class="form-control" style="padding-left:26px;"
                   value="{{ old('price', (int)$watch->price) }}"
                   min="0" step="1" required>
          </div>
          @error('price')<p style="color:var(--a-danger);font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Precio con descuento (EUR)</label>
          <div style="position:relative;">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--a-subtle);font-size:13px;">€</span>
            <input type="number" name="price_discounted" class="form-control" style="padding-left:26px;"
                   value="{{ old('price_discounted', $watch->price_discounted ? (int)$watch->price_discounted : '') }}"
                   min="0" step="1" placeholder="Opcional">
          </div>
        </div>
      </div>

      {{-- Marketplace --}}
      <div class="card" style="margin-bottom:16px;">
        <div class="ae-sh"><span class="ae-sl">Ficha en la tienda</span><div class="ae-sline"></div></div>
        <label style="display:flex;gap:10px;align-items:center;font-size:13px;margin-bottom:10px;cursor:pointer">
          <input type="checkbox" name="is_certified" value="1" @checked(old('is_certified', $watch->is_certified))>
          <img src="/images/shop/seal.png" alt="" style="width:26px;height:26px"> Sello <b>HAUS OF TIME certified</b>
        </label>
        <label style="display:flex;gap:10px;align-items:center;font-size:13px;margin-bottom:16px;cursor:pointer">
          <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $watch->is_featured))> Destacado (aparece primero)
        </label>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Tamaño de caja</label><input name="case_size" class="form-control" value="{{ old('case_size', $watch->case_size) }}" placeholder="44,25 mm"></div>
          <div class="form-group"><label class="form-label">Orden</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $watch->sort_order ?? 0) }}"></div>
        </div>
        <div class="form-group"><label class="form-label">Material / línea de la ficha</label><input name="material" class="form-control" value="{{ old('material', $watch->material) }}" placeholder="Cerámica gris con pulsera de caucho" maxlength="160"></div>
        <div class="form-row" style="margin-bottom:0">
          <div class="form-group" style="margin-bottom:0"><label class="form-label">País (ubicación)</label>
            <select name="country_code" class="form-control"><option value="">—</option>
              @foreach(\App\Models\Country::orderBy('name_es')->get() as $c)<option value="{{ $c->code }}" @selected(old('country_code', $watch->country_code) === $c->code)>{{ $c->name_es }}</option>@endforeach
            </select></div>
          <div class="form-group" style="margin-bottom:0"><label class="form-label">Ciudad</label><input name="city" class="form-control" value="{{ old('city', $watch->city) }}" placeholder="Madrid"></div>
        </div>
        @if($watch->slug && in_array($watch->status, ['approved', 'sold']))
          <a href="{{ route('shop.show', $watch->slug) }}" target="_blank" class="btn btn--ghost btn--sm" style="margin-top:14px">Ver en la tienda ↗</a>
        @endif
      </div>

      {{-- Condición & Caja --}}
      <div class="card" style="margin-bottom:16px;">
        <div class="ae-sh"><span class="ae-sl">Condición</span><div class="ae-sline"></div></div>
        <div class="form-group">
          <label class="form-label">Estado del reloj *</label>
          <select name="condition" class="form-control" required>
            <option value="">— Seleccionar —</option>
            @foreach(['mint'=>'Mint / Nunca usado','excellent'=>'Excelente','very_good'=>'Muy bueno','good'=>'Bueno','fair'=>'Aceptable'] as $v => $l)
              <option value="{{ $v }}" {{ old('condition', $watch->condition) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
          </select>
          @error('condition')<p style="color:var(--a-danger);font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Caja y documentación</label>
          <select name="box_papers" class="form-control">
            @foreach(['none'=>'Sin caja ni papeles','box_only'=>'Solo caja','papers_only'=>'Solo papeles','full_set'=>'Caja y papeles completos'] as $v => $l)
              <option value="{{ $v }}" {{ old('box_papers', $watch->box_papers ?? 'none') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Vendedor (read-only) --}}
      @if($watch->seller)
      <div class="card" style="margin-bottom:16px;">
        <div class="ae-sh"><span class="ae-sl">Vendedor</span><div class="ae-sline"></div></div>
        <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="color:var(--a-subtle);font-size:11px;letter-spacing:.08em;text-transform:uppercase;">Nombre</span>
            <span style="font-weight:500;">{{ $watch->seller->full_name }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="color:var(--a-subtle);font-size:11px;letter-spacing:.08em;text-transform:uppercase;">Email</span>
            <span style="color:var(--a-muted);">{{ $watch->seller->email }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="color:var(--a-subtle);font-size:11px;letter-spacing:.08em;text-transform:uppercase;">Teléfono</span>
            <span style="color:var(--a-muted);">{{ $watch->seller->phone_prefix }} {{ $watch->seller->phone ?? '—' }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="color:var(--a-subtle);font-size:11px;letter-spacing:.08em;text-transform:uppercase;">País</span>
            <span style="color:var(--a-muted);">{{ $watch->seller->country ?? '—' }}</span>
          </div>
          @if($watch->seller->is_founding_seller)
          <div style="margin-top:4px;"><span class="badge badge-gold">⭐ Founding Seller</span></div>
          @endif
        </div>
      </div>
      @endif

      {{-- Metadata --}}
      <div class="card">
        <div class="ae-sh"><span class="ae-sl">Información</span><div class="ae-sline"></div></div>
        <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;color:var(--a-subtle);">
          <div>ID: <span style="color:var(--a-muted);">#{{ $watch->id }}</span></div>
          <div>Creado: <span style="color:var(--a-muted);">{{ $watch->created_at->format('d/m/Y H:i') }}</span></div>
          <div>Actualizado: <span style="color:var(--a-muted);">{{ $watch->updated_at->format('d/m/Y H:i') }}</span></div>
          @if($watch->approved_at)
          <div>Aprobado: <span style="color:var(--a-success);">{{ $watch->approved_at->format('d/m/Y H:i') }}</span></div>
          @endif
        </div>
      </div>

    </div>{{-- /right --}}
  </div>{{-- /ae-layout --}}

  {{-- Submit bottom --}}
  <div style="display:flex;gap:12px;margin-top:24px;align-items:center;">
    <button type="submit" class="btn btn--primary btn--lg" style="flex:1;justify-content:center;">
      💾 Guardar cambios
    </button>
    <a href="{{ route('admin.watches.show', $watch) }}" class="btn btn--ghost">
      Cancelar
    </a>
    <button type="button" class="btn btn--danger" onclick="if(confirm('¿Eliminar este reloj? Dejará de mostrarse en la tienda y en el panel del vendedor.')) document.getElementById('adminDeleteForm').submit();">
      Eliminar
    </button>
  </div>

</form>

{{-- Delete form — fuori dal form principale --}}
<form method="POST" action="{{ route('admin.watches.destroy', $watch) }}" id="adminDeleteForm" style="display:none;">
  @csrf @method('DELETE')
</form>

@push('styles')
<style>
.ae-layout{display:grid;grid-template-columns:1.4fr 1fr;gap:20px;align-items:start;}
.ae-right{position:sticky;top:20px;}
.ae-sh{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
.ae-sl{font-size:10px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--a-text);white-space:nowrap;flex-shrink:0;}
.ae-sline{flex:1;height:1px;background:linear-gradient(to right,var(--a-border),transparent);}
.ae-upload-zone{display:flex;flex-direction:column;align-items:center;justify-content:center;border:1.5px dashed var(--a-border);border-radius:3px;padding:20px;text-align:center;cursor:pointer;background:var(--a-bg3);transition:border-color .2s;width:100%;}
.ae-upload-zone:hover{border-color:var(--a-gold);}
.ae-del-btn{background:rgba(155,51,51,.85);color:#fff;border:none;cursor:pointer;border-radius:50%;width:20px;height:20px;font-size:14px;line-height:1;display:flex;align-items:center;justify-content:center;position:absolute;top:-6px;right:-6px;z-index:2;box-shadow:0 1px 4px rgba(0,0,0,.4);transition:background .15s;}
.ae-del-btn:hover{background:var(--a-danger);}
.ae-del-btn.ae-del-inline{position:static;flex-shrink:0;}
.ae-del-btn.restore{background:rgba(61,138,94,.85);}
.ae-img-deleted img{opacity:.2;filter:grayscale(1);}
.ae-doc-deleted{opacity:.3;text-decoration:line-through;}
@media(max-width:1100px){.ae-layout{grid-template-columns:1fr;}.ae-right{position:static;}}
</style>
@endpush

@push('scripts')
<script>
function toggleBrand(sel) {
  var c = document.getElementById('brandCustomInput');
  if (!c) return;
  if (sel.value === '__custom__') {
    c.style.display='block'; c.name='brand'; sel.removeAttribute('name');
  } else {
    c.style.display='none'; c.removeAttribute('name'); sel.name='brand';
  }
}

function toggleMedia(itemId, inputId) {
  var item = document.getElementById(itemId);
  var inp  = document.getElementById(inputId);
  var btn  = item ? item.querySelector('.ae-del-btn') : null;
  if (!item || !inp || !btn) return;
  var isDeleted = item.classList.contains('ae-img-deleted') || item.classList.contains('ae-doc-deleted');
  if (isDeleted) {
    item.classList.remove('ae-img-deleted', 'ae-doc-deleted');
    inp.disabled = false;
    btn.classList.remove('restore');
    btn.textContent = '×';
  } else {
    item.classList.add(item.querySelector('img') ? 'ae-img-deleted' : 'ae-doc-deleted');
    inp.disabled = true;
    btn.classList.add('restore');
    btn.textContent = '↩';
  }
}

function previewImages(input) {
  var p = document.getElementById('newImgPreview');
  if (!p) return;
  p.innerHTML = '';
  Array.from(input.files).slice(0,10).forEach(function(f) {
    var r = new FileReader();
    r.onload = function(e) {
      var img = document.createElement('img');
      img.src = e.target.result;
      img.style.cssText = 'aspect-ratio:1;width:100%;object-fit:cover;border-radius:4px;border:1px solid var(--a-border);';
      p.appendChild(img);
    };
    r.readAsDataURL(f);
  });
}

function previewDocs(input) {
  var p = document.getElementById('newDocsList');
  if (!p) return;
  p.innerHTML = '';
  Array.from(input.files).forEach(function(f) {
    var d = document.createElement('div');
    d.style.cssText = 'font-size:11px;color:var(--a-muted);';
    d.textContent = '📄 ' + f.name;
    p.appendChild(d);
  });
}

document.addEventListener('DOMContentLoaded', function() {
  var bs = document.getElementById('brandSelect');
  if (bs) toggleBrand(bs);
});
</script>
@endpush

@endsection
