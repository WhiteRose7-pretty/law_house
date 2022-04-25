<template>
    <div>
        <div class="wrapper">

            <vue-confirm-dialog></vue-confirm-dialog>
            <nav id="sidebar" class="sidebar d-none">
                <button type="button" id="sidebarCollapse" class="navbar-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <perfect-scrollbar >
                    <div class="sidebar-header position-relative">
                        <router-link :to="{ name: 'start' }" class="d-flex align-items-center">
                            <img src="/img/logo.png" alt="" class="mt-0"/>
                            <h1 class="ml-2 mb-0">
                                Ustawoteka
                            </h1>
                        </router-link>
                        <span v-if="show_environment" style="background-color:#fff; color:#1C99AF; padding:2px; opacity:0.6;;">
                        {{show_environment}}
                    </span>
                    </div>

                    <ul class="list-unstyled components">
                        <li>
                            <router-link :to="{ name: 'start' }">
                            <span>
                                <i class="fas fa-headphones"></i>
                            </span>
                                Record
                            </router-link>
                        </li>

                        <li>
                            <router-link :to="{ name: 'shop' }">
                            <span>
                                <i class="fas fa-archive"></i>
                            </span>
                                Sklep
                            </router-link>
                        </li>
                        <li>
                            <a href="/lista/aktualnosci/" style="position: relative;" @click="clearNofityClick">
                            <span>
                                <i class="fas fa-bullhorn"></i>
                            </span>
                                Aktualności
                                <span v-if="notificationsCount"  style="
                                padding: 6px;
                                width: fit-content;
                                float: right;
                                font-weight: bold;
                                color: white;
                                font-size: 12px;
                                border-radius: 100px;">{{notificationsCount}}</span>
                            </a>
                        </li>

                        <li class="spacer"><hr/></li>

                        <li>
                            <a href="https://www.iusvitae.pl/" target="_blank">
                            <span>
                                <i class="fas fa-book"></i>
                            </span>
                                Księgarnia
                            </a>
                        </li>

                        <li>
                            <a href="/kontakt">
                            <span>
                                <i class="fas fa-envelope"></i>
                            </span>
                                Kontakt
                            </a>
                        </li>
                        <li>
                            <router-link :to="{ name: 'user' }">
                            <span>
                                <i class="fas fa-user-cog"></i>
                            </span>
                                Moje Konto
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
                            <a href="/informacje/regulamin">Regulamin</a>
                        </li>
                        <li>
                            <a href="/informacje/polityka-prywatnosci">Polityka Prywatności</a>
                        </li>
                        <li>
                            &copy; <em>ius vitae</em>
                        </li>
                    </ul>
                </perfect-scrollbar>



            </nav>

                <div id="content">
                    <div class="yh-gap-7"></div>

                    <router-view class="inner-wrapper"></router-view>

                    <div class="yh-gap-10"></div>
                </div>


        </div>

        <nav id="sidebar-right" class="sidebar-right active disabled">
            <button type="button" id="sidebarRightCollapse" class="navbar-btn active">
                <i class="fas fa-comments"></i>
                <div class="new-message-count"></div>
            </button>

            <div id="chat">
                <div id="chat-msg-list">
                </div>
                <div id="chat-msg-new">
                    <textarea placeholder="wiadomość"></textarea>
                    <button class="btn btn-outline-info"><i class="fas fa-reply"></i></button>
                </div>
            </div>
        </nav>

    </div>
</template>
<script>
    import axios from 'axios';

    export default {
        sockets: {
            connect: function () {
                console.log('socket connected')
            },
            customEmit: function (data) {
                console.log('this method was fired by the socket server. eg: io.emit("customEmit", data)')
            },
            yournotifications: function (count) {
                console.log("Notifications", count);
                this.notificationsCount = count;

            },
            newarticle: function (data) {
                this.notificationsCount += 1;
                console.log("Notifications", data);
            }
        },
        data() {
            return {
                crsf_token: '',
                user_name: null,
                show_environment: false,
                notificationsCount: 0,
            };
        },
        mounted() {

            this.$socket.emit('mynotifications');

            if ($("meta[name=csrf-token]").length) {
              this.crsf_token = $("meta[name=csrf-token]").attr('content');
            }
            this.user_name = yh.auth.user_name;
            if ($("meta[name=environemnt]").length) {
                var env = $("meta[name=environemnt]").attr('content');
                if (env == 'local' || env == 'staging' ) {
                    this.show_environment = env;
                }
            }
        },
        methods: {
            clearNofityClick: function (event) {
                this.$socket.emit('clearmynotifications');
            }
        }

    }
</script>
