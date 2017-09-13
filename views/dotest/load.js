var idSum;
var countDownDate;

function setTime(time) {
    countDownDate = time;
    var x = setInterval(function() {
        if (countDownDate) {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            $("#time").html(minutes+":"+seconds);

            if (distance < 0) {
                // clearInterval(x);
                document.getElementById("time").innerHTML = "HẾT GIỜ";
                $("#choiceuser").empty().removeClass('auto-padding');
                setTimeout(function(){
                    window.location = '/php/test/mvc2/index.php';
                },1000);
            }
        }
    }, 1000);
}

function doMoreQuestion(id) {
    idSum = id;
    $("#choiceuser-container").css('width','80%');
    $("#choiceuser").empty().removeClass('auto-padding');
    $("#ajax-load").css('display','block');
    $("#time").css('display','none');
    $("#facebook").hide();
    // $("#choiceuser").;
    $.ajax({
        url: 'index.php?controller=user&action=doMoreQuestion',
        type: 'POST',
        data: {
            id: id
        },
        success: function(data) {
            $("#ajax-load").css('display','none');
            $("#choiceuser").html(data);
        }
    });
}

function seeResult(id) { // id = testid
    idSum = id;
    $("#choiceuser-container").css('width','40%');
    $("#choiceuser").empty().removeClass('auto-padding');
    $("#ajax-load").css('display','block');
    $("#time").css('display','none');
    $.ajax({
        url: "index.php?controller=user&action=seeResult",
        type: 'POST',
        data: {
            id: id
        },success: function(data) {
            $("#ajax-load").css('display','none');
            $("#choiceuser").html(data);
        }
    });
    // Sau khi load câu hỏi xong thì mới load tiếp facebook bởi vì câu hỏi quan trọng hơn load facebook
    $.ajax({
        url: 'index.php?controller=user&action=loadFaceComment',
        type: 'POST',
        data: {
            id: id // test_id
        },success: function (data) {
            $("#facebook").html(data);
        }
    });
}

function getLink(id) { // id = testid
    idSum = id;
}

$(function(){
    $("#yes").click(function(){
        $("#choiceuser-container").css('width','80%');
        $("#choiceuser").empty().removeClass('auto-padding');
        $("#ajax-load").css('display','block');
        $("#facebook").hide();
        $.ajax({
            url: 'index.php?controller=user&action=doMoreQuestion',
            type: 'POST',
            data: {
                id: idSum,
                type: 'newTest'
            },
            dataType: 'json',
            success: function(data) {
                $("#time").text('BẮT ĐẦU').css('display','block');

                setTime(data.time);
                $("#ajax-load").css('display','none');
                $("#choiceuser").html(data.listQuestion).addClass('auto-padding');




                // $("#choiceuser").;
            }
        });
    });

    $("#wrong-button").click(function () {
        $("#choiceuser-container").css('width','40%');
        $(".right").fadeOut("1200");
        $(".wrong").fadeIn("1200");
    });

    $("#right-button").click(function () {
        $("#choiceuser-container").css('width','40%');
        $(".wrong").fadeOut("1200");
        $(".right").fadeIn("1200");
    });

    $("#submit-button").click(function(){
        $("#form-do-test").submit();
    });

    $("#turnoffface-button").click(function(){
        $("#facebook").fadeToggle("1200");
    });

    $(document).on ("click", "input", function () {
        id = $(this).attr("class");
        temp = 'div#'+id;
        $(temp).css({'pointer-events':'none','background-color':'antiquewhite'});
    });
});
