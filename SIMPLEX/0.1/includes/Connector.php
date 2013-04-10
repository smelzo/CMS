<?php
define('CASING_NONE',0);
define('CASING_LOWER',1);
define('CASING_UPPER',2);

define('XML_MODE_ATTRIBUTES',0);
define('XML_MODE_ELEMENTS',1);
function my_sql_clean_string ($string){
  if(get_magic_quotes_gpc()){  // prevents duplicate backslashes
    $string = stripslashes($string);
  }
  $string = mysql_escape_string($string);
  return $string;
}
class Connector {
	private $cs; //ConnectionString
	public $encoding = 'utf8';
	public $current_encoding = "";
	public $current_link=null; //linkid
	public $last_error;
	public $conn; //bridge adodb class Connector
	
	// così si possono chiamare tutti i metodi sottostanti a $conn
	public function __call($name , $args){
		return call_user_func_array (array($this->conn,$name),$args);
	}
	public function __set($name,$value){
		if($this->conn){
			$this->conn->$name=$value;
		}
	}
	/**
	 *@param $cs can be:
	 *1. string in format host=<host>;database=<...>;user=<...>;password=<...>
	 *2. array (host=>v,database=>v ecc..)
	 *3. object with same properties 
	*/
	public function __construct ($cs,$driver='mysqli'){
		if($driver=='ado_mssql' || $driver=='mssqlnative'){
		  $this->conn = new Database($cs,$driver);
		  return ;
		}
		if(is_string($cs)){
			if(strpos($cs,';')!==false) {
			  $cs = explode(';',$cs);
			  array_change_key_case($cs);
			}
			else {
			  $this->conn = new Database($driver,$cs);
			   return ;
			}
		}
		elseif(is_object($cs)) {
		  $cs =get_object_vars($cs);
		}
        if(is_array($cs)){
			$this->cs=new ConnectionString();
			$this->cs->host=$cs['host'];
			$this->cs->database=$cs['database'];
			$this->cs->user=$cs['user'];
			$this->cs->password=$cs['password'];
			
			$this->conn = new Database($driver,$cs);
        }
		else throw new Exception("invalid argument",1);
		$linkid = @$this->getConn();
	}

	function __destruct(){
	  /*
		if($this->current_link) {
			mysql_close($this->current_link);
		}
	 */
	}
	function getConn (){
		if($this->current_link && is_resource($this->current_link)) return $this->current_link;		
		$cs = $this->cs;
		$linkID  =  mysql_connect($cs->host, $cs->user, $cs->password) or need_config('database');// or die("Could not connect to host.");
		
        if(!$linkID) return false;
		mysql_select_db($cs->database, $linkID)  ;
		$this->current_link=$linkID;
		
		return $linkID;
	}
	
	function throwError($msg){
		throw new Exception($msg);
	}
	
	function getResult ($sql){
		$conn = $this->getConn();
		//mysql_query("SET NAMES '{$this->encoding}'");
		if(!$this->current_encoding) {
			$this->setEncoding($this->encoding);
		}
		
		$result = mysql_query($sql,$conn) or $this->throwError($sql . "\r\n" . mysql_error($conn));
		return  $result ;
	}
	
	function str_type_to_php_type($s){
		$s = strtolower($s);
		switch ($s){
			case 'char':
			case 'varchar':
			case 'tinytext':
			case 'text':
				return array('php_type'=>'string','type_variant'=>'');
			case 'mediumtext':
			case 'longtext':
				return array('php_type'=>'string','type_variant'=>'longtext');
			case 'enum':
			case 'set':
				return array('php_type'=>'string','type_variant'=>'select');
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				return array('php_type'=>'string','type_variant'=>'blob');
			case 'tinyint':
				return array('php_type'=>'bool','type_variant'=>'');
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'bigint':
				return array('php_type'=>'number','type_variant'=>'int');
			case 'float':
			case 'double':
			case 'decimal':
				return array('php_type'=>'number','type_variant'=>'decimal');
			case 'date':
				return array('php_type'=>'datetime','type_variant'=>'date');
			case 'datetime':
			case 'timestamp':
			case 'time':
				return array('php_type'=>'datetime','type_variant'=>'datetime');
		}
	}
	function str_enum_decode ($t){
		$match=array();
		preg_match_all("/\'(\\w+)'/", $t, $match);
		return implode(',',$match[1]);
	}
	
