<?php
if (!defined('_ADODB_LAYER')) {
	require_once (INCLUDE_PATH."/adodb/adodb.inc.php");
}
define('ADODB_ASSOC_CASE', 0);  //lower case
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
//ADODB wrapper
class Database {
	public $conn;
	public function col_quote($col_name){
		switch($this->conn->databaseType){
			case 'odbc_mssql':
			case 'ado_mssql':
				return "[$col_name]";
		}
		return "`$col_name`";
	}
	private $need_conversion=false;
	private $native_encoding='UTF-8';
	static function include_active_record(){
		require_once(INCLUDE_PATH."/adodb/adodb-active-record.inc.php")	;
	}
	static function include_exceptions(){
		require_once(INCLUDE_PATH."/adodb/adodb-exceptions.inc.php")	;
	}
	function __construct($driver,$dsn=null){
		$conn = ADONewConnection($driver);
		if($driver=='ado_mssql') {
			$conn->Connect($dsn);
			$this->conn=$conn;
			return ;
		}
		if($dsn) {
			if(is_array($dsn)){
				extract($dsn,EXTR_OVERWRITE,'');
				$conn->Connect($host,$user,$password,$database);
			}
			else $conn->Connect($dsn);
		}
		$this->conn=$conn;
		switch($conn->databaseType){
			case 'odbc_mssql':
			case 'mssql_n':
			case 'mssql':
			$this->need_conversion=true;
			$this->native_encoding='CP1252';
		}
	}
	/**
	 * Create Instance 
	*/
	static function CreateInstance($driver,$dsn=null){
		$obj=new Database($driver,$dsn);
		$obj->conn=$conn;
		return $obj;
	}
	
	// così si possono chiamare tutti i metodi sottostanti a $conn
	function __call($name , $args){
		return call_user_func_array (array($this->conn,$name),$args);
	}
	function __set($name,$value){
		if($this->conn){
			$this->conn->$name=$value;
		}
	}
	function decode_where($where){
		if(is_string($where)) return $where;
		if(is_array($where)){
			$w = array();
			foreach($where as $key=>$value){
				if($value==null) continue;
				if(is_numeric($key)) {
					//$value is entire statement ie:
					//price>0
					$w[]= $value;
					continue;
				}
				if(is_bool($value)) $value = $value?1:0;
				elseif(!is_numeric($value)) $value = $this->quote($value);
				$w[]= $this->col_quote($key) .'='.$value;
			}
			$where = implode(" AND ",$w);
		}
		else $where="";
		return $where;
	}	
	function quote($v){
		return $this->conn->Quote($v);
	}
	function count($sql,$params=false){
		$conn=$this->conn;
		$sql = "SELECT COUNT(*) as c from ($sql)q";
		$rs=$this->getRows($sql,$params);
		if($rs){
			return $rs[0]['c'];
		}
		return 0;
	}
	
	function html_escape_array (&$a){
		if(is_string($a)){
			htmlentities($a,ENT_NOQUOTES,'UTF-8');
		}
		elseif (is_array($a)) {
			foreach($a as $key=>&$value){
				$this->html_escape_array($value);
			}
		}
	}
	function convert_array(&$a,$to='UTF-8',$from='CP1252'){
		if(is_string($a) ) {
			$a= mb_convert_encoding($a,$to,$from);
		
		}
		elseif (is_array($a)) {
			foreach($a as $key=>&$value){
				$this->convert_array($value,$to,$from);
			}
		}
	}
	
	function autoExecute($table, $arrFields, $mode, $where=false){
		$conn=$this->conn;
		if($this->need_conversion){
			$this->convert_array($arrFields,$this->native_encoding,'UTF-8');
		}
		$b=$conn->AutoExecute($table, $arrFields, $mode, $where);
		$e = $conn->ErrorMsg();
		if(!$b && $e){
			echo $b , $e;
			$this->handle_error($e);
		}
		return $b;
	}
	private function handle_error($e){
		if(is_callable('db_error')){
			db_error($e);
		}
		elseif(is_callable('fb') && isset($_SERVER['SERVER_NAME'])){
			fb($e);
		}
		else {
			echo $e."\n<br>";
			//file_put_contents("E:/logdb.txt",$conn->ErrorMsg());
		}
	}
	function bulk_insert($table, $fields, $arrValues) {
		if(is_array($fields)){
			$fields=implode(',', $fields);
		}
		$insert_rows = array();
		foreach($arrValues as $values){
			$row_values = array();
			foreach($values as $key=>$value){
				if($value==null) $value='NULL';
				elseif(is_bool($value)) $value = $value?1:0;
				elseif(!is_numeric($value)) $value = $this->quote($value);
				$row_values[]=$value;
			}
			$insert_rows[]="(". implode (',',$row_values) . ")";
		}
		$sql = "INSERT INTO $table
		($fields)
		VALUES " . implode(',',$insert_rows). ';';
		return $this->execute($sql);
	}	
	function insert($table, $arrFields){
		return $this->autoExecute($table, $arrFields,'INSERT');
	}

