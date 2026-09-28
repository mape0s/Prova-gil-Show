# IFBANK — Sistema Agência Bancária

Projeto acadêmico IFPR com Laravel 13+, Svelte 5+, Blade, REST API, Sanctum, Breeze, Policies, padrão Controller-Service-Repository, Eloquent, migrations/seeders, Laravel Auditing e integração de e-mail.

## Requisitos

- PHP 8.3+
- Composer 2+
- Node.js 20+ e npm
- SQLite para execução local sem Docker
- Docker Desktop opcional

## Windows PowerShell — sequência pronta

Abra o PowerShell na pasta do projeto e execute uma linha por vez:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Para desenvolvimento do Svelte com atualização automática, deixe `php artisan serve` em um terminal e, em outro, execute `npm run dev`.

## Execução local — sem Docker

No diretório do projeto:

```bash
composer install
cp .env.example .env
php artisan key:generate
mkdir -p database
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
```

Em um terminal:

```bash
php artisan serve
```

Em outro terminal, para desenvolvimento Svelte com HMR:

```bash
npm run dev
```

A aplicação Laravel fica em `http://127.0.0.1:8000`. A SPA Svelte do cliente é servida pelo Laravel em `/spa` (o Vite fornece HMR em `5173` durante o desenvolvimento). O painel administrativo usa `/login` ou `/admin/login`.

No Windows PowerShell, se `touch` não existir:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
```

## Execução com Docker

```bash
docker compose up -d --build
docker compose ps
```

A aplicação fica em `http://localhost:15100`, phpMyAdmin em `http://localhost:15101` e MySQL exposto em `localhost:15102`.

O compose usa um volume próprio e nomes de containers próprios para não colidir com outros projetos.

## Contas de demonstração

O seeder cria:

- Gerente Geral: `gerente.geral@banco.test` / `senha123`
- Gerente de Conta: `gerente.conta@banco.test` / `senha123`
- Cliente: `cliente@banco.test` / `senha123`

## Regras principais

- Cliente bloqueado pode consultar apenas o saldo.
- PIX verifica destinatário, bloqueio, autoenvio e disponibilidade.
- Disponibilidade de PIX = saldo + limite aprovado. Assim, saldo de R$ 1.000, limite de R$ 500 e PIX de R$ 1.200 deixam saldo em -R$ 200; novo PIX de R$ 300 deixa saldo em -R$ 500; qualquer valor acima disso é recusado.
- Aplicações não consomem limite: exigem saldo positivo disponível.
- Resgate retorna o valor da aplicação para o saldo.
- Gerente de Conta só acessa clientes da própria carteira.
- Policy restringe bloqueio/desbloqueio ao gerente responsável.
- Gerente Geral aprova/reprova solicitações de aumento de limite.
- Auditoria registra alterações nos modelos auditáveis.

## Mailtrap

Para SMTP, preencha no `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=SEU_USERNAME
MAIL_PASSWORD=SUA_PASSWORD
```

O projeto envia credenciais de novos gerentes e clientes pela `CredenciaisAcessoMail`.

## Testes

```bash
php artisan test
```

Os testes usam SQLite em memória.
