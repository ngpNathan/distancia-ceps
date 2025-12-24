<template>
  <div>
    <div id="app" class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h4 class="card-title text-center mb-4">
                Calcular Distância entre CEPs
              </h4>
              <form @submit.prevent="adicionarCep" ref="formCeps">
                <div class="form-group">
                  <label for="cepOrigem">CEP de Origem:</label>
                  <input
                    type="text"
                    id="cepOrigem"
                    class="form-control"
                    v-model="cepOrigem"
                    v-mask="'#####-###'"
                    placeholder="00000-000"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="cepDestino">CEP de Destino:</label>
                  <input
                    type="text"
                    id="cepDestino"
                    class="form-control"
                    v-model="cepDestino"
                    v-mask="'#####-###'"
                    placeholder="00000-000"
                    required
                  />
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                  Calcular
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="row justify-content-center mt-5">
        <div class="col-md-8">
          <table class="table table-striped table-bordered shadow-sm">
            <thead class="thead-dark text-center">
              <tr>
                <th>CEP Origem</th>
                <th>CEP Destino</th>
                <th>Distância (km)</th>
                <th>Data Cadastro</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in tabela"
                :key="index"
                class="text-center"
              >
                <td>{{ item.cepOrigem }}</td>
                <td>{{ item.cepDestino }}</td>
                <td>{{ item.distancia }}</td>
                <td>{{ item.dataCadastro }}</td>
              </tr>
              <tr v-if="tabela.length === 0">
                <td colspan="4" class="text-center">
                  Nenhum registro cadastrado
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/* eslint-disable */

export default {
  data: () => ({
    cepOrigem: '',
    cepDestino: '',
    tabela: [],
  }),

  async mounted() {
    await this.buscarParametroUrlBrasilAPI();
  },

  methods: {
    async buscarParametroUrlBrasilAPI() {
      try {
        await this.$axios.get('/parametro/URLBrasilApi');
      } catch (error) {
        this.$toasted.show('Erro ao consultar parâmetro da BrasilAPI', {
          type: 'error',
          duration: 3000,
        });
      }
    },

    somenteNumeros(str) {
      return str.replace(/[^0-9]+/g, '');
    },

    consultaBrasilAPI() {},

    async adicionarCep() {
      const _cepOrigem = this.somenteNumeros(this.cepOrigem);
      const _cepDestino = this.somenteNumeros(this.cepDestino);

      if (_cepOrigem.length != 8 || _cepDestino.length != 8) {
        this.$toasted.show(
          'CEP inválido, por favor informe o CEP de origem e destino corretamente!',
          { type: 'error', duration: 3000 }
        );
        return;
      }

      // Simulando cálculo de distância aleatória
      const distancia = (Math.random() * 500).toFixed(2);
      const dataCadastro = new Date().toLocaleString();

      this.tabela.push({
        cepOrigem: this.cepOrigem,
        cepDestino: this.cepDestino,
        distancia,
        dataCadastro,
      });

      // Limpa os campos
      this.cepOrigem = '';
      this.cepDestino = '';
    },
  },
};
</script>
