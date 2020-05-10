$(document).ready(function(){
  
  //disappear login form and appear register form
  $("#signup").click(function(){
    $(".first").slideUp("slow",function(){
    	$(".second").slideDown("slow");
    });
  });

  //disappear register form and appear login form
  $("#signin").click(function(){
    $(".second").slideUp("slow",function(){
    	$(".first").slideDown("slow");
    });
  });

  //disappear login form and appear email reset form
  $("#reset_form").click(function(){
    $(".login_box").slideUp("slow",function(){
      $(".reset_password_box").slideDown("slow");
    });
  });

  $("#signup2").click(function(){
    $(".reset_password_box").slideUp("slow",function(){
      $(".login_box").slideDown("slow");
    });
  });

});