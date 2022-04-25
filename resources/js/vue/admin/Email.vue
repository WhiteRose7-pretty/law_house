<template>
    <div class="ius">
        <h2 class="mb-3">
            Emails
        </h2>

        <b-row class="mb-3">
            <b-col md="3">
                <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Type to Search"></b-form-input>
            </b-col>
        </b-row>
        <div class="card">
            <div class="card-body p-0">

                <div v-if="loading" class="text-info">
                    Ładowanie...
                </div>

                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>

                <b-table class="mb-0" id="my-table" striped hover outlined :items="list" :fields="fields" :filter="filter" :per-page="perPage" :current-page="currentPage">
                    <template v-slot:cell(Date)="data">
                        {{dateF(data.item.created_at)}}
                    </template>
                </b-table>
            </div>
        </div>
        <b-pagination class="float-right"
                      v-model="currentPage"
                      :total-rows="rows"
                      :per-page="perPage"
                      aria-controls="my-table"
        ></b-pagination>

    </div>
</template>
<script>
import axios from 'axios';

export default {
    data() {
        return {
            test: null,
            loading: false,
            counting: false,
            error: null,
            counting_error: null,
            list: [],
            picker_fix: false,
            yh: yh,
            regulations: [],
            saving: false,
            questions_error: '',
            fields: ["Date", "receiver", "subject", "content", "type"],
            filter: null,
            per_page: 10,
            current_page: 1,
        };
    },
    created() {
        this.fetch();
    },
    mounted() {
    },
    updated() {
        mdc.autoInit();
    },
    methods: {
        dateF(s) {
            return moment(s).local().format('YYYY-MM-DD');
        },
        fetch() {
            this.error = null;
            this.list = [];
            this.loading = true;
            axios
                .post(
                    '/api/admin/emails',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.list = response.data.results;
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });

            axios
                .post(
                    '/api/admin/packages',
                    {
                        hash_id: yh.auth.hash_id,
                        with_sets: false,
                    }
                )
                .then(response => {
                    this.packages = response.data.results;
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
        isNumeric(str) {
            if (typeof str != "string") return false // we only process strings!
            return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
                !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
        },

        message(title, msg, callback = null, no=false) {
            setTimeout(function(){
                $('.vc-container').center();
            },50);
            var _this = this;
            if (no) {
                this.$confirm(
                    {
                        title: title,
                        message: msg,
                        button: {
                            yes: 'Tak',
                            no: 'Nie'
                        },
                        /**
                         * Callback Function
                         * @param {Boolean} confirm
                         */
                        callback: confirm => {
                            if (confirm) {
                                if (typeof callback == 'function') {
                                    callback();
                                }
                            }
                        }
                    }
                );
            } else {
                this.$confirm(
                    {
                        title: title,
                        message: msg,
                        button: {
                            yes: 'OK'
                        },
                        /**
                         * Callback Function
                         * @param {Boolean} confirm
                         */
                        callback: confirm => {
                            if (confirm) {
                                if (typeof callback == 'function') {
                                    callback();
                                }
                            }
                        }
                    }
                );
            }
        },


    },
}
</script>
