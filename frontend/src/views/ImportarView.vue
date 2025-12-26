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
  </div>
</template>

<script>
export default {
  name: 'ImportacaoCsv',

  data() {
    return {
      arquivo: null,
      loading: false,
    };
  },

  methods: {
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
