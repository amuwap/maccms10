<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:41:"template/default_pc/html/index/index.html";i:1775659732;s:55:"/workspace/template/default_pc/html/public/include.html";i:1775659662;s:52:"/workspace/template/default_pc/html/public/head.html";i:1775659745;s:52:"/workspace/template/default_pc/html/public/foot.html";i:1775659761;}*/ ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php if(isset($config['frontend']['seo']['vod']['name']) and !empty($config['frontend']['seo']['vod']['name'])): ?><?php echo raw($config['frontend']['seo']['vod']['name']); else: ?><?php echo $maccms['site_name']; endif; ?></title>
    <meta name="keywords" content="<?php if(isset($config['frontend']['seo']['vod']['key']) and !empty($config['frontend']['seo']['vod']['key'])): ?><?php echo raw($config['frontend']['seo']['vod']['key']); else: ?><?php echo $maccms['site_keywords']; endif; ?>" />
    <meta name="description" content="<?php if(isset($config['frontend']['seo']['vod']['des']) and !empty($config['frontend']['seo']['vod']['des'])): ?><?php echo raw($config['frontend']['seo']['vod']['des']); else: ?><?php echo $maccms['site_description']; endif; ?>" />
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

<!-- 英雄区 -->
<section class="hero">
    <div class="hero-content fade-in">
        <h1 class="hero-title"><?php echo $maccms['site_name']; ?></h1>
        <p class="hero-subtitle">海量高清影视资源，畅享极致观影体验</p>
        <div class="search-bar mt-4">
            <input type="text" class="search-input" placeholder="搜索电影、电视剧、综艺..." id="search-input">
            <button class="search-button" id="search-button">搜索</button>
        </div>
    </div>
    <ul class="51buypic" style="display: none;">
        <?php $__TAG__ = '{"num":"5","level":"9","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <li><a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>  <?php echo $vo['vod_remarks']; ?>"><img src="<?php echo mac_url_img($vo['vod_pic_slide']); ?>" alt="<?php echo $vo['vod_name']; ?> <?php echo $vo['vod_remarks']; ?>" class="hero-background"/></a></li>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </ul>
</section>

<!-- 分类导航 -->
<section class="container mt-5">
    <div class="grid grid-cols-5 gap-4 mb-6">
        <?php $__TAG__ = '{"ids":"1,2,3,4","order":"asc","by":"sort","id":"vo1","key":"key1"}';$__LIST__ = model("Type")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key1 = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($key1 % 2 );++$key1;?>
        <a href="<?php echo mac_url_type($vo1); ?>" class="card p-4 text-center hover:shadow-md transition-all">
            <h3 class="card-title mb-2"><?php echo $vo1['type_name']; ?></h3>
            <p class="card-text text-sm">浏览全部<?php echo $vo1['type_name']; ?></p>
        </a>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        <a href="<?php echo mac_url('label/rank'); ?>" class="card p-4 text-center hover:shadow-md transition-all">
            <h3 class="card-title mb-2">影视排行榜</h3>
            <p class="card-text text-sm">查看热门排行</p>
        </a>
    </div>
</section>

<!-- 热门推荐 -->
<section class="container mb-8">
    <h2 class="text-2xl font-bold mb-4">热门推荐</h2>
    <div class="grid grid-cols-4 gap-4">
        <?php $__TAG__ = '{"num":"8","level":"1,2,3,4,5,6,7,8,9","order":"desc","by":"hits_month","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <div class="card">
            <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                <div class="card-content">
                    <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                    <p class="card-text"><?php echo mb_substr($vo['vod_actor'],0,20); ?>...</p>
                    <span class="text-sm font-medium text-accent-color"><?php echo $vo['vod_version']; ?></span>
                </div>
            </a>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</section>

<!-- 最新电影 -->
<section class="container mb-8">
    <h2 class="text-2xl font-bold mb-4">最新电影</h2>
    <div class="grid grid-cols-4 gap-4">
        <?php $__TAG__ = '{"num":"8","type":"1","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <div class="card">
            <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                <div class="card-content">
                    <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                    <p class="card-text"><?php echo mb_substr($vo['vod_actor'],0,20); ?>...</p>
                    <span class="text-sm font-medium text-accent-color"><?php echo $vo['vod_version']; ?></span>
                </div>
            </a>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</section>

