FROM php:7.4-cli-bullseye

# Instalar dependências PECL + compilação
RUN apt-get update && apt-get install -y \
    librabbitmq-dev \
    libicu-dev \
    libonig-dev \
    libmariadb-dev-compat \
    libsodium-dev \
    pkg-config \
    build-essential \
    autoconf \
    unzip \
    git \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Habilitar extensões nativas já presentes no PHP CLI
RUN docker-php-ext-install pdo pdo_mysql intl mbstring bcmath

# Instalar e habilitar AMQP via PECL
RUN pecl install amqp-1.11.0 \
    && docker-php-ext-enable amqp

# Definir fuso horário
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Diretório de trabalho
WORKDIR /app
COPY . /app

COPY cacert.pem /etc/ssl/certs/cacert.pem

# php.ini – NÃO habilite extensões manualmente
COPY php.ini /usr/local/etc/php/php.ini

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8081

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]