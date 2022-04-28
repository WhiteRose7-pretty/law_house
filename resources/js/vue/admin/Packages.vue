<template>
    <div class="ius">
        <h2 class="mb-3">
            Pakiety
        </h2>

        <div class="card">
            <div class="card-body">

                <div class="p-3 mb-4 text-right" style="background-color: #f1f1f1;">
                    <a v-on:click="add()" class="btn btn-success btn-sm text-white">Utwórz nowy pakiet</a>
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
                        <th scope="col">Ost. Aktualizacja</th>
                        <th scope="col">Wyświetlany</th>
                        <th scope="col">Akcje</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="{id,updated_at,name, visible} in list" v-bind:data-id="id" class="packages-set-row">
                        <th scope="row">{{ id }}</th>
                        <td>{{ name }}</td>
                        <td>{{ dateF(updated_at) }}</td>
                        <td><span v-if="visible">widoczny</span><span v-else>niewidzialny</span></td>
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
                        <th scope="col">Ost. Aktualizacja</th>
                        <th scope="col">Wyświetlany</th>
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

        <div v-if="package" class="card mt-2">
            <div class="card-body">
                <h2 v-if="package.id>0">Edycja pakietu</h2>
                <h2 v-else>Dodawanie nowego pakietu</h2>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Wprowadź nazwę pakietu" v-model="package.name">
                </div>
                <div class="form-group">
                    <textarea v-model="package.info" class="form-control"
                              placeholder="Wprowadź opis pakietu"></textarea>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" v-model="package.free" id="exampleCheck1">
                    <label class="form-check-label" for="exampleCheck1">Pakiet darmowy</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" v-model="package.visible" id="exampleCheck2">
                    <label class="form-check-label" for="exampleCheck2">Wyświetlany</label>
                </div>
                <div v-if="!package.free">
                    <div class="form-group">
                        <label>Cena za 1 miesiąc</label>
                        <input type="number" class="form-control" placeholder="100.00" v-model="package.price1m">
                    </div>
                    <div class="form-group">
                        <label>Cena za 3 miesiące</label>
                        <input type="number" class="form-control" placeholder="100.00" v-model="package.price3m">
                    </div>
                    <div class="form-group">
                        <label>Cena za 1 rok</label>
                        <input type="number" class="form-control" placeholder="100.00" v-model="package.price1y">
                    </div>
                </div>
                <div class="">
                    <h3>Wybierz zestawy pytań</h3>
                    <div v-for="set in package.sets" class="form-check">
                        <input type="checkbox" class="form-check-input" v-model="set.selected">
                        <label class="form-check-label">{{ set.name }}</label>
                    </div>
                </div>
                <div class="text-center">
                    <button v-on:click="abort()" class="btn btn-warning">Anuluj</button>
                    <button v-on:click="update()" class="btn btn-success">Zapisz</button>
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
            list: [],
            sets: [],
            package: null,
            package_clean: {
                id: 0,
                name: '',
                info: '',
                free: 0,
                price1m: null,
                price3m: null,
                price1y: null,
                sets: [],
                visible: true,
            },
        };
    },
    created() {
        this.fetch();
        this.fetchSets();
    },
    mounted() {
    },
    updated() {
    },
    methods: {
        dateF(s) {
            return moment(s).local().format('YYYY-MM-DD HH:mm:ss');
        },
        abort() {
            this.package = null;
        },
        add() {
            this.package = JSON.parse(JSON.stringify(this.package_clean));
            this.package.sets = JSON.parse(JSON.stringify(this.sets));
        },
        edit(id) {
            for (var i = 0; i < this.list.length; i++) {
                if (this.list[i].id != id) {
                    continue;
                }
                var p = JSON.parse(JSON.stringify(this.list[i]));
                var s = JSON.parse(JSON.stringify(p.sets));
                p.sets = JSON.parse(JSON.stringify(this.sets));
            }
            for (var j = 0; j < s.length; j++) {
                p.sets[s[j].document_id].selected = true;
            }
            this.package = p;
        },
        fetch() {
            this.error = null;
            this.list = [];
            this.loading = true;
            axios
                .post(
                    '/api/admin/packages',
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
        },
        fetchSets() {
            this.error = null;
            this.sets = [];
            this.loading = true;
            axios
                .post(
                    '/api/admin/documents/all',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.sets = response.data.results;
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
        remove(id) {
            if (confirm('Czy na pewno chcesz usunąć ten zestaw?')) {
                axios
                    .post(
                        '/api/admin/packages/remove',
                        {
                            hash_id: yh.auth.hash_id,
                            id: id
                        }
                    )
                    .then(response => {
                        $('.packages-set-row[data-id="' + id + '"]').remove();
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
        update() {
            if(!this.package.free){
                if(!this.package.price1m || !this.package.price3m || !this.package.price1y || !this.package.name ){
                    alert("Please select valid value!");
                    return false;
                }
            }
            this.loading = true;
            axios
                .post(
                    '/api/admin/packages/update',
                    {
                        hash_id: yh.auth.hash_id,
                        package: this.package,
                    }
                )
                .then(response => {
                    this.fetch();
                    this.package = null;
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
            this.package = null;
         },
    },
}
</script>
