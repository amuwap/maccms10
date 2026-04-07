<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:52:"/workspace/application/install/view/index/step3.html";i:1775560449;s:51:"/workspace/application/install/view/index/head.html";i:1775560112;s:51:"/workspace/application/install/view/index/foot.html";i:1775560112;}*/ ?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>苹果CMS-V10系统安装</title>
        <link rel="stylesheet" href="/static/layui/css/layui.css">
        <link rel="stylesheet" href="/static/css/admin_style.css">
        <link rel="stylesheet" href="/static/css/install.css">
        <script type="text/javascript" src="/static/layui/layui.js"></script>
        <script>
            var ROOT_PATH = "", ADMIN_PATH="<?php echo $_SERVER['SCRIPT_NAME']; ?>";
        </script>
    </head>
<body>
<div class="header">
    <h1>感谢您选择苹果CMS-V10系统建站</h1>
</div>
<style type="text/css">
.layui-table td, .layui-table th{text-align:left;}
.layui-table tbody tr.no{background-color:#f00;color:#fff;}
</style>
<div class="install-box">
    <fieldset class="layui-elem-field layui-field-title">
        <legend>数据库配置</legend>
    </fieldset>
    <form class="layui-form layui-form-pane" action="?step=4" method="post">
        <div class="layui-form-item">
            <div class="layui-form-mid">
                <p>系统将使用SQLite数据库，无需手动配置数据库连接信息。</p>
                <p>数据库文件将存储在：application/data/maccms10.db</p>
            </div>
        </div>
        <div class="layui-form-item">
            <button type="submit" class="layui-btn fl" style="margin-left:120px;" lay-submit="" lay-filter="formTest">配置数据库</button>
            <div class="layui-form-mid layui-word-aux">点击配置数据库</div>
        </div>
    </form>
    <form class="layui-form layui-form-pane" action="?step=5" method="post">
        <input type="hidden" name="install_dir" value="<?php echo $install_dir; ?>">
        <fieldset class="layui-elem-field layui-field-title">
            <legend>管理账号设置</legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label">管理员账号</label>
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" name="account" lay-verify="title" value="admin">
            </div>
            <div class="layui-form-mid layui-word-aux">管理员账号最少4位</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">管理员密码</label>
            <div class="layui-input-inline w200">
                <input type="password" class="layui-input" name="password" lay-verify="title" value="admin123">
            </div>
            <div class="layui-form-mid layui-word-aux">保证密码最少6位</div>
        </div>
        <div class="step-btns">
            <a href="?step=2" class="layui-btn layui-btn-primary layui-btn-big fl">返回上一步</a>
            <button type="submit" class="layui-btn layui-btn-big layui-btn-normal fr" lay-submit="" lay-filter="formSubmit" >立即执行安装</button>
        </div>
    </form>
</div>
<span style="display: none">
<iframe src="//www.maccms.com/tongji.html?v10-php" MARGINWIDTH="0" MARGINHEIGHT="0" HSPACE="0" VSPACE="0" FRAMEBORDER="0" SCROLLING="no" width="0" height="0"></iframe>
</span>
<div class="copyright">
    © 2008-2018 <a href="http://www.maccms.com/?v10" target="_blank">MacCMS.COM</a> All Rights Reserved.
</div>
</body>
</html>
<script type="text/javascript">
    var test=0;
layui.define(['element', 'form'], function(exports) {
    var $ = layui.jquery, layer = layui.layer, form = layui.form;
    form.on('submit(formTest)', function(data) {
        var _form = '';
        if ($(this).attr('data-form')) {
            _form = $($(this).attr('data-form'));
        } else {
            _form = $(this).parents('form');
        }
        
        layer.msg('数据提交中...',{time:500000});
        $.ajax({
            type: "POST",
            url: _form.attr('action'),
            data: _form.serialize(),
            dataType:'json',
            success: function(res) {
                if(res.code==1){
                    test=1;
                }
                layer.msg(res.msg);
            }
        });
        return false;
    });
    form.on('submit(formSubmit)', function(data) {
        if(test==0){
            layer.msg('请先点击配置数据库！');
            return false;
        }

    });



});
</script>