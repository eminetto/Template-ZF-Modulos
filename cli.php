<?php
/**
 * Arquivo Principal da Aplicacao, responsavel pelo processamento
 * de todas as requisicoes, todos os caminhos para os arquivos
 * serao contruidos aqui para serem utilizados ao longo da requisicao
 * E responsavel tambem por carregar o front-controller da aplicacao
 * E por consequencia tambem carrega o controller requisitado
 * @author    Andre Gustavo Espeiorin <xorna@coderockr.com>
 * @author    Elton Luís Minetto <eminetto@coderockr.com>
 * @version    1.0
*/


/**
 * Habilita a visualizacao de erros e avisos
*/
error_reporting(E_ALL | E_STRICT);

/**
 * Seta o TimeZone para nao haver problemas com o Zend_Date e Zend_Locale
*/
date_default_timezone_set('America/Sao_Paulo');

/**
 * Seta o document_root
 * Verifica se é o servidor de desenvolvimento ou produção
 */
$env = strtolower(getenv('APPLICATION_ENV'));
$options = getopt('e:',array('environment:'));
if(isset($options['e']) ) {
	$env = $options['e'];
}
elseif(isset($options['environment'])) {
	$env = $options['environment'];
}
else {
	$env = 'production';
}

if( $env == 'production' ){
	define('DOCUMENT_ROOT', '/var/www/html/');
	define('HTTP_URL', 'http://www.site.com/Template-ZF');
}else{
	define('DOCUMENT_ROOT', '/Applications/MAMP/htdocs/');
	define('HTTP_URL', 'http://zf.local');
}
define('BAR', '/');
define('PROJECT_PATH', DOCUMENT_ROOT . 'Template-ZF-Modulos' . BAR);
define('CLASS_PATH', DOCUMENT_ROOT . 'library' . BAR);
define('CONTROLLER_PATH', PROJECT_PATH . 'controllers' . BAR);
define('MODELS_PATH', PROJECT_PATH . 'models' . BAR);
define('FORMS_PATH', PROJECT_PATH . 'forms' . BAR);
define('VIEWS_PATH', PROJECT_PATH . 'views' . BAR);
define('DATA_PATH', PROJECT_PATH . 'data' . BAR);
define('CACHE_PATH', DATA_PATH . 'cache' . BAR);
define('CONFIG_PATH', PROJECT_PATH . 'config' . BAR);
define('LANG_PATH', PROJECT_PATH . 'lang' . BAR);
define('MODULE_CONTROLLER_PATH', PROJECT_PATH . 'modules'. BAR );


/**
 * Pega o include_path do PHP e adiciona os caminhos das classes e dos models
 * para uso posterior em includes e principalmente com Zend_Loader
 */
$includePath  = get_include_path();
$includePath .= PATH_SEPARATOR . CLASS_PATH;
$includePath .= PATH_SEPARATOR . MODELS_PATH;
$includePath .= PATH_SEPARATOR . CONTROLLER_PATH;
$includePath .= PATH_SEPARATOR . FORMS_PATH;
$includePath .= PATH_SEPARATOR . MODULE_CONTROLLER_PATH . 'default' . BAR . 'controllers';
set_include_path($includePath);


/**
 * Faz o include do Zend_Loader, responsavel pelo carregamento
 * de modelos, classes e arquivos em geral
 * Suporta o Include_Path
 * Suporta Auto-Carregamento da SPL
 * Mecanismo de falha baseado em excessao
*/

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance(); 
$autoloader->setFallbackAutoloader(true);

/* 
 * Busca as configurações
*/
$config = new Zend_Config_Ini(CONFIG_PATH . 'config.ini',$env);
$config = $config->toArray();

/*
* Zend Translate 
*/
if(isset($config['translate'])) {
	$translate = new Zend_Translate($config['translate']['adapter'],LANG_PATH, $config['translate']['locale']);
	Zend_Registry::set('Zend_Translate', $translate);
}
/**
 * Cria e configura as conexoes com o banco de dados
 */
// cria uma nova instância do objeto Zend_Config_Ini. Esta classe é de grande utilidade pois permite que sejam criados arquivos de configuração simples e eficazes.
$Connection = Zend_Db::factory($config['db']['adapter'], 
	array (
		'host' => $config['db']['host'], 
		'username' => $config['db']['username'], 
		'password' => $config['db']['password'], 
		'dbname' => $config['db']['dbname'], 
		)
	);

/**
 * Setando a conexao como padrao
*/
Zend_Db_Table::setDefaultAdapter($Connection);

/* 
 * Cache
*/
$frontendOptions = array(
   'lifetime' => $config['cache']['front']['lifetime'], // tempo de vida de 2 horas
   'automatic_serialization' => $config['cache']['front']['automatic_serialization']
);
$backendOptions = $config['cache']['back']['options'];
// criando uma instancia do cache
$cache = Zend_Cache::factory('Core',//frontend
                             $config['cache']['back']['adapter'],  //backend
                             $frontendOptions,
                             $backendOptions);

/*
* Salva o cache no Registry para ser usado posteriormente
*/
Zend_Registry::set('cache', $cache);

/*
 * cache para metadados das tabelas
*/
Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);


/**
 * Inicia a Sessao
 */
Zend_Session::start();

Zend_Registry::set('session', new Zend_Session_Namespace());

/* 
 * Registra variável de ambiente
 */
Zend_Registry::set('env',$env);


try {
    $opts = new Zend_Console_Getopt(
		array(
			'help|h' => 'Displays usage information.',
			'action|a=s' => 'Action to perform in format of module.controller.action',
			'verbose|v' => 'Verbose messages will be dumped to the default output.',
			'environment|e' => 'Environment. Default production',
			)
	);
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	exit($e->getMessage() ."\n\n". $e->getUsageMessage());
}

//mostra o help
if(isset($opts->h)) {
	echo $opts->getUsageMessage();
	exit;
}
//monta a requisição
if(isset($opts->a)) {
	$reqRoute = array_reverse(explode('.',$opts->a));
	list($action,$controller,$module) = $reqRoute;
	$request = new Zend_Controller_Request_Simple($action,$controller,$module);
	$front = Zend_Controller_Front::getInstance();
    $front->setRequest($request);
	$front->setRouter(new Coderockr_Controller_Router_Cli());
	$front->setResponse(new Zend_Controller_Response_Cli());
	$front->throwExceptions(true);
	$front->addModuleDirectory(MODULE_CONTROLLER_PATH);
    $front->dispatch();
}