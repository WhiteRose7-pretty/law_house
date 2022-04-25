<template>
    <div class="ius">
        <div class="card">
            <div class="card-body">
                <div class="position-relative">
                    <h2 style="box-shadow: none; border: none;  margin-bottom: 0; width: 80%;">{{document.name}}</h2>
                    <p class="d-none">{{document.identifier}}</p>
                    <p class="d-none">{{document.signed_at}}</p>
                    <p class="d-none">{{document.info}}</p>
                    <div v-if="loading" class="text-info">
                        Ładowanie...
                    </div>

                    <div v-if="error" class="text-danger">
                        {{ error }}
                    </div>
                    <div class="board-panel position-absolute top-0 " style="right: 0; z-index: 2; opacity: 0.7;">
                        <button v-if="view_table" class="btn btn-primary p-0" title="Audio view"
                                style="width: 38px; height: 34px; font-size: 24px;padding: 0!important;"
                                v-on:click="documentHear()">
                            <i class="fa fa-headphones"></i>
                        </button>
                        <button v-else class="btn btn-primary p-0" title="table view"
                                style="width: 38px; height: 34px; font-size: 24px; padding: 0!important;"
                                v-on:click="documentView()"
                        >
                            <i class="fa fa-table-list" style="font-size: 24px;"></i>
                        </button>
                        <div v-if="!view_table" class="mt-2">
                            <button v-if="audio_status" class="btn btn-primary" v-on:click="stop_audio()"
                                    style="width: 38px; height: 34px; font-size: 24px; padding: 0!important;"><i
                                class="fa fa-pause"></i></button>
                            <button v-else class="btn btn-primary" v-on:click="start_audio()"
                                    style="width: 38px; height: 34px; font-size: 24px; padding: 0!important;"><i
                                class="fa fa-play"></i></button>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body position-relative" style="height: Calc(100vh - 150px); overflow-y: scroll;">
                <audio id="audio" controls style="height: 1px; visibility: hidden;">
                    <source id="audio_source" type="audio/mpeg">
                </audio>

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
                            </div>
                        </template>
                    </v-jstree>
                </div>

                <div class="position-relative d-none" id="html_content">
                    <div v-html="content"></div>
                </div>
            </div>
        </div>
    </div>


</template>


<script>
    import Vue from 'vue';
    import VJstree from 'vue-jstree';
    import axios from 'axios';
    import VModal from 'vue-js-modal';
    import DocumentView from "../admin/DocumentView";
    import DocumentQuestion from "../admin/DocumentQuestion";

    Vue.use(VModal);

    export default {
        data() {
            return {
                view_table: true,
                audio_url: '',
                audio_status: false,
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
                content: '',
                element_array: [],
                element_index: 0,
                audio: null,
                save_data: 0,
            };
        },
        components: {
            VJstree
        },
        created() {
            this.document.id = this.$route.params.id;
            if (localStorage.getItem(this.document.id)) {
                this.save_data = localStorage.getItem(this.document.id);
            }
        },
        mounted() {
            if (this.document.id > 0) {
                this.documentFetch();
            }
            this.scroll_object = document.getElementsByClassName('ps')[0];

            this.audio = document.getElementById('audio');
            var self = this;
            this.audio.addEventListener("ended", function () {
                console.log("ended", self.element_index);
                self.play_audio(self.next_element(self.element_index), self.element_index + 1);
            });

        },
        updated() {
            mdc.autoInit();
        },
        watch: {
            save_data(val){
                localStorage.setItem(this.document.id, val);
            }
        },
        methods: {
            start_audio() {
                this.audio.play();
                this.audio_status = true;
            },
            stop_audio() {
                this.audio.pause();
                this.audio_status = false;
            },
            play_audio(audio_url, element_index = null, autoplay = true) {
                console.log("play", element_index);
                this.element_index = element_index;
                this.audio_url = audio_url;
                var audio = document.getElementById('audio');
                var source = document.getElementById('audio_source');
                source.src = audio_url;
                audio.load(); //call this to just preload the audio without playing
                if (autoplay) {
                    audio.play(); //call this to play the song right away
                }
            },

            next_element(index) {
                this.inactive_element(this.element_array[index].id);
                this.active_element(this.element_array[index + 1].id);
                this.save_data = index + 1;
                return this.element_array[index + 1].audio_url;
            },

            documentHear() {
                this.view_table = false;
                this.play_audio(this.get_element_audio(this.save_data), this.save_data, false);
                this.stop_audio();
                this.active_element(this.element_array[this.save_data].id);
                document.getElementById('html_content').classList.remove('d-none');
                document.getElementById('elements-tree').classList.add('d-none');
            },
            documentView() {
                this.view_table = true;
                document.getElementById('html_content').classList.add('d-none');
                document.getElementById('elements-tree').classList.remove('d-none');
            },
            get_element_audio(id) {
                return this.element_array[id].audio_url
            },

            active_element(id) {
                id = "id_" + id;
                console.log("activating: ", id);
                document.getElementById(id).className += " text-active";
            },
            inactive_element(id) {
                id = "id_" + id;
                if (document.getElementById(id)) {
                    document.getElementById(id).classList.remove("text-active");
                }
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

            addChildren(object, wrapper, raw_view, element_array) {
                if (object.children) {
                    let len = object.children.length;
                    for (let i = 0; i < len; i++) {
                        let child_obj = object.children[i];
                        let child_dom = document.createElement("div");

                        child_dom.innerHTML = this.getFullText(child_obj);
                        child_dom.className += " ml-4";
                        // if (raw_view && child_obj.data.after_date) {
                        //     child_dom.className += " text-danger";
                        // } else if (raw_view && child_obj.data.before_date) {
                        //     child_dom.className += " text-success";
                        // }
                        child_dom.className += " text-black";
                        child_dom.setAttribute('id', "id_" + child_obj.data.id);
                        child_dom.setAttribute('audio_url', child_obj.data.audio_url);
                        child_dom.setAttribute('index', element_array.length);
                        element_array.push({'id': child_obj.data.id, 'audio_url': child_obj.data.audio_url});

                        wrapper.append(child_dom);
                        this.addChildren(child_obj, child_dom, raw_view, element_array)
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
                        console.log('asdfasdf');
                        let dom = document.createElement('div');
                        let len = this.elements.length;
                        let element_title = '';
                        let element_obj;
                        let element_array = [];
                        for (let i = 0; i < len; i++) {
                            element_obj = this.elements[i];
                            element_title = document.createElement("div");
                            element_title.innerHTML = this.getFullText(element_obj);
                            element_title.className += " ml-2 font-weight-bold";
                            element_title.setAttribute('id', "id_" + element_obj.data.id);
                            element_title.setAttribute('index', element_array.length);
                            element_title.setAttribute('audio_url', element_obj.data.audio_url);
                            element_array.push({'id': element_obj.data.id, 'audio_url': element_obj.data.audio_url});
                            dom.append(element_title);
                            this.addChildren(element_obj, dom, true, element_array);
                        }
                        this.content = dom.innerHTML;
                        this.element_array = element_array;
                        this.loading = false;
                        console.log('asdfasdf1');
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
