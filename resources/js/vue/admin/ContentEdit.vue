<template>
<div class="ius">
    <h2 class="mb-3" v-if="content.id==0">
        Nowa treść w kategorii: {{categories[content.category]}}
    </h2>
    <h2 class="mb-3" v-else>
        Edycja Treści
    </h2>

    <div class="card">
        <div class="card-body">

            <div v-if="loading" class="text-info">
                Ładowanie...
            </div>

            <div v-if="error" class="text-danger">
                {{ error }}
            </div>

            <div v-if="content.id==0" class="text-success mb-1">
                Zacznij od podania tytułu, będziesz mógł go później zmienić
            </div>

            <div v-if="content.published_at" class="text-danger">
                Modyfikujesz już opublikowaną treść, zmiany zapisywane, pojawią się automatycznie.
            </div>

            <div v-if="content.id>0" class="mb-1">
                &nbsp;
                <span v-if="data_changed" class="text-info">Masz zmiany...</span>
                <span v-if="data_updating" class="text-warning">Automatyczny zapis</span>
                <span v-if="data_updated" class="text-success">Zaktualizowano</span>
            </div>

            <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                <span class="mdc-text-field__ripple"></span>
                <input class="mdc-text-field__input yh-bind-key" name="title" id="title" type="text" aria-labelledby="title-label" required v-model="content.title" v-on:keyup="dataChanged()" autofocus>
                <span class="mdc-floating-label" id="title-label">Tytuł</span>
                <span class="mdc-line-ripple"></span>
            </label>

            <div v-if="content.id>0 && content.category != 'info'" class="mb-2">
                <div v-if="content.image" class="position-relative">
                    <img  v-bind:src="content.image" style="width:100%" />
                    <div class="position-absolute text-white" style="font-weight:600; font-size:30px; top:10px; right:10px; z-index: 1000; cursor:pointer;" v-on:click="imageRemove()">
                        X
                    </div>
                </div>
                <div v-else>
                    <input type="file" accept="image/*" v-on:change="imageUpload($event)" id="file-input" v-if="!image_uploading">
                    <div class="text-info" v-else>wgrywam obrazek...</div>
                </div>

            </div>

            <label v-if="content.id>0 && content.category == 'news'" class="mdc-text-field mdc-text-field--textarea mdc-text-field--no-label">
                <textarea class="mdc-text-field__input" v-model="content.lead" rows="5" cols="40" placeholder="akapit wstępu" v-on:keyup="dataChanged()"></textarea>
                <span class="mdc-notched-outline">
                    <span class="mdc-notched-outline__leading"></span>
                    <span class="mdc-notched-outline__trailing"></span>
                </span>
            </label>

            <div class="mb-2">
                <editor
                    v-if="content.id>0"
                    v-model="content.full"
                    api-key="8v9zhxt09utqwde3nrfxoblncupa6q2dss38htt8ims6xkk5"
                    :init="{
                    init_instance_callback: function(editor) {
                        editor.on('keyup', function(e) {
                          dataChanged();
                        });
                        editor.on('change', function(e) {
                          dataChanged();
                        });
                    },
                    height: 500,
                    menubar: ['format', 'table'],
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount', 'autoresize', 'emoticons', 'importcss', 'table'
                    ],
                    toolbar:
                        'undo redo | formatselect | fontsizeselect | bold italic underline forecolor backcolor emoticons | \
                        alignleft aligncenter alignright alignjustify | \
                        bullist numlist outdent indent | removeformat | help',
                    content_css: '/admin/custom-styles.css'
                }"/>
            </div>

            <div v-if="data_updated_on_request" class="text-success mt-2 mb-2">Zaktualizowano</div>
            <div class="text-center mb-2" v-if="data_changed && content.id>0">
                <button class="btn btn-warning" v-on:click="dataAbort()">Anuluj</button>
            </div>
            <div class="text-center mb-2" v-if="data_changed">
                <button class="btn btn-success" v-on:click="dataUpdate(true)">Zapisz</button>
            </div>
            <div class="text-center mb-2" v-if="!data_changed && content.full != ''">
                <a class="btn btn-success" v-bind:href="viewLink()" target="_blank">Podgląd</a>
            </div>

        </div>
    </div>

