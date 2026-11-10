{{-- Partial riusabile: select paesi del mondo, default Spagna --}}
{{-- Usage: @include('partials._countries', ['name' => 'country', 'selected' => 'España']) --}}
@php
$countries = app()->getLocale() === 'en' ? [
  'Spain','Afghanistan','Albania','Algeria','Andorra','Angola','Argentina','Armenia',
  'Australia','Austria','Azerbaijan','Bahrain','Bangladesh','Belarus','Belgium','Bolivia',
  'Bosnia and Herzegovina','Brazil','Bulgaria','Cambodia','Cameroon','Canada','Chile',
  'China','Colombia','Costa Rica','Croatia','Cuba','Cyprus','Czech Republic','Denmark',
  'Dominican Republic','Ecuador','Egypt','El Salvador','Estonia','Ethiopia','Finland',
  'France','Georgia','Germany','Ghana','Greece','Guatemala','Honduras','Hong Kong',
  'Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy',
  'Ivory Coast','Japan','Jordan','Kazakhstan','Kenya','Kosovo','Kuwait','Kyrgyzstan',
  'Latvia','Lebanon','Libya','Liechtenstein','Lithuania','Luxembourg','Malaysia',
  'Maldives','Malta','Mexico','Moldova','Monaco','Montenegro','Morocco','Mozambique',
  'Myanmar','Nepal','Netherlands','New Zealand','Nicaragua','Nigeria','North Macedonia',
  'Norway','Oman','Pakistan','Panama','Paraguay','Peru','Philippines','Poland',
  'Portugal','Puerto Rico','Qatar','Romania','Russia','Saudi Arabia','Senegal',
  'Serbia','Singapore','Slovakia','Slovenia','South Africa','South Korea','Sri Lanka',
  'Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania','Thailand','Tunisia',
  'Turkey','Turkmenistan','Uganda','Ukraine','United Arab Emirates','United Kingdom',
  'United States','Uruguay','Uzbekistan','Venezuela','Vietnam','Yemen','Zimbabwe',
] : [
  'España','Afganistán','Albania','Algeria','Andorra','Angola','Argentina','Armenia',
  'Australia','Austria','Azerbaiyán','Bahréin','Bangladesh','Bielorrusia','Bélgica',
  'Bolivia','Bosnia Herzegovina','Brasil','Bulgaria','Camboya','Camerún','Canadá',
  'Chile','China','Colombia','Costa Rica','Croacia','Cuba','Chipre','República Checa',
  'Dinamarca','República Dominicana','Ecuador','Egipto','El Salvador','Estonia',
  'Etiopía','Finlandia','Francia','Georgia','Alemania','Ghana','Grecia','Guatemala',
  'Honduras','Hong Kong','Hungría','Islandia','India','Indonesia','Irán','Irak',
  'Irlanda','Israel','Italia','Costa de Marfil','Japón','Jordania','Kazajistán',
  'Kenia','Kosovo','Kuwait','Kirguistán','Letonia','Líbano','Libia','Liechtenstein',
  'Lituania','Luxemburgo','Malasia','Maldivas','Malta','México','Moldavia','Mónaco',
  'Montenegro','Marruecos','Mozambique','Myanmar','Nepal','Países Bajos','Nueva Zelanda',
  'Nicaragua','Nigeria','Macedonia del Norte','Noruega','Omán','Pakistán','Panamá',
  'Paraguay','Perú','Filipinas','Polonia','Portugal','Puerto Rico','Qatar','Rumanía',
  'Rusia','Arabia Saudita','Senegal','Serbia','Singapur','Eslovaquia','Eslovenia',
  'Sudáfrica','Corea del Sur','Sri Lanka','Suecia','Suiza','Siria','Taiwán',
  'Tayikistán','Tanzania','Tailandia','Túnez','Turquía','Turkmenistán','Uganda',
  'Ucrania','Emiratos Árabes','Reino Unido','Estados Unidos','Uruguay','Uzbekistán',
  'Venezuela','Vietnam','Yemen','Zimbabue',
];
$sel = $selected ?? old($name ?? 'country', app()->getLocale() === 'en' ? 'Spain' : 'España');
@endphp
<select name="{{ $name ?? 'country' }}" class="{{ $class ?? '' }}" style="{{ $style ?? '' }}">
  @foreach($countries as $c)
    <option value="{{ $c }}" {{ $sel === $c ? 'selected' : '' }}>{{ $c }}</option>
  @endforeach
</select>
