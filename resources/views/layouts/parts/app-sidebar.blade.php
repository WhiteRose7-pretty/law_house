<nav id="sidebar" class="sidebar">
    <button type="button" id="sidebarCollapse" class="navbar-btn">
        <span></span>
        <span></span>
        <span></span>
    </button>
    @if(Auth::user()->type != 'user')
        <button type="button" id="sidebarAdmin" class="navbar-btn app-mode-switch">
            <a href="{{route('admin.home')}}">
              <i class="fas fa-tools"></i>
            </a>
        </button>
    @endif
    <div class="sidebar-header">
        <a href="{{ route('welcome' )}}">
            <img src="/img/logo.png" alt="" />
            <h1>
                Ustawoteka
            </h1>
        </a>
    </div>

    <ul class="list-unstyled components">
        <li class="active">
            <a href="#homeSubmenu">
                <span>
                    <i class="fas fa-home"></i>
                </span>
                Start
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-angle-double-right"></i>
                </span>
                Kontynuuj
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-plus"></i>
                </span>
                Nowy Test
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-redo"></i>
                </span>
                Powtarzaj
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-hand-scissors"></i>
                </span>
                Zagraj
            </a>
        </li>

        <li>
            <a href="#">
                <span>
                    <i class="fas fa-signal"></i>
                </span>
                Statystyki
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-check"></i>
                </span>
                Zakończone
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-lightbulb"></i>
                </span>
                Nauka pytań
            </a>
        </li>
        <li class="spacer"><hr/></li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-archive"></i>
                </span>
                Wykup pakiet
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-book"></i>
                </span>
                Księgarnia
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-bullhorn"></i>
                </span>
                Aktualności
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-envelope"></i>
                </span>
                Kontakt
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-user-cog"></i>
                </span>
                Moje Konto
            </a>
        </li>
        <li class="spacer"><hr/></li>
        <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span>
                    <i class="fas fa-sign-out-alt"></i>
                </span>
                Wyloguj
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>

    <ul class="list-unstyled CTAs">
        <li>
            <a href="#">Regulamin</a>
        </li>
        <li>
            <a href="#">Polityka Prywatności</a>
        </li>
        <li>
            &copy; <em>ius vitae</em>
        </li>
    </ul>
</nav>
