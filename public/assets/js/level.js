$("#submit").click(function(){
    $.ajax({
        url: window.location.href+"/submit", type: "POST", data: { answer: $("#answer").val() },
        success: function (result) {
            
            if(!result["success"]){
                
                $("#message_header").text("未知错误");
                $("#message").text("请稍候重试");
                $("#incorrect_answer").fadeIn();
                setTimeout(function(){$("#incorrect_answer").fadeOut()},5000);
                return;
            }
            if(!result["correct"]){
                
                $("#message_header").text(result["message_header"]);
                $("#message").text(result["message"]);
                $("#incorrect_answer").fadeIn();
                setTimeout(function(){$("#incorrect_answer").fadeOut()},5000);
                
            }else{
                $('.ui.mini.modal').modal('show'); $('.ui.star.rating').rating();
            }
        }, error: function (xhr, ajaxOptions, thrownError) {
            $("#message_header").text("未知错误");
            $("#message").text("请稍候重试");
            $("#incorrect_answer").fadeIn();
            setTimeout(function(){$("#incorrect_answer").fadeOut()},5000);
        }
    });
});

$("#next-level").click(function(){
    $.ajax({
        url: window.location.href+"/rate", type: "POST",
        data: { rating: $('.ui.rating').rating('get rating') },
        success: function (result) {
            console.log(result);
            window.location = window.location.href+"/next"
        }
    });
});
