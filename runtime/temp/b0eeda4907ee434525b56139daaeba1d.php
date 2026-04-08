<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:40:"template/default_pc/html/label/rank.html";i:1775660296;s:55:"/workspace/template/default_pc/html/public/include.html";i:1775659662;s:52:"/workspace/template/default_pc/html/public/head.html";i:1775659745;s:52:"/workspace/template/default_pc/html/public/foot.html";i:1775659761;}*/ ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>排行榜 - <?php echo $maccms['site_name']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo $maccms['path_tpl']; ?>css/modern-style.css" rel="stylesheet" type="text/css" />
<script src="<?php echo $maccms['path']; ?>static/js/jquery.js"></script>
<script src="<?php echo $maccms['path']; ?>static/js/jquery.lazyload.js"></script>
<script src="<?php echo $maccms['path']; ?>static/js/jquery.autocomplete.js"></script>
<script src="<?php echo $maccms['path_tpl']; ?>js/jquery.superslide.js"></script>
<script src="<?php echo $maccms['path_tpl']; ?>js/jquery.lazyload.js"></script>
<script src="<?php echo $maccms['path_tpl']; ?>js/jquery.base.js"></script>
<script>var maccms={"path":"","mid":"<?php echo $maccms['mid']; ?>","aid":"<?php echo $maccms['aid']; ?>","url":"<?php echo $maccms['site_url']; ?>","wapurl":"<?php echo $maccms['site_wapurl']; ?>","mob_status":"<?php echo $maccms['mob_status']; ?>"};</script>
<script src="<?php echo $maccms['path']; ?>static/js/home.js"></script>
<script></script>

</head>
<body>
<!-- 头部 -->
<header class="header">
    <div class="container">
        <a href="<?php echo $maccms['path']; ?>" class="logo"><?php echo $maccms['site_name']; ?></a>
        
        <!-- 导航 -->
        <nav class="nav">
            <a href="<?php echo $maccms['path']; ?>" class="nav-link">首页</a>
            <?php $__TAG__ = '{"ids":"1,2,3,4","order":"asc","by":"sort","id":"vo","key":"key"}';$__LIST__ = model("Type")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
            <a href="<?php echo mac_url_type($vo); ?>" class="nav-link"><?php echo $vo['type_name']; ?></a>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <a href="<?php echo mac_url('label/rank'); ?>" class="nav-link">排行榜</a>
        </nav>
        
        <!-- 用户操作 -->
        <div class="nav">
            <?php if($maccms['user']['user_id']): ?>
            <a href="<?php echo mac_url('user/index'); ?>" class="nav-link"><?php echo $maccms['user']['user_name']; ?></a>
            <a href="<?php echo mac_url('user/logout'); ?>" class="nav-link">退出</a>
            <?php else: ?>
            <a href="<?php echo mac_url('user/login'); ?>" class="nav-link">登录</a>
            <a href="<?php echo mac_url('user/reg'); ?>" class="nav-link">注册</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- 当前位置 -->
<section class="container mt-4 mb-6">
    <nav class="text-sm text-text-light">
        <a href="<?php echo $maccms['path']; ?>" class="text-accent-color hover:underline">首页</a>
        <span class="mx-2">/</span>
        <span class="text-text-primary">排行榜</span>
    </nav>
</section>

<!-- 排行榜主体 -->
<section class="container mb-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- 电影排行榜 -->
        <div class="card p-6">
            <h2 class="text-xl font-bold mb-6 border-b pb-2">电影排行榜</h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">总评分排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"1","order":"desc","by":"score","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_score']; ?>分</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">月人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"1","order":"desc","by":"hits_month","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_month']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-medium mb-4">周人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"1","order":"desc","by":"hits_week","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_week']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 电视剧排行榜 -->
        <div class="card p-6">
            <h2 class="text-xl font-bold mb-6 border-b pb-2">电视剧排行榜</h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">总评分排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"2","order":"desc","by":"score","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_score']; ?>分</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">月人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"2","order":"desc","by":"hits_month","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_month']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-medium mb-4">周人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"2","order":"desc","by":"hits_week","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_week']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 动漫排行榜 -->
        <div class="card p-6">
            <h2 class="text-xl font-bold mb-6 border-b pb-2">动漫排行榜</h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">总评分排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"3","order":"desc","by":"score","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_score']; ?>分</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">月人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"3","order":"desc","by":"hits_month","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_month']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-medium mb-4">周人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"3","order":"desc","by":"hits_week","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_week']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 综艺排行榜 -->
        <div class="card p-6">
            <h2 class="text-xl font-bold mb-6 border-b pb-2">综艺排行榜</h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">总评分排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"4","order":"desc","by":"score","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_score']; ?>分</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">月人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"4","order":"desc","by":"hits_month","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_month']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-medium mb-4">周人气排行榜</h3>
                <div class="space-y-3">
                    <?php $__TAG__ = '{"num":"10","type":"4","order":"desc","by":"hits_week","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" class="flex items-center p-3 border border-border-color rounded-md hover:bg-background-dark transition-all">
                        <span class="w-8 h-8 flex items-center justify-center bg-accent-color text-white rounded-full mr-4 font-bold"><?php echo $key+1; ?></span>
                        <span class="flex-1 font-medium hover:text-accent-color"><?php echo $vo['vod_name']; ?></span>
                        <span class="text-text-light"><?php echo $vo['vod_hits_week']; ?>次</span>
                    </a>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- 页脚 -->
<footer class="footer">
    <div class="container">
        <div>
            <h3 class="footer-title">关于我们</h3>
            <a href="#" class="footer-link">网站简介</a>
            <a href="#" class="footer-link">联系方式</a>
            <a href="#" class="footer-link">加入我们</a>
            <a href="#" class="footer-link">版权声明</a>
        </div>
        <div>
            <h3 class="footer-title">帮助中心</h3>
            <a href="#" class="footer-link">常见问题</a>
            <a href="#" class="footer-link">使用教程</a>
            <a href="#" class="footer-link">意见反馈</a>
            <a href="#" class="footer-link">举报中心</a>
        </div>
        <div>
            <h3 class="footer-title">合作推广</h3>
            <a href="#" class="footer-link">广告合作</a>
            <a href="#" class="footer-link">内容合作</a>
            <a href="#" class="footer-link">友情链接</a>
        </div>
        <div>
            <h3 class="footer-title">关注我们</h3>
            <p class="text-white opacity-70 mb-4">扫码关注公众号</p>
            <div class="w-32 h-32 bg-white rounded mb-4"></div>
        </div>
    </div>
    <div class="container footer-bottom">
        <p><?php echo $maccms['site_copyright']; ?></p>
        <p class="mt-2"><?php echo $maccms['site_icp']; ?></p>
    </div>
</footer>
</body>
</html>