# Projeto CEPs

Este projeto utiliza Docker para manipular **frontend**, **backend**, **RabbitMQ** e **MySQL**.

## Como executar o projeto:
1. [Instalar Docker](https://docs.docker.com/desktop/setup/install/windows-install/)
2. [Clonar repositório](https://github.com/ngpNathan/distancia-ceps)
3. Na pasta do projeto clonado executar o comando: `docker compose build`
    1. O frontend ficará disponível em http://localhost:3000
    2. O backend ficará disponível em http://localhost:8081
    3. O painel do RabbitMQ ficará disponível em http://localhost:15672/#/
    4. O banco de dados pode ser acessado através do próprio Docker com a sequência dos comandos:
        1. `docker exec -it app_db bash`
        2. `mysql -u root -p ceps`