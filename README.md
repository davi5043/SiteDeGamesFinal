🎮 GGNews - Portal de Notícias de Games e E-Sports
Portal de notícias completo desenvolvido em PHP, MySQL e Tailwind CSS.
Tema: Games e E-Sports (Opção 7)

Como Instalar
Requisitos
XAMPP, WAMP ou servidor com PHP 7.4+ e MySQL
phpMyAdmin (para importar o banco)
Passo a Passo
Clone ou copie o projeto para a pasta do seu servidor web:

XAMPP: C:/xampp/htdocs/portal-games/
WAMP: C:/wamp64/www/portal-games/
Importe o banco de dados:

Abra o phpMyAdmin (http://localhost/phpmyadmin)
Clique em "Importar"
Selecione o arquivo dump.sql
Clique em "Executar"
Configure a conexão (se necessário):

Abra o arquivo conexao.php
Altere as variáveis $host, $usuario, $senha_db conforme seu ambiente
Acesse no navegador:

http://localhost/portal-games/
Usuário de Teste
Email	Senha
admin@portal.com	123456
Estrutura de Arquivos
Arquivo	Descrição
index.php	Página inicial com listagem de notícias
noticia.php	Página individual da notícia
login.php	Formulário de login
cadastro.php	Formulário de cadastro
logout.php	Encerrar sessão
dashboard.php	Painel do usuário logado
nova_noticia.php	Formulário para nova notícia
editar_noticia.php	Edição de notícia
excluir_noticia.php	Exclusão de notícia
editar_usuario.php	Edição de conta
excluir_usuario.php	Exclusão de conta
conexao.php	Conexão PDO com MySQL
funcoes.php	Funções auxiliares
verifica_login.php	Proteção de páginas restritas
dump.sql	SQL do banco de dados
css/style.css	Estilos customizados
imagens/	Pasta para imagens
Tecnologias Utilizadas
PHP - Linguagem backend
MySQL - Banco de dados relacional
PDO - Conexão segura com banco (prepared statements)
Tailwind CSS - Framework CSS utilitário
HTML5 - Estrutura das páginas
Funcionalidades
Cadastro e login de usuários (com senha criptografada)
CRUD completo de notícias (criar, ler, editar, excluir)
Apenas o autor pode editar/excluir suas notícias
Página inicial com hero section e grid responsivo
Gerenciamento de conta (editar dados, excluir conta)
Interface dark theme inspirada em portais de games
Layout responsivo (mobile, tablet, desktop)