<!-- 最新电视剧 -->
<section class="container mb-8">
    <h2 class="text-2xl font-bold mb-4">最新电视剧</h2>
    <div class="grid grid-cols-4 gap-4">
        <?php $__TAG__ = '{"num":"8","type":"2","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <div class="card">
            <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                <div class="card-content">
                    <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                    <p class="card-text"><?php echo mb_substr($vo['vod_actor'],0,20); ?>...</p>
                    <span class="text-sm font-medium text-accent-color">连载<?php echo $vo['vod_serial']; ?>集 / 共<?php echo $vo['vod_total']; ?>集</span>
                </div>
            </a>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</section>

<!-- 最新综艺 -->
<section class="container mb-8">
    <h2 class="text-2xl font-bold mb-4">最新综艺</h2>
    <div class="grid grid-cols-4 gap-4">
        <?php $__TAG__ = '{"num":"8","type":"3","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <div class="card">
            <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                <div class="card-content">
                    <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                    <p class="card-text"><?php echo mb_substr($vo['vod_actor'],0,20); ?>...</p>
                    <span class="text-sm font-medium text-accent-color">连载<?php echo $vo['vod_serial']; ?>期</span>
                </div>
            </a>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</section>

<!-- 最新动漫 -->
<section class="container mb-8">
    <h2 class="text-2xl font-bold mb-4">最新动漫</h2>
    <div class="grid grid-cols-4 gap-4">
        <?php $__TAG__ = '{"num":"8","type":"4","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <div class="card">
            <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                <div class="card-content">
                    <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                    <p class="card-text"><?php echo mb_substr($vo['vod_actor'],0,20); ?>...</p>
                    <span class="text-sm font-medium text-accent-color">连载<?php echo $vo['vod_serial']; ?>集 / 共<?php echo $vo['vod_total']; ?>集</span>
                </div>
            </a>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</section>

<!-- 最新资讯 -->
<section class="container mb-8">
    <h2 class="text-2xl font-bold mb-4">最新资讯</h2>
    <div class="grid grid-cols-2 gap-4">
        <?php $__TAG__ = '{"num":"8","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Art")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <a href="<?php echo mac_url_art_detail($vo); ?>" class="flex items-center p-4 border border-border-color rounded-lg hover:bg-background-dark transition-all">
            <div class="w-24 h-16 bg-background-dark rounded mr-4 flex-shrink-0"></div>
            <div>
                <h3 class="font-medium mb-1"><?php echo $vo['art_name']; ?></h3>
                <p class="text-sm text-text-light"><?php echo date('Y-m-d',$vo['art_time']); ?></p>
            </div>
        </a>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</section>

<!-- 友情链接 -->
<section class="container mb-8">
    <h2 class="text-2xl font-bold mb-4">友情链接</h2>
    <div class="flex flex-wrap gap-4">
        <a href="//www.maccms.com" target="_blank" class="px-4 py-2 border border-border-color rounded-md hover:bg-accent-color hover:text-white transition-all">苹果CMS-官网</a>
        <a href="//bbs.maccms.com" target="_blank" class="px-4 py-2 border border-border-color rounded-md hover:bg-accent-color hover:text-white transition-all">苹果CMS-论坛</a>
        <?php $__TAG__ = '{"num":"10","type":"all","order":"desc","by":"id","id":"vo","key":"key"}';$__LIST__ = model("Link")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <a href="<?php echo $vo['link_url']; ?>" target="_blank" class="px-4 py-2 border border-border-color rounded-md hover:bg-accent-color hover:text-white transition-all"><?php echo $vo['link_name']; ?></a>
        <?php endforeach; endif; else: echo "" ;endif; ?>
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

<script>
    // 搜索功能
    document.getElementById('search-button').addEventListener('click', function() {
        var keyword = document.getElementById('search-input').value.trim();
        if (keyword) {
            window.location.href = '<?php echo $maccms['path']; ?>/vod/search?wd=' + encodeURIComponent(keyword);
        }
    });
    
    // 回车搜索
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('search-button').click();
        }
    });
    
    // 图片懒加载
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    });
</script>
</body>
</html>