<template>
    <div class="game">
        <h1 v-if="!root_game">Widok Gry</h1>
        <h1 v-else>
            {{ root_game.name }} &commat; {{ game_owner }}
        </h1>

        <div v-if="loading" class="text-primary">
            Ładowanie...
        </div>

        <div v-if="!loading && next_game && !refresh.game" class="game-summary">
            <p>
                Typ rozgrywki: <strong>wyścig</strong>
                <br>
                Ilość pytań: <strong>{{ next_game.questions_count }}</strong>
                <br>
                <span v-if="root_game.id == next_game.id">
                    <span v-if="!next_game.finished_at">
                        <span v-if="game_starts">
                        Rozpoczęcie: <strong>{{ game_starts }}
                            <span
                            v-if="next_game.auto_start">(automatycznie)</span></strong>
                        <br>
                        </span>
                        <span v-if="game_ends">
                        Zakończenie: <strong>{{ game_ends }}</strong>
                        </span>
                    </span>
                </span>
                <span v-if="next_game.finished_at">
                Zakończona: <strong>{{ next_game.finished_at }}</strong>
                </span>
                <span v-if="next_game.removed_at" class="text-danger">
                Usunięta: <strong>{{ next_game.removed_at }}</strong>
                </span>
            </p>

            <div class="clearfix">
                <button v-if="!next_game.started_at && !next_game.finished_at"
                        class="float-left btn btn-outline-success mr-3 mb-2" v-on:click="toggleShare()">
                    Zaproś
                </button>

                <button class="float-left btn btn-outline-info mb-2" v-on:click="chatFix()">
                    Czat
                </button>

                <button v-if="owner && !next_game.started_at && !next_game.finished_at"
                        class="float-right btn btn-outline-danger mb-2" v-on:click="forceFinish()">
                    Zakończ
                </button>

                <button v-if="owner && !next_game.started_at && !next_game.auto_start && anyone_ready"
                        class="float-right btn btn-outline-warning mb-2" v-on:click="forceStart()">
                    Rozpocznij
                </button>

                <!-- <button v-if="owner && game_can_add" class="float-right btn btn-outline-danger mr-3" v-on:click="finalizeGame()">
                    Finalizuj
                </button> -->

                <button v-if="owner && game_can_add" class="float-right btn btn-outline-success mr-3 mb-2"
                        v-on:click="addGame()">
                    Dodaj Etap
                </button>
            </div>

            <div id="share-link-container" v-if="game_share" class="card p-3 m-3" style="display:none;">
                <div class="card-head">
                    <strong>Udostępniając ten link umożliwiasz innym dołączenie się do rozgrywki</strong>
                </div>
                <div class="card-body">
                    <input id="share-link" type="text" v-bind:value="game_share" class="mb-1 w-100"/>
                    <button class="btn btn-primary btn-sm" v-on:click="copyShare()">Skopiuj</button>
                    <br><br>
                    <button class="btn btn-warning btn-sm" v-on:click="toggleShare()">Zamknij</button>
                </div>
            </div>
        </div>

        <h4 v-if="next_game && !refresh.users">Etap {{ all_games.length }}: {{ next_game.name }}</h4>
        <div class="game-participants" v-if="next_game && !refresh.users">

            <div v-if="!is_ready && !has_left && !is_host"
                 style="background-color: rgba(255,0,0,0.05); border-radius:10px; padding: 15px 20px; margin-bottom: 20px;">
                <span v-if="in_game">
                    Potwierdź swoją gotowość do gry, klikając przycisk Gotowy na liście graczy.<br>
                    <span v-if="!owner" class="btn btn-sm btn-outline-danger"
                          v-on:click="gameLeaveAsk()">Opuść grę</span>
                </span>
                <span v-else>
                    Nie zgłosiłeś swojej chęci partycypacji w grze lub opuściłeś gre.<br>
                    <span class="btn btn-sm btn-outline-info" v-on:click="join()">Dołącz do Gry</span>
                </span>
            </div>
            {{ listIndex(true) }}
            <div class="row d-none d-md-flex">
                <div class="col-1"></div>
                <div class="col-1"></div>
                <div class="col"><strong>Gracz</strong></div>
                <div class="col"><strong>Wynik</strong></div>
                <div class="col"><strong>Status</strong></div>
            </div>
            <div v-for="p in next_game.participants" class="row">
                <div class="col-1">{{ listIndex() }}</div>
                <div class="col-1">
                    <i class="fas fa-check-circle text-success" v-if="p.online" title="gracz się przyłączył"></i>
                    <i class="fas fa-check-circle text-dark yh-v-o-5" title="gracz nie jest podłączony" v-else></i>
                    <i class="fas fa-crown text-warning" v-if="p.owner" title="gracz jest administratorem"></i>
                </div>
                <div class="col">{{ p.name }}</div>
                <div class="col">
                    <span v-if="p.finished_at">{{ p.questions_answered_correct_count }}</span>
                    <span v-else-if="p.started_at">w trakcie</span>
                    <span v-else>-</span>
                </div>
                <div class="col">
                    <span v-if="p.owner_host_only" class="text-info">Host</span>
                    <span v-if="p.left_at" class="text-warning">Zrezygnował</span>
                    <span v-if="p.ready && !p.banned_at && !p.finished_at" class="text-success">Gotowy</span>
                    <span v-if="p.finished_at" class="text-info">Zakończył</span>
                    <span v-if="p.banned_at" class="text-danger">Zbanowany</span>
                    <span v-if="!next_game.removed_at">
                        <span v-if="!p.banned_at">
                            <span v-if="p.user_id == yh.auth.user_id && !p.left_at && !p.ready && !p.owner_host_only"
                                  class="btn btn-sm btn-outline-success" v-on:click="gameReady()">Gotowy</span>
                            <span
                                v-if="p.owner && p.user_id == yh.auth.user_id && !p.left_at && !p.ready && !p.owner_host_only"
                                class="btn btn-sm btn-outline-info" v-on:click="gameHostOnly()">Host</span>
                            <span
                                v-if="p.user_id == yh.auth.user_id && !p.left_at && !p.finished_at && !p.started_at && (p.ready || p.owner_host_only)"
                                class="btn btn-sm btn-outline-warning" v-on:click="gameUnReady(p.owner_host_only)">Anuluj</span>
                        </span>
                        <span v-if="owner && !p.owner && !p.left_at && !p.banned_at"
                              class="btn btn-sm btn-outline-danger" v-on:click="gameBanAsk(p.user_id,true)">Banuj</span>
                        <span v-if="owner && !p.owner && !p.left_at && p.banned_at" class="btn btn-sm btn-outline-info"
                              v-on:click="gameBanAsk(p.user_id,false)">OdBanuj</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-4" v-for="(item, index) in all_games">
            <div v-if="index > 0">
                <h4>Etap {{ all_games.length - index }}: {{ item.name }}</h4>
                <div class="game-participants">
                    {{ listIndex(true) }}
                    <div class="row d-none d-md-flex">
                        <div class="col-1"></div>
                        <div class="col"><strong>Gracz</strong></div>
                        <div class="col"><strong>Wynik</strong></div>
                    </div>
                    <div v-for="p in item.participants" class="row">
                        <div class="col-1">{{ listIndex() }}</div>
                        <div class="col">{{ p.name }}</div>
                        <div class="col">
                            {{ p.questions_answered_correct_count }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3" v-if="summary && root_game.id != next_game.id">
            <h3>Podsumowanie wszystkich etapów</h3>
            <div class="game-participants">
                {{ listIndex(true) }}
                <div class="row d-none d-md-flex">
                    <div class="col-1"></div>
                    <div class="col"><strong>Gracz</strong></div>
                    <div class="col"><strong>Wynik</strong></div>
                </div>
                <div v-for="p in summary" class="row">
                    <div class="col-1">{{ listIndex() }}</div>
                    <div class="col">{{ p.name }}</div>
                    <div class="col">
                        {{ p.questions_answered_correct_count }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            loading: false,
            error: null,
            root_game: null,
            next_game: null,
            all_games: null,
            summary: null,
            game_ends: '',
            game_share: null,
            game_starts: '',
            game_can_add: false,
            game_can_start: false,
            owner: false,
            io: null,
            in_game: false,
            is_banned: false,
            is_ready: false,
            is_host: false,
            has_finished: false,
            has_left: false,
            refresh: {
                game: false,
                users: false,
                send: false,
            },
            timeout: null,
            timeout_set: false,
            yh: null,
            list_index: 0,
            new_message_count: 0,
            time_diff: '',
        };
    },
    created() {
        if (typeof this.$route.params.refresh != 'undefined' && this.$route.params.refresh != null) {
            this.refresh.send = true;
        }
        this.fetch(this.$route.params.hash_key);
        this.yh = yh;
    },
    destroyed() {
        $('#chat-msg-new').off('click', 'button', this.chatFunction);
        $('#sidebar-right').addClass('disabled');
        $('#chat-msg-list').html('');
        if (typeof this.io != 'undefined' && this.io != null) {
            this.io.emit('room_leave', {
                game_id: this.next_game.id,
                user: yh.auth
            });
        }
        this.timeout_set = false;
        if (this.timeout != null) {
            clearTimeout(this.timeout);
        }
    },
    mounted() {
        SideBarCollapseIfActive(true);
        $('#sidebar-right').removeClass('disabled');
    },
    updated() {
    },
    computed: {
        game_owner: function () {
            for (var i = 0; i < this.next_game.participants.length; i++) {
                if (this.next_game.participants[i].owner) {
                    return this.next_game.participants[i].name;
                }
            }
        },
        anyone_ready: function () {
            for (var i = 0; i < this.next_game.participants.length; i++) {
                if (this.next_game.participants[i].ready == true) {
                    return true;
                }
            }
            return false;
        },
    },
    methods: {
        fetch(hash_key, refresh = false) {
            this.error = null;
            this.loading = true;
            if (refresh) {
                if (typeof this.io != 'undefined') {
                    this.io.emit('room_leave', {
                        game_id: this.root_game.id,
                        user: yh.auth
                    });
                }
            }
            axios
                .post(
                    '/api/app/games/view/race',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: hash_key,
                    }
                )
                .then(response => {
                    this.root_game = JSON.parse(JSON.stringify(response.data.root_game));
                    this.next_game = JSON.parse(JSON.stringify(response.data.next_game));
                    this.all_games = JSON.parse(JSON.stringify(response.data.all_games));
                    this.summary = JSON.parse(JSON.stringify(response.data.summary));
                    this.owner = JSON.parse(JSON.stringify(response.data.owner));
                    this.in_game = JSON.parse(JSON.stringify(response.data.in_game));
                    this.is_host = JSON.parse(JSON.stringify(response.data.is_host));
                    this.is_ready = JSON.parse(JSON.stringify(response.data.is_ready));
                    this.is_banned = JSON.parse(JSON.stringify(response.data.is_banned));
                    this.has_left = JSON.parse(JSON.stringify(response.data.has_left));
                    this.has_finished = JSON.parse(JSON.stringify(response.data.has_finished));
                    this.game_can_add = JSON.parse(JSON.stringify(response.data.game_can_add));
                    let server_time = new Date(JSON.parse(JSON.stringify(response.data.server_time)) + " UTC+0000");
                    let browser_time = new Date();
                    this.time_diff = browser_time.getUTCHours() - server_time.getUTCHours();
                    console.log(this.time_diff);
                    this.loading = false;
                    if (this.next_game.id == this.root_game.id) {
                        this.fixTimes(true);
                    }
                    this.ioSetup(this.root_game.id);
                    if (!refresh) {
                        var url = window.location.href
                        var arr = url.split("/");
                        this.game_share = arr[0] + '//' + arr[2] + '/app/games/join/' + this.root_game.hash_key;
                    }
                    this.gameStartedCheck();
                    if (refresh && typeof this.io != 'undefined') {
                        this.io.emit('room_get', {
                            game_id: this.root_game.id
                        });
                    }
                }).catch(error => {
                this.loading = false;
                console.log(error)
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        listIndex(reset = false) {
            if (reset) {
                this.list_index = 0;
                return null;
            }
            this.list_index++;
            return this.list_index;
        },
        addGame() {
            var _this = this;
            this.message(
                'Dodawanie etapu',
                'Czy na pewno chcesz dodać nowy etap wyścigu?',
                function () {
                    _this.$router.push({name: 'games.new', params: {hash_key: _this.root_game.hash_key}});
                },
                true
            );
        },
        chatFix() {
            $('#sidebar-right').removeClass('disabled');
            $('#sidebar-right').toggleClass('active');
            $('#sidebarRightCollapse').toggleClass('active');
        },
        check_new_message(data) {
            if (data.user.user_id == yh.auth.user_id || data.read.includes(yh.auth.user_id)) {
                return 0;
            }
            return 1;
        },
        chatProcessMsg(data) {
            this.new_message_count = this.new_message_count + this.check_new_message(data);
            if (!this.new_message_count) {
                $('.new-message-count').css('display', 'none');
            } else {
                $('.new-message-count').css('display', 'initial');
            }
            $('.new-message-count').text(this.new_message_count);
            if (data.user.user_id == yh.auth.user_id) {
                var html = '<div class="chat-msg chat-msg-yours">';
            } else {
                var html = '<div class="chat-msg">';
                html += '<div class="chat-msg-user">';
                html += data.user.user_name + '(' + data.user.user_id + ')';
                html += '</div>';
            }
            html += '<div class="chat-msg-text">';
            html += data.msg;
            html += '</div>';
            html += '</div>';
            $('#chat-msg-list').append(html);
            $('#chat-msg-list').scrollTop($('#chat-msg-list')[0].scrollHeight);
        },
        chatFunction() {
            if ($('#chat-msg-new textarea').val()) {
                this.io.emit('msg', {
                    game_id: this.root_game.id,
                    user: yh.auth,
                    msg: $('#chat-msg-new textarea').val()
                });
                $('#chat-msg-new textarea').val('');
            }
        },
        readMessage() {
            this.new_message_count = 0;
            $('.new-message-count').css('display', 'none');
            this.io.emit('read', {
                game_id: this.root_game.id,
                user: yh.auth,
            });
        },
        chatSetup(data) {
            $('#chat-msg-list').html('');
            for (var i = 0; i < data.length; i++) {
                this.chatProcessMsg(data[i]);
            }
            this.io.on('msg', (data) => {
                this.chatProcessMsg(data);
            });
            if (this.in_game && !this.is_banned) {
                $('#chat-msg-new textarea').attr('placeholder', 'wiadomość');
                $('#chat-msg-new textarea').removeAttr('disabled');
                $('#chat-msg-new button').show();
                $('#chat-msg-new').on('click', 'button', this.chatFunction);
                $('#sidebar-right').on('click', 'button', this.readMessage);
            } else {
                $('#chat-msg-new textarea').attr('placeholder', 'musisz dołączyć do gry by korzystać z czatu');
                $('#chat-msg-new textarea').attr('disabled', 'disabled');
                $('#chat-msg-new button').hide();
            }
        },
        checkTimesTimeout() {
            if (this.timeout_set) {
                var _this = this;
                this.timeout = setTimeout(function () {
                    _this.fixTimes(true);
                }, 1000)
            }
        },
        copyShare() {
            var copyText = document.getElementById("share-link");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            /* Copy the text inside the text field */
            document.execCommand("copy");

            this.message('Potwierdzenie', 'Skopiowano do schowka');
        },
        dateF(s) {
            var r = s.split('T');
            var s2 = r[0];
            s2 = this.replaceAll(s2, '-', '/');
            return s2;
        },
        fixTimes(push = false) {
            if (this.next_game.finished_at) {
                this.timeout_set = false;
                this.game_can_start = false;
                if (!this.next_game.finish_checked_at) {
                    var _this = this;
                    setTimeout(function () {
                        _this.finishCheckAll();
                    }, 5000);
                }
                return;
            }
            if (this.next_game.started_at) {
                this.timeout_set = true;
                this.game_starts = this.next_game.started_at;
                this.game_can_start = false;
                this.game_ends = this.timeDiff(this.next_game.time_limit, this.next_game.started_at, 'W każdej chwili');
                if (this.game_ends == 'W każdej chwili') {
                    this.timeout_set = false;
                    var _this = this;
                    setTimeout(function () {
                        _this.finishCheckAll();
                    }, 5000);
                }
            }
            else if (this.next_game.starts_at) {
                this.game_starts = this.timeDiff(0, this.next_game.starts_at, 'W każdej chwili');
                if (this.game_starts == 'W każdej chwili') {
                    this.game_can_start = true;
                } else {
                    this.timeout_set = true;
                }
            } else {
                this.game_starts = 'Dowolnie';
                this.game_can_start = true;
            }
            if (this.game_can_start && this.next_game.auto_start) {
                this.forceStart();
            }
            this.checkTimesTimeout();
        },
        forceStartAsk() {
            var _this = this;
            this.message(
                'Zakończenie gry',
                'Czy na pewno chcesz rozpocząć rozgrywkę?',
                function () {
                    _this.forceStart();
                },
                true
            );

        },
        forceStart() {
            this.error = null;
            axios
                .post(
                    '/api/app/games/start',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.root_game.hash_key,
                    }
                )
                .then(response => {
                    this.next_game.started_at = JSON.parse(JSON.stringify(response.data.started_at));
                    this.refresh.game = true;
                    this.refresh.game = false;
                    if (this.root_game.id == this.next_game.id) {
                        this.fixTimes(true);
                    }

                    if (typeof this.io != 'undefined') {
                        this.io.emit('game_start', {
                            game_id: this.root_game.id,
                            started_at: this.next_game.started_at,
                        });
                    }
                    this.gameStartedCheck();
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        forceFinishAsk() {
            var _this = this;

            this.message(
                'Zakończenie gry',
                'Czy na pewno chcesz wymusić zakończenie gry jeszcze zanim się rozpocznie, nie będziesz mógł tego cofnąć',
                function () {
                    _this.forceFinish();
                },
                true
            );
        },
        forceFinish() {
            this.error = null;
            axios
                .post(
                    '/api/app/games/forceFinish',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.next_game.hash_key,
                    }
                )
                .then(response => {
                    this.next_game = JSON.parse(JSON.stringify(response.data.game));
                    if (typeof this.io != 'undefined') {
                        this.io.emit('room_refresh', {
                            game_id: this.root_game.id
                        });
                    }
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        gameBanAsk(user_id, flag) {
            var _this = this;
            this.message('Potwierdź', 'Czy na pewno chcesz kontynuować?', function () {
                _this.gameBan(user_id, flag);
            }, true);
        },
        gameBan(user_id, flag) {
            this.error = null;
            axios
                .post(
                    '/api/app/games/ban',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.root_game.hash_key,
                        user_id: user_id,
                        flag: flag,
                    }
                )
                .then(response => {
                    this.processUserBan(user_id, flag);
                    this.io.emit('room_ban', {
                        game_id: this.root_game.id,
                        user_id: user_id,
                        flag: flag
                    });
                    this.io.emit('room_get', {
                        game_id: this.room_game.id
                    });
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        gameHostOnly() {
            this.error = null;
            axios
                .post(
                    '/api/app/games/hostOnly',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.root_game.hash_key,
                    }
                )
                .then(response => {
                    this.fetch(this.root_game.hash_key, true);
                    if (typeof this.io != 'undefined') {
                        this.io.emit('room_refresh', {
                            game_id: this.root_game.id
                        });
                    }
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        gameLeave() {
            this.error = null;
            axios
                .post(
                    '/api/app/games/leave',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.root_game.hash_key,
                    }
                )
                .then(response => {
                    this.in_game = false;
                    this.has_left = false;
                    if (typeof this.io != 'undefined') {
                        this.io.emit('room_refresh', {
                            game_id: this.root_game.id
                        });
                        this.io.emit('room_leave', {
                            game_id: this.root_game.id,
                            user: yh.auth
                        });
                    }
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        gameLeaveAsk() {
            var _this = this;
            this.message(
                'Wyjście z gry',
                'Nie będziesz mógł się ponownie przyłączyć, czy na pewno chcesz wyjść?',
                function () {
                    _this.gameLeave();
                },
                true
            );
        },
        gameReady() {
            this.error = null;
            axios
                .post(
                    '/api/app/games/ready',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.root_game.hash_key,
                    }
                )
                .then(response => {
                    this.is_ready = true;
                    this.loading = false;
                    this.processUserReady(yh.auth.user_id);
                    this.io.emit('ready', {
                        game_id: this.root_game.id,
                        user: yh.auth
                    });
                    this.gameStartedCheck();
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        gameStartedCheck() {

            if (this.next_game.finished_at || !this.next_game.started_at || !this.is_ready || this.has_finished) {
                return;
            }

            this.$router.push({
                name: 'games.run.race',
                params: {hash_key: this.root_game.hash_key, id: this.next_game.id}
            });
        },
        gameUnReady(refresh) {
            this.error = null;
            axios
                .post(
                    '/api/app/games/unflag',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.root_game.hash_key,
                    }
                )
                .then(response => {
                    this.is_ready = false;
                    this.loading = false;
                    if (!refresh) {
                        this.processUserUnReady(yh.auth.user_id);
                        this.io.emit('unready', {
                            game_id: this.root_game.id,
                            user: yh.auth
                        });
                    } else {
                        this.fetch(this.root_game.hash_key, true);
                        if (typeof this.io != 'undefined') {
                            this.io.emit('room_refresh', {
                                game_id: this.root_game.id
                            });
                        }
                    }
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd', this.error);
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        ioSetup() {
            if (typeof io == 'undefined') {
                this.message('Błąd połączenia z serwerem', 'Serwer gier jest niedostępny.');
            }

            var hostname = window.location.hostname;
            this.io = io('https://' + hostname + ':3000/');
            this.io.on('u_room_joined', (data) => {
                this.chatSetup(data.chat);
                this.processUsers(data.users);
            });
            this.io.on('u_room_left', () => {
                this.io.disconnect();
            });
            this.io.on('room_joined', (data) => {
                this.processUserOnline(data.user.user_id);
            });
            this.io.on('room_left', (data) => {
                this.processUserOffline(data.user.user_id);
            });
            this.io.on('room_ban', (data) => {
                if (data.user_id != yh.auth.user_id) {
                    this.processUserBan(data.user_id, data.flag);
                } else {
                    this.fetch(this.root_game.hash_key, true);
                }
            });
            this.io.on('room_refresh', () => {
                this.fetch(this.root_game.hash_key, true);
            });
            this.io.on('room_get', (data) => {
                this.processUsers(data.users);
            });
            this.io.on('ready', (data) => {
                this.processUserReady(data.user.user_id);
            });
            this.io.on('unready', (data) => {
                this.processUserUnReady(data.user.user_id);
            });
            this.io.on('chat', (data) => {
            });
            this.io.on('game_start', (data) => {
                this.next_game.started_at = data.started_at;
                this.refresh.game = true;
                this.refresh.game = false;
                if (this.root_game.id = this.next_game.id) {
                    this.fixTimes(true);
                }
                this.gameStartedCheck();
            });
            this.io.on('connect', () => {
                this.io.emit('room_join', {
                    game_id: this.root_game.id,
                    user: yh.auth
                });
                if (this.refresh.send) {
                    this.io.emit('room_refresh', {
                        game_id: this.root_game.id
                    });
                    this.refresh.send = false;
                }
            });
        },
        join() {
            this.$router.push({name: 'games.join', params: {hash_key: this.root_game.hash_key}});
        },
        message(title, msg, callback = null, no = false) {
            setTimeout(function () {
                $('.vc-container').center();
            }, 50);
            var _this = this;
            if (no) {
                this.$confirm(
                    {
                        title: title,
                        message: msg,
                        button: {
                            yes: 'Tak',
                            no: 'Nie'
                        },
                        /**
                         * Callback Function
                         * @param {Boolean} confirm
                         */
                        callback: confirm => {
                            if (confirm) {
                                if (typeof callback == 'function') {
                                    callback();
                                }
                            }
                        }
                    }
                );
            } else {
                this.$confirm(
                    {
                        title: title,
                        message: msg,
                        button: {
                            yes: 'OK'
                        },
                        /**
                         * Callback Function
                         * @param {Boolean} confirm
                         */
                        callback: confirm => {
                            if (confirm) {
                                if (typeof callback == 'function') {
                                    callback();
                                }
                            }
                        }
                    }
                );
            }
        },
        processUsers(data) {
            for (var p in data) {
                this.processUserOnline(data[p].user_id);
            }
        },
        processUserBan(id, flag) {
            var err = true;
            for (var i = 0; i < this.next_game.participants.length; i++) {
                if (this.next_game.participants[i].user_id == id) {
                    this.next_game.participants[i].banned_at = flag;
                    err = false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.root_game.hash_key, true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserOnline(id) {
            var err = true;
            for (var i = 0; i < this.next_game.participants.length; i++) {
                if (this.next_game.participants[i].user_id == id) {
                    this.next_game.participants[i].online = true;
                    err = false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.root_game.hash_key, true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserOffline(id) {
            var err = true;
            for (var i = 0; i < this.next_game.participants.length; i++) {
                if (this.next_game.participants[i].user_id == id) {
                    this.next_game.participants[i].online = false;
                    err = false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.root_game.hash_key, true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserReady(id) {
            var err = true;
            for (var i = 0; i < this.next_game.participants.length; i++) {
                if (this.next_game.participants[i].user_id == id) {
                    this.next_game.participants[i].ready = true;
                    err = false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.root_game.hash_key, true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserUnReady(id) {
            var err = true;
            for (var i = 0; i < this.next_game.participants.length; i++) {
                if (this.next_game.participants[i].user_id == id) {
                    this.next_game.participants[i].ready = false;
                    err = false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.root_game.hash_key, true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        replaceAll(string, search, replace) {
            return string.split(search).join(replace);
        },
        timeDiff(limit, date, ifpasslimit = 'czas upłynął') {
            var d = new Date(date + " UTC+0000");
            var dn = new Date();
            var diff = Math.round((dn.getTime() - d.getTime()) / 1000);

            diff = diff - this.time_diff * 3600;

            if (diff > limit * 60) {
                return ifpasslimit;
            }
            diff = limit * 60 - diff;
            var h = Math.floor(diff / (60 * 60));
            if (h > 23) {
                return d.getFullYear() + '-' + (d.getMonth() < 10 ? '0' : '') + (d.getMonth() + 1) + '-' + (d.getUTCDate() < 10 ? '0' : '') + d.getUTCDate() + ' ' + d.getUTCHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes();
            }
            var m = Math.floor((diff - h * 60 * 60) / 60);
            var s = diff - h * 60 * 60 - m * 60;
            var res = '';
            if (h > 0) {
                res = h + ':';
            }
            if (h > 0 || m > 0) {
                res = res + (m < 10 ? '0' + m : m) + ':';
                res = res + (s < 10 ? '0' + s : s);
            } else {
                res = '' + s + ' sek.';
            }
            return res;
        },
        timeDiffCalc(total_sec) {
            var h = Math.floor(total_sec / (60 * 60));
            var m = Math.floor((total_sec - h * 60 * 60) / 60);
            var s = total_sec - h * 60 * 60 - m * 60;
            var res = '';
            if (h > 0) {
                res = h + ':';
            }
            if (h > 0 || m > 0) {
                res = res + (m < 10 ? '0' + m : m) + ':';
                res = res + (s < 10 ? '0' + s : s);
            } else {
                res = '' + s + ' sek.';
            }
            return res;
        },
        toggleShare() {
            $('#share-link-container').toggle();
        },
    }
}
</script>
