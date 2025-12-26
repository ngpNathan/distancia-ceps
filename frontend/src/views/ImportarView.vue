<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Importar distâncias por CEP (CSV)</h5>
      </div>

      <div class="card-body">
        <form @submit.prevent="enviarArquivo">
          <div class="form-group">
            <label>Arquivo CSV</label>
            <input
              type="file"
              class="form-control-file"
              accept=".csv"
              @change="onFileChange"
            />
            <small class="form-text text-muted">
              O arquivo deve conter as colunas: <strong>cepOrigem</strong> e
              <strong>cepFim</strong>
            </small>
          </div>

          <button class="btn btn-primary" :disabled="loading || !arquivo">
            <span
              v-if="loading"
              class="spinner-border spinner-border-sm mr-2"
            ></span>
            Importar
          </button>
        </form>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0">Acompanhar importações</h5>
      </div>

      <div class="card-body p-0">
        <div v-if="!importacoes.length" class="p-3 text-muted">
          Nenhuma importação encontrada.
        </div>

        <div class="accordion" id="accordionImportacoes" v-else>
          <div
            class="card mb-0"
            v-for="importacao in importacoes"
            :key="importacao.id"
          >
            <div
              class="card-header d-flex justify-content-between align-items-center"
            >
              <div>
                <strong>{{ importacao.nomeArquivo }}</strong>
                <br />
                <small class="text-muted">
                  {{
                    periodoImportacao(
                      importacao.dataInclusao,
                      importacao.dataFim
                    )
                  }}
                </small>
              </div>

              <div class="d-flex align-items-center">
                <span
                  class="badge mr-3"
                  :class="statusClass(importacao.status)"
                >
                  {{ importacao.status }}
                </span>

                <button
                  class="btn btn-sm btn-outline-primary"
                  data-toggle="collapse"
                  :data-target="'#collapse' + importacao.id"
                >
                  Detalhes
                </button>
              </div>
            </div>

            <div
              :id="'collapse' + importacao.id"
              class="collapse"
              data-parent="#accordionImportacoes"
            >
              <div class="card-body">
                <div
                  class="d-flex justify-content-between align-items-center mb-2"
                >
                  <div class="flex-grow-1 mr-3">
                    <div class="progress" style="height: 20px">
                      <div
                        class="progress-bar"
                        role="progressbar"
                        :style="{ width: progressPercent(importacao) + '%' }"
                        :class="progressClass(importacao)"
                        :aria-valuenow="progressPercent(importacao)"
                        aria-valuemin="0"
                        aria-valuemax="100"
                      >
                        {{ progressPercent(importacao) }}%
                      </div>
                    </div>
                  </div>

                  <div class="text-nowrap">
                    <strong>{{ importacao.itensProcessados }}</strong> /
                    {{ importacao.totalItens }}
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>CEP Origem</th>
                        <th>CEP Destino</th>
                        <th>Status</th>
                        <th>Data Inicio</th>
                        <th>Data Fim</th>
                        <th>Mensagem</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in importacao.itens" :key="item.id">
                        <td>{{ item.id }}</td>
                        <td>{{ mascararCepTabela(item.cepOrigem) }}</td>
                        <td>{{ mascararCepTabela(item.cepDestino) }}</td>
                        <td>
                          <span
                            class="badge"
                            :class="itemStatusClass(item.status)"
                          >
                            {{ item.status }}
                          </span>
                        </td>
                        <td>{{ item.dataInclusao | formataData }}</td>
                        <td>{{ item.dataFim | formataData }}</td>
                        <td class="text-danger">
                          {{ item.mensagemErro || '-' }}
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
    </div>
  </div>
</template>

<script>
export default {
  name: 'ImportacaoCsv',

  data() {
    return {
      loading: false,
      arquivo: null,
      importacoes: [],
      timerImportacoes: null,
    };
  },

  async mounted() {
    this.initTimerImportacoes();
    this.getImportacoes();
  },

  beforeDestroy() {
    clearInterval(this.timerImportacoes);
  },

  methods: {
    periodoImportacao(inicio, fim) {
      if (!inicio) return '';

      const inicioFormatado = this.$options.filters.formataData(inicio);

      if (!fim) return inicioFormatado;

      return `${inicioFormatado} - ${this.$options.filters.formataData(fim)}`;
    },

    progressPercent(importacao) {
      if (!importacao.totalItens || importacao.totalItens === 0) {
        return 0;
      }

      const percent = Math.round(
        (importacao.itensProcessados / importacao.totalItens) * 100
      );

      return Math.min(percent, 100);
    },

    progressClass(importacao) {
      if (importacao.status === 'ERRO') {
        return 'bg-danger';
      }

      if (importacao.status === 'FINALIZADA') {
        return 'bg-success';
      }

      return 'bg-warning';
    },

    mascararCepTabela(cep) {
      if (!cep) return '';

      return cep
        .toString()
        .replace(/\D/g, '')
        .replace(/^(\d{5})(\d{3})$/, '$1-$2');
    },

    statusClass(status) {
      switch (status) {
        case 'PENDENTE':
          return 'badge-warning';
        case 'PROCESSANDO':
          return 'badge-info';
        case 'FINALIZADA':
          return 'badge-success';
        case 'ERRO':
          return 'badge-danger';
        default:
          return 'badge-light';
      }
    },

    itemStatusClass(status) {
      switch (status) {
        case 'PENDENTE':
          return 'badge-warning';
        case 'PROCESSADO':
          return 'badge-success';
        case 'ERRO':
          return 'badge-danger';
        default:
          return 'badge-light';
      }
    },

    initTimerImportacoes() {
      this.timerImportacoes = setInterval(() => {
        this.getImportacoes();
      }, 10000);
    },

    async getImportacoes() {
      this.loading = true;

      try {
        const response = await this.$axios.get('/importacao');
        this.importacoes = response.data;
      } catch (error) {
        console.log('Erro: ', error);
        this.$toasted.show('Ocorreu um erro ao buscar importações!', {
          type: 'error',
          duration: 3000,
        });
      } finally {
        this.loading = false;
      }
    },

    onFileChange(event) {
      const file = event.target.files[0];

      if (!file) {
        this.arquivo = null;
        return;
      }

      if (!file.name.endsWith('.csv')) {
        this.$toasted.show('O arquivo deve ser do tipo .csv!', {
          type: 'error',
          duration: 3000,
        });
        this.arquivo = null;
        return;
      }

      this.arquivo = file;
    },

    async enviarArquivo() {
      if (!this.arquivo) return;

      this.loading = true;
      this.mensagem = '';

      const formData = new FormData();
      formData.append('arquivo', this.arquivo);

      try {
        await this.$axios.post('/importacao', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        this.$toasted.show('Arquivo enviado para importação com sucesso!', {
          type: 'success',
          duration: 3000,
        });
      } catch (error) {
        console.log('Erro: ', error);
        this.$toasted.show('Erro ao enviar arquivo para importação!', {
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
