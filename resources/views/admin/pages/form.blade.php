@extends('layouts.admin')
@section('title', isset($page) ? 'Editar página' : 'Nueva página')

@section('admin-content')
<div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;">
  <a href="{{ route('admin.pages.index') }}" class="btn btn--ghost btn--sm">← Volver</a>
  <div class="page-title">{{ isset($page) ? 'Editar: '.$page->title_es : 'Nueva página' }}</div>
  @if(isset($page))
  <div style="margin-left:auto;display:flex;gap:8px;">
    <form method="POST" action="{{ route('admin.pages.translate',$page) }}">@csrf
      <input type="hidden" name="locale" value="en">
      <button type="submit" class="btn btn--ghost btn--sm" onclick="return confirm('Traducir al inglés con IA?')">🤖 → EN</button>
    </form>
    <form method="POST" action="{{ route('admin.pages.translate',$page) }}">@csrf
      <input type="hidden" name="locale" value="es">
      <button type="submit" class="btn btn--ghost btn--sm" onclick="return confirm('Traducir al español con IA?')">🤖 → ES</button>
    </form>
  </div>
  @endif
</div>

@if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert--error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<form method="POST" action="{{ isset($page) ? route('admin.pages.update',$page) : route('admin.pages.store') }}" id="pageForm">
  @csrf
  @if(isset($page)) @method('PUT') @endif

  <div class="card" style="margin-bottom:20px;">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Slug (URL) *</label>
        <div style="display:flex;align-items:center;gap:6px;">
          <span style="color:var(--a-subtle);font-size:12px;">/</span>
          <input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug ?? '') }}" placeholder="terminos-y-condiciones" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Estado</label>
        <select name="active" class="form-control">
          <option value="1" {{ ($page->active ?? true) ? 'selected':'' }}>Activa</option>
          <option value="0" {{ !($page->active ?? true) ? 'selected':'' }}>Inactiva</option>
        </select>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:2px;background:var(--a-bg3);padding:3px;width:fit-content;margin-bottom:20px;border-radius:4px;">
    <button type="button" class="tab active" id="tab-es" onclick="switchLang('es')">🇪🇸 Español</button>
    <button type="button" class="tab" id="tab-en" onclick="switchLang('en')">🇬🇧 English</button>
  </div>

  <div id="lang-es">
    <div class="card" style="margin-bottom:20px;">
      <div class="form-group">
        <label class="form-label">Título (ES) *</label>
        <input type="text" name="title_es" class="form-control" value="{{ old('title_es', $page->title_es ?? '') }}" required>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Contenido (ES)</label>
        <div id="editor-es" style="min-height:400px"></div>
        <textarea name="content_es" id="hidden-es" style="display:none">{{ old('content_es', $page->content_es ?? '') }}</textarea>
      </div>
    </div>
  </div>

  <div id="lang-en" style="display:none">
    <div class="card" style="margin-bottom:20px;">
      <div class="form-group">
        <label class="form-label">Title (EN)</label>
        <input type="text" name="title_en" class="form-control" value="{{ old('title_en', $page->title_en ?? '') }}" placeholder="Auto-traducido si vacío">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Content (EN)</label>
        <div id="editor-en" style="min-height:400px"></div>
        <textarea name="content_en" id="hidden-en" style="display:none">{{ old('content_en', $page->content_en ?? '') }}</textarea>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:12px;">
    <a href="{{ route('admin.pages.index') }}" class="btn btn--ghost">Cancelar</a>
    <button type="submit" class="btn btn--primary" style="flex:1;">
      {{ isset($page) ? 'Guardar cambios' : 'Crear página' }}
    </button>
  </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.ql-toolbar.ql-snow{background:var(--a-bg2)!important;border-color:var(--a-border)!important;border-radius:3px 3px 0 0}
.ql-container.ql-snow{border-color:var(--a-border)!important}
.ql-editor{color:var(--a-text)!important;font-family:var(--font)!important;font-size:14px!important;min-height:380px;background:var(--a-bg3);border-radius:0 0 3px 3px}
.ql-editor.ql-blank::before{color:var(--a-subtle)!important}
.ql-snow .ql-stroke{stroke:var(--a-muted)!important}
.ql-snow .ql-fill{fill:var(--a-muted)!important}
.ql-snow .ql-picker{color:var(--a-muted)!important}
.ql-snow .ql-picker-options{background:var(--a-bg2)!important;border-color:var(--a-border)!important}
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
const toolbar = [
  [{ header:[1,2,3,false] }],
  ['bold','italic','underline'],
  [{ list:'ordered'},{ list:'bullet'}],
  ['link','blockquote'],
  [{ align:[] }],
  ['clean']
];

const qEs = new Quill('#editor-es', { theme:'snow', modules:{ toolbar } });
const qEn = new Quill('#editor-en', { theme:'snow', modules:{ toolbar } });

const esVal = document.getElementById('hidden-es').value;
const enVal = document.getElementById('hidden-en').value;
if (esVal.trim()) qEs.root.innerHTML = esVal;
if (enVal.trim()) qEn.root.innerHTML = enVal;

document.getElementById('pageForm').addEventListener('submit', function() {
  document.getElementById('hidden-es').value = qEs.root.innerHTML;
  document.getElementById('hidden-en').value = qEn.root.innerHTML;
});

function switchLang(lang) {
  ['es','en'].forEach(l => {
    document.getElementById('lang-'+l).style.display = l===lang?'block':'none';
    document.getElementById('tab-'+l).classList.toggle('active', l===lang);
  });
}
</script>
@endpush
