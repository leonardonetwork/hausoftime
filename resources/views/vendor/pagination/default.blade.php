@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;">
    <div style="font-size:.82rem;color:var(--text-muted);">
        Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
    </div>
    <div style="display:flex;gap:4px;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="padding:6px 14px;border:1px solid var(--bg-border);border-radius:4px;color:var(--text-muted);font-size:.82rem;cursor:not-allowed;">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="padding:6px 14px;border:1px solid var(--bg-border);border-radius:4px;color:var(--text-secondary);font-size:.82rem;transition:all .2s;" onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--bg-border)'">←</a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:6px 10px;color:var(--text-muted);font-size:.82rem;">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="padding:6px 14px;background:var(--gold);color:var(--bg-primary);border-radius:4px;font-size:.82rem;font-weight:600;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="padding:6px 14px;border:1px solid var(--bg-border);border-radius:4px;color:var(--text-secondary);font-size:.82rem;transition:all .2s;" onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--bg-border)'">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="padding:6px 14px;border:1px solid var(--bg-border);border-radius:4px;color:var(--text-secondary);font-size:.82rem;transition:all .2s;" onmouseover="this.style.borderColor='var(--gold)'" onmouseout="this.style.borderColor='var(--bg-border)'">→</a>
        @else
            <span style="padding:6px 14px;border:1px solid var(--bg-border);border-radius:4px;color:var(--text-muted);font-size:.82rem;cursor:not-allowed;">→</span>
        @endif
    </div>
</nav>
@endif
