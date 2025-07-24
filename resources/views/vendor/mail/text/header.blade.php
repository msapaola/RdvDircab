@if (trim($slot) === 'Laravel')
Cabinet du Gouverneur de Kinshasa: {{ $url }}
@else
{{ $slot }}: {{ $url }}
@endif 