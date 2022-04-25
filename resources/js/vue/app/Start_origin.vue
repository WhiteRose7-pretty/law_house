<template>
    <div>
        <h1>Start</h1>
        <div class="start-buttons row">
            <div class="col-12 col-sm-6 col-md-3">
                <router-link class="start-button" :to="{ name: 'tests.new' }">
                    <i class="fa fa-plus"></i>
                    <span>
                    Nowy test
                    </span>
                </router-link>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <router-link class="start-button" :to="{ name: 'tests.continue' }">
                    <i class="fa fa-angle-double-right"></i>
                    <span>
                    Kontynuuj
                    </span>
                </router-link>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <router-link class="start-button" :to="{ name: 'questions.repeats' }">
                    <i class="fa fa-redo"></i>
                    <span>
                    Powtarzaj
                    </span>
                </router-link>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <router-link class="start-button" :to="{ name: 'games' }">
                    <i class="fa fa-hand-scissors"></i>
                    <span>
                    Zagraj
                    </span>
                </router-link>
            </div>
        </div>
        <questions-repeats-calendar></questions-repeats-calendar>
        <stats-summary v-if="summary" v-bind:stats="summary"></stats-summary>
    </div>
</template>


<script>
export default {
    data() {
        return {
            loading: false,
            error: null,
            summary: null,
        };
    },
    created() {
        this.fetchSummary();
    },
    destroyed() {
    },
    mounted() {
        SideBarCollapseIfActive(true);
    },
    updated() {
    },
    methods: {
        fetchSummary() {
            this.error = this.summary = null;
            this.loading = true;
            axios
                .post(
                    '/api/app/stats/summary',
                    {
                        hash_id: yh.auth.hash_id,
                    }
                )
                .then(response => {
                    this.loading = false;
                    this.summary = response.data.stats;
                }).catch(error => {
                    this.loading = false;
                    this.error = error.response.data.message || error.message;
                    if (error.response.data.location) {
                        setTimeout(function(){document.location = error.response.data.location;},2000);
                    }
                });
        },
    },
}
</script>
