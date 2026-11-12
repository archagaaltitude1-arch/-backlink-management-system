@extends('layouts.customer')

@section('title', 'Sign In')

@section('content')
    <div class="mx-auto max-w-xl space-y-6">
        <div>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Customer access</p>
            <h1 class="mt-2 text-3xl font-semibold">Sign in</h1>
        </div>
        @if ($errors->any())
            <p class="rounded-xl bg-red-50 p-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</p>
        @endif
        <section class="panel space-y-5 p-6">
            <button id="google-sign-in" type="button" class="btn w-full border border-navy/15 bg-white hover:bg-canvas">
                Continue with Google
            </button>
            <p id="google-sign-in-error" class="text-sm text-red-800" role="alert" hidden></p>
            <p class="text-center text-sm text-navy/60">Or sign in with email</p>
            <form method="POST" action="{{ route('auth.authenticate') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium" for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1">
                    Remember me
                </label>
                <button class="btn-primary w-full" type="submit">Sign in</button>
            </form>
        </section>
        <p class="text-center text-sm text-navy/65">New here? <a class="font-semibold text-brand-dark hover:underline" href="{{ route('auth.register') }}">Create an account</a></p>
    </div>
@endsection

@push('scripts')
    <script type="module">
        const button = document.getElementById('google-sign-in');
        const error = document.getElementById('google-sign-in-error');
        const sdk = Promise.all([
            import('https://www.gstatic.com/firebasejs/13.2.0/firebase-app.js'),
            import('https://www.gstatic.com/firebasejs/13.2.0/firebase-auth.js'),
        ]).then(([appSdk, authSdk]) => ({
            ...authSdk,
            auth: authSdk.initializeAuth(appSdk.initializeApp(@js(config('services.firebase'))), {
                persistence: authSdk.inMemoryPersistence,
                popupRedirectResolver: authSdk.browserPopupRedirectResolver,
            }),
        })).catch(() => null);

        button.addEventListener('click', async () => {
            button.disabled = true;
            error.hidden = true;
            let firebase;

            try {
                firebase = await sdk;
                if (!firebase) {
                    error.textContent = 'Google sign-in could not load. Please try again.';
                    error.hidden = false;
                    return;
                }

                const result = await firebase.signInWithPopup(firebase.auth, new firebase.GoogleAuthProvider());
                const response = await fetch(@js(route('auth.google.firebase', absolute: false)), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ id_token: await result.user.getIdToken() }),
                });
                const data = await response.json();

                if (response.ok && data.redirect) {
                    window.location.assign(data.redirect);
                    return;
                }

                error.textContent = data.errors?.google?.[0] ?? data.message ?? 'Google sign-in failed. Please try again.';
                error.hidden = false;
            } catch (failure) {
                error.textContent = {
                    'auth/operation-not-allowed': 'Google sign-in is disabled in Firebase Authentication.',
                    'auth/unauthorized-domain': 'This website domain must be authorized in Firebase Authentication.',
                    'auth/popup-blocked': 'Allow pop-ups for this website and try again.',
                    'auth/popup-closed-by-user': 'Google sign-in was cancelled. Please try again.',
                }[failure.code] ?? 'Google sign-in failed. Please try again.';
                error.hidden = false;
            } finally {
                button.disabled = false;
                if (firebase?.auth.currentUser) {
                    await firebase.signOut(firebase.auth).catch(() => {});
                }
            }
        });
    </script>
@endpush
