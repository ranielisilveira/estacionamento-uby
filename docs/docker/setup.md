# Docker Configuration - Estacionamento Uby

## 🐳 Arquitetura de Containers

```
┌─────────────────────────────────────────────────────────────┐
│                    Docker Compose Network                    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐ │
│  │   Frontend   │    │   Backend    │    │     Chat     │ │
│  │  React:5173  │───▶│ Laravel:9000 │◀───│  Node:3000   │ │
│  └──────────────┘    └──────────────┘    └──────────────┘ │
│         │                    │                    │         │
│         │                    ▼                    │         │
│         │            ┌──────────────┐            │         │
│         │            │    MySQL     │            │         │
│         │            │    :3306     │            │         │
│         │            └──────────────┘            │         │
│         │                    │                    │         │
│         │                    ▼                    │         │
│         │            ┌──────────────┐            │         │
│         └───────────▶│    Nginx     │◀───────────┘         │
│                      │    :80       │                      │
│                      └──────────────┘                      │
│                             │                               │
│                             ▼                               │
│                      ┌──────────────┐                      │
│                      │    Redis     │                      │
│                      │    :6379     │                      │
│                      └──────────────┘                      │
└─────────────────────────────────────────────────────────────┘
```

## 📋 Containers

### 1. Backend (Laravel + PHP-FPM)
- **Imagem Base:** `php:8.2-fpm-alpine`
- **Porta:** 9000 (PHP-FPM)
- **Volumes:**
  - `./backend:/var/www/html`
  - `./backend/storage:/var/www/html/storage`
- **Dependências:** MySQL, Redis

### 2. Nginx (Reverse Proxy)
- **Imagem Base:** `nginx:alpine`
- **Porta:** 80 → exposta externamente
- **Volumes:**
  - `./nginx/conf.d:/etc/nginx/conf.d`
  - `./backend/public:/var/www/html/public`
- **Função:** Servir arquivos estáticos e proxy para PHP-FPM

### 3. MySQL
- **Imagem Base:** `mysql:8.0`
- **Porta:** 3306
- **Volumes:**
  - `mysql_data:/var/lib/mysql`
- **Variáveis de Ambiente:**
  - `MYSQL_DATABASE=estacionamento_uby`
  - `MYSQL_USER=laravel`
  - `MYSQL_PASSWORD=secret`

### 4. Redis
- **Imagem Base:** `redis:7-alpine`
- **Porta:** 6379
- **Volumes:**
  - `redis_data:/data`
- **Função:** Cache, sessões, queues

### 5. Frontend (React + Vite)
- **Imagem Base:** `node:20-alpine`
- **Porta:** 5173
- **Volumes:**
  - `./frontend:/app`
  - `/app/node_modules` (volume anônimo)
- **Comando:** `npm run dev -- --host`

### 6. Chat (Node.js + Socket.io)
- **Imagem Base:** `node:20-alpine`
- **Porta:** 3000
- **Volumes:**
  - `./chat:/app`
  - `/app/node_modules` (volume anônimo)
- **Dependências:** MySQL, Redis

---

## 📝 docker-compose.yml

```yaml
version: '3.8'

services:
  # Backend Laravel + PHP-FPM
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: estacionamento-backend
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./backend:/var/www/html
      - ./backend/storage:/var/www/html/storage
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=estacionamento_uby
      - DB_USERNAME=laravel
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    depends_on:
      - mysql
      - redis
    networks:
      - estacionamento-network

  # Nginx Web Server
  nginx:
    image: nginx:alpine
    container_name: estacionamento-nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - ./backend/public:/var/www/html/public
      - ./nginx/conf.d:/etc/nginx/conf.d
    depends_on:
      - backend
    networks:
      - estacionamento-network

  # MySQL Database
  mysql:
    image: mysql:8.0
    container_name: estacionamento-mysql
    restart: unless-stopped
    ports:
      - "3307:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: estacionamento_uby
      MYSQL_USER: laravel
      MYSQL_PASSWORD: secret
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - estacionamento-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis Cache/Queue
  redis:
    image: redis:7-alpine
    container_name: estacionamento-redis
    restart: unless-stopped
    ports:
      - "6380:6379"
    volumes:
      - redis_data:/data
    networks:
      - estacionamento-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Frontend React
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    container_name: estacionamento-frontend
    restart: unless-stopped
    ports:
      - "5173:5173"
    volumes:
      - ./frontend:/app
      - /app/node_modules
    environment:
      - VITE_API_URL=http://localhost:8000/api
      - VITE_CHAT_URL=http://localhost:3000
    networks:
      - estacionamento-network
    command: npm run dev -- --host

  # Chat Service (Node.js + Socket.io)
  chat:
    build:
      context: ./chat
      dockerfile: Dockerfile
    container_name: estacionamento-chat
    restart: unless-stopped
    ports:
      - "3000:3000"
    volumes:
      - ./chat:/app
      - /app/node_modules
    environment:
      - NODE_ENV=development
      - PORT=3000
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=estacionamento_uby
      - DB_USERNAME=laravel
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    depends_on:
      - mysql
      - redis
    networks:
      - estacionamento-network

networks:
  estacionamento-network:
    driver: bridge

volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local
```

