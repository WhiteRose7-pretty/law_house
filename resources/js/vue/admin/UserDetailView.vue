
<template>
    <div class="ius">
        <h2 class="mb-3">
            User Detail Information
        </h2>

        <div v-if="error" class="text-danger">
            {{ error }}
        </div>

        <div>
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a href="#tab-01" aria-controls="tab-01" role="tab" data-toggle="tab" class="active">Customer Data</a></li>
                <li role="presentation"><a href="#tab-02" aria-controls="tab-02" role="tab" data-toggle="tab">Ordered Packages</a></li>
                <li role="presentation"><a href="#tab-03" aria-controls="tab-03" role="tab" data-toggle="tab">Used Discounts</a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active p-2 mt-2" id="tab-01">
                    <div class="card">
                        <div class="card-body p-0 border-0">
                            <table class="table table-bordered mb-0">
                                <tr><td>id:</td><td>{{ id }}</td></tr>
                                <tr><td>email:</td><td>{{ email }}</td></tr>
                                <tr><td>name:</td><td>{{ name }}</td></tr>
                                <tr><td>last name:</td><td>{{ surname }}</td></tr>
                                <tr><td>Date of Registration:</td><td>{{ dateF(email_verified_at) }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <button class="btn btn-success text-white mt-2" v-on:click="toggleForm()">
                        Send Email to Customer</button>
                    <div class="pt-2" v-if="email_form_show">
                        <b-form-input class="mt-2" placeholder="subject for email" v-model="email_subject"></b-form-input>
                        <b-form-textarea class="mt-2" placeholder="content for email" v-model="email_content"></b-form-textarea>
                        <button class="btn btn-success text-white mt-2" v-on:click="send()">Send</button>
                        <button class="btn btn-danger text-white mt-2" v-on:click="cancelForm()">Cancel</button>
                        <p class="text-danger mt-2">{{ form_error }}</p>
                    </div>

                </div>
                <div role="tabpanel" class="tab-pane p-2" id="tab-02">
                    <b-row class="mb-3">
                        <b-col md="3">
                            <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Type to Search"></b-form-input>
                        </b-col>
                    </b-row>
                    <div class="card">
                        <div class="card-body p-0 border-0">
                            <b-table class="mb-0" id="my-table" striped hover outlined :per-page="perPage" :current-page="currentPage" :items="user_packages" :fields="fields" :filter="filter">
                                <template v-slot:cell(Name)="data">
                                    {{data.item.name}}
                                </template>
                                <template v-slot:cell(Package_Id)="data">
                                    {{data.item.package_id}}
                                </template>
                                <template v-slot:cell(Ordered_At)="data">
                                    {{dateF(data.item.ordered_at)}}
                                </template>
                                <template v-slot:cell(Price)="data">
                                    {{data.item.price}}
                                </template>
                                <template v-slot:cell(Months)="data">
                                    {{data.item.type}}
                                </template>
                                <template v-slot:cell(Valid_Until)="data">
                                    {{dateF(data.item.valid_until)}}
                                </template>
                                <template v-slot:cell(Status)="data">
                                    <div v-html="status(data.item.valid_until)"></div>
                                </template>


                            </b-table>
                        </div>
                    </div>
                    <p class="mt-3 float-left">Current Page: {{ currentPage }}</p>
                    <b-pagination class="float-right"
                                  v-model="currentPage"
                                  :total-rows="rows"
                                  :per-page="perPage"
                                  aria-controls="my-table"
                    ></b-pagination>
                </div>

                <div role="tabpanel" class="tab-pane p-2" id="tab-03">
                    <div class="card">
                        <div class="card-body p-0 border-0">
                    <b-table class="mb-0" id="my-table" striped hover outlined :items="user_transactions" :fields="transaction_fields">
                        <template v-slot:cell(Code)="data">
                            {{data.item.discount_code}}
                        </template>
                        <template v-slot:cell(Discount)="data">
                            {{data.item.discount}}
                        </template>
                        <template v-slot:cell(Paid_At)="data">
                            {{dateF(data.item.verified_at)}}
                        </template>
                        <template v-slot:cell(Package_Name)="data">
                            {{data.item.user_package.name }}
                        </template>
                        <template v-slot:cell(Package_Id)="data">
                            {{data.item.user_package.package_id }}
                        </template>
                        <template v-slot:cell(Months)="data">
                            {{data.item.user_package.type }}
                        </template>
                    </b-table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

<script>
    import axios from "axios";

    export default {
        data(){
            return {
                error: null,
                loading: false,
                date: null,
                content_string: '',
                user_packages: [],
                user_transactions: [],
                filter: "",
                currentPage: 1,
                perPage: 10,
                fields: ["Name", "Package_Id", "Ordered_At", "Months", "Valid_Until", "Status"],
                transaction_fields: ["Code", "Discount", "Paid_At", "Package_Name", "Package_Id", "Months"],
                email_form_show: false,
                email_subject: '',
                email_content: '',
                form_error: '',
            }
        },
        props: ['id', 'email', 'email_verified_at', 'name', 'surname', 'type'],
        mounted() {
            this.fetchUserPackages()
            this.fetchUserTransactions()
        },
        computed: {
            rows(){
                if(this.user_packages){
                    return this.user_packages.length;
                }
                else{
                    return 0;
                }
            },
        },
        methods:{
            toggleForm(){
                this.email_form_show = true;
                this.form_error = ''
            },
            cancelForm(){
                this.email_form_show = false;
                this.form_error = ''
            },
            send(){
                if(!this.email_subject){
                    this.form_error = "Please input email subject!"
                    return false;
                }
                if(!this.email_content){
                    this.form_error = "Please input email content!"
                    return false;
                }
                this.form_error = 'Sending...'
                this.error = null;
                this.loading = true;
                axios
                    .post(
                        '/api/admin/send-email',
                        {
                            hash_id: yh.auth.hash_id,
                            user_id: this.id,
                            email: this.email,
                            subject: this.email_subject,
                            content: this.email_content,
                        }
                    )
                    .then(response => {
                        this.form_error = 'Success!'
                        this.loading = false;
                        this.email_subject = '';
                        this.email_content = '';
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });

            },
            fetchUserPackages() {
                this.error = null;
                this.loading = true;
                axios
                    .post(
                        '/api/admin/user-packages',
                        {
                            hash_id: yh.auth.hash_id,
                            user_id: this.id,
                        }
                    )
                    .then(response => {
                        this.user_packages = response.data;
                        this.loading = false;
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            fetchUserTransactions() {
                this.error = null;
                this.loading = true;
                axios
                    .post(
                        '/api/admin/user-transactions',
                        {
                            hash_id: yh.auth.hash_id,
                            user_id: this.id,
                        }
                    )
                    .then(response => {
                        this.user_transactions = response.data;
                        this.loading = false;
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            dateF(s) {
                if(s){
                    if (s.includes('T')) {
                        var r = s.split('T');
                    } else {
                        var r = s.split(' ');
                    }
                    var s2 = r[0];
                    s2 = this.replaceAll(s2,'-','/');
                    return s2;
                }
                return ''

            },
            replaceAll(string, search, replace) {
                return string.split(search).join(replace);
            },
            status(valid_until){
                const current = new Date()
                const end_date = new Date(valid_until)
                if (current < end_date){
                    return `<div class="btn btn-success">Active</div>`
                }else{
                    return `<button class="btn btn-danger">Expired</button>`
                }
            }
        }
    }
</script>
