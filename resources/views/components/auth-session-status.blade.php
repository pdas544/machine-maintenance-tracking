@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-info']) }}>
        {{ $status }}
    </div>
@endif
