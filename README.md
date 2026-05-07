# Oportunidades Belém

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

> **Plataforma SaaS gratuita que conecta talentos locais com oportunidades de trabalho em Belém, Pará.**

---

## ☁️ Deploy em Servidor Cloud (Ubuntu 22.04 LTS)

Guia completo passo a passo para deploy em produção.

### Pré-requisitos do Servidor
- CPU: 2 vCPUs mínimo
- RAM: 4GB mínimo  
- Storage: 40GB SSD
- Domínio apontando para o IP do servidor

---

## Passo 1: Acesso e Atualização

```bash
ssh root@seu-ip-servidor
apt update && apt upgrade -y
apt install -y curl git vim ufw fail2ban wget
```

---

## Passo 2: Configurar Firewall (UFW)

```bash
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
ufw status
```

---

## Passo 3: Instalar Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs
node --version && npm --version
```

---

## Passo 4: Instalar PostgreSQL

```bash
apt install -y postgresql postgresql-contrib
systemctl start postgresql
systemctl enable postgresql
```

### Configurar Banco de Dados

```bash
sudo -u postgres psql
```

No prompt do PostgreSQL:
```sql
CREATE DATABASE oportunidades_belem;
CREATE USER belem_user WITH PASSWORD 'SenhaForte123!@#';
GRANT ALL PRIVILEGES ON DATABASE oportunidades_belem TO belem_user;
\c oportunidades_belem
GRANT ALL ON SCHEMA public TO belem_user;
\q
```

---

## Passo 5: Instalar PM2

```bash
npm install -g pm2
pm2 startup systemd -u root --hp /root
# Execute o comando gerado se houver
```

---

## Passo 6: Instalar Nginx

```bash
apt install -y nginx
systemctl start nginx
systemctl enable nginx
```

---

## Passo 7: Clonar Projeto

```bash
cd /var/www
git clone https://github.com/futturu/oportunidades-belem.git
cd oportunidades-belem
```

---

## Passo 8: Configurar Backend

```bash
cd /var/www/oportunidades-belem/backend
npm install --production
nano .env
```

**Conteúdo do .env:**
```env
PORT=3001
NODE_ENV=production
DB_HOST=localhost
DB_PORT=5432
DB_NAME=oportunidades_belem
DB_USER=belem_user
DB_PASSWORD=SenhaForte123!@#
JWT_SECRET=GERAR_COM_NODE_CRYPTO
JWT_EXPIRES_IN=7d
FRONTEND_URL=https://seu-dominio.com
WHATSAPP_BASE_URL=https://wa.me/
MAX_FILE_SIZE=5242880
UPLOAD_PATH=/var/www/oportunidades-belem/backend/uploads
```

Gerar JWT_SECRET:
```bash
node -e "console.log(require('crypto').randomBytes(64).toString('hex'))"
```

---

## Passo 9: Configurar Frontend

```bash
cd /var/www/oportunidades-belem/frontend
npm install
nano .env
```

**Conteúdo do .env:**
```env
VITE_API_URL=https://seu-dominio.com/api
VITE_MAP_CENTER_LAT=-1.4558
VITE_MAP_CENTER_LNG=-48.5039
VITE_MAP_ZOOM=12
VITE_APP_NAME="Oportunidades Belém"
VITE_FUTTURU_BRAND=true
```

---

## Passo 10: Build do Frontend

```bash
cd /var/www/oportunidades-belem/frontend
npm run build
```

---

## Passo 11: Iniciar API com PM2

```bash
cd /var/www/oportunidades-belem/backend
pm2 start npm --name "oportunidades-api" -- start
pm2 save
pm2 status
```

---

## Passo 12: Configurar Nginx

```bash
nano /etc/nginx/sites-available/oportunidades
```

**Configuração:**
```nginx
server {
    listen 80;
    server_name seu-dominio.com www.seu-dominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seu-dominio.com www.seu-dominio.com;

    ssl_certificate /etc/letsencrypt/live/seu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/seu-dominio.com/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;

    root /var/www/oportunidades-belem/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location /uploads {
        alias /var/www/oportunidades-belem/backend/uploads;
        expires 30d;
    }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
}
```

Ativar site:
```bash
ln -s /etc/nginx/sites-available/oportunidades /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

---

## Passo 13: SSL com Let's Encrypt

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d seu-dominio.com -d www.seu-dominio.com
```

Renovação automática já é configurada. Teste:
```bash
certbot renew --dry-run
```

---

## Passo 14: Script de Deploy

```bash
nano /var/www/oportunidades-belem/deploy.sh
```

```bash
#!/bin/bash
cd /var/www/oportunidades-belem
git pull origin main
cd backend && npm install --production
pm2 restart oportunidades-api
cd ../frontend && npm install && npm run build
nginx -t && systemctl reload nginx
echo "Deploy concluído!"
pm2 status
```

Tornar executável:
```bash
chmod +x deploy.sh
```

---

## Passo 15: Backup Automático

```bash
nano /var/www/oportunidades-belem/backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/oportunidades-belem"
mkdir -p $BACKUP_DIR
PGPASSWORD="SenhaForte123!@#" pg_dump -U belem_user -h localhost oportunidades_belem > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
```

Agendar no crontab (diário às 3h):
```bash
crontab -e
# Adicionar: 0 3 * * * /var/www/oportunidades-belem/backup.sh
```

---

## Verificação Final

```bash
# Status dos serviços
systemctl status nginx
systemctl status postgresql
pm2 status

# Testar HTTPS
curl -I https://seu-dominio.com

# Testar API
curl https://seu-dominio.com/api/health

# Ver logs
pm2 logs oportunidades-api
tail -f /var/log/nginx/error.log
```

---

## Troubleshooting

| Problema | Solução |
|----------|---------|
| Porta 3001 ocupada | `lsof -i :3001` e mate o processo |
| Erro CORS | Verifique FRONTEND_URL no .env |
| 502 Bad Gateway | `pm2 status` e `curl http://localhost:3001/api` |
| SSL não funciona | `certbot certificates` e `nginx -t` |

---

## Estrutura de Diretórios

```
/var/www/oportunidades-belem/
├── backend/
│   ├── src/
│   ├── uploads/
│   ├── .env
│   └── package.json
├── frontend/
│   ├── dist/ (build)
│   ├── src/
│   ├── .env
│   └── package.json
├── deploy.sh
├── backup.sh
└── README.md
```

---

**Futturu®** © 2024 - Feito com ❤️ em Belém do Pará
