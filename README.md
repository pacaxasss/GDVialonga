# GD Vialonga — Website Oficial

Site em PHP com base de dados SQLite e painel de administração completo.

## Arranque rápido

```bash
php -S localhost:8080 -t C:\Users\dinis\.gemini\antigravity\scratch\gdvialonga-web
```

Abrir: **http://localhost:8080**

> Precisa de PHP 8.1+ com extensão `pdo_sqlite` ativa (vem por padrão na maioria das instalações).

---

## Painel de Administração

**URL:** http://localhost:8080/admin/

Não existe senha padrão. Em bases antigas, a senha legada foi desativada para login; antes de entrar, configurar `GDV_ADMIN_INITIAL_PASSWORD` no ambiente do Apache. Usar essa senha uma única vez e trocá-la em **Segurança**.

Em instalações novas, configurar `GDV_ADMIN_INITIAL_PASSWORD` antes da primeira requisição que inicializa a base SQLite. Opcionalmente, definir `GDV_ADMIN_INITIAL_USERNAME` (por omissão `admin`). A senha deve ter pelo menos 14 caracteres. Remover a variável de senha do ambiente após a conta ser criada/trocada; nunca a guardar no código-fonte nem em ficheiros públicos.

No Apache do XAMPP, as variáveis podem ser definidas na configuração local do VirtualHost (não neste repositório):

```apache
SetEnv GDV_ADMIN_INITIAL_USERNAME admin
SetEnv GDV_ADMIN_INITIAL_PASSWORD "SUBSTITUIR-POR-SEGREDO-ALEATORIO"
SetEnv GDV_SITE_URL https://teu-dominio
```

Substituir o valor de exemplo por um segredo aleatório de pelo menos 14 caracteres. Para uma base antiga, reiniciar o Apache, entrar com esse segredo, trocá-lo em **Segurança**, removê-lo da configuração do Apache e reiniciar novamente.

### O que pode gerir no admin:

| Secção | Funcionalidades |
|--------|-----------------|
| **Notícias** | Criar, editar, publicar/despublicar, eliminar artigos |
| **Jogos & Resultados** | Sincronizar jogos/resultados da FPF ou gerir por competição |
| **Classificação** | Recalcular automaticamente a partir dos resultados sincronizados |

### Segurança
- A conta de admin existente exige troca de senha no próximo acesso. Usa uma senha exclusiva com pelo menos 14 caracteres.
- Sessões usam cookies `HttpOnly`, `SameSite=Lax`, modo estrito, rotação do ID no login e expiração após 30 minutos sem atividade.
- Operações administrativas exigem CSRF; login tem limite de tentativas; conteúdo de notícias passa por allowlist HTML.
- `.htaccess` bloqueia listagem e acesso web aos dados SQLite/JSON, configuração e includes. Em Apache, confirma `AllowOverride All` para a pasta do site.
- Para produção, ativa HTTPS no servidor e define `GDV_SITE_URL=https://teu-dominio`. Sem certificado TLS o site local continua HTTP e o cookie não pode ser `Secure`.
- Senhas são armazenadas com `password_hash()` (hash adaptativo, não cifra reversível). O SQLite guarda dados editoriais/resultados; não introduzir dados privados sem avaliar encriptação e controlo de acesso adicionais.

---

## Estrutura do Projeto

```
gdvialonga-web/
│
├── config.php              ← Configuração global (senha admin, época, etc.)
│
├── index.php               ← Homepage
├── o-clube.php             ← Sobre o clube
├── noticias.php            ← Lista de notícias
├── noticia.php             ← Artigo individual
├── futebol.php             ← Resultados & Classificação
├── plantel.php             ← Plantéis
├── formacao.php            ← Formação (inscrições)
├── contactos.php           ← Contactos
│
├── admin/
│   ├── login.php           ← Login do admin
│   ├── logout.php
│   ├── index.php           ← Dashboard
│   ├── noticias.php        ← CRUD notícias
│   ├── resultados.php      ← CRUD jogos/resultados
│   ├── classificacao.php   ← Editor de classificação
│   └── assets/admin.css    ← Estilos do painel
│
├── includes/
│   ├── config.php          ← (carregado via config.php na raiz)
│   ├── db.php              ← SQLite: conexão, schema, seed automático
│   ├── auth.php            ← Autenticação de sessão
│   ├── functions.php       ← Funções públicas (news, matches, standings)
│   ├── header.php          ← Header do site público
│   └── footer.php          ← Footer do site público
│
├── data/
│   ├── gdvialonga.db       ← Base de dados SQLite (criada automaticamente)
│   ├── news.json           ← Seed inicial de notícias
│   ├── matches.json        ← Seed inicial de jogos
│   ├── standings.json      ← Seed inicial de classificação
│   ├── players.json        ← Dados de jogadores
│   └── sponsors.json       ← Patrocinadores
│
└── assets/
    ├── css/style.css       ← Estilos do site público
    ├── js/main.js          ← JavaScript público
    └── images/             ← Imagens
```

