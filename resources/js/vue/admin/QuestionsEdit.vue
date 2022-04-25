<template>
<div class="ius">
    <h2 class="mb-3" v-if="set.id==0">
        Nowy zestaw pytań
    </h2>
    <h2 class="mb-3" v-else>
        Edycja zestawu pytań
    </h2>

    <div class="card">
        <div class="card-body">

            <div v-if="loading" class="text-info">
                Ładowanie...
            </div>

            <div v-if="error" class="text-danger">
                {{ error }}
            </div>

            <div v-if="set.edit_on">
                <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                    <span class="mdc-text-field__ripple"></span>
                    <input class="mdc-text-field__input yh-bind-key" name="name" id="d-name" type="text" aria-labelledby="d-name-label" required v-model="set.name" autofocus>
                    <span class="mdc-floating-label" id="d-name-label">Nazwa</span>
                    <span class="mdc-line-ripple"></span>
                </label>

                <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                    <span class="mdc-text-field__ripple"></span>
                    <input class="mdc-text-field__input yh-bind-key" name="name" id="d-name" type="text" aria-labelledby="d-name-label" required v-model="set.group" autofocus>
                    <span class="mdc-floating-label" id="d-name-label">Grupa</span>
                    <span class="mdc-line-ripple"></span>
                </label>

                <div class="text-center mb-2">
                    <button class="btn btn-success" v-on:click="setUpdate()">Zapisz</button>
                </div>
            </div>
            <div v-else>

              <div class="text-center float-right">
                  <button class="btn btn-success" v-on:click="setEditOn()">Zmień</button>
              </div>
              <h1>{{set.name}}</h1>
              <h2>{{set.group}}</h2>
            </div>
        </div>
    </div>

    <div class="card mt-2" v-if="set.id>0">
        <div class="card-body position-relative">

            <div class="p-3 mb-4" style="background-color: #f1f1f1;">
                <strong class="yh-fs-15 mr-2">Lista pytań:</strong>
                <a class="btn btn-success btn-sm text-white" v-on:click="questionAdd()">Dodaj</a>
            </div>

            <div class="mb-1 clearfix">
                <input id="fetch-query" placeholder="Szukaj" class="pl-1" v-model="query" autofocus/>
                <div class="float-right">
                    Limit:
                    <select v-model="limit" v-on:change="questionsFetchLimit()">
                        <option value="5" >5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <table v-if="pages > 0" class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Pytanie</th>
                        <th scope="col">Ost. Aktualizacja</th>
                        <th scope="col">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in list" v-bind:data-id="r.id" class="question-row">
                        <th scope="row">{{r.id}}</th>
                        <td>{{r.question}}
                            <i class="fas fa-exclamation text-danger" v-if="r.old_status" aria-hidden="true"></i>
                            <i class="fa fa-clock-o text-danger" v-if="r.after_date" aria-hidden="true"></i>
                            <i class="fa fa-clock-o text-success" v-if="r.before_date" aria-hidden="true"></i>
                        </td>
                        <td>{{dateF(r.updated_at)}}</td>
                        <td class="text-nowrap">
                            <button v-on:click="questionEdit(r)" class="btn btn-sm btn-success">Edytuj</button>
                            <button v-on:click="questionRemove(r.id)" class="btn btn-sm btn-danger">Usuń</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table v-else class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Pytanie</th>
                        <th scope="col">Ost. Aktualizacja</th>
                        <th scope="col">Akcje</th>
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

    <div class="card mt-2" v-if="question.edit_on" ref="question-editor" id="question-editor">
        <div class="card-body position-relative">
            <div v-if="error" class="text-danger">
                {{ error }}
            </div>
            <div v-if="loading_documents" class="bg-info text-white p-1">
                Poczekaj na załadowanie bazy podstawy prawnej do edytora pytań! <br/>
                Jeśli podstawa jest na nowo budowana (ze względu na zmiany), może to potrwać minutę albo dłużej.
            </div>
            <div class="row m-3">
                <input type="text" v-model="question.question" placeholder="Pytanie" class="w-100 p-2">
            </div>
            <div class="row m-3">
                <div class="col-1 text-right">
                    <input type="radio" name="correct" v-model="question.correct" v-bind:value="question.options[0].id > 0 ? question.options[0].id : 1" class="mt-3">
                </div>
                <div class="col-11">
                    <input class="p-2 w-100" type="text" v-model="question.options[0].option" placeholder="odpowiedź 1">
                </div>
            </div>
            <div class="row m-3">
                <div class="col-1 text-right">
                    <input type="radio" name="correct" v-model="question.correct" v-bind:value="question.options[1].id > 0 ? question.options[1].id : 2" class="mt-3">
                </div>
                <div class="col-11">
                    <input class="p-2 w-100" type="text" v-model="question.options[1].option" placeholder="odpowiedź 2">
                </div>
            </div>
            <div class="row m-3">
                <div class="col-1 text-right">
                    <input type="radio" name="correct" v-model="question.correct" v-bind:value="question.options[2].id > 0 ? question.options[2].id : 3"  class="mt-3">
                </div>
                <div class="col-11">
                    <input class="p-2 w-100" type="text" v-model="question.options[2].option" placeholder="odpowiedź 3">
                </div>
            </div>
            <hr style="margin:10px; border-top:1px #ddd dashed; height:1px;">
            <label>Podstawa prawna:</label>
            <select v-model="question.laws_count">
                <option value="0">opisowa</option>
                <option v-for="i in 30" v-bind:value="i" >{{i}}</option>
            </select>
            <div v-if="question.laws_count > 0">
                <hr style="margin:10px; border-top:1px #ddd dashed; height:1px;">
                <div v-for="i in question.laws_count">
                    <select v-model="question.laws[i-1].law_document_id" style="width: 100%">
                        <option value="0">wybierz</option>
                        <option v-for="d in documents" v-bind:value="d.id" style="width: 100%">{{d.label}}</option>
                    </select>
                    <select v-model="question.laws[i-1].law_document_element_id" v-if="question.laws[i-1].law_document_id>0">
                        <option value="0">wybierz</option>
                        <option v-for="e in documentsElements(question.laws[i-1].law_document_id)" v-bind:value="e.id">{{e.label}}</option>
                    </select>

                    <label class="mdc-text-field mdc-text-field--filled mt-2" data-mdc-auto-init="MDCTextField">
                        <span class="mdc-text-field__ripple"></span>
                        <input class="mdc-text-field__input yh-bind-key" name="start_at" id="e-start-at" type="date" aria-labelledby="e-start-at-label" v-model="question.laws[i-1].start_date" autofocus>
                        <span class="mdc-floating-label" id="e-start-at-label">Data Początkowa</span>
                        <span class="mdc-line-ripple"></span>
                    </label>

                    <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                        <span class="mdc-text-field__ripple"></span>
                        <input class="mdc-text-field__input yh-bind-key" name="end_at" id="e-end-at" type="date" aria-labelledby="e-end-at-label" v-model="question.laws[i-1].end_date" autofocus>
                        <span class="mdc-floating-label" id="e-end-at-label">Data Końcowa</span>
                        <span class="mdc-line-ripple"></span>
                    </label>
                </div>
                <hr style="margin:10px; border-top:1px #ddd dashed; height:1px;">
                <div v-for="i in question.laws_count">
                    <div v-if="question.laws[i-1].law_document_id>0 && question.laws[i-1].law_document_element_id>0">
                        <strong>
                            {{documentLabel(question.laws[i-1].law_document_id)}},
                            {{documentElementLabel(question.laws[i-1].law_document_id,question.laws[i-1].law_document_element_id)}}
                        </strong>
                        <p>
                          {{documentElementContent(question.laws[i-1].law_document_id,question.laws[i-1].law_document_element_id)}}
                        </p>
                    </div>
                </div>
            </div>
            <div v-else>
                <textarea class="w-100 p-2" placeholder="Lub wpisz" v-model="question.legal_basis_text">{{this.question.legal_basis_text}}</textarea>
            </div>

            <textarea class="w-100 p-2" placeholder="write comment" v-model="question.help_text">{{this.question.help_text}}</textarea>
            <hr style="margin:10px; border-top:1px #ddd dashed; height:1px;">

            <div class="text-center">
                <button class="btn btn-warning" v-on:click="questionUpdateAbort()">Anuluj</button>
                <button class="btn btn-success" v-on:click="questionUpdate()">Zapisz</button>
            </div>
        </div>
        <div class="text-danger" v-if="question.errors">
            {{question.errors}}
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
            loading_documents: false,
            error: null,
            set: {
                id: 0,
                name: '',
                group: '',
                edit_on: true,
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
                help_text: '',
            },
            laws_clean: [
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,

              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
              {
                  law_document_id:0,
                  law_document_element_id:0,
                  start_date: null,
                  end_date: null,
              },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,

                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,

                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
                {
                    law_document_id:0,
                    law_document_element_id:0,
                    start_date: null,
                    end_date: null,
                },
            ],
            list: null,
            count: 0,
            query: '',
            limit: 50,
            pages: 0,
            page: 1,
            documents: [],
            scroll_object: null,
        };
    },
    created() {
        this.set.id = this.$route.params.id;
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
        this.scroll_object = document.getElementsByClassName('ps')[0];
    },
    updated() {
        mdc.autoInit();
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
                        questions_set_id: this.set.id,
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
            this.scrollMeTo('question-editor');
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
