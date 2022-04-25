<template>
    <div class="ius">
        <h2 class="mb-3">
            Test
        </h2>

        <div class="card">
            <div class="card-body">
                <div class="p-3 mb-4 text-right" style="background-color: #f1f1f1;">
                    <a v-on:click="add()" class="btn btn-success btn-sm text-white">Utwórz nowy test</a>
                    <a v-on:click="update_games()" class="btn btn-success btn-sm text-white d-none">Update game name</a>
                </div>

                <div v-if="loading" class="text-info">
                    Ładowanie...
                </div>

                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>

                <table v-if="list.length > 0" class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nazwa</th>
                        <th scope="col">Pacakge Name</th>
                        <th scope="col">Ost. Aktualizacja</th>
                        <th scope="col">Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in list" v-bind:data-id="item.id"
                        class="packages-set-row">
                        <th scope="row">{{ item.id }}</th>
                        <td>{{ item.name }}</td>
                        <td>{{ item.package.name }}</td>
                        <td>{{ dateF(item.updated_at) }}</td>

                        <td class="text-nowrap">
                            <button v-on:click="edit(item)" class="btn btn-sm btn-success">
                                Edit
                            </button>
                            <button v-on:click="remove(item.id)" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table v-else class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nazwa</th>
                        <th scope="col">Ost. Aktualizacja</th>
                        <th scope="col">Valid until</th>
                        <th scope="col">Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="4">brak pasujących wyników</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="test" class="card mt-2">
            <div class="card-body">
                <h2 class="pt-2" v-if="test.id>0">Edycja Test</h2>
                <h2 class="pt-2" v-else>Dodawanie nowego test</h2>
                <h5 class="py-2">Package:</h5>
                <div v-if="packages">
                    <select v-model="test.package_id" class="form-control form-control-sm">
                        <option v-for="r in packages" v-bind:value="r.id">{{ r.name }}</option>
                    </select>
                </div>
                <div class="form-group mt-4">
                    <label for="name">Test name</label>
                    <input type="text" class="form-control" id="name"
                              v-model="test.name" placeholder="Wprowadź nazwę pakietu"/>
                </div>
                <div class="form-group mt-4">
                    <label for="question-numbers">Input question numbers</label>
                    <textarea type="text" class="form-control" id="question-numbers" rows="6"
                              v-model="test.questions" placeholder="Wprowadź nazwę pakietu"/>
                </div>

                <div class="mt-4">
                    <button v-on:click="abort()" class="btn btn-warning">Anuluj</button>
                    <button v-on:click="save()" class="btn btn-success">Zapisz</button>
                </div>

                <div v-if="questions_error" class="text-danger mt-2">
                    {{ questions_error }}
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
            test: null,
            loading: false,
            counting: false,
            error: null,
            counting_error: null,
            list: [],
            sets: [],
            packages: [],
            package: null,
            package_id: null,
            package_name: null,
            test_clean: {
                id: 0,
                name: '',
                questions: '',
                package_id: 0,
            },
            game: null,
            settings: {
                total: 0,
                total_new: 0,
                total_known: 0,
                selected: 1,
                limit: 50,
                time: 20,
                type: '',
                name: '',
                starts_at: '',
                auto_start: false,
                custom_name: false,
                custom_time: false,
                out_date: false,
                law_documents_id: 0,
                law_documents_elements_id: 0,
                set_date: null,
                regula: false,
                regula_id: 0,
                selected_questions: '',
            },
            picker_fix: false,
            yh: yh,
            regulations: [],
            saving: false,
            questions_error: '',
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
        abort() {
            this.test = null;
        },
        add() {
            this.test = JSON.parse(JSON.stringify(this.test_clean));
        },
        update_games(){
            axios
                .post(
                    '/api/admin/games/update-name',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {

                }).catch(error => {

            });
        },
        fetch() {
            this.error = null;
            this.list = [];
            this.loading = true;
            axios
                .post(
                    '/api/admin/tests',
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
        edit(item) {
            this.test = item;
            console.log(this.test);
        },
        save() {
            let questions_array = this.test.questions.split(',');
            this.questions_error = ''
            questions_array.forEach((item, index, arr) => {
                if (this.isNumeric(item)) {
                    arr[index] = parseInt(item)
                } else {
                    this.questions_error = `(${item}) is not validate number`
                }
            })

            if(this.questions_error){
                return false;
            }

            this.test.counts_of_questions = questions_array.length;
            this.error = null;
            this.loading = true;
            this.saving = true;

            axios
                .post(
                    '/api/admin/tests/update',
                    {
                        hash_id: yh.auth.hash_id,
                        test: this.test,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.test = null;
                    this.fetch();
                }).catch(error => {
                this.loading = false;
                this.saving = false;
                this.error = error.response.data.message || error.message;
                this.message('Wystąpił błąd',this.error);
                if (error.response.data.location) {
                    setTimeout(function(){document.location = error.response.data.location;},2000);
                }
            });


        },
        remove(id) {
            if (confirm('Czy na pewno chcesz usunąć ten zestaw?')) {
                axios
                    .post(
                        '/api/admin/tests/remove',
                        {
                            hash_id: yh.auth.hash_id,
                            id: id
                        }
                    )
                    .then(response => {
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
