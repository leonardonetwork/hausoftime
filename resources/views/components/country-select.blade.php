{{-- resources/views/components/country-select.blade.php
     Usage: @include('components.country-select', ['name' => 'country', 'selected' => 'España'])
--}}
@php
$countries = [
  'España','Italia','Francia','Alemania','Reino Unido','Portugal','Suiza','Bélgica',
  'Países Bajos','Austria','Luxemburgo','Mónaco','Andorra','Liechtenstein',
  'Estados Unidos','Canadá','México','Argentina','Brasil','Chile','Colombia',
  'Uruguay','Venezuela','Perú','Ecuador','Bolivia','Paraguay',
  'China','Japón','India','Corea del Sur','Singapur','Hong Kong','Taiwán',
  'Emiratos Árabes Unidos','Arabia Saudita','Catar','Baréin','Kuwait','Omán',
  'Australia','Nueva Zelanda',
  'Rusia','Ucrania','Polonia','República Checa','Hungría','Rumanía',
  'Noruega','Suecia','Dinamarca','Finlandia','Islandia',
  'Turquía','Israel','Marruecos','Egipto','Sudáfrica',
  'Afganistán','Albania','Angola','Antigua y Barbuda','Armenia','Azerbaiyán',
  'Bangladés','Barbados','Bielorrusia','Belice','Benín','Birmania (Myanmar)',
  'Bolivia','Bosnia y Herzegovina','Botsuana','Brunéi','Bulgaria','Burkina Faso',
  'Burundi','Bután','Cabo Verde','Camboya','Camerún','Catar','Chad','Chipre',
  'Colombia','Comoras','Corea del Norte','Costa de Marfil','Costa Rica','Croacia',
  'Cuba','Dominica','Ecuador','Egipto','El Salvador','Eritrea','Eslovaquia',
  'Eslovenia','Estonia','Esuatini','Etiopía','Fiyi','Filipinas','Finlandia',
  'Gabón','Gambia','Georgia','Ghana','Granada','Grecia','Guatemala','Guinea',
  'Guinea-Bisáu','Guinea Ecuatorial','Guyana','Haití','Honduras','Hungría',
  'Indonesia','Irak','Irán','Irlanda','Islandia','Islas Marshall','Islas Salomón',
  'Jamaica','Jordania','Kazajistán','Kenia','Kirguistán','Kiribati','Laos',
  'Lesoto','Letonia','Líbano','Liberia','Libia','Lituania','Macedonia del Norte',
  'Madagascar','Malasia','Malaui','Maldivas','Malí','Malta','Mauricio',
  'Mauritania','Micronesia','Moldavia','Mongolia','Montenegro','Mozambique',
  'Namibia','Nauru','Nepal','Nicaragua','Níger','Nigeria','Pakistán','Palaos',
  'Panamá','Papúa Nueva Guinea','República Centroafricana','República Checa',
  'República Democrática del Congo','República del Congo','República Dominicana',
  'Ruanda','Samoa','San Cristóbal y Nieves','San Marino','San Vicente y las Granadinas',
  'Santa Lucía','Santo Tomé y Príncipe','Senegal','Serbia','Seychelles','Sierra Leona',
  'Siria','Somalia','Sri Lanka','Sudán','Sudán del Sur','Tailandia','Tanzania',
  'Tayikistán','Timor Oriental','Togo','Tonga','Trinidad y Tobago','Túnez',
  'Turkmenistán','Tuvalu','Uganda','Uzbekistán','Vanuatu','Vaticano',
  'Vietnam','Yemen','Zambia','Zimbabue'
];
sort($countries);
@endphp
<select name="{{ $name }}" class="form-control form-control--select">
  <option value="">— {{ app()->getLocale() === 'es' ? 'Seleccionar país' : 'Select country' }} —</option>
  @foreach($countries as $country)
    <option value="{{ $country }}" {{ (isset($selected) && $selected === $country) ? 'selected' : '' }}>
      {{ $country }}
    </option>
  @endforeach
</select>
