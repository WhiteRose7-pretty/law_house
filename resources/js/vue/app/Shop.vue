<template>
    <div>
        <h1>
            Sklep
        </h1>

        <div class="shop">
          <ul class="nav nav-tabs" role="tablist">
              <li role="presentation"><a href="#tab-01" aria-controls="tab-01" role="tab" data-toggle="tab" class="active">Pakiety</a></li>
              <li role="presentation"><a href="#tab-02" aria-controls="tab-02" role="tab" data-toggle="tab">Transakcje</a></li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="tab-01">

              <div class="row" v-if="user_packages && packages">
                  <div class="col-12 col-md-6 p-1" v-for="p in packages">
                      <div style="background-color:#f1f1f1;" class="p-3">
                          <h2>{{p.name}}</h2>
                          <p>{{p.info}}</p>
                          <div v-if="isCurrent(p.id)">
                              <span> dostępny {{validUntil(p.id)}}</span>
                          </div>
                          <div v-else>
                              <button v-if="dev_options" v-on:click="packageFix1Month(p.id)" class="btn btn-sm btn-info">dev1m</button>
                              <button v-if="p.free" v-on:click="addFreePackage(p.id)" class="btn btn-sm btn-info">WYPRÓBUJ ZA DARMO </button>
                              <div v-else class="d-inline">
                                  <button  v-on:click="packageBuy(p.id,1)" class="btn btn-sm btn-success mb-2">{{pricePrint(p.price1m)}} / 1 m.</button>
                                  <button  v-on:click="packageBuy(p.id,3)" class="btn btn-sm btn-success mb-2">{{pricePrint(p.price3m)}} / 3 m.</button>
                                  <button  v-on:click="packageBuy(p.id,12)" class="btn btn-sm btn-success mb-2">{{pricePrint(p.price1y)}} / 1 r.</button>
                              </div>

                          </div>
                      </div>
                  </div>
              </div>

            </div>
            <div role="tabpanel" class="tab-pane" id="tab-02">
                <div v-if="transactions">
                    <div class="row p-3 border-bottom" v-for="t in transactions">
                        <div class="col-12 col-sm-4 col-md-2">
                            {{dateF(t.created_at)}}
                        </div>
                        <div class="col-12 col-sm-8 col-md-4">
                            {{t.description}}
                            <span v-if="t.discount">
                            (z rabatem {{t.discount}}%)
                            </span>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            {{fixAmount(t.amount_gross)}}
                        </div>
                        <div class="col-6 col-sm-8 col-md-2">
                            <span v-if="t.status == 'new'">
                                nowa
                            </span>
                            <span v-else-if="t.status == 'registered'">
                                oczekuje*
                            </span>
                            <span v-else-if="t.status == 'verified'">
                                opłacona
                            </span>
                            <span v-else>
                                anulowana
                            </span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-2">
                            <a v-if="t.status == 'registered' && t.delta < 60*30" class="btn btn-info btn-sm text-white" v-bind:href="url+t.p24_token">opłać</a>
                            <a v-if="t.service_invoice_token" class="btn btn-info btn-sm text-white" v-bind:href="urlFV+t.service_invoice_token" target="_blank">
                              <i class="fas fa-file-invoice"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-3">
                        * transakcje których nie zrealizowałeś zostaną automatycznie zaktualizowane jako przeterminowane po upływie 10 dni
                    </div>
                </div>
                <div v-else class="p-2">
                    brak historycznych transakcji
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
            dev_options: false,
            loading: false,
            error: null,
            user_packages: null,
            packages: null,
            transactions: null,
            url: null,
            urlFV: null,
        };
    },
    created() {
        this.userPackagesFetch();
        this.packagesFetch();
        this.transactionsFetch();
    },
    mounted() {
        SideBarCollapseIfActive(true);
        if ($("meta[name=environemnt]").length) {
            var env = $("meta[name=environemnt]").attr('content');
            if (env == 'local' || env == 'staging' ) {
                this.dev_options = true;
            }
        }
    },
    updated() {},
    methods: {
        dateF(s) {
            return moment(s).local().format('YYYY-MM-DD HH:mm:ss');
        },
        fixAmount(s) {
            return s/100 + ' PLN';
        },
        isCurrent(id) {
            for(var i=0;i<this.user_packages.length;i++) {
                if (this.user_packages[i].package_id==id) {
                    return true;
                }
            }
            return false;
        },
        validUntil(id) {
            for(var i=0;i<this.user_packages.length;i++) {
                if (this.user_packages[i].package_id==id) {
                    if (this.user_packages[i].type=='free') {
                        return 'bezterminowo';
                    }
                    return 'do ' + this.user_packages[i].valid_until;
                }
            }
        },
        userPackagesFetch() {
            this.error = this.user_packages = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/packages/current',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.user_packages = JSON.parse(JSON.stringify(response.data));
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        packagesFetch() {
            this.error = this.packages = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/packages/available',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.packages = JSON.parse(JSON.stringify(response.data));
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        packageBuy(id,months) {
            this.$router.push({ name: 'shop.form', params: { package_id: id, months: months } });
        },
        addFreePackage(id) {
            this.loading = true;
            axios
                .post(
                    '/api/app/packages/add/free',
                    {
                        hash_id: yh.auth.hash_id,
                        package_id: id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.userPackagesFetch();
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function(){document.location = error.response.data.location;},2000);
                }
            });
        },
        active_manual() {
            this.loading = true;
            axios
                .post(
                    '/api/app/packages/buy/active_manual',
                    {
                        hash_id: yh.auth.hash_id,
                        package_id: 'id',
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.userPackagesFetch();
                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function(){document.location = error.response.data.location;},2000);
                }
            });
        },
        packageFix1Month(id) {
            this.loading = true;
            axios
                .post(
                    '/api/app/packages/quickfix',
                    {
                        hash_id: yh.auth.hash_id,
                        package_id: id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.userPackagesFetch();
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        pricePrint(price) {
            price = price.replace('.00','');
            return price + 'zł';
        },
        transactionsFetch() {
            this.error = this.transactions = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/transactions',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.transactions = JSON.parse(JSON.stringify(response.data.transactions));
                    this.url = JSON.parse(JSON.stringify(response.data.url));
                    this.urlFV = JSON.parse(JSON.stringify(response.data.urlFV));
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
    },
}
</script>
