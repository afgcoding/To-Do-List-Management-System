<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[color:var(--brand,#4F46E5)] focus:ring-offset-2']) }} style="background-color: var(--brand, #4F46E5)">
    {{ $slot }}
</button>
