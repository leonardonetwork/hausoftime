@extends('layouts.seller')
@section('title', 'Editar — ' . $watch->brand . ' ' . $watch->model)

@section('seller-content')
<div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;">
  <a href="{{ route('seller.watches.show', $watch) }}" class="btn btn--ghost btn--sm">← Volver</a>
  <div class="page-title">{{ app()->getLocale() === 'es' ? 'Editar reloj' : 'Edit watch' }}</div>
</div>

@if($errors->any())
  <div class="alert alert--error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('seller.watches.update', $watch) }}" enctype="multipart/form-data" style="max-width:760px;">
  @csrf @method('PUT')

  <div class="card" style="margin-bottom:20px;">
    <div class="card__num" style="margin-bottom:20px;">{{ __('messages.seller.brand') }} & {{ __('messages.seller.model') }}</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.brand') }}</label>
        <input type="text" name="brand" class="form-control" value="{{ old('brand', $watch->brand) }}" required list="brandsList">
        <datalist id="brandsList">
          @foreach(['Rolex','Patek Philippe','Audemars Piguet','Richard Mille','Vacheron Constantin','Cartier','IWC','Omega','Breitling','Hublot','Jaeger-LeCoultre','A. Lange & Söhne','Panerai','TAG Heuer'] as $b)
            <option value="{{ $b }}">
          @endforeach
        </datalist>
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.model') }}</label>
        <input type="text" name="model" class="form-control" value="{{ old('model', $watch->model) }}" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.reference') }}</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference', $watch->reference) }}">
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.year') }}</label>
        <input type="number" name="manufacture_year" class="form-control" value="{{ old('manufacture_year', $watch->manufacture_year) }}" min="1900" max="{{ date('Y') }}">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.condition') }}</label>
        <select name="condition" class="form-control form-control--select" required>
          @foreach(['mint' => 'Mint / Nunca usado','excellent' => 'Excelente','very_good' => 'Muy bueno','good' => 'Bueno','fair' => 'Aceptable'] as $v => $l)
            <option value="{{ $v }}" {{ old('condition', $watch->condition) === $v ? 'selected' : '' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.category') }}</label>
        <select name="category" class="form-control form-control--select">
          <option value="">—</option>
          @foreach(['Sport','Dress','Complication','Tourbillon','Diver','Pilot','Chronograph','Classic'] as $cat)
            <option value="{{ $cat }}" {{ old('category', $watch->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">{{ __('messages.seller.description') }}</label>
      <textarea name="description" class="form-control" rows="5">{{ old('description', $watch->description) }}</textarea>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px;">
    <div class="card__num" style="margin-bottom:20px;">Precio & Documentación</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.price') }}</label>
        <div style="position:relative;">
          <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);">€</span>
          <input type="number" name="price" class="form-control" value="{{ old('price', $watch->price) }}" required min="100" step="100" style="padding-left:28px;">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.box_papers') }}</label>
        <select name="box_papers" class="form-control form-control--select" required>
          @foreach(['none' => 'Sin caja ni papeles','box_only' => 'Solo caja','papers_only' => 'Solo papeles','full_set' => 'Full set'] as $v => $l)
            <option value="{{ $v }}" {{ old('box_papers', $watch->box_papers) === $v ? 'selected' : '' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{-- Add more images --}}
  <div class="card" style="margin-bottom:28px;">
    <div class="card__num" style="margin-bottom:16px;">{{ app()->getLocale() === 'es' ? 'Añadir más fotografías' : 'Add more photos' }}</div>
    @if(!empty($watch->images))
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-bottom:16px;">
      @foreach($watch->images as $img)
        <div style="aspect-ratio:1;border-radius:6px;overflow:hidden;border:1px solid var(--bg-border);">
          <img src="{{ asset('storage/'.$img) }}" style="width:100%;height:100%;object-fit:cover;">
        </div>
      @endforeach
    </div>
    @endif
    <div class="file-upload" onclick="document.getElementById('imgInput').click()">
      <div class="file-upload__icon">📷</div>
      <div class="file-upload__text">{{ app()->getLocale() === 'es' ? 'Añadir más fotos (opcional)' : 'Add more photos (optional)' }}</div>
      <input type="file" id="imgInput" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
    </div>
    <div id="imgPreview" style="margin-top:12px;display:grid;grid-template-columns:repeat(5,1fr);gap:8px;"></div>
  </div>

  <div style="display:flex;gap:12px;">
    <a href="{{ route('seller.watches.show', $watch) }}" class="btn btn--ghost">← Volver</a>
    <button type="submit" class="btn btn--primary" style="flex:1;">
      {{ app()->getLocale() === 'es' ? 'Guardar y reenviar a revisión' : 'Save and resubmit for review' }}
    </button>
  </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('imgInput').addEventListener('change', function() {
  const preview = document.getElementById('imgPreview');
  preview.innerHTML = '';
  Array.from(this.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.style.cssText = 'aspect-ratio:1;border-radius:6px;overflow:hidden;border:1px solid var(--bg-border);';
      div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
      preview.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
});
</script>
@endpush
