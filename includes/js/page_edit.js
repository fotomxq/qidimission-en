//初始化
var message_object_name = "#message";
var message_time;

//激活消息框
function message(type, title, content) {
    var attr_class = "";
    switch (type) {
        case 1:
            attr_class = "alert-error";
            break;
        case 2:
            attr_class = "";
            break;
        case 3:
            attr_class = "alert-info";
            break;
        default:
            attr_class = "alert-success";
            break;
    }
    $(message_object_name).parent().parent().click(function() {
        message_hide();
    });
    $(message_object_name).html($(document).data("message_html") + content);
    $(message_object_name).children("h4").html(title);
    $(message_object_name).attr("class", $(document).data("message_class") + " " + attr_class);
    $(message_object_name).show();
    $(message_object_name).parent().parent().fadeIn();
    clearTimeout(message_time);
    message_time = setTimeout("message_hide();", 5000);
}
//隐藏消息框事件
function message_hide() {
    $(message_object_name).parent().parent().fadeOut();
}

//编辑器封装
var edit = new Object();
edit.table_name = "#data-table";
edit.tip_name = "#data-tip";
edit.tip_a_name = "a[href='#tip-level']";
edit.button_add_auto_name = "a[href='#add-title-auto']";
edit.button_edit_name = "a[href='#edit-unit-save']";
edit.button_edit_word_name = "a[href='#edit-word-save']";
edit.button_edit_word_info_name = "a[href='#edit-word-url']";
edit.button_del_name = "a[href='#del-id-ok']";
edit.button_view_name = "a[href='#table-view']";
edit.button_set_mission_name = "a[href='#table-set-mission']";
edit.button_up_name = "a[href='#table-button-up']";
edit.button_down_name = "a[href='#table-button-down']";
edit.input_add_name = "#add-input";
edit.input_edit_name = "#edit-unit-input";
edit.b_del_name = "#del-id-name";
edit.table_type_arr = ["word", "unit", "class"];
edit.table_type = 2;
edit.ajax_url = "do_edit.php?mode=";
edit.list_parent = 0;
edit.list_page = 1;
edit.list_max = 9999;
edit.edit_data_key = 0;
edit.ajax_on = true;
edit.tip_word_time = "";
edit.tip_word_arr = new Array();
//获取列表
edit.get_list = function() {
    if (edit.ajax_on == true) {
        edit.ajax_on = false;
        //清空表
        edit.table_clear();
        edit.edit_data_key = 0;
        //从URL获取数据
        $.ajax(edit.ajax_url + "list&parent=" + edit.list_parent + "&type=" + edit.table_type + "&page=" + edit.list_page + "&max=" + edit.list_max, {
            "dataType": "json",
            "complete": function(a, b) {
                //解锁
                edit.ajax_on = true;
            },
            "success": function(data,t) {
                if (data) {
                    //保存数据
                    $(edit.table_name).data("data", data);
                    //根据表类型更新内容
                    res_row = 0;
                    if (data["status"]["res"]) {
                        res_row = Math.abs(data["status"]["row"]);
                        //更新表内容
                        $(data["status"]["res"]).each(function(i) {
                            if (edit.table_type == 0 && !data["status"]["res"][i]["word"]) {
                                edit.table_add(i, data["status"]["res"][i]["post_title"] + " [没有单词信息]", edit.table_type);
                            } else {
                                edit.table_add(i, data["status"]["res"][i]["post_title"], edit.table_type);
                            }
                        });
                        //更新事件
                        edit.table_event_create();
                    }
                    switch (edit.table_type) {
                        case 2:
                            $(edit.input_add_name).attr("value", "第" + (res_row + 1) + "课");
                            $(edit.input_add_name).attr("placeholder", "新的课名称");
                            break;
                        case 1:
                            $(edit.input_add_name).attr("value", "第" + (res_row + 1) + "单元");
                            $(edit.input_add_name).attr("placeholder", "新的单元名称");
                            break;
                        default:
                            $(edit.input_add_name).attr("value", "");
                            $(edit.input_add_name).attr("placeholder", "新的单词");
                            $(edit.button_add_auto_name).attr("data-toggle", "tooltip");
                            $(edit.button_add_auto_name).attr("title", "自动添加单词的相关信息");
                            $(edit.button_add_auto_name).tooltip();
                            break;
                    }
                    //更新顶部层级
                    edit.table_tip(data["status"]["level"]);
                }
            }});
        //单词类表更新按钮事件
        if (edit.table_type !== "word") {
            $(edit.button_add_auto_name).tooltip("destroy");
            $(edit.button_add_auto_name).removeAttr("data-toggle");
            $(edit.button_add_auto_name).removeAttr("title");
        }
    }
};
//查看操作
edit.view = function() {
    if (edit.table_type > 0) {
        val_arr = edit.get_data_value(edit.edit_data_key);
        edit.list_parent = val_arr["id"];
        edit.table_type = val_arr["post_type"];
        for (var i = 1; i < edit.table_type_arr.length; i++) {
            if (edit.table_type_arr[i] == val_arr["post_type"]) {
                edit.table_type = i - 1;
            }
        }
        edit.get_list();
    } else {
        $("#table-view-word").modal("show");
    }
}
//添加操作
edit.add = function(b) {
    var val = $(edit.input_add_name).val();
    if (val) {
        message(3,"稍等","正在添加单词，请稍后……");
        edit.ajax_simple("add", {
            "title": val,
            "parent": edit.list_parent,
            "type": edit.table_type,
            "manually": b
        });
        if(edit.table_type === 0){
            $(edit.input_add_name).val("");
        }
    }
}
//编辑操作
edit.edit = function() {
    var val = $(edit.input_edit_name).val();
    val_arr = edit.get_data_value(edit.edit_data_key);
    if (val && val_arr) {
        message(3,"稍等","正在修改单词，请稍后……");
        edit.ajax_simple("edit", {
            "type": 0,
            "id": val_arr["id"],
            "title": val
        });
    } else {
        message(3, "信息错误！", "请填写名称！");
    }
}
//编辑单词信息操作
edit.edit_word = function(t) {
    val_arr = edit.get_data_value(edit.edit_data_key);
    if (t === 0) {
        var infos = {
            "word": $("#table-edit-info-word").val(),
            "img": $("#table-edit-info-img").val(),
            "pho": $("#table-edit-info-pho").val(),
            "voice": $("#table-edit-info-voice").val()
        }
        if (infos["word"] && infos["pho"]) {
            infos["note"] = {};
            infos["note"][0] = {"en":$("#table-edit-info-note-en").val(),"zh":$("#table-edit-info-note-zh").val()};
            infos["des"] = {};
            infos["dict"] = {};
            //解释
            t = 0;
            $("#table-edit-info-des > div").each(function(i, v) {
                input_val = {};
                input_val["p"] = $(v).children("input").first().val();
                input_val["d"] = $(v).children("input").last().val();
                if (input_val["p"] && input_val["d"]) {
                    infos["des"][t] = {"p": input_val["p"], "d": input_val["d"]};
                    t++;
                }
            });
            if (!infos["des"][0]) {
                infos["des"][0] = {};
                infos["des"][0]["p"] = "";
                infos["des"][0]["d"] = "";
            }
            //例句
            t = 0;
            $("#table-edit-info-dict > div").each(function(i, v) {
                input_val = {};
                input_val["en"] = $(v).children("input").first().val();
                input_val["zh"] = $(v).children("input").last().val();
                if (input_val["en"] && input_val["zh"]) {
                    infos["dict"][t] = {"en": input_val["en"], "zh": input_val["zh"]};
                    t++;
                }
            });
            if (!infos["dict"][0]) {
                infos["dict"][0] = {};
                infos["dict"][0]["en"] = "";
                infos["dict"][0]["zh"] = "";
            }
            //发送URL
            edit.ajax_simple("edit", {
                "type": 2,
                "word": val_arr["post_title"],
                "infos": infos
            });
        } else {
            message(2, "信息不完整！", "请输入单词的名称和音标！");
        }
    } else if (t === 1) {
        if (val_arr) {
            edit.ajax_simple("edit", {
                "type": 1,
                "word": val_arr["post_title"]
            });
        }
    }
}
//移动位置操作
edit.move = function(src_id, dest_id) {
    edit.ajax_simple("move", {
        "src": src_id,
        "dest": dest_id
    });
}
//设定课堂操作
edit.set_mission = function() {
    val_arr = edit.get_data_value(edit.edit_data_key);
    if (val_arr) {
        message(3,"稍等","正在设定课堂，请稍后……");
        edit.ajax_simple("set-mission", {
            "id": val_arr["id"]
        });
    }
}
//删除操作
edit.del = function() {
    if (edit.edit_data_key) {
        val_arr = edit.get_data_value(edit.edit_data_key);
        if (val_arr) {
            message(3,"稍等","正在删除单词，请稍后……");
            edit.ajax_simple("del", {
                "id": val_arr["id"]
            });
        }
    }
}

