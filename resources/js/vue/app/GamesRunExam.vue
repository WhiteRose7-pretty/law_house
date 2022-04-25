<template>
    <div class="game-run test-run">
        <div class="card mb-2" v-if="loading||error">
            <div class="card-body" v-touch:swipe="swipeHandler">
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
                    <span v-if="index<game.questions.length">
                        <a target="_blank" :href="'/kontakt?message=This is error report message for question:' + game.questions[index].id" class="btn btn-outline-secondary btn-mic yh-v-o-25 mr-1" title="Zgłoś błąd w pytaniu">
                            <i class="fa fa-exclamation-triangle"></i>
                        </a>
                        Pytanie {{game.questions_answered_count + index + 1}}/{{game.questions_count}},
                    </span>
                    Postęp {{Math.round((game.questions_answered_count + answered)/game.questions_count*100)}}%<span class="yh-v-o-75" v-if="game.time_limit">, <span id="time-remaining">{{game_ends}}</span></span>
                </span>
                <span v-if="game.questions[index].help_text">
                    <div class="btn btn-outline-secondary btn-mic yh-v-o-25 mr-1">
                       <i class="fas fa-graduation-cap" v-tooltip="tooltip(game.questions[index].help_text)"></i>
                    </div>
                </span>
                <div class="float-right">
                    <span class="yh-fw-6 yh-v-o-60">{{game.name}}</span>
                </div>
            </div>
            <div class="card-body" v-if="index == game.questions.length || out_of_time" v-touch:swipe="swipeHandler">
                <div class="question">
                    <span v-if="out_of_time">
                        Nie masz więcej czasu. game zostanie automatycznie zakończony.
                    </span>
                    <span v-else-if="answered==game.questions.length">
                        Odpowiedziałeś na wszystkie pytania.<br/><br/>
                        <span class="btn btn-outline-success" v-on:click="sendAnswers(true)">
                            ZAKOŃCZ GRĘ
                        </span>
                    </span>
                    <span v-else>
                        Czy chcesz zakończyć? Zostały jeszcze pytania.<br/><br/>
                        <span class="btn btn-outline-primary" v-on:click="sendAnswers(true)">
                            ZAKOŃCZ GRĘ
                        </span>
                    </span>
                </div>
            </div>
            <div v-else class="card-body" v-touch:swipe="swipeHandler">
                <div v-if="!processing">
                    <div class="question">
                        {{game.questions[index].question}}
                    </div>
                    <div v-if="game.show_correct">
                        <div v-if="typeof game.questions[index].answer == 'undefined'">
                            <div v-for="i in 3" v-bind:id="'answer-'+i" class="answer row" v-bind:data-index="index" v-bind:data-i="i-1">
                                <div class="answer-input col-1">
                                    <input v-bind:id="'answer-input-'+i" type="radio" name="answer" v-bind:value="i" v-on:click="selectAnswer(index,i-1)">
                                </div>
                                <div class="answer-label col-11">
                                    <label v-bind:for="'answer-input-'+i">{{game.questions[index].options[i-1].option}}</label>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div v-for="i in 3" v-bind:id="'answer-'+i" v-bind:class="'answer row '+(game.questions[index].options[i-1].correct ? 'answer-correct' : 'answer-incorrect')">
                                <div class="answer-input col-1">
                                    <input type="radio" name="answer" v-bind:value="i" :checked="game.questions[index].answer.question_option_id == game.questions[index].options[i-1].id" :disabled="game.questions[index].answer.question_option_id != game.questions[index].options[i-1].id">
                                </div>
                                <div class="answer-label col-11">
                                    <label>{{game.questions[index].options[i-1].option}}</label>
                                </div>
                            </div>
                            <div class="law-information mt-1">
                                <button id="show-law-info-button" class="btn btn-sm btn-outline-secondary" onclick="$('#show-law-info').toggle();">
                                    <i class="fa fa-info"></i>
                                    &nbsp;
                                    <i class="fa fa-university"></i>
                                </button>
                                <div id="show-law-info" class="info" style="display:none;">
                                    {{game.questions[index].legal_basis_text}}
                                    <div v-html="game.questions[index].legal_basis_generated"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div v-for="i in 3" v-bind:id="'answer-'+i" class="answer row" v-bind:data-index="index" v-bind:data-i="i-1">
                            <div class="answer-input col-1">
                                <input v-bind:id="'answer-input-'+i" type="radio" name="answer" v-bind:value="i" v-on:click="selectAnswer(index,i-1)" v-if="typeof game.questions[index].answer == 'undefined'" :checked="false">
                                <input v-bind:id="'answer-input-'+i" type="radio" name="answer" v-bind:value="i" v-on:click="selectAnswer(index,i-1)" v-else :checked="game.questions[index].answer.question_option_id == game.questions[index].options[i-1].id">
                            </div>
                            <div class="answer-label col-11">
                                <label v-bind:for="'answer-input-'+i">{{game.questions[index].options[i-1].option}}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    {{processing = false}}
                </div>
            </div>
            <div class="card-footer">
                <div class="float-left yh-v-o-70">
                    <span class="btn btn-outline-secondary btn-sm yh-v-o-30" v-on:click="keyboard()">
                      <i class="fa fa-keyboard"></i>
                    </span>
                    <span class="btn btn-outline-secondary btn-sm" v-if="skipped>0" v-on:click="scrollBackFirstSkipped()" title="Wróć szybko do pierwszego wcześniejszego pytania, na które nie udzieliłeś jeszcze odpowiedzi">
                        <i class="fa fa-arrow-down"></i>
                        <span class="d-none d-sm-inline">
                        Wróć
                        </span>
                    </span>
                    <span class="btn btn-outline-secondary btn-sm" v-if="index>0" v-on:click="scrollBack()">
                        <i class="fa fa-arrow-left"></i>
                        <span class="d-none d-sm-inline">
                        Poprzednie
                        </span>
                    </span>
                    <span class="btn btn-outline-secondary btn-sm" v-if="index<game.questions.length" v-on:click="scrollNext()">
                        <span v-if="typeof game.questions[index].answer == 'undefined'" title="Będziesz mógł łatwo i szybko wrócić do każdego pominiętego pytania">
                            <i class="fa fa-arrow-right"></i>
                            <span class="d-none d-sm-inline">
                            Pomiń
                            </span>
                        </span>
                        <span v-else>
                            <i class="fa fa-arrow-right"></i>
                            <span class="d-none d-sm-inline">
                            Następne
                            </span>
                        </span>
                    </span>
                    <span class="btn btn-outline-secondary btn-sm" v-if="index<(game.questions.length-1) && typeof game.questions[index+1].answer != 'undefined' && (skipped+answered<game.questions.length)" title="Wróć szybko do pierwszego pytania z kolei, na które nie udzieliłeś jeszcze odpowiedzi" v-on:click="scrollNextUnanswered()">
                        <i class="fa fa-arrow-up"></i>
                        <span class="d-none d-sm-inline">
                        Wróć
                        </span>
                    </span>
                </div>
                <div class="float-right yh-v-o-70">
                    <span v-if="new_answers || sending" class="btn btn-outline-warning btn-sm yh-v-o-70">
                        <i class="fa fa-save"></i>
                        <span class="d-none d-sm-inline">
                        autozapis
                        </span>
                    </span>
                    <span class="btn btn-outline-secondary btn-sm" v-on:click="sendAnswers(true)">
                        Zakończ
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Vue from 'vue';
import Vue2TouchEvents from 'vue2-touch-events'

