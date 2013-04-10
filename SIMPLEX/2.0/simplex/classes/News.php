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
                NI.position
                FROM news_downloads NI
                LEFT OUTER JOIN news_downloads_text NT ON
                NI.id=NT.id_news_downloads AND
                NT.lang='$lang'
                WHERE id_news='$id'
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
                , NT.content as title
                , NC.type as type
                FROM news_categories NC
                LEFT OUTER JOIN news_cat_title NT ON
                NC.id=NT.id_cat AND
                NT.lang='$lang'
                WHERE NC.id IN (SELECT id_cat FROM news_cat WHERE id_news='$id')
                AND NC.type='$type'
             ";
             return $db->queryAssoc($sql);
        }
        
        static function getCategories($type='news',$lang=null){
            $db =  db();
             if(!$lang) $lang = current_lang();
             if($lang!='all') {
                $sql = "
                   SELECT
                   NC.id
                   , NC.image as image
                   , NT.content as title
                   , NC.type as type
                   FROM news_categories NC
                   LEFT OUTER JOIN news_cat_title NT ON
                   NC.id=NT.id_cat AND
                   NT.lang='$lang'
                   WHERE  NC.type='$type'
                ";
                return $db->queryAssoc($sql);
            }
            else {
                //MARK > tutte le categorie
                $sql = "
                    SELECT id,image FROM news_categories
                    WHERE  [type]='$type'
                ";
                $list =  $db->queryAssoc($sql);
                foreach($list as &$cat){
                    $id_cat = array_get($cat,'id');
                    $sql = "
                        SELECT * FROM news_cat_title
                        WHERE id_cat = $id_cat
                    ";
                    $cat_titles = $db->queryAssoc($sql);
                    $a = array();
                    $langs = langs();
                    foreach($langs as $lg=>$language){
                        $key = $lg;
                        $val = '';
                        foreach($cat_titles as $cat_title){
                            $cat_lang = array_get($cat_title,'lang');
                            if($cat_lang == $lg)  $val = array_get($cat_title,'content');
                        }
                        $a[$key] = $val;
                    }
                    $cat['titles'] = $a;
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
                    $item[$col] = "[". array_get($row,'lang') . "] " . array_get($row,'content');
                }
            }
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
                    $item = "'". $db->escape_string($item) . "'";
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
                self::DbInsert('news_abstract',array('id_news'=>$id,'lang'=>$lang,'content'=>array_get($data,'description')));
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
        static function saveCat ($data){
            $db =  db();
            if(is_string($data)) $data = json_decode($data,true);
            $id = (int) array_get($data,'id',0);
            $titles = array_get_unset($data,'titles',array());
            $response = array();
            if(!$id) {
                self::DbInsert('news_categories',array_pick($data,array('image','type')),$id);
            }
            else {
                //update
                $db->update('news_categories',array_pick($data,array('image','type')),"id=$id");
            }
            if($id) {
               $db->delete('news_cat_title',"id_cat=$id");
                //tabelle collegate
               foreach($titles as $title){
                    $title['id_cat'] = $id;
                   self::DbInsert('news_cat_title',$title); 
               }
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
        static function setFileDownload($id,$id_news,$url,$position){
            $db =  db();
            $id = ($id)?$id:0;
            $data = array(                
                'id_news'=>$id_news,
                'content'=>$url,
                'position'=>$position,
            );
            if(!$id) {
                self::DbInsert('news_downloads',$data,$id);
            }
            else {
                $db->update('news_downloads',$data,"id=$id");
            }
            return $id;
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
        private function parse($apply_filter = true){
            $dir_iterator=new DirectoryIterator($this->dir);
            $c=0;
            $this->_news = array();
            foreach($dir_iterator as $file){
                if($file->isDot() || $file->isDir())continue;
                $filename=$file->getFilename();
                $pattern = '/news\\-(\\d+)\\.(html?|php)$/i';
                $a = array();
                if(!preg_match($pattern,$filename,$a)) continue;
                $this->parse_file($file->getPathname(),$a[1],$apply_filter);
                $c++;
            }
            usort($this->_news,array($this,'sort_comparer'));
        }

        private function get_item_prop($nd,$name,$attribute=""){
            $nd =pq($nd)->find('[itemprop='.$name.']');
            if($nd->length){
                if($attribute) {
                    return pq($nd)->attr($attribute);
                }
                else return trim(pq($nd)->html());
            }
            return null;
        }
        
        private function parse_file($filename,$index,$apply_filter=true){
            $doc = phpQuery::newDocumentFileHTML($filename);
            //get title before remove lang
            $prop_tag = $doc->find('section:first');
            $default_title = "";
            $first_title = $prop_tag->find('[itemprop=title][lang='.$this->lang .']');
            if(!$first_title->length || !$first_title->text()){
                $first_title = $prop_tag->find('[itemprop=title]:first');
                if($first_title->length){
                    $default_title = $first_title->text() . " (" . $first_title->attr('lang') . ")";
                }
            }
            langs_fragment_render($doc,$this->lang,true);
            $prop_tag = $doc->find('section:first');
            
            if($prop_tag->length) {
                $photo = $this->get_item_prop($prop_tag,'photo','src');
                if(!$photo) $photo = 'images/news-image-default.jpg';
                $description = $this->get_item_prop($prop_tag,'description');
                $title = $this->get_item_prop($prop_tag,'title');
                $title = $title?$title:$default_title;
                $creation_str = $this->get_item_prop($prop_tag,'creation');
                $creation =$creation_str ? $this->dtime($creation_str): 0;
                $content = $doc->find('details');
                $item =array(
                    'name'=>basename_without_extension($filename)
                    ,'index'=>$index
                    ,'photo'=>$photo
                    ,'title'=>$title
                    ,'creation_str'=>$creation_str
                    ,'creation'=>$creation
                    ,'description'=>$description
                    ,'lang'=>$this->lang
                    ,'filename'=>$filename
                    ,'content'=> ($content?pq($content)->html():'')
                );
                if($apply_filter){
                    if(hook_call('filter_news',$item)===false) return ;
                }
                $this->_news[]=$item;
            }
        }

        private function dtime($sdate){
            return  Dates::str_to_date_time($sdate,0,1,2,'-',':');
        }
          
        private function sort_comparer($item1,$item2){
            $a = array_get($item1,'creation');
            $b = array_get($item2,'creation');
            if ($a == $b) {
                return 0;
            }
            return ($a > $b) ? -1 : 1;

        }
        function get_news(){
            return $this->_news;
        }
        

        
        static function edit($data,$dir=null,$lang=null){
            if(is_string($data)) $data = json_decode($data,true);
            $filename = array_get($data,'filename',null);
            $cls = new News($dir,$lang);
            $cls->parse(false);
            
            if(!$filename) {
                $filename = $cls->dir. DIRECTORY_SEPARATOR .'news-';
                //nuova (id progressivo)
                $index = 0;
                foreach($cls->get_news() as $n){
                    $i = (int) array_get($n,'index');
                    if($i>$index)$index=$i;
                }
                $index++;
                $filename .= $index . ".html";
            }
            $item = new NewsItem($filename);
            
            $section = array_get($data, 'section',null);
            
            $content = array_get($data, 'content',null);
            $item->set($section,$content,current_lang());
            //fb($item->toString());
            return  $item->save();
            //return file_put_contents($filename,$html);
        }
        
        static function remove($filename){
            unlink($filename );
        }
    }
    # NewsItem Class
    class NewsItem {
        //@property  phpQueryObject doc
        public $doc;
        public $filename="";
        
        private function getEmptyDoc(){
            ob_start();
            ?>
               <section class="hide" itemtype="#news" itemscope="itemscope">
                    <img src="" itemprop="photo">
                    <?php  foreach(langs_keys() as $lg): ?>
                        <var itemprop="title" lang="<?php echo $lg?>"></var>
                        <var itemprop="description" lang="<?php echo $lg?>"></var>
                    <?php endforeach; ?>                                
                    <datetime itemprop="creation"></datetime>
                </section>
               <?php  foreach(langs_keys() as $lg): ?>
                <details lang="<?php echo $lg?>"></details>
               <?php endforeach; ?>  
            <?php
            $buffer = ob_get_clean();
            $this->doc = phpQuery::newDocumentHTML($buffer);
        }
        
        function __construct($filename){
            $this->filename = $filename;
            if(is_file($filename)) {
                $this->doc = phpQuery::newDocumentFileHTML($filename);
            }
            else {
                $this->getEmptyDoc();
            }
        }
        private function find_create ($node, $propname, $template,$lang=null){
            $search = "[itemprop=$propname]";
            if($lang) $search .="[lang=$lang]";
            $item = pq($node)->find($search);
            if(!$item->length) {
                $item = pq($template)->appendTo($node);
                $item->attr('itemprop',$propname);
                if($lang) $item->attr('lang',$lang);
            }
            return $item;
        }
        private function set_item_prop($node, $propname, $template, $content,  $lang = null){
            $item = $this->find_create($node, $propname, $template,$lang);
            $item->html($content);
        }
        private function set_item_attr($node, $propname, $template, $attr, $value,  $lang = null){
            $item = $this->find_create($node, $propname, $template,$lang);
            $item->attr($attr, $value);
        }
        /**
         * @param Array $data 
        */
        public function setSectionData($data,$lang = null){
            if($lang==null) $lang = current_lang();
            $section = $this->doc->find('section:first');
            $this->set_item_attr($section,'photo','<img>','src',array_get($data,'photo','images/news-image-default.jpg'),null);
            $this->set_item_prop ($section,'title','<var></var>',array_get($data,'title',''),$lang);
            $this->set_item_prop ($section,'description','<var></var>',array_get($data,'description',''),$lang);
            $this->set_item_prop ($section,'creation','<datetime></datetime>',array_get($data,'creation',''));
        }
        public function setContentData($content,$lang = null){
            if($lang==null) $lang = current_lang();
            $details = $this->doc->find('details[lang='.$lang.']');
            if(!$details->length){
                $details = pq('<details lang="'.$lang.'"></details>')->appendTo($this->doc);
            }
            $details->html($content);
        }
        public function set($section,$content,$lang=null){
            $this->setSectionData($section,$lang);
            $this->setContentData($content,$lang);
        }
         function save(){
            return file_put_contents($this->filename ,$this->toString());
        }
        function toString(){
            return $this->doc->htmlOuter();
        }
    }
?>