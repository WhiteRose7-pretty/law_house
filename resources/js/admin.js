import {BootstrapVue, IconsPlugin, TablePlugin, PaginationPlugin } from "bootstrap-vue";

require('./bootstrap');
// import '@babel/polyfill';
// var ES6Promise = require("es6-promise");
// ES6Promise.polyfill();

window.$ = window.jQuery = require('jquery');

require('./shared/forms');
require('./shared/init');
require('./shared/ping');
require('./shared/sidebar');
require('./yh/yh');

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import App from './vue/admin/App';
import Content from './vue/admin/Content';
import ContentEdit from './vue/admin/ContentEdit';
import Documents from './vue/admin/Documents';
import DocumentsEdit from './vue/admin/DocumentsEdit';
import Money from './vue/admin/Money';
import Money1 from './vue/admin/Money1';
import Packages from './vue/admin/Packages';
import Questions from './vue/admin/Questions';
import QuestionsEdit from './vue/admin/QuestionsEdit';
import Users from './vue/admin/Users';
import Tests from './vue/admin/Tests';
import Email from "./vue/admin/Email";
import Start from "./vue/admin/Start";
import PerfectScrollbar from 'vue2-perfect-scrollbar'
import 'vue2-perfect-scrollbar/dist/vue2-perfect-scrollbar.css'
import { DatetimePicker } from '@livelybone/vue-datepicker';
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'


Vue.use(PerfectScrollbar)
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(TablePlugin);
Vue.use(PaginationPlugin)

Vue.component('datetime-picker', DatetimePicker);

const scroll_object = document.getElementById('content');

$(document).ready(function() {

    VueAppInitialize(function(){
        const router = new VueRouter({
            mode: 'history',
            routes: [
                {
                    path: '/admin/content',
                    name: 'content',
                    component: Content
                },
                {
                    path: '/admin/content/edit/:category/:id',
                    name: 'content.edit',
                    component: ContentEdit
                },
                {
                    path: '/admin/documents/limit/:limit/page/:page',
                    name: 'documents',
                    component: Documents
                },
                {
                    path: '/admin/documents/edit/:id',
                    name: 'documents.edit',
                    component: DocumentsEdit
                },
                {
                    path: '/admin/money',
                    name: 'money',
                    component: Money
                },
                {
                    path: '/admin/money1',
                    name: 'money1',
                    component: Money1
                },
                {
                    path: '/admin/packages',
                    name: 'packages',
                    component: Packages
                },
                {
                    path: '/admin/questions',
                    name: 'questions',
                    component: Questions
                },
                {
                    path: '/admin/questions/edit/:id',
                    name: 'questions.edit',
                    component: QuestionsEdit
                },
                {
                    path: '/admin/start',
                    name: 'start',
                    component: Start
                },
                {
                    path: '/admin/users',
                    name: 'users',
                    component: Users
                },
                {
                    path: '/admin/tests',
                    name: 'tests',
                    component: Tests
                },
                {
                    path: '/admin/emails',
                    name: 'emails',
                    component: Email,
                },
            ],
        });

        const app = new Vue({
            el: '#app',
            components: { App },
            router,
            mounted() {
              EnableSideBarCollapse();
              EnableSideBarAppButton();
              $('#app-loader').fadeOut(1000);
            }
        });
    },true);
});
