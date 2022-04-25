<template>
    <div class="ius">

        <div class="mb-3 clearfix">
            <input id="fetch-query" placeholder="Szukaj" class="form-control" v-bind:value="query" autofocus style="padding: 30px 24px; font-size: 18px; border-radius: 50px; border: none;
box-shadow: 0 0 2px 0px rgb(0 0 0 / 4%);"/>
        </div>

        <div class="">
            <div class="p-0">
                <div v-if="loading" class="text-info">
                    Ładowanie...
                </div>

                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>
                <ul class="list-group list-group-flush">
                    <a v-for="{id,name,info, audio} in list" v-bind:data-id="id" class="document-row list-group-item list-group-item-action mb-2" :title="info" v-on:click="edit(id)">
                        <div><i v-if="audio" class="fa fa-microphone mr-2 text-warning"></i>{{name}}</div>
                    </a>
                </ul>

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
                limit: 200,
                pages: 0,
                page: 1,
            };
        },
        created() {
            // this.limit = this.$route.params.limit;
            // this.page = this.$route.params.page;
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
                this.$router.push({ name: 'legal.detail', params: { id: id} });
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
                        // window.history.pushState("","",'/admin/documents/limit/'+this.limit+'/page/'+this.page);

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
