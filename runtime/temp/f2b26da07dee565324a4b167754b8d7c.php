<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:47:"/workspace/application/admin/view/art/info.html";i:1775560112;s:50:"/workspace/application/admin/view/public/head.html";i:1775560112;s:52:"/workspace/application/admin/view/public/editor.html";i:1775560112;s:50:"/workspace/application/admin/view/public/foot.html";i:1775560112;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $title; ?> - 苹果CMS内容管理系统</title>
    <link rel="stylesheet" href="/static/layui/css/layui.css">
    <link rel="stylesheet" href="/static/css/admin_style.css">
    <script type="text/javascript" src="/static/js/jquery.js"></script>
    <script type="text/javascript" src="/static/layui/layui.js"></script>
    <script>
        var ROOT_PATH="",ADMIN_PATH="<?php echo $_SERVER['SCRIPT_NAME']; ?>", MAC_VERSION='v10';
    </script>
</head>
<body>
<script type="text/javascript" src="/static/js/jquery.jscolor.js"></script>
<?php 
$editor=$GLOBALS['config']['app']['editor'];
$ue_old= ROOT_PATH . 'static/ueditor/' ;
$ue_new= ROOT_PATH . 'static/editor/'. $editor ;
if( (!file_exists($ue_new) && file_exists($ue_old)) || $editor=='' ){
    $editor = 'ueditor';
}
 if($editor == 'ueditor'): ?>
<script type="text/javascript" src="/static/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/static/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript">
    window.UEDITOR_CONFIG.serverUrl = "<?php echo url('upload/upload'); ?>?from=ueditor&flag=art_editor&input=upfile";
    var EDITOR = UE;
</script>
<?php elseif($editor == 'umeditor'): ?>
<link rel="stylesheet" href="/static/editor/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="/static/editor/umeditor/umeditor.config.js"></script>
<script type="text/javascript" src="/static/editor/umeditor/umeditor.min.js"></script>
<script type="text/javascript">
    window.UMEDITOR_CONFIG.imageUrl = "<?php echo url('upload/upload'); ?>?from=umeditor&flag=art_editor&input=upfile";
    var EDITOR = UM;
</script>
<?php elseif($editor == 'kindeditor'): ?>
<script type="text/javascript" src="/static/editor/kindeditor/kindeditor-all-min.js"></script>
<script type="text/javascript">
    var EDITOR = KindEditor;
</script>
<?php elseif($editor == 'ckeditor'): ?>
<script type="text/javascript" src="/static/editor/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    var EDITOR = CKEDITOR;
</script>
<?php endif; ?>
<script>
    var editor = "<?php echo $editor; ?>";
    function editor_getEditor(obj)
    {
        var res;
        switch(editor){
            case "kindeditor":
                res = KindEditor.create('#'+obj, { uploadJson:"<?php echo url('upload/upload'); ?>?from=kindeditor&flag=art_editor&input=imgFile" , allowFileManager : false });
                break;
            case "ckeditor":
                res = CKEDITOR.replace(obj,{filebrowserImageUploadUrl:"<?php echo url('upload/upload'); ?>?from=ckeditor&flag=art_editor&input=upload"});
                break;
            default:
                res = EDITOR.getEditor(obj);
                break;
        }
        return res;
    }
    function editor_setContent(obj,html) {
        var res;
        switch(editor){
            case "kindeditor":
                res = obj.html(html);
                break;
            case "ckeditor":
                res = obj.setData(html);
                break;
            default:
                res = obj.setContent(html);
                break;
        }
        return res;
    }
    function editor_getContent(obj) {
        var res;
        switch(editor){
            case "kindeditor":
                res = obj.html();
                break;
            case "ckeditor":
                res = obj.getData();
                break;
            default:
                res = obj.getContent();
                break;
        }
        return res;
    }
</script>

