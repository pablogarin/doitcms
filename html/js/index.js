/*
// Toggle Function
$('.toggle').click(function(){
  // Switches the Icon
  $(this).children('i').toggleClass('fa-pencil');
  // Switches the forms  
  $('.form').animate({
    height: "toggle",
    'padding-top': 'toggle',
    'padding-bottom': 'toggle',
    opacity: "toggle"
  }, "slow");
});
*/

$("#login_form").submit(function(){
    $.ajax({
	  type: "POST",
	  url: "panel/panel.php",
	  data: {"user":$("txtuser").text(), "pass":$("txtpass").text()},
	  success: function(s,e){
	  	if (s == "true") {
	  		
	  	}else{
	  		$("#Alerta").html("Datos Invalidos");
	  	}
	  }
	});
});