	function describe ($table,$full=false,$exclude_autoincr=false) {
		$sql = "SHOW " . (($full)?'FULL ':'') . "COLUMNS FROM $table";
		$ds = $this->queryAssoc($sql);
		$result = array();
		if($ds) {
			foreach($ds as $row){
				if($exclude_autoincr && $row['Extra']=='auto_increment') {
				continue;
				}
				$newrow = array();
				foreach($row as $key=>$value){					
					if($key=='Type') {
						$match = array();
						preg_match('/^(\\w+)/m', $value, $match);
						$newrow['typename']=(count($match)==2)?$match[1]:'';
						$p_type = $this->str_type_to_php_type($newrow['typename']);
						$newrow['php_type']=$p_type['php_type'];
						$newrow['type_variant']=$p_type['type_variant'];
						
						$match = array();
						preg_match('/(\\d+)/m', $value, $match);
						$newrow['len']=(count($match)==2)?$match[1]:'';
						if($newrow['type_variant']=='select'){
							$newrow['len']='';
							$newrow['values']=$this->str_enum_decode($value);
						}
					}
					$newrow[strtolower($key)]=$value;
				}
				$result[$row['Field']]=$newrow;
			}
		}
		return $result;
	}
	
	function queryStructure ($tablename){
		return $this->getFields($tablename);
		$result = $this->getResult($sql);
		if (!$result) return null;
		$info = array();
		/* get column metadata */
		$i = 0;
		while ($i < mysql_num_fields($result)) {
		    $meta = mysql_fetch_field($result, $i);
		    if ($meta) $info[] = $meta;
		    $i++;
		}
		mysql_free_result($result);
		return $info;
	}
	
	function queryStructureInfo ($sql,$metapropname='name'){
		$info = $this->queryStructure($sql);
		$result = array();
		foreach($info as $col){
			$v = get_object_vars($col);
			$result[]=$v[$metapropname];
		}
		return $result;
	}

	//print structure of a query
	function printStructure ($sql){
		$structure = $this->queryStructure($sql) or die("error in $sql");
		echo '<table border="1" cellpadding="2" cellspacing="0">';
		$i=0;
			foreach($structure as $columnInfo){
				$info = get_object_vars($columnInfo);
				$out = '<tr>';
				$head = '<tr>';
						foreach($info as $key=>$value){
				$out .= "<td>$value</td>";
				$head .= "<th>$key</th>";
						}
				$out .= '</tr>';
				$head .= '</tr>';
				if(!$i) echo($head);
				echo($out);
				$i++;
			}
		echo '</table>';
	}
	/**
	 * @return Array list of databases' names
	*/
	function getDatabases(){
		return $this->conn->MetaDatabases();
	}
	function getFields($tablename,$database=null,$nameOnly = false){
	  if(!$nameOnly){
		$fs= $this->conn->MetaColumns($tablename,false);
		$a = array();
		foreach($fs as $obj){
		  $a[]=get_object_vars($obj);
		}
		return $a;
	  }
	  else return $this->conn->MetaColumnNames($tablename);
	}
	
	function getTables($database=null){
	  return $this->conn->MetaTables('TABLES');
	}
	
