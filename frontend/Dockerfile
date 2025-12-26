# ---------- STAGE 1: build ----------
FROM node:22.18.0-alpine AS build

WORKDIR /app

# Copia dependências
COPY package*.json ./
RUN npm install

# Copia código
COPY . .

# Build do Vue
RUN npm run build


# ---------- STAGE 2: nginx ----------
FROM nginx:alpine

# Remove config padrão
RUN rm /etc/nginx/conf.d/default.conf

# Config customizada
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copia build
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
