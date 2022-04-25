<template>
    <div class="test-list">
        <h1>Kontynuuj</h1>
        <div v-if="loading" class="text-info">
            Ładowanie...
        </div>
        <div v-else-if="list && list.length>0">
            <div class="row test-summary mb-4" v-for="test in list">
                <div class="pie-chart col-12 col-sm-3 col-lg-2 text-center p-1  yh-v-o-70">
                    <svg class="my-pie" viewBox="0 0 200 200">
                        <circle r="85" cx="100" cy="100"
                            fill="transparent"
                            stroke="#ccc"
                            stroke-width="30"
                        />
                        <circle r="85" cx="100" cy="100" fill="transparent"
                            stroke="#62A136"
                            stroke-width="30"
                            v-bind:stroke-dasharray="`calc(${test.questions_answered_count}/${test.questions_count} * 2*22/7*85) calc(2*22/7*85)`"
                         />
                         <text x="100" y="105" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="3em" font-weight="500">{{Math.round(test.questions_answered_count/test.questions_count*100)}}%</text>
                    </svg>
                </div>
                <div class="col-12 col-sm-9 pt-sm-2 pl-sm-5">
                    <div class="yh-gap-3 d-sm-none"></div>
                    <h5 class="float-right yh-v-o-60 font-italic">{{dateF(test.created_at)}}</h5>
                    <h3 class="yh-fw-5 mb-4">{{test.name}}</h3>
                    <h4 class="mb-2">{{test.info}}</h4>
                    <p style="font-size:1.2rem;">
                        Odpowiedziałeś: {{test.questions_answered_count}}/{{test.questions_count}}
                        <span v-if="test.time_limit"><br>Limit czasu: {{test.time_limit}} min., pozostało: <span v-bind:id="'time-remaining-'+test.id">{{test.time_remaining}}</span></span>
                    </p>

                    <router-link :to="{ name: 'tests.run', params:{ id: test.id } }" class="btn btn-sm btn-primary text-white">Kontynuuj</router-link>
                    <button class="btn btn-warning btn-sm ml-2" v-on:click="finish(test.id)">Zakończ</button>
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
import axios from 'axios';
export default {
    data() {
        return {
            loading: false,
            error: null,
            list: null,
            index: 0,
            timeout: null,
            timeout_set: true,
        };
    },
    created() {
        this.fetch();
    },
    destroyed() {
        this.timeout_set = false;
        if (this.timeout!=null) {
            clearTimeout(this.timeout);
        }
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
    },
    methods: {
        checkTimesTimeout() {
            if (this.timeout_set) {
                var _this = this;
                this.timeout = setTimeout(function(){
                    _this.fixTimes(true);
                },1000)
            }
        },
        timeDiff(limit,date) {
            var d=new Date(date);
            var dn=new Date();
            var diff=Math.round((dn.getTime() - d.getTime())/1000);
            if (diff>limit*60) {
                return 'czas upłynął';
            }
            diff=limit*60-diff;
            var h=Math.floor(diff/(60*60));
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
        fixTimes(push=false) {
            this.timeout_set=false;
            if (this.list && this.list.length >0) {
                for(var i=0;i<this.list.length;i++) {
                    if (this.list[i].time_limit) {
                        this.list[i].time_remaining = this.timeDiff(this.list[i].time_limit,this.list[i].created_at);
                        if (this.list[i].time_remaining == 'czas upłynął') {
                            this.finishCall(this.list[i].id,true);
                        } else {
                          this.timeout_set=true;
                        }
                        if (push) {
                            $('#time-remaining-'+this.list[i].id).text(this.list[i].time_remaining);
                        }
                    }
                }
            }
            this.checkTimesTimeout();
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
                    '/api/app/tests/continue',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.list = JSON.parse(JSON.stringify(response.data));
                    this.fixTimes();
                    this.loading = false;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        finish(id) {
            setTimeout(function(){
              $('.vc-container').center();
            },50);
            var _this = this;
            this.$confirm(
              {
                message: `Czy na pewno chcesz zakończyć ten test, pomijając resztę pytań?`,
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
                      _this.finishCall(id);
                  }
                }
              }
            )
        },
        finishCall(id, out_of_time=false) {
            this.error = null;
            axios
                .post(
                    '/api/app/tests/end',
                    {
                        hash_id: yh.auth.hash_id,
                        id: id,
                        out_of_time: out_of_time,
                    }
                )
                .then(response => {
                    this.$router.push({ name: 'tests.ended.summary', params: { id: response.data.id } });
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
