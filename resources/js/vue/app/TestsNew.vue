<template>
    <div>
        <h1>Nowy Test</h1>
        <div class="select-sets card mt-5 mb-5">
            <div class="card-body  px-3 p-lg-5">
                <loading :active.sync="loading"
                         :loader="'dots'"
                         :can-cancel="false"
                         :is-full-page="true"
                         :opacity="0.8"
                         :color="'#1596b2'"></loading>

                <div v-if="error" class="text-danger">
                    {{ error }}
                </div>

                <div v-if="sets">
                    <h3 class="mb-3">Wybierz zestaw</h3>
                    <div v-for="s in sets">
                        <div v-if="s.group">
                            <div style="background-color:#eee; padding:8px 5px;">
                                <div class="mdc-form-field cursor-pointer max-width-85"
                                     v-on:click="groupSelect(s.index)">
                                    <div class="mdc-checkbox">
                                        <input type="checkbox"
                                               class="mdc-checkbox__native-control group-select"
                                               v-bind:id="'group-checkbox-'+s.index" v-bind:data-index="s.index"/>
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
                                    <label class="m-0" v-bind:for="'group-checkbox-'+s.index">{{ s.group }}
                                        ({{ s.total }})</label>

                                </div>
                                <i class="fa fa-plus group-toggle" v-bind:data-for="'group-'+s.index"
                                   style="cursor:pointer; float:right; padding: 15px 15px 0 0;"></i>

                            </div>
                            <div v-bind:id="'group-'+s.index" style="display:none; padding:8px 5px; padding-left:25px;">
                                <div v-for="ss in s.list">
                                    <div class="mdc-form-field cursor-pointer max-width-85"
                                         v-on:click="setSelect(ss.id, s.index)">
                                        <div class="mdc-checkbox">
                                            <input type="checkbox"
                                                   class="mdc-checkbox__native-control group-set-select"
                                                   v-bind:id="'group-set-checkbox-'+ss.id"
                                                   v-bind:data-group-index="s.index" v-bind:data-set-id="ss.id"/>
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
                                        <label class="m-0" v-bind:for="'group-set-checkbox-'+ss.id">{{ ss.name }}
                                            ({{ ss.total }})</label>

                                    </div>
                                    <i class="fa fa-plus group-toggle ml-1" v-bind:data-for="'elements-tree-'+ss.id"
                                       style="cursor:pointer; padding: 15px 15px 0 0;"></i>
                                    <div v-bind:id="'elements-tree-'+ss.id" v-if="elements" class="mt-3 ml-3"
                                         style="display:none;">
                                        <v-jstree v-if="elements" :data="legal_tree(ss.legal_id)" multiple
                                                  allow-batch whole-row
                                                  @item-click="itemClick(ss.id, s.index, ss.legal_id)">
                                        </v-jstree>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else style="padding:8px 5px;">
                            <div class="mdc-form-field cursor-pointer max-width-85" v-on:click="setSelect(s.id)">
                                <div class="mdc-checkbox">
                                    <input type="checkbox"
                                           class="mdc-checkbox__native-control set-select"
                                           v-bind:id="'set-checkbox-'+s.id" v-bind:data-set-id="s.id"/>
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
                                <label class="m-0" v-bind:for="'set-checkbox-'+s.id">{{ s.name }}
                                    ({{ s.total }})</label>
                            </div>
                            <i class="fa fa-plus group-toggle ml-1" v-bind:data-for="'elements-tree-'+s.id"
                               style="cursor:pointer; padding: 15px 15px 0 0;"></i>
                            <div v-bind:id="'elements-tree-'+s.id" v-if="elements" class="mt-3 ml-3 overflow-auto"
                                 style="display:none;">
                                <v-jstree v-if="elements" :data="legal_tree(s.legal_id)" show-checkbox multiple
                                          allow-batch whole-row @item-click="itemClick(s.id, 0, s.legal_id)">
                                </v-jstree>
                            </div>
                        </div>

                    </div>
                    <h3 class="my-4">Wybierz test</h3>
                    <div>
                        <div v-for="item in admin_tests">
                            <div class="mdc-form-field cursor-pointer max-width-85">
                                <div class="mdc-checkbox">
                                    <input type="checkbox" :id="'admin-test-'+item.id"  class="mdc-checkbox__native-control set-select"
                                           v-on:click="selectAdminTest(item.id)">
                                    <div class="mdc-checkbox__background">
                                        <svg viewBox="0 0 24 24" class="mdc-checkbox__checkmark">
                                            <path fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"
                                                  class="mdc-checkbox__checkmark-path"></path>
                                        </svg>
                                        <div class="mdc-checkbox__mixedmark"></div>
                                    </div>
                                    <div class="mdc-checkbox__ripple"></div>
                                </div>
                                <label :for="'admin-test-'+item.id"  class="m-0">{{ item.name }} ({{item.counts_of_questions}})</label>
                            </div>
                        </div>
                        <div>
                    </div>
                </div>

                <h3 class="mt-3 mb-3 pt-3" style="border-top:1px #ddd dotted;">Ustawienia</h3>
                <div class="ml-1">
                    <div class="mb-3">
                        <select v-model="settings.time" class="form-control form-control-lg">
                            <option value="0">bez ograniczeń czasowych</option>
                            <option v-for="t in 30" v-bind:value="t">{{ t }} min.</option>
                            <option v-for="t in 6" v-bind:value="30+5*t">{{ 30 + 5 * t }} min.</option>
                            <option v-for="t in 6" v-bind:value="60+10*t">{{ 60 + 10 * t }} min.</option>
                            <option v-for="t in 6" v-bind:value="120+30*t">{{ 120 + 30 * t }} min.</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <select v-model="settings.limit" class="form-control form-control-lg">
                            <option value="0">maksymalna możliwa ilość pytań</option>
                            <option v-for="t in 9" v-bind:value="5*t">{{ 5 * t }} pytań maks.</option>
                            <option v-for="t in 10" v-bind:value="50*t">{{ 50 * t }} pytań maks.</option>
                            <option v-for="t in 20" v-bind:value="500+100*t">{{ 500 + 100 * t }} pytań maks.
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="mdc-switch ml-2" data-mdc-auto-init="MDCSwitch" style="vertical-align:middle;">
                            <div class="mdc-switch__track"></div>
                            <div class="mdc-switch__thumb-underlay">
                                <div class="mdc-switch__thumb"></div>
                                <input type="checkbox" id="only-new-switch" class="mdc-switch__native-control"
                                       role="switch" aria-checked="" v-model="settings.only_new"
                                       v-on:change="onlyNewSwitch()">
                            </div>
                        </div>
                        <label for="only-new-switch" class="ml-3 mb-0 pb-0 yh-fs-17 yh-v-o-80">Tylko nowe
                            pytania</label>
                    </div>
                    <div class="mb-3">
                        <div class="mdc-switch ml-2" data-mdc-auto-init="MDCSwitch" style="vertical-align:middle;">
                            <div class="mdc-switch__track"></div>
                            <div class="mdc-switch__thumb-underlay">
                                <div class="mdc-switch__thumb"></div>
                                <input type="checkbox" id="only-known-switch" class="mdc-switch__native-control"
                                       role="switch" aria-checked="" v-model="settings.only_known"
                                       v-on:change="onlyKnownSwitch()">
                            </div>
                        </div>
                        <label for="only-known-switch" class="ml-3 mb-0 pb-0 yh-fs-17 yh-v-o-80">Tylko znane
                            pytania</label>
                    </div>
                    <div class="mb-3">
                        <div class="mdc-switch ml-2" data-mdc-auto-init="MDCSwitch" style="vertical-align:middle;">
                            <div class="mdc-switch__track"></div>
                            <div class="mdc-switch__thumb-underlay">
                                <div class="mdc-switch__thumb"></div>
                                <input type="checkbox" id="show-correct-switch" class="mdc-switch__native-control"
                                       role="switch" aria-checked="" v-model="settings.show_correct">
                            </div>
                        </div>
                        <label for="show-correct-switch" class="ml-3 mb-0 pb-0 yh-fs-17 yh-v-o-80">Pokazuj poprawne
                            odpowiedzi</label>
                    </div>
                </div>

                <label class="mdc-text-field mdc-text-field--filled mt-2" data-mdc-auto-init="MDCTextField"
                       style="width: 100%">
                    <span class="mdc-text-field__ripple"></span>
                    <input class="mdc-text-field__input yh-bind-key" name="start_at" id="e-start-at"
                           v-model="settings.set_date" type="date" aria-labelledby="e-start-at-label">
                    <span class="mdc-floating-label" id="e-start-at-label">Stan prawny pytań na dzień:</span>
                    <span class="mdc-line-ripple"></span>
                </label>


                <div v-if="(settings.selected>0) || admin_tests_selected">
                    <div v-if="settings.only_new == true">
                        <div v-if="settings.total_new > 0">
                            <div v-if="show_counts == 1">
                                <h3 class="mt-3 mb-3 pt-3" style="border-top:1px #ddd dotted;">Podsumowanie</h3>
                                <p>
                                    Twój test będzie zawierał
                                    <span style="font-size:1.5em;">
                                        {{
                                            settings.limit == 0
                                                ?
                                                settings.total_new
                                                :
                                                (
                                                    settings.limit > settings.total_new
                                                        ?
                                                        settings.total_new
                                                        :
                                                        settings.limit
                                                )
                                        }}
                                        </span>
                                    pytań
                                </p>
                            </div>
                            <button v-on:click="countQuestions()" class="btn btn-primary mt-2 mt-sm-0">przelicz
                                pytania
                            </button>
                            <button v-on:click="save()" class="btn btn-primary mt-2 mt-sm-0">Utwórz i rozpocznij
                            </button>
                        </div>
                        <div v-else>
                            <h3 class="mt-3 mb-3 pt-3" style="border-top:1px #ddd dotted;">Brak możliwości
                                stworzenia takiego testu</h3>
                            <p>Widziałeś już wszystkie pytania z wybranych zestawów.</p>
                        </div>
                    </div>
                    <div v-else-if="settings.only_known == true">
                        <div v-if="settings.total_known > 0">
                            <div v-if="show_counts == 1">
                                <h3 class="mt-3 mb-3 pt-3" style="border-top:1px #ddd dotted;">Podsumowanie</h3>
                                <p>
                                    Twój test będzie zawierał
                                    <span style="font-size:1.5em;">
                                        {{
                                            settings.limit == 0
                                                ?
                                                settings.total_known
                                                :
                                                (
                                                    settings.limit > settings.total_known
                                                        ?
                                                        settings.total_known
                                                        :
                                                        settings.limit
                                                )
                                        }}
                                        </span>
                                    pytań
                                </p>
                            </div>
                            <button v-on:click="countQuestions()" class="btn btn-primary mt-2 mt-sm-0">przelicz
                                pytania
                            </button>
                            <button v-on:click="save()" class="btn btn-primary mt-2 mt-sm-0">Utwórz i rozpocznij
                            </button>
                        </div>
                        <div v-else>
                            <h3 class="mt-3 mb-3 pt-3" style="border-top:1px #ddd dotted;">Brak możliwości
                                stworzenia takiego testu</h3>
                            <p>Nie widziałeś jeszcze żadnych pytań z wybranych zestawów.</p>
                        </div>
                    </div>
                    <div v-else>
                        <div>
                            <div v-if="show_counts == 1">
                                <h3 class="mt-3 mb-3 pt-3" style="border-top:1px #ddd dotted;">Podsumowanie</h3>
                                <p>
                                    Twój test będzie zawierał
                                    <span style="font-size:1.5em;">
                                        {{
                                            settings.limit == 0
                                                ?
                                                settings.total
                                                :
                                                (
                                                    settings.limit > settings.total
                                                        ?
                                                        settings.total
                                                        :
                                                        settings.limit
                                                )
                                        }}
                                        </span>
                                    pytań
                                </p>
                            </div>
                            <button v-if="!saving" v-on:click="countQuestions()"
                                    class="btn btn-primary mt-2 mt-sm-0">przelicz pytania
                            </button>
                            <button v-if="!saving" v-on:click="save()" class="btn btn-primary mt-2 mt-sm-0">Utwórz i
                                rozpocznij
                            </button>
                            <span v-else class="btn btn-outline-success">Tworzenie...</span>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <h3 class="mt-3 mb-3 pt-3 yh-v-o-60" style="border-top:1px #ddd dotted;">Wybierz zakres
                        materiału</h3>
                    <p>Żeby stworzyć nowy test musisz wybrać choć jeden zestaw pytań.</p>
                </div>

                <div style="color: white;">{{ changed_set }}</div>
            </div>
            <div v-if="counting" class="text-info">
                Liczenie pytań...
            </div>
            <div v-if="counting_error" class="text-danger">
                {{ counting_error }}
            </div>

        </div>
    </div>
    </div>
