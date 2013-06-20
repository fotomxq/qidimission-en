//切换注记
function set_note() {
    if (info_note_en && info_note_zh) {
        if ($("#info-word").data("note") === "0") {
            $("#info-word").data("note", "1");
            $("#info-word").html(info_note_en);
            $("#info-pho").html(info_note_zh);
        } else {
            $("#info-word").data("note", "0");
            $("#info-word").html($("#info-word").data("html"));
            $("#info-pho").html($("#info-pho").data("html"));
        }
    }
}
//初始化
$(document).ready(function() {
    //重置按钮组位置
    var buttons_postion = (($(document).width() - 960) / 2) - 150;
    $("#buttons").css("right",buttons_postion+"px");
    //按键事件监听
    $(document).keypress(function(key) {
        key_code = key.keyCode;
        if (key_code == 32 || key_code == 13) {
            window.location = "init.php?mode=mission&offset=" + (offset + 1);
        }
    });
    //浮动按钮组根据滚动条移动
    $(window).scroll(function() {
        var scroll_top = Math.abs($(document).scrollTop());
        $("#buttons").css("top", scroll_top + 200 + "px");
    });
    //按钮选单弹出
    $("#buttons_next").hover(function(){
        $(this).children("div:first").children("a").stop();
        $(this).children("div:first").children("a").fadeIn("fast");
    },function(){
        $(this).children("div:first").children("a").stop();
        $(this).children("div:first").children("a").fadeOut("fast");
    });
    $("#buttons_show").hover(function(){
        $(this).children("div:first").children("a").stop();
        $(this).children("div:first").children("a").fadeIn("fast");
    },function(){
        $(this).children("div:first").children("a").stop();
        $(this).children("div:first").children("a").fadeOut("fast");
    });
    //播放按钮事件
    $(document).data("audio-key", 0);
    $("#buttons_play > div > a").click(function() {
        key = $(document).data("audio-key") + 1;
        dom = $("audio").get(key);
        if (!dom) {
            key = 0;
            dom = $("audio").get(key);
        }
        dom.play();
        $(document).data("audio-key", key);
    });
    //注记事件
    $("#info-word").data("note","0");
    $("#info-word").data("html",$("#info-word").html());
    $("#info-pho").data("html",$("#info-pho").html());
    $("#info-word").click(function(){
        set_note();
    });
    $("#buttons_note").click(function(){
        set_note();
    });
});