//表格操作
//顶部提示
edit.table_tip = function(levels) {
    $(edit.tip_name).html($(edit.tip_name).data("html"));
    if (levels) {
        for (var i = levels.length; i > 0; i--) {
            if (i <= 1) {
                $(edit.tip_name).append('<li class="active"> <span class="divider">/</span> ' + levels[i - 1]["title"] + '</li>');
            } else {
                $(edit.tip_name).append('<li> <span class="divider">/</span> <a href="#tip-level" data-key="' + (i - 1) + '">' + levels[i - 1]["title"] + '</a></li>');
            }
        }
        //添加顶部层级事件
        $(edit.tip_a_name).click(function() {
            edit.edit_data_key = $(this).attr("data-key");
            val_arr = $(edit.table_name).data("data")["status"]["level"][edit.edit_data_key];
            edit.list_parent = val_arr["id"];
            edit.table_type = val_arr["type"] - 1;
            edit.get_list();
        });
    }
}
//添加一行
edit.table_add = function(key, title, type) {
    var button_html = '';
    if (type !== 0) {
        button_html += '<a class="btn" href="#table-set-mission"><i class="icon-zoom-in"></i> 设定为课堂</a>';
        button_html += '<a class="btn" href="#table-view"><i class="icon-zoom-in"></i> 查看</a>';
    } else {
        button_html += '<a class="btn" href="#table-view-word" data-toggle="modal"><i class="icon-zoom-in"></i> 查看</a>';
    }
    button_html += '<a class="btn" href="#table-button-up"><i class="icon-chevron-up"></i></a>';
    button_html += '<a class="btn" href="#table-button-down"><i class="icon-chevron-down"></i></a>';
    if (type !== 0) {
        button_html += '<a class="btn" href="#edit-unit" data-toggle="modal"><i class="icon-edit"></i> 修改</a>';
    } else {
        button_html += '<a class="btn" href="#table-edit-word" data-toggle="modal"><i class="icon-edit"></i> 修改</a>';
    }
    button_html += '<a class="btn btn-danger" href="#del-id" data-toggle="modal"><i class="icon-trash icon-white"></i> 删除</a>';
    $(edit.table_name).children("tbody").append('<tr data-key="' + key + '"><td>' + title + '</td><td><div class="btn-group hide">' + button_html + '</div></td>' + '</tr>');
}
//为所有行添加事件
edit.table_event_create = function() {
    //移动激活事件
    $(edit.table_name).children("tbody").children("tr").hover(function() {
        $(this).children("td:last").children("div").show();
        edit.edit_data_key = $(this).attr("data-key");
    }, function() {
        $(this).children("td:last").children("div").hide();
    });
    //单击tr事件
    $(edit.table_name).children("tbody").children("tr").click(function() {
        val_arr = edit.get_data_value(edit.edit_data_key);
        //更新删除框架
        $(edit.b_del_name).html(val_arr["post_title"]);
        //更新编辑框架
        $(edit.input_edit_name).attr("value", val_arr["post_title"]);
        //更新查看和编辑单词框架
        if (edit.table_type == 0) {
            if (val_arr["word"]) {
                //查看
                $("#table-view-info-word").html(val_arr["word"]["word"]);
                if(val_arr["word"]["note"]){
                    $("#table-view-info-note-en").html(val_arr["word"]["note"][0]['en']);
                    $("#table-view-info-note-zh").html(val_arr["word"]["note"][0]['zh']);
                }else{
                    $("#table-view-info-note-en").html("");
                    $("#table-view-info-note-zh").html("");
                }
                $("#table-view-info-pho").html(val_arr["word"]["pho"]);
                $("#table-view-info-voice").html(val_arr["word"]["voice"]);
                $("#table-view-info-des").html("");
                if(val_arr["word"]["des"]){
                    for (var i = 0; i < val_arr["word"]["des"].length; i++) {
                        $("#table-view-info-des").append("<p>" + val_arr["word"]["des"][i]["p"] + "&nbsp;&nbsp;" + val_arr["word"]["des"][i]["d"] + "</p>");
                    }
                }
                $("#table-view-info-dict").html("");
                if(val_arr["word"]["dict"]){
                    for (var i = 0; i < val_arr["word"]["dict"].length; i++) {
                        $("#table-view-info-dict").append("<p>" + val_arr["word"]["dict"][i]["en"] + "</p>" + "<p>&nbsp;&nbsp;" + val_arr["word"]["dict"][i]["zh"] + "</p>");
                    }
                }
                if (val_arr["word"]["img"]) {
                    $("#table-view-info-img").html('<img src="do_img.php?word=' + val_arr["word"]["word"] + '" class="img-polaroid">');
                } else {
                    $("#table-view-info-img").html("没有图片...");
                }
                //编辑
                $("#table-edit-info-word").val(val_arr["word"]["word"]);
                if(val_arr["word"]["note"]){
                    $("#table-edit-info-note-en").val(val_arr["word"]["note"][0]['en']);
                    $("#table-edit-info-note-zh").val(val_arr["word"]["note"][0]['zh']);
                }else{
                    $("#table-edit-info-note-en").html("");
                    $("#table-edit-info-note-zh").html("");
                }
                $("#table-edit-info-pho").val(val_arr["word"]["pho"]);
                $("#table-edit-info-voice").val(val_arr["word"]["voice"]);
                $("#table-edit-info-pho ~ div").html(val_arr["word"]["pho"]);
                $("#table-edit-info-des").html("");
                if(val_arr["word"]["des"]){
                    for (var i = 0; i < val_arr["word"]["des"].length; i++) {
                        edit.word_edit_add.des(val_arr["word"]["des"][i]["p"], val_arr["word"]["des"][i]["d"]);
                    }
                }
                $("#table-edit-info-dict").html("");
                if(val_arr["word"]["dict"]){
                    for (var i = 0; i < val_arr["word"]["dict"].length; i++) {
                        edit.word_edit_add.dict(val_arr["word"]["dict"][i]["en"], val_arr["word"]["dict"][i]["zh"]);
                    }
                }
                $("#table-edit-info-img").val(val_arr["word"]["img"]);
            } else {
                $("#table-view-info-word").html(val_arr["post_title"]);
                $("#table-view-info-pho").html("&nbsp;");
                $("#table-view-info-note-en").html("&nbsp;");
                $("#table-view-info-note-zh").html("&nbsp;");
                $("#table-view-info-des").html("&nbsp;");
                $("#table-view-info-dict").html("&nbsp;");
                $("#table-view-info-voice").html("&nbsp;");
                $("#table-view-info-img").html("&nbsp;");
                $("#table-edit-info-word").val(val_arr["post_title"]);
                $("#table-edit-info-note-en").html("");
                $("#table-edit-info-note-zh").html("");
                $("#table-edit-info-pho").val("");
                $("#table-edit-info-pho ~ div").html("");
                $("#table-edit-info-des").html("");
                $("#table-edit-info-dict").html("");
                $("#table-edit-info-voice").val("");
                $("#table-edit-info-img").html("");
            }
        }
    });
    //单击进入事件
    $(edit.table_name + " > tbody > tr > td:even").click(function() {
        edit.view();
    });
    //上下移动事件
    $(edit.button_up_name).click(function() {
        val_src_arr = edit.get_data_value(edit.edit_data_key);
        val_dest_arr = edit.get_data_value(Math.abs(edit.edit_data_key) - 1);
        if (val_src_arr && val_dest_arr) {
            edit.move(val_src_arr["id"], val_dest_arr["id"]);
        }
    });
    $(edit.button_down_name).click(function() {
        val_src_arr = edit.get_data_value(edit.edit_data_key);
        val_dest_arr = edit.get_data_value(Math.abs(edit.edit_data_key) + 1);
        if (val_src_arr && val_dest_arr) {
            edit.move(val_src_arr["id"], val_dest_arr["id"]);
        }
    });
    //修正最初上移动和最后下移动为禁用
    $(edit.button_up_name + ":first").attr("class", $(edit.button_up_name + ":first").attr("class") + " disabled");
    $(edit.button_up_name + ":first").attr("disabled", "disabled");
    $(edit.button_down_name + ":last").attr("class", $(edit.button_up_name + ":first").attr("class") + " disabled");
    $(edit.button_down_name + ":last").attr("disabled", "disabled");
    //查看事件
    $(edit.button_view_name).click(function() {
        edit.view();
    });
    //设定课堂事件
    $(edit.button_set_mission_name).click(function() {
        edit.set_mission();
    });
}

