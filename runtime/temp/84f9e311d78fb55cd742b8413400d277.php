<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:48:"/workspace/application/admin/view/vod/batch.html";i:1775560112;s:50:"/workspace/application/admin/view/public/head.html";i:1775560112;}*/ ?>
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
<div class="page-container p10">

    <form class="layui-form" method="post" action="">

        <div class="my-toolbar-box">

            <div class="center mb10">

                <div class="layui-input-inline w150">
                    <select name="type">
                        <option value="">选择分类</option>
                        <?php if(is_array($type_tree) || $type_tree instanceof \think\Collection || $type_tree instanceof \think\Paginator): $i = 0; $__LIST__ = $type_tree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if($vo['type_mid'] == 1): ?>
                        <option value="<?php echo $vo['type_id']; ?>" <?php if($param['type'] == $vo['type_id']): ?>selected <?php endif; ?>><?php echo $vo['type_name']; ?></option>
                        <?php if(is_array($vo['child']) || $vo['child'] instanceof \think\Collection || $vo['child'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['child'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ch): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $ch['type_id']; ?>" <?php if($param['type'] == $ch['type_id']): ?>selected <?php endif; ?>>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;<?php echo $ch['type_name']; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; endif; endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value="">选择状态</option>
                        <option value="0" <?php if($param['status'] == '0'): ?>selected <?php endif; ?>>未审核</option>
                        <option value="1" <?php if($param['status'] == '1'): ?>selected <?php endif; ?>>已审核</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="level">
                        <option value="">选择推荐</option>
                        <option value="9" <?php if($param['level'] == '9'): ?>selected <?php endif; ?>>推荐9-幻灯</option>
                        <option value="1" <?php if($param['level'] == '1'): ?>selected <?php endif; ?>>推荐1</option>
                        <option value="2" <?php if($param['level'] == '2'): ?>selected <?php endif; ?>>推荐2</option>
                        <option value="3" <?php if($param['level'] == '3'): ?>selected <?php endif; ?>>推荐3</option>
                        <option value="4" <?php if($param['level'] == '4'): ?>selected <?php endif; ?>>推荐4</option>
                        <option value="5" <?php if($param['level'] == '5'): ?>selected <?php endif; ?>>推荐5</option>
                        <option value="6" <?php if($param['level'] == '6'): ?>selected <?php endif; ?>>推荐6</option>
                        <option value="7" <?php if($param['level'] == '7'): ?>selected <?php endif; ?>>推荐7</option>
                        <option value="8" <?php if($param['level'] == '8'): ?>selected <?php endif; ?>>推荐8</option>

                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="lock">
                        <option value="">选择锁定</option>
                        <option value="0" <?php if($param['lock'] == '0'): ?>selected <?php endif; ?>>未锁定</option>
                        <option value="1" <?php if($param['lock'] == '1'): ?>selected <?php endif; ?>>已锁定</option>
                    </select>
                </div>


                <div class="layui-input-inline w150">
                    <select name="weekday">
                        <option value="">选择周期</option>
                        <?php $_result=explode(',',$GLOBALS['config']['app']['vod_extend_weekday']);if(is_array($_result) || $_result instanceof \think\Collection || $_result instanceof \think\Paginator): $key2 = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo2): $mod = ($key2 % 2 );++$key2;?>
                        <option value="<?php echo $vo2; ?>" <?php if($param['weekday'] == $vo2): ?>selected <?php endif; ?>><?php echo $vo2; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="area">
                        <option value="">选择地区</option>
                        <?php $_result=explode(',',$GLOBALS['config']['app']['vod_extend_area']);if(is_array($_result) || $_result instanceof \think\Collection || $_result instanceof \think\Paginator): $key2 = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo2): $mod = ($key2 % 2 );++$key2;?>
                        <option value="<?php echo $vo2; ?>" <?php if($param['area'] == $vo2): ?>selected <?php endif; ?>><?php echo $vo2; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="lang">
                        <option value="">选择语言</option>
                        <?php $_result=explode(',',$GLOBALS['config']['app']['vod_extend_lang']);if(is_array($_result) || $_result instanceof \think\Collection || $_result instanceof \think\Paginator): $key2 = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo2): $mod = ($key2 % 2 );++$key2;?>
                        <option value="<?php echo $vo2; ?>" <?php if($param['lang'] == $vo2): ?>selected <?php endif; ?>><?php echo $vo2; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>

            </div>

            <div class="center mb10">

                <div class="layui-input-inline w150">
                    <select name="player">
                        <option value="">选择播放器</option>
                        <option value="no">空播放组</option>
                        <?php if(is_array($player_list) || $player_list instanceof \think\Collection || $player_list instanceof \think\Paginator): $i = 0; $__LIST__ = $player_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $vo['from']; ?>"><?php echo $vo['show']; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="downer">
                        <option value="">选择下载器</option>
                        <option value="no" >空下载组</option>
                        <?php if(is_array($downer_list) || $downer_list instanceof \think\Collection || $downer_list instanceof \think\Paginator): $i = 0; $__LIST__ = $downer_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $vo['from']; ?>"><?php echo $vo['show']; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="pic">
                        <option value="">选择图片</option>
                        <option value="1" <?php if($param['pic'] == '1'): ?>selected<?php endif; ?>>无图片</option>
                        <option value="2" <?php if($param['pic'] == '2'): ?>selected<?php endif; ?>>远程图片</option>
                        <option value="3" <?php if($param['pic'] == '3'): ?>selected<?php endif; ?>>同步出错图</option>
                    </select>
                </div>

                <div class="layui-input-inline w150">
                    <select name="isend">
                        <option value="">选择完结</option>
                        <option value="0" <?php if($param['isend'] == '0'): ?>selected <?php endif; ?>>未完结</option>
                        <option value="1" <?php if($param['isend'] == '1'): ?>selected <?php endif; ?>>已完结</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="copyright">
                        <option value="">选择版权</option>
                        <option value="0" <?php if($param['copyright'] == '0'): ?>selected <?php endif; ?>>未开启</option>
                        <option value="1" <?php if($param['copyright'] == '1'): ?>selected <?php endif; ?>>已开启</option>
                    </select>
                </div>

                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="请输入关键字" class="layui-input" name="wd" value="<?php echo $param['wd']; ?>">
                </div>

            </div>

        </div>

        <fieldset class="layui-elem-field">
            <legend>批量删除</legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label"><input type="radio" lay-ignore value="1" name="ck_del">删除数据</label>
                        <label class="layui-form-label"><input type="radio" lay-ignore value="2" name="ck_del">删播放组</label>
                        <label class="layui-form-label"><input type="radio" lay-ignore value="3" name="ck_del">删下载组</label>
                    </div>
                </div>

                <div class="layui-form-item">
                    <button type="button" class="layui-btn btn_submit">批量删除</button>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
        <legend>批量设置</legend>
        <div class="layui-field-box">

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_level" title="推荐">推荐</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_level">
                            <option value="">选择推荐</option>
                            <option value="9" >推荐9-幻灯</option>
                            <option value="1" >推荐1</option>
                            <option value="2" >推荐2</option>
                            <option value="3" >推荐3</option>
                            <option value="4" >推荐4</option>
                            <option value="5" >推荐5</option>
                            <option value="6" >推荐6</option>
                            <option value="7" >推荐7</option>
                            <option value="8" >推荐8</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_lock">锁定</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_lock">
                            <option value="">选择操作</option>
                            <option value="0" >解锁</option>
                            <option value="1" >锁定</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_status">状态</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_status">
                            <option value="">选择状态</option>
                            <option value="0" >未审核</option>
                            <option value="1" >已审核</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_hits">人气</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_min" required  placeholder="最小值" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_max" required  placeholder="最大值" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_points">积分</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_points_play" required  placeholder="播放积分" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_points_down" required  placeholder="下载积分" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>


            <div class="layui-form-item">
                <button type="submit" class="layui-btn btn_submit">开始执行</button>
            </div>

        </div>
    </fieldset>
    </form>
</div>

<script type="text/javascript">
    layui.use(['form'], function () {

    });

    $('.btn_submit').click(function(){
        $('form').submit();
    })
</script>
</body>
</html>