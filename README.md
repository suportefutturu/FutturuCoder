# Oportunidades Belém

Plataforma gratuita e simplificada que conecta talentos locais (desempregados, autônomos, estudantes) com oportunidades reais de trabalho em Belém, Pará, e região metropolitana.

**Desenvolvido e hospedado pela [Futturu®](https://futturu.com.br)**

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Stack Tecnológica](#stack-tecnológica)
- [Funcionalidades](#funcionalidades)
- [Pré-requisitos](#pré-requisitos)
- [Instalação Local (Desenvolvimento)](#instalação-local-desenvolvimento)
- [Deploy em Cloud Server (Ubuntu)](#deploy-em-cloud-server-ubuntu)
- [Deploy Manual Alternativo](#deploy-manual-alternativo)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Comandos Úteis](#comandos-úteis)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Seed Data](#seed-data)
- [Administração](#administração)
- [Segurança](#segurança)
- [Suporte](#suporte)

---

## Visão Geral

O **Oportunidades Belém** é um MVP SaaS focado em facilitar contatos diretos via WhatsApp entre profissionais e contratantes, com interface mobile-first, leve e de alta performance.

### Público-Alvo
- Profissionais: desempregados, autônomos, estudantes
- Contratantes: empresas, pequenos negócios, particulares
- Região: Belém, Ananindeua, Marituba e região metropolitana

---

## Stack Tecnológica

| Componente | Tecnologia |
|------------|-----------|
| **Backend** | Python 3.12+ com Django 5.x |
| **Frontend** | Django Templates + HTMX + Tailwind CSS + Alpine.js |
| **Banco de Dados** | PostgreSQL 14+ |
| **Mapas** | Leaflet.js + OpenStreetMap |
| **Ícones** | Heroicons/Lucide (SVG inline) |
| **Servidor Web** | Nginx + Gunicorn |
| **Sistema Operacional** | Ubuntu Server 22.04 LTS |
| **Autenticação** | Django Auth (customizado) |

---

## Funcionalidades

### Para Profissionais (Prestadores)
- ✅ Cadastro simplificado com WhatsApp
- ✅ Busca de oportunidades por categoria e bairro
- ✅ Visualização de vagas com mapa interativo
- ✅ Contato direto via WhatsApp
- ✅ Dashboard com histórico de visualizações

### Para Contratantes
- ✅ Publicação de novas oportunidades
- ✅ Gestão de vagas (editar, excluir, acompanhar status)
- ✅ Moderação de conteúdo
- ✅ Dashboard com status das publicações

### Recursos Gerais
- ✅ Landing page com busca rápida
- ✅ Mapa interativo com vagas ativas
- ✅ Filtros dinâmicos com HTMX (sem recarregar página)
- ✅ SEO otimizado para indexação no Google
- ✅ Design mobile-first com Tailwind CSS
- ✅ Painel administrativo Django customizado

---

## Pré-requisitos

### Para Desenvolvimento Local
- Python 3.12 ou superior
- PostgreSQL 14+ ou SQLite (para desenvolvimento)
- pip e virtualenv
- Git

### Para Produção (Cloud Server)
- Ubuntu Server 22.04 LTS
- PostgreSQL 14+
- Nginx
- Domínio configurado com SSL (Let's Encrypt)
- Usuário sudo com permissões adequadas

---

## Instalação Local (Desenvolvimento)

### 1. Clonar o Repositório

```bash
git clone https://github.com/seu-usuario/oportunidades-belem.git
cd oportunidades-belem
```

### 2. Criar Ambiente Virtual

```bash
python3 -m venv venv
source venv/bin/activate  # Linux/Mac
# ou
venv\Scripts\activate  # Windows
```

### 3. Instalar Dependências

```bash
pip install --upgrade pip
pip install -r requirements.txt
```

### 4. Configurar Variáveis de Ambiente

```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas configurações locais:

```env
DEBUG=True
SECRET_KEY=sua-chave-secreta-local
DATABASE_URL=sqlite:///db.sqlite3
ALLOWED_HOSTS=localhost,127.0.0.1
```

### 5. Configurar Banco de Dados

Para desenvolvimento rápido com SQLite:

```bash
python manage.py migrate
```

Para PostgreSQL local:

```bash
# Crie o banco de dados no PostgreSQL
sudo -u postgres psql
CREATE DATABASE oportunidades_belem;
CREATE USER dev_user WITH PASSWORD 'sua_senha';
GRANT ALL PRIVILEGES ON DATABASE oportunidades_belem TO dev_user;
\q

# Atualize o .env
DATABASE_URL=postgres://dev_user:sua_senha@localhost:5432/oportunidades_belem
```

### 6. Popular Dados Iniciais (Seed Data)

```bash
python manage.py seed_data
```

### 7. Executar Servidor de Desenvolvimento

```bash
python manage.py runserver
```

Acesse: `http://localhost:8000`

### 8. Criar Superusuário

```bash
python manage.py createsuperuser
```

Acesse o admin: `http://localhost:8000/admin`

---

## Deploy em Cloud Server (Ubuntu)

Este guia assume um servidor Ubuntu 22.04 LTS com acesso root/sudo.

### 1. Atualizar Sistema e Instalar Dependências

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y python3-pip python3-venv python3-dev \
    libpq-dev gcc postgresql postgresql-contrib \
    nginx curl git supervisor
```

### 2. Configurar PostgreSQL

```bash
# Acessar PostgreSQL
sudo -u postgres psql

# Criar banco e usuário
CREATE DATABASE oportunidades_belem;
CREATE USER ob_user WITH PASSWORD 'senha_forte_aqui';
ALTER ROLE ob_user SET client_encoding TO 'utf8';
ALTER ROLE ob_user SET default_transaction_isolation TO 'read committed';
ALTER ROLE ob_user SET timezone TO 'America/Santarem';
GRANT ALL PRIVILEGES ON DATABASE oportunidades_belem TO ob_user;
\q
```

### 3. Configurar Usuário da Aplicação

```bash
sudo useradd -m -s /bin/bash ob_app
sudo passwd ob_app
```

### 4. Clonar Projeto

```bash
sudo -i -u ob_app
cd /home/ob_app
git clone https://github.com/seu-usuario/oportunidades-belem.git
cd oportunidades-belem
```

### 5. Criar Ambiente Virtual

```bash
python3 -m venv venv
source venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
```

### 6. Configurar Variáveis de Ambiente

```bash
nano .env
```

Conteúdo mínimo para produção:

```env
DEBUG=False
SECRET_KEY=sua-chave-secreta-muito-forte-e-aleatoria
DATABASE_URL=postgres://ob_user:senha_forte_aqui@localhost:5432/oportunidades_belem
ALLOWED_HOSTS=seudominio.com,www.seudominio.com
CSRF_TRUSTED_ORIGINS=https://seudominio.com,https://www.seudominio.com
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_HOST_USER=seu-email@gmail.com
EMAIL_HOST_PASSWORD=sua-senha-de-app
DEFAULT_FROM_EMAIL=contato@seudominio.com
WHATSAPP_NUMBER=5591999999999
```

### 7. Configurar Coleta de Arquivos Estáticos

```bash
source venv/bin/activate
python manage.py collectstatic --noinput
```

### 8. Executar Migrações

```bash
python manage.py migrate
```

### 9. Popular Dados Iniciais

```bash
python manage.py seed_data
```

### 10. Criar Superusuário

```bash
python manage.py createsuperuser
```

### 11. Configurar Gunicorn

Criar arquivo de serviço systemd:

```bash
sudo nano /etc/systemd/system/gunicorn-ob.service
```

Conteúdo:

```ini
[Unit]
Description=Gunicorn instance to serve Oportunidades Belém
After=network.target

[Service]
User=ob_app
Group=www-data
WorkingDirectory=/home/ob_app/oportunidades-belem
ExecStart=/home/ob_app/oportunidades-belem/venv/bin/gunicorn \
    --access-logfile - \
    --workers 3 \
    --bind unix:/home/ob_app/oportunidades-belem/gunicorn.sock \
    oportunidades_belem.wsgi:application

Restart=on-failure
RestartSec=5s

[Install]
WantedBy=multi-user.target
```

Habilitar e iniciar serviço:

```bash
sudo systemctl daemon-reload
sudo systemctl start gunicorn-ob
sudo systemctl enable gunicorn-ob
sudo systemctl status gunicorn-ob
```

### 12. Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/oportunidades-belem
```

Conteúdo:

```nginx
server {
    listen 80;
    server_name seudominio.com www.seudominio.com;

    location = /favicon.ico { access_log off; log_not_found off; }
    
    location /static/ {
        alias /home/ob_app/oportunidades-belem/staticfiles/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location /media/ {
        alias /home/ob_app/oportunidades-belem/media/;
        expires 7d;
    }

    location / {
        include proxy_params;
        proxy_pass http://unix:/home/ob_app/oportunidades-belem/gunicorn.sock;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Headers de segurança
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

Habilitar site:

```bash
sudo ln -s /etc/nginx/sites-available/oportunidades-belem /etc/nginx/sites-enabled
sudo nginx -t
sudo systemctl restart nginx
```

### 13. Configurar SSL com Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d seudominio.com -d www.seudominio.com
```

Certbot irá:
- Obter certificado SSL gratuito
- Configurar redirecionamento HTTP → HTTPS automaticamente
- Agendar renovação automática

### 14. Configurar Firewall

```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

### 15. Configurar Backup Automático

Criar script de backup:

```bash
sudo nano /home/ob_app/backup_db.sh
```

Conteúdo:

```bash
#!/bin/bash
BACKUP_DIR="/home/ob_app/backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="oportunidades_belem"
DB_USER="ob_user"

mkdir -p $BACKUP_DIR
PGPASSWORD="senha_forte_aqui" pg_dump -U $DB_USER -h localhost $DB_NAME > $BACKUP_DIR/backup_$DATE.sql
gzip $BACKUP_DIR/backup_$DATE.sql

# Manter apenas últimos 7 backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

Tornar executável e agendar:

```bash
chmod +x /home/ob_app/backup_db.sh
sudo crontab -e
```

Adicionar linha (backup diário às 3 AM):

```cron
0 3 * * * /home/ob_app/backup_db.sh
```

### 16. Monitoramento e Logs

```bash
# Logs do Gunicorn
sudo journalctl -u gunicorn-ob -f

# Logs do Nginx
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# Status dos serviços
sudo systemctl status gunicorn-ob
sudo systemctl status nginx
sudo systemctl status postgresql
```

---

## Deploy Manual Alternativo

Para ambientes sem acesso root ou servidores compartilhados.

### Opção A: Usando Docker

#### 1. Pré-requisitos
- Docker e Docker Compose instalados

#### 2. Estrutura Docker

Criar `docker-compose.yml`:

```yaml
version: '3.8'

services:
  db:
    image: postgres:14
    volumes:
      - postgres_data:/var/lib/postgresql/data
    environment:
      POSTGRES_DB: oportunidades_belem
      POSTGRES_USER: ob_user
      POSTGRES_PASSWORD: senha_forte
    ports:
      - "5432:5432"

  web:
    build: .
    command: gunicorn --bind 0.0.0.0:8000 oportunidades_belem.wsgi:application
    volumes:
      - .:/app
      - static_volume:/app/staticfiles
      - media_volume:/app/media
    ports:
      - "8000:8000"
    depends_on:
      - db
    env_file:
      - .env

  nginx:
    image: nginx:alpine
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - static_volume:/app/staticfiles
      - media_volume:/app/media
    ports:
      - "80:80"
    depends_on:
      - web

volumes:
  postgres_data:
  static_volume:
  media_volume:
```

Criar `Dockerfile`:

```dockerfile
FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

WORKDIR /app

RUN apt-get update && apt-get install -y \
    libpq-dev \
    gcc \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --upgrade pip && pip install -r requirements.txt

COPY . .

RUN python manage.py collectstatic --noinput

CMD ["gunicorn", "--bind", "0.0.0.0:8000", "oportunidades_belem.wsgi:application"]
```

#### 3. Executar

```bash
docker-compose up -d
docker-compose run web python manage.py migrate
docker-compose run web python manage.py seed_data
docker-compose run web python manage.py createsuperuser
```

### Opção B: Usando Render/Railway/Fly.io

#### Render.com

1. Criar novo Web Service
2. Conectar repositório GitHub
3. Configurar variáveis de ambiente
4. Build Command: `pip install -r requirements.txt && python manage.py collectstatic --noinput`
5. Start Command: `gunicorn oportunidades_belem.wsgi:application`
6. Adicionar banco PostgreSQL como serviço separado

#### Railway.app

1. Novo Projeto → Deploy from GitHub
2. Adicionar PostgreSQL
3. Configurar variáveis de ambiente automaticamente
4. Deploy automático a cada push

### Opção C: Hospedagem Compartilhada (PythonAnywhere)

1. Criar conta em [PythonAnywhere](https://www.pythonanywhere.com)
2. Upload do código via Git ou FTP
3. Criar virtualenv pelo painel
4. Instalar dependências
5. Configurar WSGI file no painel
6. Configurar banco MySQL ou PostgreSQL
7. Rodar migrações via console
8. Configurar arquivos estáticos

---

## Estrutura do Projeto

```
oportunidades-belem/
├── accounts/                 # App de autenticação e usuários
│   ├── models.py            # CustomUser (estende AbstractUser)
│   ├── forms.py             # Formulários de registro/login
│   ├── views.py             # Views de autenticação
│   └── urls.py              # Rotas de autenticação
├── core/                     # App principal
│   ├── models.py            # Categoria, Bairro
│   ├── views.py             # Landing page, mapas
│   ├── context_processors.py # Variáveis globais de template
│   └── management/          # Commands personalizados
│       └── commands/
│           └── seed_data.py # Popular dados iniciais
├── jobs/                     # App de oportunidades
│   ├── models.py            # Oportunidade
│   ├── forms.py             # Formulários de vaga
│   ├── views.py             # CRUD de oportunidades
│   └── urls.py              # Rotas de vagas
├── oportunidades_belem/     # Configurações do projeto
│   ├── settings.py          # Configurações Django
│   ├── urls.py              # URLs principais
│   └── wsgi.py              # Configuração WSGI
├── templates/               # Templates HTML
│   ├── base.html           # Template base
│   ├── core/               # Templates do app core
│   ├── accounts/           # Templates de autenticação
│   └── jobs/               # Templates de vagas
├── static/                  # Arquivos estáticos
│   ├── css/                # Estilos customizados
│   └── js/                 # JavaScript customizado
├── media/                   # Uploads de usuários
├── requirements.txt         # Dependências Python
├── manage.py               # Script de gerenciamento Django
├── .env.example            # Exemplo de variáveis de ambiente
└── README.md               # Este arquivo
```

---

## Comandos Úteis

### Desenvolvimento

```bash
# Rodar servidor
python manage.py runserver

# Criar migrações
python manage.py makemigrations

# Aplicar migrações
python manage.py migrate

# Criar superusuário
python manage.py createsuperuser

# Popular dados de teste
python manage.py seed_data

# Rodar testes
python manage.py test

# Verificar erros de código
python manage.py check

# Limpar cache
python manage.py clear_cache
```

### Produção

```bash
# Coletar estáticos
python manage.py collectstatic --noinput

# Reiniciar Gunicorn
sudo systemctl restart gunicorn-ob

# Recarregar Nginx
sudo systemctl reload nginx

# Ver logs
sudo journalctl -u gunicorn-ob -f

# Backup manual
/home/ob_app/backup_db.sh
```

---

## Variáveis de Ambiente

| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `DEBUG` | Modo debug (False em produção) | `False` |
| `SECRET_KEY` | Chave secreta Django | `abc123...` |
| `DATABASE_URL` | URL de conexão do banco | `postgres://user:pass@host:5432/db` |
| `ALLOWED_HOSTS` | Domínios permitidos | `dominio.com,www.dominio.com` |
| `CSRF_TRUSTED_ORIGINS` | Origens confiáveis CSRF | `https://dominio.com` |
| `EMAIL_HOST` | Servidor SMTP | `smtp.gmail.com` |
| `EMAIL_PORT` | Porta SMTP | `587` |
| `EMAIL_HOST_USER` | Usuário email | `contato@dominio.com` |
| `EMAIL_HOST_PASSWORD` | Senha email/app | `senha_app` |
| `DEFAULT_FROM_EMAIL` | Email remetente padrão | `noreply@dominio.com` |
| `WHATSAPP_NUMBER` | Número WhatsApp suporte | `5591999999999` |

---

## Seed Data

O comando `seed_data` popula o banco com:

- **Bairros**: Belém (Centro, Umarizal, Batista Campos, etc.), Ananindeua, Marituba
- **Categorias**: Serviços, Vagas CLT, Estágios, Bicos, Freelance
- **Usuários de teste**: Prestadores e contratantes fictícios
- **Oportunidades**: 10-20 vagas de exemplo para testes

```bash
python manage.py seed_data
```

---

## Administração

### Acesso ao Admin Django

URL: `https://seudominio.com/admin`

### Funcionalidades do Admin

- ✅ Aprovar/rejeitar oportunidades pendentes
- ✅ Gerenciar usuários (prestadores/contratantes)
- ✅ Cadastrar/editar categorias e bairros
- ✅ Visualizar logs de auditoria
- ✅ Actions em massa (aprovar múltiplas vagas)

### Fluxo de Moderação

1. Contratante publica vaga → Status: `pendente`
2. Moderador revisa no admin
3. Aprova → Status: `aprovado` (visível no site)
4. Rejeita → Status: `rejeitado` (notifica autor)

---

## Segurança

### Medidas Implementadas

- ✅ CSRF Protection (nativo Django)
- ✅ SQL Injection Protection (ORM Django)
- ✅ XSS Protection (templates auto-escape)
- ✅ Clickjacking Protection (X-Frame-Options)
- ✅ HTTPS forçado (produção)
- ✅ SECRET_KEY em variáveis de ambiente
- ✅ Validação de WhatsApp brasileiro
- ✅ Senhas hash (PBKDF2)

### Recomendações Adicionais

- Renovar certificados SSL antes do vencimento
- Manter sistema operacional atualizado
- Monitorar logs regularmente
- Backup diário do banco de dados
- Usar senhas fortes para todos os acessos

---

## Suporte

### Documentação

- [Django Documentation](https://docs.djangoproject.com/)
- [HTMX Documentation](https://htmx.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Leaflet.js](https://leafletjs.com/)

### Contato

- **Email**: contato@futturu.com.br
- **Website**: [futturu.com.br](https://futturu.com.br)

### Contribuição

Contribuições são bem-vindas! Por favor:

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

---

## Licença

Este projeto é desenvolvido pela **Futturu®** para fins educacionais e comerciais.

© 2024 Futturu® - Todos os direitos reservados.

---

<div align="center">

**Plataforma desenvolvida e hospedada pela [Futturu®](https://futturu.com.br)**

Conectando talentos e oportunidades em Belém e região metropolitana.

</div>