{{-- Isotipo HAUS OF TIME (presentación, pág. 19–21): 8 rayos + chevron --}}
<svg class="{{ $class ?? 'hs-iso' }}" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="{{ $stroke ?? 3.2 }}" aria-hidden="true">
  @foreach([0,45,90,135,180,225,270,315] as $a)
    @php $r = deg2rad($a); @endphp
    <line x1="{{ round(50 + 31 * sin($r), 2) }}" y1="{{ round(50 - 31 * cos($r), 2) }}" x2="{{ round(50 + 46 * sin($r), 2) }}" y2="{{ round(50 - 46 * cos($r), 2) }}"/>
  @endforeach
  <polyline points="38.5,56.5 50,45 61.5,56.5"/>
</svg>
