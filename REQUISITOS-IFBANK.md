# Checklist da avaliação IFBANK

## Arquitetura e tecnologia
- Laravel 13+
- Svelte 5+ e Vite
- Blade para área interna/administrativa
- REST API separada das telas da SPA
- Breeze para sessão web
- Sanctum para tokens da API
- Eloquent/Models e relacionamentos
- Migrations e Seeders
- Policies
- Controller → Service → Repository
- Laravel Auditing
- Mail para credenciais

## Gerente Geral
- CRUD de Gerentes de Conta
- Definição de e-mail e senha
- Envio de credenciais por e-mail
- Aprovação/reprovação de solicitações de limite
- Consulta de auditoria

## Gerente de Conta
- CRUD de clientes da própria carteira
- Conta criada automaticamente com número
- Saldo e limite iniciais
- Consulta de extrato do cliente
- PIX e investimentos aparecem no histórico
- Bloqueio/desbloqueio apenas das contas da própria carteira
- Solicitação de aumento de limite

## Cliente / SPA
- Login REST com token Sanctum
- Saldo em tempo real após autenticação
- Extrato por período
- PIX por e-mail
- CDB, CDI e Poupança
- Aplicação e resgate
- Logout
- Conta bloqueada: somente saldo

## Regra de limite implementada
A disponibilidade para PIX é `saldo + limite`. O limite não é usado para aplicações.

Exemplo: saldo R$ 1.000,00 + limite R$ 500,00 → PIX de R$ 1.200,00 → saldo R$ -200,00; depois PIX de R$ 300,00 → saldo R$ -500,00; qualquer novo PIX acima da disponibilidade é rejeitado.

## Execução
- Local sem Docker usando SQLite
- Docker opcional usando MySQL
- README com comandos Windows e Linux/macOS
