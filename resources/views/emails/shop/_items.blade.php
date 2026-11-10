@php $m = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €'; @endphp
@foreach($items as $i)
<table class="item" width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr>
  <td width="76">@if($i->image_url)<img src="{{ $i->image_url }}" alt="" width="64">@endif</td>
  <td><div class="t">{{ $i->title }}</div><div class="s">{{ $i->reference ? 'Ref. ' . $i->reference : '' }}</div></td>
  <td class="amt">{{ $m($showPayout ?? false ? $i->seller_payout : $i->price - $i->discount) }}</td>
</tr></table>
@endforeach
