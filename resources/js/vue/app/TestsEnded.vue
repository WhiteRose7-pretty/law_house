<template>
    <div class="test-list shop">
        <h1>Zakończone</h1>
        <div v-if="loading" class="text-info">
            Ładowanie...
        </div>

        <div v-else>
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a href="#tab-01" aria-controls="tab-01" role="tab" data-toggle="tab"
                                           class="active">Testy</a></li>
                <li role="presentation"><a href="#tab-02" aria-controls="tab-02" role="tab" data-toggle="tab">Gry</a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="tab-01">
                    <div v-if="list && list.length>0">
                        <div class="row test-summary mb-4" v-for="test in list">
                            <div class="pie-chart col-12 col-sm-3 col-lg-2 text-center p-4 pt-2 pb-2 p-sm-1  yh-v-o-70">
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
                                    <text x="100" y="105" dominant-baseline="middle" text-anchor="middle" fill="#666"
                                          font-size="3em" font-weight="500">
                                        {{
                                            Math.round(test.questions_answered_correct_count / test.questions_count * 100)
                                        }}%
                                    </text>
                                </svg>
                            </div>
                            <div class="col-12 col-sm-9 pt-sm-2 pl-sm-5">
                                <div class="yh-gap-3 d-sm-none"></div>
                                <h5 class="float-right yh-v-o-60 font-italic">{{ dateF(test.finished_at) }}</h5>
                                <h3 class="yh-fw-5 mb-4">{{ test.name }}</h3>
                                <h4 class="mb-2">{{ test.info }}</h4>
                                <p style="font-size:0.9rem; line-height:1.3rem;">
                        <span v-if="test.show_correct" class="d-block">
                            Tryb nauki, widziałeś poprawne odpowiedzi.
                        </span>
                                    <span v-if="test.time_limit">
                        Egzamin<span v-if="test.show_correct"> i nauka</span>: {{ test.time_limit }} min.<br>
                        Zajął:
                            <span v-if="!test.completion_seconds">cały dostępny czas</span>
                            <span v-else>{{ timeDiffCalc(test.completion_seconds) }}</span>
                        </span>
                                </p>
                                <p style="font-size:1.4rem;">
                                    Wynik: {{ test.questions_answered_correct_count }}/{{ test.questions_count }}
                                    <span v-if="test.questions_count - test.questions_answered_count>0"><br>Pominiętych: {{
                                            test.questions_count - test.questions_answered_count
                                        }}</span>
                                </p>
                                <router-link :to="{ name: 'tests.ended.summary', params:{ id: test.id } }"
                                             class="btn btn-sm btn-primary text-white">Przeglądaj
                                </router-link>
                                <button class="btn btn-danger btn-sm" v-on:click="remove(test.id)">Usuń</button>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="mb-3" style="font-size:1.5em;">Brak testów na liście zakończonych.</div>
                        <router-link class="btn-primary btn text-white" :to="{ name: 'tests.new' }">
                            Nowy Test
                        </router-link>
                        <router-link class="btn-primary btn text-white ml-2" :to="{ name: 'tests.continue' }">
                            Kontynuuj
                        </router-link>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="tab-02">
                    <div v-if="game_list && game_list.length>0" class="game-list">
                        <div v-for="(test, idx) in game_list" class="game-summary-group">
                            <div v-bind:id="'group-toggle-'+ idx" v-on:click="toggle(idx)" v-if="test.sub_games.length > 0" class="g-toggle" title="more sub Games">
                                <i class="i-active fa fa-toggle-on"></i>
                                <i class="i-disabled fa fa-toggle-off"></i>
                            </div>
                            <div class="row test-summary mb-4">
                                <div
                                    class="pie-chart col-12 col-sm-3 col-lg-2 text-center p-4 pt-2 pb-2 p-sm-1  yh-v-o-70">
                                    <svg class="my-pie" viewBox="0 0 200 200">
                                        <circle r="85" cx="100" cy="100"
                                                fill="transparent"
                                                stroke="#C76857"
                                                stroke-width="30"
                                        />

                                        <circle r="85" cx="100" cy="100" fill="transparent"
                                                v-if="test.questions_answered_count"
                                                stroke="#62A136"
                                                stroke-width="30"
                                                v-bind:stroke-dasharray="`calc(${test.questions_answered_correct_count}/${test.user_game.questions_count } * 2*22/7*85) calc(2*22/7*85)`"
                                        />
                                        <text x="100" y="105" dominant-baseline="middle" text-anchor="middle"
                                              fill="#666"
                                              font-size="3em" font-weight="500">
                                            {{
                                                Math.round(test.questions_answered_correct_count / test.user_game.questions_count * 100)
                                            }}%
                                        </text>
                                    </svg>
                                </div>
                                <div class="col-12 col-sm-9 pt-sm-2 pl-sm-5" >
                                    <div class="yh-gap-3 d-sm-none"></div>
                                    <h5 class="float-right yh-v-o-60 font-italic">{{
                                            dateF(test.user_game.finished_at)
                                        }}</h5>
                                    <h3 class="yh-fw-5 mb-4">{{ test.user_game.name }}</h3>

                                    <p style="font-size:0.9rem; line-height:1.3rem;">
                                        <span v-if="test.user_game.type == 'race'"> Typ: Wyścig </span>
                                        <span v-else-if="test.user_game.type == 'exam'"> Typ: Egzamin </span>
                                    </p>
                                    <p style="font-size:1.4rem;">
                                        Wynik: {{
                                            test.questions_answered_correct_count
                                        }}/{{ test.user_game.questions_count }}
                                        <span
                                            v-if="test.user_game.questions_count - test.questions_answered_count>0"><br>Pominiętych: {{ test.user_game.questions_count - test.questions_answered_count }}</span>
                                    </p>
                                    <router-link :to="{ name: 'games.ended.summary', params:{ id: test.id } }" v-if=""
                                                 class="btn btn-sm btn-primary text-white">Przeglądaj
                                    </router-link>
                                </div>
                            </div>
                            <div>
                                <div v-bind:id="'group-list-'+idx" class="row test-summary ml-4 mb-4 game-sub-group" style="background: #9e9e9e4a !important; display: none;" v-for="sub_test in test.sub_games">
                                    <div
                                        class="pie-chart col-12 col-sm-3 col-lg-2 text-center p-4 pt-2 pb-2 p-sm-1  yh-v-o-70">
                                        <svg class="my-pie" viewBox="0 0 200 200">
                                            <circle r="85" cx="100" cy="100"
                                                    fill="transparent"
                                                    stroke="#C76857"
                                                    stroke-width="30"
                                            />

                                            <circle r="85" cx="100" cy="100" fill="transparent"
                                                    v-if="sub_test.questions_answered_count"
                                                    stroke="#62A136"
                                                    stroke-width="30"
                                                    v-bind:stroke-dasharray="`calc(${sub_test.questions_answered_correct_count}/${sub_test.user_game.questions_count } * 2*22/7*85) calc(2*22/7*85)`"
                                            />
                                            <text x="100" y="105" dominant-baseline="middle" text-anchor="middle"
                                                  fill="#666"
                                                  font-size="3em" font-weight="500">
                                                {{
                                                    Math.round(sub_test.questions_answered_correct_count / sub_test.user_game.questions_count * 100)
                                                }}%
                                            </text>
                                        </svg>
                                    </div>
                                    <div class="col-12 col-sm-9 pt-sm-2 pl-sm-5">
                                        <div class="yh-gap-3 d-sm-none"></div>
                                        <h5 class="float-right yh-v-o-60 font-italic">
                                            {{ dateF(sub_test.user_game.finished_at) }}</h5>
                                        <h3 class="yh-fw-5 mb-4">{{ sub_test.user_game.name }}</h3>

                                        <p style="font-size:1.4rem;">
                                            Wynik: {{
                                                sub_test.questions_answered_correct_count
                                            }}/{{ sub_test.user_game.questions_count }}
                                            <span
                                                v-if="sub_test.user_game.questions_count - sub_test.questions_answered_count>0"><br>Pominiętych: {{ sub_test.user_game.questions_count - sub_test.questions_answered_count }}</span>
                                        </p>
                                        <router-link :to="{ name: 'games.ended.summary', params:{ id: sub_test.id } }"
                                                     v-if=""
                                                     class="btn btn-sm btn-primary text-white">Przeglądaj
                                        </router-link>

                                    </div>
                                </div>
                            </div>
                        </div>
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
            list: null,
            game_list: null,
            index: 0,
            user_id: null,
        };
    },
    created() {
        this.user_id = yh.auth.user_id;
        this.fetch();
        this.fetchGame();
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
    },
    methods: {
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
        fetch() {
            this.error = this.test = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/tests/finished',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.list = JSON.parse(JSON.stringify(response.data));
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
        fetchGame() {
            this.error = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/games/finished',
                    {
                        hash_id: yh.auth.hash_id,
                        filter: 'finished',
                    }
                )
                .then(response => {
                    this.game_list = JSON.parse(JSON.stringify(response.data.list));
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
        remove(id) {
            setTimeout(function () {
                $('.vc-container').center();
            }, 50);
            this.$confirm(
                {
                    message: `To działanie spowoduje usunięcie tego testu trwale z tej listy, statystyki odpowiedzi i powtórek pozostaną bez zmian. Czy chcesz kontynuować?`,
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
                            axios
                                .post(
                                    '/api/app/tests/delete',
                                    {
                                        hash_id: yh.auth.hash_id,
                                        id: id,
                                    }
                                )
                                .then(response => {
                                    this.fetch();
                                }).catch(error => {
                                this.loading = false;
                                this.error = error.response.data.message || error.message;
                                if (error.response.data.location) {
                                    setTimeout(function () {
                                        document.location = error.response.data.location;
                                    }, 2000);
                                }
                            });
                        }
                    }
                }
            )
        },
        toggle(index) {
            if ($('#group-toggle-'+index).hasClass('g-active')) {
                $('#group-toggle-'+index).removeClass('g-active');
                $('#group-list-'+index).hide();
                return;
            }
            $('#group-toggle-'+index).addClass('g-active');
            $('#group-list-'+index).show();
        },
    },
}
</script>