//编辑框添加封装
edit.word_edit_add = new Object();
//添加解释
edit.word_edit_add.des = function(p, d) {
    $("#table-edit-info-des").append('<p><div class="input-append"><input class="span1" type="text" id="table-edit-info-des" value="' + p + '"><input class="span2" type="text" id="table-edit-info-des" value="' + d + '"><a class="btn" href="#table-edit-info-del"><i class="icon-remove"></i></a></div></p>');
    edit.word_edit_add.event();
}
//添加
edit.word_edit_add.dict = function(en, zh) {
    $("#table-edit-info-dict").append('<p><div class="input-append"><input class="span3" type="text" id="table-edit-info-dict" value="' + en + '"><input class="span3" type="text" id="table-edit-info-dict" value="' + zh + '"><a class="btn" href="#table-edit-info-del"><i class="icon-remove"></i></a></div></p>');
    edit.word_edit_add.event();
}
//添加语音
edit.word_edit_add.voice = function(url) {
    $("#table-edit-info-voice").append('<p><div class="input-append"><input class="span5" type="text" id="table-edit-info-voice" value="' + url + '"><a class="btn" href="#table-edit-info-del"><i class="icon-remove"></i></a></div></p>');
    edit.word_edit_add.event();
}
//删除条目事件
edit.word_edit_add.event = function() {
    //删除条目事件
    $("a[href='#table-edit-info-del']").click(function() {
        $(this).parent().remove();
    });
}

