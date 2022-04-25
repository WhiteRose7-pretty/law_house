<header class="front d-none {{!empty($bg) ? 'with-bg' : ''}}">
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark navbar-iusvitae-animate">
        <div class="container">
            <a class="navbar-brand" href="{{ route('welcome') }}">
                <img alt="ius vitae" src="/img/logo.png">
                Testy egzaminacyjne <span>online</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item {{Route::is('welcome') ? 'active' : ''}}">
                        <a class="nav-link" href="{{ route('welcome') }}">
                        @if(Auth::guest())
                        @else
                          Witaj, {{ Auth::user()->name }}
                        @endif
                        @if(Route::is('welcome'))
                            <span class="sr-only">(current)</span>
                        @endif
                        </a>
                    </li>
                    <li class="nav-item {{(Route::is('news') || Route::is('news.list')) ? 'active' : ''}}">
                        <a class="nav-link" href="{{route('news.list')}}">Aktualności</a>
                        @if(Route::is('news')||Route::is('news.list'))
                            <span class="sr-only">(current)</span>
                        @endif
                    </li>
                    @if(Auth::guest())
                        <li class="nav-item {{Route::is('register') ? 'active' : ''}}">
                            <a class="nav-link" href="{{ route('register') }}">Rejestracja
                              @if(Route::is('register'))
                                  <span class="sr-only">(current)</span>
                              @endif
                            </a>
                        </li>
                        <li class="nav-item {{Route::is('login') ? 'active' : ''}}">
                            <a class="nav-link" href="{{ route('login') }}">Logowanie
                              @if(Route::is('login'))
                                  <span class="sr-only">(current)</span>
                              @endif
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="/app/start">Testy
                            </a>
                        </li>
                        @if(Auth::user()->type != 'user')
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/start">Admin
                                </a>
                            </li>
                        @endif
                    @endif
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- ======= Header ======= -->
<header id="header" class="fixed-top d-flex align-items-center {{!empty($bg) ? 'with-bg' : ''}}">
    <div class="container d-flex justify-content-between align-items-center logo">


        <h1><img src="/img/logo.png" title="ustawoteka" alt="ustawoteka" class="img-fluid">
            <a href="{{ route('welcome') }}">ustawoteka</a></h1>

        <nav id="navbar" class="navbar">
            <ul>
                <li class="nav-item">
                    <a class="{{Route::is('welcome') ? 'active' : ''}}" href="{{ route('welcome') }}">
                        @if(Auth::guest())
                        @else
                            Witaj, {{ Auth::user()->name }}
                        @endif
                        @if(Route::is('welcome'))
                            <span class="sr-only">(current)</span>
                        @endif
                    </a>
                </li>
                <li class="">
                    <a class="{{(Route::is('news') || Route::is('news.list')) ? 'active' : ''}}" href="{{route('news.list')}}">Aktualności</a>
                    @if(Route::is('news')||Route::is('news.list'))
                        <span class="sr-only">(current)</span>
                    @endif
                </li>
                @if(Auth::guest())
                    <li class="nav-item">
                        <a class="{{Route::is('register') ? 'active' : ''}}" href="{{ route('register') }}">Rejestracja
                            @if(Route::is('register'))
                                <span class="sr-only">(current)</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="{{Route::is('login') ? 'active' : ''}}" href="{{ route('login') }}">Logowanie
                            @if(Route::is('login'))
                                <span class="sr-only">(current)</span>
                            @endif
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="" href="/app/start">Dashboard
                        </a>
                    </li>
                    @if(Auth::user()->type != 'user')
                        <li class="nav-item">
                            <a class="" href="/admin/start">Admin
                            </a>
                        </li>
                    @endif
                @endif
                <li><a href="{{ route('contact') }}">Contact Us</a></li>
            </ul>
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav><!-- .navbar -->
    </div>
</header><!-- End Header -->

@if(!empty($bg))
<div class="yh-gap-8"></div>
<div class="yh-gap-lg-12 yh-gap-4"></div>
@endif
