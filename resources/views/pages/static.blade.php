@extends('layouts.app')
@section('title', $page->title)

@section('content')

{{-- Hero identico alla pagina contatti --}}
<div class="page-hero">
  <div class="page-hero__inner" style="max-width:1300px;margin:0 auto;width:100%;padding:0 60px;">
    <div class="page-hero__label">Legal</div>
    <h1 class="page-hero__title">{{ $page->title }}</h1>
  </div>
</div>

{{-- Contenuto — sfondo come contatti --}}
<div style="background:var(--light-bg);padding:100px 0 120px;">
  <div style="max-width:900px;margin:0 auto;padding:0 60px;">
    <div class="static-content">
      {!! $page->content !!}
    </div>
  </div>
</div>

@push('styles')
<style>
/* Contenuto pagina statica */
.static-content {
  background: #fff;
  border: 1px solid var(--light-border);
  padding: 64px 72px;
  font-family: var(--font-body);
  font-size: 15px;
  font-weight: 300;
  line-height: 1.9;
  color: var(--light-body);
}

/* Tipografia interna */
.static-content h1,
.static-content h2 {
  font-family: var(--font-display);
  font-weight: 300;
  color: var(--light-text);
  line-height: 1.1;
  margin: 2.5rem 0 1rem;
  letter-spacing: -.01em;
}
.static-content h1 { font-size: clamp(28px, 3vw, 42px); margin-top: 0; }
.static-content h2 { font-size: clamp(22px, 2.2vw, 32px); }
.static-content h3 {
  font-family: var(--font-body);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: .15em;
  text-transform: uppercase;
  color: var(--light-text);
  margin: 2rem 0 .75rem;
}
.static-content p {
  margin-bottom: 1.4rem;
  color: var(--light-body);
}
.static-content p:last-child { margin-bottom: 0; }
.static-content a {
  color: var(--light-text);
  text-decoration: underline;
  text-underline-offset: 3px;
}
.static-content ul,
.static-content ol {
  margin: 1rem 0 1.4rem 1.5rem;
}
.static-content li {
  margin-bottom: .5rem;
  color: var(--light-body);
}
.static-content strong { font-weight: 600; color: var(--light-text); }
.static-content em { font-style: italic; }
.static-content blockquote {
  border-left: 2px solid var(--light-border);
  padding: .75rem 0 .75rem 1.5rem;
  margin: 1.5rem 0;
  color: var(--light-muted);
  font-style: italic;
}
.static-content hr {
  border: none;
  border-top: 1px solid var(--light-border);
  margin: 2.5rem 0;
}

@media (max-width: 768px) {
  .static-content { padding: 36px 28px; }
  div[style*="padding:100px 0"] { padding: 60px 0 80px !important; }
  div[style*="padding:0 60px"] { padding: 0 24px !important; }
}
</style>
@endpush

@endsection
