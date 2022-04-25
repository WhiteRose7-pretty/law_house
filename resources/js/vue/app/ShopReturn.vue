<template>
    <div>
        <h1>
            Zakup Pakietu / Podsumowanie
        </h1>

        <div class="card mb-2" v-if="loading||error">
            <div class="card-body">
                <div v-if="loading" class="text-info">
                    Ładowanie...
                </div>
                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>
            </div>
        </div>

        <div class="ius" v-if="transaction">
            <div class="card"><div class="card-body">
                <div class="">
                    <h3>Status zamówienia</h3>
                    <div class="">
                        PAKIET: {{transaction.description}}
                    </div>
                    <div class="row">
                        <div class="col-6">
                        Kwota brutto:
                        </div>
                        <div class="col-6 text-right">
                        {{fixPrice(transaction.amount_gross)}} zł
                        </div>
                    </div>
                </div>
                <div class="">
                    <span v-if="transaction.status == 'registered'">
                        Oczekujemy na potwierdzenie płatności.
                        <br>
                        <br>
                        Ponowne sprawdzenie za 10 sekund.
                    </span>
                    <span v-if="transaction.status == 'verified'">
                        Opłacona
                    </span>
                </div>
            </div></div>
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
            transaction: null,
        };
    },
    created() {
        this.transactionFetch();
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {

    },
    methods: {
      fixPrice(s) {
          return s/100;
      },
      transactionFetch() {
          this.error = this.transaction = null;
          this.loading = true;
          axios
              .post(
                  '/api/app/transactions/return',
                  {
                      hash_id: yh.auth.hash_id,
                  }
              )
              .then(response => {
                  this.loading = false;
                  this.transaction = JSON.parse(JSON.stringify(response.data.transaction));
                  if (this.transaction.status == 'registered') {
                      var _this = this;
                      setTimeout(function(){
                          _this.transactionFetch();
                      },10000);
                  }
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
