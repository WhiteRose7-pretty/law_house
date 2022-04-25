<template>
    <div class="test-list">
        <h1>Powtarzaj</h1>
        <questions-repeats-calendar></questions-repeats-calendar>

        <loading :active.sync="loading"
                 :loader="'dots'"
                 :can-cancel="false"
                 :is-full-page="true"
                 :opacity = "0.8"
                 :color="'#1596b2'"   ></loading>

        <div v-if="list">
            <div v-for="r1 in list">
                <div v-if="typeof r1.stats.empty_stats == 'undefined'" v-bind:class="(typeof r1.name=='undefined'?'test-summary-group':'test-summary')+' mb-4'">
                    <div v-bind:id="'group-toggle-'+r1.index" v-on:click="toggle(r1.index)" v-if="typeof r1.list != 'undefined'" class="g-toggle">
                        <i class="i-active fa fa-toggle-on"></i>
                        <i class="i-disabled fa fa-toggle-off"></i>
                    </div>
                    <div class="test-summary-container row mt-sm-5 mt-md-0">
                        <div class="pie-chart col-12 col-sm-6 col-md-3 col-lg-2 text-center p-1 pl-3 pr-3 p-md-1">
                            <svg class="my-pie" viewBox="0 0 200 200">
                                <circle r="85" cx="100" cy="100"
                                    fill="transparent"
                                    stroke="#ccc"
                                    stroke-width="30"
                                />
                                <circle r="85" cx="100" cy="100" fill="transparent"
                                    stroke="#e5e998"
                                    stroke-width="30"
                                    v-bind:stroke-dasharray="`calc(${r1.stats.correct_0}/${r1.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                 />
                                <g v-bind:transform="'rotate('+count_radius(r1,0)+' 100 100)'">
                                    <circle r="85" cx="100" cy="100" fill="transparent"
                                        stroke="#deea31"
                                        stroke-width="30"
                                        v-bind:stroke-dasharray="`calc(${r1.stats.correct_1}/${r1.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                    />
                                </g>
                                 <g v-bind:transform="'rotate('+count_radius(r1,1)+' 100 100)'">
                                     <circle r="85" cx="100" cy="100" fill="transparent"
                                         stroke="#b0dc33"
                                         stroke-width="30"
                                         v-bind:stroke-dasharray="`calc(${r1.stats.correct_2}/${r1.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                      />
                                 </g>
                                 <g v-bind:transform="'rotate('+count_radius(r1,2)+' 100 100)'">
                                     <circle r="85" cx="100" cy="100" fill="transparent"
                                         stroke="#7ccc30"
                                         stroke-width="30"
                                         v-bind:stroke-dasharray="`calc(${r1.stats.correct_3}/${r1.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                      />
                                 </g>
                                 <g v-bind:transform="'rotate('+count_radius(r1,3)+' 100 100)'">
                                     <circle r="85" cx="100" cy="100" fill="transparent"
                                         stroke="#35ae3f"
                                         stroke-width="30"
                                         v-bind:stroke-dasharray="`calc(${r1.stats.correct_4}/${r1.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                      />
                                 </g>
                                 <g v-bind:transform="'rotate('+count_radius(r1,4)+' 100 100)'">
                                     <circle r="85" cx="100" cy="100" fill="transparent"
                                         stroke="#236930"
                                         stroke-width="30"
                                         v-bind:stroke-dasharray="`calc(${r1.stats.correct_5}/${r1.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                      />
                                 </g>
                                 <g v-bind:transform="'rotate('+count_radius(r1,5)+' 100 100)'">
                                     <circle r="85" cx="100" cy="100" fill="transparent"
                                         stroke="#7c4787"
                                         stroke-width="30"
                                         v-bind:stroke-dasharray="`calc(${r1.stats.correct_m}/${r1.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                      />
                                 </g>
                                 <text x="100" y="95" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="3em" font-weight="500">{{r1.stats.today_repeat}}</text>
                                 <text x="100" y="120" dominant-baseline="middle" text-anchor="middle" fill="#999" font-size="1em" font-weight="400">do powtórki</text>
                                 <text x="100" y="140" dominant-baseline="middle" text-anchor="middle" fill="#999" font-size="1em" font-weight="400">dziś</text>
                            </svg>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="line-chart-list mt-4 mt-sm-1 mt-md-0 ml-md-5 mr-md-3">
                                <div class="line-chart"  v-tooltip="'Pytania na które nie udzieliłeś poprawnej odpowiedzi.'" >
                                    <div class="line-chart-val" v-bind:style="'width:'+Math.round(r1.stats.correct_0/r1.stats.q_total*100)+'%; background-color:#e5e998;'"></div>
                                    <span>poziom 0</span>
                                </div>
                                <div class="line-chart"   v-tooltip="'Pytania na które udzieliłeś ostatnio poprawnej odpowiedzi.'">
                                    <div class="line-chart-val" v-bind:style="'width:'+Math.round(r1.stats.correct_1/r1.stats.q_total*100)+'%; background-color:#deea31;'"></div>
                                    <span>poziom 1</span>
                                </div>
                                <div class="line-chart"  v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 2 razy z rzędu.'">
                                    <div class="line-chart-val" v-bind:style="'width:'+Math.round(r1.stats.correct_2/r1.stats.q_total*100)+'%; background-color:#b0dc33;'"></div>
                                    <span>poziom 2</span>
                                </div>
                                <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 3 razy z rzędu.'">
                                    <div class="line-chart-val" v-bind:style="'width:'+Math.round(r1.stats.correct_3/r1.stats.q_total*100)+'%; background-color:#7ccc30;'"></div>
                                    <span>poziom 3</span>
                                </div>
                                <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 4 razy z rzędu.'">
                                    <div class="line-chart-val" v-bind:style="'width:'+Math.round(r1.stats.correct_4/r1.stats.q_total*100)+'%; background-color:#35ae3f;'"></div>
                                    <span>poziom 4</span>
                                </div>
                                <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 5 razy z rzędu.'">
                                    <div class="line-chart-val" v-bind:style="'width:'+Math.round(r1.stats.correct_5/r1.stats.q_total*100)+'%; background-color:#236930;'"></div>
                                    <span>poziom 5</span>
                                </div>
                                <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi więcej niż 5 razy z rzędu.'">
                                    <div class="line-chart-val" v-bind:style="'width:'+Math.round(r1.stats.correct_m/r1.stats.q_total*100)+'%; background-color:#7c4787;'"></div>
                                    <span>poziom +</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-5 col-lg-6 pt-2 pl-md-5">
                            <h2 class="yh-fw-5 mb-4">
                                <span v-if="r1.name">{{r1.name}}</span>
                                <span v-else>{{r1.group}}</span>
                            </h2>
                            <button v-if="r1.stats.today_repeat" v-on:click="repeat(r1)" class="btn btn-sm btn-primary text-white">Powtarzaj</button>
                        </div>
                    </div>
                    <div v-bind:id="'group-list-'+r1.index" v-if="typeof r1.list != 'undefined'" class="summary-group-list" style="display:none;">
                        <div v-for="r2 in r1.list">
                            <div class="test-summary-container tsc-bt row">
                                <div class="pie-chart col-12 col-sm-6 col-md-3 col-lg-2 text-center p-1 pl-3 pr-3 p-md-1">
                                    <svg class="my-pie" viewBox="0 0 200 200">
                                        <circle r="85" cx="100" cy="100"
                                            fill="transparent"
                                            stroke="#ccc"
                                            stroke-width="30"
                                        />
                                        <circle r="85" cx="100" cy="100" fill="transparent"
                                            stroke="#e5e998"
                                            stroke-width="30"
                                            v-bind:stroke-dasharray="`calc(${r2.stats.correct_0}/${r2.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                         />
                                         <g v-bind:transform="'rotate('+count_radius(r2,0)+' 100 100)'">
                                             <circle r="85" cx="100" cy="100" fill="transparent"
                                                 stroke="#deea31"
                                                 stroke-width="30"
                                                 v-bind:stroke-dasharray="`calc(${r2.stats.correct_1}/${r2.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                              />
                                         </g>
                                         <g v-bind:transform="'rotate('+count_radius(r2,1)+' 100 100)'">
                                             <circle r="85" cx="100" cy="100" fill="transparent"
                                                 stroke="#b0dc33"
                                                 stroke-width="30"
                                                 v-bind:stroke-dasharray="`calc(${r2.stats.correct_2}/${r2.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                              />
                                         </g>
                                         <g v-bind:transform="'rotate('+count_radius(r2,2)+' 100 100)'">
                                             <circle r="85" cx="100" cy="100" fill="transparent"
                                                 stroke="#7ccc30"
                                                 stroke-width="30"
                                                 v-bind:stroke-dasharray="`calc(${r2.stats.correct_3}/${r2.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                              />
                                         </g>
                                         <g v-bind:transform="'rotate('+count_radius(r2,3)+' 100 100)'">
                                             <circle r="85" cx="100" cy="100" fill="transparent"
                                                 stroke="#35ae3f"
                                                 stroke-width="30"
                                                 v-bind:stroke-dasharray="`calc(${r2.stats.correct_4}/${r2.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                              />
                                         </g>
                                         <g v-bind:transform="'rotate('+count_radius(r2,4)+' 100 100)'">
                                             <circle r="85" cx="100" cy="100" fill="transparent"
                                                 stroke="#236930"
                                                 stroke-width="30"
                                                 v-bind:stroke-dasharray="`calc(${r2.stats.correct_5}/${r2.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                              />
                                         </g>
                                         <g v-bind:transform="'rotate('+count_radius(r2,5)+' 100 100)'">
                                             <circle r="85" cx="100" cy="100" fill="transparent"
                                                 stroke="#7c4787"
                                                 stroke-width="30"
                                                 v-bind:stroke-dasharray="`calc(${r2.stats.correct_m}/${r2.stats.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                              />
                                         </g>
                                         <text x="100" y="95" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="3em" font-weight="500">{{r2.stats.today_repeat}}</text>
                                         <text x="100" y="120" dominant-baseline="middle" text-anchor="middle" fill="#999" font-size="1em" font-weight="400">do powtórki</text>
                                         <text x="100" y="140" dominant-baseline="middle" text-anchor="middle" fill="#999" font-size="1em" font-weight="400">dziś</text>
                                    </svg>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="line-chart-list mt-4 mt-sm-1 mt-md-0 ml-md-5 mr-md-3">
                                        <div class="line-chart cursor" v-tooltip="'Pytania na które nie udzieliłeś poprawnej odpowiedzi.'" >
                                            <div class="line-chart-val" v-bind:style="'width:'+Math.round(r2.stats.correct_0/r2.stats.q_total*100)+'%; background-color:#e5e998;'"></div>
                                            <span>poziom 0</span>
                                        </div>
                                        <div class="line-chart"  v-tooltip="'Pytania na które udzieliłeś ostatnio poprawnej odpowiedzi.'">
                                            <div class="line-chart-val" v-bind:style="'width:'+Math.round(r2.stats.correct_1/r2.stats.q_total*100)+'%; background-color:#deea31;'"></div>
                                            <span>poziom 1</span>
                                        </div>
                                        <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 2 razy z rzędu.'">
                                            <div class="line-chart-val" v-bind:style="'width:'+Math.round(r2.stats.correct_2/r2.stats.q_total*100)+'%; background-color:#b0dc33;'"></div>
                                            <span>poziom 2</span>
                                        </div>
                                        <div class="line-chart"  v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 3 razy z rzędu.'">
                                            <div class="line-chart-val" v-bind:style="'width:'+Math.round(r2.stats.correct_3/r2.stats.q_total*100)+'%; background-color:#7ccc30;'"></div>
                                            <span>poziom 3</span>
                                        </div>
                                        <div class="line-chart"  v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 4 razy z rzędu.'">
                                            <div class="line-chart-val" v-bind:style="'width:'+Math.round(r2.stats.correct_4/r2.stats.q_total*100)+'%; background-color:#35ae3f;'"></div>
                                            <span>poziom 4</span>
                                        </div>
                                        <div class="line-chart"  v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 5 razy z rzędu.'">
                                            <div class="line-chart-val" v-bind:style="'width:'+Math.round(r2.stats.correct_5/r2.stats.q_total*100)+'%; background-color:#236930;'"></div>
                                            <span>poziom 5</span>
                                        </div>
                                        <div class="line-chart"  v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi więcej niż 5 razy z rzędu.'">
                                            <div class="line-chart-val" v-bind:style="'width:'+Math.round(r2.stats.correct_m/r2.stats.q_total*100)+'%; background-color:#7c4787;'"></div>
                                            <span>poziom +</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5 col-lg-6 pt-2 pl-md-5">
                                    <h2 class="yh-fw-5 mb-4">
                                        <span v-if="r2.name">{{r2.name}}</span>
                                        <span v-else>{{r2.group}}</span>
                                    </h2>
                                    <button v-if="r2.stats.today_repeat" v-on:click="repeat(r2)" class="btn btn-sm btn-primary text-white">Powtarzaj</button>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="mb-3" style="font-size:1.5em;">Nie masz obecnie żadnego aktywnego testu.</div>
            <router-link class="btn-primary btn text-white" :to="{ name: 'tests.new' }">
                Nowy Test
            </router-link>
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
            list: null,
            timeout_refresh: true,
            timeout_refresh_id: null,
        };
    },
    components: {
        Loading,
    },
    created() {
        if ($cookies.isKey('app-questions-repeats-refresh')) {
            $cookies.remove('app-questions-repeats-refresh');
        }
        this.fetch();
        this.checkRefresh();
    },
    destroyed() {
        this.timeout_refresh = false;
        if (this.timeout_refresh_id) {
            clearTimeout(this.timeout_refresh_id);
        }
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
    },
    methods: {
        toggle(index) {
            if ($('#group-toggle-'+index).hasClass('g-active')) {
                $('#group-toggle-'+index).removeClass('g-active');
                $('#group-list-'+index).hide();
                return;
            }
            $('#group-toggle-'+index).addClass('g-active');
            $('#group-list-'+index).show();
        },
        checkRefresh() {
            if ($cookies.isKey('app-questions-repeats-refresh')) {
                $cookies.remove('app-questions-repeats-refresh');
                this.fetch();
            }
            if (this.timeout_refresh) {
                var _this=this;
                setTimeout(function(){
                    _this.checkRefresh();
                },1000);
            }
        },
        count_radius(m,i) {
            var x=parseFloat(m.stats.correct_0);
            x = i>0 ? x+parseFloat(m.stats.correct_1) : x;
            x = i>1 ? x+parseFloat(m.stats.correct_2) : x;
            x = i>2 ? x+parseFloat(m.stats.correct_3) : x;
            x = i>3 ? x+parseFloat(m.stats.correct_4) : x;
            x = i>4 ? x+parseFloat(m.stats.correct_5) : x;
            if(!m.stats.q_total){
                console.log(m.stats.q_total);
                console.log(m.id);
            }
            return x/m.stats.q_total*360;
        },
        dateF(s) {
            var r = s.split('T');
            var s2 = r[0];
            s2 = this.replaceAll(s2,'-','/');
            return s2;
        },
        replaceAll(string, search, replace) {
            return string.split(search).join(replace);
        },
        fetch() {
            this.error = this.list = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/questions/repeats/daily',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.list = JSON.parse(JSON.stringify(response.data.list));
                    this.loading = false;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        repeat(row) {
            axios
                .post(
                    '/api/app/questions/repeats/make',
                    {
                        hash_id: yh.auth.hash_id,
                        ids : (typeof row.id == 'undefined' ? row.ida : row.id),
                        name : 'Powtórka z ' + (typeof row.name == 'undefined' ? row.group : row.name),
                    }
                )
                .then(response => {
                    this.$router.push({ name: 'tests.run', params: { id: response.data.id } });
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        }
    },
}
</script>
