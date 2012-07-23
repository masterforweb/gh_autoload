<?php


//экспресс функция доступа к таблице
function Table($name, $config = null) {
	
	$newtable = new fakDB($name);
	$newtable->config = $config;
		
	return $newtable;

}



class fakDB {

	static $db = null; //соединение с основной БД
	static $dbs = null; //массив для подключений других баз данных
	
	public $config = 'db_default';
	private $arkey = null; //ключ возвращаемого массива
	private $query = '' ; //сформированный запрос к базе
	private $table = null;
	private $distinct = '';
	private $select = '*'; // выборка
	private $fields = array(); //список полей в запросе
	private $where = array();
	private $order = '';
	private $items = array(); //список полей
	private $limit = null;
	private $count = null; //общее количество элементов
	private $pages = null; //общее количество страниц для панигатора
	private $offset = 0;
	private $merge = null; //join


	static $deline = ''; //отладка
	static $qcount = 0; //количество запросов

	

	function __construct($table) {
		$this->table = $table;
		return;
	}
	
	
	function __toString() { //выводим запрос
			return $this->builder();
	}
	
	
	
	static function separ($value){
		return '`'.$value.'`';
	}


	static function quote($value){
		return chr(39).$value.chr(39);
	}


	static function table($name, $config = null) {
		
		$newtable = new fakDB($name);
		$newtable->config = $config;
		
		return $newtable;
	}



	/* соединение с базой данных */
	
	static function conn($set = array()) { 

		try {
			$conn = new PDO($set['driver'].':host='.$set['host'].';dbname='.$set['db'], $set['user'], $set['passwd']);		
			return $conn;
		}
		
		catch (PDOException $e) {
      		if (function_exists('noconn'))
      			noconn(); //функция обработки ошибки
	   	}	

	}


	/* отправление запроса с базу данных */
	
	static function query ($query, $config = null) { //запрос
		
		if (fakDB::$db == null) { // если неи соединения создаем его
 			
 			if ($config == null)
				$config = array('driver'=>dfDB, 'db'=>fDB, 'user'=>ufDB, 'passwd'=>pfDB);
						
			if (!array_key_exists('host', $config))
				$config['host'] = 'localhost';

			fakDB::$db = fakDB::conn($config);
		}

		
		if (defined('DEBUG')) { //отладочная информация 
			fakDB::$qcount++; //подсчет количества запросов
			$start = microtime(true); //засекаем время
		}
		
		$result = fakDB::$db->query($query);

		if (defined('DEBUG')) { //выводим отладочную информацию
			$duration = microtime(true) - $start;
			fakDB::$deline .= '['.fakDB::$qcount.'] '.$duration.' : "'.$query.'"'."\n\r"; 
		}

		return $result;	

			
	}

	
	//особая выборка
	function select($value = '*') {
		$this->select = $value;
		return $this;		
	}

	//функция count
	function count($fields = '*') {
		$sql  = $this->select('COUNT('.$fields.')')->builder();
		$result = fakDB::query($sql)->fetch();
		if (isset($result))
			return $this->count = $result[0];
		else
			return 0;
	}


	//добавление значений
	function insert($items = null) {
		
		if (is_array($items)) {
			
			foreach ($items as $key=>$item) {
				$colums[] = $key;
				$values[] = $item;	
			}
			
			$this->query('INSERT INTO '.$this->table.' ('.implode(',',$colums).') VALUES ('.implode(',',$values).');');
		}

		
	}

	
	function where($column, $value = 1, $operation = '=', $prefix = 'AND') {

		$this->where[] = array('column'=>$column, 'operation' => $operation, 'value' => $value, 'prefix' => $prefix);

		return $this;

	}

	
	//сортировка
	function order($field, $type = 'ASC') {
		
		$type = strtoupper(trim($type));

		if ($this->order !== '')
			$this->order .= ',' ;
		
		$this->order .= fakDB::separ($field).' '.$type;

		return $this;

	}

	


	function orwhere ($column, $value, $operation = '=') {

		return $this->where($column, $value, $operation, 'OR');

	}

	
		
	function limit($limit) {
		$this->limit = $limit;
		return $this;
	}	


	
	function builder() { //собираем запрос

		
		$sql = ($this->distinct == '') ? 'SELECT ' : 'SELECT DISTINCT ';
		$sql .= $this->select.' FROM '.fakDB::separ($this->table);

		$wh_count = sizeof($this->where);

		if ($wh_count > 0) { //сборка условий
			$wh_str = '';
			$where = $this->where;
			
			for ($i = 0; $i < $wh_count; $i++) { 
    			if ($i > 0) 
    				$wh_str .= ' '.$where[$i]['prefix'].' ';
    			$wh_str .= fakDB::separ($where[$i]['column']).$where[$i]['operation'].fakDB::quote($where[$i]['value']);
			}

			$sql .= ' WHERE '.$wh_str;

		}
			
		if ($this->order !== '')
			$sql .= ' ORDER BY '.$this->order;	

		if ($this->limit > 0) {
			$sql .= ' LIMIT ';
			if ($this->offset > 0)
				$sql .= $this->offset.', ';
			$sql .= $this->limit;
		}
			
				
		
		return $sql;

	} 


	//вывод результата в массив
	function all($arkey = null) {
		
		return $this->result($arkey);
	
	}


	function first($arkey = null) {
	
		return $this->limit(1)->result($arkey);
	}


	
	//формируем результат выборки
	function result($arkey = null) {
				
		
		if ($this->select !== '*' and $arkey !== null){
			if (!strpos($this->select, $arkey))
				$this->select .= ','.$arkey;

		}

		if ($this->query == '')
			$this->query = $this->builder();

		
		$stmt = fakDB::query($this->query);	

		$i = 0;
				
		while ($data = $stmt->fetch(PDO::FETCH_ASSOC)){
								
			if ($this->merge !== null) {
				foreach ($this->merge as $index=>$merge) {
					$ind = $data[$index]; //индекс в массиве данных
					if (array_key_exists($ind, $merge)){
						$data = array_merge($data, $merge[$ind]);
					}	 		
				}
			}	
			
			if ($arkey !== null) //по уникальному полю 
				$result[$data[$arkey]] = $data;
			else
				$result[] = $data;

			$i++;

		}

		unset($this->merge); //очистка присоединенных массивов

		return $result;		
	}


	
	//панигатор - текущая страница
	function page($page = 0) {
		
		if ($page > 1)
			$this->offset = $this->limit * ($page-1);
		
		return $this;
	}


	//панигатор - возвращает общее количество страниц
	function pages() {
		
		if (!isset($this->pages)) {
			if (!isset($this->count))
				$this->count();
			/*if ($this->limit > $this->count)
				return 1;*/
			$this->pages = (int)($this->count / $this->limit) + 1;
		}		
		
		return $this->pages;

	}


	/* имитация join

		table   - таблица которую мы подключаем
		key     - текущая поле на которое ориентируемся
		column  - колонку которую добавляем в результат
		id      -   

	*/

	
	function merge($column, $data = array()) {
	 		 	
	 	$this->merge[$column] = $data;
	 	
	 	return $this;
 	
 	}


 
 	
}