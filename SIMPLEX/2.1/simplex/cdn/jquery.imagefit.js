(function (){
    var _bgwidth = { width: '100%', height : 'auto' };
    var _bgheight = { width: 'auto', height : '100%' };
    function getImageSize (img,prop){
        if($(img).attr('data-'+prop)) return parseInt($(img).attr('data-'+prop));
        var v = $(img)[prop]();
        $(img).attr('data-'+prop,v);
        return v;
    }
    function getImageWidth(img){
        if($(img).attr('width')) return parseInt($(img).attr('width'));
        return getImageSize(img,'width');
    }
    function getImageHeight(img){
        if($(img).attr('height')) return parseInt($(img).attr('height'));
        return getImageSize(img,'height');
    }

    function ScaleImage(srcwidth, srcheight, targetwidth, targetheight, fLetterBox) {
    
        var result = { width: 0, height: 0, fScaleToTargetWidth: true };
    
        if ((srcwidth <= 0) || (srcheight <= 0) || (targetwidth <= 0) || (targetheight <= 0)) {
            return result;
        }
    
        // scale to the target width
        var scaleX1 = targetwidth;
        var scaleY1 = (srcheight * targetwidth) / srcwidth;
    
        // scale to the target height
        var scaleX2 = (srcwidth * targetheight) / srcheight;
        var scaleY2 = targetheight;
    
        // now figure out which one we should use
        var fScaleOnWidth = (scaleX2 > targetwidth);
        if (fScaleOnWidth) {
            fScaleOnWidth = fLetterBox;
        }
        else {
           fScaleOnWidth = !fLetterBox;
        }
    
        if (fScaleOnWidth) {
            result.width = Math.floor(scaleX1);
            result.height = Math.floor(scaleY1);
            result.fScaleToTargetWidth = true;
        }
        else {
            result.width = Math.floor(scaleX2);
            result.height = Math.floor(scaleY2);
            result.fScaleToTargetWidth = false;
        }
        result.targetleft = Math.floor((targetwidth - result.width) / 2);
        result.targettop = Math.floor((targetheight - result.height) / 2);
    
        return result;
    }
    function resizeImage ( img, settings ) {
        var options =  {
            container : window
        }
        $.extend( options, settings  || {});
        var $img = $(img);
        if( $img.height() == 0 ) {
            console.log('image height 0');
            $img.load( function(){
                resizeImage( $(this), settings );
            } );
            return;
        }
        var image_height = getImageHeight(img);
        var image_width = getImageWidth(img);
        
        //console.log(image_height,image_width);
        var container = options.container;
        if(!$(container).is(':visible')) {
        }
        
        var containerWidth = $( container ).width(),
            containerHeight= $( container ).height(),
            ratio = containerHeight/containerWidth,
            iw = image_width,
            ih = image_height,
            aspectRatio = ih / iw ;
       
        var scale = ScaleImage(image_width,image_height, containerWidth, containerHeight, false);
        
        var properties = {
            width : scale.width + 'px',
            height : scale.height + 'px',
            top : scale.targettop + 'px',
            left : scale.targetleft + 'px',
            position : 'absolute'
        }
        $img.css(properties);

    } //resizeImage

    jQuery.fn.imagefit = function (options){
        if(!$.isPlainObject(options)) options = {};
        return this.each(function (){
            var container = $(this).is('img')?$(this).parent():$(this);
            if(options['container']==window) container = window;
            options['container'] = container;
            container.find('img').each(function (){
                resizeImage (this,options);
            })
        })
    }
})()