(function($){
    var self = this;
    console.log("Inicializando Sitio...");
    $("img").on("load",function(){
        var self = this;
        var img = new Image();
        img.onload = function(){
            if(img.height<img.width){
                $(self).addClass("horizontal");
            } else {
                $(self).addClass("vertical");
            }
        }
        img.src = this.src;
    });
})(jQuery);
