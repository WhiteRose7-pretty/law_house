<template>
    <div>
        <h1>Statystyki</h1>

        <loading :active.sync="loading"
                 :loader="'dots'"
                 :can-cancel="false"
                 :is-full-page="true"
                 :opacity="0.8"
                 :color="'#1596b2'"></loading>

        <div v-if="error" class="text-danger">
            {{ error }}
        </div>

        <div class="statistics">
            <select class="choose-set" v-if="sets" id="fetch-set" v-model="current_set" v-on:change="fetchStats()"
                    style="width:100%;">
                <option value="0">Zestaw</option>
                <option v-for="s in sets" v-bind:value="s.id">{{ s.name }}</option>
            </select>

            <div>
                <stats-summary v-if="summary" v-bind:stats="summary"></stats-summary>
            </div>

            <div ref="daily-chart" class="multi-stats-chart d-block mt-3">
                <div class="mt-5 pl-1 pr-1" style="font-size: 1em;">
                    Poniższe statystyki aktualizowane są dobowo. Baza pytań aktualizowana jest na bieżąco ze względu na
                    zmiany w aktach prawnych. Z uwagi na to zmianie może ulec liczba pytań w bazie oraz niektóre
                    statystyki.
                </div>
                <div class="mt-3">
                    <select class="choose-set d-none" v-model="settings.chart_type" v-on:change="fetchDaily()">
                        <option value="line">Liniowy</option>
                        <option value="bar">Kolumnowy</option>
                    </select>
                    <label class="choose-set ml-0 ml-lg-4" for="start-date">Od</label>
                    <input type="date" id="start-date" class="choose-set" v-model="settings.chart_start" style="width: 178px;"
                           v-on:change="fetchDaily()">
                    <div class="d-block d-lg-none"></div>
                    <label class="choose-set ml-0 ml-lg-4" for="end-date">Do</label>
                    <input type="date" class="choose-set" id="end-date" v-model="settings.chart_end" style="width: 178px;"
                           v-on:change="fetchDaily()">

                    <!--                    <select v-if="dates" class="choose-set" v-model="settings.chart_start" v-on:change="fetchDaily()">-->
                    <!--                        <option value="">Od</option>-->
                    <!--                        <option v-for="d in dates" v-bind:value="d">{{d}}</option>-->
                    <!--                    </select>-->

                    <!--                    <select v-if="dates" class="choose-set" v-model="settings.chart_end" v-on:change="fetchDaily()">-->
                    <!--                        <option value="">Do</option>-->
                    <!--                        <option v-for="d in dates" v-bind:value="d">{{d}}</option>-->
                    <!--                    </select>-->

                    <div class="d-block d-lg-none"></div>
                    <label class="choose-set ml-0 ml-lg-4" style="visibility: hidden;">Do</label>
                    <select class="choose-set float-lg-right" v-model="settings.chart_group" style="width: 178px;"
                            v-on:change="fetchDaily()">
                        <option value="day">dzienny</option>
                        <option value="week">tygodniowy</option>
                        <option value="month">miesięczny</option>
                    </select>
                </div>

                <div v-if="canvas_loading" class="d-flex justify-content-center align-items-center"
                     style="height: 300px;">
                    <div style="font-size: 24px;">Wczytywanie...</div>
                </div>
                <div v-if="canvas_error" class="d-flex justify-content-center align-items-center canvas-error"
                     style="height: 300px;">
                    <div style="font-size: 24px;">Nieprawidłowe żądanie</div>
                </div>


                        <canvas id="myChart" width="100" height="50" class="mb-3"></canvas>



                <div class="ql-icons clearfix mb-3 d-none">

                    <span v-bind:class="'ql-check'+(settings.answers?'':' ql-check-disabled')"
                          v-on:click="toggle('answers')">
                        <i class="fa fa-dot-circle"></i>
                        wszystkie
                    </span>

                    <span v-bind:class="'ql-check'+(settings.new?'':' ql-check-disabled')" v-on:click="toggle('new')">
                        <span class="c-x c-n rounded-circle">?</span>
                        nowe
                    </span>

                    <span v-bind:class="'ql-check'+(settings.repeat?'':' ql-check-disabled')"
                          v-on:click="toggle('repeat')">
                        <i class="fa fa-redo" style="color:rgb(101, 116, 205);"></i>
                        powtórki
                    </span>

                    <span v-bind:class="'ql-check'+(settings.correct?'':' ql-check-disabled')"
                          v-on:click="toggle('correct')">
                        <i class="fa fa-check-circle text-success"></i>
                        poprawne
                    </span>

                    <span v-bind:class="'ql-check'+(settings.incorrect?'':' ql-check-disabled')"
                          v-on:click="toggle('incorrect')">
                        <i class="fa fa-times-circle text-warning"></i>
                        niepoprawne
                    </span>

                </div>
                <div class="ql-icons clearfix mb-5 d-none">

                    <span v-bind:class="'ql-check'+(settings.correct_0?'':' ql-check-disabled')"
                          v-on:click="toggle('correct_0')"
                          v-tooltip="'Pytania na które nie udzieliłeś poprawnej odpowiedzi.'">
                        <span class="c-x c-0 rounded-circle">0</span>
                        poziom 0
                    </span>

                    <span v-bind:class="'ql-check'+(settings.correct_1?'':' ql-check-disabled')"
                          v-on:click="toggle('correct_1')"
                          v-tooltip="'Pytania na które udzieliłeś ostatnio poprawnej odpowiedzi.'">
                        <span class="c-x c-1 rounded-circle">1</span>
                        poziom 1
                    </span>

                    <span v-bind:class="'ql-check'+(settings.correct_2?'':' ql-check-disabled')"
                          v-on:click="toggle('correct_2')"
                          v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 2 razy z rzędu.'">
                        <span class="c-x c-2 rounded-circle">2</span>
                        poziom 2
                    </span>

                    <span v-bind:class="'ql-check'+(settings.correct_3?'':' ql-check-disabled')"
                          v-on:click="toggle('correct_3')"
                          v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 3 razy z rzędu.'">
                        <span class="c-x c-3 rounded-circle">0</span>
                        poziom 3
                    </span>

                    <span v-bind:class="'ql-check'+(settings.correct_4?'':' ql-check-disabled')"
                          v-on:click="toggle('correct_4')"
                          v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 4 razy z rzędu.'">
                        <span class="c-x c-4 rounded-circle">4</span>
                        poziom 4
                    </span>

                    <span v-bind:class="'ql-check'+(settings.correct_5?'':' ql-check-disabled')"
                          v-on:click="toggle('correct_5')"
                          v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 5 razy z rzędu.'">
                        <span class="c-x c-5 rounded-circle">5</span>
                        poziom 5
                    </span>

                    <span v-bind:class="'ql-check'+(settings.correct_m?'':' ql-check-disabled')"
                          v-on:click="toggle('correct_m')"
                          v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi więcej niż 5 razy z rzędu.'">
                        <span class="c-x c-p rounded-circle">+</span>
                        poziom +
                    </span>

                </div>

            </div>
        </div>

        <div class="mt-5 pl-1 pr-1 d-none" style="font-size: 0.8em;">
            Na urządzeniach mobilnych niektóre statystyki nie są pokazywane w ogóle lub pokazywane jedynie w trybie
            poziomym.
        </div>

    </div>

