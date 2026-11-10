@extends('layouts.seller')
@section('title', __('messages.seller.upload_watch'))

@section('seller-content')
<div class="page-title">{{ __('messages.seller.watch_form_title') }}</div>
<p class="page-subtitle">{{ __('messages.seller.watch_form_sub') }}</p>

@if($errors->any())
  <div class="alert alert--error">
    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
  </div>
@endif

<form method="POST" action="{{ route('seller.watches.store') }}" enctype="multipart/form-data" style="max-width:760px;">
  @csrf

  {{-- Brand / Model / Reference / Year --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card__num" style="margin-bottom:20px;">{{ __('messages.seller.brand') }} & {{ __('messages.seller.model') }}</div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.brand') }}</label>
        <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" required
               list="brandsList" placeholder="Rolex, Patek Philippe…">
        <datalist id="brandsList">
          @foreach(['Rolex','Patek Philippe','Audemars Piguet','Richard Mille','Vacheron Constantin','Cartier','IWC','Omega','Breitling','Hublot','Jaeger-LeCoultre','A. Lange & Söhne','Panerai','TAG Heuer'] as $b)
            <option value="{{ $b }}">
          @endforeach
        </datalist>
        @error('brand')<p class="field-error">{{ $message }}</p>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.model') }}</label>
        <input type="text" name="model" class="form-control" value="{{ old('model') }}" required>
        @error('model')<p class="field-error">{{ $message }}</p>@enderror
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.reference') }}</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="126610LN">
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.year') }}</label>
        <input type="number" name="manufacture_year" class="form-control" value="{{ old('manufacture_year') }}" min="1900" max="{{ date('Y') }}">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.condition') }}</label>
        <select name="condition" class="form-control form-control--select" required>
          <option value="">—</option>
          @foreach(['mint' => 'Mint / Nunca usado','excellent' => 'Excelente','very_good' => 'Muy bueno','good' => 'Bueno','fair' => 'Aceptable'] as $v => $l)
            <option value="{{ $v }}" {{ old('condition') === $v ? 'selected' : '' }}>{{ $l }}</option>
          @endforeach
        </select>
        @error('condition')<p class="field-error">{{ $message }}</p>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.category') }}</label>
        <select name="category" class="form-control form-control--select">
          <option value="">—</option>
          @foreach(['Sport','Dress','Complication','Tourbillon','Diver','Pilot','Chronograph','Classic'] as $cat)
            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">{{ __('messages.seller.description') }}</label>
      <textarea name="description" class="form-control" rows="5" placeholder="Describe el reloj, su historial, estado, etc.">{{ old('description') }}</textarea>
    </div>
  </div>

  {{-- Price & Box --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card__num" style="margin-bottom:20px;">Precio & Documentación</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.price') }}</label>
        <div style="position:relative;">
          <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);">€</span>
          <input type="number" name="price" class="form-control" value="{{ old('price') }}" required min="100" step="100" style="padding-left:28px;">
        </div>
        @error('price')<p class="field-error">{{ $message }}</p>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.box_papers') }}</label>
        <select name="box_papers" class="form-control form-control--select" required id="boxPapersSelect">
          <option value="">—</option>
          <option value="none" {{ old('box_papers') === 'none' ? 'selected' : '' }}>Sin caja ni papeles</option>
          <option value="box_only" {{ old('box_papers') === 'box_only' ? 'selected' : '' }}>Solo caja</option>
          <option value="papers_only" {{ old('box_papers') === 'papers_only' ? 'selected' : '' }}>Solo papeles</option>
          <option value="full_set" {{ old('box_papers') === 'full_set' ? 'selected' : '' }}>Full set (caja + papeles)</option>
        </select>
        @error('box_papers')<p class="field-error">{{ $message }}</p>@enderror
      </div>
    </div>

    {{-- PDF upload (shown when papers selected) --}}
    <div id="docUpload" style="display:none;">
      <div class="form-group">
        <label class="form-label">{{ __('messages.seller.doc_files') }}</label>
        <div class="file-upload" id="docDropzone" onclick="document.getElementById('docInput').click()">
          <div class="file-upload__icon">📄</div>
          <div class="file-upload__text">{{ __('messages.seller.doc_files_hint') }}</div>
          <input type="file" id="docInput" name="documentation_files[]" multiple accept=".pdf">
        </div>
        <div id="docFileList" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;"></div>
        @error('documentation_files')<p class="field-error">{{ $message }}</p>@enderror
        @error('documentation_files.*')<p class="field-error">{{ $message }}</p>@enderror
      </div>
    </div>
  </div>

  {{-- Images --}}
  <div class="card" style="margin-bottom:28px;">
    <div class="card__num" style="margin-bottom:20px;">{{ __('messages.seller.images') }}</div>
    <div class="file-upload" id="imgDropzone" onclick="document.getElementById('imgInput').click()">
      <div class="file-upload__icon">📷</div>
      <div class="file-upload__text">{{ __('messages.seller.images_hint') }}</div>
      <input type="file" id="imgInput" name="images[]" multiple accept="image/jpeg,image/png,image/webp" required>
    </div>
    <div id="imgPreview" style="margin-top:16px;display:grid;grid-template-columns:repeat(5,1fr);gap:10px;"></div>
    @error('images')<p class="field-error">{{ $message }}</p>@enderror
    @error('images.*')<p class="field-error">{{ $message }}</p>@enderror
  </div>

  <div style="display:flex;gap:12px;">
    <a href="{{ route('seller.dashboard') }}" class="btn btn--ghost">← Volver</a>
    <button type="submit" class="btn btn--primary" style="flex:1;">
      {{ __('messages.seller.submit_watch') }}
    </button>
  </div>
</form>
@endsection

@push('scripts')
<script>
// Show/hide doc upload based on box_papers selection
const bpSelect = document.getElementById('boxPapersSelect');
const docDiv   = document.getElementById('docUpload');
function toggleDoc() {
  const v = bpSelect.value;
  docDiv.style.display = (v === 'papers_only' || v === 'full_set') ? 'block' : 'none';
}
bpSelect.addEventListener('change', toggleDoc);
toggleDoc();

// Image preview
document.getElementById('imgInput').addEventListener('change', function() {
  const preview = document.getElementById('imgPreview');
  preview.innerHTML = '';
  Array.from(this.files).slice(0,10).forEach(file => {
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

// Doc file list
document.getElementById('docInput')?.addEventListener('change', function() {
  const list = document.getElementById('docFileList');
  list.innerHTML = '';
  Array.from(this.files).forEach(file => {
    const span = document.createElement('span');
    span.style.cssText = 'padding:4px 12px;background:var(--bg-elevated);border:1px solid var(--bg-border);border-radius:4px;font-size:.78rem;color:var(--text-secondary);';
    span.textContent = '📄 ' + file.name;
    list.appendChild(span);
  });
});

// Drag & drop
['imgDropzone','docDropzone'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener('dragover', e => { e.preventDefault(); el.classList.add('dragover'); });
  el.addEventListener('dragleave', () => el.classList.remove('dragover'));
  el.addEventListener('drop', e => {
    e.preventDefault(); el.classList.remove('dragover');
    const inp = el.querySelector('input[type="file"]');
    if (inp) { inp.files = e.dataTransfer.files; inp.dispatchEvent(new Event('change')); }
  });
});
</script>
@endpush
