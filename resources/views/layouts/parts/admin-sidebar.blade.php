<nav id="sidebar" class="sidebar">
    <button type="button" id="sidebarCollapse" class="navbar-btn">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <button type="button" id="sidebarApp" class="navbar-btn app-mode-switch">
        <a href="/app/start">
          <i class="fas fa-house-user"></i>
        </a>
    </button>
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
                    <i class="fas fa-users"></i>
                </span>
                Użytkownicy
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-sitemap"></i>
                </span>
                Treści
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-file-powerpoint"></i>
                </span>
                Dokumenty
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-lightbulb"></i>
                </span>
                Pytania
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-archive"></i>
                </span>
                Pakiety
            </a>
        </li>
        <li>
            <a href="#">
                <span>
                    <i class="fas fa-money-bill-wave"></i>
                </span>
                Płatności
            </a>
        </li>
        <li class="spacer"><hr/></li>
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
            <a href="#">Regulamin Przetwarzania Danych Osobowych</a>
        </li>
        <li>
            &copy; <em>ius vitae</em>
        </li>
    </ul>
</nav>
