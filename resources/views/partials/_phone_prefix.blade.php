{{-- Selector de prefijo telefonico con bandera en PNG.
     Sostituisce il <select> nativo: le option non possono contenere immagini
     e su Windows le emoji bandiera degradano nelle due lettere del paese.
     Il valore viaggia in un input hidden, quindi il name resta invariato.
     Usage: @include('partials._phone_prefix', ['name' => 'phone_prefix', 'selected' => '+34']) --}}
@php
$prefixes = [
  ['+1','us','Estados Unidos'],
  ['+1','ca','Canadá'],
  ['+7','ru','Rusia'],
  ['+7','kz','Kazajistán'],
  ['+20','eg','Egipto'],
  ['+27','za','Sudáfrica'],
  ['+30','gr','Grecia'],
  ['+31','nl','Países Bajos'],
  ['+32','be','Bélgica'],
  ['+33','fr','Francia'],
  ['+34','es','España'],
  ['+36','hu','Hungría'],
  ['+39','it','Italia'],
  ['+40','ro','Rumanía'],
  ['+41','ch','Suiza'],
  ['+43','at','Austria'],
  ['+44','gb','Reino Unido'],
  ['+45','dk','Dinamarca'],
  ['+46','se','Suecia'],
  ['+47','no','Noruega'],
  ['+48','pl','Polonia'],
  ['+49','de','Alemania'],
  ['+51','pe','Perú'],
  ['+52','mx','México'],
  ['+53','cu','Cuba'],
  ['+54','ar','Argentina'],
  ['+55','br','Brasil'],
  ['+56','cl','Chile'],
  ['+57','co','Colombia'],
  ['+58','ve','Venezuela'],
  ['+60','my','Malasia'],
  ['+61','au','Australia'],
  ['+62','id','Indonesia'],
  ['+63','ph','Filipinas'],
  ['+64','nz','Nueva Zelanda'],
  ['+65','sg','Singapur'],
  ['+66','th','Tailandia'],
  ['+81','jp','Japón'],
  ['+82','kr','Corea del Sur'],
  ['+84','vn','Vietnam'],
  ['+86','cn','China'],
  ['+90','tr','Turquía'],
  ['+91','in','India'],
  ['+92','pk','Pakistán'],
  ['+93','af','Afganistán'],
  ['+94','lk','Sri Lanka'],
  ['+95','mm','Myanmar'],
  ['+98','ir','Irán'],
  ['+212','ma','Marruecos'],
  ['+213','dz','Argelia'],
  ['+216','tn','Túnez'],
  ['+218','ly','Libia'],
  ['+221','sn','Senegal'],
  ['+225','ci','Costa de Marfil'],
  ['+230','mu','Mauricio'],
  ['+233','gh','Ghana'],
  ['+234','ng','Nigeria'],
  ['+237','cm','Camerún'],
  ['+242','cg','Congo'],
  ['+243','cd','RD Congo'],
  ['+248','sc','Seychelles'],
  ['+249','sd','Sudán'],
  ['+251','et','Etiopía'],
  ['+252','so','Somalia'],
  ['+254','ke','Kenia'],
  ['+255','tz','Tanzania'],
  ['+256','ug','Uganda'],
  ['+258','mz','Mozambique'],
  ['+260','zm','Zambia'],
  ['+263','zw','Zimbabue'],
  ['+264','na','Namibia'],
  ['+266','ls','Lesoto'],
  ['+267','bw','Botsuana'],
  ['+268','sz','Esuatini'],
  ['+351','pt','Portugal'],
  ['+352','lu','Luxemburgo'],
  ['+353','ie','Irlanda'],
  ['+354','is','Islandia'],
  ['+355','al','Albania'],
  ['+356','mt','Malta'],
  ['+357','cy','Chipre'],
  ['+358','fi','Finlandia'],
  ['+359','bg','Bulgaria'],
  ['+370','lt','Lituania'],
  ['+371','lv','Letonia'],
  ['+372','ee','Estonia'],
  ['+373','md','Moldavia'],
  ['+374','am','Armenia'],
  ['+375','by','Bielorrusia'],
  ['+376','ad','Andorra'],
  ['+377','mc','Mónaco'],
  ['+380','ua','Ucrania'],
  ['+381','rs','Serbia'],
  ['+382','me','Montenegro'],
  ['+383','xk','Kosovo'],
  ['+385','hr','Croacia'],
  ['+386','si','Eslovenia'],
  ['+387','ba','Bosnia'],
  ['+389','mk','Macedonia del Norte'],
  ['+420','cz','Chequia'],
  ['+421','sk','Eslovaquia'],
  ['+423','li','Liechtenstein'],
  ['+502','gt','Guatemala'],
  ['+503','sv','El Salvador'],
  ['+504','hn','Honduras'],
  ['+505','ni','Nicaragua'],
  ['+506','cr','Costa Rica'],
  ['+507','pa','Panamá'],
  ['+591','bo','Bolivia'],
  ['+592','gy','Guyana'],
  ['+593','ec','Ecuador'],
  ['+595','py','Paraguay'],
  ['+597','sr','Surinam'],
  ['+598','uy','Uruguay'],
  ['+679','fj','Fiyi'],
  ['+675','pg','Papúa Nueva Guinea'],
  ['+685','ws','Samoa'],
  ['+852','hk','Hong Kong'],
  ['+853','mo','Macao'],
  ['+855','kh','Camboya'],
  ['+856','la','Laos'],
  ['+880','bd','Bangladés'],
  ['+886','tw','Taiwán'],
  ['+960','mv','Maldivas'],
  ['+961','lb','Líbano'],
  ['+962','jo','Jordania'],
  ['+963','sy','Siria'],
  ['+964','iq','Irak'],
  ['+965','kw','Kuwait'],
  ['+966','sa','Arabia Saudí'],
  ['+967','ye','Yemen'],
  ['+968','om','Omán'],
  ['+970','ps','Palestina'],
  ['+971','ae','Emiratos Árabes Unidos'],
  ['+972','il','Israel'],
  ['+973','bh','Baréin'],
  ['+974','qa','Catar'],
  ['+976','mn','Mongolia'],
  ['+977','np','Nepal'],
  ['+992','tj','Tayikistán'],
  ['+993','tm','Turkmenistán'],
  ['+994','az','Azerbaiyán'],
  ['+995','ge','Georgia'],
  ['+996','kg','Kirguistán'],
  ['+998','uz','Uzbekistán'],
];
$field = $name ?? 'phone_prefix';
$sel   = $selected ?? old($field, '+34');
$cur   = collect($prefixes)->firstWhere(0, $sel) ?? collect($prefixes)->firstWhere(1, 'es');
$uid   = 'pp' . substr(md5($field . uniqid('', true)), 0, 8);
$es    = app()->getLocale() === 'es';
@endphp

