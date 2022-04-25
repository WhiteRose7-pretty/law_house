
<template>
    <div class="p-4">
        <label class="mdc-text-field mdc-text-field--filled" data-mdc-auto-init="MDCTextField" v-if="!raw_view">
            <span class="mdc-text-field__ripple"></span>
            <input class="mdc-text-field__input yh-bind-key" v-on:change="change_date()" name="end_at" id="e-end-at" type="date" v-model="date" aria-labelledby="e-end-at-label" autofocus>
            <span class="mdc-line-ripple"></span>
        </label>
        <h3 v-if="raw_view" v-html="title"></h3>
        <div v-if="raw_view" v-html="content"></div>
        <div v-if="!raw_view" v-html="content_string"></div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                date: null,
                content_string: '',
            }
        },
        props: ['content', 'title', 'data', 'raw_view'],
        created() {
            let today = new Date();
            today = today.toISOString().split('T')[0];
            this.date = today;
            let set_date = new Date(this.date);
            let dom = document.createElement('div');
            if (!this.raw_view){
                this.addChildren(this.data, dom,  set_date);
            }
            this.content_string = dom.innerHTML;
        },
        methods:{
            change_date(){
                let set_date = new Date(this.date);
                let dom = document.createElement('div');
                this.addChildren(this.data, dom,  set_date);
                this.content_string = dom.innerHTML;
            },
            getFullText(object){
                let text = '<b>';
                text += (object.data.name )? object.data.name + " " : "";
                text += (object.data.enumeration )? object.data.enumeration + " " : "";
                text += (object.data.title )? object.data.title + " " : "";
                text += '</b>';
                text += (object.data.content )? object.data.content + " " : "";;
                return text;
            },
            addChildren(object, wrapper,  set_date){
                if(object.children){
                    let len = object.children.length;
                    for (let i=0; i<len; i++){
                        let child_obj = object.children[i];
                        let child_dom = document.createElement("div");

                        child_dom.innerHTML = this.getFullText(child_obj);
                        child_dom.className += " ml-4";
                        if(child_obj.data.start_at){
                            let start_date = new Date(child_obj.data.start_at);
                            if(start_date > set_date){
                                continue;
                            }
                        }
                        if(child_obj.data.end_at){
                            let end_date = new Date(child_obj.data.end_at);
                            if(end_date < set_date){
                                continue;
                            }
                        }

                        wrapper.append(child_dom);
                        this.addChildren(child_obj, child_dom, set_date)
                    }
                }
            },
        }
    }
</script>
