import GamesEndedSummary from "./vue/app/GamesEndedSummary";

require('./bootstrap');
// import '@babel/polyfill';
// var ES6Promise = require("es6-promise");
// ES6Promise.polyfill();

window.moment = require('moment/locale/pl');

window.Chart = require('chart.js');
window.$ = window.jQuery = require('jquery');

require('./shared/forms');
require('./shared/init');
require('./shared/ping');
require('./shared/sidebar');
require('./shared/center');
require('./yh/yh');


// basics and dependencies
import Vue from 'vue';
import VueRouter from 'vue-router';
import {BootstrapVue, IconsPlugin} from 'bootstrap-vue';
import VueCookies from 'vue-cookies';
import VueConfirmDialog from 'vue-confirm-dialog';
import {DatetimePicker} from '@livelybone/vue-datepicker';
import VueSocketIO from 'vue-socket.io';
import SocketIO from "socket.io-client";
import PerfectScrollbar from 'vue2-perfect-scrollbar';
import 'vue2-perfect-scrollbar/dist/vue2-perfect-scrollbar.css';
import VTooltip from 'v-tooltip';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

// Global register

Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(VueCookies);
Vue.use(VueConfirmDialog);
Vue.component('datetime-picker', DatetimePicker);
Vue.use(PerfectScrollbar);
Vue.use(VTooltip);
Vue.use(Loading);

Vue.$cookies.config('30d');

Vue.component('vue-confirm-dialog', VueConfirmDialog.default);

//
// VUE DATE PICKER
//
// https://livelybone.github.io/vue/vue-datepicker/
//
//

// custom router templates
import App from './vue/app/App';
import Games from './vue/app/Games';
import GamesJoin from './vue/app/GamesJoin';
import GamesNew from './vue/app/GamesNew';
import GamesRunExam from './vue/app/GamesRunExam';
import GamesRunRace from './vue/app/GamesRunRace';
import GamesViewExam from './vue/app/GamesViewExam';
import GamesViewRace from './vue/app/GamesViewRace';
import QuestionsList from './vue/app/QuestionsList';
import QuestionsRepeats from './vue/app/QuestionsRepeats';
import Shop from './vue/app/Shop';
import ShopForm from './vue/app/ShopForm';
import ShopReturn from './vue/app/ShopReturn';
import Start from './vue/app/Start';
import Stats from './vue/app/Stats';
import TestsContinue from './vue/app/TestsContinue';
import TestsEnded from './vue/app/TestsEnded';
import TestsEndedSummary from './vue/app/TestsEndedSummary';
import TestsNew from './vue/app/TestsNew';
import TestsRun from './vue/app/TestsRun';
import User from './vue/app/User';
import LegalDetail from "./vue/app/LegalDetail";

// custom components
import QuestionsRepeatsCalendar from './vue/app/QuestionsRepeatsCalendar';

Vue.use(QuestionsRepeatsCalendar);
Vue.component('questions-repeats-calendar', QuestionsRepeatsCalendar);

import StatsSummary from './vue/app/StatsSummary';

Vue.use(StatsSummary);
Vue.component('stats-summary', StatsSummary);

// start
$(document).ready(function () {

    VueAppInitialize(function () {
        const router = new VueRouter({
            mode: 'history',
            routes: [
                {
                    path: '/app/games',
                    name: 'games',
                    component: Games
                },
                {
                    path: '/app/games/join/:hash_key',
                    name: 'games.join',
                    component: GamesJoin
                },
                {
                    path: '/app/games/new/:hash_key?',
                    name: 'games.new',
                    component: GamesNew
                },
                {
                    path: '/app/games/run/exam/:hash_key',
                    name: 'games.run.exam',
                    component: GamesRunExam
                },
                {
                    path: '/app/games/run/race/:hash_key/:id',
                    name: 'games.run.race',
                    component: GamesRunRace
                },
                {
                    path: '/app/games/view/exam/:hash_key',
                    name: 'games.view.exam',
                    component: GamesViewExam
                },
                {
                    path: '/app/games/view/race/:hash_key/:refresh?',
                    name: 'games.view.race',
                    component: GamesViewRace
                },
                {
                    path: '/app/questions/list',
                    name: 'questions.list',
                    component: QuestionsList
                },
                {
                    path: '/app/questions/repeats',
                    name: 'questions.repeats',
                    component: QuestionsRepeats
                },
                {
                    path: '/app/shop',
                    name: 'shop',
                    component: Shop
                },
                {
                    path: '/app/shop/form/:package_id/:months/',
                    name: 'shop.form',
                    component: ShopForm
                },
                {
                    path: '/app/shop/return/',
                    name: 'shop.return',
                    component: ShopReturn
                },
                {
                    path: '/app/start',
                    name: 'start',
                    component: Start
                },
                {
                    path: '/app/legal/:id',
                    name: 'legal.detail',
                    component: LegalDetail
                },
                {
                    path: '/app/stats',
                    name: 'stats',
                    component: Stats
                },
                {
                    path: '/app/tests/continue',
                    name: 'tests.continue',
                    component: TestsContinue
                },
                {
                    path: '/app/tests/ended',
                    name: 'tests.ended',
                    component: TestsEnded
                },
                {
                    path: '/app/tests/ended/summary/:id',
                    name: 'tests.ended.summary',
                    component: TestsEndedSummary
                },
                {
                    path: '/app/games/ended/summary/:id',
                    name: 'games.ended.summary',
                    component: GamesEndedSummary
                },
                {
                    path: '/app/tests/new',
                    name: 'tests.new',
                    component: TestsNew
                },
                {
                    path: '/app/tests/run/:id',
                    name: 'tests.run',
                    component: TestsRun
                },
                {
                    path: '/app/user',
                    name: 'user',
                    component: User
                },
            ],
        });

        // SocketIO('http://metinseylan.com:1992', options)
        const options = {
            query: 'user=' + yh.auth.user_name//+'&userid='+yh.auth.user_id
        }
        Vue.use(new VueSocketIO({
            debug: true,
            connection: SocketIO('https://testy.iusvitae.pl:3000', options), // ,
            /*vuex: {
                store,
                actionPrefix: 'SOCKET_',
                mutationPrefix: 'SOCKET_'
            },*/
            options: {path: ""} //Optional options
        }))

        const app = new Vue({
            el: '#app',
            components: {App},
            router,
            mounted() {
                EnableSideBarCollapse();
                EnableSideBarRightCollapse();
                if (yh.auth.user_type !== 'user') {
                    EnableSidebarAdminButton();
                }
                $('#app-loader').fadeOut(1000);
            }
        });
    });
});




