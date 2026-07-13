<x-guest-layout>

    <h4 class="text-center mb-4">Login</h4>

    <!-- Session Status -->
    <x-auth-session-status class="alert alert-info mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" class="form-label" />
            <x-text-input id="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="invalid-feedback d-block" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" class="form-label" />

            <x-text-input id="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="invalid-feedback d-block" />
        </div>

        <!-- Remember Me -->
        <div class="mb-3 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label class="form-check-label" for="remember_me">Remember me</label>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            @if (Route::has('password.request'))
                <a class="text-decoration-none icon-link icon-link-hover" style="--bs-link-hover-color-rgb: 25, 135, 84;" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            @endif

                <x-primary-button>
                    {{ __('Log in') }}
                </x-primary-button>
        </div>
    </form>
</x-guest-layout>
