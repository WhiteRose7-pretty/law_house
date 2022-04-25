<template>
<div class="ius">
    <h2 class="mb-3">
        Płatności
    </h2>

    <div v-if="loading" class="text-info">
        Ładowanie...
    </div>

    <div v-if="error" class="text-danger">
        {{ error }}
    </div>

    <div>
      <ul class="nav nav-tabs" role="tablist">

        <li role="presentation"><a href="#tab-01" aria-controls="tab-01" role="tab" data-toggle="tab" class="active">Transakcje</a></li>
        <li role="presentation"><a href="#tab-02" aria-controls="tab-02" role="tab" data-toggle="tab">Kody rabatowe</a></li>
      </ul>
      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active p-2 mt-2" id="tab-01">

              <div class="mb-1 clearfix">
                  <div class="float-left">
                    <select id="fetch-type" v-model="type" v-on:change="fetch()">
                        <option value="all" :selected="type == 'all'">wszystkie</option>
                        <option value="paid" :selected="type == 'paid'">opłacone</option>
                        <option value="unpaid" :selected="type == 'unpaid'">nieopłacone</option>
                    </select>
                  </div>
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

              <table v-if="pages > 0" class="table">
                  <thead>
                      <tr>
                          <th scope="col">#</th>
                          <th scope="col">Data</th>
                          <th scope="col">Użytkownik</th>
                          <th scope="col">Opis</th>
                          <th scope="col">Rabat</th>
                          <th scope="col">Wartość</th>
                          <th scope="col">Status</th>
                          <th scope="col">Faktura</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr v-for="{id,created_at,user,description,discount,discount_code,amount_gross,status,service_invoice_token} in transactions" v-bind:data-id="id" class="user-row">
                          <th scope="row">{{id}}</th>
                          <td>{{dateF(created_at)}}</td>
                          <td>{{user.name}} {{user.surname}}</td>
                          <td>{{description}}</td>
                          <td><span v-if="discount">{{discount_code}} ({{discount}}%)</span></td>
                          <td>{{fixAmount(amount_gross)}}</td>
                          <td>
                              <span v-if="status == 'new'">
                                  nowa
                              </span>
                              <span v-else-if="status == 'registered'">
                                  nowa
                              </span>
                              <span v-else-if="status == 'verified'">
                                  opłacona
                              </span>
                              <span v-else>
                                  anulowana
                              </span>
                          </td>
                          <td >
                              <a v-if="service_invoice_token" class="btn btn-info btn-sm text-white" v-bind:href="urlFV+service_invoice_token" target="_blank">
                                <i class="fas fa-file-invoice"></i>
                              </a>
                          </td>
                      </tr>
                  </tbody>
              </table>
              <table v-else class="table">
                  <thead>
                      <tr>
                          <th scope="col">#</th>
                          <th scope="col">Data</th>
                          <th scope="col">Użytkownik</th>
                          <th scope="col">Opis</th>
                          <th scope="col">Rabat</th>
                          <th scope="col">Wartość</th>
                          <th scope="col">Status</th>
                          <th scope="col">Faktura</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr>
                          <td colspan="9">brak pasujących wyników</td>
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
        <div role="tabpanel" class="tab-pane p-2" id="tab-02">

            <div class="p-3 mb-4 text-right" style="background-color: rgb(241, 241, 241);">
                  <a class="btn btn-success btn-sm text-white" v-on:click="add()">Dodaj kod</a>
            </div>

            <b-row class="mb-3">
                <b-col md="3">
                    <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Type to Search"></b-form-input>
                </b-col>
            </b-row>

            <b-table id="my-table" striped hover outlined :per-page="discount_perPage" :current-page="discount_currentPage" :items="discounts" :fields="fields" :filter="filter">
                <template v-slot:cell(#)="data">
                    {{data.item.id}}
                </template>
                <template v-slot:cell(Data_rozpoczęcia)="data">
                    <span v-if="data.item.start_date" style="white-space: pre;">{{dateOnly(data.item.start_date)}}</span>
                    <span v-else style="white-space: pre;">{{dateOnly(data.item.created_at)}}</span>
                </template>

                <template v-slot:cell(Kod)="data">
                    <span>{{data.item.code}}</span>
                </template>
                <template v-slot:cell(Wygasa)="data">
                    <span style="white-space: pre;"><span v-if="data.item.valid_until">{{dateOnly(data.item.valid_until)}}</span><span v-else>bezterminowo</span></span>
                </template>
                <template v-slot:cell(Rabat)="data">
                    {{data.item.discount}}%
                </template>
                <template v-slot:cell(Pakiety)="data">
                    {{data.item.packages?data.item.packages.name : ''}}
                </template>
                <template v-slot:cell(miesiace)="data">
                    <span>{{data.item.period }}</span>
                </template>
                <template v-slot:cell(status)="data">
                    {{data.item.active? 'dostępny' : 'niedostępne'}}
                </template>
                <template v-slot:cell(type)="data">
                    {{data.item.type }}
                </template>
            </b-table>
            <p class="mt-3 float-left">Current Page: {{ discount_currentPage }}</p>
            <b-pagination class="float-right"
                v-model="discount_currentPage"
                :total-rows="discounts_rows"
                :per-page="discount_perPage"
                aria-controls="my-table"
            ></b-pagination>
            <div style="clear:both"></div>

            <div v-if="discount" class="card mt-2" ref="add-discount" id="add-discount">
                <div class="card-body">
                    <h2>Dodawanie nowego kodu rabatowego</h2>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="wprowadz-swoj-kod-rabatowy" v-model="discount.code">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="rabat w procentach np.: 22" v-model="discount.discount">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="data rozpoczęcia np.: 2222-10-08" v-model="discount.start_date">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="data ważności np.: 2222-10-08" v-model="discount.valid_until">
                    </div>

                    <div class="form-group">
                        <select v-model="discount.package" class="form-control">
                            <option value="0">wybierz</option>
                            <option value="">wszystkie pakiety</option>
                            <option v-for="d in packages" v-bind:value="d.id">{{d.name}}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select v-model="discount.period" class="form-control">
                            <option value="0">wybierz</option>
                            <option value="">wszystkie pakiety</option>
                            <option value="1">1 miesiąc</option>
                            <option value="3">3 miesiące</option>
                            <option value="12">1 rok</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select v-model="discount.type" class="form-control">
                            <option value="once use">once use</option>
                            <option value="infinite use">infinite use</option>
                        </select>
                    </div>

                    <p class="mt-2 text-danger">{{discount_error}}</p>

                    <div class="text-center">
                        <button v-on:click="abort()" class="btn btn-warning">Anuluj</button>
                        <button v-on:click="save()" class="btn btn-success">Zapisz</button>
                    </div>
                </div>
            </div>

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
            type: 'paid',
            limit: 5,
            count: 0,
            pages: 0,
            page: 1,
            transactions: null,
            discounts: null,
            discount: null,
            filter: "",
            discount_currentPage: 1,
            discount_perPage: 10,
            discount_clean: {
                code: null,
                discount: null,
                start_date: null,
                valid_until: null,
                package: 0,
                period: 0,
                type:'once use',
            },
            urlFV: null,
            packages: null,
            scroll_object: null,
            fields: ["#", "Data_rozpoczęcia", "Kod", "Wygasa", "Rabat", "Pakiety", "miesiace", "status", "type"],
            discount_error: null,
        };
    },
    created() {
        this.fetch();
        this.fetchDiscounts();
        this.fetchPackages();
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
        this.scroll_object = document.getElementsByClassName('ps')[0];
    },
    updated() {
    },
    computed: {
        discounts_rows(){
            if(this.discounts){
                return this.discounts.length;
            }
            else{
                return 0;
            }
        }
    },
    methods: {
        scrollMeTo(refName) {
            var _this = this;
            setTimeout(function(){
                var element = _this.$refs[refName];
                var top = element.offsetTop;
                _this.scroll_object.scrollTo(0, top);
            });
        },
        dateF(s) {
            return moment(s).local().format('YYYY-MM-DD HH:mm:ss');
        },
        dateOnly(s) {
            return moment(s).local().format('YYYY-MM-DD');
        },
        fixAmount(s) {
            return s/100 + ' PLN';
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
            this.error = this.transactions = null;
            this.loading = true;
            this.pages = 0;
            console.log(yh.auth.user_type)
            axios
                .post(
                    '/api/admin/transactions',
                    {
                        hash_id: yh.auth.hash_id,
                        limit: this.limit,
                        query: this.query,
                        page: this.page,
                        type: this.type,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.pages = response.data.pages;
                    this.page = response.data.page;
                    this.transactions = response.data.results;
                    this.count = response.data.count;
                    this.urlFV = response.data.urlFV;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        fetchPackages() {
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
                    this.packages = response.data.results;
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function(){document.location = error.response.data.location;},2000);
                }
            });
        },
        fetchDiscounts() {
            this.error = this.discounts = null;
            this.loading = true;
            this.pages = 0;
            axios
                .post(
                    '/api/admin/discounts',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.discounts = response.data.results;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        add() {
            this.discount = this.discount_clean;
            this.scrollMeTo('add-discount');
        },
        abort() {
            this.discount = null;
        },
        save() {
            this.loading = true;

            if(!this.discount.code || !this.discount.discount){
                this.discount_error = "Enter valid value!";
                this.loading = false;
                return false;
            }
            if( (this.discount.package === 0) || (this.discount.period === 0)){
                this.discount_error = "Enter valid value!";
                this.loading = false;
                return false;
            }
            this.error = null;
            this.loading = true;
            axios
                .post(
                    '/api/admin/discounts/add',
                    {
                        hash_id: yh.auth.hash_id,
                        discount: this.discount,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.discount = null;
                    this.fetchDiscounts();
                }).catch(error => {
                    this.loading = false;
                    this.discount_error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        }
    },
}
</script>
