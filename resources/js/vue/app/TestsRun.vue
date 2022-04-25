<template>
    <div class="test-run">
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

        <div class="card mb-2" v-if="test && test.finished_at">
            <div class="text-info p-5">
                Przekierowanie...
            </div>
        </div>

        <div class="card" v-if="test && !test.finished_at">
            <div class="card-header">
                <span>
                    <span v-if="index<test.questions.length">
                        Pytanie {{ test.questions_answered_count + index + 1 }}/{{ test.questions_count }},
                    </span>
                    Postęp {{
                        Math.round((test.questions_answered_count + answered) / test.questions_count * 100)
                    }}%<span
                    class="yh-v-o-75" v-if="test.time_limit">, <span
                    id="time-remaining">{{ test.time_remaining }}</span></span>
                    <span v-if="index<test.questions.length">
                        <a target="_blank"
                           :href="'/kontakt?message=This is error report message for question:' + test.questions[index].id"
                           class="btn btn-outline-secondary btn-mic yh-v-o-25 mr-1"
                           title="Zgłoś błąd w pytaniu">
                            <i class="fa fa-exclamation-triangle"></i>
                        </a>
                    </span>

                    <span v-if="test.questions[index].help_text">
                        <div class="btn btn-outline-secondary btn-mic yh-v-o-25 mr-1" >
                           <i class="fas fa-graduation-cap" v-tooltip="tooltip(test.questions[index].help_text)"></i>
                        </div>
                    </span>
                </span>
                <div class="float-right">
                    <span class="yh-fw-6 yh-v-o-60">{{ test.name }}</span>
                </div>
            </div>
            <div class="card-body" v-touch:swipe="swipeHandler" v-if="index == test.questions.length || out_of_time">
                <div class="question">
                    <span v-if="out_of_time">
                        Nie masz więcej czasu. Test zostanie automatycznie zakończony.
                    </span>
                    <span v-else-if="answered==test.questions.length">
                        Odpowiedziałeś na wszystkie pytania.<br/><br/>
                        <span class="btn btn-outline-success" v-on:click="finish()">
                            Zakończ test
                        </span>
                    </span>
                    <span v-else>
                        Czy chcesz zakończyć test? Zostały jeszcze pytania.<br/><br/>
                        <span class="btn btn-outline-primary" v-on:click="finish()">
                            Zakończ test
                        </span>
                    </span>
                </div>
            </div>
            <div v-else class="card-body" v-touch:swipe="swipeHandler">
                <div v-if="!processing">
                    <div class="question">
                        {{ test.questions[index].question }}
                    </div>
                    <div v-if="test.show_correct">
                        <div v-if="typeof test.questions[index].answer == 'undefined'">
                            <div v-for="i in 3" v-bind:id="'answer-'+i" class="answer row" v-bind:data-index="index"
                                 v-bind:data-i="i-1">
                                <div class="answer-input col-1">
                                    <input v-bind:id="'answer-input-'+i" type="radio" name="answer" v-bind:value="i"
                                           v-on:click="selectAnswer(index,i-1)">
                                </div>
                                <div class="answer-label col-11">
                                    <label
                                        v-bind:for="'answer-input-'+i">{{
                                            test.questions[index].options[i - 1].option
                                        }}</label>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div v-for="i in 3" v-bind:id="'answer-'+i"
                                 v-bind:class="'answer row '+(test.questions[index].options[i-1].correct ? 'answer-correct' : 'answer-incorrect')">
                                <div class="answer-input col-1">
                                    <input type="radio" name="answer" v-bind:value="i"
                                           :checked="test.questions[index].answer.question_option_id == test.questions[index].options[i-1].id"
                                           :disabled="test.questions[index].answer.question_option_id != test.questions[index].options[i-1].id">
                                </div>
                                <div class="answer-label col-11">
                                    <label>{{ test.questions[index].options[i - 1].option }}</label>
                                </div>
                            </div>
                            <div class="law-information mt-1">
                                <button id="show-law-info-button" class="btn btn-sm btn-outline-secondary"
                                        onclick="$('#show-law-info').toggle();">
                                    <i class="fa fa-info"></i>
                                    &nbsp;
                                    <i class="fa fa-university"></i>
                                </button>
                                <div id="show-law-info" class="info" style="display:none;">
                                    {{ test.questions[index].legal_basis_text }}
                                    <div v-html="test.questions[index].legal_basis_generated"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div v-for="i in 3" v-bind:id="'answer-'+i" class="answer row" v-bind:data-index="index"
                             v-bind:data-i="i-1">
                            <div class="answer-input col-1">
                                <input v-bind:id="'answer-input-'+i" type="radio" name="answer" v-bind:value="i"
                                       v-on:click="selectAnswer(index,i-1)"
                                       v-if="typeof test.questions[index].answer == 'undefined'" :checked="false">
                                <input v-bind:id="'answer-input-'+i" type="radio" name="answer" v-bind:value="i"
                                       v-on:click="selectAnswer(index,i-1)" v-else
                                       :checked="test.questions[index].answer.question_option_id == test.questions[index].options[i-1].id">
                            </div>
                            <div class="answer-label col-11">
                                <label
                                    v-bind:for="'answer-input-'+i">{{
                                        test.questions[index].options[i - 1].option
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
                    <span class="btn btn-outline-secondary btn-sm" v-if="skipped>0"
                          v-on:click="scrollBackFirstSkipped()"
                          title="Wróć szybko do pierwszego wcześniejszego pytania, na które nie udzieliłeś jeszcze odpowiedzi">
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
                    <span class="btn btn-outline-secondary btn-sm" v-if="index<test.questions.length"
                          v-on:click="scrollNext()">
                        <span v-if="typeof test.questions[index].answer == 'undefined'"
                              title="Będziesz mógł łatwo i szybko wrócić do każdego pominiętego pytania">
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
                    <span class="btn btn-outline-secondary btn-sm"
                          v-if="index<(test.questions.length-1) && typeof test.questions[index+1].answer != 'undefined' && (skipped+answered<test.questions.length)"
                          title="Wróć szybko do pierwszego pytania z kolei, na które nie udzieliłeś jeszcze odpowiedzi"
                          v-on:click="scrollNextUnanswered()">
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
                    <span class="btn btn-outline-secondary btn-sm" v-on:click="finish()">
                        Zakończ
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
import Vue from "vue";
import Vue2TouchEvents from "vue2-touch-events";

Vue.use(Vue2TouchEvents);

export default {
    data() {
        return {
            answered: 0,
            error: null,
            index: 0,
            loading: false,
            new_answers: false,
            processing: false,
            sending: false,
            skipped: 0,
            test: null,
            timeout: null,
            timeout_set: false,
            out_of_time: false,
            sidebar_fix: false,
        };
    },
    created() {
        this.fetch(this.$route.params.id);
    },
    destroyed() {
        $(window).off('keyup');
        $(window).off('keydown');
        this.timeout_set = false;
        if (this.timeout != null) {
            clearTimeout(this.timeout);
        }
        if (this.sidebar_fix) {
            SideBarShowIfDisabled();
        }
    },
    mounted() {
        this.sidebar_fix = SideBarCollapseIfActive();
        $(window).on('keydown', function (e) {
            switch (e.which) {
                case 37: // [<-]
                case 39: // [->]
                case 38: // [arr up]
                case 40: // [arr down]
                    e.preventDefault();
                    break;
            }
        });
        var _this = this;
        $(window).on('keyup', function (e) {
            switch (e.which) {
                case 49: // [1]
                    _this.selectAnswer(_this.index, 0);
                    break;
                case 50: // [2]
                    _this.selectAnswer(_this.index, 1);
                    break;
                case 51: // [3]
                    _this.selectAnswer(_this.index, 2);
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
    methods: {
        tooltip(text){
            return text
        },
        swipeHandler(direction) {
            if (direction == 'left') {
                if (this.index < this.test.questions.length) {
                    this.scrollNext();
                }
            } else if (direction == 'right') {
                if (this.index > 0) {
                    this.scrollBack();
                }
            } else if (direction == 'down') {
                if (this.skipped > 0) {
                    this.scrollBackFirstSkipped();
                }
            }
        },
        dateF(s) {
            if (s.includes('T')) {
                var r = s.split('T');
            } else {
                var r = s.split(' ');
            }
            var s2 = r[0];
            s2 = this.replaceAll(s2, '-', '/');
            return s2;
        },
        replaceAll(string, search, replace) {
            return string.split(search).join(replace);
        },
        checkTimeTimeout() {
            if (this.timeout_set) {
                var _this = this;
                this.timeout = setTimeout(function () {
                    _this.fixTime(true);
                }, 1000)
            }
        },
        timeDiff(limit, date) {
            var d = new Date(date);
            var dn = new Date();
            var diff = Math.round((dn.getTime() - d.getTime()) / 1000);
            if (diff > limit * 60) {
                if (!this.out_of_time) {
                    this.timeout_set = false;
                    this.out_of_time = true;
                    this.sendAnswers(true);
                }
                return 'czas upłynął';
            }
            diff = limit * 60 - diff;
            return this.timeDiffCalc(diff);
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
        fixTime(push = false) {
            if (this.test.finished_at) {
                return;
            }
            this.timeout_set = false;
            if (this.test.time_limit) {
                this.test.time_remaining = this.timeDiff(this.test.time_limit, this.test.created_at);
                this.timeout_set = true;
                if (push) {
                    $('#time-remaining').text(this.test.time_remaining);
                }
            }
            this.checkTimeTimeout();
        },
        fetch(id) {
            this.error = this.test = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/tests/run',
                    {
                        hash_id: yh.auth.hash_id,
                        id: id,
                    }
                )
                .then(response => {
                    this.test = JSON.parse(JSON.stringify(response.data));
                    if (this.test.finished_at) {
                        this.$router.push({name: 'tests.ended.summary', params: {id: this.test.id}});
                        return;
                    }
                    this.fixTime();
                    this.loading = false;
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
        scrollBack() {
            if (this.index == 0) {
                return;
            }
            if (typeof this.test.questions[this.index - 1].answer == 'undefined') {
                this.test.questions[this.index - 1].skipped = false;
                this.skipped = this.skipped - 1;
            }
            this.index = this.index - 1;
            this.processing = true;
        },
        scrollBackFirstSkipped() {
            if (this.skipped == 0) {
                return;
            }
            for (var j = 0; j < this.index; j++) {
                if (typeof this.test.questions[this.index - j - 1].skipped != 'undefined' && this.test.questions[this.index - j - 1].skipped) {
                    this.test.questions[this.index - j - 1].skipped = false;
                    this.index = this.index - j - 1;
                    this.processing = true;
                    this.skipped = this.skipped - 1;
                    return;
                }
            }
        },
        scrollNext() {
            if (this.index == this.test.questions.length) {
                return;
            }
            if (typeof this.test.questions[this.index].answer == 'undefined') {
                this.test.questions[this.index].skipped = true;
                this.skipped = this.skipped + 1;
            }
            this.index = this.index + 1;
            this.processing = true;
        },
        scrollNextUnanswered() {
            if (this.skipped + this.answered == this.test.questions.length) {
                return;
            }
            if (typeof this.test.questions[this.index].answer == 'undefined') {
                this.test.questions[this.index].skipped = true;
                this.skipped = this.skipped + 1;
            }
            var x = this.test.questions.length - this.index - 1;
            for (var j = 1; j < x; j++) {
                if (typeof this.test.questions[this.index + j].answer != 'undefined') {
                    continue;
                }
                this.index = this.index + j;
                this.processing = true;
                return;
            }
        },
        selectAnswer(index, i) {
            $('.answer-input input').blur(); // fix for keyboard and touchpad conflict (in case user uses both keyboard and touchpad)
            if (this.test.show_correct) {
                return this.selectAnswerShowCorrect(index, i);
            }
            this.selectAnswerAndProgress(index, i);
        },
        selectAnswerShowCorrect(index, i) {

            if (index == this.test.questions.length || typeof this.test.questions[index].answer != 'undefined') {
                return;
            }
            this.test.questions[index].answer = {
                question_id: this.test.questions[index].id,
                question_option_id: this.test.questions[index].options[i].id,
                correct: this.test.questions[index].options[i].correct,
            };
            this.new_answers = true;
            this.answered = this.answered + 1;
            this.processing = true;
            var _this = this;
            setTimeout(function () {
                _this.sendAnswers();
            }, 5000);
        },
        selectAnswerAndProgress(index, i) {
            var answered_increment = typeof this.test.questions[index].answer == 'undefined' ? 1 : 0;
            this.test.questions[index].answer = {
                question_id: this.test.questions[index].id,
                question_option_id: this.test.questions[index].options[i].id,
                correct: this.test.questions[index].options[i].correct,
            };
            this.new_answers = true;
            this.answered = this.answered + answered_increment;
            var _index = index;
            var _this = this;
            setTimeout(function () {
                if (_this.index == _index) {
                    _this.index = index + 1;
                    _this.processing = true;
                }
            }, 1000);
            setTimeout(function () {
                _this.sendAnswers();
            }, 5000);
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
                    '/api/app/tests/answers',
                    {
                        hash_id: yh.auth.hash_id,
                        test: this.test,
                        finish: finish,
                        out_of_time: this.out_of_time,
                    }
                )
                .then(response => {
                    this.sending = false;
                    if (typeof response.data.finished != 'undefined') {
                        this.$router.push({name: 'tests.ended.summary', params: {id: response.data.test.id}});
                    }
                }).catch(error => {
                this.loading = false;
                this.sending = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
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
                    message: 'Użyj klawiszy 1-3 by wybrać odpowiedź, użyj strzałek prawo, lewo, góra, dół oraz Enter do nawigacji po pytaniach testu.',
                    button: {
                        yes: 'OK',
                    }
                },
            );
        },
        finish() {
            setTimeout(function () {
                $('.vc-container').center();
            }, 50);
            var msg = 'Czy na pewno chcesz zakończyć ten test, pomijając resztę pytań?';
            if (this.test.show_correct) {
                if (this.answered == this.test.questions.length) {
                    this.sendAnswers(true);
                    return;
                }
            }
            if (this.answered == this.test.questions.length) {
                var msg = 'Czy na pewno chcesz już zakończyć test?';
            }
            var _this = this;
            this.$confirm(
                {
                    message: msg,
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
                            _this.sendAnswers(true);
                        }
                    }
                }
            );
        },
    },
}
</script>
