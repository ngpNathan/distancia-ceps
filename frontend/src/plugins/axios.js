import axios from 'axios';
import Vue from 'vue';

const baseURL = process.env.VUE_APP_AXIOS_BASEURL;

const instance = axios.create({
  baseURL,
});

Vue.prototype.$axios = instance;

export default Vue;
