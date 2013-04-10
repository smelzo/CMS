<?php
#COMMON RESOURCE FILE

#START COMMON CODE
if(!isset($resources)) $resources = array();
$resources = array_merge($resources,array(
#START DEFINITIONS    
    'back'=>'back'
    ,'stop'=>'stop'
    ,'go'=>'go'
    ,'loading'=>'Caricamento in corso'
    ,'users'=>'Utenti'
    ,'user'=>'Utente'
    ,'add_user'=>'Aggiungi Utente'
    ,'group'=>'Gruppo'
    ,'edit'=>'Modifica'
    ,'add_group'=>'Aggiungi Gruppo'
    ,'groups'=>'Gruppi'    
    ,'administrator'=>'Amministratore'
    ,'poweruser'=>'Utente avanzato'
    ,'lightuser'=>'Ospite'
    ,'none'=>'Nessuno'
    ,'name'=>'Nome'
    ,'email'=>'E-mail'
    ,'power'=>'Diritti'
    ,'save'=>'Salva'
    ,'remove'=>'Elimina'
    ,'missing_user_login'=>'Email e Password sono obbligatorie'
    ,'missing_group_name'=>'Il Nome è obbligatorio'
    ,'missing_user_name'=>'Il Nome è obbligatorio'
#END DEFINITIONS        
));
#END COMMON CODE
?>