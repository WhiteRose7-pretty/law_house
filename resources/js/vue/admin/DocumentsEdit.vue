<template>
    <div class="ius">
        <h2 class="mb-3" v-if="document.id==0">
            Nowy dokument
        </h2>
        <h2 class="mb-3" v-else>
            Edycja dokumentu
        </h2>

        <div class="card">
            <div class="card-body">

                <div v-if="loading" class="text-info">
                    Ładowanie...
                </div>

                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>

                <div v-if="document.edit_on">
                    <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                        <span class="mdc-text-field__ripple"></span>
                        <input class="mdc-text-field__input yh-bind-key" name="name" id="d-name" type="text"
                               aria-labelledby="d-name-label" required v-model="document.name" autofocus>
                        <span class="mdc-floating-label" id="d-name-label">Nazwa</span>
                        <span class="mdc-line-ripple"></span>
                    </label>

                    <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                        <span class="mdc-text-field__ripple"></span>
                        <input class="mdc-text-field__input yh-bind-key" name="identifier" id="d-identifier" type="text"
                               aria-labelledby="d-identifier-label" required v-model="document.identifier" autofocus>
                        <span class="mdc-floating-label" id="d-identifier-label">Identyfikator</span>
                        <span class="mdc-line-ripple"></span>
                    </label>

                    <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField">
                        <span class="mdc-text-field__ripple"></span>
                        <input class="mdc-text-field__input yh-bind-key" name="signed_at" id="d-signed-at" type="date"
                               aria-labelledby="d-signed-at-label" required v-model="document.signed_at" autofocus>
                        <span class="mdc-floating-label" id="d-signed-at-label">Data</span>
                        <span class="mdc-line-ripple"></span>
                    </label>

                    <label class="mdc-text-field mdc-text-field--textarea mdc-text-field--no-label">
                        <textarea class="mdc-text-field__input" v-model="document.info" rows="5" cols="40"
                                  placeholder="informacje"></textarea>
                        <span class="mdc-notched-outline">
                            <span class="mdc-notched-outline__leading"></span>
                            <span class="mdc-notched-outline__trailing"></span>
                        </span>
                    </label>

                    <div class="text-center mb-2">
                        <button class="btn btn-warning" v-on:click="documentUpdateAbort()">Anuluj</button>
                        <button class="btn btn-success" v-on:click="documentUpdate()">Zapisz</button>
                    </div>
                </div>
                <div v-else>

                    <div class="text-center float-right d-none">
                        <button class="btn btn-success" v-on:click="documentView()">Widok</button>
                        <button class="btn btn-success" v-on:click="documentEditOn()">Zmień</button>
                        <button class="btn btn-success" v-on:click="documentDownload()">download</button>
                    </div>
                    <h1>{{document.name}}</h1>
                    <h2>{{document.identifier}}</h2>
                    <h3>{{document.signed_at}}</h3>
                    <p>{{document.info}}</p>

                </div>
            </div>
        </div>

        <div class="card mt-2" v-if="document.id>0">
            <div class="card-body position-relative">
                <div id="elements-tree">
                    <v-jstree :data="elements">
                        <template slot-scope="_">
                            <div>
                                <i :class="_.vm.themeIconClasses" role="presentation" v-if="!_.model.loading"></i>
                                <span v-if="_.model.options.click" v-on:click="call(_.model.options.click,_.model,_)">
                                {{_.model.text}}
                                </span>
                                <span v-else v-on:click="elementToggle(_.model)">
                                {{_.model.text}}
                                </span>
                                <button style="border: 0; background-color: transparent; cursor: pointer;"
                                        class="text-primary" @click="elementEdit( _.model, _, false)"><i
                                    class="fa fa-microphone"></i></button>
                                <button style="border: 0; background-color: transparent; cursor: pointer;"
                                        v-if="_.model.data.audio_url"
                                        class="text-success" @click="play_audio(_.model.data.audio_url)"><i
                                    class="fa fa-play"></i></button>

                            </div>
                        </template>
                    </v-jstree>
                </div>

                <div ref="element-editor" v-if="element.edit_on" id="element-editor" class="mt-3 p-2"
                     style="background-color:#eee;">
                    <form id="frm-audio" style="max-width: 100%;">
                        <label>Text:</label>
                        <p class="ml-md-2">{{element.text}}</p>

                        <label>Record File:</label>
                        <input type="hidden" v-model="element.data.id">
                        <input type="file" id="id_audio" class="form-control-file ml-md-2" accept="audio/*">


                        <div class="text-center mb-2">
                            <button class="btn btn-warning" v-on:click="elementUpdateAbort()">Anuluj</button>
                            <button class="btn btn-success" v-on:click="elementUpdate()">Zapisz</button>
                        </div>
                    </form>
                </div>

                <div ref="element-viewer" id="element-viewer" style="display:none">

                </div>

                <iframe id="frame-download" style="display:none;"></iframe>

                <audio id="audio" controls style="visibility: hidden;">
                    <source id="audio_source" type="audio/mpeg">
                </audio>

            </div>
        </div>

    </div>


