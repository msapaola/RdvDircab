@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ url('/images/logohvk.webp') }}" class="logo" alt="Logo Cabinet du Gouverneur de Kinshasa" style="height: 50px; width: auto;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr> 