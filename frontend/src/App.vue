<template>
  <div id="app">
    <BarraSuperior />
    <div class="container mt-4">
      <router-view />
    </div>
  </div>
</template>

<script>
import BarraSuperior from './components/BarraSuperior.vue';

export default {
  components: {
    BarraSuperior,
  },
};
</script>
