<template>
<div class="ius">
    <h2 class="mb-3">
        Pytania
    </h2>

    <div class="card">
        <div class="card-body">

            <div class="p-3 mb-4 text-right" style="background-color: #f1f1f1;">
                <router-link :to="{ name: 'questions.edit', params: { id: 0} }" class="btn btn-success btn-sm text-white">Utwórz nowy zestaw</router-link>
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
                        <option value="50" :selected="50 == limit">50</option>
                        <option value="100" :selected="100 == limit">100</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="text-info">
                Ładowanie...
            </div>

            <div v-if="error" class="text-danger">
                {{ error }}
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nazwa</th>
                        <th scope="col">Grupa</th>
                        <th scope="col">Ost. Aktualizacja</th>
                        <th scope="col">Ilość pytań</th>
                        <th scope="col">Akcje</th>
                        <th scope="col">System powtórzeń</th>
                    </tr>
                </thead>
                <tbody v-if="pages > 0">
                    <tr v-for="{id,updated_at,name,group,questions_count, after_date,before_date, old_status, repeatable} in list" v-bind:data-id="id" class="questions-set-row">
                        <th scope="row">{{id}}</th>
                        <td>{{name}}
                            <i class="fas fa-exclamation text-danger" v-if="old_status" aria-hidden="true"></i>
                            <i class="fa fa-clock-o text-danger" v-if="after_date" aria-hidden="true"></i>
                            <i class="fa fa-clock-o text-success" v-if="before_date" aria-hidden="true"></i>
                        </td>
                        <td>{{group}}</td>
                        <td>{{dateF(updated_at)}}</td>
                        <td>{{questions_count}}</td>
                        <td class="text-nowrap">
                            <button v-on:click="edit(id)" class="btn btn-sm btn-success">Edytuj</button>
                            <button v-on:click="remove(id)" class="btn btn-sm btn-danger">Usuń</button>

                        </td>

                        <td class="text-nowrap">
                            <button v-on:click="repeat_enable(id, false)" v-if="repeatable" class="btn btn-sm btn-danger">Wykluczać</button>
                            <button v-on:click="repeat_enable(id, true)" v-if="!repeatable" class="btn btn-sm btn-success">Zawierać</button>
                        </td>

                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="6">brak pasujących wyników</td>
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
            list: null,
            count: 0,
            query: '',
            limit: 50,
            pages: 0,
            page: 1,
        };
    },
    created() {
        this.fetch();
    },
    mounted() {
        var _this = this;
        $('#fetch-query').keyup(function(e){
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
          this.$router.push({ name: 'questions.edit', params: { id: id} });
        },
        fetch() {
          this.error = this.list = null;
          this.loading = true;
          this.pages = 0;
          axios
              .post(
                  '/api/admin/questions/sets/list',
                  {
                      hash_id: yh.auth.hash_id,
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
                      setTimeout(function(){document.location = error.response.data.location;},2000);
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
            if (confirm('Czy na pewno chcesz usunąć ten zestaw?')) {
                axios
                    .post(
                        '/api/admin/questions/sets/remove',
                        {
                            hash_id: yh.auth.hash_id,
                            id: id
                        }
                    )
                    .then(response => {
                        $('.questions-set-row[data-id="'+id+'"]').remove();
                        var _this = this;
                        setTimeout(function(){
                            _this.fetch();
                        },500);
                    }).catch(error => {
                        this.loading = false;
                        this.error = error.response.data.message || error.message;
                        if (error.response.data.location) {
                            setTimeout(function(){document.location = error.response.data.location;},2000);
                        }
                    });
            }
        },
        repeat_enable(id, repeatable){
            let text = ((repeatable) ? 'include' : 'exclude');
            if (confirm('Are you '+ text +' this set of questions to repetition system?')) {
                axios
                    .post(
                        '/api/admin/questions/sets/repeat-enable',
                        {
                            hash_id: yh.auth.hash_id,
                            id: id,
                            repeatable: repeatable,
                        }
                    )
                    .then(response => {
                        console.log(response);
                        // $('.questions-set-row[data-id="'+id+'"]').remove();
                        var _this = this;
                        setTimeout(function(){
                            _this.fetch();
                        },500);
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            }
        },

    },
}
</script>
