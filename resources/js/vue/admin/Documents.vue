<template>
<div class="ius">
    <h2 class="mb-3">
        Dokumenty - podstawa prawna
    </h2>

    <div class="card">
        <div class="card-body">

            <div class="p-3 mb-4 text-right" style="background-color: #f1f1f1;">
                <router-link :to="{ name: 'documents.edit', params: { id: 0} }" class="btn btn-success btn-sm text-white">Utwórz nowy</router-link>
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

            <table v-if="pages > 0" class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nazwa</th>
                        <th scope="col">Identyfikator</th>
                        <th scope="col">Data</th>
                        <th scope="col">Informacje</th>
                        <th scope="col">Aktualizacja</th>
                        <th scope="col">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="{id,updated_at,name,identifier,signed_at,info} in list" v-bind:data-id="id" class="document-row">
                        <th scope="row">{{id}}</th>
                        <td>{{name}}</td>
                        <td>{{identifier}}</td>
                        <td class="text-nowrap">{{signed_at}}</td>
                        <td>{{info}}</td>
                        <td>{{dateF(updated_at)}}</td>
                        <td class="text-nowrap">
                            <button v-on:click="edit(id)" class="btn btn-sm btn-success">Edytuj</button>
                            <button v-on:click="remove(id)" class="btn btn-sm btn-danger">Usuń</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-else class="table">
                <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Nazwa</th>
                      <th scope="col">Identyfikator</th>
                      <th scope="col">Data</th>
                      <th scope="col">Informacje</th>
                      <th scope="col">Aktualizacja</th>
                      <th scope="col">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">brak pasujących wyników</td>
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
            limit: 5,
            pages: 0,
            page: 1,
        };
    },
    created() {
        this.limit = this.$route.params.limit;
        this.page = this.$route.params.page;
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
          this.$router.push({ name: 'documents.edit', params: { id: id} });
        },
        fetch() {
          this.error = this.list = null;
          this.loading = true;
          this.pages = 0;
          axios
              .post(
                  '/api/admin/documents/list',
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
                  window.history.pushState("","",'/admin/documents/limit/'+this.limit+'/page/'+this.page);

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
            if (confirm('Czy na pewno chcesz usunąć ten document?')) {
                axios
                    .post(
                        '/api/admin/document/remove',
                        {
                            hash_id: yh.auth.hash_id,
                            id: id
                        }
                    )
                    .then(response => {
                        $('.document-row[data-id="'+id+'"]').remove();
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
