import Vue from 'vue';
import App from './App.vue';
import router from './router';

import './plugins/axios.js';

import { VueMaskDirective } from 'v-mask';
Vue.directive('mask', VueMaskDirective);

import Toasted from 'vue-toasted';
Vue.use(Toasted);

Vue.config.productionTip = false;

new Vue({
  router,
  render: (h) => h(App),
}).$mount('#app');
