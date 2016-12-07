var resizableOptions = {
			disabled:false,
			containment: "parent",			
		    aspectRatio: false,
		    handles: 'e, w',
		    cursorAt: { left : 5,top : 5 },
		    //handles: 'ne, se, sw, nw',
		    stop:function(event,ui){
		    	
		    	var width = ($(this).width() * 100 ) / $(this).parent().width();
				console.log($(this).parent().attr("class"));
				var clase = "";
				if(width > 0 && width < 8.3 ){			    
				    console.log("col-sm-1");
				    clase = "col-sm-1";
				}else if(width > 8.3 && width < 16.66 ){			    
				    console.log("col-sm-2");
				    clase = "col-sm-2";
				}else if(width > 16.66 && width < 24.99 ){			    
				    console.log("col-sm-3");
				    clase = "col-sm-3";
			    }else if(width > 24.99 && width < 33.32 ){			    
			    	console.log("col-sm-4");
			    	clase = "col-sm-4";
			    }else if(width > 33.32 && width < 41.65 ){			    
			    	console.log("col-sm-5");
			    	clase = "col-sm-5";
			    }else if(width > 41.65 && width < 49.98 ){			    
			    	console.log("col-sm-6");
			    	clase = "col-sm-6";
			    }else if(width > 49.98 && width < 58.31 ){			    
			    	console.log("col-sm-7");
			    	clase = "col-sm-7";
			    }else if(width > 58.31 && width < 66.64 ){			    
			    	console.log("col-sm-8");
			    	clase = "col-sm-8";
			    }else if(width > 66.64  && width < 74.97 ){			    
			    	console.log("col-sm-9");
			    	clase = "col-sm-9";
			    }else if(width > 74.97 && width < 83.3 ){			    
			    	console.log("col-sm-10");
			    	clase = "col-sm-10";
			    }else if(width > 83.3 && width < 91.63 ){			    
			    	console.log("col-sm-11");
			    	clase = "col-sm-11";
			    }else if(width > 91.63 && width < 100 ){			    
			    	console.log("col-sm-12");
			    	clase = "col-sm-12";
			    }

			    $(this).removeAttr("style");
			    $(this).removeAttr("class");
			    $(this).addClass("object ui-resizable " + clase);
		    	console.log(ui.element);
		    	instanceSort();
		    }
		};

$( function() {
	$( '*[data-drag="draggable"]' ).draggable({
	  connectToSortable: ".sortable",
	  handle: ".move",
      helper: "clone",
      revert: false,	  
	});

	$( '*[data-drag="draggable"]' ).disableSelection();
	instanceSort();
    $("form[name='save-template']").on("submit", function(e){
        e.preventDefault();
        $("[contenteditable='true']").attr("contenteditable","false");
        for(name in CKEDITOR.instances){
            CKEDITOR.instances[name].destroy(true);
        }
        var id = this.idRow.value;
        var strHTML = $(document.getElementById("main_content")).html();
        var dataSet = {
            'name'  : this.name.value,
            'html'  : strHTML
        }
        console.log(dataSet);
        //showSpinner();
        $.ajax({
            url     : '/admin/edit/'+id+'/'+this.name.value+'/',
            type    : 'POST',
            data    : dataSet,
            success : function(data){
                if( typeof data.ok !== 'undefined' ){
                    if( data.ok ){
                        alert("Vista grabada");
                    }
                } else {
                    alert("No se pudo grabar. Revise los datos e intente nuevamente.");
                }
                    /*console.log(data);*/
            }
        }).done(function(){
            //showSpinner();
        });;
    });
});

function DeleteObject(s){
	$(s).parent().parent().remove();
}

function isntanceObject(s,object){
    //console.log(s);
    var Element = $(s).parent().parent().find(object);
    //var Element = $(s).parents(".btn-group:first").find(object)

    try{
        $("[contenteditable='true']").attr("contenteditable","false");
        for(name in CKEDITOR.instances){
            CKEDITOR.instances[name].destroy(true);
        }

        $(Element).attr("contenteditable","true");
        Element.ckeditor();
        console.log(Element);
        setTimeout(function(){
            $(Element).focus();
        }, 1000);
        /*$(Element).off("blur");
        $(Element).on("blur",function(){
            $(Element).attr("contenteditable","false");
            CKEDITOR.instances[Element.editor.name].destroy(true);
        });*/
    }catch(e){
        console.log(e);
    }
}


function instanceSort(){
	$( ".sortable" ).sortable({
	  containment: ".sortable",
	  connectWith: ".sortable",
      revert: false,	  
	  handle: ".move",	 
	  placeholder: "sortable-placeholder", 
	  update:function(event,ui) {
	  	$(ui.item).removeClass("ui-draggable ui-draggable-handle ui-draggable-disabled");
	    $(ui.item).removeAttr("style");
	    $(ui.item).removeAttr("data-drag");
	    $(ui.item).resizable(resizableOptions);
	    $(ui.item).draggable({disabled:true});
	    //$(ui.item).resizable(resizableOptions);
		//console.log(ui.item);		
	  }
	});



}


function openPanel(buttonclick,panel){			
	var checkeds = $("#"+buttonclick).attr("checked");			

	if (checkeds == "checked"){
		$("#"+buttonclick).attr("checked",false);
		$("div [data-name='"+ panel +"']").animate({'right':'-320px'},250);
	}else{
		$("#"+buttonclick).attr("checked",true);
		$("div [data-name='"+ panel +"']").animate({'right':'0px'},250);
	}
}