	function delete ($table, $where=false){
		$where= $this->decode_where($where);
		$sql = "DELETE FROM $table ";
		if($where) $sql.="WHERE $where";
		return $this->execute($sql);
	}
	function update($table, $arrFields, $where){
		$where= $this->decode_where($where);
		return $this->autoExecute($table, $arrFields,'UPDATE',$where);
	}
	
	function replace($table, $arrFields, $keyCols,$autoQuote=true){
		$conn=$this->conn;
		
		if($this->need_conversion){
			$this->convert_array($arrFields,$this->native_encoding,'UTF-8');
		}
		
		$b=$conn->Replace($table, $arrFields, $keyCols, $autoQuote);
		if(!$b){
			$this->handle_error($conn->ErrorMsg());
		}
		return $b;
	}

	function execute($sql,$params=false,$fetch=ADODB_FETCH_ASSOC){
		$GLOBALS['ADODB_FETCH_MODE'] = $fetch;
		$conn=$this->conn;
		$this->ParametersParse($sql,$params);
		$b=$conn->Execute($sql,$params);
		$e = $conn->ErrorMsg();
		if(!$b && $e){
			echo $b , $e;
			$this->handle_error($e);
		}
		return $b;
	}
	
	static $PARAMETERS_PARSE_PREFIX="{"; //prefisso nelle query letterali
	static $PARAMETERS_PARSE_SUFFIX="}";
	private function ParametersParse(&$sql, &$inputarr){
		$conn=$this->conn;
		if($inputarr && is_array($inputarr)) {
			
			foreach($inputarr as $key=>$v){
					if(is_numeric($key)) continue;
					
					$typ = gettype($v);
					if ($typ == 'string')
						$v = $conn->qstr($v);
					else if ($typ == 'double')
						$v = str_replace(',','.',$v); // locales fix so 1.1 does not get converted to 1,1
					else if ($typ == 'boolean')
						$v = $v ? $conn->true : $conn->false;
					else if ($typ == 'object') {
						if (method_exists($v, '__toString')) $v = $conn->qstr($v->__toString());
						else $v = $conn->qstr((string) $v);
					}
					else if ($v === null){
						$v = 'NULL';
					}
					
					$sql = str_ireplace(
									self::$PARAMETERS_PARSE_PREFIX.$key.self::$PARAMETERS_PARSE_SUFFIX,
									$v,
									$sql);
					unset($inputarr[$key]);
				}
		}
	}
	function pageExecute($sql, $nrows, $page, $params=false,&$paging=null /*returns paging object*/){
		$GLOBALS['ADODB_FETCH_MODE'] =ADODB_FETCH_ASSOC;
		$conn=$this->conn;
		$this->ParametersParse($sql,$params);
		$rs=$conn->pageExecute($sql, $nrows, $page, $params=false);
		if($rs){
			if(!$paging) $paging=new stdClass();
			$count = $rs->_maxRecordCount;
			$paging->pages=$rs->_lastPageNo;
			$paging->count=$count;
			$paging->pagesize=$nrows;
			$paging->page=$rs->_currentPage;
			$paging->first=1;
			$paging->last=$rs->_lastPageNo;
			$paging->next=($paging->page>=$paging->last)?0:$paging->page+1;
			$paging->prev=($paging->page<=$paging->first)?0:$paging->page-1;
			$rows=$rs->GetRows();
			return $rows;
		}
		return null;
	}
	
	function tableExists($table){
		return $this->execute("DESCRIBE $table");
	}
	
