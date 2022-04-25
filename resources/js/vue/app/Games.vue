<template>
    <div>
        <h1>Zagraj z innymi</h1>

        <select class="games-list-select" v-model="filter" v-on:change="fetch()">
            <option value="active">aktywne</option>
            <option value="finished">zakończone</option>
        </select>

        <router-link class="btn btn-primary text-white float-right" :to="{ name: 'games.new' }">
            Załóż nową grę
        </router-link>

        <div class="games-list mt-4" v-if="list && list.length">
            <div class="row m-2 row-head d-md-flex d-none">
                <div class="col">
                    Data Utworzenia
                </div>
                <div class="col">
                    Nazwa
                </div>
                <div class="col">
                    Typ
                </div>
                <div class="col">
                    Gracze
                </div>
                <div class="col">
                    Akcje
                </div>
            </div>
            <div class="m-2 row" v-for="game in list">
                <div class="col-12 col-md">
                    {{dateF(game.created_at)}}
                </div>
                <div class="col-12 col-md">
                    {{game.name}}
                </div>
                <div class="col-12 col-md">
                    <span v-if="game.type == 'exam'">
                        egzamin
                    </span>
                    <span v-else>
                        wyścig
                    </span>
                </div>
                <div class="col-12 col-md">
                    <i class="fas fa-crown text-warning" v-if="game.user_id == user_id" title="Twoja Gra"></i>
                    {{game.participants_count}}
                    <i class="fas fa-user-friends d-md-none"></i>
                </div>
                <div class="col-12 col-md">
                  <router-link v-if="game.type == 'exam'" class="btn btn-sm btn-primary text-white" :to="{ name: 'games.view.exam', params: { hash_key: game.hash_key } }">
                      Zobacz
                  </router-link>
                  <router-link v-if="game.type == 'race'" class="btn btn-sm btn-primary text-white" :to="{ name: 'games.view.race', params: { hash_key: game.hash_key } }">
                      Zobacz
                  </router-link>
                </div>
            </div>
        </div>
        <div class="games-list" v-else>
            Brak gier na liście
            <span v-if="filter=='active'">aktywnych</span>
            <span v-else>zakończonych</span>
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
            filter: 'active',
            user_id: null,
        };
    },
    created() {
        this.user_id = yh.auth.user_id;
        this.fetch();
    },
    destroyed() {
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
    },
    methods: {
        dateF(s) {
            var r = s.split('T');
            var s2 = r[0];
            s2 = this.replaceAll(s2,'-','/');
            return s2;
        },
        fetch() {
            this.error = this.list = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/games/list',
                    {
                        hash_id: yh.auth.hash_id,
                        filter: this.filter,
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
        replaceAll(string, search, replace) {
            return string.split(search).join(replace);
        },
    }
}
</script>
