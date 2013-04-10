<?php
/**
 * Add analytics code to page
*/
class ExtensionAnalytics {
    static function parse($doc){
        $UA = getGlobal('ANALYTICS_UA',false);
        if(!$UA) return ;
        ob_start();
        ?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo $UA?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' :
'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(ga, s);
  })();

</script>
        <?php 
        $analytics_code = ob_get_clean();
        $doc->find('body')->append(pq($analytics_code));
    }
    static function add(){
        Simplex::add_parser(array('ExtensionAnalytics','parse'));
    }
}
hook_register('after_standard_parsers',array('ExtensionAnalytics','add'));
?>