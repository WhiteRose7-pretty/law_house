<template>
    <div class="wrapper">
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
            <div class="sidebar-container">

                <div class="sidebar-header position-relative">
                    <router-link :to="{ name: 'start' }">
                        <img src="/img/logo.png" alt="" />
                        <h1>
                            Ustawoteka
                        </h1>
                    </router-link>
                    <span v-if="show_environment" style="background-color:#fff; color:#BB6A44; padding:2px; opacity:0.6;;">
                        {{show_environment}}
                    </span>
                </div>

                <ul class="list-unstyled components">
                    <li>
                        <router-link :to="{ name: 'start' }">
                            <span>
                                <i class="fas fa-info"></i>
                            </span>
                            Start
                        </router-link>
                    </li>
                    <li v-if="is_admin" class="d-none">
                        <router-link :to="{ name: 'users' }">
                            <span>
                                <i class="fas fa-users"></i>
                            </span>
                            Użytkownicy
                        </router-link>
                    </li>
                    <li class="d-none">
                        <router-link :to="{ name: 'content' }">
                            <span>
                                <i class="fas fa-sitemap"></i>
                            </span>
                            Treści
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="{ name: 'documents' , params: { page: 1, limit:5}}">
                            <span>
                                <i class="fas fa-file-powerpoint"></i>
                            </span>
                            Dokumenty
                        </router-link>
                    </li>
                    <li class="d-none">
                        <router-link :to="{ name: 'questions' }">
                            <span>
                                <i class="fas fa-lightbulb"></i>
                            </span>
                            Pytania
                        </router-link>
                    </li>
                    <li v-if="is_admin || is_subadmin" class="d-none">
                        <router-link :to="{ name: 'packages' }">
                            <span>
                                <i class="fas fa-archive"></i>
                            </span>
                            Pakiety
                        </router-link>
                    </li>
                    <li v-if="is_admin" class="d-none">
                        <router-link :to="{ name: 'money' }">
                            <span>
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                            Płatności
                        </router-link>
                    </li>
                    <li v-if="is_subadmin" class="d-none">
                        <router-link :to="{ name: 'money1' }">
                            <span>
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                            Płatności
                        </router-link>
                    </li>
                    <li class="d-none">
                        <router-link :to="{ name: 'tests' }">
                            <span>
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            Test
                        </router-link>
                    </li>
                    <li class="d-none">
                        <router-link :to="{ name: 'emails' }">
                            <span>
                                <i class="fas fa-envelope"></i>
                            </span>
                            Emails
                        </router-link>
                    </li>
                    <li class="spacer"><hr/></li>
                    <li>
                        <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <span>
                                <i class="fas fa-sign-out-alt"></i>
                            </span>
                            Wyloguj
                        </a>

                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                            <input type="hidden" name="_token" v-bind:value="crsf_token">
                        </form>
                    </li>
                </ul>

                <ul class="list-unstyled CTAs">
                    <li>
                        <a href="/informacje/regulamin-administratora">Regulamin administratora</a>
                    </li>
                    <li>
                        &copy; <em>ius vitae</em>
                    </li>
                </ul>


            </div>

        </nav>

        <perfect-scrollbar class="content-ps" ref="scroll">
            <div id="content">
                <div v-if="user_name" class="float-right pr-2 yh-fs-16">
                    Witaj,
                    <strong>{{user_name}}</strong>
                </div>
                <div class="yh-gap-10"></div>

                <!-- @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
                @endif -->

                <router-view></router-view>

                <div class="yh-gap-10"></div>

            </div>
        </perfect-scrollbar>

    </div>
</template>

<script>
import axios from 'axios';
export default {
    data() {
        return {
            crsf_token: '',
            is_admin: false,
            user_name: null,
            show_environment: false,
            is_subadmin: false,
        };
    },
    mounted() {
        if ($("meta[name=csrf-token]").length) {
          this.crsf_token = $("meta[name=csrf-token]").attr('content');
        }
        console.log(yh.auth.user_type)
        this.is_admin = yh.auth.user_type === 'admin';
        this.is_subadmin = yh.auth.user_type === 'subadmin';
        this.user_name = yh.auth.user_name;
        if ($("meta[name=environemnt]").length) {
            var env = $("meta[name=environemnt]").attr('content');
            if (env == 'local' || env == 'staging' ) {
                this.show_environment = env;
            }
        }
    }
}
</script>