//清空
edit.table_clear = function() {
    $(edit.table_name).children("tbody").html("");
}

//清空复习数据
edit.review_clear = function() {
    edit.ajax_simple("clear-review", {});
}
//系统设置保存
edit.system_save = function() {
    //判断参数是否满足条件
    var data = new Array();
    data["status"] = 2;
    data["error"] = "";
    var sys_a_val = $("#system-operate-config-a").val();
    if (sys_a_val == "" || Math.abs(sys_a_val) < 60) {
        data["error"] = "用户登录时限不能太低，最低为60秒。";
        edit.message(data);
    }
    //提交数据
    edit.ajax_simple("sys", {
        "a": sys_a_val
    });
}

//返回指定键值的数据
edit.get_data_value = function(key) {
    if ($(edit.table_name).data("data")) {
        return $(edit.table_name).data("data")["status"]["res"][key];
    }
}
//提交信息操作
edit.ajax_simple = function(url, post) {
    if (edit.ajax_on == true) {
        edit.ajax_on = false;
        $.ajax(edit.ajax_url + url, {
            "type": "POST",
            "data": post,
            "dataType": "json",
            "complete": function(a, b) {
                edit.ajax_on = true;
                edit.get_list();
            },
            "success": function(data,t) {
                edit.message(data);
            }});
    } else {
        data = {"status": "2", "error": "网络阻塞，请等待之前的请求结束后再试。"};
        edit.message(data);
    }
}
//特殊提交数据-单词提示列表
edit.ajax_word_like_list = function(word){
    if (edit.ajax_on == true) {
        edit.ajax_on = false;
        $.ajax("do_word_list.php?word=" + word,{
        "type": "GET",
        "dataType": "json",
        "complete": function(a, b) {
            edit.ajax_on = true;
        },
        "success":function(data,t){
            if(data["status"]){
                for(i=0;i<data["status"].length;i++){
                    add_word = data["status"][i];
                    for(j=0;j<edit.tip_word_arr.length;j++){
                        if(edit.tip_word_arr[j] == data["status"][i]){
                            add_word = "";
                        }
                    }
                    if(add_word){
                        edit.tip_word_arr.push(add_word);
                    }
                }
                if(edit.tip_word_arr.length > 100){
                    edit.tip_word_arr.shift();
                }
                $(edit.input_add_name).typeahead({
                    "source":edit.tip_word_arr
                });
            }
        }});
    }
}
//呼叫消息框
edit.message = function(data) {
    var title = "";
    var status = 0;
    switch (data["status"]) {
        case "1":
            //处理成功
            title = "成功！";
            status = 0;
            break;
        case "2":
            //用户提交信息错误
            title = "信息有误！";
            status = 2;
            break;
        default:
            //处理失败
            title = "失败！";
            status = 1;
            break;
    }
    message(status, title, data["error"]);
}