<div class="page-container p10">
    <div class="showpic" style="display:none;"><img class="showpic_img" width="120" height="160"></div>
    
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="art_id" value="<?php echo $info['art_id']; ?>">

        <div class="layui-tab">
            <ul class="layui-tab-title ">
                <li class="layui-this">基本信息</a></li>
                <li>其他信息</li>
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">
                    
                <div class="layui-form-item">
                    <label class="layui-form-label">参数：</label>
                    <div class="layui-input-inline w150">
                            <select name="type_id" lay-filter="type_id">
                                <option value="">请选择分类</option>
                                <?php if(is_array($type_tree) || $type_tree instanceof \think\Collection || $type_tree instanceof \think\Paginator): $i = 0; $__LIST__ = $type_tree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if($vo['type_mid'] == 2): ?>
                                    <option value="<?php echo $vo['type_id']; ?>" <?php if($info['type_id'] == $vo['type_id']): ?>selected<?php endif; ?>><?php echo $vo['type_name']; ?></option>
                                    <?php if(is_array($vo['child']) || $vo['child'] instanceof \think\Collection || $vo['child'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ch): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $ch['type_id']; ?>" <?php if($info['type_id'] == $ch['type_id']): ?>selected<?php endif; ?>>&nbsp;|&nbsp;&nbsp;&nbsp;|—<?php echo $ch['type_name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; endif; endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                            <select name="art_level">
                                <option value="0">请选择推荐</option>
                                <option value="9" <?php if($info['art_level'] == 9): ?>selected<?php endif; ?>>推荐9-幻灯</option>
                                <option value="1" <?php if($info['art_level'] == 1): ?>selected<?php endif; ?>>推荐1</option>
                                <option value="2" <?php if($info['art_level'] == 2): ?>selected<?php endif; ?>>推荐2</option>
                                <option value="3" <?php if($info['art_level'] == 3): ?>selected<?php endif; ?>>推荐3</option>
                                <option value="4" <?php if($info['art_level'] == 4): ?>selected<?php endif; ?>>推荐4</option>
                                <option value="5" <?php if($info['art_level'] == 5): ?>selected<?php endif; ?>>推荐5</option>
                                <option value="6" <?php if($info['art_level'] == 6): ?>selected<?php endif; ?>>推荐6</option>
                                <option value="7" <?php if($info['art_level'] == 7): ?>selected<?php endif; ?>>推荐7</option>
                                <option value="8" <?php if($info['art_level'] == 8): ?>selected<?php endif; ?>>推荐8</option>

                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                            <select name="art_status">
                                <option value="1" >已审核</option>
                                <option value="0" <?php if($info['art_status'] == '0'): ?>selected<?php endif; ?>>未审核</option>
                            </select>
                    </div>
                    <div class="layui-input-inline w150">
                        <select name="art_lock">
                            <option value="0">未锁</option>
                            <option value="1" <?php if($info['art_lock'] == 1): ?>selected<?php endif; ?>>锁定</option>
                        </select>
                    </div>

                    <div class="layui-input-inline">
                        <input type="checkbox" name="uptime" title="更新时间" value="1" checked class="layui-checkbox checkbox-ids" lay-skin="primary">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">标题：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo $info['art_name']; ?>" placeholder="请输入" name="art_name">
                    </div>
                    <label class="layui-form-label">副标：</label>
                    <div class="layui-input-inline ">
                        <input type="text" class="layui-input" value="<?php echo $info['art_sub']; ?>" placeholder="请输入副标题" name="art_sub">
                    </div>

                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">拼音：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo $info['art_en']; ?>" placeholder="" name="art_en">
                    </div>
                    <label class="layui-form-label">首字母：</label>
                    <div class="layui-input-inline w70">
                        <input type="text" class="layui-input" value="<?php echo $info['art_letter']; ?>" placeholder="" name="art_letter">
                    </div>
                    <label class="layui-form-label">高亮：</label>
                    <div class="layui-input-inline w70">
                        <input type="text" class="layui-input color" value="<?php echo $info['art_color']; ?>" placeholder="" name="art_color">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">TAG：</label>
                    <div class="layui-input-inline w500">
                        <input type="text" class="layui-input" value="<?php echo $info['art_tag']; ?>" placeholder="" name="art_tag">
                    </div>
                    <div class="layui-input-inline w120">
                        <input type="checkbox" name="uptag" title="自动生成" value="1" class="layui-checkbox checkbox-ids" lay-skin="primary">
                    </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">备注：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo $info['art_remarks']; ?>" placeholder="" name="art_remarks">
                        </div>
                    </div>


                    <div class="layui-form-item">
                        <label class="layui-form-label">关联视频：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo $info['art_rel_vod']; ?>" placeholder="如“变形金刚”1、2、3部ID分别为11,12,13或将每部都填“变形金刚”" name="art_rel_vod">
                        </div>
                        <div class="layui-input-inline ">
                            <a class="layui-btn j-iframe" data-href="<?php echo url('vod/data'); ?>?select=1&input=art_rel_vod" href="javascript:;" title="查询数据">查询数据</a>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">关联文章：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo $info['art_rel_art']; ?>" placeholder="如“变形金刚资讯”1、2、3部ID分别为11,12,13或将每部都填“变形金刚资讯”" name="art_rel_art">
                        </div>
                        <div class="layui-input-inline ">
                            <a class="layui-btn j-iframe" data-href="<?php echo url('art/data'); ?>?select=1&input=art_rel_art" href="javascript:;" title="查询数据">查询数据</a>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">扩展分类：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo $info['art_class']; ?>" placeholder="" id="art_class" name="art_class">
                        </div>
                        <div class="layui-input-inline w500 art_class_label">

                        </div>
                    </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">图片：</label>
                    <div class="layui-input-inline w500 upload">
                        <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo $info['art_pic']; ?>" placeholder="" id="art_pic" name="art_pic">
                    </div>
                    <div class="layui-input-inline ">
                        <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:1,thumb_class:'upload-thumb'}}" id="upload1">上传图片</button>
                    </div>
                </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">缩略图：</label>
                        <div class="layui-input-inline w500 upload">
                            <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo $info['art_pic_thumb']; ?>" placeholder="" id="art_pic_thumb" name="art_pic_thumb">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'upload-thumb'}}" id="upload2">上传图片</button>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">海报图：</label>
                        <div class="layui-input-inline w500 upload">
                            <input type="text" class="layui-input upload-input" style="max-width:100%;" value="<?php echo $info['art_pic_slide']; ?>" placeholder="" id="art_pic_slide" name="art_pic_slide">
                        </div>
                        <div class="layui-input-inline ">
                            <button type="button" class="layui-btn layui-upload" lay-data="{data:{thumb:0,thumb_class:'upload-thumb'}}" id="upload3">上传图片</button>
                        </div>
                    </div>

                <div class="layui-form-item">
                        <label class="layui-form-label">简介：</label>
                    <div class="layui-input-block">
                        <textarea name="art_blurb" cols="" rows="3" class="layui-textarea"  placeholder="不填写将自动从第一页详情里获取前100个字" style="height:40px;"><?php echo $info['art_blurb']; ?></textarea>
                    </div>
                </div>

                    <script>
                        var ueArray=[];
                        var content_arr_len = <?php echo count($art_page_list); ?>;
                    </script>

                <div id="artlist" class="contents">
                    <?php if(is_array($art_page_list) || $art_page_list instanceof \think\Collection || $art_page_list instanceof \think\Paginator): $i = 0; $__LIST__ = $art_page_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <div class="layui-form-item" data-i="<?php echo $key; ?>">
                        <label class="layui-form-label">分页内容<?php echo $key; ?>：</label>
                        <div class="layui-input-inline w200"><input type="text" name="art_title[]" class="layui-input" value="<?php echo $vo['title']; ?>" placeholder="页标题"></div>
                        <div class="layui-input-inline w200"><input type="text" name="art_note[]" class="layui-input" value="<?php echo $vo['note']; ?>" placeholder="页备注"></div>
                        <div class="layui-input-inline w200"><a href="javascript:void(0)" class="j-editor-clear">清空</a>&nbsp;<a href="javascript:void(0)" class="j-editor-remove">删除</a>&nbsp;<a href="javascript:void(0)" class="j-editor-up">上移</a>&nbsp;<a href="javascript:void(0)" class="j-editor-down">下移</a>&nbsp;<br></div>
                        <div class="p10 m20"></div>
                        <div class="layui-input-block"><textarea id="art_content<?php echo $key; ?>" name="art_content[]" type="text/plain" style="width:99%;height:250px"><?php echo mac_url_content_img($vo['content']); ?></textarea></div>
                        <script>ueArray[<?php echo $key; ?>] = editor_getEditor('art_content<?php echo $key; ?>');</script>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>


                    <div class="layui-form-item">
                        <label class=""><button class="layui-btn radius j-editor-add" type="button">添加一页内容</button></label>
                        <div class="layui-input-block">

                        </div>
                    </div>
                    
        </div>


                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">顶数量：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_up']; ?>" placeholder="" id="art_up" name="art_up">
                            </div>
                            <label class="layui-form-label">踩数量：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_down']; ?>" placeholder="" id="art_down" name="art_down">
                            </div>
                            <button class="layui-btn" type="button" id="btn_rnd">随机生成</button>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">总人气：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_hits']; ?>" placeholder="" id="art_hits" name="art_hits">
                            </div>
                            <label class="layui-form-label">月人气：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_hits_month']; ?>" placeholder="" id="art_hits_month" name="art_hits_month" >
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">周人气：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_hits_week']; ?>" placeholder="" id="art_hits_week" name="art_hits_week">
                            </div>
                            <label class="layui-form-label">日人气：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input " value="<?php echo $info['art_hits_day']; ?>" placeholder="" id="art_hits_day" name="art_hits_day">
                            </div>
                        </div>


                        <div class="layui-form-item">
                            <label class="layui-form-label">平均分：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_score']; ?>" placeholder="" id="art_score" name="art_score">
                            </div>
                            <label class="layui-form-label">总评分：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_score_all']; ?>" placeholder="" id="art_score_all" name="art_score_all">
                            </div>
                            <label class="layui-form-label">总评次：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_score_num']; ?>" placeholder="" id="art_score_num" name="art_score_num">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">整数据积分：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_points']; ?>" placeholder="" name="art_points">
                            </div>
                            <label class="layui-form-label">每页积分：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_points_detail']; ?>" placeholder="" name="art_points_detail">
                            </div>
                            <label class="layui-form-label">独立模板：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_tpl']; ?>" placeholder="" name="art_tpl">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">编辑人：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_author']; ?>" placeholder="" name="art_author">
                            </div>
                            <label class="layui-form-label">来源：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_from']; ?>" placeholder="" name="art_from">
                            </div>
                            <label class="layui-form-label">跳转URL：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_jumpurl']; ?>" placeholder="" name="art_jumpurl">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">访问密码：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_pwd']; ?>" placeholder="非静态模式下可用" name="art_pwd">
                            </div>
                            <label class="layui-form-label">密码链接：</label>
                            <div class="layui-input-inline ">
                                <input type="text" class="layui-input" value="<?php echo $info['art_pwd_url']; ?>" placeholder="" name="art_pwd_url">
                            </div>

                        </div>

                    </div>
            </div>
        </div>

                <div class="layui-form-item center">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="">保 存</button>
                        <button class="layui-btn layui-btn-warm" type="reset">还 原</button>
                    </div>
                </div>
    </form>

