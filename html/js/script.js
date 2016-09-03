(function(){
    var self = this;
    var opened = false;
    console.log("Inicializando Sitio...");
    // var url = "http://follert.neversaynever.cl"
    var title = $("title").text();
    $("body").on("click","#cart-container a[href='#cart']",function(evt){
        evt.preventDefault();
        opened = !opened;
        $(this).siblings("#cart").toggle();
    });
    $("#ficha-producto .image-container").zoom({
        duration    : 400
    });
    $("#slider-home").slick({
        autoplay        : true,
        autoplaySpeed   : 5000,
        fade            : true,
        cssEase         : 'linear'
    });
    $("body").on("change", "#select-comuna", function(e){
        $("#valor-despacho").html("$0");
        var zona = $(this).val();
        $.ajax({
            url     : "/ajax/service",
            data    : {
                action  : "costo-comuna",
                zona    : zona
            },
            dataType: "json",
            type    : "POST",
            success : function(resp){
                if( typeof resp === 'string' ){
                    resp = JSON.parse(resp);
                }
                if( resp.ok ){
                    var costo = (resp.costo).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                    $("#valor-despacho").html("$"+costo);
                } else {
                    alert(resp.message);
                }
            }
        });
    });
    /*
    $("body").on("submit", "form[name='despacho-form']", function(e){
        e.preventDefault();

    });
    //*/
    $("body").on("click", ".btn-actions", function(e){
        var id = $(this).attr("data-rel");
        var q = $("#cantidad").val();
        if( typeof q === 'undefined' ){
            q = 1;
        }
        $.ajax({
            url     : "/ajax/service",
            data    : {
                action  : "add",
                product : id,
                quantity: q
            },
            dataType: "json",
            type    : "post",
            success : function(data){
                self.updateCart(false);
            }
        });
    });
    $("body").on("click", ".btn-remove-prd", function(e){
        e.preventDefault();
        var id = $(this).attr("data-rel");
        var display = !($(this).hasClass("btn-danger"));
        $.ajax({
            url     : "/ajax/service",
            data    : {
                action  : "remove",
                product : id
            },
            dataType: "json",
            type    : "post",
            success : function(data){
                if(display)
                    self.updateCart(false);
                else
                    self.updateCheckout();
            }
        });
    });
    $("body").on("click", ".control-cantidad a",function(evt){
        evt.preventDefault();
        var dir = this.className;
        if( dir!="up" && dir!="down" ){
            return;
        }
        var input = $($(this).parents(".cantidad:first")).find("input");
        var currQuantity = input.val();
        var prd = $(this).attr("data-rel");
        var quantity = dir==='up' ? 1 : -1;
        var newQuantity = parseInt(currQuantity)+quantity;
        if( newQuantity==0 ){
            var data = {
                action      : 'remove',
                product     : prd
            }
        } else {
            var data = {
                action      : 'add',
                quantity    : quantity,
                product     : prd
            }
        }
        console.log(data);
        $.ajax({
            url     : "/ajax/service",
            data    : data,
            dataType: "json",
            type    : "post",
            success : function(data){
                self.updateCart(false, false);
                self.updateCheckout();
            }
        });
    });
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
    var cartFixed = false;
    $(window).on("scroll", function(){
        if( this.pageYOffset >= 114 && !cartFixed ){
            cartFixed = true;
            $("#cart-container:not(.responsive)").addClass("fixed-cart");
            $("#cart-container.responsive").addClass("fixed-cart");
        }
        if( this.pageYOffset <114 && cartFixed ){
            cartFixed = false;
            $("#cart-container:not(.responsive)").removeClass("fixed-cart");
            $("#cart-container.responsive").removeClass("fixed-cart");
        }
    });
    //*/
    this.updateCart = function(message, display){
        $("#cart-container.responsive").load("/ajax/service", {action:'getCart', format: 'html', open: opened}, function(){
            if( typeof message !== 'undefined' && message ){
                alert(message);
            }
            if( typeof display==='undefined' || display ){
                $(this).find("#cart").show();
            }
        });
        $("#cart-container:not(.responsive)").load("/ajax/service", {action:'getCart', format: 'html', open: opened}, function(){
            if( typeof message !== 'undefined' && message ){
                alert(message);
            }
            if( typeof display==='undefined' || display ){
                $(this).find("#cart").show();
            }
        });
    }
    this.updateCheckout = function(){
        $("#carro").load("/carro #carro > *", function(){
            //TODO
        });
    }
})();
