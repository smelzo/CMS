<?php
class MailMessage {
	public $to=array();
	public $cc=array();
	public $bcc=array();
	public $subject='';
	public $from='';//email from
	public $fromName='';
	public $replyto='';
	public $replytoName='';
	/**
	 * array di filename per gli attach
	*/
	public $attachs=array();
	/**
	 * array associativo $key=>$value dove
	 * $key = nome del file
	 * $value=contenuto
	 * per le stringhe da database (es. immagini)
	*/
	public $attachsStrings=array();
	public $htmlBody='';
	public $textBody='';
	
}
?>