	/**
	 * questa funzione accoda una riga (rappresentata come array assoc)
	 * ad una tabella creandola se non esiste e creando i campi che non esistono 
	*/
	function storeDynamicData($tablename,$data){
//		se la tabella non è presente la crea
		if(!$this->tableExists($tablename)) {
		$r=$this->execute("CREATE TABLE `$tablename` (`_progid` int(11) NOT NULL auto_increment,PRIMARY KEY  (`_progid`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		if($r==-1) {
			echo $this->last_error;
			die;
		}
		}
		$fields = $this->getFields($tablename);
		$fieldnames=array();
		$missingfields = array();
		$sets = array();
		foreach($fields as $field) $fieldnames[]=$field['name'];
		
		foreach($data as $key=>$value){
			$key=strtolower($key);
			$TYPE="VARCHAR(255)";
			if(is_array($value) || is_object($value)) {
				$value = serialize($value);
				$TYPE="LONGTEXT";
			}
			if(!in_array($key,$fieldnames)) {
				$missingfields[]= "ADD COLUMN `$key` $TYPE";
			}
			
			$sets[]="`$key`=".$this->parseValue($value);
		}
		if($missingfields) {
			$r = $this->execute("ALTER TABLE `$tablename` " . implode(",",$missingfields));
			if($r==-1) {
				echo $this->last_error;
				die;
			}
		}
		$this->execute("INSERT INTO `$tablename` SET ". implode(",",$sets));
		
	}
	function last_id(){
	  return $this->conn->Insert_ID();
	}
	function Insert($tablename,$data,&$id=0){
		$b = $this->conn->insert($tablename,$data);
		if($b) $id = $this->conn->Insert_ID();
		$this->fire_DB_EXECUTE(1,"INSERT $tablename");
		return $this->conn->Affected_Rows();

	}
	
	/**
	 * $tablename=nome tabella
	 * $data=array associativo con i dati
	 * $execute true per eseguire il codice SQL, false per restituirlo
	 * $var_prefix=prefisso dato ai nomi dei campi
	 * 
	 * $addSetCallback=callback per aggiungere campi
	 * @example addSetCallback:
	 * function myCallBack(&$set,$data){
	 * 	$set[name]=$data[name];
	 * }
	*/
	function InsertUpdate($tablename,$data,$execute=true,$key_field_name='',$var_prefix='',$addSetCallback=null,$options=null){
		if(!$options) $options=array();
		$fields_case = array_named($options,'fields_case',CASING_NONE);
		$fields = $this->getFields($tablename);
		$set = array(); //array assoc nomeCampo=>valore
		$key_field_name='';
		$key_field_value=0;
		foreach($fields as $field){
			$not_null = (
						 (isset($field['not_null']) && $field['not_null']==1) ||
						 (isset($field['flags']) && stripos($field['flags'],'not_null')!==false));
			$primary_key = (
							(isset($field['primary_key']) && $field['primary_key']==1) ||
							(isset($field['flags']) && stripos($field['flags'],'primary_key')!==false));
			$auto_increment = (
						  (isset($field['auto_increment']) && $field['auto_increment']==1) ||
						  (isset($field['flags']) && stripos($field['flags'],'auto_increment')!==false));
			$name =$field_name= $field['name'];
			if($fields_case!=CASING_NONE){
				if($fields_case==CASING_LOWER) $name =	strtolower($name);
				elseif($fields_case==CASING_UPPER) $name =	strtoupper($name);
			}
			$dataname = $var_prefix.$name;
			$datavalue = (array_key_exists($dataname,$data))?$data[$dataname]:null;
			$value = $this->parseValue($datavalue);
			if($auto_increment) {
				$key_field_name=$name;
				$key_field_value=$datavalue;
				continue;
			}
			if(!array_key_exists($dataname,$data)) continue;
			$set[$field_name]=$value;
		}
		if($addSetCallback && is_callable($addSetCallback)) {
			$addSetCallback($set,$data);
		}
		$statement = array();
		foreach($set as $key=>$value){
			$statement[]="`$key`=$value";
		}
		
		$join = implode(",\n",$statement);
		$action = 'INSERT';
		if($key_field_value && $key_field_name) {
			if($this->queryCount("SELECT `$key_field_name` from `$tablename` WHERE `$key_field_name`=$key_field_value")) {
				$action = "UPDATE";
				$sql = "UPDATE `$tablename` SET $join WHERE `$key_field_name`=$key_field_value";
				
			}
			else {
				$statement[]="`$key_field_name`=".$this->parseValue($key_field_value);
				$join = implode(",\n",$statement);
				$sql = "INSERT INTO `$tablename` SET $join";
				
			}
		}
		else $sql = "INSERT INTO `$tablename` SET $join";
		//echo "$sql\n";
		
		if(!$execute) return $sql;
		if($action == 'INSERT') {
			$id=0;
			$numrows = $this->execute($sql,null,$id);			
		}
		else {
			$id=$key_field_value;
			$numrows = $this->execute($sql);
		}
		$last_error=null;
		if(!$numrows ||$this->last_error) $last_error=$this->last_error;
		return array(
			$key_field_name=>$id,
			'action'=>$action,
			'rows'=>$numrows,
			'error'=>$last_error,
			'sql'=>$sql
		);
	}
	