</template>


<script>
    import Vue from 'vue';
    import VJstree from 'vue-jstree';
    import axios from 'axios';
    import VModal from 'vue-js-modal';
    import DocumentView from "./DocumentView";
    import DocumentQuestion from "./DocumentQuestion";

    Vue.use(VModal);

    export default {
        data() {
            return {
                audio_url: '',
                loading: false,
                error: null,
                document: {
                    id: 0,
                    name: '',
                    identifier: '',
                    signed_at: '',
                    info: '',
                    edit_on: true,
                },
                data_updating: false,
                data_updated: false,
                element: {
                    label: '',
                    text: '',
                    data: {
                        name: '',
                        enumeration: '',
                        title: '',
                        content: '',
                        start_at: null,
                        end_at: null,
                        parent_element_id: null,
                        depth: 0,
                        position: 1,
                    },
                    edit_on: false,
                },
                element_clean: {
                    label: '',
                    text: '',
                    data: {
                        name: '',
                        enumeration: '',
                        title: '',
                        content: '',
                        start_at: null,
                        end_at: null,
                        parent_element_id: null,
                        depth: 0,
                        position: 1,
                    },
                    edit_on: false,
                },
                elements: [],
                modal_content: '',
                scroll_object: null,
            };
        },
        components: {
            VJstree
        },
        created() {
            this.document.id = this.$route.params.id;
        },
        mounted() {
            if (this.document.id > 0) {
                this.documentFetch();
            }
            this.scroll_object = document.getElementsByClassName('ps')[0];
        },
        updated() {
            mdc.autoInit();
        },
        methods: {
            play_audio(audio_url) {
                this.audio_url = audio_url;
                var audio = document.getElementById('audio');

                var source = document.getElementById('audio_source');
                source.src = audio_url;

                audio.load(); //call this to just preload the audio without playing
                audio.play(); //call this to play the song right away
            },
            scrollMeTo(refName) {
                var _this = this;
                setTimeout(function () {
                    var element = _this.$refs[refName];
                    var top = element.offsetTop;
                    _this.scroll_object.scrollTo(0, top);
                });
            },
            call(cokolwiek, p1, p2) {
                this[cokolwiek](p1, p2);
            },
            documentCreate() {
                this.document.edit_on = false;
                axios
                    .post(
                        '/api/admin/document/update',
                        {
                            hash_id: yh.auth.hash_id,
                            document: this.document,
                        }
                    )
                    .then(response => {
                        this.document.id = response.data.id;
                        this.$router.push({name: 'documents.edit', params: {id: response.data.id}});
                        this.elementsFetch();
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
            documentText() {
                let full_text = '';
                let len = this.elements.length;
                let element_title = '';
                let element_obj;
                for (let i = 0; i < len; i++) {
                    // add element title
                    element_obj = this.elements[i];

                    element_title = '  ' + this.getFullRawText(element_obj);
                    full_text += element_title;

                    full_text = this.addText(element_obj, full_text, true);
                }
                return full_text;
            },
            documentView() {
                let content = '';
                let title = '';

                let dom = document.createElement('div');

                let len = this.elements.length;
                let element_title = '';
                let element_obj;
                for (let i = 0; i < len; i++) {
                    // add element title
                    element_obj = this.elements[i];
                    element_title = document.createElement("div");

                    element_title.innerHTML = this.getFullText(element_obj);
                    element_title.className += " ml-2";

                    element_title.className += " font-weight-bold";

                    dom.append(element_title);
                    //
                    this.addChildren(element_obj, dom, true);
                }

                this.modal_content = dom.innerHTML;
                content = dom.innerHTML;
                title = this.document.name;
                console.log("title:", title);

                this.$modal.show(
                    DocumentView,
                    {
                        content: content,
                        title: title, data: this.elements[0], raw_view: true
                    },
                    {
                        height: "auto",
                        width: "65%",
                        scrollable: true
                    });
            },
            documentEditOn(o) {
                this.document.edit_on = true;
            },
            documentDownload() {
                this.loading = true;
                let full_text = this.documentText();
                axios
                    .post(
                        '/api/admin/document/download',
                        {
                            hash_id: yh.auth.hash_id,
                            id: this.document.id,
                            title: this.document.name,
                            text: full_text,
                        }
                    )
                    .then(response => {
                        this.downloadURI('https://testy.iusvitae.pl/' + response.data, this.document.name);
                        this.loading = false;
                    }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function () {
                            document.location = error.response.data.location;
                        }, 2000);
                    }
                });
            },

            downloadURI(uri, name) {
                var link = document.createElement("a");
                // If you don't know the name or want to use
                // the webserver default set name = ''
                link.setAttribute('download', name);
                link.href = uri;
                document.body.appendChild(link);
                link.click();
                link.remove();
            },

            documentFetch(no_elements = 0) {
                this.loading = true;
                this.document.edit_on = false;
                axios
                    .post(
                        '/api/admin/document',
                        {
                            hash_id: yh.auth.hash_id,
                            id: this.document.id,
                        }
                    )
                    .then(response => {
                        this.document.name = response.data.name;
                        this.document.identifier = response.data.identifier;
                        this.document.signed_at = response.data.signed_at;
                        this.document.info = response.data.info;
                        this.loading = false;
                        if (no_elements == 0) {
                            this.elementsFetch();
                        }
                    }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function () {
                            document.location = error.response.data.location;
                        }, 2000);
                    }
                });
            },
            documentUpdateAbort() {
                this.documentFetch(1);
            },
            documentUpdate() {
                if (this.document.id == 0) {
                    return this.documentCreate();
                }
                this.document.edit_on = false;
                axios
                    .post(
                        '/api/admin/document/update',
                        {
                            hash_id: yh.auth.hash_id,
                            document: this.document,
                        }
                    )
                    .then(response => {
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
            elementToggle(model) {
                if (model.opened) {
                    model.opened = false;
                    return;
                }
                model.opened = true;
            },
            elementEdit(model, base) {
                let parent = base.vm.$parent.data;

                this.element = JSON.parse(JSON.stringify(this.element_clean));
                this.element.data = model.data;
                console.log(model.text);
                this.element.text = model.text;

                console.log(this.element.text);
                this.element.edit_on = true;
                if (parent.data) {
                    if (!this.element.data.start_at) {
                        this.element.data.start_at = parent.data.start_at;
                    }
                    if (!this.element.data.end_at) {
                        this.element.data.end_at = parent.data.end_at;
                    }
                }

                this.scrollMeTo('element-editor');
            },
            elementAddAfter(model, base) {
                let parent = base.vm.$parent.data;
                this.element = JSON.parse(JSON.stringify(this.element_clean));
                this.element.label = 'Wstaw po: ' + model.text;
                this.element.data.parent_element_id = model.data.parent_element_id;
                this.element.data.depth = model.data.depth;
                this.element.data.position = model.data.position + 1;
                this.element.edit_on = true;
                this.element.data.start_at = parent.data.start_at;
                this.element.data.end_at = parent.data.end_at;
                this.scrollMeTo('element-editor');
            },
            elementAddBefore(model, base) {
                let parent = base.vm.$parent.data;
                this.element = JSON.parse(JSON.stringify(this.element_clean));
                this.element.label = 'Wstaw przed: ' + model.text;
                this.element.data.parent_element_id = model.data.parent_element_id;
                this.element.data.depth = model.data.depth;
                this.element.data.position = model.data.position;
                this.element.edit_on = true;
                this.element.data.start_at = parent.data.start_at;
                this.element.data.end_at = parent.data.end_at;
                this.scrollMeTo('element-editor');
            },
            elementRemove(model, base) {
                if (!confirm('Czy na pewno chcesz usunąć element i wszystkie jego podelementy?')) {
                    return false;
                }
                this.loading = true;
                axios
                    .post(
                        '/api/admin/document/element/remove',
                        {
                            hash_id: yh.auth.hash_id,
                            id: model.data.id,
                        }
                    )
                    .then(response => {
                        this.elementsFetch();
                        this.loading = false;
                    }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function () {
                            document.location = error.response.data.location;
                        }, 2000);
                    }
                });
            },

            elementView(model, base, raw_view) {
                let content = '';
                let title = '';

                if (model.data.out_date) {
                    title = "This legal is out of date";
                } else {
                    let dom = document.createElement('div');
                    this.addChildren(model, dom, raw_view);
                    this.modal_content = dom.innerHTML;
                    content = dom.innerHTML;
                    title = this.getFullText(model);
                }

                this.$modal.show(
                    DocumentView,
                    {
                        content: content,
                        title: title, data: model, raw_view: raw_view
                    },
                    {
                        height: "auto",
                        width: "65%",
                        scrollable: true
                    });
            },

            elementQuestion(model, base) {

                this.$modal.show(
                    DocumentQuestion,
                    {title: model.text, element_id: model.data.id},
                    {
                        height: "100%",
                        width: "65%",
                        minHeight: 900,
                        scrollable: true
                    });
            },

            addText(object, wrapper, raw_view) {
                if (object.children) {
                    let len = object.children.length;
                    for (let i = 0; i < len - 1; i++) {
                        let child_obj = object.children[i];

                        let child_dom = this.getFullRawText(child_obj);
                        wrapper += child_dom;
                        wrapper = this.addText(child_obj, wrapper, raw_view)
                    }
                }
                return wrapper;
            },

            addChildren(object, wrapper, raw_view) {
                if (object.children) {
                    let len = object.children.length;
                    for (let i = 0; i < len; i++) {
                        let child_obj = object.children[i];
                        let child_dom = document.createElement("div");

                        child_dom.innerHTML = this.getFullText(child_obj);
                        child_dom.className += " ml-4";
                        if (raw_view && child_obj.data.after_date) {
                            child_dom.className += " text-danger";
                        } else if (raw_view && child_obj.data.before_date) {
                            child_dom.className += " text-success";
                        }
                        wrapper.append(child_dom);
                        this.addChildren(child_obj, child_dom, raw_view)
                    }
                }
            },

            getSpaceLine(len) {
                let spacestr = ''
                while (len > 0) {
                    spacestr += '  ';
                    len--;
                }
                return spacestr;
            },

            getFullRawText(object) {

                let text = this.getSpaceLine(object.data.depth);
                text += (object.data.name) ? object.data.name + " " : "";
                text += (object.data.enumeration) ? object.data.enumeration + " " : "";
                text += (object.data.title) ? object.data.title + " " : "";

                text += (object.data.content) ? object.data.content + " " : "";
                text += '\n';
                return text;
            },
            getFullText(object) {
                let text = '<b>';
                text += (object.data.name) ? object.data.name + " " : "";
                text += (object.data.enumeration) ? object.data.enumeration + " " : "";
                text += (object.data.title) ? object.data.title + " " : "";
                text += '</b>';
                text += (object.data.content) ? object.data.content + " " : "";
                ;
                return text;
            },
            elementCreate() {
                axios
                    .post(
                        '/api/admin/document/element/update',
                        {
                            hash_id: yh.auth.hash_id,
                            document: this.document,
                            element: this.element.data,
                        }
                    )
                    .then(response => {
                        this.elementsFetch();
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
            elementsFetch() {
                this.loading = true;
                axios
                    .post(
                        '/api/admin/document/elements',
                        {
                            hash_id: yh.auth.hash_id,
                            id: this.document.id,
                            elements: this.elements,
                        }
                    )
                    .then(response => {
                        this.elements = response.data.elements;
                        this.loading = false;
                    }).catch(error => {
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function () {
                            document.location = error.response.data.location;
                        }, 2000);
                    }
                });
            },
            elementUpdate() {
                this.element.edit_on = false;

                let audio = document.getElementById('id_audio').files[0];
                let data = new FormData();
                data.append('audio', audio);
                data.append('hash_id', yh.auth.hash_id);
                data.append('element_id', this.element.data.id);
                data.append('document_id', this.document.id);

                axios
                    .post(
                        '/api/admin/document/element/update_audio',
                        data
                    )
                    .then(response => {
                        this.elementsFetch();
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
            elementUpdateAbort() {
                this.element.edit_on = false;
            },
        },
    }
</script>
