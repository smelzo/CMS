<?php
require_once("phpmailer/class.phpmailer.php");
class Mailer extends PHPMailer{
	
//    function __construct($host,$username,$password,$from,$smtpauth=true,$html=true){
//        $this->Host=$host;
//        $this->Username=$username;
//        $this->Password=$password;
//        $this->IsHTML($html);
//        $this->From=$from;
//		$this->FromName=$from;
//        $this->IsSMTP();
//        $this->SMTPAuth=$smtpauth;
//	}
	private $class_vars;
	
    function sendmail($to,$subject,$body){
       $this->AddAddress($to) ;
       $this->Subject=$subject;
       $this->Body=$body;
       return $this->Send() ;
	}
	function setProperty($key,$value){
		if(!isset($this->class_vars)){
			$this->class_vars = array_keys(get_object_vars($this));
		}
		if(isset($this->$key)){
			$this->$key=$value;
		}
		else {
			foreach($this->class_vars as $name){
				if(strcasecmp($name,$key)===0){					
					$this->$name=$value;
					return ;
				}
			}
		}
	}
	static function Mail($config,$to,$subject,$body,&$err) {
	  $mailer=self::CreateMailer($config);
	  $b = $mailer->sendmail($to,$subject,$body);
	  if(!$b) $err =$mailer->ErrorInfo;
	  return $b;
	}
	static function CreateMailer($config){
		$mailer = new Mailer();
		
		if(is_object($config)) {
			$config=get_object_vars($config);
			
		}
		elseif($config && is_string($config)) {
			eval($config);
		}
		else {
			if(!is_array($config)) return $mailer;
		}
		foreach($config as $key=>$value) {
			$mailer->setProperty($key,$value);
		}
		$mailer->config = $config;
		if(!isset($config['Mailer'])) {
		 $mailer->IsSMTP(); 
		}
		return $mailer;
	}	
}
?>