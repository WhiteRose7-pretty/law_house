<template>
    <div>
        <h1>Lista pytań</h1>

        <div class="card mt-5 questions-list">
            <div class="card-body">
                <div class="ql-search mb-1 clearfix yh-v-o-70">
                    <input id="fetch-query" placeholder="Szukaj" class="pl-1 mb-1 float-left" v-model="query" autofocus/>
                    <button class="ml-1 btn btn-outline-success btn-sm float-right float-md-left" v-on:click="fetchQuestions()">Szukaj</button>
                    <div class="float-md-right mt-1 mt-md-0 yh-v-o-70">
                          <select id="fetch-order" v-model="current_order" v-on:change="fetchQuestions()" class="w-100 w-md-auto">
                              <option value="id">#</option>
                              <option value="last-answered">ost. odpow.</option>
                              <option value="last-correct">ost. dobr. odpow.</option>
                              <option value="last-incorrect">ost. niepoprawna odpow.</option>
                              <option value="last-repeat">ost. powt.</option>
                              <option value="next-repeat">wkrótce powt.</option>
                              <option value="newest">ost. dodane</option>
                              <option value="updated">ost. aktual.</option>
                          </select>
                          <select id="fetch-repeat" v-model="current_edu" v-on:change="fetchQuestions()" class="w-100 w-md-auto">
                              <option value="">wszystkie</option>
                              <option value="new">nierozwiązywane</option>
                              <option value="0">poziom 0</option>
                              <option value="1">poziom 1</option>
                              <option value="2">poziom 2</option>
                              <option value="3">poziom 3</option>
                              <option value="4">poziom 4</option>
                              <option value="5">poziom 5</option>
                              <option value="plus">poziom +</option>
                              <option value="skip">pomijane</option>
                          </select>
                    </div>
                    <select v-if="sets" id="fetch-set" v-model="current_set" v-on:change="fetchQuestions()" class="mt-1 w-100">
                        <option value="0">Zestaw</option>
                        <option v-for="s in sets" v-bind:value="s.id">{{s.name}}</option>
                    </select>
                </div>

                <loading :active.sync="loading"
                         :loader="'dots'"
                         :can-cancel="false"
                         :is-full-page="true"
                         :opacity = "0.8"
                         :color="'#1596b2'"   ></loading>

                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>

                <div class="ql-table">
                    <div class="row ql-head">
                        <div class="col col-1">
                            #
                        </div>
                        <div class="col col-11 col-md-5">
                            Pytanie
                        </div>
                        <div class="col d-none d-md-block col-md-6">
                            Odpowiedzi
                        </div>
                    </div>
                    <div v-if="pages>0">
                        <div class="row ql-row" v-for="q in questions">
                            <div class="col">
                                {{q.id}}
                            </div>
                            <div class="col col-12 col-md-5 yh-fs-16">
                                {{q.question}}
                            </div>
                            <div class="col col-12 col-md-5">
                                <div v-for="o in q.options" v-bind:class="'answer row '+(o.correct ? 'answer-correct' : 'answer-incorrect')">
                                    <div class="answer-input col-1">
                                        <input type="radio" name="answer" :disabled="true">
                                    </div>
                                    <div class="answer-label col-11">
                                        <label>{{o.option}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col col-12 col-md-1 ql-icons">
                                <i v-if="!q.correct_in_row" class="fa fa-ban yh-v-o-15 text-secondary" v-tooltip="'Możliwość wyłączenia pytania z systemu powtórek'" v-on:click="skipBlocked()"></i>
                                <i v-else-if="q.skip" class="fa fa-ban yh-v-o-50 text-danger cursor-pointer"  v-tooltip="'Możliwość wyłącznia pytania z systemu powtórek'" v-on:click="skipToggle(q.id,0)"></i>
                                <i v-else="q.skip" class="fa fa-ban yh-v-o-50 text-secondary cursor-pointer"  v-tooltip="'Możliwość wyłącznia pytania z systemu powtórek'" v-on:click="skipToggle(q.id,1)"></i>
                                <span v-if="!q.last_answer_at"  class="rounded-circle c-x c-n" v-tooltip="'ani razu nie odpowiedziałeś na to pytanie'">?</span>
                                <span v-else>
                                    <span v-if="q.correct_in_row<6" v-tooltip="'Twój poziom znajomości pytania w systemie powtórek'"  v-bind:class="'rounded-circle c-x c-'+q.correct_in_row">{{q.correct_in_row}}</span>
                                    <span v-else class="rounded-circle c-x c-p" >+</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="!loading" class="row ql-row">
                        <div class="col">
                            brak pasujących wyników
                        </div>
                    </div>
                </div>
                <div class="ql-foot yh-v-o-50">
                    <div class="clearfix">
                        <div v-if="pages > 1" class="float-left">
                            <button v-if="page > 1" class="btn btn-outline-secondary btn-mic" v-on:click="prevPage()">
                              <i class="fa fa-arrow-left"></i>
                            </button>
                            <button v-else class="btn btn-outline-secondary btn-mic yh-v-o-20">
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <select class="btn btn-outline-secondary btn-mic" v-model="page" v-on:change="fetchQuestions()">
                                <option v-for="p in pages" :value="p">{{p}}</option>
                            </select>
                            <span class="btn btn-mic">
                            / {{pages}}
                            </span>
                            <button v-if="page < pages" class="btn btn-outline-secondary btn-mic" v-on:click="nextPage()">
                                <i class="fa fa-arrow-right"></i>
                            </button>
                            <button v-else class="btn btn-outline-secondary btn-mic yh-v-o-20">
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                        <div v-else-if="pages > 0" class="btn btn-mic float-left">
                            Strona: 1/1
                        </div>
                        <div v-if="count > 0" class="float-right">
                            <span class="btn btn-mic">
                            Limit:
                            </span>
                            <select class="btn btn-outline-secondary btn-mic" v-model="limit" v-on:change="fetchQuestions()">
<!--                                <option v-for="i in 4" v-bind:value="i" >{{i}}</option>-->
                                <option v-for="i in 5" v-bind:value="i*20" >{{i*20}}</option>
                            </select>
                        </div>
                    </div>
                    <div v-if="count > 0" class="float-left mt-2 yh-fs-14">
                      <span class="btn btn-outline-secondary btn-mic yh-v-o-40" v-on:click="keyboard()">
                        <i class="fa fa-keyboard"></i>
                      </span>
                        Wszystkich: {{count}}
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
import Loading from "vue-loading-overlay";

export default {
    data() {
        return {
            loading: false,
            error: null,
            sets: null,
            current_edu: '',
            current_order: 'id',
            current_set: 0,
            questions: null,
            count: 0,
            query: '',
            limit: 20,
            pages: 0,
            page: 1,
        };
    },
    components: {
        Loading,
    },
    created() {
        this.getAllSettings();
        this.fetchSets();
        this.fetchQuestions();
    },
    destroyed() {
        $(window).off('keyup');
        $(window).off('keydown');
    },
    mounted() {
        SideBarCollapseIfActive(true);
        var _this = this;
        $('#fetch-query').keyup(function(e){
            var code = e.key;
            if (code == 'Enter') {
                e.preventDefault();
                _this.fetchQuestions();
            }
        });
        $('#fetch-query').focus();
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
                    _this.prevPage();
                    break;
                case 39: // [->]
                    _this.nextPage();
                    break;
            }
        });
    },
    updated() {
        this.saveAllSettings();
    },
    methods: {
        prevPage() {
            if (this.page > 1) {
                this.page = this.page - 1;
                this.fetchQuestions();
            }
        },
        nextPage() {
            if (this.page < this.pages) {
                this.page = this.page + 1;
                this.fetchQuestions();
            }
        },
        keyboard() {
            setTimeout(function(){
              $('.vc-container').center();
            },50);
            this.$confirm(
                {
                  title: 'Obsługa klawiatury',
                  message: 'Użyj strzałek lewo, prawo do nawigacji po stronach listy.',
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
        getAllSettings() {
            if ($cookies.isKey('app-que-query')) {
                this.query = $cookies.get('app-que-query');
            }
            if ($cookies.isKey('app-que-page')) {
                this.page = $cookies.get('app-que-page');
            }
            if ($cookies.isKey('app-que-limit')) {
                this.limit = $cookies.get('app-que-limit');
            }
            if ($cookies.isKey('app-que-current-set')) {
                this.current_set = $cookies.get('app-que-current-set');
            }
        },
        saveAllSettings() {
            $cookies.set('app-que-query',this.query?this.query:'');
            $cookies.set('app-que-page',this.page);
            $cookies.set('app-que-limit',this.limit);
            $cookies.set('app-que-current-set',this.current_set);
        },
        dateF(s) {
            return moment(s).local().format('YYYY-MM-DD HH:mm:ss');
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
            for(var i=0;i<this.questions.length;i++) {
                if (this.questions[i].id == question_id) {
                    this.questions[i].skip = toggle;
                    continue;
                }
            }
            var _this=this;
            if (this.current_edu=='skip') {
                setTimeout(function(){
                    _this.fetchQuestions(true);
                },500);
            }
        },
        fetchQuestions(noloading=false) {
            this.error = null;
            if (!noloading) {
                this.questions = null;
                this.loading = true;
            }
            axios
                .post(
                    '/api/app/questions/available',
                    {
                        hash_id: yh.auth.hash_id,
                        set_id: this.current_set,
                        filter_edu: this.current_edu,
                        filter_order: this.current_order,
                        query: this.query,
                        limit: this.limit,
                        page: this.page
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.questions = JSON.parse(JSON.stringify(response.data.results));
                    this.count = JSON.parse(JSON.stringify(response.data.count));
                    this.pages = JSON.parse(JSON.stringify(response.data.pages));
                    this.page = JSON.parse(JSON.stringify(response.data.page));
                    this.saveAllSettings();
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        fetchSets() {
            this.error = this.sets = null;
            axios
                .post(
                    '/api/app/questions/sets/available',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.sets = JSON.parse(JSON.stringify(response.data));
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
    },
}
</script>
