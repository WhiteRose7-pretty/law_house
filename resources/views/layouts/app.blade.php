@extends('layouts.base')

@section('head_content')
    <meta name="yh-auth-hash-id" content="{{$hash_id}}">
    <meta name="environemnt" content="{{App::environment()}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="/vendor/aos/aos.css" rel="stylesheet">
    <link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

    <script src="{{ mix('/js/app.js') }}"></script>
    <script src="https://kit.fontawesome.com/5800c1d1d9.js" crossorigin="anonymous"></script>
    <!-- Popper.JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"
            integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    @if(Config::get('app.env')=='local')
        <script src="http://localhost:3000/socket.io/socket.io.js"></script>
    @elseif(Config::get('app.env')=='production')
        <script src="https://testy.iusvitae.pl:3000/socket.io/socket.io.js"></script>
    @else
        <script src="http://localhost:3000/socket.io/socket.io.js"></script>
    @endif
@endsection

@section('body_class','app')

@section('body_content')

    <header id="header" class="fixed-top d-flex align-items-center" style="background: rgba(39, 70, 133, 0.8)">
        <div class="container d-flex justify-content-between align-items-center logo">
            <h1><img src="/img/logo.png" title="ustawoteka" alt="ustawoteka" class="img-fluid">
                <a href="/">ustawoteka</a></h1>

            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="active " href="/app/start">Record</a></li>
                    <li><a href="/app/shop">Shop</a></li>
                    <li><a href="{{ route('news.list') }}">News</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    <li><a href="/app/user">My account</a></li>
                    <li><a href="/logout">Logout</a></li>
                    @if(Auth::user()->type != 'user')
                        <li class="nav-item">
                            <a class="" href="/admin/start">Admin
                            </a>
                        </li>
                    @endif
                </ul>
                <i class="bi mobile-nav-toggle bi-list"></i>
            </nav><!-- .navbar -->
        </div>
    </header>
    <div id="app-loader">
        <div class="message">inicjalizacja...</div>
    </div>
    <div id="app">
        <app></app>
    </div>
@endsection

@section('body_script')
    <script src="/vendor/purecounter/purecounter.js"></script>
    <script src="/vendor/aos/aos.js"></script>
    <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="/vendor/php-email-form/validate.js"></script>

    {{--    <!-- Template Main JS File -->--}}
    <script src="/js/main.js"></script>

@endsection

@section('body_last')
@endsection
