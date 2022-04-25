<template>
  <div class="repeats-calendar mt-3 mb-5" v-if="list">
      <div class="repcal-header row">
          <div class="col-sm-6 col-xs-12 text-center">
              Dzienne statystyki
          </div>
          <div class="col-6 d-none d-sm-block  text-center" >
              Kalendarz Powtórek
          </div>
      </div>
      <div class="row">
          <div v-for="(c, i) in list"
              v-bind:class="'col '+((i>2&&i<5)||(i>5&&i<8)?' d-sm-block': (c.date==today?' col-12 col-sm-4 ':''))+(i<3||i>7?' d-lg-block d-sm-none d-xs-block ':(c.date==today?' col-md-4 ':' col-md-2 '))+' col-lg-'+(c.date==today?'2':'1')+(c.date==today?' col-today':'')"
          >
              <div class="repcal-day">
                  {{c.day}}
              </div>

              <div class="repcal-summary" v-if="c.type=='summary'">
                  <div>
                      <svg class="my-pie yh-v-o-70" viewBox="0 0 200 200">
                          <circle v-if="c.total>0" r="85" cx="100" cy="100"
                              fill="transparent"
                              stroke="#C76857"
                              stroke-width="30"
                          />
                          <circle v-else r="85" cx="100" cy="100"
                              fill="transparent"
                              stroke="#EEE"
                              stroke-width="30"
                          />
                          <circle v-if="c.total>0" r="85" cx="100" cy="100" fill="transparent"
                              stroke="#62A136"
                              stroke-width="30"
                              v-bind:stroke-dasharray="`calc(${c.correct}/${c.total} * 2*22/7*85) calc(2*22/7*85)`"
                           />
                          <text v-if="c.total" x="100" y="105" dominant-baseline="middle" text-anchor="middle" fill="#555" font-size="3em" font-weight="500">{{Math.round(c.correct/c.total*100)}}%</text>
                          <text v-else x="100" y="105" dominant-baseline="middle" text-anchor="middle" fill="#555" font-size="3em" font-weight="500">brak</text>
                      </svg>
                      <div class="mt-3 text-center">
                          {{c.correct}}<span class="text-secondary">/{{c.total}}</span>
                      </div>
                  </div>
              </div>
              <div class="repcal-plan" v-else>
                  <div>
                      <svg class="my-pie" viewBox="0 0 200 200">
                          <circle r="85" cx="100" cy="100"
                              fill="transparent"
                              stroke="#EEE"
                              stroke-width="30"
                          />
                           <text x="100" y="105" dominant-baseline="middle" text-anchor="middle" fill="#888" font-size="3em" font-weight="500">{{c.number}}</text>
                      </svg>
                  </div>
              </div>
              <div class="repeat-btn" v-if="c.type=='plan'&&c.number>0">
                  <button v-on:click="repeat(c.date)" class="btn btn-outline-success btn-sm yh-v-o-50">
                      <i class="fa fa-check"></i>
                      <span v-if="c.date==today">Powtarzaj</span>
                  </button>
              </div>
              <div v-if="c.date==today" class="d-xs-block d-sm-none  text-center add-title">
                  Kalendarz Powtórek
              </div>
          </div>
      </div>
      <div v-if="postpone" class="repcal-postpone">
          Masz dzisiaj kumulację powtórek z więcej niż jednego dnia, możesz przesunąć o jeden dzień.
          <button v-on:click="callPostpone()" class="btn btn-outline-primary btn-sm ml-2 yh-v-o-50">Przesuń</button>
      </div>
  </div>
</template>
<script>
const Component = {
  name: 'QuestionsRepeatsCalendar',
  data() {
    return {
      list: null,
      today: null,
      postpone: false,
    }
  },
  created() {
      this.fetch();
  },
  mounted() {
      SideBarCollapseIfActive(true);
  },
  methods: {
      fetch() {
          axios
              .post(
                  '/api/app/questions/repeats/calendar',
                  {
                      hash_id: yh.auth.hash_id,
                  }
              )
              .then(response => {
                  this.list = response.data.list;
                  this.today = response.data.today;
                  this.postpone = response.data.postpone;
              }).catch(error => {
                  this.loading = false;
                  this.error = error.response.data.message || error.message;
                  if (error.response.data.location) {
                      setTimeout(function(){document.location = error.response.data.location;},2000);
                  }
              });
      },
      callPostpone() {
          this.postpone = false;
          axios
              .post(
                  '/api/app/questions/repeats/postpone',
                  {
                      hash_id: yh.auth.hash_id,
                      days: 1,
                  }
              )
              .then(response => {
                  this.fetch();
                  $cookies.set('app-questions-repeats-refresh',1,'1d');
              }).catch(error => {
                  this.loading = false;
                  this.error = error.response.data.message || error.message;
                  if (error.response.data.location) {
                      setTimeout(function(){document.location = error.response.data.location;},2000);
                  }
              });
      },
      repeat(date) {
          axios
              .post(
                  '/api/app/questions/repeats/make',
                  {
                      hash_id: yh.auth.hash_id,
                      date: date,
                      name: 'Powtórka na dzień '+date,
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
export default Component
</script>