Vue.use(Vue2TouchEvents);

export default {
    data() {
        return {
            loading: false,
            error: null,
            game: null,
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
            new_message_count: 0,
        };
    },
    created() {
        this.fetch(this.$route.params.hash_key);
        this.yh = yh;
    },
    destroyed() {
        $(window).off('keyup');
        $(window).off('keydown');
        $('#chat-msg-new').off('click','button',this.chatFunction);
        $('#sidebar-right').addClass('disabled');
        $('#chat-msg-list').html('');
        if (typeof this.io != 'undefined' && this.io != null) {
            this.io.emit( 'room_leave', {
                game_id : this.game.id,
                user : yh.auth
            });
        }
        this.timeout_set = false;
        if (this.timeout!=null) {
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

        $(window).on('keydown',function(e) {
            switch(e.which) {
                case 37: // [<-]
                case 39: // [->]
                case 38: // [arr up]
                case 40: // [arr down]
                    e.preventDefault();
                    break;
            }
        });

        var _this = this;
        $(window).on('keyup',function(e) {
            if ($("#chat textarea").is(":focus")) {
                return;
            }
            switch(e.which) {
                case 49: // [1]
                    _this.selectAnswer(_this.index,0);
                    break;
                case 50: // [2]
                    _this.selectAnswer(_this.index,1);
                    break;
                case 51: // [3]
                    _this.selectAnswer(_this.index,2);
                    break;
                case 37: // [<-]
                    _this.scrollBack();
                    break;
                case 13: // [Enter]
                case 39: // [->]
                    _this.scrollNext();
                    break;
                case 38: // [arr up]
                    _this.scrollNextUnanswered();
                    break;
                case 40: // [arr down]
                    _this.scrollBackFirstSkipped();
                    break;
            }
        });
    },
    updated() {
    },
    beforeRouteLeave(to, from, next) {
        if (!this.new_answers && !this.sending) {
            next();
            return;
        }
        setTimeout(function(){
            $('.vc-container').center();
        },50);
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
    computed: {

    },
    methods: {
        tooltip(text){
            return text
        },
        swipeHandler(direction){
            if(direction == 'left'){
                if (this.index<this.game.questions.length){
                    this.scrollNext();
                }
            }
            else if(direction == 'right'){
                if (this.index > 0 ) {
                    this.scrollBack();
                }
            }
            else if(direction == 'down'){
                if(this.skipped > 0){
                    this.scrollBackFirstSkipped();
                }
            }
        },
        chatFix() {
            $('#sidebar-right').removeClass('disabled');
            $('#sidebar-right').toggleClass('active');
            $('#sidebarRightCollapse').toggleClass('active');
        },
        check_new_message(data){
            if (data.user.user_id == yh.auth.user_id || data.read.includes(yh.auth.user_id)){
                return 0;
            }
            return 1;
        },
        chatProcessMsg(data) {
            this.new_message_count = this.new_message_count +  this.check_new_message(data);
            if(!this.new_message_count){
                $('.new-message-count').css('display', 'none');
            }
            else{
                $('.new-message-count').css('display', 'initial');
            }
            $('.new-message-count').text(this.new_message_count);
            if (data.user.user_id == yh.auth.user_id) {
                var html = '<div class="chat-msg chat-msg-yours">';
            } else {
                var html = '<div class="chat-msg">';
                html += '<div class="chat-msg-user">';
                html += data.user.user_name + '('+data.user.user_id+')';
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
              this.io.emit('msg',{
                  game_id : this.game.id,
                  user : yh.auth,
                  msg: $('#chat-msg-new textarea').val()
              });
              $('#chat-msg-new textarea').val('');
            }
        },
        readMessage(){
            this.new_message_count = 0;
            $('.new-message-count').css('display', 'none');
            this.io.emit('read',{
                game_id : this.game.id,
                user : yh.auth,
            });
        },
        chatSetup(data) {
            $('#chat-msg-list').html('');
            for(var i=0;i<data.length;i++) {
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
                $('#chat-msg-new').on('click','button',this.chatFunction);
                $('#sidebar-right').on('click','button',this.readMessage);
            } else {
                $('#chat-msg-new textarea').attr('placeholder', 'musisz dołączyć do gry by korzystać z czatu');
                $('#chat-msg-new textarea').attr('disabled', 'disabled');
                $('#chat-msg-new button').hide();
            }

        },
        checkGame() {
            var _this = this;
            if (this.game.type != 'exam') {
                this.message('Zły typ gry', 'Ten moduł obsługuje jedynie grę typu egzamin',function(){
                    _this.$router.push({ name: 'games.view.exam', params: { hash_key: _this.game.hash_key } });
                });
                return false;
            }
            if (!this.game.started_at) {
                this.message('Gra się jeszcze nie zaczęła', 'Ta gra się jeszcze nie zaczęła',function(){
                    _this.$router.push({ name: 'games.view.exam', params: { hash_key: _this.game.hash_key } });
                });
                return false;
            }
            if (this.game.finished_at) {
                this.message('Gra się już zakończyła', 'Ta gra się już zakończyła',function(){
                    _this.$router.push({ name: 'games.view.exam', params: { hash_key: _this.game.hash_key } });
                });
                return false;
            }
            return true;
        },
        checkTimesTimeout() {
            if (this.timeout_set) {
                var _this = this;
                this.timeout = setTimeout(function(){
                    _this.fixTimes();
                },1000)
            }
        },
        copyShare() {
            var copyText = document.getElementById("share-link");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            /* Copy the text inside the text field */
            document.execCommand("copy");

            this.message('Potwierdzenie','Skopiowano do schowka');
        },
        dateF(s) {
            var r = s.split('T');
            var s2 = r[0];
            s2 = this.replaceAll(s2,'-','/');
            return s2;
        },

        scrollBack() {
            if (this.index==0) {
                return;
            }
            if(typeof this.game.questions[this.index-1].answer == 'undefined') {
                this.game.questions[this.index-1].skipped = false;
                this.skipped = this.skipped - 1;
            }
            this.index = this.index - 1;
            this.processing = true;
        },
        scrollBackFirstSkipped() {
            if (this.skipped==0) {
                return;
            }
            for(var j=0;j<this.index;j++) {
                if (typeof this.game.questions[this.index-j-1].skipped != 'undefined' && this.game.questions[this.index-j-1].skipped) {
                    this.game.questions[this.index-j-1].skipped = false;
                    this.index = this.index-j-1;
                    this.processing = true;
                    this.skipped = this.skipped -1;
                    return;
                }
            }
        },
        scrollNext() {
            if (this.index==this.game.questions.length) {
                return;
            }
            if(typeof this.game.questions[this.index].answer == 'undefined') {
                this.game.questions[this.index].skipped = true;
                this.skipped = this.skipped + 1;
            }
            this.index = this.index + 1;
            this.processing = true;
        },
        scrollNextUnanswered() {
            if (this.skipped+this.answered==this.game.questions.length) {
                return;
            }
            if(typeof this.game.questions[this.index].answer == 'undefined') {
                this.game.questions[this.index].skipped = true;
                this.skipped = this.skipped + 1;
            }
            var x=this.game.questions.length-this.index-1;
            for(var j=1;j<x;j++) {
                if (typeof this.game.questions[this.index+j].answer != 'undefined') {
                    continue;
                }
                this.index = this.index+j;
                this.processing = true;
                return;
            }
        },
        selectAnswer(index,i) {
            $('.answer-input input').blur(); // fix for keyboard and touchpad conflict (in case user uses both keyboard and touchpad)
            var answered_increment = typeof this.game.questions[index].answer == 'undefined' ? 1 : 0;
            this.game.questions[index].answer = {
                question_id: this.game.questions[index].id,
                question_option_id: this.game.questions[index].options[i].id,
                correct: this.game.questions[index].options[i].correct,
            };
            this.new_answers=true;
            this.answered = this.answered+answered_increment;
            var _index = index;
            var _this = this;
            setTimeout(function() {
              if (_this.index == _index) {
                  _this.index = index+1;
                  _this.processing = true;
              }
            },1000);
            setTimeout(function(){
                _this.sendAnswers();
            },1000); //5000
        },
        sendAnswers(finish=false) {
            if (this.sending) {
                if (this.new_answers || finish) {
                    var _this = this;
                    setTimeout(function(){
                        _this.sendAnswers(finish);
                    },500);
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
                    '/api/app/games/exam/answers',
                    {
                        hash_id: yh.auth.hash_id,
                        game: this.game,
                        finish: finish,
                        out_of_time: this.out_of_time,
                    }
                )
                .then(response => {
                    this.sending = false;
                    if (typeof response.data.redirect != 'undefined') {
                        if (typeof this.io != 'undefined') {
                            this.io.emit( 'room_refresh', {
                                game_id : this.game.id
                            });
                        }
                        this.$router.push({ name: 'games.view.exam', params: { hash_key: this.game.hash_key } });
                    }
                }).catch(error => {
                    this.sending = false;
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.redirect) {
                        if (typeof this.io != 'undefined') {
                            this.io.emit( 'room_refresh', {
                                game_id : this.game.id
                            });
                        }
                        this.$router.push({ name: 'games.view.exam', params: { hash_key: this.game.hash_key } });
                    }
                });
        },
        keyboard() {
            setTimeout(function(){
              $('.vc-container').center();
            },50);
            this.$confirm(
                {
                  title: 'Obsługa klawiatury',
                  message: 'Użyj klawiszy 1-3 by wybrać odpowiedź, użyj strzałek prawo, lewo, góra, dół oraz Enter do nawigacji po pytaniach testu.',
                  button: {
                    yes: 'OK',
                  }
                },
            );
        },
        fixTimes() {
            if (this.game.finished_at) {
                this.timeout_set=false;
                return;
            }
            this.timeout_set=true;
            this.game_starts = this.game.started_at;
            this.game_can_start = false;
            this.game_ends = this.timeDiff(this.game.time_limit,this.game.started_at,'W każdej chwili');
            if (this.game_ends == 'W każdej chwili') {
                this.timeout_set=false;
                this.finish(this.game.hash_key);
            }
            this.checkTimesTimeout();
        },
        fetch(hash_key) {
            this.error = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/games/exam/continue',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: hash_key,
                    }
                )
                .then(response => {
                    this.game = JSON.parse(JSON.stringify(response.data.game));
                    if (!this.checkGame()) {
                        return;
                    }
                    if (typeof this.io != 'undefined' && this.io != null && response.data.room_refresh) {
                        this.io.emit( 'room_refresh', {
                            game_id : this.game.id
                        });
                    }
                    this.owner = JSON.parse(JSON.stringify(response.data.owner));
                    this.in_game = JSON.parse(JSON.stringify(response.data.in_game));
                    this.is_host = JSON.parse(JSON.stringify(response.data.is_host));
                    this.is_ready = JSON.parse(JSON.stringify(response.data.is_ready));
                    this.is_banned = JSON.parse(JSON.stringify(response.data.is_banned));
                    this.has_left = JSON.parse(JSON.stringify(response.data.has_left));
                    this.loading = false;
                    this.fixTimes();
                    this.ioSetup(this.game.id);
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    var _this = this;
                    this.message('Wystąpił błąd',this.error,function(){
                        _this.$router.push({ name: 'games.view.exam', params: { hash_key: hash_key } });
                    });
                });
        },
        finish(hash_key) {
            if (this.new_answers) {
                this.sendAnswers(true);
                return;
            }
            this.error = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/games/finish',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: hash_key,
                    }
                )
                .then(response => {
                    if (typeof this.io != 'undefined' && response.data.room_refresh) {
                        this.io.emit( 'room_refresh', {
                            game_id : this.game.id
                        });
                    }
                    this.$router.push({ name: 'games.view.exam', params: { hash_key: hash_key } });
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    this.message('Wystąpił błąd',this.error,function(){
                        _this.$router.push({ name: 'games.view.exam', params: { hash_key: hash_key } });
                    });
                });
        },
        ioSetup() {
            if (typeof io == 'undefined') {
                this.message('Błąd połączenia z serwerem', 'Serwer gier jest niedostępny.');
            }
            var hostname = window.location.hostname;
            this.io = io('https://'+hostname+':3000/');
            this.io.on('u_room_joined', (data) => {
                this.chatSetup(data.chat);
                this.io.emit( 'room_refresh', {
                    game_id : this.game.id
                });
            });
            this.io.on('u_room_left', () => {
                this.io.disconnect();
            });
            this.io.on('chat', (data) => {
            });
            this.io.on('connect', () => {
                this.io.emit('room_join',{
                    game_id : this.game.id,
                    user : yh.auth
                });
            });
        },
        message(title, msg, callback = null, no=false) {
            setTimeout(function(){
                $('.vc-container').center();
            },50);
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
        processUserBan(id,flag) {
            var err=true;
            for(var i=0;i<this.game.participants.length;i++) {
                if (this.game.participants[i].user_id == id) {
                    this.game.participants[i].banned_at = flag;
                    err=false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.game.hash_key,true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserOnline(id) {
            var err=true;
            for(var i=0;i<this.game.participants.length;i++) {
                if (this.game.participants[i].user_id == id) {
                    this.game.participants[i].online = true;
                    err=false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.game.hash_key,true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserOffline(id) {
            var err=true;
            for(var i=0;i<this.game.participants.length;i++) {
                if (this.game.participants[i].user_id == id) {
                    this.game.participants[i].online = false;
                    err=false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.game.hash_key,true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserReady(id) {
            var err=true;
            for(var i=0;i<this.game.participants.length;i++) {
                if (this.game.participants[i].user_id == id) {
                    this.game.participants[i].ready = true;
                    err=false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.game.hash_key,true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        processUserUnReady(id) {
            var err=true;
            for(var i=0;i<this.game.participants.length;i++) {
                if (this.game.participants[i].user_id == id) {
                    this.game.participants[i].ready = false;
                    err=false;
                    break;
                }
            }
            if (err) {
                this.fetch(this.game.hash_key,true);
                return;
            }
            this.refresh.users = true;
            this.refresh.users = false;
        },
        replaceAll(string, search, replace) {
            return string.split(search).join(replace);
        },
        timeDiff(limit,date,ifpasslimit='czas upłynął') {
            var d=new Date(date);
            var dn=new Date();
            var diff=Math.round((dn.getTime() - d.getTime())/1000);
            if (diff>limit*60) {
                return ifpasslimit;
            }
            diff=limit*60-diff;
            var h=Math.floor(diff/(60*60));
            if (h>23) {

                return d.getFullYear() + '-' + (d.getMonth() < 10 ? '0' : '') + d.getMonth() + '-' +(d.getDate() < 10 ? '0' : '') + d.getDate() + ' ' + d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes();
            }
            var m=Math.floor((diff-h*60*60)/60);
            var s=diff-h*60*60-m*60;
            var res='';
            if (h>0) {
              res=h+':';
            }
            if (h>0||m>0) {
              res=res+(m<10?'0'+m:m)+':';
              res=res+(s<10?'0'+s:s);
            } else {
              res=''+s+' sek.';
            }
            return res;
        },
        toggleShare() {
            $('#share-link-container').toggle();
        },
    }
}
</script>
