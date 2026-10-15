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
├── config.php              ← Configuração global (sessões, segurança, época)
├── index.php               ← Homepage (ponto de entrada público)
├── .htaccess               ← Regras de reescrita limpa para pages/ e segurança
│
├── pages/                  ← Páginas públicas do website
│   ├── o-clube.php         ← Sobre o clube e história
│   ├── futebol.php         ← Futebol sénior, resultados e classificação
│   ├── plantel.php         ← Plantéis e fichas de atletas
│   ├── formacao.php        ← Formação e inscrições
│   ├── noticias.php        ← Arquivo de notícias
│   ├── noticia.php         ← Artigo individual
│   ├── loja.php            ← Loja oficial de equipamentos GDV
│   ├── patrocinadores.php  ← Patrocinadores oficiais
│   └── contactos.php       ← Formulário e contactos do clube
│
├── admin/                  ← Painel de administração
│   ├── login.php           ← Login do admin
│   ├── logout.php          ← Logout
│   ├── index.php           ← Dashboard
│   ├── noticias.php        ← Gestão de notícias
│   ├── resultados.php      ← Gestão de jogos e resultados
│   ├── classificacao.php   ← Editor de classificação
│   └── seguranca.php       ← Gestão de credenciais
│
├── includes/               ← Componentes e bibliotecas partilhadas
│   ├── db.php              ← Conexão e esquemas SQLite
│   ├── auth.php            ← Autenticação e controlo de sessão
│   ├── functions.php       ← Lógica de negócio e dados públicos
│   ├── fpf.php             ← Sincronização oficial FPF
│   ├── header.php          ← Cabeçalho e navegação
│   └── footer.php          ← Rodapé do website
│
├── data/                   ← Dados estruturados e persistência
│   ├── gdvialonga.db       ← Base de dados SQLite
│   ├── news.json           ← Dados e artigos de notícias
│   ├── matches.json        ← Jogos e calendário
│   ├── standings.json      ← Tabela classificativa
│   ├── players.json        ← Atletas e equipa técnica
│   ├── products.json       ← Produtos da loja oficial
│   └── sponsors.json       ← Patrocinadores oficiais
│
├── tools/                  ← Utilitários e scripts CLI
│   ├── sync_fpf.php        ← Sincronizador de dados FPF
│   └── generate_player_avatars.php ← Gerador de avatares placeholder
│
└── assets/                 ← Recursos estáticos
    ├── css/style.css       ← Folha de estilos global
    ├── js/main.js          ← Scripts interativos
    └── images/             ← Imagens, logótipos e fotos de jogadores
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

## Plantéis, Fotos e Estatísticas

O plantel de cada escalão vive em `data/players.json`. Cada jogador tem:

| Campo | Descrição |
|-------|-----------|
| `number` | Número de camisola (`0` = sem número, usado pela equipa técnica) |
| `name`, `position`, `nationality`, `age` | Identificação (posições: `goalkeeper`, `defender`, `midfielder`, `forward`, `coach`, `staff`) |
| `photo` | Caminho da fotografia (`assets/images/players/...`) |
| `clubs` | Clubes por onde passou, do mais recente para o mais antigo |
| `goals`, `assists` | Golos e assistências na época |
| `bio` | Pequena biografia apresentada na ficha do jogador |

Na página **Plantéis** (`plantel.php`), cada cartão mostra a foto, o número, a posição, a nacionalidade e os golos/assistências, e abre uma ficha completa (modal acessível por rato e por teclado) com nome, idade, percurso de clubes e biografia.

### Fotografias

Foram gerados retratos *placeholder* em `assets/images/players/` com as cores do clube e o número de camisola. Para publicar fotografias reais, basta substituir o ficheiro indicado no campo `photo` (mesmo nome e formato `.png`); o site ajusta a imagem automaticamente. Para voltar a gerar os placeholders:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\gdvialonga-web\tools\generate_player_avatars.php'
```

Se faltar uma fotografia, o cartão recorre a um ícone de silhueta (sem quebrar o layout).

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