//备份
edit.backup = new Object();
//刷新备份列表
edit.backup.list = function(lock){
    if (edit.ajax_on == true) {
        if(lock == true){
            edit.ajax_on = false;
        }
        $("#system-operate-backup-list").html("");
        $.ajax(edit.ajax_url + "backup-list",{
        "type": "GET",
        "dataType": "json",
        "complete": function(a, b) {
            edit.ajax_on = true;
        },
        "success":function(data,t){
            if(data["status"]){
                for(i=0;i<data["status"].length;i++){
                    $("#system-operate-backup-list").append('<option data-key="'+i+'">'+data["status"][i]+'</option>');
                }
            }
        }});
    }
}
//备份操作
edit.backup.backup = function() {
    message(2, "备份提示", "备份数据需要很多时间，在此期间请耐心等待...");
    edit.ajax_simple("backup", {});
    edit.backup.list(true);
}
//还原操作
edit.backup.re = function() {
    message(2, "还原中", "还原数据需要非常长的时间，且还原后可能需要重新登陆，请耐心等待...");
    var selectObj = $("#system-operate-backup-list");
    if (selectObj) {
        edit.ajax_simple("backup", {
            "return": $("#system-operate-backup-list > option:contains('"+selectObj.val()+"')").attr("data-key"),
            "file": selectObj.val()
        });
    }
}

