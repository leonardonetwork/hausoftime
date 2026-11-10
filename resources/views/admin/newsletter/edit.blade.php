@extends('layouts.admin')
@section('title', 'Editar campaña: ' . $campaign->name)

@section('admin-content')
<div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;">
  <a href="{{ route('admin.newsletter.index') }}" class="btn btn--ghost btn--sm">← Volver</a>
  <div class="page-title">Editar campaña</div>
</div>

@if($errors->any())
  <div class="alert alert--error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('admin.newsletter.update', $campaign) }}" style="max-width:920px;">
  @csrf @method('PUT')

  <div class="card" style="margin-bottom:20px;">
    <div class="card__num" style="margin-bottom:20px;">Configuración</div>
    <div class="form-group">
      <label class="form-label">Nombre interno de la campaña</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $campaign->name) }}" required>
    </div>
    <div class="form-group">
      <label class="form-label">Destinatarios</label>
      <select name="recipient_type" class="form-control form-control--select" required>
        @foreach(['all'=>'Todos los suscriptores','buyers'=>'Solo compradores','sellers'=>'Solo vendedores'] as $v=>$l)
          <option value="{{ $v }}" {{ old('recipient_type', $campaign->recipient_type) === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px;">
    <div class="card__num" style="margin-bottom:20px;">Contenido</div>
    <div style="display:flex;gap:2px;background:var(--bg-elevated);padding:4px;border-radius:8px;width:fit-content;margin-bottom:24px;">
      <button type="button" class="tab active" id="tab-es" onclick="switchLang('es')">🇪🇸 Español</button>
      <button type="button" class="tab" id="tab-en" onclick="switchLang('en')">🇬🇧 English</button>
    </div>

    <div id="lang-es">
      <div class="form-group">
        <label class="form-label">Asunto (ES)</label>
        <input type="text" name="subject_es" class="form-control" value="{{ old('subject_es', $campaign->subject_es) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Cuerpo del email (ES)</label>
        <div class="editor-wrap"><div id="editor-es" style="min-height:300px;padding:16px;"></div></div>
        <textarea name="body_es" id="body_es" style="display:none;">{{ old('body_es', $campaign->body_es) }}</textarea>
      </div>
    </div>

    <div id="lang-en" style="display:none;">
      <div class="form-group">
        <label class="form-label">Subject (EN)</label>
        <input type="text" name="subject_en" class="form-control" value="{{ old('subject_en', $campaign->subject_en) }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email body (EN)</label>
        <div class="editor-wrap"><div id="editor-en" style="min-height:300px;padding:16px;"></div></div>
        <textarea name="body_en" id="body_en" style="display:none;">{{ old('body_en', $campaign->body_en) }}</textarea>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:12px;">
    <a href="{{ route('admin.newsletter.index') }}" class="btn btn--ghost">Cancelar</a>
    <button type="submit" class="btn btn--primary" style="flex:1;">Guardar cambios</button>
  </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.ql-snow .ql-stroke{stroke:var(--text-secondary)!important}
.ql-snow .ql-fill{fill:var(--text-secondary)!important}
.ql-snow .ql-picker{color:var(--text-secondary)!important}
.ql-toolbar.ql-snow{background:var(--bg-card)!important;border-color:var(--bg-border)!important}
.ql-container.ql-snow{border-color:var(--bg-border)!important;color:var(--text-primary)!important}
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
const opts = {theme:'snow',modules:{toolbar:[['bold','italic','underline'],[{'header':[1,2,3,false]}],[{'list':'ordered'},{'list':'bullet'}],['link'],['clean']]}};
const qEs = new Quill('#editor-es', opts);
const qEn = new Quill('#editor-en', opts);
const bodyEs = document.getElementById('body_es').value;
const bodyEn = document.getElementById('body_en').value;
if (bodyEs) qEs.root.innerHTML = bodyEs;
if (bodyEn) qEn.root.innerHTML = bodyEn;
document.querySelector('form').addEventListener('submit', () => {
  document.getElementById('body_es').value = qEs.root.innerHTML;
  document.getElementById('body_en').value = qEn.root.innerHTML;
});
function switchLang(lang) {
  ['es','en'].forEach(l => {
    document.getElementById('lang-'+l).style.display = l === lang ? 'block' : 'none';
    document.getElementById('tab-'+l).classList.toggle('active', l === lang);
  });
}
</script>
@endpush
