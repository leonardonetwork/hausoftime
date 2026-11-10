{{-- resources/views/components/phone-prefix.blade.php
     Usage: @include('components.phone-prefix', ['name' => 'phone_prefix', 'selected' => '+34'])
--}}
@php
$prefixes = [
  'España +34'         => '+34',
  'Italia +39'         => '+39',
  'Francia +33'        => '+33',
  'Alemania +49'       => '+49',
  'Reino Unido +44'    => '+44',
  'Portugal +351'      => '+351',
  'EEUU +1'            => '+1',
  'México +52'         => '+52',
  'Argentina +54'      => '+54',
  'Colombia +57'       => '+57',
  'Brasil +55'         => '+55',
  'Chile +56'          => '+56',
  'Suiza +41'          => '+41',
  'Luxemburgo +352'    => '+352',
  'Mónaco +377'        => '+377',
  'Andorra +376'       => '+376',
  'Bélgica +32'        => '+32',
  'Países Bajos +31'   => '+31',
  'Austria +43'        => '+43',
  'Rusia +7'           => '+7',
  'China +86'          => '+86',
  'Japón +81'          => '+81',
  'India +91'          => '+91',
  'Australia +61'      => '+61',
  'Emiratos Árabes +971' => '+971',
  'Arabia Saudí +966'  => '+966',
  'Qatar +974'         => '+974',
  'Afganistán +93'     => '+93',
  'Albania +355'       => '+355',
  'Argelia +213'       => '+213',
];
@endphp
<select name="{{ $name }}" class="form-control form-control--select">
  @foreach($prefixes as $label => $val)
    <option value="{{ $val }}" {{ (isset($selected) && $selected === $val) ? 'selected' : '' }}>
      {{ $label }}
    </option>
  @endforeach
</select>
