README
======

Esse é um template de aplicação Zend Framework usado por mim para os projetos da Coderockr

Estrutura do projeto:
.htaccess - arquivo com as configurações de redirect necessárias para o projeto
config/config.ini - arquivo com as configurações do projeto como cache, banco de dados, traduções, etc
data/cache - diretório para salvar cache em arquivos
doc - documentações do projeto
forms - diretório para os formulários do projeto, criados com Zend_Form
index.php - bootstrap do projeto. É necessário configurar ao menos os caminhos dos diretórios, nas linhas 33 a 40
models - modelos do projeto
modules - diretório com os módulos do projeto
modules/default - diretório com o módulo principal do projeto
modules/default/controllers/ - diretório para os controladores do módulo
modules/default/views/scripts/ - diretórios para as visões usadas pelos controladores
views/layouts - layouts do projeto
public/css/ - arquivos css
public/js/ - arquivos js
public/img/ - imagens


VHOST
=====================

Uma forma fácil de se trabalhar com projetos é configurar um VHOST em seu Apache. No arquivo de configuração faça como no exemplo:

NameVirtualHost *

<VirtualHost *:80>
   DocumentRoot "/Applications/MAMP/htdocs/Template-ZF-Modulos"
   ServerName zf.local
	
   SetEnv APPLICATION_ENV "development"	
	
   <Directory "/Applications/MAMP/htdocs/Template-ZF-Modulos">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>
</VirtualHost>