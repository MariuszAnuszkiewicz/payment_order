require('./bootstrap');

import Vue from 'vue';

   Vue.component('instant-payment', require('./components/InstantPayment').default);

const app = new Vue({
    el: '#app',
});
