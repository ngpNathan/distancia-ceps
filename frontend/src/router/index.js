import Vue from 'vue';
import VueRouter from 'vue-router';

import CalculaDistanciaView from '@/views/CalculaDistanciaView.vue';
import DistanciasView from '@/views/DistanciasView.vue';
import ImportarView from '@/views/ImportarView.vue';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'routeCalculaDistancia',
    component: CalculaDistanciaView,
  },
  {
    path: '/distancias',
    name: 'routeDistancias',
    component: DistanciasView,
  },
  {
    path: '/importar',
    name: 'routeImportar',
    component: ImportarView,
  },
];

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
});

export default router;
