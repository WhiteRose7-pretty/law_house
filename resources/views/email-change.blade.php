@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])

<div class="container ius">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0">
                <div class="card-header">
                    <h2>Zmiana adresu email</h2>
                </div>

                <div class="card-body">
                    @if(empty($error))
                    <div class="text-success pb-2">
                        Adres email został zmieniony.
                    </div>
                    @else
                    <div class="text-danger pb-2">
                        Adres email nie został zmieniony.<br>
                        {{$error}}
                    </div>
                    @endif
                    <a class="btn btn-success" href="/app/start">Przejdź do aplikacji</a>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