</div>
<script type="text/javascript" src="/static/js/admin_common.js"></script>

<script type="text/javascript">

    layui.use(['form','upload', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
                , upload = layui.upload;;

        // 验证
        form.verify({
            art_name: function (value) {
                if (value == "") {
                    return "请输入专题名称";
                }
            }
        });

        $(document).on("click", ".extend", function(){
            $id = $(this).attr('data-id');
            if($id == 'art_class'||$id == 'art_keywords'){
                $val = $("input[id='"+$id+"']").val();
                if($val!=''){
                    $val = $val+',';
                }
                if($val.indexOf($(this).text())>-1){
                    return;
                }
                $("input[id='"+$id+"']").val($val+$(this).text());
            }else{
                $("input[id='"+$id+"']").val($(this).text());
            }
        });


        form.on('select(type_id)', function(data){
            getExtend(data.value);
        });

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo url('upload/upload'); ?>?flag=art"
            ,method: 'post'
            ,before: function(input) {
                layer.msg('文件上传中...', {time:3000000});
            },done: function(res, index, upload) {
                var obj = this.item;
                if (res.code == 0) {
                    layer.msg(res.msg);
                    return false;
                }
                layer.closeAll();
                var input = $(obj).parent().parent().find('.upload-input');
                if ($(obj).attr('lay-type') == 'image') {
                    input.siblings('img').attr('src', res.data.file).show();
                }
                input.val(res.data.file);

                if(res.data.thumb_class !=''){
                    $('.'+ res.data.thumb_class).val(res.data.thumb[0].file);
                }
            }
        });

        $('.upload-input').hover(function (e){
            var e = window.event || e;
            var imgsrc = $(this).val();
            if(imgsrc.trim()==""){ return; }
            var left = e.clientX+document.body.scrollLeft+20;
            var top = e.clientY+document.body.scrollTop+20;
            $(".showpic").css({left:left,top:top,display:""});
            if(imgsrc.indexOf('://')<0){ imgsrc = ROOT_PATH + '/' + imgsrc;	} else{ imgsrc = imgsrc.replace('mac:','http:'); }
            $(".showpic_img").attr("src", imgsrc);
        },function (e){
            $(".showpic").css("display","none");
        });


        $("#btn_rnd").click(function(){
            $("#art_hits").val( rndNum(5000,9999) );
            $("#art_hits_month").val( rndNum(1000,4999) );
            $("#art_hits_week").val( rndNum(300,999) );
            $("#art_hits_day").val( rndNum(1,299) );
            $("#art_up").val( rndNum(1,999) );
            $("#art_down").val( rndNum(1,999) );
            $("#art_score").val( rndNum(10) );
            $("#art_score_all").val( rndNum(1000) );
            $("#art_score_num").val( rndNum(100) );
        });



        $('.contents').on('click','.j-editor-clear',function(){
            var datai = $(this).parent().parent().attr('data-i');
            editor_setContent(ueArray[datai],'');
        });
        $('.contents').on('click','.j-editor-remove',function(){
            var datai = $(this).parent().parent().attr('data-i');
            $(this).parent().parent().remove();
            delete ueArray[datai];
        });
        $('.contents').on('click','.j-editor-up',function(){
            var current = $(this).parent().parent();
            var current_index = current.index();
            var current_i = current.attr('data-i');
            var prev = current.prev();
            var prev_i = prev.attr('data-i');
            if(current_index>0){
                var current_txt = editor_getContent(ueArray[current_i]);
                var prev_txt = editor_getContent(ueArray[prev_i]);
                editor_setContent(ueArray[current_i],prev_txt);
                editor_setContent(ueArray[prev_i],current_txt);
            }
        });
        $('.contents').on('click','.j-editor-down',function(){
            var current = $(this).parent().parent();
            var current_index = current.index();
            var current_i = current.attr('data-i');
            var next = current.next();
            var next_i = next.attr('data-i');

            if(next.length>0){
                var current_txt = editor_getContent(ueArray[current_i]);
                var prev_txt = editor_getContent(ueArray[next_i]);

                editor_setContent(ueArray[current_i],prev_txt);
                editor_setContent(ueArray[next_i],current_txt);
            }
        });

        $('.j-editor-add').on('click',function(){
            content_arr_len++;
            var tpl='<div class="layui-form-item" data-i="'+content_arr_len+'"><label class="layui-form-label">分页内容'+(content_arr_len)+'：</label><div class="layui-input-inline w200"><input type="text" name="art_title[]" class="layui-input" placeholder="页标题"></div><div class="layui-input-inline w200"><input type="text" name="art_note[]" class="layui-input" placeholder="页备注"></div><div class="layui-input-inline w200 p10"><a href="javascript:void(0)" class="j-editor-clear">清空</a>&nbsp;<a href="javascript:void(0)" class="j-editor-remove">删除</a>&nbsp;<a href="javascript:void(0)" class="j-editor-up">上移</a>&nbsp;<a href="javascript:void(0)" class="j-editor-down">下移</a>&nbsp;<br></div><div class="p10 m20"></div><div class="layui-input-block"><textarea id="art_content'+(content_arr_len)+'" name="art_content[]" type="text/plain" style="width:99%;height:250px"></textarea></div></div>';
            $(".contents").append(tpl);
            ueArray[content_arr_len] = editor_getEditor( 'art_content'+content_arr_len );

        });

        if(content_arr_len==0){
            $('.j-editor-add').click();
        }
    });

    function getExtend(id){
        $.post("<?php echo url('type/extend'); ?>", {id:id}, function(res) {

            if (res.code == 1) {
                $.each(res.data, function(key, value){
                    $('.art_'+key+"_label").html('');
                    if(value != ''){
                        $.each(value, function(key2, value2){
                            $(".art_"+key+"_label").append('<a class="layui-btn layui-btn-xs extend" href="javascript:;" data-id="art_'+key+'">'+value2+'</a>');
                        });
                    }
                });
            }
        });
    }

    <?php if($info['art_id'] > 0): ?>
    setTimeout(function () {
        getExtend('<?php echo $info['type_id']; ?>')
    },1000);
    <?php endif; ?>
    
</script>

</body>
</html>