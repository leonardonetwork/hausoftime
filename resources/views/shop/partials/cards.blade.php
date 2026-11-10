@php
  $brandMap = $brandMap ?? \App\Models\WatchBrand::all()->flatMap(fn ($b) => array_filter([$b->name_es => $b, $b->name_en => $b]))->all();
@endphp
@foreach($watches as $watch)
  @include('shop.partials.card', ['watch' => $watch, 'brandMap' => $brandMap, 'favoriteIds' => $favoriteIds ?? []])
@endforeach
