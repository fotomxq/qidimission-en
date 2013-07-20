<?php
/**
 * 编辑模式
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
require_once('glob_logged.php');
?>
<div class="container">
    <!-- 消息 -->
    <div class="row hide">
        <div class="span12">
            <div class="alert alert-block" id="message">
                <button type="button" class="close">&times;</button>
                <h4></h4>
            </div>
        </div>
    </div>
    <!-- 消息 结束 -->
    
    <!-- 单元提示 -->
    <div class="row">
        <div class="span12 text-left">
            <ul class="breadcrumb" id="data-tip">
                <li><i class="icon-home"></i> <a href="init.php?mode=edit">首页</a></li>
            </ul>
        </div>
    </div>
    <!-- 单元提示 结束 -->
    
    <!-- 表格 -->
    <div class="row">
        <div class="span12">
            <table class="table table-hover" id="data-table">
                <thead>
                    <tr>
                        <th>名称</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th><input id="add-input" type="text" placeholder=""></th>
                        <th>
                            <div class="btn-group">
                                <a class="btn btn-primary" href="#add-title-auto" data-provide="typeahead"><i class="icon-plus-sign icon-white"></i> 添加</a>
                            </div>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- 表格 结束 -->
</div>

<!-- 编辑课、单元框架 -->
<div id="edit-unit" class="modal fade hide" tabindex="-1" role="dialog" aria-labelledby="edit-unit-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="edit-unit-label">修改名称</h3>
    </div>
    <div class="modal-body">
        <dl class="dl-horizontal">
            <dt>新的名称</dt>
            <dd>
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-pencil"></i></span>
                    <input class="span2" id="edit-unit-input" type="text" value="">
                </div>
            </dd>
        </dl>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
        <a class="btn btn-primary" href="#edit-unit-save" data-dismiss="modal" aria-hidden="true">保存</a>
    </div>
</div>
<!-- 编辑课、单元框架 结束 -->

<!-- 编辑单词 -->
<div id="table-edit-word" class="modal fade hide" tabindex="-1" role="dialog" aria-labelledby="table-edit-word-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="table-edit-word-label">编辑单词信息</h3>
    </div>
    <div class="modal-body">
        <dl class="dl-horizontal">
            <dt>单词</dt>
            <dd><input type="text" id="table-edit-info-word" value=""></dd>
            <dt>上传</dt>
            <dd>
                <div class="row">
                    <div class="span6">支持上传文件格式有：jpg、jpeg、gif、png、mp3</div>
                </div>
                <div class="row">
                    <div class="span2">
                        <div class="btn-group" data-toggle="buttons-radio">
                            <button id="button-upload-img" type="button" class="btn btn-info active">图片</button>
                            <button id="button-upload-voice" type="button" class="btn btn-info">发音</button>
                        </div>
                    </div>
                    <div class="span3">
                        <form>
                            <div id="queue"></div>
                            <input id="file_upload" name="file_upload" type="file" multiple="true">
                        </form>
                    </div>
                </div>
            </dd>
            <dt>注记英</dt>
            <dd><input type="text" id="table-edit-info-note-en" class="span3" value=""></dd>
            <dt>注记中</dt>
            <dd><input type="text" id="table-edit-info-note-zh" class="span3" value=""></dd>
            <dt>图片</dt>
            <dd><input type="text" id="table-edit-info-img" class="span5" value=""></dd>
            <dt>发音</dt>
            <dd><input type="text" id="table-edit-info-voice" class="span5" value=""></dd>
            <dt>音标</dt>
            <dd><input type="text" id="table-edit-info-pho" value=""><div></div></dd>
            <dt>解释</dt>
            <dd id="table-edit-info-des"></dd>
            <dt>例句</dt>
            <dd id="table-edit-info-dict"></dd>
        </dl>
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">添加 <span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="#table-edit-des-add">添加解释</a></li>
                <li><a href="#table-edit-dict-add">添加例句</a></li>
            </ul>
        </div>
        <a class="btn btn-info" href="#edit-word-url" data-dismiss="modal" aria-hidden="true" title="不会删除已存在的单词信息">从互联网获取单词信息</a>
        <a class="btn btn-primary" href="#edit-word-save" data-dismiss="modal" aria-hidden="true">保存</a>
        <button class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
    </div>
</div>
<!-- 编辑单词 结束 -->

<!-- 查看单词 -->
<div id="table-view-word" class="modal fade hide" tabindex="-1" role="dialog" aria-labelledby="table-view-word-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="table-view-word-label">查看单词</h3>
    </div>
    <div class="modal-body">
        <dl class="dl-horizontal">
            <dt>单词</dt>
            <dd id="table-view-info-word">&nbsp;</dd>
            <dt>注记（英文）</dt>
            <dd id="table-view-info-note-en">&nbsp;</dd>
            <dt>注记（中文）</dt>
            <dd id="table-view-info-note-zh">&nbsp;</dd>
            <dt>图片</dt>
            <dd id="table-view-info-img">&nbsp;</dd>
            <dt>发音</dt>
            <dd id="table-view-info-voice">&nbsp;</dd>
            <dt>音标</dt>
            <dd id="table-view-info-pho">&nbsp;</dd>
            <dt>解释</dt>
            <dd id="table-view-info-des">&nbsp;</dd>
            <dt>例句</dt>
            <dd id="table-view-info-dict">&nbsp;</dd>
        </dl>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
    </div>
</div>
<!-- 查看单词 结束 -->

<!-- 删除提示框 -->
<div id="del-id" class="modal fade hide" tabindex="-1" role="dialog" aria-labelledby="del-id-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="del-id-label">删除确定</h3>
    </div>
    <div class="modal-body">
        <p>您确定要删除“<b id="del-id-name"></b>”么？</p>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
        <a class="btn btn-danger" href="#del-id-ok" data-dismiss="modal" aria-hidden="true">删除</a>
    </div>
</div>
<!-- 删除提示框 结束 -->

<!-- 高级设置 -->
<div id="system-operate" class="modal fade hide" tabindex="-1" role="dialog" aria-labelledby="system-operate-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="system-operate-label">系统高级设置</h3>
    </div>
    <div class="modal-body">
        <dl class="dl-horizontal">
            <dt>用户登录失效时长(秒)</dt>
            <dd>
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-time"></i></span>
                    <input class="span2" id="system-operate-config-a" type="text" value="<?php echo $sysconfigs->load('USER_TIMEOUT'); ?>">
                </div>
            </dd>
            <hr>
            <dt>高级选项</dt>
            <dd>
                <div class="btn-group">
                    <a class="btn btn-warning" href="#system-operate-review-clear" data-dismiss="modal" aria-hidden="true">清空复习数据</a>
                </div>
                <p>&nbsp;</p>
            </dd>
            <hr>
            <dt>备份和还原</dt>
            <dd>
                <div class="input-group">
                    <a class="btn btn-primary" href="#system-operate-backup" data-dismiss="modal" aria-hidden="true"><i class="icon-retweet icon-white"></i> 备份数据库</a>
                </div>
                <p>&nbsp;</p>
                <div class="input-append">
                    <select id="system-operate-backup-list" class="input-medium"></select>
                    <a class="btn btn-warning" href="#system-operate-backup-return" data-dismiss="modal" aria-hidden="true"><i class="icon-share-alt icon-white"></i> 还原</a>
                </div>
            </dd>
        </dl>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">关闭</button>
        <a class="btn btn-primary" href="#system-operate-save" data-dismiss="modal" aria-hidden="true">保存</a>
    </div>
</div>
<!-- 高级设置 结束 -->

<div id="push"></div>