	function getRowsPaging($sql,$params=false,$where=false, $orderby=false,$page=1,$nrows=20){
		if($where) $where = $this->decode_where($where);
		if($where) {
			if($where) $sql.=" WHERE $where ";
		}
		//$count = $this->count($sql,$params);
		if($orderby){
			if(stripos($orderby,'ORDER BY ')===false){
				$orderby = " ORDER BY $orderby ";
			}
			$sql .=  $orderby;
		}
		//$paging = get_paging($page, $nrows,$count);
		$paging=new stdClass();
		$result = $this->pageExecute($sql,$nrows,$page,$params,$paging);
		return array(
			'paging'=>$paging ,
			'result' => $result
		);
	}
	function getRecordset($sql,$params=false,$empty_array=false,$fetch=ADODB_FETCH_ASSOC){
		return $this->execute($sql,$params,$fetch);
	}
	function getRows($sql,$params=false,$empty_array=false,$fetch=ADODB_FETCH_ASSOC){
		$rowset = $this->execute($sql,$params,$fetch);
		//file_put_contents('log.txt',print_r($rowset,1),FILE_APPEND);
		if($this->ErrorMsg()){
			$this->handle_error ($this->ErrorMsg());
		}
		if($rowset && $rowset->RowCount()) {
				$rows=@$rowset->GetRows();
				if($rows) {
					if($this->need_conversion) $this->convert_array($rows,'UTF-8',$this->native_encoding);
					return $rows;
				}
		}
		else {
			//fb($rowset);
		}
		return ($empty_array)?array():null;
	}
	function getRow($sql,$params=false,$fetch=ADODB_FETCH_ASSOC){
		$rowset = $this->execute($sql,$params,$fetch);
		if($rowset) {
			$rows=$rowset->GetRows(1);
			if($rows) return $rows[0];
		}
		return null;
	}
	
	
	function selectRows($table,$filters=false, $fields='*',$orderby=''){
		$sql = "SELECT $fields FROM $table";
		$params= array();
		$where = false;
		if(is_array($filters)) {
			$where = array();
			foreach($filters as $key=>$value){
				$op = '=';
				if(is_array($value)){
					$op = $value[0];
					$value= $value[1];
				}
				if($value===null) {
					$where[] ="$key IS NULL";
					#$params[]="";
				}
				else {
					$where[] = "$key$op?";
					$params[]=$value;
				}
			}
			$where = implode(" AND ",$where);
		}
		elseif(is_string($filters)) {
			$where = $filters;
		}
		if($where) $sql .= " WHERE $where";
		if($orderby) $sql .= " ORDER BY $orderby";
		return $this->getRows($sql,$params,true);
	}
	function selectRow($table,$filters=false, $fields='*') {
		$rows = $this->selectRows($table,$filters,$fields);
		if($rows) return $rows[0];
		return null;
	}
	function MetaType($nativeDBType,$field_max_length=-1,$fieldobj=null){
		$conn=$this->conn;
		$mt=$conn->MetaType($nativeDBType,$field_max_length,$fieldobj);
		switch (strtolower($nativeDBType)){
			case 'tinyint':
				if($field_max_length=1 || ($fieldobj && $fieldobj->max_length==1)){
					$mt='L';
				}
		}
		return $mt;
	}
}
/**
 * funzioni di supporto
*/
function input_parse_dates(&$array,$names){
	if(is_array($array) && is_array($names)){
		foreach($names as $name){
			$v = array_named($array,$name,null);
			if(!$v) {
				$array[$name]=null;
				continue;
			}
			if(preg_match('/^\d{2}\/\d{2}\/\d{4}$/',$v)){
				$array[$name]=str_to_date_it($v);
			}
			elseif(preg_match('/^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2}$/',$v)){
				$array[$name]=str_to_date_time($v,2,1,0,'/',':');
			}
			elseif(preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/',$v)){
				$array[$name]=str_to_date_time ($v,0,1,2,'-',':');
			}
			elseif(preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)){
				$array[$name]=str_to_date ($v,0,1,2,'-');
			}
		}
	}
}
function input_parse_bools(&$array,$names){
	if(is_array($array) && is_array($names)){
		foreach($names as $name){
			$v = array_named($array,$name,0);
			if(!$v||$v=='0'||$v=='false'||$v=='no') {
				$array[$name]=0;
			}
			else {
				$array[$name]=1;
			}
		}
	}
}
?>