<x-guest-layout>
    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Verify your email</h1>
    <p class="mt-2 text-sm leading-6 text-slate-500">
        {{ __('Thanks for signing up. Confirm your email address using the link we sent you. If it did not arrive, we can send another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-xl bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-8 space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-center text-sm font-semibold text-slate-500 hover:text-slate-800">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
