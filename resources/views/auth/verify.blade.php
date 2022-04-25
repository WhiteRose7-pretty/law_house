@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])
<div class="container ius">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0">
                <div class="card-header">
                    <h2>Potwierdź swój adres email</h2>
                </div>

                <div class="card-body mb-2">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            Nowy link weryfikacyjny został przesłany na Twój adres email.
                        </div>
                    @endif
                    <p>
                    Zanim będziesz mógł kontynuować musisz potwierdzić swój adres email. Sprawdź swoją skrzynkę, również folder Spam.</p>
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <p>
                            Jeśli wciąż nie otrzymałeś maila z linkiem...

                              <button type="submit" class="btn btn-link p-0 m-0 align-baseline">Kliknij tutaj, wyślemy ponownie</button>.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
