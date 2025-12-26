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
              <form
                @submit.prevent="adicionarDistancia"
                method="post"
                ref="formCeps"
              >
                <div class="form-group">
                  <label for="cepOrigem">CEP de Origem:</label>
                  <input
                    type="text"
                    id="cepOrigem"
                    class="form-control"
                    v-model="cepOrigem"
                    v-mask="'#####-###'"
                    :disabled="formDisabled"
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
                    :disabled="formDisabled"
                    placeholder="00000-000"
                    required
                  />
                </div>
                <button
                  type="submit"
                  :disabled="formDisabled"
                  class="btn btn-primary btn-block"
                >
                  Calcular
                </button>
              </form>
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
    qtdReqApi: 0,
    cepOrigem: '88318481',
    cepDestino: '89041003',
    urlBrasilApiValue: null,
    limiteReqApiValue: null,
  }),

  computed: {
    disabledLimiteReqApi() {
      return this.qtdReqApi >= this.limiteReqApiValue;
    },

    formDisabled() {
      return (
        !this.urlBrasilApiValue || this.disabledLimiteReqApi || this.loading
      );
    },
  },

  async mounted() {
    await this.buscarParametroUrlBrasilApi();
    await this.buscarParametroLimiteReqApi();

    this.initTimerLimiteReqApi();
  },

  beforeDestroy() {
    localStorage.clear();
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

    somenteNumeros(str) {
      return str.replace(/[^0-9]+/g, '');
    },

    // Parâmetros
    async buscarParametroUrlBrasilApi() {
      this.loading = true;

      try {
        const response = await this.$axios.get('/parametro/URLBrasilApi');
        this.urlBrasilApiValue = response.data.valor;
      } catch (error) {
        console.log('Erro: ', error);

        this.$toasted.show('Erro ao consultar parâmetro da BrasilAPI!', {
          type: 'error',
          duration: 3000,
        });
      } finally {
        this.loading = false;
      }
    },

    async buscarParametroLimiteReqApi() {
      this.loading = true;

      try {
        const response = await this.$axios.get('/parametro/QtdLmtReqAPI');
        this.limiteReqApiValue = Number(response.data.valor);
      } catch (error) {
        console.log('Erro: ', error);

        this.$toasted.show('Erro ao consultar parâmetro da BrasilAPI!', {
          type: 'error',
          duration: 3000,
        });
      } finally {
        this.loading = false;
      }
    },

    // A cada 1 minuto limita o número de requisições e cálculos da API
    initTimerLimiteReqApi() {
      setInterval(() => {
        this.qtdReqApi = 0;
      }, 10000);
    },

    async consultaBrasilAPI(cep) {
      try {
        const keyCep = `cep_${cep}`;
        if (localStorage.getItem(keyCep) != null) {
          return JSON.parse(localStorage.getItem(keyCep));
        }

        const { data } = await this.$axios.get(this.urlBrasilApiValue + cep);

        if (
          data?.location?.coordinates?.latitude &&
          data?.location?.coordinates.longitude
        ) {
          localStorage.setItem(keyCep, JSON.stringify(data));
        }

        return data;
      } catch (error) {
        console.log('Erro: ', error);

        throw error;
      }
    },

    // Distâncias
    // Fórmula de Haversine que considera curvatura da terra e retorna distância em KM
    calcularDistanciaHaversine(
      { latitude: latitude1, longitude: longitude1 },
      { latitude: latitude2, longitude: longitude2 }
    ) {
      const raioTerra = 6371; // Raio da Terra em km

      const dLat = ((latitude2 - latitude1) * Math.PI) / 180;
      const dLon = ((longitude1 - longitude2) * Math.PI) / 180;

      const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((latitude1 * Math.PI) / 180) *
          Math.cos((latitude2 * Math.PI) / 180) *
          Math.sin(dLon / 2) *
          Math.sin(dLon / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

      const distancia = raioTerra * c;

      return distancia.toFixed(2);
    },

    async adicionarDistancia() {
      this.loading = true;

      if (this.disabledLimiteReqApi) {
        this.loading = false;
        this.$toasted.show(
          `Você atingiu o máximo de ${this.limiteReqApiValue} requisições no intervalo de 1 minuto, aguarde para realizar novas consultas!`,
          { type: 'error', duration: 3000 }
        );
        return;
      }

      const _cepOrigem = this.somenteNumeros(this.cepOrigem);
      const _cepDestino = this.somenteNumeros(this.cepDestino);

      if (_cepOrigem.length != 8 || _cepDestino.length != 8) {
        this.loading = false;
        this.$toasted.show(
          'CEP inválido, por favor informe o CEP de origem e destino corretamente!',
          { type: 'error', duration: 3000 }
        );
        return;
      }

      if (_cepOrigem == _cepDestino) {
        this.loading = false;
        this.$toasted.show('Os CEPs devem ser diferentes!', {
          type: 'error',
          duration: 3000,
        });
        return;
      }

      try {
        const dataCepOrigem = await this.consultaBrasilAPI(_cepOrigem);
        const coordenadasOrigem = dataCepOrigem?.location?.coordinates;

        if (!coordenadasOrigem?.latitude || !coordenadasOrigem?.longitude) {
          throw 'Coordenadas do CEP de origem não encontradas!';
        }

        const dataCepDestino = await this.consultaBrasilAPI(_cepDestino);
        const coordenadasDestino = dataCepDestino.location.coordinates;

        if (!coordenadasDestino?.latitude || !coordenadasDestino?.longitude) {
          throw 'Coordenadas do CEP de destino não encontradas!';
        }

        const distancia = this.calcularDistanciaHaversine(
          coordenadasOrigem,
          coordenadasDestino
        );

        await this.$axios.post('/distancia', {
          cepOrigem: _cepOrigem,
          cepOrigemJson: JSON.stringify(dataCepOrigem),
          cepDestino: _cepDestino,
          cepDestinoJson: JSON.stringify(dataCepDestino),
          distancia,
        });

        this.$toasted.show('Distância calculada e inserida com sucesso!', {
          type: 'success',
          duration: 3000,
        });

        this.cepOrigem = '';
        this.cepDestino = '';
      } catch (error) {
        console.log('Erro: ', error);

        this.$toasted.show('Erro ao calcular e gravar distância entre CEPs!', {
          type: 'error',
          duration: 3000,
        });
      } finally {
        this.qtdReqApi++;
        this.loading = false;
      }
    },
  },
};
</script>
