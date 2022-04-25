<template>
    <div class="test-run">
        <h1>Zakończone</h1>
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
        <div class="card" v-if="show_repeat_global && show_repeat && today">
            <div class="card-header">
                <div class="float-right">
                    <span class="yh-fw-6 yh-v-o-60">{{test.name}}</span>
                    <i class="fa fa-check-circle ml-1 text-success yh-v-o-70" title="Zakończony"></i>
                </div>
            </div>
            <div class="card-body">
                <div class="question">
                    <p>
                        Gratulujemy zakończenia testu.
                    </p>
                    <p v-if="test.repeated_count">
                        Pamiętaj, że Twoje odpowiedzi trafiają do systemu powtórek.
                    </p>
                    <p v-if="test.repeated_count">
                        Jeśli udzieliłeś poprawnej odpowiedzi, informacja została skumulowana i pytanie przechodzi na wyższy poziom.
                    </p>
                    <p v-if="test.repeated_count">
                        Jeśli udzieliłeś niepoprawnej odpowiedzi, pytanie zostało zresetowane w systemie powtórek i zaczynasz jego powtarzanie od nowa.
                    </p>
                    <p v-if="test.repeated_count">
                        Konfiguracja powtórek dostępna jest w ustawianiach Twojego konta.
                    </p>
                </div>
            </div>
            <div class="card-footer">
                <div class="float-left yh-v-o-70">
                    <span class="btn btn-outline-secondary btn-sm" v-on:click="scrollSummary()">
                        <i class="fa fa-arrow-right"></i>
                        OK
                    </span>
                </div>
                <div class="float-right yh-v-o-70">
                    <span class="btn btn-outline-secondary btn-sm" v-on:click="disableRepeatInfo()">
                        <i class="fa fa-times"></i>
                        Nie pokazuj tego więcej
                    </span>
                </div>
            </div>
        </div>
        <div class="card" v-else-if="test">
            <div class="card-header" style="vertical-align:middle;">
                <span v-if="index != test.questions.length">
                    <a target="_blank" :href="'/kontakt?message=This is error report message for question:' + test.questions[index].id" class="btn btn-outline-secondary btn-mic yh-v-o-25" title="Zgłoś błąd w pytaniu">
                        <i class="fa fa-exclamation-triangle"></i>
                    </a>
                    <span v-if="index < test.questions.length">
                    Pytanie {{index + 1}}/{{test.questions.length}}
                    <span class="ql-icons ql-icons-sm ml-3">
                        <i v-if="!test.questions[index].correct_in_row" v-tooltip="'Możliwość wyłączenia pytania z systemu powtórek'" class="fa fa-ban yh-v-o-15 text-secondary" v-on:click="skipBlocked()"></i>
                        <i v-else-if="test.questions[index].skip" v-tooltip="'Możliwość wyłączenia pytania z systemu powtórek'" class="fa fa-ban yh-v-o-50 text-danger cursor-pointer" v-on:click="skipToggle(test.questions[index].id,0)"></i>
                        <i v-else="test.questions[index].skip" v-tooltip="'Możliwość wyłączenia pytania z systemu powtórek'" class="fa fa-ban yh-v-o-50 text-secondary cursor-pointer" v-on:click="skipToggle(test.questions[index].id,1)"></i>
                        <span v-if="!test.questions[index].last_answer_at" v-tooltip="'Twój poziom znajomości pytania w systemie powtórek'"  class="rounded-circle c-x c-n" title="ani razu nie odpowiedziałeś na to pytanie">?</span>
                        <span v-else  v-tooltip="'Twój poziom znajomości pytania w systemie powtórek'" >
                            <span v-if="test.questions[index].correct_in_row<6" v-bind:class="'rounded-circle c-x c-'+test.questions[index].correct_in_row">{{test.questions[index].correct_in_row}}</span>
                            <span v-else class="rounded-circle c-x c-p" >+</span>
                        </span>
                        <span v-if="test.questions[index].deleted" v-tooltip="'Pytanie nieaktualne'"  class="rounded-circle c-x c-n" title="Pytanie nieaktualne">!</span>

                    </span>
                    </span>
                </span>
                <div class="float-right">
                    <span class="yh-fw-6 yh-v-o-60">{{test.name}}</span>
                    <i class="fa fa-check-circle ml-1 text-success yh-v-o-70" title="Zakończony"></i>
                </div>
            </div>
            <div class="card-body" v-if="index == test.questions.length">
                <div class="question">
                    <div class="row mt-2 mr-1 ml-1">
                        <div class="pie-chart col-12 col-md-3 text-center p-1  yh-v-o-70">
                            <svg class="my-pie" viewBox="0 0 200 200">
                                <circle r="85" cx="100" cy="100"
                                    fill="transparent"
                                    stroke="#C76857"
                                    stroke-width="30"
                                />
                                <circle r="85" cx="100" cy="100" fill="transparent"
                                    stroke="#62A136"
                                    stroke-width="30"
                                    v-bind:stroke-dasharray="`calc(${test.questions_answered_correct_count}/${test.questions_count} * 2*22/7*85) calc(2*22/7*85)`"
                                 />
                                 <text x="100" y="105" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="3em" font-weight="500">{{Math.round(test.questions_answered_correct_count/test.questions_count*100)}}%</text>
                            </svg>
                        </div>
                        <div class="col-12 col-md-9 pt-2 pl-5">
                            <h4 class="float-md-right yh-v-o-60 font-italic">{{dateF(test.finished_at)}}</h4>
                            <h2 class="yh-fw-5 mb-4">{{test.name}}</h2>
                            <h4 class="mb-2">{{test.info}}</h4>
                            <p style="font-size:0.9rem; line-height:1.3rem;">
                                <span v-if="test.show_correct" class="d-block">
                                    Tryb nauki, widziałeś poprawne odpowiedzi.
                                </span>
                                <span v-if="test.time_limit">
                                Egzamin<span v-if="test.show_correct"> i nauka</span>: {{test.time_limit}} min.<br>
                                Zajął:
                                    <span v-if="!test.completion_seconds">cały dostępny czas</span>
                                    <span v-else>{{timeDiffCalc(test.completion_seconds)}}</span>
                                </span>
                            </p>
                            <p style="font-size:1.4rem;">
                                Wynik: {{test.questions_answered_correct_count}}/{{test.questions_count}}
                                <span v-if="test.questions_count - test.questions_answered_count>0"><br>Pominiętych: {{test.questions_count - test.questions_answered_count}}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="card-body">
                <div v-if="!processing">
                    <div class="question">
                        {{test.questions[index].question}}
                    </div>
                    <div>
                        <div>
                            <div v-for="i in 3" v-bind:id="'answer-'+i" v-bind:class="'answer row '+(test.questions[index].options[i-1].correct ? 'answer-correct' : 'answer-incorrect')">
                                <div class="answer-input col-1">
                                    <input v-if="typeof test.questions[index].answer == 'undefined'" type="radio" name="answer" v-bind:value="i" :disabled="true">
                                    <input v-else type="radio" name="answer" v-bind:value="i" :checked="test.questions[index].answer.question_option_id == test.questions[index].options[i-1].id" :disabled="test.questions[index].answer.question_option_id != test.questions[index].options[i-1].id">
                                </div>
                                <div class="answer-label col-11">
                                    <label>{{test.questions[index].options[i-1].option}}</label>
                                </div>
                            </div>
                            <div class="law-information mt-1">
                                <button id="show-law-info-button" class="btn btn-sm btn-outline-secondary" onclick="$('#show-law-info').toggle();">
                                    <i class="fa fa-info"></i>
                                    &nbsp;
                                    <i class="fa fa-university"></i>
                                </button>
                                <button v-if="typeof test.questions[index].answer == 'undefined'" class="btn btn-sm btn-warning cursor-default">pominięte</button>
                                <div id="show-law-info" class="info" style="display:none; ">{{test.questions[index].legal_basis_text}}
                                    <div v-html="test.questions[index].legal_basis_generated"></div>
                                </div>
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
                    <span v-if="index==test.questions.length">
                        <span class="btn btn-outline-secondary btn-sm" v-on:click="scrollFirst('all')">
                            Wszystkie <span class="d-none d-sm-inline-block">pytania</span>
                        </span>
                        <span v-if="test.questions_count>test.questions_answered_count" class="btn btn-outline-secondary btn-sm" v-on:click="scrollFirst('skipped')">
                            <i class="fa fa-forward"></i>
                            <span class="d-none d-md-inline-block">
                                Pominięte pyt.
                            </span>
                        </span>
                        <span v-if="test.questions_answered_correct_count>0" class="btn btn-outline-secondary btn-sm" v-on:click="scrollFirst('correct')">
                            <i class="fas fa-thumbs-up"></i>
                            <span class="d-none d-md-inline-block">
                                Poprawne odpowiedzi
                            </span>
                        </span>
                        <span v-if="test.questions_answered_count>test.questions_answered_correct_count" class="btn btn-outline-secondary btn-sm" v-on:click="scrollFirst('incorrect')">
                            <i class="fas fa-thumbs-down"></i>
                            <span class="d-none d-md-inline-block">
                                Błędne odp.
                            </span>
                        </span>
                    </span>
                    <span class="btn btn-outline-secondary btn-sm" v-if="index>0 && index<test.questions.length" v-on:click="scrollBack()">
                        <i class="fa fa-arrow-left"></i>
                        <span class="d-none d-md-inline-block">
                            Poprzednie
                        </span>
                    </span>
                    <span class="btn btn-outline-secondary btn-sm" v-if="index<test.questions.length-1" v-on:click="scrollNext()">
                        <i class="fa fa-arrow-right"></i>
                        <span class="d-none d-md-inline-block">
                            Następne
                        </span>
                    </span>
                </div>
                <div class="float-right yh-v-o-70" v-if="index!=test.questions.length">
                    <span class="btn btn-outline-secondary btn-sm" v-on:click="scrollSummary()">
                        Podsumowanie
                    </span>
                </div>
            </div>
        </div>
        <router-link :to="{ name: 'tests.ended' }" class="btn btn-primary text-white yh-v-o-70 mt-5">
            <i class="fa fa-arrow-circle-left mr-1"></i>
            Wróć
          </router-link>
    </div>