	/**
	 * emulate MYSQL's REPLACE STATEMENT but not require an Index field
	 * $tablename : name of table
	 * $set : associative array with field and values (do not escape values)
	 * $key_fields : string or array with name(s) of fields to check
	*/
	function replace($tablename,$set,$key_fields,$execute=true){
		
		if(!is_array($set)) return -1;
		if(!is_array($key_fields)) $key_fields=array($key_fields);
		
		$r = $this->conn->replace($tablename , $set , $key_fields,true);
		$this->fire_DB_EXECUTE(1,"REPLACE $tablename");
		return $r;

	}
	/**
	 * $tablename = nome tabella
	 * $set = array con i valori
	 * $where = array con i filtri
	*/
	function update($tablename,$set,$where,$execute=true){
	  $b=$this->conn->update($tablename,$set,$where);
	  $this->fire_DB_EXECUTE(1,"UPDATE $tablename");
	  return $this->conn->Affected_Rows();
	}

	function delete($tablename,$where,$execute=true){
		$b=$this->conn->delete($tablename , $where);
		$this->fire_DB_EXECUTE(1,"DELETE $tablename");
		return $this->conn->Affected_Rows();
	}

	
	function tableExists ($tablename){
		$tables = $this->getTables();
		return in_array($tablename,$tables);
	}
	
	function enumTypeArray($tablename,$enumField){
		$result=array();
		$result[0]='';
		$tableinfo = $this->queryAssoc("describe `$tablename` `$enumField`");
		if(count($tableinfo)){
			$rowinfo = $tableinfo[0];
			$stype=$rowinfo['Type'];
			$stype=str_replace("'",'',str_replace(')','', str_replace('enum(','',$stype)));
			$atype = explode(',',$stype);
			foreach($atype as $value){
				$result[]=trim($value);
			}
		}
		return $result;
	}
	//costruisce l'sql da un nome di tabella
	function table_select($tablename,$fields = null){
		$fieldnames = '*';
		if(is_array($fields)) {
			$fieldnames = implode(',',$fields);
		}
		elseif(is_string($fields)) {
			$fieldnames = $fields;
		}
		return "SELECT $fieldnames FROM $tablename";
	}
	
	function getRowsPaging($sql,$params=false,$where=false, $orderby=false,$page=1,$nrows=20){
	  return $this->conn->getRowsPaging($sql,$params,$where,$orderby,$page,$nrows);
	}
	
	//array di oggetti
	function queryObjects ($sql,$params=false){
	  $result = $this->queryAssoc($sql,$params);
	  $list = array();
	  foreach($result as $row){
		$list[]=array_to_object($row);
	  }
	  return $list;
	  /*
		$result = $this->getResult($sql);
		$rows = array();
		while ($row = mysql_fetch_object($result)) {
			$rows[]=$row;
		}
		mysql_free_result($result);
		return $rows;
	*/
	}	
	//lista di key/value (a due campi 1=key 2=value)
	//da usare per i select
	function queryAssocList ($sql,$params=false){
	  $result = $this->conn->getRows($sql,$params,true,ADODB_FETCH_NUM);
	  $list = array();
	  foreach($result as $row){
		$key = $row[0];
		$value = (isset($row[1]))?$row[1]:$key;
		$list[$key]=$value;
	  }
	  return $list;

	}//queryAssocList

