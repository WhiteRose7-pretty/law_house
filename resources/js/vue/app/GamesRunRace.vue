<template>
    <div class="game-run test-run">
        <div class="card mb-2" v-if="loading||error">
            <div class="card-body">
                <div v-if="loading" class="text-info">
                    Ładowanie...
                </div>
                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>
            </div>
        </div>

        <div class="card mb-2" v-if="game && game.finished_at">
            <div class="text-info p-5">
                Przekierowanie...
            </div>
        </div>

        <div class="card" v-if="!loading && game && !game.finished_at">
            <div class="card-header">
                <span>
                    Postęp {{ Math.round((game.questions_answered_count + answered) / game.questions_count * 100) }}%
                </span>

                <span v-if="game.questions[index].help_text">
                    <div class="btn btn-outline-secondary btn-mic yh-v-o-25 mr-1">
                       <i class="fas fa-graduation-cap" v-tooltip="tooltip(game.questions[index].help_text)"></i>
                    </div>
                </span>


                <div class="float-right">
                    <span class="yh-fw-6 yh-v-o-60">{{ game.name }}</span>
                </div>
            </div>
            <div class="card-body" v-if="index == game.questions.length">
                <div class="question">
                    <span>
                        Koniec gry, za chwile nastąpi przekierowanie
                    </span>
                </div>
            </div>
            <div v-else class="card-body">
                <div v-if="!processing">
                    <div class="question">
                        {{ game.questions[index].question }}
                    </div>
                    <div>
                        <div v-for="(i, idx) in get_order()" v-bind:id="'answer-'+idx" class="answer row"
                             v-bind:data-index="index" v-bind:data-i="i-1">
                            <div class="answer-input col-1">
                                <input v-bind:id="'answer-input-'+idx" type="radio" name="answer" v-bind:data-value="i"
                                       v-bind:value="i" v-on:click="selectAnswer(index,i-1)"
                                       v-if="typeof game.questions[index].answer == 'undefined'" :checked="false">
                                <input v-bind:id="'answer-input-'+idx" type="radio" name="answer" v-bind:data-value="i"
                                       v-bind:value="i" v-on:click="selectAnswer(index,i-1)" v-else
                                       :checked="game.questions[index].answer.question_option_id == game.questions[index].options[i-1].id">
                            </div>
                            <div class="answer-label col-11">
                                <label
                                    v-bind:for="'answer-input-'+idx">{{
                                        game.questions[index].options[i - 1].option
                                    }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>

                    {{ processing = false }}

                </div>
            </div>
            <div class="card-footer">
                <div class="float-left yh-v-o-70">
                    <span class="btn btn-outline-secondary btn-sm yh-v-o-30" v-on:click="keyboard()">
                      <i class="fa fa-keyboard"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="game-participants mt-5" v-if="participants && !refresh.users">
            <div v-for="p in participants" class="row">
                <div class="col">{{ p.name }}</div>
                <div class="col">
                    <span v-if="p.started_at">{{ p.questions_answered_correct_count }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Vue from 'vue';

export default {
    data() {
        return {
            loading: false,
            error: null,
            game: null,
            participants: null,
            game_ends: '',
            game_share: null,
            game_starts: '',
            game_can_start: false,
            owner: false,
            io: null,
            in_game: false,
            is_banned: false,
            is_ready: false,
            is_host: false,
            has_left: false,
            refresh: {
                game: false,
                users: false,
            },
            timeout: null,
            timeout_set: false,
            yh: null,
            answered: 0,
            index: 0,
            new_answers: false,
            processing: false,
            sending: false,
            skipped: 0,
            out_of_time: false,
            sidebar_fix: false,
            hash_key: null,
            new_message_count: 0,
            random_array: {
                value: [1, 2, 3],
                default: 0,
            }
        };
    },
    created() {
        this.hash_key = this.$route.params.hash_key;
        this.fetch(this.$route.params.hash_key, this.$route.params.id);
        this.yh = yh;
        console.log('created');
    },
    destroyed() {
        console.log('destroyed');
        $(window).off('keydown');
        $('#chat-msg-new').off('click', 'button', this.chatFunction);
        $('#sidebar-right').addClass('disabled');
        $('#chat-msg-list').html('');
        if (typeof this.io != 'undefined' && this.io != null) {
            this.io.emit('room_leave', {
                game_id: this.game.id,
                user: yh.auth
            });
        }
        this.timeout_set = false;
        if (this.timeout != null) {
            clearTimeout(this.timeout);
        }
        if (this.sidebar_fix) {
            SideBarShowIfDisabled();
        }
    },
    mounted() {
        SideBarCollapseIfActive(true);
        $('#sidebar-right').removeClass('disabled');
        this.sidebar_fix = SideBarCollapseIfActive();

        var _this = this;
        $(window).on('keyup', function (e) {
            if ($("#chat textarea").is(":focus")) {
                return;
            }
            if (e.which < 49 || e.which > 51) {
                return;
            }
            let key_index = e.which - 49;
            console.log("key", key_index + 1);
            const selected_answer = $('#answer-input-' + key_index);
            selected_answer.prop("checked", true);
            let option_index = selected_answer.attr('value');
            console.log("option", option_index);
            _this.selectAnswer(_this.index, option_index - 1);
        });
        console.log('mounted');
    },
    updated() {
    },
    beforeRouteLeave(to, from, next) {
        console.log('beforeRouteLeave');
        if (!this.new_answers && !this.sending) {
            next();
            return;
        }
        setTimeout(function () {
            $('.vc-container').center();
        }, 50);
        this.$confirm(
            {
                message: 'Odpowiedzi czekają w kolejce na przesłanie. Najlepiej poczekaj kilka sekund aż zgaśnie napis AUTOZAPIS. Możesz kontynuować, ale istnieje szansa utracenia odpowiedzi. Czy chcesz kontynuować?',
                button: {
                    no: 'Nie',
                    yes: 'Tak'
                },
                /**
                 * Callback Function
                 * @param {Boolean} confirm
                 */
                callback: confirm => {
                    if (confirm) {
                        next();
                        return;
                    }
                    if (!this.new_answers && !this.sending) {
                        next();
                        return;
                    }
                    next(false);
                }
            }
        );
    },
    computed: {},
    methods: {
        tooltip(text){
            return text
        },
        shuffle(array) {
            var currentIndex = array.length, temporaryValue, randomIndex;
            // While there remain elements to shuffle...
            while (0 !== currentIndex) {

                // Pick a remaining element...
                randomIndex = Math.floor(Math.random() * currentIndex);
                currentIndex -= 1;

                // And swap it with the current element.
                temporaryValue = array[currentIndex];
                array[currentIndex] = array[randomIndex];
                array[randomIndex] = temporaryValue;
            }
            return array;
        },
        get_random() {
            let answer_array = [1, 2, 3];
            this.shuffle(answer_array);
            this.random_array.value = answer_array;
        },
        get_order() {
            return this.random_array.value;
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
                    game_id: this.game.id,
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
                game_id: this.game.id,
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
            var _this = this;
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
        checkGame() {
            var _this = this;
            if (this.game.type != 'race') {
                this.message('Zły typ gry', 'Ten moduł obsługuje jedynie grę typu wyścig', function () {
                    _this.$router.push({name: 'games.view.race', params: {hash_key: _this.hash_key}});
                });
                return false;
            }
            if (!this.game.started_at) {
                this.message('Gra się jeszcze nie zaczęła', 'Ta gra się jeszcze nie zaczęła', function () {
                    _this.$router.push({name: 'games.view.race', params: {hash_key: _this.hash_key}});
                });
                return false;
            }
            if (this.game.finished_at) {
                this.message('Gra się już zakończyła', 'Ta gra się już zakończyła', function () {
                    _this.$router.push({name: 'games.view.race', params: {hash_key: _this.hash_key}});
                });
                return false;
            }
            return true;
        },
        selectAnswer(index, i) {

            $('.answer-input input').blur(); // fix for keyboard and touchpad conflict (in case user uses both keyboard and touchpad)

            if (!this.game.questions[index].options[i].correct) {
                return this.selectAnswerWrong(index, i);
            }

            this.game.questions[index].answer = {
                question_id: this.game.questions[index].id,
                question_option_id: this.game.questions[index].options[i].id,
                correct: this.game.questions[index].options[i].correct,
            };

            var _index = index;
            var _this = this;
            this.new_answers = true;
            this.answered = this.answered + 1;
            setTimeout(function () {
                if (_this.index == _index) {
                    _this.index = _index + 1;
                    _this.processing = true;
                    _this.get_random();
                }
            }, 1000);
            setTimeout(function () {
                _this.get_random();
                _this.processing = true;
                _this.sendAnswers();
            }, 1000); //5000
        },
        selectAnswerWrong(index, i) {
            var q = JSON.parse(JSON.stringify(this.game.questions[index]));
            var qln = [];
            for (var idx = 0; idx < index; idx++) {
                qln.push(JSON.parse(JSON.stringify(this.game.questions[idx])));
            }
            for (idx = index + 1; idx < this.game.questions.length; idx++) {
                qln.push(JSON.parse(JSON.stringify(this.game.questions[idx])));
            }
            qln.push(JSON.parse(JSON.stringify(q)));
            var _this = this;
            setTimeout(function () {
                _this.game.questions = JSON.parse(JSON.stringify(qln));
                _this.processing = true;
                _this.get_random();
            }, 1000);
        },
        sendAnswers(finish = false) {
            if (this.sending) {
                if (this.new_answers || finish) {
                    var _this = this;
                    setTimeout(function () {
                        _this.sendAnswers(finish);
                    }, 500);
                    return;
                }
            }
            if (!this.new_answers && !finish) {
                // to jest wyścig, poszedł już równoległy request, a nie jest to zamknięcie testu
                return;
            }
            this.sending = true;
            this.new_answers = false;
            axios
                .post(
                    '/api/app/games/race/answers',
                    {
                        hash_id: yh.auth.hash_id,
                        game: this.game,
                        finish: finish,
                    }
                )
                .then(response => {
                    this.sending = false;
                    if (typeof this.io != 'undefined') {
                        this.io.emit('room_refresh', {
                            game_id: this.game.id
                        });
                    }
                    if (typeof this.io != 'undefined' && typeof response.data.finished == 'undefined') {
                        this.io.emit('race_refresh', {
                            game_id: this.game.id
                        });
                    }
                    if (typeof response.data.finished != 'undefined' && typeof this.io != 'undefined') {
                        this.io.emit('race_finished', {
                            game_id: this.game.id
                        });
                    }
                    if (typeof response.data.redirect != 'undefined') {
                        this.$router.push({name: 'games.view.race', params: {hash_key: this.hash_key}});
                    } else {
                        this.fetchParticipants();
                    }
                }).catch(error => {
                this.sending = false;
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.redirect) {
                    if (typeof this.io != 'undefined') {
                        this.io.emit('race_refresh', {
                            game_id: this.game.id
                        });
                        this.io.emit('room_refresh', {
                            game_id: this.game.id
                        });
                    }
                    this.$router.push({name: 'games.view.race', params: {hash_key: this.hash_key}});
                }
            });
        },
        keyboard() {
            setTimeout(function () {
                $('.vc-container').center();
            }, 50);
            this.$confirm(
                {
                    title: 'Obsługa klawiatury',
                    message: 'Użyj klawiszy 1-3 by wybrać odpowiedź.',
                    button: {
                        yes: 'OK',
                    }
                },
            );
        },
        fetch(hash_key, id) {
            this.error = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/games/race/continue',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: hash_key,
                        id: id,
                    }
                )
                .then(response => {
                    this.game = JSON.parse(JSON.stringify(response.data.game));
                    if (!this.checkGame()) {
                        return;
                    }
                    if (typeof this.io != 'undefined' && this.io != null && response.data.room_refresh) {
                        this.io.emit('room_refresh', {
                            game_id: this.game.id
                        });
                    }
                    this.owner = JSON.parse(JSON.stringify(response.data.owner));
                    this.in_game = JSON.parse(JSON.stringify(response.data.in_game));
                    this.is_host = JSON.parse(JSON.stringify(response.data.is_host));
                    this.is_ready = JSON.parse(JSON.stringify(response.data.is_ready));
                    this.is_banned = JSON.parse(JSON.stringify(response.data.is_banned));
                    this.has_left = JSON.parse(JSON.stringify(response.data.has_left));
                    this.loading = false;
                    this.ioSetup(this.game.id);
                    this.fetchParticipants();
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                var _this = this;
                this.message('Wystąpił błąd', this.error, function () {
                    _this.$router.push({name: 'games.view.race', params: {hash_key: hash_key}});
                });
            });
        },
        fetchParticipants() {
            this.error = null;
            axios
                .post(
                    '/api/app/games/race/participants',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: this.game.hash_key,
                    }
                )
                .then(response => {
                    this.participants = JSON.parse(JSON.stringify(response.data.participants));
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                var _this = this;
                this.message('Wystąpił błąd podczas pobierania/aktualizacji listy graczy', this.error);
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
                this.io.emit('room_refresh', {
                    game_id: this.game.id
                });
            });
            this.io.on('u_room_left', () => {
                this.io.disconnect();
            });
            this.io.on('race_refresh', () => {
                this.fetchParticipants();
            });
            this.io.on('race_finished', () => {
                this.$router.push({name: 'games.view.race', params: {hash_key: this.hash_key}});
            });
            this.io.on('chat', (data) => {
            });
            this.io.on('connect', () => {
                this.io.emit('room_join', {
                    game_id: this.game.id,
                    user: yh.auth
                });
            });
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
    }
}
</script>