</template>
<script>
import axios from 'axios';
export default {
    data() {
        return {
            error: null,
            index: 0,
            loading: false,
            processing: false,
            test: null,
            today: false,
            show_repeat: true,
            show_repeat_global: true,
            filter: null,
        };
    },
    created() {
        this.getAllSettings();
        this.fetch(this.$route.params.id);
    },
    destroyed() {
        $(window).off('keyup');
        $(window).off('keydown');
        this.saveAllSettings();
    },
    mounted() {
        SideBarCollapseIfActive(true);
        $(window).on('keydown',function(e) {
            switch(e.which) {
                case 37: // [<-]
                case 39: // [->]
                    e.preventDefault();
                    break;
            }
        });
        var _this = this;
        $(window).on('keyup',function(e) {
            switch(e.which) {
                case 37: // [<-]
                    _this.scrollBack();
                    break;
                case 13: // [Enter]
                case 39: // [->]
                    _this.scrollNext();
                    break;
            }
        });
    },
    updated() {
    },
    methods: {
        getAllSettings() {
            if ($cookies.isKey('app-test-summary-dont-show-repeat')) {
                this.show_repeat_global = false;
            }
            if (!this.show_repeat_global) {
                return;
            }
            if ($cookies.isKey('app-test-summary-shown-'+this.$route.params.id)) {
                this.show_repeat = false;
            }
        },
        saveAllSettings() {
            if (!this.show_repeat_global) {
                $cookies.set('app-test-summary-dont-show-repeat',1);
                return;
            }
            if (this.test) {
                $cookies.set('app-test-summary-shown-'+this.test.id,1,'1d');
            }
        },
        disableRepeatInfo() {
            this.show_repeat_global = false;
            saveAllSettings();
        },
        dateF(s) {
          if (s.includes('T')) {
              var r = s.split('T');
          } else {
              var r = s.split(' ');
          }
          var s2 = r[0];
          s2 = this.replaceAll(s2,'-','/');
          return s2;
        },
        replaceAll(string, search, replace) {
          return string.split(search).join(replace);
        },
        timeDiffCalc(total_sec) {
            var h=Math.floor(total_sec/(60*60));
            var m=Math.floor((total_sec-h*60*60)/60);
            var s=total_sec-h*60*60-m*60;
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
        fetch(id,filter='noquestions') {
            this.filter = filter;
            this.error = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/tests/summary',
                    {
                        hash_id: yh.auth.hash_id,
                        id: id,
                        filter: filter,
                    }
                )
                .then(response => {
                    this.today = response.data.today;
                    this.test = JSON.parse(JSON.stringify(response.data.test));
                    this.index=0;
                    this.loading = false;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        scrollBack() {
            if (this.index==0) {
                this.scrollSummary();
                return;
            }
            this.index = this.index - 1;
            this.processing = true;
        },
        scrollFirst(filter='all') {
            if (this.filter == filter) {
                this.index = 0;
                this.processing = true;
                return;
            }
            this.fetch(this.test.id,filter);
        },
        scrollNext() {
            if (this.index==this.test.questions.length) {
                return;
            }
            this.index = this.index + 1;
            this.processing = true;
        },
        scrollSummary() {
            this.index = this.test.questions.length;
            this.processing = true;
            this.show_repeat = false;
        },
        keyboard() {
            setTimeout(function(){
              $('.vc-container').center();
            },50);
            this.$confirm(
                {
                  title: 'Obsługa klawiatury',
                  message: 'Użyj strzałek prawo, lewo oraz Enter do nawigacji po pytaniach testu.',
                  button: {
                    yes: 'OK',
                  }
                },
            );
        },
        skipBlocked() {
            setTimeout(function(){
              $('.vc-container').center();
            },50);
            this.$confirm(
                {
                  message: 'Nie możesz blokować powtórek w pytaniach, na które jeszcze nie odpowiadałeś lub na które odpowiedziałeś ostatnio niepoprawnie ani w pytaniach wyłączonych z systemu powtórek (np. z egzaminów z ubiegłych lat)',
                  button: {
                    yes: 'Rozumiem',
                  }
                },
            );
        },
        skipToggle(question_id,toggle) {
            axios
                .post(
                    '/api/app/questions/repeats/skip',
                    {
                        hash_id: yh.auth.hash_id,
                        question_id: question_id,
                        toggle: toggle,
                    }
                )
                .then(response => {
                    this.fixQuestionSkip(question_id,toggle);
                }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        fixQuestionSkip(question_id,toggle) {
            for(var i=0;i<this.test.questions.length;i++) {
                if (this.test.questions[i].id == question_id) {
                    this.test.questions[i].skip = toggle;
                    continue;
                }
            }
        },
    },
}
</script>
