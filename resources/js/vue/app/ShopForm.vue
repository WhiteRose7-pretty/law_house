<template>
    <div>
        <h1>
            Zakup Pakietu
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

        <div class="ius" v-if="package">
        <div class="card"><div class="card-body">
            <div class="">
                <h3>Sprawdź i potwierdź zamówienie</h3>
                <div class="">
                    PAKIET: {{package.name}}
                </div>
                <div class="">
                    <select v-model="months" class="form-control form-control-lg" style="height:58px;" v-on:change="packageFetch(package.id,months)">
                        <option value="1" >1 miesiąc</option>
                        <option value="3">3 miesiące</option>
                        <option value="12">1 rok</option>
                    </select>
                </div>
                <div class="row mt-2 mb-2 border-bottom">
                    <div class="col-6" style="font-size:1.5em;">
                    Kwota brutto:
                    </div>
                    <div class="col-6 text-right" style="font-size:1.5em;">
                    {{price.gross}} zł
                    </div>
                </div>
            </div>
            <div class="">
                <h3>Kod opcjonalnie</h3>
                <div v-if="discount.error" class="text-danger">
                    {{ discount.error }}
                </div>
                <div v-if="!discount.discount" class="row">
                    <div class="col-6">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="name" id="discount_code" type="text" aria-labelledby="discount-code-label"  autocomplete="name" v-model="discount.code">
                            <span class="mdc-floating-label" id="discount-code-label">Kod rabatowy</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-info text-white mt-1" v-on:click="packageFetch(package.id,months)">Użyj</button>
                    </div>
                </div>
                <div v-else class="row">
                    <div class="col-6">
                        Użyty kod: {{discount.code}}, rabat : {{discount.discount}}%
                    </div>
                    <div class="col-6 text-right">
                        Kwota brutto (po rabacie): <strong>{{discount.price.gross}}</strong> zł
                    </div>
                </div>
            </div>
            <div v-if="user" class="">

                <h3>Podaj dane</h3>
                <div class="row m-0">
                    <div class="col-12 col-sm-4 col-md-2 pl-1 pr-1">
                        <select v-model="user.salutation" class="form-control form-control-lg" style="height:58px;" v-on:change="userDataChanged()">
                            <option value="mr">Pan</option>
                            <option value="mrs">Pani</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-8 col-md-5 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="name" id="name" type="text" aria-labelledby="name-label" required autocomplete="name" v-model="user.name">
                            <span class="mdc-floating-label" id="name-label">Imię</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                    <div class="col-12 col-sm-12 col-md-5 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="surname" id="surname" type="text" aria-labelledby="surname-label" required autocomplete="surname" v-model="user.surname">
                            <span class="mdc-floating-label" id="surname-label">Nazwisko</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                </div>
                <div class="row m-0">
                    <div class="col-12 pl-3 pr-1" style="line-height:55px;">
                        <div class="mdc-switch ml-2" data-mdc-auto-init="MDCSwitch" style="vertical-align:middle;">
                            <div class="mdc-switch__track"></div>
                            <div class="mdc-switch__thumb-underlay">
                                <div class="mdc-switch__thumb"></div>
                                <input type="checkbox" id="request_invoice" class="mdc-switch__native-control" role="switch" aria-checked="" v-model="user.request_invoice" v-on:change="userDataChanged()">
                            </div>
                        </div>
                        <label for="request_invoice" class="ml-2 pb-0 yh-fs-16 yh-v-o-70">Faktura</label>
                    </div>
                </div>

                <div class="row m-0" v-if="user.request_invoice">
                    <div class="col-12 col-sm-6 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="address_company" id="address_company" type="text" aria-labelledby="address-company" required autocomplete="address_company" v-model="user.address_company">
                            <span class="mdc-floating-label" id="address-company">Nazwa firmy</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                    <div class="col-12 col-sm-6 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="tax_nip" id="tax_nip" type="text" aria-labelledby="tax-nip-label" required autocomplete="tax_nip" v-model="user.tax_nip">
                            <span class="mdc-floating-label" id="tax-nip-label">NIP</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                </div>

                <div class="row m-0" v-if="user.request_invoice">
                    <div class="col-12 col-sm-8 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="address_street_name" id="address_street_name" type="text" aria-labelledby="address-street-name-label" required autocomplete="address_street_name" v-model="user.address_street_name">
                            <span class="mdc-floating-label" id="address-street-name-label">Nazwa Ulicy</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                    <div class="col-6 col-sm-2 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="address_street_number" id="address_street_number" type="text" aria-labelledby="address-street-number-label" required autocomplete="address_street_number" v-model="user.address_street_number">
                            <span class="mdc-floating-label" id="address-street-number-label-label">Nr</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                    <div class="col-6 col-sm-2 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="address_apartment_number" id="address_apartment_number" type="text" aria-labelledby="address-apartment-number-label" autocomplete="address_apartment_number" v-model="user.address_apartment_number">
                            <span class="mdc-floating-label" id="address-apartment-number-label">Nr lokalu</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                </div>

                <div class="row m-0" v-if="user.request_invoice">
                    <div class="col-12 col-sm-3 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="address_post_code" id="address_post_code" type="text" aria-labelledby="address-post-code-label" required autocomplete="address_post_code" v-model="user.address_post_code">
                            <span class="mdc-floating-label" id="address-post-code-label">Kod pocztowy</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                    <div class="col-12 col-sm-9 pl-1 pr-1">
                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input yh-bind-key" name="address_city" id="address_city" type="text" aria-labelledby="address-city-label" required autocomplete="address_city" v-model="user.address_city">
                            <span class="mdc-floating-label" id="address-city-label">Miasto</span>
                            <span class="mdc-line-ripple"></span>
                        </label>
                    </div>
                </div>

                <div class="text-danger text-center mb-2" v-if="user_data_update_validation_error_msg">
                    {{user_data_update_validation_error_msg}}
                </div>

            </div>

            <div v-if="user" class="">
                <h3>Wymagane zgody</h3>

                <div class="mdc-form-field">
                    <div class="mdc-touch-target-wrapper">
                      <div class="mdc-checkbox mdc-checkbox--touch">
                        <input type="checkbox" required
                                name="checkbox-1"
                               class="mdc-checkbox__native-control"
                               id="checkbox-1" v-model="checbox_law"/>
                        <div class="mdc-checkbox__background">
                          <svg class="mdc-checkbox__checkmark"
                               viewBox="0 0 24 24">
                            <path class="mdc-checkbox__checkmark-path"
                                  fill="none"
                                  d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                          </svg>
                          <div class="mdc-checkbox__mixedmark"></div>
                        </div>
                        <div class="mdc-checkbox__ripple"></div>
                      </div>
                    </div>
                    <label for="checkbox-1" class="cursor-pointer">* Przeczytałem i akceptuję <a style="border-bottom: 1px solid #000" href="/informacje/regulamin">regulamin</a> sklepu oraz wyrażam zgodę na spełnienie świadczenia przed upływem terminu odstąpienia od umowy</label>
                </div>

                <div class="mdc-form-field">
                    <div class="mdc-touch-target-wrapper">
                      <div class="mdc-checkbox mdc-checkbox--touch">
                        <input type="checkbox" required
                               class="mdc-checkbox__native-control"
                               name="checkbox-2"
                               id="checkbox-2" v-model="checbox_law_1"/>
                        <div class="mdc-checkbox__background">
                          <svg class="mdc-checkbox__checkmark"
                               viewBox="0 0 24 24">
                            <path class="mdc-checkbox__checkmark-path"
                                  fill="none"
                                  d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
                          </svg>
                          <div class="mdc-checkbox__mixedmark"></div>
                        </div>
                        <div class="mdc-checkbox__ripple"></div>
                      </div>
                    </div>
                    <label for="checkbox-2" class="cursor-pointer">* Wyrażam zgodę na przetwarzanie moich danych osobowych w celu realizacji zamówienia <a style="border-bottom: 1px solid #000" href="/informacje/polityka-prywatnosci">Polityka prywatności</a></label>
                </div>

                <div class="text-danger text-center mb-2" v-if="checkboxes_required_error_msg">
                    {{checkboxes_required_error_msg}}
                </div>

                <br>
                <button class="btn btn-success" v-on:click="packageBuyProcessStart()">KUPUJĘ I PŁACĘ</button>
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
            discount: {
                code: null,
                discount: null,
                error: null,
                price: null,
            },
            package: null,
            price: null,
            months: null,
            user: null,
            user_data_changed: false,
            user_data_updated: false,
            user_data_changed2: false,
            user_data_update_validation_error_msg: '',
            checbox_law: false,
            checbox_law_1: false,
            checkboxes_required_error_msg: '',
        };
    },
    created() {
        if (this.$route.params.months != 1 && this.$route.params.months != 3 && this.$route.params.months != 12) {
            this.$router.push({ name: 'shop' });
            return;
        }
        this.packageFetch(this.$route.params.package_id,this.$route.params.months);
        this.userFetch();
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
        mdc.autoInit();
    },
    methods: {
        isValidNip(nip) {
            if(typeof nip !== 'string') {
                return false;
            }

            nip = nip.replace(/[\ \-]/gi, '');

            let weight = [6, 5, 7, 2, 3, 4, 5, 6, 7];
            let sum = 0;
            let controlNumber = parseInt(nip.substring(9, 10));
            let weightCount = weight.length;
            for (let i = 0; i < weightCount; i++) {
                sum += (parseInt(nip.substr(i, 1)) * weight[i]);
            }

            return sum % 11 === controlNumber;
        },
        packageBuyProcessStart() {
            this.user_data_update_validation_error_msg = '';
            this.checkboxes_required_error_msg = '';
            if (!this.user.name || !this.user.surname) {
                this.user_data_update_validation_error_msg = 'Wypełnij pola oznaczone gwiazdką';
                return;
            }
            if (this.user.request_invoice) {
                if (
                  !this.user.address_company ||
                  !this.user.address_street_name ||
                  !this.user.address_street_number ||
                  !this.user.address_city ||
                  !this.user.address_post_code ||
                  !this.user.tax_nip
                ) {
                  this.user_data_update_validation_error_msg = 'Wypełnij pola oznaczone gwiazdką';
                  return;
                }
                if (!this.isValidNip(this.user.tax_nip)) {
                  this.user_data_update_validation_error_msg = 'Niepoprawny numer NIP';
                  return;
                }
                // TODO : check POSTCODE format
            }
            if (!this.checbox_law || !this.checbox_law_1) {
                this.checkboxes_required_error_msg = 'Zaznacz wymagane zgody';
                return;
            }
            axios
                .post(
                    '/api/app/packages/buy/start',
                    {
                        hash_id: yh.auth.hash_id,
                        user: this.user,
                        package: this.package,
                        months: this.months,
                        discount: this.discount,
                    }
                )
                .then(response => {
                    document.location = response.data.url;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        packageFetch(package_id, months) {
            this.error = this.packages = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/packages/form',
                    {
                        hash_id: yh.auth.hash_id,
                        package_id: package_id,
                        months: months,
                        discount: this.discount,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.package = JSON.parse(JSON.stringify(response.data.package));
                    this.price = JSON.parse(JSON.stringify(response.data.price));
                    this.months = months;

                    this.discount.discount = JSON.parse(JSON.stringify(response.data.discount));
                    this.discount.error = JSON.parse(JSON.stringify(response.data.discount_error));
                    this.discount.price = JSON.parse(JSON.stringify(response.data.discount_price));

                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        userFetch() {
            this.error = this.user = this.user_last = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/user',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.user = JSON.parse(JSON.stringify(response.data));
                    this.user_last = JSON.parse(JSON.stringify(response.data));
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