//开始加载执行
$(document).ready(function() {
    //消息框全局变量
    $(document).data("message_html", $(message_object_name).html());
    $(document).data("message_class", $(message_object_name).attr("class"));
    /**
     * 系统设置部分
     */
    //系统设置保存按钮
    $("a[href='#system-operate-save']").click(function() {
        $(this).attr("class", "btn btn-primary disabled");
        $(this).attr("disabled", "disabled");
        edit.system_save();
    });
    //清空复习数据按钮
    $("a[href='#system-operate-review-clear']").click(function() {
        edit.review_clear();
    });
    /**
     * 单词框架初始化
     */
    //添加信息
    $("a[href='#table-edit-des-add']").click(function() {
        edit.word_edit_add.des("", "");
    });
    $("a[href='#table-edit-dict-add']").click(function() {
        edit.word_edit_add.dict("", "");
    });
    $("a[href='#table-edit-voice-add']").click(function() {
        edit.word_edit_add.voice("");
    });
    //编辑单词
    $(edit.button_edit_word_name).click(function() {
        edit.edit_word(0);
    });
    //重新从URL获取信息
    $(edit.button_edit_word_info_name).click(function() {
        edit.edit_word(1);
    });
    /**
     * 内容初始化
     */
    $(edit.tip_name).data("html", $(edit.tip_name).html());
    //添加事件
    $(edit.button_add_auto_name).click(function() {
        edit.add(0);
    });
    $(edit.input_add_name).keydown(function(event) {
        if(event.keyCode == 13){
            edit.add(0);
        }
    });
    //编辑事件
    $(edit.button_edit_name).click(function() {
        edit.edit();
    });
    //删除事件
    $(edit.button_del_name).click(function() {
        edit.del();
    });
    /**
     * 特殊事件
     */
    //添加焦点全选
    $(edit.input_add_name).hover(function() {
        $(this).select();
    });
    //上传文件框架
    $(document).data("upload-type","img");
    $("#button-upload-img").click(function() {
        $(document).data("upload-type","img");
        $("#file_upload").uploadify("settings", "uploader", "../../do_upload.php?type=img&word="+edit.get_data_value(edit.edit_data_key)["post_title"]);
    });
    $("#button-upload-voice").click(function() {
        $(document).data("upload-type","voice");
        $("#file_upload").uploadify("settings", "uploader", "../../do_upload.php?type=voice&word="+edit.get_data_value(edit.edit_data_key)["post_title"]);
    });
    $("#file_upload").uploadify({
        "formData": {
            "timestamp": Math.random(),
            "token": Math.random()
        },
        "swf": "includes/swf/uploadify.swf",
        "uploader": "../../do_upload.php?type=img&word=",
        "buttonText": "选择文件"
    });
    $("#table-edit-word").on("shown", function() {
        //上传框架URL更新
        if (edit.table_type === 0) {
            upload_url = "../../do_upload.php?type=" + $(document).data("upload-type") + "&word=" + edit.get_data_value(edit.edit_data_key)["word"]["word"];
            $("#file_upload").uploadify("settings", "uploader", upload_url);
        }
    });
    //添加单词提示事件
    //$(edit.input_add_name).typeahead();
    $(edit.input_add_name).keyup(function() {
        clearTimeout(edit.tip_word_time);
        if (edit.table_type === 0 && $(edit.input_add_name).val()) {
            edit.tip_word_time = setTimeout("edit.ajax_word_like_list($(edit.input_add_name).val());",500);
        }else{
            /*
            $(edit.input_add_name).typeahead({
                "source":[]
            });*/
        }
    });
    //备份和还原按钮事件
    $('a[href="#system-operate-backup"]').click(function(){
        edit.backup.backup();
    });
    $('a[href="#system-operate-backup-return"]').click(function(){
        edit.backup.re();
    });
    //获取备份列表
    edit.backup.list(false);
    //获取列表
    edit.get_list();
});