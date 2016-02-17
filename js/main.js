/**
 * Created by evgkan on 10.02.2016.
 */
//запускаемся только после полной подгрузки страницы(картинок)
$(window).load(function(){
    window.InitImageAreaSelect = function(){
        var iass = $('.crop-image');
        iass.each( function( ind ){
            var elm = $(this);
            var inputId = elm.data('forinput');
            var coords = elm.data('coords') ;
            if(coords==null) {coords={'x1':0, 'y1':0, 'x2':1, 'y2':1};}
            //console.log(coords);
            var width = elm.width();
            var height = elm.height();
            //console.log(ind, width, height);
            elm.imgAreaSelect({
                handles: true,
                x1: coords.x1*width,
                y1: coords.y1*height,
                x2: coords.x2*width,
                y2: coords.y2*height,
                onSelectEnd: function(img,sel) {
                    var x1=sel.x1/width; var y1=sel.y1/height; var x2=sel.x2/width; var y2=sel.y2/height;
                    updateInputs(inputId,x1,y1,x2,y2);
                }
            });
            updateInputs(inputId, coords.x1, coords.y1, coords.x2, coords.y2);
        });

        function updateInputs(inputId,x1,y1,x2,y2){
            if(x1==x2 || y1==y2){ x1 = y1 = 0; x2 = y2 = 1; }
            $('#'+inputId+'-x1').val(x1);
            $('#'+inputId+'-y1').val(y1);
            $('#'+inputId+'-x2').val(x2);
            $('#'+inputId+'-y2').val(y2);
        }
    }
    window.UnInitImageAreaSelect = function (inputId){
        var elm = $('[data-forinput="'+inputId+'"]');
        console.log(elm);
        elm.imgAreaSelect({ remove: true });
    }
    InitImageAreaSelect();
});