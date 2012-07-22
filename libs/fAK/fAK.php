<?php


	/*
		fAK 1.0.0
		минимальный набор функций для запуска веб-приложений на php
	*/

	require('libs/hottags/hottags.php'); //функции горячих тегов


	define('URL', trim($_SERVER['REQUEST_URI'],'/'));
	define('SITE', str_replace('www', '', $_SERVER['HTTP_HOST']));


	/* текущие папки */
	define('SITEPATH', $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR); 		// директория сайта
	define('CPATH', SITEPATH.'cache'.DIRECTORY_SEPARATOR);	// директория кеша
	define('APPPATH', SITEPATH.'app'.DIRECTORY_SEPARATOR);	// директория приложения
	define('COMPATH', APPPATH.'components'.DIRECTORY_SEPARATOR);
	define('VIEWPATH', APPPATH.'views'.DIRECTORY_SEPARATOR);


	$c = array(); //массив данных

	/*

		проверяет совпадение адреса с условием ($pattern),
		в случае удачи возвращает True или полученные параметры

	*/


	//роутинг

	function get($pattern) {

		if ($pattern == '/') { //индексовая страница
			if (URL == '')
				return True;
			else
				return False;
		}

		if ($pattern == URL) //прямая проверка
			return True;

		if (preg_match('!'.$pattern.'!', URL, $matches)) //регулярка
			return  $matches;

		return null;

	}


	/* запуск активного щаблона/подшаблона

		fview - файл шаблона
		layout - файл центрального шаблона
		c - переменные шаблона
		cache - кеширование

	*/
	function view ($view, $layout = null, $c = null, $cache = null) {

		//$args = func_get_args());

		$fview = VIEWPATH.$view.'.phtml'; //определяем файл текущего шаблона
		ob_start();
		include($fview);
		$content = trim(ob_get_contents()); //сформированный html текущего шаблона
		ob_end_clean();

	
		if ($layout !== null) {
			$flayout = VIEWPATH.$layout.'.phtml';
			ob_start();
			include($flayout);
			$content = trim(ob_get_contents()); //сформированный html главного шаблона
			ob_end_clean();
		}

		echo $content;
			

	}

	//автозагрузка компонентов
	spl_autoload_register('component'); //по умолчанию грузим класс компонента
	
	
	function component($name) {

		$cfile = COMPATH.$name.'.php';
						
		if (file_exists($cfile))
			require_once $cfile;

	}


	//функция обработки отсутствия соединения с БД
	function noconn() { 
		echo 'Нет соединения с базой';	
		exit;
	}