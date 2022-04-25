@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])

<div class="container ius">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0">
                <div class="card-header">
                    <h2>Formularz kontaktowy</h2>
                </div>
                @if(empty($ok))
                <div class="card-body">
                    <form method="POST" action="{{ route('contact.send') }}">
                        @csrf

                        @if(!empty($user))

                        <label class="mdc-text-field mdc-text-field--filled @error('email') mdc-text-field--invalid @enderror" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" name="name" id="name" type="text" value="{{ $user->name }}" aria-labelledby="name-label" required autocomplete="name" disabled>
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
                            <input class="mdc-text-field__input" name="email" id="email" type="email" value="{{ $user->email }}" aria-labelledby="email-label" required autocomplete="email" disabled>
                            <span class="mdc-floating-label" id="email-label">E-mail</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                        @error('email')
                        <div class="mdc-text-field-helper-line">
                            <div class="text-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        @else

                        <label class="mdc-text-field mdc-text-field--filled @error('email') mdc-text-field--invalid @enderror" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" name="name" id="name" type="text" value="{{ old('name') }}" aria-labelledby="name-label" required autocomplete="name" autofocus>
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
                            <input class="mdc-text-field__input" name="email" id="email" type="email" value="{{ old('email') }}" aria-labelledby="email-label" required autocomplete="email" autofocus>
                            <span class="mdc-floating-label" id="email-label">E-mail</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                        @error('email')
                        <div class="mdc-text-field-helper-line">
                            <div class="text-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        @endif
                        <input type="hidden" name="r_message" value="{{$r_message !== ''?$r_message: ''}}">
                        <label class="mdc-text-field mdc-text-field--textarea mdc-text-field--no-label">
                            <textarea class="mdc-text-field__input" rows="8" cols="40" name="message" placeholder="treść wiadomości" autofocus>{{old('message')}}</textarea>
                            <span class="mdc-notched-outline">
                                <span class="mdc-notched-outline__leading"></span>
                                <span class="mdc-notched-outline__trailing"></span>
                            </span>
                        </label>
                        @error('message')
                        <div class="mdc-text-field-helper-line">
                            <div class="text-danger">{{ $message }}</div>
                        </div>
                        @enderror

                        <div class="form-group row mt-2 mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-success ">
                                    Prześlij
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @else
                <div class="card-body yh-fs-15 p-5">
                    Wiadomość została wysłana. <br/>Nasz pracownik skontaktuje się z Tobą w ciągu najbliższego dnia roboczego.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