</div>
</template>
<script>
import Editor from '@tinymce/tinymce-vue'
import axios from 'axios';
export default {
    data() {
        return {
            loading: false,
            error: null,
            categories: {
                news: "Aktualności",
                promo: "Promocyjne",
                info: "Stałe",
                regula: "Przepisy prawne",
            },
            categories_paths: {
                news: '/aktualnosci/',
                promo: '/czytaj/',
                info: '/informacje/',
                regula: '/przepisy-prawne/'
            },
            content: {
                id: 0,
                category:null,
                image: false,
                title: '',
                title_uri: '',
            },
            image_uploading: false,
            data_changed: false,
            data_updating: false,
            data_updated: false,
            data_updated_on_request: false,
        };
    },
    components: {
      'editor': Editor
    },
    created() {
        this.content.category = this.$route.params.category;
        this.content.id = this.$route.params.id;
    },
    mounted() {
        if (this.content.id > 0) {
            this.dataFetch();
            this.data_changed=false;
        }
    },
    updated() {
        mdc.autoInit();
    },
    methods: {
        dataAbort() {
            this.data_changed=false;
            if (confirm('Treść zostanie cofnięta do ostatniego zapisu. Czy na pewno chcesz kontynuować?')) {
                this.dataFetch();
            }
        },
        dataChanged() {
            this.data_updated_on_request = false;
            this.data_changed = true;
        },
        dataCreate() {
            $('#title').attr('disabled',true);
            this.data_changed = false;
            axios
                .post(
                    '/api/admin/content/update',
                    {
                        hash_id: yh.auth.hash_id,
                        content: this.content,
                    }
                )
                .then(response => {
                    this.content.id = response.data.id;
                    $('#title').attr('disabled',false);
                    this.$router.push({ name: 'content.edit', params: { category: response.data.category, id: response.data.id } });
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        dataFetch() {
          this.loading = true;
          axios
              .post(
                  '/api/admin/content',
                  {
                      hash_id: yh.auth.hash_id,
                      id: this.content.id,
                      category: this.content.category,
                  }
              )
              .then(response => {
                  this.content.title=response.data.title;
                  this.content.lead=response.data.lead;
                  this.content.full=response.data.full;
                  this.content.title_uri=response.data.title_uri;
                  this.content.published_at=response.data.published_at
                  if ('image' in response.data) {
                      this.content.image = response.data.image;
                  }
                  this.loading = false;
              }).catch(error => {
                  this.error = error.response.data.message || error.message;
                  if (error.response.data.location) {
                      setTimeout(function(){document.location = error.response.data.location;},2000);
                  }
              });
        },
        dataUpdate(onrequest=false) {
            if (this.content.id==0) {
                return this.dataCreate();
            }
            this.data_changed = false;
            axios
                .post(
                    '/api/admin/content/update',
                    {
                        hash_id: yh.auth.hash_id,
                        content: this.content,
                    }
                )
                .then(response => {
                    this.content.title_uri=response.data.title_uri;
                    this.data_updated_on_request = true;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        imageUpload(e) {
            this.image_uploading = true;
            let data = new FormData();
            data.append('image', e.target.files[0]);
            data.append('hash_id', yh.auth.hash_id );
            data.append('id', this.content.id );
            axios
                .post(
                    '/api/admin/content/image/upload',
                    data,
                )
                .then(response => {
                    this.image_uploading = false;
                    this.content.image = response.data.image;
                }).catch(error => {
                    this.image_uploading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        imageRemove() {
            if(!confirm('Czy na pewno chcesz usunąć obrazek?')) {
                return;
            }
            axios
                .post(
                    '/api/admin/content/image/remove',
                    {
                        hash_id: yh.auth.hash_id,
                        id: this.content.id
                    }
                )
                .then(response => {
                    this.content.image = false;
                }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
        viewLink() {
            return this.categories_paths[this.content.category]+this.content.title_uri;
        },
    },
}
</script>