	//array di array assoc
	//il param $keyfield indica il nome del campo che
	//contiene il valore della chiave di riga (es. un ID)
	//se lasciato a null l'array risultante sarà numerico
	function queryAssoc ($sql,$keyfield=null,$params=false){
		$result = $this->conn->getRows($sql,$params,true);
		if(!$keyfield) return $result;
		$rows = array();
		foreach($result as $row){
		  $rows[$row[$keyfield]]=$row;
		}
		return $rows;
	  /*
		$result = $this->getResult($sql);
		$rows = array();
		while ($row = mysql_fetch_assoc($result)) {
			if($keyfield===null || !isset($row[$keyfield])) {
				$rows[]=$row;
			}
			else {
				$rows[$row[$keyfield]]=$row;
			}
		}
		mysql_free_result($result);
		return $rows;
*/
	}//queryAssoc
	/**
	 * lo stesso di queryAssoc soltanto che come item
	 * ritorna un valore
	*/
	function queryNameValue($sql,$keyFieldname,$valueFieldname,$params=false){
	  $result = $this->queryAssoc($sql,null,$params);
	  $rows = array();
	  foreach($result as $row){
		$rows[$row[$keyFieldname]]=$row[$valueFieldname];
	  }
	  return $rows;
	  /*
		$result = $this->getResult($sql);
		$rows = array();
		while ($row = mysql_fetch_assoc($result)) {
			$rows[$row[$keyFieldname]]=$row[$valueFieldname];
		}
		mysql_free_result($result);
		return $rows;
*/
	}//queryNameValue
	
	function queryAssocJSON($sql,$keyfield=null){
		$result =$this->queryAssoc ($sql,$keyfield);
		return json_encode($result);
	}
	
	//array di array assoc with key
	//consente di mettere un campo (es. l'id) come chiave
	function queryAssocKey ($sql,$keyname,$params=false){
	  return $this->queryAssoc($sql,$keyname,$params);
	  /*
		$result = $this->getResult($sql);
		$rows = array(); //array associativo risultante
		while ($row = mysql_fetch_assoc($result)) {
			$key=$row[$keyname];
			$rows[$key]=$row;
		}
		mysql_free_result($result);
		return $rows;
	  */
	}//queryAssocKey
	
	
	function querySingle ($sql,$params=false){
	  return $this->conn->getRow($sql,$params);
	  /*
		$result = $this->queryAssoc($sql);
		if($result && count($result)) {
			return $result[0];
		}
		return null;
*/
	}
	//array di array index
	function queryArray ($sql,$params=false){
	  return $this->conn->getRows($sql,$params,true,ADODB_FETCH_NUM);
	  /*
		$result = $this->getResult($sql);
		$rows = array();
		while ($row = mysql_fetch_row($result)) {
			$rows[]=$row;
		}
		mysql_free_result($result);
		return $rows;
*/
	}//queryArray
	
	//array dei valori del primo campo di ogni riga
	function querySimpleArray ($sql,$params=false){
	  $result = $this->queryArray($sql,$params);
	  $list = array();
	  foreach($result as $row){
		$list[]=$row[0];
	  }
	  $result = $this->getResult($sql);
	  return $list;
	/*
		while ($row = mysql_fetch_row($result)) {
			$list[]=$row[0];
		}
		mysql_free_result($result);
		return $list;
	*/
	}//querySimpleArray
	
	//query Scalar
	function queryScalar($sql,$params=false){
	  $row = $this->conn->getRow($sql,$params,ADODB_FETCH_NUM);
	  if($row) return $row[0];
	  return null;
	/*
		$result = $this->getResult($sql);
		$a = mysql_fetch_row($result);
		if($a) return $a[0];
		return null;
		*/
	}//queryScalar
	
	/**
	 * queryCount
	 * @param $force_use_count racchiude in ogni caso il
	 * param $sql in una select count(*)
	*/
	function queryCount ($sql,$force_use_count=false){
		if(!strripos($sql,'count(') || $force_use_count){
			$sql=trim($sql);
			if(strpos($sql,' ')!==false) $sql = "($sql)";
			$sql = "SELECT COUNT(*) from $sql c";//for table name
		}
		$result = $this->queryScalar($sql);
		if($result==null) $result=0;
		return $result;
	  /*
		$result = $this->getResult($sql);
		$c=0;
		while ($row = mysql_fetch_row($result)) {
			$c=$row[0];
			break;
		}
		mysql_free_result($result);
		return $c;
  */
	}//queryCount
	
