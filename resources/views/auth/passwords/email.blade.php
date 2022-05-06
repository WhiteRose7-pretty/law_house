@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])

<div class="container ius">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0">
                <div class="card-header">
                    <h2>Reset hasła</h2>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

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

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Wyślij
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