---

## Base de Dados (SQLite)

A base de dados `data/gdvialonga.db` é criada automaticamente na primeira execução.
Notícias e patrocinadores podem ser inicializados a partir dos ficheiros JSON. Jogos e classificação são obtidos da FPF pelo comando de sincronização.

### Tabelas

| Tabela | Descrição |
|--------|-----------|
| `news` | Notícias e artigos |
| `matches` | Jogos (agendados e resultados) |
| `standings` | Tabelas classificativas |
| `competitions` | Competições (Honra AFL, Juniores, etc.) |
| `sponsors` | Patrocinadores |
| `admin_users` | Utilizadores do painel |

---

## Funcionalidades do Admin

### Notícias
- Lista com estado (Publicado / Rascunho)
- Formulário com: título, categoria, data, autor, resumo, conteúdo HTML, imagem, tags
- Publicar/despublicar com checkbox
- Eliminar com confirmação

### Jogos & Resultados
- Filtro por competição
- Agendar novo jogo (equipas, data, hora, local)
- Registar resultado (resultado final + marcadores linha a linha)
- Botão "Registar resultado" direto do dashboard para o próximo jogo
- Estados: Agendado / Terminado / Cancelado

### Classificação
- Tabela editável inline (clique em qualquer campo para editar)
- A tabela recalcula vitórias/empates/derrotas/golos/pontos, forma e posições ao guardar, editar ou eliminar jogos
- **Calcular Auto** — permite forçar um novo cálculo a partir dos jogos registados
- Adicionar nova equipa
- Eliminar equipa
- Coluna "GDV" para marcar a equipa do clube (destaque visual no site)

### Sincronização de Futebol
- A fonte configurada é o Centro de Resultados oficial da FPF: **CD I Divisão | SABSEG**, AF Lisboa, época 2026/27.
- No admin, abrir **Jogos & Resultados** e escolher **Sincronizar FPF**. A confirmação substitui os jogos e a classificação locais desta competição pelos dados recebidos da FPF.
- A sincronização valida época, jornadas e presença do GDV antes de alterar a base de dados. Jornadas ainda não publicadas pela FPF não são inventadas nem importadas.
- Os horários são obtidos nos detalhes oficiais dos jogos do GDV; quando a FPF ainda não os publica, o site deixa o horário em branco.
- A FPF informa que os resultados só se tornam oficiais 15 dias após cada jogo. A página de Futebol liga à fonte original.
- A FPF não documenta estas rotas HTML como API estável; podem mudar. Os termos da FPF autorizam a utilização informativa pública não comercial com indicação da fonte; para utilização comercial ou redistribuição, é necessária autorização da FPF.

Para sincronizar pela linha de comandos:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\gdvialonga-web\sync_fpf.php'
```

Neste computador, a tarefa `GDVialonga-FPF-Sync` já está configurada para executar diariamente às 23:00. Noutra instalação Windows, criar uma tarefa no Agendador de Tarefas para executar o comando acima após as jornadas. Os IDs da competição e da época estão em `config.php` e devem ser atualizados no início de cada temporada.

---

## Configuração Produção (XAMPP/Laragon)

1. Copiar pasta para `htdocs/gdvialonga-web/`
2. Configurar HTTPS e definir `GDV_SITE_URL` com o endereço `https://` antes de publicar; isso ativa cookies `Secure` e HSTS
3. Em instalação nova, provisionar `GDV_ADMIN_INITIAL_PASSWORD` no ambiente do Apache antes da primeira execução, reiniciar o Apache e removê-la após criar a conta
4. Aceder a `/gdvialonga-web/admin/` e confirmar a senha da conta
5. Manter `GDV_DEBUG` desativado em produção (padrão)
6. Confirmar que Apache permite `.htaccess`; regras bloqueiam listagem e acesso direto a `data/`, `includes/`, `config.php` e `README.md`
7. Restringir permissões Windows da pasta `data/` à conta do Apache e aos administradores do servidor
8. Configurar backups regulares e protegidos de `data/gdvialonga.db`

As senhas são armazenadas com `password_hash()` e nunca devem ser encriptadas reversivelmente. A aplicação exige troca da senha legada, limita tentativas de login, regenera o ID de sessão após autenticação e usa tokens CSRF nos formulários administrativos.

O SQLite deste XAMPP não usa SQLCipher e, portanto, o ficheiro `.db` não está encriptado em repouso. A base contém dados editoriais/desportivos e hashes de senha; protege-a com permissões NTFS restritas e BitLocker no volume/dispositivo. HTTPS protege os dados em trânsito; a aplicação redireciona para HTTPS quando `GDV_SITE_URL` está configurado com `https://`.
