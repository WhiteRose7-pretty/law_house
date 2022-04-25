@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])

<div class="container ius">
    <div class="row justify-content-center">
        <div class="col-md-8" style="max-width: 600px;">
            <div class="card border-0 mb-3">
                <div class="card-header pb-2 pb-lg-4">
                    <h2>Rejestracja</h2>
                </div>

                <div id="ie-warning" class="bg-danger text-white p-3 display-4 font-head" style="display:none; font-size:1.5em; font-weight: 400;">
                    Twoja przeglądarka (Internet Explorer), nie jest obecnie obsługiwana. Skorzystaj z jednej z najnowszych wersji przeglądarki: Microsoft Edge, Chrome, Firefox lub Safari.
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <label class="mdc-text-field mdc-text-field--filled @error('name') mdc-text-field--invalid @enderror" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" name="name" id="name" type="text" aria-labelledby="name-label" required autocomplete="name" autofocus>
                            <span class="mdc-floating-label" id="name-label">Imię</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                        @error('name')
                        <div class="mdc-text-field-helper-line">
                            <div class="text-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        <label class="mdc-text-field mdc-text-field--filled @error('email') mdc-text-field--invalid @enderror" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" name="email" id="email" type="email" value="{{ old('email') }}" aria-labelledby="email-label" required autocomplete="email">
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

                        <div class="mdc-form-field">
                            <div class="mdc-touch-target-wrapper">
                                <div class="mdc-checkbox mdc-checkbox--touch">
                                    <input type="checkbox" required
                                           class="mdc-checkbox__native-control"
                                           name="checkbox-2"
                                           id="checkbox-2" v-model="user.newsletter"/>
                                    <div class="mdc-checkbox__background">
                                        <svg class="mdc-checkbox__checkmark"
                                             viewBox="0 0 24 24">
                                            <path class="mdc-checkbox__checkmark-path"
                                                  fill="none"
                                                  d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                                        </svg>
                                        <div class="mdc-checkbox__mixedmark"></div>
                                    </div>
                                    <div class="mdc-checkbox__ripple"></div>
                                </div>
                            </div>
                            <label for="checkbox-2" class="cursor-pointer">Wyrażam zgodę na przetwarzanie moich danych osobowych zgodnie z ustawą o ochronie danych osobowych (Dz.U. z 2018 r., poz. 1000 ze zm.)</label>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12 mt-3 text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>

                        <div class="pt-2 text-center">
                            <a href="{{ route('login') }}">Posiadasz już konto? Zaloguj się!</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
  var ua = window.navigator.userAgent;
  var msie = ua.indexOf("MSIE ");
  if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))
  {
      $('#ie-warning').show();
  }
});
</script>
@endsection
