import Vue from 'vue';
import App from './App.vue';
import router from './router';

import './plugins/axios.js';

import { VueMaskDirective } from 'v-mask';
Vue.directive('mask', VueMaskDirective);

import Toasted from 'vue-toasted';
Vue.use(Toasted);

Vue.filter('formataData', (value) => {
  if (!value) return '-';

  const d = new Date(value.replace(' ', 'T'));
  const pad = (n) => n.toString().padStart(2, '0');

  return (
    pad(d.getDate()) +
    '/' +
    pad(d.getMonth() + 1) +
    '/' +
    d.getFullYear() +
    ' ' +
    pad(d.getHours()) +
    ':' +
    pad(d.getMinutes()) +
    ':' +
    pad(d.getSeconds())
  );
});

Vue.config.productionTip = false;

new Vue({
  router,
  render: (h) => h(App),
}).$mount('#app');
