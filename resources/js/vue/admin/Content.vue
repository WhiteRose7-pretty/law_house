<template>
    <div class="ius">
        <h2 class="mb-3">
            Treści
        </h2>

        <div class="card">
            <div class="card-body">

                <div class="p-3 mb-4" style="background-color: #f1f1f1;">
                    Kategoria:
                    <select id="category" v-on:change="fetchCategory()" v-model="current_category">
                        <option v-for="o in categories" v-bind:value="o.v" :selected="o.v == current_category">
                            {{ o.l }}
                        </option>
                    </select>
                    <router-link v-if="current_category!='info'"
                                 :to="{ name: 'content.edit', params: { category: current_category, id: 0} }"
                                 class="btn btn-success btn-sm text-white float-right">Utwórz nowy
                    </router-link>
                </div>

                <div class="mb-1 clearfix">
                    <input id="fetch-query" placeholder="Szukaj" class="pl-1" v-bind:value="query" autofocus/>
                    <div class="float-right">
                        Limit:
                        <select id="fetch-limit" v-on:change="fetchLimit()">
                            <option value="5" :selected="5 == limit">5</option>
                            <option value="10" :selected="10 == limit">10</option>
                            <option value="15" :selected="15 == limit">15</option>
                            <option value="20" :selected="20 == limit">20</option>
                        </select>
                    </div>
                </div>

                <div v-if="loading" class="text-info">
                    Ładowanie...
                </div>

                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>

                <table v-if="pages > 0" class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Tytuł</th>
                        <th scope="col">Aktualizacja</th>
                        <th scope="col">Publikacja</th>
                        <th scope="col">Akcja</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="{id,title,created_at,updated_at,published_at} in list" v-bind:data-id="id"
                        class="content-row">
                        <th scope="row">{{id}}</th>
                        <td>{{title}}</td>
                        <td><span v-if="updated_at">{{dateF(updated_at)}}</span><span
                            v-else>{{dateF(created_at)}}</span></td>
                        <td><span v-if="published_at">{{dateF(published_at)}}</span></td>
                        <td class="text-nowrap">
                            <button v-on:click="edit(id)" class="btn btn-sm btn-success">Edytuj</button>
                            <!--                            <button v-if="published_at || current_category == 'info'" class="d-none"></button>-->
                            <button v-on:click="remove(id)" class="btn btn-sm btn-danger">Usuń</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table v-else class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Tytuł</th>
                        <th scope="col">Aktualizacja</th>
                        <th scope="col">Publikacja</th>
                        <th scope="col">Akcja</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="5">brak pasujących wyników</td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="pages > 1" class="float-left">
                    Strona:
                    <select id="fetch-pager" v-on:change="fetchPager()">
                        <option v-for="p in pages" :value="p" :selected="p == page">{{p}}</option>
                    </select>
                    / {{pages}}
                </div>
                <div v-else-if="pages > 0" class="float-left">
                    Strona: 1/1
                </div>
                <div v-if="count > 0" class="float-right">
                    Wszystkich: {{count}}
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

                categories: [
                    {v: 'news', l: "Aktualności"},
                    {v: 'promo', l: "Promocyjne1"},
                    {v: 'promo2', l: "Promocyjne2"},
                    {v: 'info', l: "Stałe"},
                    {v: 'regula', l: "Przepisy prawne"},
                ],

                current_category: 'news',
                list: null,
                count: 0,
                query: '',
                limit: 20,
                pages: 0,
                page: 1,
            };
        },
        created() {
            this.fetch();
        },
        mounted() {
            var _this = this;
            $('#fetch-query').keyup(function (e) {
                var code = e.key;
                if (code == 'Enter') {
                    e.preventDefault();
                    _this.fetchQuery();
                }
            });
            $('#fetch-query').focus();
        },
        updated() {
        },
        methods: {
            dateF(s) {
                return moment(s).local().format('YYYY-MM-DD HH:mm:ss');
            },
            edit(id) {
                this.$router.push({name: 'content.edit', params: {category: this.current_category, id: id}});
            },
            fetch() {
                this.error = this.list = null;
                this.loading = true;
                this.pages = 0;
                axios
                    .post(
                        '/api/admin/content/list',
                        {
                            hash_id: yh.auth.hash_id,
                            category: this.current_category,
                            limit: this.limit,
                            query: this.query,
                            page: this.page,
                        }
                    )
                    .then(response => {
                        this.loading = false;
                        this.pages = response.data.pages;
                        this.page = response.data.page;
                        this.list = response.data.results;
                        this.count = response.data.count;
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
            fetchCategory() {
                this.page = 1;
                this.query = '';
                this.fetch();
            },
            fetchLimit() {
                this.limit = $('#fetch-limit').val();
                this.page = 1;
                this.fetch();
            },
            fetchPager() {
                this.page = $('#fetch-pager').val();
                this.fetch();
            },
            fetchQuery() {
                this.query = $('#fetch-query').val();
                this.page = 1;
                this.fetch();
            },
            remove(id) {
                if (confirm('Czy na pewno chcesz usunąć tą treść?')) {
                    axios
                        .post(
                            '/api/admin/content/remove',
                            {
                                hash_id: yh.auth.hash_id,
                                id: id
                            }
                        )
                        .then(response => {
                            $('.content-row[data-id="' + id + '"]').remove();
                            var _this = this;
                            setTimeout(function () {
                                _this.fetch();
                            }, 500);
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
            },

        },
    }
</script>
