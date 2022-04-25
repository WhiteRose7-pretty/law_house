<template>
<div>
    <h1>
        Moje konto
    </h1>

    <div v-if="loading" class="text-info">
        Ładowanie...
    </div>

    <div v-if="error" class="text-danger">
        {{ error }}
    </div>

    <div role="tablist" class="ius">
        <b-card v-if="user" no-body class="mb-1">
            <b-card-header header-tag="header" class="p-1" role="tab">
                <b-button block v-b-toggle.user-data variant="special">Dane</b-button>
            </b-card-header>
            <b-collapse id="user-data" accordion="user-account" role="tabpanel">
                <b-card-body class="p-2">
                    <!-- <b-card-text>I start opened because <code>visible</code> is <code>true</code></b-card-text> -->
                    <!-- <b-card-text>{{ text }}</b-card-text> -->

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

                    <div class="text-success text-center mb-2" v-if="user_data_updated">
                        Zaktualizowano!
                    </div>

                    <div class="text-center mb-2" v-if="user_data_changed">
                        <button class="btn btn-warning" v-on:click="userUpdateAbort()">Anuluj</button>
                        <button class="btn btn-success" v-on:click="userUpdate()">Zapisz</button>
                    </div>

                </b-card-body>
            </b-collapse>
        </b-card>

        <b-card v-if="user" no-body class="mb-1">
            <b-card-header header-tag="header" class="p-1" role="tab">
                <b-button block v-b-toggle.user-repeat-setting variant="special">Ustawienia Powtórzeń</b-button>
            </b-card-header>
            <b-collapse id="user-repeat-setting" accordion="user-account" role="tabpanel">
                <b-card-body class="p-2">
                    <b-card-text class="p-1 yh-fs-16">Ustal po ilu dniach, chcesz powtarzać pytanie:</b-card-text>
                    <div class="row m-0 mb-3">
                        <div class="col-6 text-right pt-1">
                            jeśli udzielisz, złej odpowiedzi:
                        </div>
                        <div class="col-6 text-left">
                            <button class="btn btn-primary btn-sm mb-1" v-on:click="userRepeatChange(0,-1)"><i class="fa fa-minus"></i></button>
                            <span class="text-center d-inline-block" style="width:40px;">{{user.repeat_incorrect}}</span>
                            <button class="btn btn-primary btn-sm" v-on:click="userRepeatChange(0,1)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="row m-0 mb-3">
                        <div class="col-6 text-right pt-1">
                            jeśli odpowiesz poprawnie 1 raz:
                        </div>
                        <div class="col-6 text-left">
                            <button class="btn btn-primary btn-sm mb-1" v-on:click="userRepeatChange(1,-1)"><i class="fa fa-minus"></i></button>
                            <span class="text-center d-inline-block" style="width:40px;">{{user.repeat_1_correct}}</span>
                            <button class="btn btn-primary btn-sm" v-on:click="userRepeatChange(1,1)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="row m-0 mb-3">
                        <div class="col-6 text-right pt-1">
                            jeśli odpowiesz poprawnie 2 razy:
                        </div>
                        <div class="col-6 text-left">
                            <button class="btn btn-primary btn-sm mb-1" v-on:click="userRepeatChange(2,-1)"><i class="fa fa-minus"></i></button>
                            <span class="text-center d-inline-block" style="width:40px;">{{user.repeat_2_correct}}</span>
                            <button class="btn btn-primary btn-sm" v-on:click="userRepeatChange(2,1)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="row m-0 mb-3">
                        <div class="col-6 text-right pt-1">
                            jeśli odpowiesz poprawnie 3 razy:
                        </div>
                        <div class="col-6 text-left">
                            <button class="btn btn-primary btn-sm mb-1" v-on:click="userRepeatChange(3,-1)"><i class="fa fa-minus"></i></button>
                            <span class="text-center d-inline-block" style="width:40px;">{{user.repeat_3_correct}}</span>
                            <button class="btn btn-primary btn-sm" v-on:click="userRepeatChange(3,1)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="row m-0 mb-3">
                        <div class="col-6 text-right pt-1">
                            jeśli odpowiesz poprawnie 4 razy:
                        </div>
                        <div class="col-6 text-left">
                            <button class="btn btn-primary btn-sm mb-1" v-on:click="userRepeatChange(4,-1)"><i class="fa fa-minus"></i></button>
                            <span class="text-center d-inline-block" style="width:40px;">{{user.repeat_4_correct}}</span>
                            <button class="btn btn-primary btn-sm" v-on:click="userRepeatChange(4,1)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="row m-0 mb-3">
                        <div class="col-6 text-right pt-1">
                            jeśli odpowiesz poprawnie 5 razy:
                        </div>
                        <div class="col-6 text-left">
                            <button class="btn btn-primary btn-sm mb-1" v-on:click="userRepeatChange(5,-1)"><i class="fa fa-minus"></i></button>
                            <span class="text-center d-inline-block" style="width:40px;">{{user.repeat_5_correct}}</span>
                            <button class="btn btn-primary btn-sm" v-on:click="userRepeatChange(5,1)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>

                    <!-- 'repeat_incorrect','repeat_1_correct','repeat_2_correct','repeat_3_correct','repeat_4_correct','repeat_5_correct' -->

                    <div class="text-danger text-center mb-2" v-if="user_data_update_validation_error_msg">
                        {{user_data_update_validation_error_msg}}
                    </div>

                    <div class="text-success text-center mb-2" v-if="user_data_updated">
                        Zaktualizowano!
                    </div>

                    <div class="text-center mb-2" v-if="user_data_changed2">
                        <button class="btn btn-warning" v-on:click="userUpdateAbort()">Anuluj</button>
                        <button class="btn btn-success" v-on:click="userUpdate()">Zapisz</button>
                    </div>

                </b-card-body>
            </b-collapse>
        </b-card>

        <b-card no-body class="mb-1">
            <b-card-header header-tag="header" class="p-1" role="tab">
                <b-button block v-b-toggle.user-password variant="special">Zmiana hasła</b-button>
            </b-card-header>
            <b-collapse id="user-password" accordion="user-account" role="tabpanel">
                <b-card-body>
                    <form id="password-update" style="max-width:500px" @submit="userPasswordUpdate">

                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" id="password_current" name="password_current" type="password" aria-labelledby="password-label" required>
                            <span class="mdc-floating-label" id="password-label">Obecne hasło</span>
                            <span class="mdc-line-ripple"></span>
                        </label>

                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" id="password" name="password" type="password" aria-labelledby="password-label" required autocomplete="new-password">
                            <span class="mdc-floating-label" id="password-label">Nowe hasło</span>
                            <span class="mdc-line-ripple"></span>
                        </label>

                        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                            <span class="mdc-text-field__ripple"></span>
                            <input class="mdc-text-field__input" id="password_confirmation" name="password_confirmation" type="password" aria-labelledby="password-confirm-label" required autocomplete="new-password">
                            <span class="mdc-floating-label" id="password-confirm-label">Potwierdź nowe hasło</span>
                            <span class="mdc-line-ripple"></span>
                        </label>

                        <div class="text-danger text-center mb-2" v-if="user_password_update_validation_error_msg">
                            {{user_password_update_validation_error_msg}}
                        </div>

                        <div class="text-success text-center mb-2" v-if="user_password_updated">
                            Hasło zostało zmienione, pamiętaj przy kolejnym logowaniu będziesz musiał wprowadzić nowe hasło!
                        </div>

                        <div class="text-center mb-2" v-if="user_password_show_button">
                            <button class="btn btn-success" type="submit">Zapisz</button>
                        </div>
                    </form>
                </b-card-body>
            </b-collapse>
        </b-card>

        <b-card no-body class="mb-1">
            <b-card-header header-tag="header" class="p-1" role="tab">
                <b-button block v-b-toggle.user-email-change variant="special">Zmiana adresu email</b-button>
            </b-card-header>
            <b-collapse id="user-email-change" accordion="user-account" role="tabpanel" v-if="user">
                <b-card-body>
                    <b-card-text>
                        <div v-if="!email_change_success" class="row m-0">
                            <div class="col-12 p-2 mb-2">
                                Twój obecny adres mailowy to {{user.email}}
                            </div>
                            <div v-if="user.email_new" class="col-12 text-info p-2 mb-2">
                                Wysłaliśmy do Ciebie mail, jesteś w trakcie zmiany adresu email na '{{user.email_new}}'.
                            </div>
                            <div v-if="email_change_error" class="col-12 bg-danger text-white p-2 mb-2">
                                {{email_change_error}}
                            </div>
                            <div class="col-12 pl-1 pr-1">
                                <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                                    <span class="mdc-text-field__ripple"></span>
                                    <input class="mdc-text-field__input yh-bind-key" id="user-new-email" type="text" aria-labelledby="user-new-email-label" required>
                                    <span class="mdc-floating-label" for="user-new-email" id="user-new-email-label">Nowy adres email</span>
                                    <span class="mdc-line-ripple"></span>
                                </label>
                            </div>
                            <div class="col-12 col-sm-6 pl-1 pr-1">
                                <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                                    <span class="mdc-text-field__ripple"></span>
                                    <input class="mdc-text-field__input yh-bind-key" id="user-new-email-password" type="password" aria-labelledby="user-new-email-password-label" required>
                                    <span class="mdc-floating-label" for="user-new-email-password" id="user-new-email-password-label">Hasło</span>
                                    <span class="mdc-line-ripple"></span>
                                </label>
                            </div>
                            <div class="col-12 col-sm-6 pl-1 pr-1">
                                <button v-if="!email_changing" class="btn btn-danger" v-on:click="userEmailChange()">Zmień mój adres email</button>
                                <span v-else class="btn btn-outline-warning">Proszę czekać, trwa aktualizacja...</span>
                            </div>
                        </div>
                        <div v-else class="text-success">
                            Sprawdź swoją skrzynkę mailową i potwierdź operację.
                            To może potrwać kilka minut, w razie wątpliwości sprawdź folder Spam i ewentualnie spróbuj ponownie.
                        </div>
                    </b-card-text>
                </b-card-body>
            </b-collapse>
        </b-card>

        <b-card no-body class="mb-1">
            <b-card-header header-tag="header" class="p-1" role="tab">
                <b-button block v-b-toggle.user-remove variant="special">Usuwanie Konta</b-button>
            </b-card-header>
            <b-collapse id="user-remove" accordion="user-account" role="tabpanel">
                <b-card-body>
                    <b-card-text>
                        <div class="row m-0">
                            <div class="col-12 text-warning">
                                To działanie jest nieodwracalne!
                            </div>
                            <div v-if="removal_error" class="col-12 bg-danger text-white p-2 mb-2">
                                {{removal_error}}
                            </div>
                            <div class="col-12 col-sm-6 pl-1 pr-1">
                                <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                                    <span class="mdc-text-field__ripple"></span>
                                    <input class="mdc-text-field__input yh-bind-key" id="user-removal-password" type="password" aria-labelledby="user-removal-password-label" required>
                                    <span class="mdc-floating-label" for="user-removal-password" id="user-removal-password-label">Hasło</span>
                                    <span class="mdc-line-ripple"></span>
                                </label>
                            </div>
                            <div class="col-12 col-sm-6 pl-1 pr-1">
                                <button v-if="!removing" class="btn btn-danger" v-on:click="userRemove()">Usuń moje konto</button>
                                <span v-else class="btn btn-outline-warning">Proszę czekać, trwa usuwanie konta...</span>
                            </div>
                        </div>
                    </b-card-text>
                </b-card-body>
            </b-collapse>
        </b-card>

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
            user: null,
            user_last: null,
            user_data_changed: false,
            user_data_updated: false,
            user_data_changed2: false,
            user_data_update_validation_error_msg: '',
            // potrzebna będzie walidacja dlugosci hasla - obecnie nie jest
            user_password_changed: false,
            user_password_update_validation_error_msg: '',
            user_password_updated: false,
            user_password_show_button: true,
            removing: false,
            removal_error: false,
            email_changing: false,
            email_change_error: false,
            email_change_success: false,
        };
    },
    created() {
        this.userFetch();
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
      mdc.autoInit();
      var _this = this;
      $('.yh-bind-key').keyup(function(e){
          _this.user_data_changed = true;
      });
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
        userRepeatChange(model,value) {
            var cv=0;
            switch(model) {
                case 0:
                    cv=this.user.repeat_incorrect;
                    break;
                case 1:
                    cv=this.user.repeat_1_correct;
                    break;
                case 2:
                    cv=this.user.repeat_2_correct;
                    break;
                case 3:
                    cv=this.user.repeat_3_correct;
                    break;
                case 4:
                    cv=this.user.repeat_4_correct;
                    break;
                case 5:
                    cv=this.user.repeat_5_correct;
                    break;
            }
            if (cv<2 && value<0) {
                return;
            }
            if (cv>199 && value>0) {
                return;
            }
            cv=cv+value;
            switch(model) {
                case 0:
                    this.user.repeat_incorrect=cv;
                    break;
                case 1:
                    this.user.repeat_1_correct=cv;
                    break;
                case 2:
                    this.user.repeat_2_correct=cv;
                    break;
                case 3:
                    this.user.repeat_3_correct=cv;
                    break;
                case 4:
                    this.user.repeat_4_correct=cv;
                    break;
                case 5:
                    this.user.repeat_5_correct=cv;
                    break;
            }
            this.user_data_changed2 = true;
        },
        userDataChanged() {
            this.user_data_changed = true;
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
        userUpdateAbort() {
            this.user = JSON.parse(JSON.stringify(this.user_last));
            this.user_data_changed = false;
            this.user_data_changed2 = false;
        },
        userUpdate() {
            if (this.user_data_changed) {

                this.user_data_update_validation_error_msg = '';
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
                this.user_data_changed = false;
            }
            this.user_data_changed2 = false;
            axios
                .post(
                    '/api/app/user/update',
                    {
                        hash_id: yh.auth.hash_id,
                        user: this.user,
                    }
                )
                .then(response => {
                    this.user = JSON.parse(JSON.stringify(response.data));
                    this.user_last = JSON.parse(JSON.stringify(response.data));
                    this.user_data_updated = true;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        userPasswordUpdate(e) {
            e.preventDefault();
            this.user_password_updated = false;
            this.user_password_show_button = false;
            this.user_password_update_validation_error_msg = '';
            axios
                .post(
                    '/api/app/user/password',
                    {
                        hash_id: yh.auth.hash_id,
                        password_current: $('#password_current').val(),
                        password: $('#password').val(),
                        password_confirmation: $('#password_confirmation').val(),
                    }
                )
                .then(response => {
                    this.user_password_show_button = true;
                    $('#password_current').val('');
                    $('#password').val('');
                    $('#password_confirmation').val('');
                    if (response.data.error) {
                        this.user_password_update_validation_error_msg = response.data.error;
                        return;
                    }
                    this.user_password_updated = true;
                }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            return;
        },
        userRemove() {
            this.removing = true;
            this.removal_error = false;
            var pass = $('#user-removal-password').val();
            axios
                .post(
                    '/api/app/user/remove',
                    {
                        hash_id: yh.auth.hash_id,
                        password: pass,
                    }
                )
                .then(response => {
                    document.location = '/';
                }).catch(error => {
                    this.removing = false;
                    this.removal_error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        email_valid(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        },
        userEmailChange() {
            var pass = $('#user-new-email-password').val();
            var email = $('#user-new-email').val();
            if (!this.email_valid(email)) {
                this.email_change_error = 'Nieporawny format adresu email';
                return;
            }
            this.email_changing = true;
            this.email_change_error = false;

            axios
                .post(
                    '/api/app/user/email',
                    {
                        hash_id: yh.auth.hash_id,
                        password: pass,
                        email: email,
                    }
                )
                .then(response => {
                    this.email_changing = false;
                    this.email_change_success = true;
                }).catch(error => {
                    this.email_changing = false;
                    this.email_change_error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });

        }
    }
}
</script>
