<template>
  <div>
    <div id="app" class="container py-5">
      <div class="row justify-content-center mt-5">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5>Distâncias</h5>
            </div>
            <div class="card-body">
              <table class="table table-striped table-bordered shadow-sm">
                <thead class="thead-dark text-center">
                  <tr>
                    <th>CEP Origem</th>
                    <th>Localidade Origem</th>
                    <th>CEP Destino</th>
                    <th>Localidade Destino</th>
                    <th>Distância (km)</th>
                    <th>Data Cadastro</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in itemsDistancias"
                    :key="index"
                    class="text-center"
                  >
                    <td>{{ mascararCepTabela(item.cepOrigem) }}</td>
                    <td>
                      {{
                        item.estadoOrigem +
                        ' - ' +
                        item.cidadeOrigem +
                        ' - ' +
                        item.ruaOrigem
                      }}
                    </td>
                    <td>{{ mascararCepTabela(item.cepDestino) }}</td>
                    <td>
                      {{
                        item.estadoDestino +
                        ' - ' +
                        item.cidadeDestino +
                        ' - ' +
                        item.ruaDestino
                      }}
                    </td>
                    <td>{{ item.distancia }}</td>
                    <td>{{ item.dataInclusao }}</td>
                  </tr>
                  <tr v-if="itemsDistancias.length === 0">
                    <td colspan="6" class="text-center">
                      Nenhum registro cadastrado
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/* eslint-disable */

export default {
  data: () => ({
    loading: false,
    itemsDistancias: [],
  }),

  async mounted() {
    await this.buscarDistancias();
  },

  methods: {
    // Helpers
    mascararCepTabela(cep) {
      if (!cep) return '';

      return cep
        .toString()
        .replace(/\D/g, '')
        .replace(/^(\d{5})(\d{3})$/, '$1-$2');
    },

    async buscarDistancias() {
      this.loading = true;

      try {
        await this.$axios.get('/distancia').then(response => {
          this.itemsDistancias = response.data;
        });
      } catch (error) {
        console.log('Erro: ', error);

        this.$toasted.show('Erro ao consultar distâncias!', {
          type: 'error',
          duration: 3000,
        });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>