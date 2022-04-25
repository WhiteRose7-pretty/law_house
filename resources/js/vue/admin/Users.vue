<template>
<div class="ius">
    <h2 class="mb-3">
        Użytkownicy
    </h2>

    <div class="card">
        <div class="card-body">
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
                        <th scope="col">Email</th>
                        <th scope="col">Imię</th>
                        <th scope="col">Nazwisko</th>
                        <th scope="col">Typ</th>
                        <th scope="col">Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="{id,email,email_verified_at,name,surname,type} in users" v-bind:data-id="id" class="user-row">
                        <th scope="row">{{id}}</th>
                        <td>{{email}} <i v-if="email_verified_at" class="fas fa-check-circle"></i></td>
                        <td><u v-on:click="openDetail(id, email, email_verified_at, name, surname, type)">{{name}}</u></td>
                        <td>{{surname}}</td>
                        <td>
                            <select v-bind:data-id="id" v-on:change="typeChange(id)" class="user-type">
                              <option v-for="t in types" v-bind:value="t.v" :selected="t.v == type">
                                {{ t.l }}
                              </option>
                            </select>
                        </td>
                        <td class="text-nowrap">
                            <button v-if="email_verified_at" class="d-none"></button>
                            <button v-else v-on:click="remove(id)" class="btn btn-sm btn-danger">Usuń</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-else class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Email</th>
                        <th scope="col">Imię</th>
                        <th scope="col">Nazwisko</th>
                        <th scope="col">Typ</th>
                        <th scope="col">Akcja</th>
                    </tr>
                </thead>
                <tbody>
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
import UserDetailView from "./UserDetailView";
import User from "../app/User";
export default {
    data() {
        return {
            loading: false,
            users: null,
            query: '',
            limit: 5,
            count: 0,
            pages: 0,
            page: 1,
            error: null,
            types: [
              { v: 'user', l: "Użytkownik" },
              { v: 'editor', l: "Redaktor" },
              { v: 'admin', l: "Admin" },
              { v: 'subadmin', l: "Subadmin"}
            ],
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
        openDetail(id, email, email_verified_at, name, surname, type){
            this.$modal.show(
                UserDetailView,
                {id: id, email:email, email_verified_at:email_verified_at, name:name, surname:surname, type:type},
                {
                    height: "100%",
                    minHeight: 900,
                    scrollable: true,
                    width: '90%',
                }
            )
        },
        fetchQuery() {
            this.query = $('#fetch-query').val();
            this.fetch();
        },
        fetchLimit() {
            this.limit = $('#fetch-limit').val();
            this.fetch();
        },
        fetchPager() {
            this.page = $('#fetch-pager').val();
            this.fetch();
        },
        fetch() {
            this.error = this.users = null;
            this.loading = true;
            this.pages = 0;
            axios
                .post(
                    '/api/admin/users',
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
                    this.users = response.data.results;
                    this.count = response.data.count;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        remove(id) {
            if (confirm('Czy na pewno chcesz usunąć użytkownika?')) {
                axios
                    .post(
                        '/api/admin/users/remove',
                        {
                            hash_id: yh.auth.hash_id,
                            id: id
                        }
                    )
                    .then(response => {
                        $('.user-row[data-id="'+id+'"]').remove();
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
        typeChange(id) {
          if (confirm('Czy na pewno chcesz zmienić typ użytkownika?')) {
              axios
                  .post(
                      '/api/admin/users/type',
                      {
                          hash_id: yh.auth.hash_id,
                          id: id,
                          type: $('.user-type[data-id="'+id+'"]').val()
                      }
                  )
                  .then(response => {
                      alert('zmieniono typ użytkownika');
                  });
          }
        },
    },
}
</script>
