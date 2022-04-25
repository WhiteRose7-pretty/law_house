<template>
    <div>
        <h1>Dołączanie do gry</h1>
        <div v-if="game.regula">
            <input type="checkbox" id="regula" v-model="regula_check">
            <label for="regula">Przed dołączeniem do gry zaakceptuj jej regulamin</label> <br><br>
            <a target="_blank" style="text-decoration: underline;" :href="'/przepisy-prawne/' + game.regulation.title_uri">Czytaj regulamin</a><br><br><br>
            <button class="form-control" style="width: fit-content;" v-if="regula_check" v-on:click="join(hash_key)">Dołącz</button>
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
            game: null,
            regula_check: false,
            hash_key: this.$route.params.hash_key,
        };
    },
    created() {
        this.fetch(this.$route.params.hash_key);
        //this.join(this.$route.params.hash_key);
    },
    destroyed() {
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
    },
    methods: {
        fetch(hash_key) {
            this.error = this.list = null;
            this.loading = true;
            var _this = this;
            axios
                .post(
                    '/api/app/games/regulations',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: hash_key,
                    }
                )
                .then(response => {
                    this.game = response.data;
                    if(!this.game.regula){
                        this.join(this.hash_key);
                    }

                }).catch(error => {
                this.loading = false;
                this.error = error.response.data.message || error.message;
                if (error.response.data.location) {
                    this.message('Wystąpił błąd',this.error,function(){document.location = error.response.data.location;});
                    return;
                }
                this.message('Wystąpił błąd',this.error);
            });
        },
        join(hash_key) {
            this.error = this.list = null;
            this.loading = true;
            var _this = this;
            axios
                .post(
                    '/api/app/games/join',
                    {
                        hash_id: yh.auth.hash_id,
                        hash_key: hash_key,
                    }
                )
                .then(response => {
                    var type = 'games.view.'+response.data.game.type;
                    this.message('Potwierdzenie','Zostałeś dołączony do gry',function(){
                        _this.$router.push({ name: type, params: { hash_key: hash_key } });
                    });
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        this.message('Wystąpił błąd',this.error,function(){document.location = error.response.data.location;});
                        return;
                    }
                    this.message('Wystąpił błąd',this.error);
                });
        },
        message(title, msg, callback = null, no=false) {
            setTimeout(function(){
                $('.vc-container').center();
            },50);
            var _this = this;
            if (no) {
                this.$confirm(
                  {
                    title: title,
                    message: msg,
                    button: {
                      yes: 'Tak',
                      no: 'Nie'
                    },
                    /**
                    * Callback Function
                    * @param {Boolean} confirm
                    */
                    callback: confirm => {
                      if (confirm) {
                          if (typeof callback == 'function') {
                              callback();
                          }
                      }
                    }
                  }
                );
            } else {
                this.$confirm(
                  {
                    title: title,
                    message: msg,
                    button: {
                      yes: 'OK'
                    },
                    /**
                    * Callback Function
                    * @param {Boolean} confirm
                    */
                    callback: confirm => {
                      if (confirm) {
                          if (typeof callback == 'function') {
                              callback();
                          }
                      }
                    }
                  }
                );
            }
        },
    }
}
</script>
