(function($){
    this.spinnerOpts = {
          lines: 13 // The number of lines to draw
          , length: 14 // The length of each line
          , width: 7 // The line thickness
          , radius: 14 // The radius of the inner circle
          , scale: 1 // Scales overall size of the spinner
          , corners: 1 // Corner roundness (0..1)
          , color: '#000' // #rgb or #rrggbb or array of colors
          , opacity: 0.25 // Opacity of the lines
          , rotate: 0 // The rotation offset
          , direction: 1 // 1: clockwise, -1: counterclockwise
          , speed: 1 // Rounds per second
          , trail: 60 // Afterglow percentage
          , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
          , zIndex: 2e9 // The z-index (defaults to 2000000000)
          , className: 'spinner' // The CSS class to assign to the spinner
          , top: '50%' // Top position relative to parent
          , left: '50%' // Left position relative to parent
          , shadow: false // Whether to render a shadow
          , hwaccel: false // Whether to use hardware acceleration
          , position: 'relative' // Element positioning
    }
    this.loading = false;
    this.spinner = new Spinner(self.spinnerOpts);
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
    $('[data-toggle="tooltip"]').tooltip()
    try{
        $("textarea.ckeditor").each(function(k,v){ 
            var name = v.name; 
            if( $(v).hasClass("inline") )
                CKEDITOR.inline(name); 
            else
                CKEDITOR.replace(name); 
        });
    } catch(e){
        console.log(e);
    }
    this.showSpinner = function(message = "Cargando&hellip;") {
        var load = $("body");
        if( !this.loading ){
            spinner.spin(load[0]);
        } else {
            spinner.stop();
        }
        this.loading = !this.loading;
    }
    //this.showSpinner();
})(jQuery);
