
# Projeto CEPs – Cálculo de Distância entre CEPs

Este projeto tem como objetivo realizar o cadastro e a consulta de distâncias entre CEPs, utilizando validação por API externa e cálculo interno de distância geográfica.

A aplicação foi desenvolvida com as seguintes tecnologias:
- **Frontend:** VueJS2, Bootstrap4 e outros pacotes do npm
- **Backend:** PHP
- **Banco de dados:** MySQL
- **Controle de filas:** RabbitMQ
- **Docker:** Combinar e manter serviços de **Frontend**, **Backend**, **MySQL** e **RabbitMQ**.

---

## Funcionalidades

- Cadastro manual de distância entre CEP de origem e CEP de destino
- Validação dos CEPs através da [BrasilAPI](https://brasilapi.com.br/docs#tag/CEP-V2)
- Cálculo da distância geográfica através do método de [Haversine](https://codidata.com.br/glossario/o-que-e-haversine/#:~:text=O%20Haversine%20%C3%A9%20uma%20f%C3%B3rmula,considera%C3%A7%C3%A3o%20a%20curvatura%20da%20Terra.)
- Persistência dos dados em banco MySQL
- Listagem das distâncias já calculadas
- Importação em massa de CEPs via arquivo CSV
- Processamento assíncrono da importação utilizando RabbitMQ

---

## Observações importantes

- A BrasilAPI pode eventualmente não retornar coordenadas para determinados CEPs. Esse cenário é tratado pela aplicação.
- Para evitar bloqueio da API externa, o sistema foi configurado inicialmente para realizar **no máximo 3 consultas por minuto**.
- O uso do RabbitMQ está relacionado ao processamento das importações em massa.

---

## Como executar o projeto

### Pré-requisitos
- Docker instalado  
  https://docs.docker.com/desktop/setup/install/windows-install/

### Passos

1. Clonar o repositório:
```bash
git clone https://github.com/ngpNathan/distancia-ceps
````

2. Acessar a pasta do projeto:

```bash
cd distancia-ceps
```

3. Buildar os containers:

```bash
docker compose build
```

4. Subir o ambiente:

```bash
docker compose up
```

---

## Serviços disponíveis

* Frontend: [http://localhost:3000](http://localhost:3000)
* Backend: [http://localhost:8081](http://localhost:8081)
* RabbitMQ (Painel): [http://localhost:15672/#/](http://localhost:15672/#/)

---

## Acesso ao banco de dados

O banco MySQL pode ser acessado via Docker:

```bash
docker exec -it app_db bash
mysql -u root -p ceps
```