	function queryJson ($sql){
		$rows = $this->queryObjects($sql);
		return json_encode($rows);
	}
	
	function queryXml ($sql,$rootname='root',$case=CASING_LOWER){
		$doc = new DomDocument;
		$root = $doc->appendChild($doc->createElement($rootname));
		$rows = $this->queryAssoc($sql);
		foreach ($rows as $row) {
			$node = $root->appendChild($doc->createElement($rootname));
			foreach($row as $key=>$value){
				$attname = $key;
				if($case==CASING_LOWER) $attname = strtolower($attname);
				if($case==CASING_UPPER) $attname = strtoupper($attname);
				$node->setAttribute($attname,$value);
			}
		}
		return $doc;
	}
	function escape (&$s){
	  if(is_array($s)){
		foreach ( (array) $s as $k => $v ) {
				if (!is_object($v)) {
					$this->escape($s[$k]);
				} 
			}
	  }
	  else {
		$this->escape_string($s);
	  }
	  return $s;
	}
	function escape_string($s){
	  if($s) $s = my_sql_clean_string(strval($s));
	  return $s?$s:'';
	}
	function parseValue ($value){
		// for insert/update query
		return ($value===null)?"NULL":"'". my_sql_clean_string( strval($value) ) ."'";
	}
	
	function parseValue_date_it ($value){
		$sdate = str_to_mysqldate_it($value);
		if ($sdate!='') return "'$sdate'";
		return 'NULL';
	}

	function parseValue_currency_it ($value){
		$num = currency_deformat($value);
		if ($num!=null) return $num;
		return 'NULL';
	}

	function parseParameters ($sql,$params){
		foreach($params as $key=>$value){
			$v=$this->parseValue($value);
			$p = $key;
			if(strpos($p,'@')==false) $p = "@".$p;
			$sql = str_ireplace($p,$v,$sql);
		}
		return $sql;
	}
	/**
	 * settare l'encoding serve per poter inviare stringhe
	 * codificate ad es. in UTF 8 
	*/
	function setEncoding($encoding) {
		$conn = $this->getConn();
		if($this->encoding!=$encoding) $this->encoding=$encoding;
		$this->current_encoding=$encoding;
		mysql_query("SET character_set_results=" . $encoding,$conn);
		mysql_query("SET character_set_client=". $encoding,$conn);
		mysql_query("SET character_set_connection=". $encoding,$conn);	
	}
	function fire_DB_EXECUTE($i=1,$sql=""){
		if(function_exists('DB_EXECUTE_EVENT')) {
		  call_user_func('DB_EXECUTE_EVENT',$i,$sql);
		}
	}
	function execute_recordset($sql,$params=null){
	  return $this->conn->execute($sql,$params);
	}
	function execute ($sql,$params=null, &$id=0){
		$rs = $this->conn->execute($sql,$params);
		$id = $this->conn->Insert_ID();
		$this->last_error=$this->conn->ErrorMsg();
		$i = $this->conn->Affected_Rows();
		$this->fire_DB_EXECUTE($i,$sql);
		return $i;
	  /*
		$conn = $this->getConn();
		if(!$this->current_encoding) {
			$this->setEncoding($this->encoding);
		}
		if(is_array($sql)) {
			$result = array();
			$affected_rows=0;
			foreach($sql as $sqlc){
				$i =$this->execute($sqlc);
				if($this->last_error)fb($this->last_error);
				$affected_rows+=$i;
			}
			return $affected_rows;
		}
		if($params) $sql = $this->parseParameters($sql,$params);
		
		$res = mysql_query($sql,$conn);
		if (!$res) {
			$this->last_error=mysql_error($conn);
			return -1;
		}
		$id=mysql_insert_id ($conn);
		$i = mysql_affected_rows ();
		if(is_resource($res))mysql_free_result($res);
		if(function_exists('DB_EXECUTE_EVENT')) {
		  call_user_func('DB_EXECUTE_EVENT',$i,$sql);
		}
		return $i;
*/
	}
	
	function executeFile($filename,$params, &$id=0){
		$sql = file_get_contents($filename) or die("File $filename not exists");
		$this->execute($sql,$params, $id);
	}
}

?>