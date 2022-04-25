<template>
    <div>
        <div v-if="data">
            <div class="statistics-summary">
                  <div class="row">
                      <div class="pie-chart col-12 col-sm-6 col-md-3 text-center p-3 pl-5 pr-5 p-md-1">
                          <svg class="my-pie" viewBox="0 0 200 200">
                              <circle r="85" cx="100" cy="100"
                                  fill="transparent"
                                  stroke="#ccc"
                                  stroke-width="30"
                              />
                              <circle r="85" cx="100" cy="100" fill="transparent"
                                  stroke="#e5e998"
                                  stroke-width="30"
                                  v-bind:stroke-dasharray="`calc(${data.correct_0}/${data.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                               />
                               <g v-bind:transform="'rotate('+count_radius(data,0)+' 100 100)'">
                                   <circle r="85" cx="100" cy="100" fill="transparent"
                                       stroke="#deea31"
                                       stroke-width="30"
                                       v-bind:stroke-dasharray="`calc(${data.correct_1}/${data.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                    />
                               </g>
                               <g v-bind:transform="'rotate('+count_radius(data,1)+' 100 100)'">
                                   <circle r="85" cx="100" cy="100" fill="transparent"
                                       stroke="#b0dc33"
                                       stroke-width="30"
                                       v-bind:stroke-dasharray="`calc(${data.correct_2}/${data.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                    />
                               </g>
                               <g v-bind:transform="'rotate('+count_radius(data,2)+' 100 100)'">
                                   <circle r="85" cx="100" cy="100" fill="transparent"
                                       stroke="#7ccc30"
                                       stroke-width="30"
                                       v-bind:stroke-dasharray="`calc(${data.correct_3}/${data.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                    />
                               </g>
                               <g v-bind:transform="'rotate('+count_radius(data,3)+' 100 100)'">
                                   <circle r="85" cx="100" cy="100" fill="transparent"
                                       stroke="#35ae3f"
                                       stroke-width="30"
                                       v-bind:stroke-dasharray="`calc(${data.correct_4}/${data.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                    />
                               </g>
                               <g v-bind:transform="'rotate('+count_radius(data,4)+' 100 100)'">
                                   <circle r="85" cx="100" cy="100" fill="transparent"
                                       stroke="#236930"
                                       stroke-width="30"
                                       v-bind:stroke-dasharray="`calc(${data.correct_5}/${data.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                    />
                               </g>
                               <g v-bind:transform="'rotate('+count_radius(data,5)+' 100 100)'">
                                   <circle r="85" cx="100" cy="100" fill="transparent"
                                       stroke="#7c4787"
                                       stroke-width="30"
                                       v-bind:stroke-dasharray="`calc(${data.correct_m}/${data.q_total} * 2*22/7*85) calc(2*22/7*85)`"
                                    />
                               </g>
                               <text x="100" y="80" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="2.5em" font-weight="500">{{Math.round((parseInt(data.correct_total)+parseInt(data.correct_0)))}}</text>

                               <line x1="40" y1="100" x2="160" y2="100" style="stroke:#aaa;stroke-width:1" />

                               <text x="100" y="130" dominant-baseline="middle" text-anchor="middle" fill="#666" font-size="2.5em" font-weight="500">{{data.q_total}}</text>
                          </svg>
                      </div>
                      <div class="col-12 col-sm-6 col-md-4 pt-4 pt-md-0">
                          <div class="line-chart-list ml-md-5 mr-md-3">
                              <div class="line-chart cursor" v-tooltip="'Pytania na które nie udzieliłeś poprawnej odpowiedzi.'" >
                                  <div class="line-chart-val" v-bind:style="'width:'+Math.round(data.correct_0/data.q_total*100)+'%; background-color:#e5e998;'"></div>
                                  <span>poziom 0</span>
                              </div>
                              <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś ostatnio poprawnej odpowiedzi.'">
                                  <div class="line-chart-val" v-bind:style="'width:'+Math.round(data.correct_1/data.q_total*100)+'%; background-color:#deea31;'"></div>
                                  <span>poziom 1</span>
                              </div>
                              <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 2 razy z rzędu.'">
                                  <div class="line-chart-val" v-bind:style="'width:'+Math.round(data.correct_2/data.q_total*100)+'%; background-color:#b0dc33;'"></div>
                                  <span>poziom 2</span>
                              </div>
                              <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 3 razy z rzędu.'">
                                  <div class="line-chart-val" v-bind:style="'width:'+Math.round(data.correct_3/data.q_total*100)+'%; background-color:#7ccc30;'"></div>
                                  <span>poziom 3</span>
                              </div>
                              <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 4 razy z rzędu.'">
                                  <div class="line-chart-val" v-bind:style="'width:'+Math.round(data.correct_4/data.q_total*100)+'%; background-color:#35ae3f;'"></div>
                                  <span>poziom 4</span>
                              </div>
                              <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi 5 razy z rzędu.'">
                                  <div class="line-chart-val" v-bind:style="'width:'+Math.round(data.correct_5/data.q_total*100)+'%; background-color:#236930;'"></div>
                                  <span>poziom 5</span>
                              </div>
                              <div class="line-chart" v-tooltip="'Pytania na które udzieliłeś poprawnej odpowiedzi więcej niż 5 razy z rzędu.'">
                                  <div class="line-chart-val" v-bind:style="'width:'+Math.round(data.correct_m/data.q_total*100)+'%; background-color:#7c4787;'"></div>
                                  <span>poziom +</span>
                              </div>
                          </div>
                      </div>
                      <div class="col-12 col-md-5 pt-4 pt-md-2 pl-md-5">
                          <h2 class="yh-fw-5 mb-4">
                              {{Math.round((parseInt(data.correct_total)+parseInt(data.correct_0)))}} &#8212; rozwiązywane pytania spośród wszystkich dostępnych pytań <br><br>
                              {{evaluate(data)}} &#8212; poprawnych odpowiedzi <br><br>
                              {{data.q_total}} &#8212; wszystkie dostępne pytania
                          </h2>
                      </div>
                  </div>
            </div>
        </div>
    </div>
</template>
<script>
const Component = {
  name: 'StatsSummary',
  props: ['stats'],
  computed: {
      data() {
          return this.stats;
      }
  },
  methods: {
      evaluate(data) {
          if (parseInt(data.correct_total)+parseInt(data.correct_0)) {
              return Math.round(data.correct_total);
          }
          return 0;
      },
      count_radius(m,i) {
          var x=parseFloat(m.correct_0);
          x = i>0 ? x+parseFloat(m.correct_1) : x;
          x = i>1 ? x+parseFloat(m.correct_2) : x;
          x = i>2 ? x+parseFloat(m.correct_3) : x;
          x = i>3 ? x+parseFloat(m.correct_4) : x;
          x = i>4 ? x+parseFloat(m.correct_5) : x;
          return x/m.q_total*360;
      },
  }
}
export default Component
</script>
