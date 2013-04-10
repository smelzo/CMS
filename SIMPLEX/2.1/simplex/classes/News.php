<?php
    class News {
        private $_news = array();
        private $dir = null;
        private $lang = null;
        // @var Connector
       
        function __construct($dir=null,$lang=null){
            
            if(!$dir && defined('PAGES_PATH')) $dir = mkdir_check(PAGES_PATH. DIRECTORY_SEPARATOR .'news');
            $this->dir=$dir;
            if(!$lang) $lang = current_lang();
            $this->parse();
        }
        private static function itemImages($item,$lang=null){
             $db =  db();
             if(!$lang) $lang = current_lang();
             $id = array_get($item,'id');
             $sql = "
                SELECT
                NI.id as id,
                NI.content as path,
                NT.content as description,
                NI.position
                FROM news_images NI
                LEFT OUTER JOIN news_images_text NT ON
                NI.id=NT.id_news_images AND
                NT.lang='$lang'
                WHERE id_news='$id'
                ORDER BY NI.position
             ";
             return $db->queryAssoc($sql);
        }
        private static function itemDownloads($item,$lang=null){
             $db =  db();
             if(!$lang) $lang = current_lang();
             $id = array_get($item,'id');
             $sql = "
                SELECT
                NI.id as id,
                NI.content as path,
                NT.content as description,
                NI.lang as lang,
                NI.position
                FROM news_downloads NI
                LEFT OUTER JOIN news_downloads_text NT ON
                NI.id=NT.id_news_downloads AND NT.lang='$lang'
                WHERE id_news='$id'
                AND (NI.lang='$lang' OR NI.lang IS NULL)
                ORDER BY NI.position
             ";
             return $db->queryAssoc($sql);
        }
        
        private static function itemCategories($item,$lang=null){
             $db =  db();
             if(!$lang) $lang = current_lang();
             $id = array_get($item,'id');
             $type = array_get($item,'type');
            
            $sql = "
               SELECT
               NC.id
               , NC.image as image
               , NC.name as name
               , NT.content as title
               , ND.content as description
               , NC.type as type
               FROM news_categories NC
               LEFT OUTER JOIN news_cat_title NT ON
               NC.id=NT.id_cat AND
               NT.lang='$lang'
               LEFT OUTER JOIN news_cat_description ND ON
               NC.id=ND.id_cat AND
               ND.lang='$lang'
               WHERE NC.id IN (SELECT id_cat FROM news_cat WHERE id_news='$id')
               AND NC.type='$type'
            ";
             return $db->queryAssoc($sql);
        }
        static function cat_related_data_get($table,$id_cat){
            $db =  db();
            $sql = "
                SELECT * FROM $table
                WHERE id_cat = $id_cat
            ";
            $series = $db->queryAssoc($sql);
            
            $a = array();
            $langs = langs();
            foreach($langs as $lg=>$language){
                $key = $lg;
                $val = '';
                foreach($series as $serie){
                    $cat_lang = array_get($serie,'lang');
                    if($cat_lang == $lg)  $val = array_get($serie,'content');
                }
                $a[$key] = $val;
            }
            return  $a;
        }

        static function getCategories($type='news',$lang=null){
            $db =  db();
             if(!$lang) $lang = current_lang();
             if($lang!='all') {
                $sql = "
                   SELECT
                   NC.id
                   , NC.image as image
                   , NC.name as name
                   , NT.content as title
                   , ND.content as description
                   , NC.type as type
                   FROM news_categories NC
                   LEFT OUTER JOIN news_cat_title NT ON
                   NC.id=NT.id_cat AND
                   NT.lang='$lang'
                   LEFT OUTER JOIN news_cat_description ND ON
                   NC.id=ND.id_cat AND
                   ND.lang='$lang'
                   WHERE  NC.type='$type'
                   order by NC.position asc
                ";
                return $db->queryAssoc($sql);
            }
            else {
                //MARK > tutte le categorie
                $sql = "
                    SELECT id,image,name FROM news_categories
                    WHERE  [type]='$type'
                    order by news_categories.position asc
                ";
                $list =  $db->queryAssoc($sql);
                foreach($list as &$cat){
                    $id_cat = array_get($cat,'id');
   
                    $cat['titles'] = self::cat_related_data_get('news_cat_title',$id_cat);
                    $cat['descriptions'] = self::cat_related_data_get('news_cat_description',$id_cat);
                }
                return $list;
            }
        }
        static function getCategoriesAll($type='news'){
            return self::getCategories($type,'all');
        }
        //deve essere identica a quella precedente
        static function grabNews($dir=null,$lang=null){
            $data = News::getItems('news',current_lang(),null,1,80);
            $items = array_get($data,'result');
            foreach($items as &$item){
                $item['description'] = array_get($item,'abstract');
                $item['photo'] = array_get($item,'image');
            }
            return $items;
        }
        
        static function getRassegna () {
             $data = News::getItems('rassegna',current_lang(),null,1,80);
             $items = array_get($data,'result');
             return $items;
        }
        
        /**
         * types = news | rassegna | schede
         * $complete = estrae anche le lingue
        */
        static function getItems ($type='news',$lang=null,$cat=null,$page=1,$nrows=20,$orderby=false,$where=false) {
            $db =  db();
            if(!$lang) $lang = current_lang();
            $sql = "
                SELECT Q.*
                from (
                    SELECT N.id as id
                    ,N.image as image
                    ,N.creation as creation
                    ,N.type as type
                    ,N.valutazione as valutazione
                    ,N.position as position
                    ,NT.content as title
                    ,NA.content as abstract
                    ,NC.content as content
                    FROM news N
                    LEFT OUTER JOIN news_title NT ON
                    N.id=NT.id_news AND
                    NT.lang='$lang'
                    LEFT OUTER JOIN news_abstract NA ON
                    N.id=NA.id_news AND
                    NA.lang='$lang'
                    LEFT OUTER JOIN news_content NC ON
                    N.id=NC.id_news AND
                    NC.lang='$lang'
                    WHERE type='$type'
                    ";
                if($cat) {
                    $sql .=  "AND N.id IN (SELECT id_news FROM news_cat WHERE id_cat=$cat)";
                }
            $sql .=  "
                )Q
            ";
            if(!$orderby) $orderby = "position asc,creation desc";
            
            $queryResult = $db->getRowsPaging($sql,false,$where,$orderby,$page,$nrows);
            $result = array_get($queryResult,'result');
            $paging = array_get($queryResult,'paging');
            if(is_array($result)) {
                foreach($result as &$item){
                    $id = array_get($item,'id');
                    $item['images'] = self::itemImages($item,$lang);
                    $item['downloads'] = self::itemDownloads($item,$lang);
                    $item['categories'] = self::itemCategories($item,$lang);
                    self::getFallback($item);
                }
            }
            return array(
                         'paging'=>$paging,
                         'result'=>$result,
                         );
        }
        static function getFallback(&$item){
            $columns = array('title','abstract','content');
            $tables  = array('news_title','news_abstract','news_content');
            $db =  db();
            $id = array_get($item,'id');
            foreach($columns as $i=>$col){
                if(array_get($item,$col)) continue; //valorizzato
                $sql = "SELECT content,lang FROM " . $tables[$i] . " WHERE id_news=$id";
                $row = $db->querySingle($sql);
                if($row) {
                    $item[$col] = array_get($row,'content');
                }
            }
        }
        static function getCategory($id,$lang=null){
            $db =  db();
             if(!$lang) $lang = current_lang();
                $sql = "
                   SELECT
                   NC.id
                   , NC.image as image
                   , NC.name as name
                   , NT.content as title
                   , ND.content as description
                   , NC.type as type
                   FROM news_categories NC
                   LEFT OUTER JOIN news_cat_title NT ON
                   NC.id=NT.id_cat AND
                   NT.lang='$lang'
                   LEFT OUTER JOIN news_cat_description ND ON
                   NC.id=ND.id_cat AND
                   ND.lang='$lang'
                   WHERE  NC.id='$id'
                   order by NC.position asc
                ";
                return $db->querySingle($sql);
        }

        static function getItem ($id,$lang=null) {
            $db =  db();
            if(!$lang) $lang = current_lang();
            $sql = "
                SELECT Q.*
                from (
                    SELECT N.id as id,N.image as image,N.creation as creation,N.type as type,N.valutazione as valutazione,N.position as position
                    ,NT.content as title
                    ,NA.content as abstract
                    ,NC.content as content
                    FROM news N
                    LEFT OUTER JOIN news_title NT ON
                    N.id=NT.id_news AND
                    NT.lang='$lang'
                    LEFT OUTER JOIN news_abstract NA ON
                    N.id=NA.id_news AND
                    NA.lang='$lang'
                    LEFT OUTER JOIN news_content NC ON
                    N.id=NC.id_news AND
                    NC.lang='$lang'
                    
                )Q
                WHERE  Q.id=$id
            ";
            $item = $db->querySingle($sql);
            if($item) {
                $item['images'] = self::itemImages($item,$lang);
                $item['downloads'] = self::itemDownloads($item,$lang);
                $item['categories'] = self::itemCategories($item,$lang);
                self::getFallback($item);
            }
            return $item;
        }
        static function getNews ($lang=null,$cat=null,$page=1,$nrows=20,$orderby=false,$where=false) {
            return self::getItems('news',$lang,$cat,$page,$nrows,$orderby,$where);
        }
        
        //se non funziona db->insert!
        static function DbInsert($table,$dataset,&$id=0){
             $db =  db();
             $cols = array();
             $values = array();
             foreach($dataset as $key=>$item){
                 $cols[]=  "[$key]";
                 if($item===null) {
                    $item = 'NULL';
                 }
                 elseif(is_string($item)) {
                    if(function_exists('sqlite_escape_string')) {
                        //fb('use sqlite_escape_string');
                       $item = sqlite_escape_string($item);
                    }
                    else {
                        if(class_exists('SQLite3')) {
                            $item = SQLite3::escapeString($item);
                            //fb('use SQLite3 escapeString');
                        }
                        else $item = str_replace("'","''",$item);
                    }
                    $item = "'". $item . "'";
                 }
                 $values[] = "$item";
             }
             $cols = implode(',',$cols);
             $values = implode(',',$values);
             $sql = "INSERT INTO $table ($cols) VALUES ($values)";
             return $db->execute($sql,null,$id);
             //fb($sql);
        }
//        save
        static function save ($data){
            $db =  db();
            if(is_string($data)) $data = json_decode($data,true);
            $id = (int) array_get($data,'id',0);
            $lang = array_get($data,'lang',current_lang());
            $categories = array_get_unset($data,'categories',0);
            $response = array();
            if(!$id) {
                self::DbInsert('news',array_pick($data,array('image','type')),$id);
                
            }
            else {
                //update
                $db->update('news',array_pick($data,array('image','type')),"id=$id");
            }
            if($id) {
               
                //tabelle collegate
               
                $db->delete('news_content',"id_news=$id AND lang='$lang'");
                $content = array_get($data,'content','');
                self::DbInsert('news_content',array('id_news'=>$id,'lang'=>$lang,'content'=>$content));       
                //$db->Insert('news_content',array('id_news'=>$id,'lang'=>$lang,'content'=>$content));       

                $db->delete('news_title',"id_news=$id AND lang='$lang'");
                self::DbInsert('news_title',array('id_news'=>$id,'lang'=>$lang,'content'=>array_get($data,'title')));
                //$db->Insert('news_title',array('id_news'=>$id,'lang'=>$lang,'content'=>array_get($data,'title')));
                
                $db->delete('news_abstract',"id_news=$id AND lang='$lang'");
                $description = array_get($data,'description');
                self::DbInsert('news_abstract',array('id_news'=>$id,'lang'=>$lang,'content'=>$description));
                //$db->Insert('news_abstract',array('id_news'=>$id,'lang'=>$lang,'content'=>array_get($data,'description')));
                 $response['item'] = self::getItem($id,$lang);
            }
            else {
                $response['item'] = null;
                return $response;
            }
            //categorie
            //cancella pre-esistenti
            $db->delete('news_cat',"id_news=$id");
            if(is_array($categories)){
                foreach($categories as $cat){
                    $id_cat = array_get($cat,'id');
                    self::DbInsert('news_cat',array('id_news'=>$id,'id_cat'=>$id_cat));
                    //$db->Insert('news_cat',array('id_news'=>$id,'id_cat'=>$id_cat));
                }
            }
            return $response;
        }
        
        static function cat_related_data_update ($table,$series,$id){
            $db =  db();
            if(!is_array($series)) return ;
            $db->delete($table,"id_cat=$id");
            //tabelle collegate
           foreach($series as $serie){
                $serie['id_cat'] = $id;
               self::DbInsert($table,$serie); 
           }
        }
        static function saveCat ($data){
            $db =  db();
            if(is_string($data)) $data = json_decode($data,true);
            $id = (int) array_get($data,'id',0);
            $titles = array_get_unset($data,'titles',array());
            $descriptions= array_get_unset($data,'descriptions',array());
            $response = array();
            if(!$id) {
                self::DbInsert('news_categories',array_pick($data,array('image','type','name')),$id);
            }
            else {
                //update
                $db->update('news_categories',array_pick($data,array('image','type','name')),"id=$id");
            }
            if($id) {
                self::cat_related_data_update('news_cat_title',$titles,$id);
                self::cat_related_data_update('news_cat_description',$descriptions,$id);
               //$db->delete('news_cat_title',"id_cat=$id");
               // //tabelle collegate
               //foreach($titles as $title){
               //     $title['id_cat'] = $id;
               //    self::DbInsert('news_cat_title',$title); 
               //}
               $response = $id;
            }
            else {
                $response = 0;
            }
            return $response;
        }
        
        static function removeItem($id){
            $db =  db();
             $db->delete('news',"id=$id"); //i trigger cancellano tutta la roba collegata!
        }
        static function removeCatItem($id){
            $db =  db();
             $db->delete('news_categories',"id=$id"); //i trigger cancellano tutta la roba collegata!
        }
        //multi table positions set
        static function setPositions($table,$series){
            $db =  db();
            if(is_string($series)) $series = json_decode($series,true);
            foreach($series as $item){
                $id =array_get($item,'id');
                $position =array_get($item,'position');
                $db->update($table,array('position'=>$position),"id=$id");
            }
            return 1;
        }
        //cambia definizioni testo in tabelle collegate su thumbs e files
        static function changeCaption($table,$id,$idname,$value,$lang){
            $db =  db();
            $db->delete($table,"$idname=$id AND lang='$lang'");
            $data = array('lang'=>$lang,'content'=>$value);
            $data[$idname]=$id;
            
            self::DbInsert($table,$data);
        }
        static function setGalleryImage($id,$id_news,$url,$position){
            $db =  db();
            $id = ($id)?$id:0;
            $data = array(                
                'id_news'=>$id_news,
                'content'=>$url,
                'position'=>$position,
            );
            if(!$id) {
                self::DbInsert('news_images',$data,$id);
            }
            else {
                $db->update('news_images',$data,"id=$id");
            }
            return $id;
        }
        static function setFileDownload($id,$id_news,$url,$position,$lang=null){
            $db =  db();
            $id = ($id)?$id:0;
            $data = array(                
                'id_news'=>$id_news,
                'content'=>$url,
                'position'=>$position,
                'lang'=>$lang,
            );
            if(!$id) {
                self::DbInsert('news_downloads',$data,$id);
            }
            else {
                $db->update('news_downloads',$data,"id=$id");
            }
            return $id;
        }
        static function fileDownloadChangeLang($id,$lang){
            $db =  db();
            $data = array(                
                'lang'=>$lang,
            );
            $db->update('news_downloads',$data,"id=$id");
        }
        static function removeFileDownload($id){
             $db =  db();
            $db->delete('news_downloads',"id=$id");
            return $id;
        }
        static function removeGalleryImage($id){
            $db =  db();
            $db->delete('news_images',"id=$id");
            return $id;
        }
        

        # parse every file 
        //private function parse($apply_filter = true){
        //    $dir_iterator=new DirectoryIterator($this->dir);
        //    $c=0;
        //    $this->_news = array();
        //    foreach($dir_iterator as $file){
        //        if($file->isDot() || $file->isDir())continue;
        //        $filename=$file->getFilename();
        //        $pattern = '/news\\-(\\d+)\\.(html?|php)$/i';
        //        $a = array();
        //        if(!preg_match($pattern,$filename,$a)) continue;
        //        $this->parse_file($file->getPathname(),$a[1],$apply_filter);
        //        $c++;
        //    }
        //    usort($this->_news,array($this,'sort_comparer'));
        //}


    }

?>