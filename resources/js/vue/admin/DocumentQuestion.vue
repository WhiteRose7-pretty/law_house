<template>
    <div class="p-4">
        <h3>{{title}}</h3>
        <div class="card mt-2" v-if="set.id>0">
            <div class="card-body position-relative">

                <div class="mb-1 clearfix">
                    <input id="fetch-query" placeholder="Szukaj" class="pl-1" v-model="query" autofocus/>
                    <div class="float-right">
                        Limit:
                        <select v-model="limit" v-on:change="questionsFetchLimit()">
                            <option value="5" >5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                </div>

                <table v-if="pages > 0" class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Pytanie</th>
                        <th scope="col">zestawu</th>
                        <th scope="col">Ost. Aktualizacja</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="r in list" v-bind:data-id="r.id" class="question-row">
                        <th scope="row">{{r.id}}</th>
                        <td>{{r.question}}
                            <i class="fas fa-exclamation text-danger" v-if="r.old_status" aria-hidden="true"></i>
                            <i class="fa fa-clock-o text-danger" v-if="r.out_date" aria-hidden="true"></i>
                        </td>
                        <td>{{r.questions_set.name}}
                        </td>
                        <td>{{dateF(r.updated_at)}}</td>
                    </tr>
                    </tbody>
                </table>
                <table v-else class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Pytanie</th>
                        <th scope="col">zestawu</th>
                        <th scope="col">Ost. Aktualizacja</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="4">brak pasujących wyników</td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="pages > 1" class="float-left">
                    Strona:
                    <select v-model="page" v-on:change="questionsFetchPager()">
                        <option v-for="p in pages" :value="p">{{p}}</option>
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
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        props: ['title', 'element_id'],
        data() {
            return {
                loading: false,
                loading_documents: false,
                error: null,
                set: {
                    id: 0,
                    name: '',
                    group: '',
                    edit_on: true,
                },
                legal_element:{
                    id: 0,
                },
                question: {
                    edit_on: false,
                },
                question_clean: {
                    edit_on: false,
                    id: 0,
                    question: '',
                    errors: '',
                    legal_basis_text: '',
                    correct: '',
                    options: [
                        {
                            id:0,
                            option: '',
                            correct: false,
                        },
                        {
                            id:0,
                            option: '',
                            correct: false,
                        },
                        {
                            id:0,
                            option: '',
                            correct: false,
                        },
                    ],
                    laws_count: 0,
                    laws:[],
                },
                laws_clean: [
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                    {
                        law_document_id:0,
                        law_document_element_id:0,
                    },
                ],
                list: null,
                count: 0,
                query: '',
                limit: 5,
                pages: 0,
                page: 1,
                documents: [],
            };
        },
        created() {
            this.set.id = 1;
            this.legal_element.id = this.element_id;
        },
        mounted() {
            if (this.set.id > 0) {
                this.setFetch();
            }
            this.documentsFetch();
            var _this=this;
            $('#fetch-query').keyup(function(e){
                var code = e.key;
                if (code == 'Enter') {
                    e.preventDefault();
                    _this.questionsFetchQuery();
                }
            });
        },
        updated() {
            mdc.autoInit();
        },
        methods: {
            dateF(s) {
            return moment(s).local().format('YYYY-MM-DD HH:mm:ss');
        },
            documentsElements(id) {
                for(var i=0;i<this.documents.length;i++) {
                    if (this.documents[i].id == id) {
                        return this.documents[i].elements;
                    }
                }
            },
            documentLabel(id) {
                for(var i=0;i<this.documents.length;i++) {
                    if (this.documents[i].id == id) {
                        return this.documents[i].label;
                    }
                }
            },
            documentElementLabel(ldi,lei) {
                for(var i=0;i<this.documents.length;i++) {
                    if (this.documents[i].id != ldi) {
                        continue;
                    }
                    for(var j=0;this.documents[i].elements.length;j++) {
                        if (this.documents[i].elements[j].id != lei) {
                            continue;
                        }
                        return this.documents[i].elements[j].label;
                    }
                }
            },
            documentElementContent(ldi,lei) {
                for(var i=0;i<this.documents.length;i++) {
                    if (this.documents[i].id != ldi) {
                        continue;
                    }
                    for(var j=0;this.documents[i].elements.length;j++) {
                        if (this.documents[i].elements[j].id != lei) {
                            continue;
                        }
                        return this.documents[i].elements[j].content;
                    }
                }
            },
            documentsFetch() {
                this.error = null;
                this.loading_documents = true;
                axios
                    .post(
                        '/api/admin/documents/all',
                        {
                            hash_id: yh.auth.hash_id,
                        }
                    )
                    .then(response => {
                        this.documents = response.data.results;
                        this.loading_documents = false;
                    }).catch(error => {
                    this.loading_documents = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            setCreate() {
                this.set.edit_on=false;
                axios
                    .post(
                        '/api/admin/questions/sets/update',
                        {
                            hash_id: yh.auth.hash_id,
                            set: this.set,
                        }
                    )
                    .then(response => {
                        this.set.id=response.data.id;
                        this.$router.push({ name: 'questions.edit', params: { id: response.data.id } });
                        // this.elementsFetch();
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            setEditOn(o) {
                this.set.edit_on=true;
            },
            setFetch() {
                this.loading = true;
                this.set.edit_on=false;
                axios
                    .post(
                        '/api/admin/questions/set',
                        {
                            hash_id: yh.auth.hash_id,
                            id: this.set.id,
                        }
                    )
                    .then(response => {
                        this.set.name=response.data.name;
                        this.set.group=response.data.group;
                        this.loading = false;
                        this.questionsFetch();
                    }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            setUpdate() {
                if (this.set.id==0) {
                    return this.setCreate();
                }
                this.set.edit_on = false;
                axios
                    .post(
                        '/api/admin/questions/sets/update',
                        {
                            hash_id: yh.auth.hash_id,
                            set: this.set,
                        }
                    )
                    .then(response => {
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            questionsFetch() {
                this.error = this.list = null;
                this.loading = true;
                this.pages = 0;
                axios
                    .post(
                        '/api/admin/questions',
                        {
                            hash_id: yh.auth.hash_id,
                            questions_set_id: '',
                            legal_element_id: this.legal_element.id,
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
                    }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            questionsFetchLimit() {
                this.page = 1;
                this.questionsFetch();
            },
            questionsFetchPager() {
                this.questionsFetch();
            },
            questionsFetchQuery() {
                this.page = 1;
                this.questionsFetch();
            },
            questionRemove(id) {
                if (confirm('Czy na pewno chcesz usunąć to pytanie?')) {
                    axios
                        .post(
                            '/api/admin/question/remove',
                            {
                                hash_id: yh.auth.hash_id,
                                id: id
                            }
                        )
                        .then(response => {
                            $('.question-row[data-id="'+id+'"]').remove();
                            var _this = this;
                            setTimeout(function(){
                                _this.questionsFetch();
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
            questionAdd() {
                this.question = JSON.parse(JSON.stringify(this.question_clean));
                this.question.laws = JSON.parse(JSON.stringify(this.laws_clean));
                this.question.edit_on = true;
                this.error = '';
            },
            questionEdit(model) {
                var laws=JSON.parse(JSON.stringify(model.laws));
                this.question = JSON.parse(JSON.stringify(model));
                if (this.question.options[0].correct) {
                    this.question.correct = this.question.options[0].id;
                }
                if (this.question.options[1].correct) {
                    this.question.correct = this.question.options[1].id;
                }
                if (this.question.options[2].correct) {
                    this.question.correct = this.question.options[2].id;
                }
                this.question.edit_on = true;
                this.question.laws = JSON.parse(JSON.stringify(this.laws_clean));
                for(var i=0;i<laws.length;i++) {
                    this.question.laws[i] = laws[i];
                }
                this.error = '';
            },
            questionUpdate() {
                if (this.question.correct=='') {
                    this.error = 'Wybierz odpowiedź';
                    return;
                }
                if (this.question.id) {
                    for(var i=0;i<3;i++) {
                        if (this.question.correct==this.question.options[i].id) {
                            this.question.options[i].correct=1;
                        } else {
                            this.question.options[i].correct=0;
                        }
                    }
                } else {
                    for(var i=0;i<3;i++) {
                        if (this.question.correct == i+1) {
                            this.question.options[i].correct=1;
                        } else {
                            this.question.options[i].correct=0;
                        }
                    }
                }
                this.question.edit_on = false;
                axios
                    .post(
                        '/api/admin/question/update',
                        {
                            hash_id: yh.auth.hash_id,
                            questions_set_id: this.set.id,
                            question: this.question,
                        }
                    )
                    .then(response => {
                        this.questionsFetch();
                    }).catch(error => {
                    this.loading = false;
                    this.question.edit_on = true;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
            },
            questionUpdateAbort() {
                this.question = JSON.parse(JSON.stringify(this.question_clean));
            },
        },
    }
</script>