</template>

<script>
import axios from 'axios';
import VJstree from 'vue-jstree';
import Loading from "vue-loading-overlay";

export default {
    data() {
        return {
            loading: false,
            loading_documents: false,
            counting: false,
            error: null,
            counting_error: null,
            sets: null,
            settings: {
                total: 0,
                total_new: 100,
                total_known: 100,
                selected: 0,
                limit: 0,
                time: 0,
                only_new: 0,
                only_known: 0,
                show_correct: 0,
                out_date: 0,
                law_documents_id: 0,
                law_documents_elements_id: 0,
                set_date: null,
            },
            changed_set: 0,
            saving: false,
            documents: [],
            elements: [],
            show_counts: 0,
            admin_tests: [],
            admin_tests_selected: false,
        };
    },
    components: {
        VJstree,
        Loading,
    },
    created() {
        let today = new Date();
        today = today.toISOString().split('T')[0];
        this.settings.set_date = today;
        this.fetch();
        this.documentsElements();
    },
    mounted() {
        SideBarCollapseIfActive(true);
        $('.select-sets').on('click', '.group-toggle', function () {
            var id = $(this).data('for');
            if ($(this).hasClass('fa-plus')) {
                $('#' + id).show();
                $(this).addClass('fa-minus');
                $(this).removeClass('fa-plus');
                return;
            }
            $('#' + id).hide();
            $(this).removeClass('fa-minus');
            $(this).addClass('fa-plus');
        });

    },
    updated() {
        mdc.autoInit();
    },
    methods: {
        selectAdminTest(test_id){
            let temp = false;
            for (let item of this.admin_tests){
                if (item.id === test_id){
                    item.selected = 1 - item.selected;
                    temp = temp || item.selected
                }
            }
            this.admin_tests_selected = temp;
            console.log(this.admin_tests_selected);
            console.log(this.settings.selected);
        },
        itemClick(id, index, legal_id) {
            let legal_item_selected = false;
            for (let legal_item of this.elements) {
                if (legal_item.id == legal_id) {
                    legal_item_selected = this.check_legal(legal_item);
                }
            }
            $('.set-select[data-set-id=' + id + ']').prop('checked', legal_item_selected);
            $('.group-set-select[data-set-id=' + id + ']').prop('checked', legal_item_selected);
            this.setSelect(id, index, false);
        },
        check_legal(legal_item) {
            if (legal_item.selected) {
                return true;
            }
            for (let child of legal_item.children) {
                if (this.check_legal(child)) {
                    return true;
                }
            }
            return false;
        },
        selectWithChildren(legal, value) {
            for (let child of legal) {
                child.selected = value;
                this.selectWithChildren(child.children, value);
            }
        },
        legal_tree(id) {
            for (var item of this.elements) {
                if (item.id === id) {
                    return item.children;
                }
            }
            return [];
        },
        documentsElements() {
            let id = 100;
            this.elements_error = null;
            this.loading = true;
            axios
                .post(
                    '/api/admin/document/elements_chapter',
                    {
                        hash_id: yh.auth.hash_id,
                        id: id,
                    }
                )
                .then(response => {
                    this.elements = response.data.elements;
                    this.loading = false;
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
        limit_max_length(string, limit) {
            if (string.length > limit) {
                return string.substring(0, limit) + '...';
            } else {
                return string;
            }
        },
        onlyNewSwitch() {
            if (!this.settings.only_new) {
                return;
            }
            if (this.settings.only_known) {
                this.show_counts = 0;
                this.settings.total = 100;
                this.settings.total_new = 100;
                $('#only-known-switch').click();
            }
        },
        onlyKnownSwitch() {

            if (!this.settings.only_known) {
                return;
            }
            if (this.settings.only_new) {
                this.show_counts = 0;
                this.settings.total = 100;
                this.settings.total_known = 100;
                $('#only-new-switch').click();
            }
            console.log(this.settings.total_new, this.settings.total_known)

        },
        selected_set() {
            var count = 0;
            var count_new = 0;
            var count_known = 0;
            var selected = 0;
            for (var s in this.sets) {
                if (typeof this.sets[s].group == 'undefined') {
                    if (this.sets[s].selected) {
                        count = count + this.sets[s].total;
                        count_new = count_new + this.sets[s].new;
                        count_known = count_known + this.sets[s].known;
                        selected = selected + 1;
                    }
                    continue;
                }
                for (var ss in this.sets[s].list) {
                    if (this.sets[s].list[ss].selected) {
                        count = count + this.sets[s].list[ss].total;
                        count_new = count_new + this.sets[s].list[ss].new;
                        count_known = count_known + this.sets[s].list[ss].known;
                        selected = selected + 1;
                        continue;
                    }
                }
            }

            this.settings.total = count;
            this.settings.total_new = count_new;
            this.settings.total_known = count_known;
            this.settings.selected = selected;
            this.show_counts = 0;
            this.changed_set = 1 - this.changed_set;

        },
        countQuestions() {
            this.counting_error = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/tests/calculate_questions',
                    {
                        hash_id: yh.auth.hash_id,
                        sets: this.sets,
                        settings: this.settings,
                        elements: this.elements,
                        admin_tests: this.admin_tests,
                    }
                )
                .then(response => {
                    this.loading = false;
                    if (response.data.question_counts == 0) {
                        this.counting_error = "";
                    } else {
                        this.counting_error = '';
                    }

                    this.settings.total = response.data.question_counts;
                    this.settings.total_new = response.data.question_counts;
                    this.settings.total_known = response.data.question_counts;
                    this.show_counts = 1;

                    this.changed_set = 1 - this.changed_set;
                }).catch(error => {
                this.loading = false;
                this.counting_error = error.response.data.message || error.message;
            });
        },
        groupSelect(index, f = 1) {
            for (var s in this.sets) {
                if (typeof this.sets[s].group == 'undefined' || this.sets[s].index != index) {
                    continue;
                }
                var sel = $('.group-select[data-index=' + index + ']').prop('checked');
                this.sets[s].selected = sel;
                $('.group-set-select[data-group-index=' + index + ']').prop('checked', sel);
                for (var ss in this.sets[s].list) {
                    this.sets[s].list[ss].selected = sel;
                    this.selectWithChildren(this.legal_tree(this.sets[s].list[ss].legal_id), sel);
                }
                break;
            }
            this.selected_set();
        },
        setSelect(id, index = 0, with_child = true) {
            console.log('index', index);
            for (var s in this.sets) {
                if (index == 0) {
                    if (this.sets[s].id != id) {
                        continue;
                    }
                    var sel = $('.set-select[data-set-id=' + id + ']').prop('checked');
                    this.sets[s].selected = sel;
                    $('.set-select[data-set-id=' + id + ']').prop('checked', sel);
                    if (with_child) {
                        this.selectWithChildren(this.legal_tree(this.sets[s].legal_id), sel);
                    }
                    break;
                }
                if (index && (typeof this.sets[s].group == 'undefined' || this.sets[s].index != index)) {
                    continue;
                }
                var sall = true;
                for (var ss in this.sets[s].list) {
                    if (id == this.sets[s].list[ss].id) {
                        sel = $('.group-set-select[data-set-id=' + this.sets[s].list[ss].id + ']').prop('checked');
                        this.sets[s].list[ss].selected = sel;
                        sall = sall && sel;
                        if (with_child) {
                            this.selectWithChildren(this.legal_tree(this.sets[s].list[ss].legal_id), sel);
                        }
                        continue;
                    }
                    sall = sall && this.sets[s].list[ss].selected;
                }
                $('.group-select[data-index=' + index + ']').prop('checked', sall);
                this.sets[s].selected = sall;
                break;
            }
            this.selected_set();
        },
        fetch() {
            this.error = this.sets = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/questions/sets/tree',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.sets = JSON.parse(JSON.stringify(response.data));

                }).catch(error => {
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });

            axios
                .post(
                    '/api/app/admin-tests/available',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.admin_tests = JSON.parse(JSON.stringify(response.data));
                    this.admin_tests.forEach((item, index, arr) => {
                        item.selected = 0;
                    })
                    console.log(this.admin_tests);

                }).catch(error => {
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });


        },
        save() {
            this.error = null;
            this.loading = true;
            this.saving = true;
            axios
                .post(
                    '/api/app/tests/new',
                    {
                        hash_id: yh.auth.hash_id,
                        sets: this.sets,
                        settings: this.settings,
                        elements: this.elements,
                        admin_tests: this.admin_tests,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.$router.push({name: 'tests.run', params: {id: response.data.user_test_id}});
                }).catch(error => {
                this.loading = false;
                this.saving = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    setTimeout(function () {
                        document.location = error.response.data.location;
                    }, 2000);
                }
            });
        },
    },
}
</script>
