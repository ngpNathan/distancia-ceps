1. Instalar PHP 7.2.29 (https://windows.php.net/downloads/releases/archives/php-7.2.29-nts-Win32-VC15-x64.zip)
2. Instalar NodeJS para ter acesso ao NPM (https://nodejs.org/en/download)
3. Instalar pacotes e dependências do frontend acessando a pasta /frontend com o comando: npm install
4. Rodar ambiente backend local com o comando: php -S localhost:8081 router.php
5. Rodar ambiente frontend local com o comando: npm run serve
6. Criar banco de dados MySQL local e executar comandos da pasta banco_dados para criar objetos/tabelas que irão persistir os dados (Dependendo será necessário também atuaizar os parâmetros de conexão do backend PHP)