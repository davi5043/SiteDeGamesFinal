# 🎮 GGNews - Passo a Passo de Instalação e Uso

## 📋 O que é este projeto?

Este é um **Portal de Notícias de Games e E-Sports** desenvolvido em PHP com banco de dados MySQL.
Ele permite que usuários se cadastrem, façam login e publiquem notícias sobre o mundo dos games.

---

## 🌳 Estrutura do Projeto (Árvore de Arquivos)

```
portal-games/
│
├── index.php                        → Página inicial do site (o que o visitante vê primeiro)
│
├── includes/                        → PASTA DE SUPORTE (arquivos que outras páginas usam)
│   ├── conexao.php                  → Faz a conexão do PHP com o banco de dados MySQL
│   ├── funcoes.php                  → Funções auxiliares reutilizáveis (resumo, data, escape, etc.)
│   └── verifica_login.php           → Protege páginas que só usuários logados podem ver
│
├── pages/                           → PASTA DAS PÁGINAS DO SISTEMA
│   │
│   ├── auth/                        → Páginas de AUTENTICAÇÃO (login/cadastro)
│   │   ├── login.php                → Tela de login (onde o usuário entra na conta)
│   │   ├── cadastro.php             → Tela de cadastro (onde cria uma conta nova)
│   │   └── logout.php               → Encerra a sessão do usuário (faz o "sair")
│   │
│   ├── noticias/                    → Páginas de NOTÍCIAS (ver, criar, editar, excluir)
│   │   ├── noticia.php              → Página para ler uma notícia completa
│   │   ├── dashboard.php            → Painel do usuário (lista suas notícias publicadas)
│   │   ├── nova_noticia.php         → Formulário para escrever uma notícia nova
│   │   ├── editar_noticia.php       → Formulário para editar uma notícia existente
│   │   └── excluir_noticia.php      → Apaga uma notícia do banco de dados
│   │
│   └── usuario/                     → Páginas de CONTA DO USUÁRIO
│       ├── editar_usuario.php       → Alterar nome, email ou senha da conta
│       └── excluir_usuario.php      → Excluir a conta permanentemente
│
├── css/                             → PASTA DE ESTILOS
│   └── style.css                    → Estilos visuais personalizados (animações, fontes, etc.)
│
├── imagens/                         → PASTA DE IMAGENS (para guardar imagens das notícias)
│
├── dump.sql                         → Arquivo SQL (importar no phpMyAdmin para criar o banco)
├── README.md                        → Informações gerais do projeto
└── PASSO-A-PASSO.md                 → Este arquivo que você está lendo agora
```

---

### 🔍 Explicando cada pasta:

| Pasta | O que tem dentro | Por que está separada |
|-------|------------------|----------------------|
| `includes/` | Arquivos de apoio (conexão, funções, verificação) | São usados por TODAS as páginas, então ficam num lugar central |
| `pages/auth/` | Login, cadastro e logout | Tudo relacionado a entrar/sair do sistema fica junto |
| `pages/noticias/` | CRUD de notícias + dashboard | Tudo sobre notícias fica organizado numa só pasta |
| `pages/usuario/` | Editar e excluir conta | Gerenciamento da conta do usuário |
| `css/` | Arquivos de estilo visual | Separar visual do código PHP é boa prática |
| `imagens/` | Imagens das notícias | Local para guardar uploads |

---

## 🚀 Passo a Passo para Rodar o Projeto

### Passo 1: Instalar o XAMPP

1. Baixe o XAMPP em: https://www.apachefriends.org/pt_br/index.html
2. Instale normalmente (Next, Next, Finish)
3. Abra o **XAMPP Control Panel**
4. Clique em **Start** no **Apache** (servidor web)
5. Clique em **Start** no **MySQL** (banco de dados)
6. Os dois devem ficar com fundo **verde** — isso significa que estão rodando

---

### Passo 2: Copiar o Projeto para a Pasta Certa

1. Abra o explorador de arquivos
2. Navegue até: `C:\xampp\htdocs\`
3. Crie uma pasta chamada `portal-games`
4. Copie **TODOS** os arquivos e pastas deste projeto para dentro dela

O resultado deve ficar assim:
```
C:\xampp\htdocs\portal-games\
    ├── index.php
    ├── dump.sql
    ├── includes\
    │   ├── conexao.php
    │   ├── funcoes.php
    │   └── verifica_login.php
    ├── pages\
    │   ├── auth\
    │   ├── noticias\
    │   └── usuario\
    ├── css\
    └── imagens\
