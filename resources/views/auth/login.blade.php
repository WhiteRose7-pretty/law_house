@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])

<div class="container ius">
    <div class="row justify-content-center">
        <div class="col-md-8" style="max-width: 600px;">
            <div class="card border-0 mb-3">
                <div class="card-header pb-2 pb-lg-4">
                    <h2 class="mt-3">Logowanie</h2>
                </div>

                <div id="ie-warning" class="bg-danger text-white p-3 display-4 font-head" style="display:none; font-size:1.5em; font-weight: 400;">
                    Twoja przeglądarka (Internet Explorer), nie jest obecnie obsługiwana. Skorzystaj z jednej z najnowszych wersji przeglądarki: Microsoft Edge, Chrome, Firefox lub Safari.
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <label class="mdc-text-field mdc-text-field--filled @error('email') mdc-text-field--invalid @enderror" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" name="email" id="email" type="email" value="{{ old('email') }}" aria-labelledby="email-label" required autocomplete="email" autofocus>
                            <span class="mdc-floating-label" id="email-label">E-mail</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                        @error('email')
                        <div class="mdc-text-field-helper-line">
                            <div class="text-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" id="password" name="password" type="password" aria-labelledby="password-label" required autocomplete="current-password">
                            <span class="mdc-floating-label" id="password-label">Hasło</span>
                            <span class="mdc-line-ripple"></span>
                        </label>

                        <div class="row">
                            <div class="mdc-form-field col-md-6">
                                <div class="mdc-checkbox" style="margin-left:-10px;">
                                    <input type="checkbox" class="mdc-checkbox__native-control" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                                    <div class="mdc-checkbox__background">
                                        <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                            <path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59" />
                                        </svg>
                                        <div class="mdc-checkbox__mixedmark"></div>
                                    </div>
                                    <div class="mdc-checkbox__ripple"></div>
                                </div>
                                <label for="remember">Zapamiętaj mnie</label>
                            </div>
                        </div>

                        <div class="form-group row mt-5 mb-0">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    Zaloguj
                                </button>
                                <br />
                                @if (Route::has('password.request'))


                                <a class="d-block btn-link pt-4 text-body" href="{{ route('password.request') }}">
                                    Zapomniałeś hasła?
                                </a>
                                @endif
                                <a class="d-block btn-link pt-2 pb-4 text-body" href="{{ route('register') }}">
                                    Zarejestruj się.
                                </a>
                            </div>
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
