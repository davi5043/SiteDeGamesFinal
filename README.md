# 🎮 GGNews - Portal de Notícias de Games e E-Sports

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</p>

<p align="center">
  <strong>Portal completo de notícias sobre Games e E-Sports</strong><br>
  Desenvolvido em PHP, MySQL, Tailwind CSS e JavaScript
</p>

---

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias Utilizadas](#-tecnologias-utilizadas)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Como Instalar](#-como-instalar)
- [Como Usar](#-como-usar)
- [Banco de Dados](#-banco-de-dados)
- [Telas e Funcionalidades](#-telas-e-funcionalidades)
- [Galeria de Imagens (Mockup)](#-galeria-de-imagens-mockup)
- [Contribuição](#-contribuição)
- [Licença](#-licença)

---

## 🎯 Sobre o Projeto

**GGNews** é um portal de notícias focado no universo de **Games e E-Sports**, desenvolvido como projeto acadêmico para demonstrar habilidades em desenvolvimento web full-stack.

O projeto conta com um sistema completo de **CRUD de notícias**, **autenticação de usuários**, **sistema de comentários**, **upload de avatares**, **tema escuro/claro** e uma interface responsiva inspirada em portais como G1 e Globo Esporte.

---

## ✨ Funcionalidades

### 🔐 Autenticação
- ✅ Cadastro de usuários com validação de dados
- ✅ Login com verificação de senha (hash Bcrypt)
- ✅ Logout seguro com destruição de sessão
- ✅ Proteção de rotas (páginas restritas)
- ✅ Token CSRF para segurança em formulários

### 📰 Gerenciamento de Notícias
- ✅ CRUD completo (Criar, Ler, Editar, Excluir)
- ✅ Categorias: CS2, VALORANT, League of Legends, Esports, Geral
- ✅ Imagem de destaque (via URL)
- ✅ Autor e data de publicação automáticos
- ✅ Listagem com hierarquia estilo telejornal (destaque, secundárias, lista)

### 💬 Comentários
- ✅ Sistema completo de comentários por notícia
- ✅ Apenas usuários logados podem comentar
- ✅ Autor pode excluir seus próprios comentários
- ✅ Contador de comentários

### 👤 Perfil do Usuário
- ✅ Edição de nome, username, email e senha
- ✅ Upload de avatar (JPG, PNG, WEBP - até 2MB)
- ✅ Exclusão de conta com remoção de todas as notícias
- ✅ Avatar com fallback (iniciais + cores dinâmicas)

### 🎨 Interface
- ✅ Tema claro/escuro (persistente via localStorage)
- ✅ Layout responsivo (mobile, tablet, desktop)
- ✅ Sidebar com navegação e categorias
- ✅ Header com menu hambúrguer no mobile
- ✅ Animações suaves e transições
- ✅ Estilização inspirada em portais de notícia

---

## 🛠️ Tecnologias Utilizadas

| Tecnologia | Descrição |
|------------|-----------|
| **PHP 8+** | Linguagem backend para lógica de negócio |
| **MySQL** | Banco de dados relacional |
| **PDO** | Camada de abstração para banco de dados (prepared statements) |
| **Tailwind CSS** | Framework CSS utilitário |
| **HTML5** | Estrutura das páginas |
| **CSS3** | Estilização customizada (temas, animações) |
| **JavaScript** | Interatividade (tema, sidebar mobile, preview de imagens) |
| **Bcrypt** | Criptografia de senhas |

---

## 📁 Estrutura do Projeto

```
📂 portal-games/
├── 📂 pages/
│   ├── 📂 auth/
│   │   ├── login.php
│   │   ├── cadastro.php
│   │   └── logout.php
│   ├── 📂 noticias/
│   │   ├── dashboard.php
│   │   ├── nova_noticia.php
│   │   ├── editar_noticia.php
│   │   ├── excluir_noticia.php
│   │   └── noticia.php
│   ├── 📂 usuario/
│   │   ├── editar_usuario.php
│   │   └── excluir_usuario.php
│   └── 📂 categorias/
│       └── categoria.php
├── 📂 includes/
│   ├── conexao.php
│   ├── funcoes.php
│   ├── verifica_login.php
│   ├── avatar_helper.php
│   └── comentarios.php
├── 📂 css/
│   ├── style.css
│   └── categoria.css
├── 📂 uploads/
├── index.php
├── .htaccess
└── README.md
```

---

## 📥 Como Instalar

### 📋 Pré-requisitos
- **XAMPP**, **WAMP** ou servidor com PHP 8+ e MySQL
- **phpMyAdmin** (para importar o banco)
- **Git** (opcional, para clonar o repositório)

### 🔧 Passo a Passo

#### 1️⃣ Clone o repositório
```bash
git clone https://github.com/seu-usuario/portal-games.git
```
Ou baixe o ZIP e extraia.

#### 2️⃣ Mova para a pasta do servidor
- **XAMPP:** `C:/xampp/htdocs/portal-games/`
- **WAMP:** `C:/wamp64/www/portal-games/`

#### 3️⃣ Importe o banco de dados
1. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Crie um banco chamado `portal_games`
3. Clique em **Importar**
4. Selecione o arquivo `portal_games (4).sql`
5. Clique em **Executar**

#### 4️⃣ Configure a conexão
Edite o arquivo `includes/conexao.php`:
```php
$host = 'localhost';
$dbname = 'portal_games';
$usuario = 'root';      // ALTERE SE NECESSÁRIO
$senha_db = '';         // ALTERE SE NECESSÁRIO
```

#### 5️⃣ Permissões da pasta uploads
No **Linux/Mac**:
```bash
chmod -R 777 uploads/
```
No **Windows**, as permissões geralmente são automáticas.

#### 6️⃣ Acesse no navegador
```
http://localhost/portal-games/
```

---

## 👤 Usuário de Teste

| Email | Senha |
|-------|-------|
| admin@ggnews.com | 123456 |

*(Ou crie seu próprio usuário no cadastro)*

---

## 🎯 Como Usar

### 🏠 Página Inicial
- Visualiza a **manchete principal** em destaque
- Cards com as **notícias secundárias** (3 colunas)
- **Lista completa** das últimas notícias
- Navegação por **categorias** via sidebar

### 📝 Criar Notícia
1. Faça login com sua conta
2. Acesse o **Painel** (Dashboard)
3. Clique em **"Nova Notícia"**
4. Preencha título, categoria, conteúdo e URL da imagem
5. Clique em **"Publicar Notícia"**

### ✏️ Editar Notícia
1. No Dashboard, clique em **"Editar"** na notícia desejada
2. Modifique os campos necessários
3. Clique em **"Salvar Alterações"**

### 🗑️ Excluir Notícia
1. No Dashboard, clique em **"Excluir"** na notícia desejada
2. Confirme a exclusão no pop-up

### 💬 Comentar
1. Acesse uma notícia (clique no título)
2. Role até a seção de comentários
3. Faça login (se necessário)
4. Escreva seu comentário e clique em **"Enviar Comentário"**

---

## 🗄️ Banco de Dados

### Tabelas

#### `usuarios`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| nome | VARCHAR(100) | Nome completo |
| email | VARCHAR(150) | E-mail (único) |
| senha | VARCHAR(255) | Hash da senha (Bcrypt) |
| username | VARCHAR(50) | Apelido (opcional) |
| foto | VARCHAR(255) | Nome do arquivo do avatar |
| criado_em | DATETIME | Data de criação |

#### `categorias`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| nome | VARCHAR(100) | Nome da categoria |
| slug | VARCHAR(100) | URL amigável (único) |
| icone | VARCHAR(10) | Emoji da categoria |

#### `noticias`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| titulo | VARCHAR(255) | Título da notícia |
| noticia | TEXT | Conteúdo da notícia |
| data | DATETIME | Data de publicação |
| autor | INT | FK para `usuarios.id` |
| imagem | VARCHAR(255) | URL da imagem de destaque |
| categoria_id | INT | FK para `categorias.id` |

#### `comentarios`
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| noticia_id | INT | FK para `noticias.id` |
| usuario_id | INT | FK para `usuarios.id` |
| conteudo | TEXT | Conteúdo do comentário |
| data | DATETIME | Data do comentário |

---

## 🖥️ Telas e Funcionalidades

### 📱 Página Inicial
- Layout estilo telejornal
- Destaque principal com imagem e título
- Cards secundários com preview
- Lista completa de notícias
- Sidebar com navegação e categorias

### 🔐 Login / Cadastro
- Formulários com validação em tempo real
- Mensagens de erro/sucesso
- Proteção contra SQL Injection
- Senhas criptografadas com Bcrypt

### 📋 Dashboard
- Lista de notícias do usuário logado
- Botões de ação: Editar, Excluir, Nova
- Mensagens de feedback
- Apenas o autor pode editar/excluir

### 📄 Página da Notícia
- Leitura completa da notícia
- Categoria e autor
- Sistema de comentários
- Botão "Voltar para as notícias"
- Editar (se for o autor)

### 👤 Editar Perfil
- Upload de avatar com preview
- Alterar nome, username, email
- Alterar senha (com validação da atual)
- Excluir conta

---

## 🖼️ Galeria de Imagens (Mockup)

<!-- ============================================================ -->
<!-- INSIRA AQUI AS IMAGENS DO SEU PROJETO (MOCKUP)                -->
<!-- ============================================================ -->

### 🏠 Página Inicial
![Página Inicial](.github/mockups/home.png)
*Layout estilo telejornal com destaque, cards secundários e lista de notícias*

### 🔐 Login
![Login](.github/mockups/login.png)
*Formulário de login com tema escuro/claro*

### 📝 Nova Notícia
![Nova Notícia](.github/mockups/nova-noticia.png)
*Formulário completo para criação de notícias*

### 📋 Dashboard
![Dashboard](.github/mockups/dashboard.png)
*Painel do usuário com listagem de notícias e botões de ação*

### 📄 Página da Notícia
![Notícia](.github/mockups/noticia.png)
*Visualização completa da notícia com comentários*

### 👤 Editar Perfil
![Editar Perfil](.github/mockups/editar-perfil.png)
*Edição de dados do usuário com upload de avatar*

### 🎨 Tema Escuro vs Claro
![Tema Escuro](.github/mockups/dark-theme.png)
*Comparação entre os temas escuro e claro*

### 📱 Responsividade Mobile
![Mobile](.github/mockups/mobile.png)
*Layout adaptado para dispositivos móveis*

---

## 🤝 Contribuição

Contribuições são sempre bem-vindas!

1. **Fork** o projeto
2. Crie sua **branch** (`git checkout -b feature/AmazingFeature`)
3. **Commit** suas alterações (`git commit -m 'Add some AmazingFeature'`)
4. **Push** para a branch (`git push origin feature/AmazingFeature`)
5. Abra um **Pull Request**

---

## 📄 Licença

Este projeto é de uso acadêmico e está sob a licença **MIT**.

---

## 👨‍💻 Autores

**Mauricio De Campos** & **Davi Foppa**

---

## 🙏 Agradecimentos

- [Tailwind CSS](https://tailwindcss.com/) - Framework CSS utilizado
- [Unsplash](https://unsplash.com/) - Imagens de placeholder
- [Font Awesome](https://fontawesome.com/) - Ícones (via emojis)
- [Google Fonts](https://fonts.google.com/) - Fontes Inter e Syne

---


<p align="center">
  <strong>GGNews</strong> — Seu portal de notícias sobre o mundo dos games!
</p>
