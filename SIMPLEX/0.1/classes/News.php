<?php
    class News {
        private $_news = array();
        private $dir = null;
        private $lang = null;
        
        function __construct($dir=null,$lang=null){
            if(!$dir && defined('PAGES_PATH')) $dir = mkdir_check(PAGES_PATH._.'news');
            $this->dir=$dir;
            if(!$lang) $lang = current_lang();
            $this->parse();
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
        
        static function grabNews($dir=null,$lang=null){
            $cls = new News($dir,$lang);
            $news =  $cls->_news;
            //echo "<pre>";
            //print_r($news);
            //echo "</pre>";
            //exit;
            return $news;
        }
        
        static function edit($data,$dir=null,$lang=null){
            if(is_string($data)) $data = json_decode($data,true);
            $filename = array_get($data,'filename',null);
            $cls = new News($dir,$lang);
            $cls->parse(false);
            
            if(!$filename) {
                $filename = $cls->dir._.'news-';
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