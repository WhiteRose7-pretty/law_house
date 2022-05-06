@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])

<div class="container ius">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0">
                <div class="card-header">
                    <h2>Resetuj hasło</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <label class="mdc-text-field mdc-text-field--filled @error('email') mdc-text-field--invalid @enderror" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" name="email" id="email" type="email" value="{{ $email ?? old('email') }}" aria-labelledby="email-label" required autocomplete="email">
                            <span class="mdc-floating-label" id="email-label">E-mail</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                        @error('email')
                        <div class="mdc-text-field-helper-line">
                            <div class="text-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        <label class="mdc-text-field mdc-text-field--filled @error('password') mdc-text-field--invalid @enderror" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" id="password" name="password" type="password" aria-labelledby="password-label" required autocomplete="new-password">
                            <span class="mdc-floating-label" id="password-label">Hasło</span>
                            <span class="mdc-line-ripple"></span>
                        </label>

                        @error('password')
                        <div class="mdc-text-field-helper-line">
                            <div class="text-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" id="password-confirm" name="password_confirmation" type="password" aria-labelledby="password-confirm-label" required autocomplete="new-password">
                            <span class="mdc-floating-label" id="password-confirm-label">Potwierdź hasło</span>
                            <span class="mdc-line-ripple"></span>
                        </label>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Resetuj hasło
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
