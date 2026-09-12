<x-guest-layout>
    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Check your email</h1>
    <p class="mt-2 text-sm leading-6 text-slate-500">
        Enter the 6-digit code we sent to <span class="font-medium text-slate-700">{{ $email }}</span>. It expires in 10 minutes.
    </p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form
        method="POST"
        action="{{ route('password.otp.store') }}"
        class="mt-8 space-y-5"
        x-data="{
            digits: ['', '', '', '', '', ''],
            get otp() { return this.digits.join('') },
            focusIndex(index) {
                this.$refs.otpInputs?.children[index]?.querySelector('input')?.focus()
            },
            onInput(index, event) {
                const value = event.target.value.replace(/\D/g, '').slice(-1)
                this.digits[index] = value
                event.target.value = value
                if (value && index < 5) {
                    this.focusIndex(index + 1)
                }
            },
            onKeydown(index, event) {
                if (event.key === 'Backspace' && ! this.digits[index] && index > 0) {
                    this.focusIndex(index - 1)
                }
            },
            onPaste(event) {
                const pasted = (event.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 6)
                if (! pasted) {
                    return
                }
                event.preventDefault()
                this.digits = ['', '', '', '', '', '']
                pasted.split('').forEach((digit, index) => { this.digits[index] = digit })
                this.focusIndex(Math.min(pasted.length, 5))
            }
        }"
        @paste="onPaste($event)"
    >
        @csrf
        <input type="hidden" name="otp" :value="otp">

        <div>
            <x-input-label for="otp-0" :value="__('Verification code')" />
            <div x-ref="otpInputs" class="flex justify-between gap-2">
                @for ($index = 0; $index < 6; $index++)
                    <div class="min-w-0 flex-1">
                        <input
                            id="otp-{{ $index }}"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            autocomplete="{{ $index === 0 ? 'one-time-code' : 'off' }}"
                            @if ($index === 0) autofocus @endif
                            class="w-full rounded-lg border-slate-300 bg-slate-50 px-0 py-3 text-center text-lg font-semibold text-slate-900 shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20"
                            x-on:input="onInput({{ $index }}, $event)"
                            x-on:keydown="onKeydown({{ $index }}, $event)"
                        >
                    </div>
                @endfor
            </div>
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Verify code') }}
        </x-primary-button>
    </form>

    <div
        class="mt-6 text-center text-sm text-slate-500"
        x-data="{
            seconds: 60,
            init() {
                const timer = setInterval(() => {
                    if (this.seconds <= 0) {
                        clearInterval(timer)
                        return
                    }
                    this.seconds--
                }, 1000)
            }
        }"
    >
        <form method="POST" action="{{ route('password.otp.resend') }}" x-show="seconds <= 0" x-cloak>
            @csrf
            <button type="submit" class="font-semibold hover:opacity-80" style="color: var(--brand)">
                Resend code
            </button>
        </form>
        <p x-show="seconds > 0">Resend code in <span class="font-semibold text-slate-700" x-text="seconds"></span>s</p>
    </div>

    <p class="mt-5 text-center text-sm text-slate-500">
        <a href="{{ route('password.request') }}" class="font-semibold hover:opacity-80" style="color: var(--brand)">Use a different email</a>
    </p>
</x-guest-layout>
