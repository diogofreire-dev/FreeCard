# TROUBLESHOOTING: Email e Lembretes (RESOLVIDO)

Resumo dos problemas encontrados e soluções aplicadas:

- Problema: Emails de teste não eram entregues
  - Solução: Corrigida inclusão do `vendor/autoload.php` e atualização das App Passwords

- Problema: Colunas faltando na BD (password_reset_token, password_reset_expires)
  - Solução: Adicionadas via ALTER TABLE

- Problema: Lembretes não sendo enviados
  - Solução: Corrigido filtro de datas no cron e adicionado `last_notification_sent`

- Problema: Emails de boas-vindas silenciosamente falhavam
  - Solução: Adicionada gestão de exceções em `site/register.php` e logging

Regeneração de passos de debug:
1. Criado `DIAGNOSTIC_EMAIL.php`, `DEBUG_REMINDERS.php`, `DIRECT_EMAIL_TEST.php` para teste
2. Executado cron manual e verificado logs
3. Confirmado que emails foram enviados

Status: **RESOLVIDO**
