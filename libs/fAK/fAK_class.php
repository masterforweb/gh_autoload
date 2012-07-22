<?php
	
	
	require('fakDB.php');



	/*
		иницилизация приложения
	*/


	define('SITEPATH', $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR); 		// директория сайта
	define('CPATH', SITEPATH.'cache'.DIRECTORY_SEPARATOR);	// директория кеша
	define('APPPATH', SITEPATH.'app'.DIRECTORY_SEPARATOR);	// директория приложения
	define('COMPATH', APPPATH.'components'.DIRECTORY_SEPARATOR);
	define('VIEWPATH', APPPATH.'views'.DIRECTORY_SEPARATOR);

	

	class fAK
	{
		
		static $set = array();        //конфиги
		public $layout = null;        //центральный шаблон
		public $params = array();     //массив значений uri
		
		private $url = ''; //текущий урл
		private $cache_page = null;   //время глобального кеша
		private $fcache = null; //файл кеша
		private $cache_type = 'file'; //тип кэширования
		private $content = '';        //сгенерированный шаблон страницы
		//private $get = array();

		private $curr_key = null; //текущий ключ кеша для сохранения

		public $title = '(c) framework AK'; //заголовок страницы
		public $js = array(); //список js файлов для подключения
		public $css = array(); //список css для подключения


		
		
		function __construct($url = null) {

			if ($url == null)
				$url = $_SERVER['REQUEST_URI']; //текущий url

			$this->uri($url); 

			return;
		}



		//определяем параметры url
		function uri ($url) {
			
			$url = trim($url,'/');

			$this->url = $url;

			/* обработка GET */
			if ($_SERVER['QUERY_STRING'] !== ''){
				$get_cache_line = str_replace('=','_', $_SERVER['QUERY_STRING']);
				$pos_query = strpos($uri, '?');
				$uri = substr($uri, 0, $pos_query);
			}
			else
				$get_cache_line = '';
		
	
			if ($url == '')
				$fcache =  'index';  //определяем файл кеша гл страницы
			else
				$fcache = $url;

			if 	($get_cache_line !== '')
				$fcache .= $get_cache_line;

			$this->fcache = CPATH.$fcache.'.html';
			
			return;

		}



		//кеширование всей страницы
		function cache_page($time = 0) { //файловое кэширование
	
				
			//загрузка приложения
			if ($time > 0)  
				if ($this->filecache($this->fcache, $time) == TRUE)
					exit; //выход из приложения
			else	
				$this->cache_page = $time;
			
			return;	

		}





		/*проверка правила роутинга
			$regexp - включить поддержку регулярных выражений
		*/
		function get($pattern, $regexp = True) {

			if ($pattern == '/' and $this->url == '')
				return True;

			
			echo $this->url;


			if ($regexp) { //регулярные выражения
				
				
				echo $pattern;

				if (preg_match('!'.$pattern.'!', $this->url, $matches)) {
					$this->params = $matches;
					return True;
				}	
							
			}
			else {
			    if ($pattern == $this->url)
				return  True;
			}

			
			return False;
		
		}

	

		//кеширование куска
		function cache($key, $time = 0) {
			
			$this->curr_key = $key;
			ob_start();
			
			return False;

		}


		//сохранение кэширования 
		function save() {

			if ($this->curr_key == null) 
				return;

			$result = trim(ob_get_contents());
			ob_end_clean();	

			echo $result;

			return;

		}


		

		//конфиги и настройки 
		function set($name, $value) {

			fAK::$set[$name] = $value;
			return $this;
		}

		

		//центральный шаблон
		function layout($file = null) {

			$this->layout = $file;
			return $this;

		}


		//запуск визуальной части приложения
		function view($fview) {
							
			$fview = VIEWPATH.$fview.'.phtml';

			if ($this->layout !== null) {
				$this->content = $this->render($fview); //основной шаблон страницы
				$flayout = VIEWPATH.$this->layout.'.phtml';
				echo $this->render($flayout, $this->fcache);
			}	
			else 
				echo $this->render($fview, $this->fcache);

			return $this;

		}	


		//запуск шаблона/подшаблона
		function render ($fview, $fcache = '', $interval = 0) {
						
			ob_start();
			include($fview);
			$result = trim(ob_get_contents());
			ob_end_clean();	

			//сохраняем в кеш
			if ($interval > 0)
				if ($fcache ==  '') 
					$this->save2file($fcache, $result);
		
			return $result;

		}
		
		
		//запуск компонента
		private function component($name){
			
			$cfile = COMPATH.$name.'.php';
						
			if (file_exists($cfile)){
				require_once $cfile;
				$cclass = $name;
				return new $cclass();
			}	
			else 	
				return null;
						
		}


		//экспорт php-кода в файл
		function  exportfile($name, $data, $save = True)
		{
			$fname = sha1($name).'.txt';
			echo '$data ='.var_export($data, True);
			
			return;
					
		}
		
		
		//выводит актуального файлового кеша
		function filecache($fcache, $time) {
			if (sizeof($_POST) == 0) { //если пришли данные из формы кэш не нужен
				if ($fcache > 0) {
					if (file_exists($fcache) and ((filemtime($fcache) + $time) > $_SERVER['REQUEST_TIME'])){
						echo file_get_contents($fcache);
						exit;
					}
				}
			}

			return False;

		}

	


		//сохранение данных в файл
		function save2file($file, $data=null)
		{
			
			if ($file == '') return;
			
			$dir = dirname($file);
			if (!is_dir($dir)) {
				mkdir($dir, 0644, True);
				chmod($dir, 0644);
			}
						
			$sfile = fopen($file,'w');
			if ($sfile) {
				flock($sfile, LOCK_EX);
				if (fwrite($sfile, $data))
					$result =  True;
				else
					$result =  False;
				flock($sfile, LOCK_UN);
				fclose($sfile);
				chmod($file, 0775);
			}
									
			
			return $result; 
					
		}
		
		
		//записываем ошибки в лог-файл
		static function log($error)
		{
			
		
		}
		
		
		
	}	
		
		
	