```

---

### Passo 3: Criar o Banco de Dados no phpMyAdmin

1. Abra o navegador (Chrome, Firefox, etc.)
2. Acesse: **http://localhost/phpmyadmin**
3. No menu lateral esquerdo, clique em **"Novo"** (ou "New")
4. No campo "Nome do banco de dados", digite: `portal_games`
5. Clique em **"Criar"**

---

### Passo 4: Importar as Tabelas (dump.sql)

1. Ainda no phpMyAdmin, clique no banco `portal_games` no menu lateral
2. Clique na aba **"Importar"** (no topo da página)
3. Clique em **"Escolher arquivo"** (ou "Browse")
4. Navegue até a pasta do projeto e selecione o arquivo **`dump.sql`**
5. Desça a página e clique no botão **"Executar"** (ou "Go")
6. Deve aparecer uma mensagem verde dizendo que foi importado com sucesso
7. Agora no menu lateral você verá as tabelas: `usuarios` e `noticias`

---

### Passo 5: Acessar o Site

1. Abra o navegador
2. Digite na barra de endereço: **http://localhost/portal-games/**
3. Pronto! O portal de notícias vai aparecer na tela com as notícias de exemplo

---

### Passo 6: Testar o Sistema

**Conta de teste já criada:**
- Email: `admin@portal.com`
- Senha: `123456`

---

**Roteiro de teste completo:**

1. ✅ Acesse `http://localhost/portal-games/` — veja as notícias de exemplo
2. ✅ Clique em uma notícia — leia o conteúdo completo
3. ✅ Clique em **"Login"** no canto superior direito
4. ✅ Entre com: `admin@portal.com` / `123456`
5. ✅ No painel, clique em **"+ Nova Notícia"** — publique algo
6. ✅ Volte ao painel — veja sua notícia na lista
7. ✅ Clique em **"Editar"** — altere o título ou conteúdo
8. ✅ Clique em **"Excluir"** — remova a notícia
9. ✅ Volte à página inicial — confira que sumiu
10. ✅ Clique em **"Sair"** — faça logout

**Testando o cadastro de novo usuário:**

1. Na página inicial, clique em **"Cadastrar"**
2. Preencha: nome, email e senha (mínimo 6 caracteres)
3. Faça login com a conta nova
4. Publique uma notícia — ela aparece na página inicial!

---

## ❓ Problemas Comuns

| Problema | Solução |
|----------|---------|
| Página em branco | Verifique se o Apache está rodando no XAMPP |
| Erro de conexão com banco | Verifique se o MySQL está rodando no XAMPP |
| "Banco não encontrado" | Volte ao Passo 3 e crie o banco `portal_games` |
| Tabelas não existem | Volte ao Passo 4 e importe o `dump.sql` |
| Página não encontrada (404) | Verifique se os arquivos estão em `C:\xampp\htdocs\portal-games\` |
| Erro ao fazer login | Verifique se importou o dump.sql (ele cria o usuário de teste) |

---

## 🎨 Sobre o Visual

O site usa um **tema escuro** (dark mode) com cores roxas, inspirado em portais famosos de games como IGN e e-sporti. O layout é **responsivo** — funciona bem no computador, tablet e celular.

---

## 📚 Para Estudar o Código

Todo o código está comentado em português. Recomendamos ler nesta ordem para entender o fluxo:

| Ordem | Arquivo | O que você aprende |
|-------|---------|-------------------|
| 1 | `includes/conexao.php` | Como o PHP se conecta ao MySQL usando PDO |
| 2 | `includes/funcoes.php` | Como criar funções reutilizáveis |
| 3 | `pages/auth/cadastro.php` | Como criar um usuário com senha criptografada |
| 4 | `pages/auth/login.php` | Como verificar senha e criar sessão |
| 5 | `includes/verifica_login.php` | Como proteger páginas com sessão |
| 6 | `pages/noticias/nova_noticia.php` | Como inserir dados no banco (CREATE) |
| 7 | `pages/noticias/editar_noticia.php` | Como atualizar dados no banco (UPDATE) |
| 8 | `pages/noticias/excluir_noticia.php` | Como deletar dados do banco (DELETE) |
| 9 | `index.php` | Como buscar e exibir dados (READ) + montar layout |

---

Bom estudo e boa sorte com o projeto! 🚀🎮