@once
<style>
.pp{position:relative;width:100%;font-family:var(--font-body),sans-serif;}
.pp__btn{display:flex;align-items:center;gap:9px;width:100%;background:transparent;border:none;
  border-bottom:1px solid #ddd;border-radius:0;padding:.62rem 0;font-family:inherit;font-size:15px;
  font-weight:300;color:var(--light-text,#1a1916);cursor:pointer;text-align:left;transition:border-color .25s;}
.pp__btn:hover,.pp.is-open .pp__btn{border-bottom-color:#111;}
.pp__flag{width:20px;height:15px;object-fit:contain;flex-shrink:0;image-rendering:-webkit-optimize-contrast;}
.pp__code{flex:1;min-width:0;}
.pp__car{width:9px;height:9px;border-right:1.4px solid #999;border-bottom:1.4px solid #999;
  transform:rotate(45deg) translate(-2px,-2px);flex-shrink:0;transition:transform .2s;}
.pp.is-open .pp__car{transform:rotate(-135deg) translate(-3px,-3px);}
.pp__panel{display:none;position:absolute;z-index:60;top:calc(100% + 4px);left:0;min-width:270px;max-width:340px;
  background:#fff;border:1px solid #ddd8cc;box-shadow:0 12px 34px rgba(0,0,0,.13);}
.pp.is-open .pp__panel{display:block;}
.pp__search{width:100%;border:none;border-bottom:1px solid #eee;padding:12px 14px;font-family:inherit;
  font-size:13px;font-weight:300;color:#1a1916;outline:none;background:#fff;}
.pp__search::placeholder{color:#aaa;}
.pp__list{max-height:266px;overflow-y:auto;padding:5px 0;}
.pp__opt{display:flex;align-items:center;gap:10px;width:100%;padding:9px 14px;background:none;border:none;
  cursor:pointer;font-family:inherit;font-size:13.5px;font-weight:300;color:#1a1916;text-align:left;}
.pp__opt:hover,.pp__opt.is-active{background:#f5f3ee;}
.pp__opt.is-sel{font-weight:500;}
.pp__name{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.pp__num{color:#8a857c;font-variant-numeric:tabular-nums;flex-shrink:0;}
.pp__none{padding:16px 14px;font-size:12.5px;font-weight:300;color:#999;}
</style>
@endonce

<div class="pp {{ $class ?? '' }}" id="{{ $uid }}" style="{{ $style ?? '' }}">
  <input type="hidden" name="{{ $field }}" value="{{ $sel }}" data-pp-value>

  <button type="button" class="pp__btn" data-pp-toggle aria-haspopup="listbox" aria-expanded="false">
    <img class="pp__flag" data-pp-flag src="{{ asset('images/flags/'.$cur[1].'.png') }}" alt="">
    <span class="pp__code" data-pp-code>{{ $cur[0] }}</span>
    <span class="pp__car" aria-hidden="true"></span>
  </button>

  <div class="pp__panel" role="listbox">
    <input type="text" class="pp__search" data-pp-search autocomplete="off"
           placeholder="{{ $es ? 'Buscar país o prefijo…' : 'Search country or prefix…' }}">
    <div class="pp__list" data-pp-list>
      @foreach($prefixes as [$code, $iso, $country])
        <button type="button" role="option"
                class="pp__opt {{ $sel === $code && $cur[1] === $iso ? 'is-sel' : '' }}"
                data-pp-opt data-code="{{ $code }}" data-iso="{{ $iso }}"
                data-search="{{ mb_strtolower($country) }} {{ $code }} {{ $iso }}">
          <img class="pp__flag" src="{{ asset('images/flags/'.$iso.'.png') }}" alt="" loading="lazy">
          <span class="pp__name">{{ $country }}</span>
          <span class="pp__num">{{ $code }}</span>
        </button>
      @endforeach
    </div>
    <div class="pp__none" data-pp-none style="display:none;">{{ $es ? 'Sin resultados' : 'No results' }}</div>
  </div>
</div>

<script>
(function () {
  var root = document.getElementById('{{ $uid }}');
  if (!root || root.dataset.ppInit) return;
  root.dataset.ppInit = '1';

  var val    = root.querySelector('[data-pp-value]');
  var btn    = root.querySelector('[data-pp-toggle]');
  var flag   = root.querySelector('[data-pp-flag]');
  var codeEl = root.querySelector('[data-pp-code]');
  var search = root.querySelector('[data-pp-search]');
  var list   = root.querySelector('[data-pp-list]');
  var none   = root.querySelector('[data-pp-none]');
  var opts   = Array.prototype.slice.call(root.querySelectorAll('[data-pp-opt]'));

  function open() {
    root.classList.add('is-open');
    btn.setAttribute('aria-expanded', 'true');
    search.value = ''; filter('');
    search.focus();
    var sel = root.querySelector('.pp__opt.is-sel');
    if (sel) list.scrollTop = Math.max(0, sel.offsetTop - 90);
  }
  function close() {
    root.classList.remove('is-open');
    btn.setAttribute('aria-expanded', 'false');
    opts.forEach(function (o) { o.classList.remove('is-active'); });
  }
  function filter(q) {
    q = q.trim().toLowerCase().replace(/^\+/, '');
    var n = 0;
    opts.forEach(function (o) {
      var hit = !q || o.dataset.search.replace(/\+/g, '').indexOf(q) !== -1;
      o.style.display = hit ? 'flex' : 'none';
      if (hit) n++;
    });
    none.style.display = n ? 'none' : 'block';
  }
  function pick(o) {
    val.value = o.dataset.code;
    codeEl.textContent = o.dataset.code;
    flag.src = o.querySelector('img').src;
    opts.forEach(function (x) { x.classList.remove('is-sel'); });
    o.classList.add('is-sel');
    close(); btn.focus();
    val.dispatchEvent(new Event('change', { bubbles: true }));
  }

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    root.classList.contains('is-open') ? close() : open();
  });
  opts.forEach(function (o) { o.addEventListener('click', function () { pick(o); }); });
  search.addEventListener('input', function () { filter(search.value); });

  search.addEventListener('keydown', function (e) {
    var vis = opts.filter(function (o) { return o.style.display !== 'none'; });
    if (!vis.length) return;
    var i = vis.indexOf(root.querySelector('.pp__opt.is-active'));
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      var next = e.key === 'ArrowDown' ? Math.min(i + 1, vis.length - 1) : Math.max(i - 1, 0);
      if (i > -1) vis[i].classList.remove('is-active');
      vis[next].classList.add('is-active');
      vis[next].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter') {
      e.preventDefault();
      pick(i > -1 ? vis[i] : vis[0]);
    } else if (e.key === 'Escape') {
      e.preventDefault(); close(); btn.focus();
    }
  });

  document.addEventListener('click', function (e) { if (!root.contains(e.target)) close(); });
  btn.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); }
  });
})();
</script>