</template>

<script>

import Loading from "vue-loading-overlay";
import VJstree from "vue-jstree";

export default {
    data() {
        return {
            loading: false,
            error: null,
            current_set: 0,
            sets: null,
            summary: null,
            daily: null,
            daily_chart: null,
            settings: {
                chart_type: 'bar',
                chart_start: '',
                chart_end: '',
                chart_group: 'day',
                new: false,
                repeat: false,
                answers: false,
                correct: true,
                incorrect: true,
                correct_0: false,
                correct_1: false,
                correct_2: false,
                correct_3: false,
                correct_4: false,
                correct_5: false,
                correct_m: false
            },
            canvas_loading: true,
            canvas_error: true,
        };
    },
    components: {
        Loading,
    },
    created() {
        this.fetchSets();
        this.fetchStats();
    },
    destroyed() {
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
    },
    methods: {
        scrollMeTo(refName) {
            var _this = this;
            setTimeout(function () {
                var element = _this.$refs[refName];
                var top = element.offsetTop;
                window.scrollTo(0, top);
            });
        },
        toggle(l) {
            if (this.settings[l]) {
                this.settings[l] = false;
                this.fetchDaily();
                return;
            }
            this.settings[l] = true;
            this.fetchDaily();
        },
        chart() {
            var ctx = document.getElementById('myChart');
            console.log(ctx);
            if (this.daily_chart) {
                this.daily_chart.destroy();
            }
            this.daily_chart = new Chart(ctx, this.daily);
            this.scrollMeTo('daily-chart');
        },
        fetchStats() {
            this.fetchSummary();
            this.fetchDaily();
        },
        fetchSummary() {
            this.error = this.summary = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/stats/summary',
                    {
                        hash_id: yh.auth.hash_id,
                        questions_set_id: this.current_set,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.summary = response.data.stats;
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
        fetchDaily() {
            this.error = this.daily = this.dates = null;
            this.canvas_error = false;
            let ctx = document.getElementById('myChart');
            if (ctx) {
                ctx.style.visibility = 'hidden';
            }

            this.canvas_loading = true;
            axios
                .post(
                    '/api/app/stats/daily',
                    {
                        hash_id: yh.auth.hash_id,
                        questions_set_id: this.current_set,
                        settings: this.settings,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.canvas_loading = false;

                    this.dates = response.data.dates;
                    this.daily = response.data.stats;
                    this.settings.chart_start = response.data.chart_start;
                    this.settings.chart_end = response.data.chart_end;
                    if (ctx) {
                        ctx.style.visibility = 'visible';
                    }
                    this.chart();
                }).catch(error => {

                    this.loading = false;
                    this.canvas_loading = false;
                    this.canvas_error = true;
                    if (error.response.data.location) {
                        setTimeout(function () {
                            document.location = error.response.data.location;
                        }, 2000);
                    }
            });
        },
        fetchSets() {
            this.error = this.sets = null;
            this.loading = true;
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
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
    },
}

// function onLegendHover(event, legendItem) {
//     if (hovering) {
//         return;
//     }
//     hovering = true;
//     tooltip.innerHTML = tooltips[legendItem.datasetIndex];
//     tooltip.style.left = event.x + "px";
//     tooltip.style.top = event.y + "px";
// }
//
// function onLegendLeave() {
//     hovering = false;
//     tooltip.innerHTML = "";
// }

</script>
