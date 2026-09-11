@props(['messages'])
@if ($messages)
    @foreach ((array) $messages as $message)
        <p {{ $attributes->merge(['class' => 'text-xs text-rose-600']) }}>{{ $message }}</p>
    @endforeach
@endif