---

## 📦 Dockerfiles

### backend/Dockerfile
```dockerfile
FROM php:8.2-fpm-alpine

# Argumentos de build
ARG USER_ID=1000
ARG GROUP_ID=1000

# Instalar dependências do sistema
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    nodejs \
    npm

# Instalar extensões PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli gd xml

# Instalar Redis extension
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário não-root
RUN addgroup -g ${GROUP_ID} laravel && \
    adduser -D -u ${USER_ID} -G laravel laravel

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY --chown=laravel:laravel . .

# Instalar dependências do Composer
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Configurar permissões
RUN chown -R laravel:laravel /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Mudar para usuário não-root
USER laravel

# Expor porta PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
```

### nginx/conf.d/default.conf
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass backend:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### frontend/Dockerfile
```dockerfile
FROM node:20-alpine

WORKDIR /app

# Copiar package.json e package-lock.json
COPY package*.json ./

# Instalar dependências
RUN npm ci

# Copiar código fonte
COPY . .

# Expor porta do Vite
EXPOSE 5173

# Comando padrão
CMD ["npm", "run", "dev", "--", "--host"]
```

### chat/Dockerfile
```dockerfile
FROM node:20-alpine

WORKDIR /app

# Copiar package.json
COPY package*.json ./

# Instalar dependências
RUN npm ci

# Copiar código fonte
COPY . .

# Expor porta do chat
EXPOSE 3000

# Comando padrão
CMD ["npm", "start"]
```

---

## 🚀 Comandos Úteis

### Iniciar todos os containers
```bash
docker-compose up -d
```

### Ver logs
```bash
docker-compose logs -f backend
docker-compose logs -f chat
```

### Entrar no container
```bash
docker-compose exec backend sh
docker-compose exec mysql mysql -u laravel -p
```

### Rodar migrations
```bash
docker-compose exec backend php artisan migrate
```

### Rodar testes
```bash
docker-compose exec backend php artisan test
```

### Limpar cache
```bash
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear
```

### Rebuild de containers
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Parar todos os containers
```bash
docker-compose down
```

### Remover volumes (CUIDADO: apaga dados!)
```bash
docker-compose down -v
```

---

## 🔧 Configuração do Backend (.env)

```env
APP_NAME="Estacionamento Uby"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=estacionamento_uby
DB_USERNAME=laravel
DB_PASSWORD=secret

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@estacionamento-uby.com"
MAIL_FROM_NAME="${APP_NAME}"

# ViaCEP API
VIACEP_API_URL=https://viacep.com.br/ws

# JWT
JWT_SECRET=
JWT_TTL=60

# Frontend URL (CORS)
FRONTEND_URL=http://localhost:5173

# Chat Service
CHAT_SERVICE_URL=http://chat:3000
```

---

## 📊 Health Checks

### Backend
```bash
curl http://localhost:8000/api/health
```

### Chat
```bash
curl http://localhost:3000/health
```

### MySQL
```bash
docker-compose exec mysql mysqladmin ping -h localhost
```

### Redis
```bash
docker-compose exec redis redis-cli ping
```

---

## 🔒 Segurança

### Produção:
1. Remover `APP_DEBUG=true`
2. Usar senhas fortes para MySQL
3. Configurar CORS adequadamente
4. Usar HTTPS (adicionar certificado SSL)
5. Configurar rate limiting
6. Adicionar firewall rules

### Volumes em Produção:
- Usar named volumes para persistência
- Backup regular do volume MySQL
- Logs externos (ELK, CloudWatch)

---

## 📈 Performance

### Otimizações:
1. **OPcache:** Habilitar para PHP
2. **Redis:** Cache de queries e sessões
3. **Nginx:** Gzip compression
4. **MySQL:** Configurar buffer pools
5. **Composer:** `--optimize-autoloader --classmap-authoritative`

---

## ✅ Checklist de Setup

- [ ] Criar `docker-compose.yml`
- [ ] Criar Dockerfiles (backend, frontend, chat)
- [ ] Criar configuração Nginx
- [ ] Configurar `.env` do backend
- [ ] Testar build de todos os containers
- [ ] Verificar conectividade entre containers
- [ ] Rodar migrations
- [ ] Rodar seeders
- [ ] Testar endpoints da API
- [ ] Verificar logs

---

**Próximo passo:** Criar os arquivos Docker e iniciar os containers.
