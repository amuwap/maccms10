<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:41:"template/default_pc/html/index/index.html";i:1775737045;s:55:"/workspace/template/default_pc/html/public/include.html";i:1775737045;s:52:"/workspace/template/default_pc/html/public/head.html";i:1775737045;s:52:"/workspace/template/default_pc/html/public/foot.html";i:1775737045;}*/ ?>
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
<script src="<?php echo $maccms['path_tpl']; ?>js/animations.js"></script>
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
<?php $__TAG__ = '{"num":"1","level":"9","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
<section class="hero-section">
    <div class="hero-wrapper">
        <div class="hero-bg-layer">
            <img src="<?php echo mac_url_img($vo['vod_pic_slide']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="hero-bg-image"/>
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content-wrapper">
            <div class="container">
                <div class="hero-main">
                    <div class="hero-visual">
                        <div class="hero-poster">
                            <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="hero-poster-img">
                        </div>
                    </div>
                    <div class="hero-info">
                        <div class="hero-meta">
                            <span class="hero-tag hero-tag-primary"><?php echo $vo['vod_remarks']; ?></span>
                            <span class="hero-tag"><?php echo $vo['vod_year']; ?></span>
                            <span class="hero-tag"><?php echo $vo['vod_area']; ?></span>
                            <span class="hero-tag"><?php echo $vo['vod_lang']; ?></span>
                        </div>
                        <h1 class="hero-main-title"><?php echo $vo['vod_name']; ?></h1>
                        <div class="hero-rating">
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="rating-score"><?php echo $vo['vod_score']; ?></span>
                            <span class="rating-label">评分</span>
                        </div>
                        <p class="hero-description"><?php echo mb_substr($vo['vod_blurb'],0,200); ?>...</p>
                        <div class="hero-actions-wrapper">
                            <a href="<?php echo mac_url_vod_detail($vo); ?>" class="hero-btn-cta hero-btn-cta-primary">
                                <span class="btn-icon"><i class="fas fa-play"></i></span>
                                <span class="btn-text">立即观看</span>
                            </a>
                            <a href="<?php echo mac_url_vod_detail($vo); ?>" class="hero-btn-cta hero-btn-cta-secondary">
                                <span class="btn-icon"><i class="fas fa-info-circle"></i></span>
                                <span class="btn-text">详细信息</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="hero-search-wrapper">
                    <div class="search-box">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input-modern" placeholder="搜索电影、电视剧、综艺、动漫..." id="search-input">
                        </div>
                        <button class="search-button-modern" id="search-button">
                            <span>搜索</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endforeach; endif; else: echo "" ;endif; ?>

<!-- 分类导航 -->
<section class="container mt-8">
    <h2 class="section-title">
        分类导航
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12">
        <?php $__TAG__ = '{"ids":"1,2,3,4","order":"asc","by":"sort","id":"vo1","key":"key1"}';$__LIST__ = model("Type")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key1 = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($key1 % 2 );++$key1;?>
        <a href="<?php echo mac_url_type($vo1); ?>" class="category-card">
            <div class="category-image-container">
                <img src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=<?php echo $vo1['type_name']; ?>%20category%20banner%20cinema%20dark%20theme&image_size=landscape_16_9" alt="<?php echo $vo1['type_name']; ?>" class="category-image">
                <div class="category-gradient"></div>
            </div>
            <div class="category-content">
                <h3 class="category-title"><?php echo $vo1['type_name']; ?></h3>
                <p class="category-subtitle">浏览全部<?php echo $vo1['type_name']; ?></p>
                <div class="category-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </a>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        <a href="<?php echo mac_url('label/rank'); ?>" class="category-card">
            <div class="category-image-container">
                <img src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=movie%20ranking%20leaderboard%20cinema%20dark%20theme&image_size=landscape_16_9" alt="影视排行榜" class="category-image">
                <div class="category-gradient"></div>
            </div>
            <div class="category-content">
                <h3 class="category-title">影视排行榜</h3>
                <p class="category-subtitle">查看热门排行</p>
                <div class="category-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>
</section>

<!-- 热门推荐 -->
<section class="container mb-12">
    <h2 class="section-title">
        热门推荐
        <a href="<?php echo mac_url('vod/type'); ?>">查看全部 <i class="fas fa-chevron-right text-xs"></i></a>
    </h2>
    <div class="scroll-list">
        <button class="scroll-control scroll-control-left" onclick="scrollList(this, -1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="scroll-container" id="scroll-container-1">
            <?php $__TAG__ = '{"num":"12","level":"1,2,3,4,5,6,7,8,9","order":"desc","by":"hits_month","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
            <div class="scroll-item">
                <div class="card">
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                        <div class="relative">
                            <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                            <div class="card-overlay">
                                <div class="flex items-center gap-2">
                                    <span class="card-badge"><?php echo $vo['vod_score']; ?>分</span>
                                    <span class="card-badge card-badge-secondary"><?php echo $vo['vod_hits_month']; ?>次观看</span>
                                </div>
                            </div>
                            <span class="card-badge"><?php echo $vo['vod_remarks']; ?></span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                            <div class="card-meta">
                                <span><?php echo $vo['vod_year']; ?></span>
                                <span><?php echo $vo['vod_area']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <button class="scroll-control scroll-control-right" onclick="scrollList(this, 1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- 最新电影 -->
<section class="container mb-12">
    <h2 class="section-title">
        最新电影
        <a href="<?php echo mac_url_type(['id'=>1]); ?>">查看全部 <i class="fas fa-chevron-right text-xs"></i></a>
    </h2>
    <div class="scroll-list">
        <button class="scroll-control scroll-control-left" onclick="scrollList(this, -1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="scroll-container" id="scroll-container-2">
            <?php $__TAG__ = '{"num":"12","type":"1","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
            <div class="scroll-item">
                <div class="card">
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                        <div class="relative">
                            <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                            <div class="card-overlay">
                                <div class="flex items-center gap-2">
                                    <span class="card-badge"><?php echo $vo['vod_score']; ?>分</span>
                                    <span class="card-badge card-badge-secondary"><?php echo $vo['vod_hits']; ?>次观看</span>
                                </div>
                            </div>
                            <span class="card-badge"><?php echo $vo['vod_remarks']; ?></span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                            <div class="card-meta">
                                <span><?php echo $vo['vod_year']; ?></span>
                                <span><?php echo $vo['vod_area']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <button class="scroll-control scroll-control-right" onclick="scrollList(this, 1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- 最新电视剧 -->
<section class="container mb-12">
    <h2 class="section-title">
        最新电视剧
        <a href="<?php echo mac_url_type(['id'=>2]); ?>">查看全部 <i class="fas fa-chevron-right text-xs"></i></a>
    </h2>
    <div class="scroll-list">
        <button class="scroll-control scroll-control-left" onclick="scrollList(this, -1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="scroll-container" id="scroll-container-3">
            <?php $__TAG__ = '{"num":"12","type":"2","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
            <div class="scroll-item">
                <div class="card">
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                        <div class="relative">
                            <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                            <div class="card-overlay">
                                <div class="flex items-center gap-2">
                                    <span class="card-badge"><?php echo $vo['vod_score']; ?>分</span>
                                    <span class="card-badge card-badge-secondary">连载<?php echo $vo['vod_serial']; ?>集</span>
                                </div>
                            </div>
                            <span class="card-badge"><?php echo $vo['vod_remarks']; ?></span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                            <div class="card-meta">
                                <span><?php echo $vo['vod_year']; ?></span>
                                <span><?php echo $vo['vod_area']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <button class="scroll-control scroll-control-right" onclick="scrollList(this, 1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- 最新综艺 -->
<section class="container mb-12">
    <h2 class="section-title">
        最新综艺
        <a href="<?php echo mac_url_type(['id'=>3]); ?>">查看全部 <i class="fas fa-chevron-right text-xs"></i></a>
    </h2>
    <div class="scroll-list">
        <button class="scroll-control scroll-control-left" onclick="scrollList(this, -1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="scroll-container" id="scroll-container-4">
            <?php $__TAG__ = '{"num":"12","type":"3","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
            <div class="scroll-item">
                <div class="card">
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                        <div class="relative">
                            <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                            <div class="card-overlay">
                                <div class="flex items-center gap-2">
                                    <span class="card-badge"><?php echo $vo['vod_score']; ?>分</span>
                                    <span class="card-badge card-badge-secondary">连载<?php echo $vo['vod_serial']; ?>期</span>
                                </div>
                            </div>
                            <span class="card-badge"><?php echo $vo['vod_remarks']; ?></span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                            <div class="card-meta">
                                <span><?php echo $vo['vod_year']; ?></span>
                                <span><?php echo $vo['vod_area']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <button class="scroll-control scroll-control-right" onclick="scrollList(this, 1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- 最新动漫 -->
<section class="container mb-12">
    <h2 class="section-title">
        最新动漫
        <a href="<?php echo mac_url_type(['id'=>4]); ?>">查看全部 <i class="fas fa-chevron-right text-xs"></i></a>
    </h2>
    <div class="scroll-list">
        <button class="scroll-control scroll-control-left" onclick="scrollList(this, -1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="scroll-container" id="scroll-container-5">
            <?php $__TAG__ = '{"num":"12","type":"4","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Vod")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
            <div class="scroll-item">
                <div class="card">
                    <a href="<?php echo mac_url_vod_detail($vo); ?>" title="<?php echo $vo['vod_name']; ?>">
                        <div class="relative">
                            <img src="<?php echo mac_url_img($vo['vod_pic']); ?>" alt="<?php echo $vo['vod_name']; ?>" class="card-image">
                            <div class="card-overlay">
                                <div class="flex items-center gap-2">
                                    <span class="card-badge"><?php echo $vo['vod_score']; ?>分</span>
                                    <span class="card-badge card-badge-secondary">连载<?php echo $vo['vod_serial']; ?>集</span>
                                </div>
                            </div>
                            <span class="card-badge"><?php echo $vo['vod_remarks']; ?></span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo $vo['vod_name']; ?></h3>
                            <div class="card-meta">
                                <span><?php echo $vo['vod_year']; ?></span>
                                <span><?php echo $vo['vod_area']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <button class="scroll-control scroll-control-right" onclick="scrollList(this, 1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- 最新资讯 -->
<section class="container mb-12">
    <h2 class="section-title">
        最新资讯
        <a href="<?php echo mac_url('art/type'); ?>">查看全部 <i class="fas fa-chevron-right text-xs"></i></a>
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php $__TAG__ = '{"num":"8","order":"desc","by":"time","id":"vo","key":"key"}';$__LIST__ = model("Art")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <a href="<?php echo mac_url_art_detail($vo); ?>" class="news-card">
            <div class="news-image">
                <img src="<?php echo mac_url_img($vo['art_pic']); ?>" alt="<?php echo $vo['art_name']; ?>" class="w-full h-full object-cover">
            </div>
            <div class="news-content">
                <h3 class="news-title"><?php echo $vo['art_name']; ?></h3>
                <p class="news-excerpt"><?php echo mb_substr(strip_tags($vo['art_content']),0,80); ?>...</p>
                <p class="news-date"><?php echo date('Y-m-d H:i',$vo['art_time']); ?></p>
            </div>
        </a>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</section>

<!-- 友情链接 -->
<section class="container mb-12">
    <h2 class="section-title">
        友情链接
    </h2>
    <div class="flex flex-wrap gap-4">
        <a href="//www.maccms.com" target="_blank" class="px-6 py-2 bg-background-dark rounded-full hover:bg-accent-color hover:text-white transition-all border border-border-color/50">苹果CMS-官网</a>
        <a href="//bbs.maccms.com" target="_blank" class="px-6 py-2 bg-background-dark rounded-full hover:bg-accent-color hover:text-white transition-all border border-border-color/50">苹果CMS-论坛</a>
        <?php $__TAG__ = '{"num":"10","type":"all","order":"desc","by":"id","id":"vo","key":"key"}';$__LIST__ = model("Link")->listCacheData($__TAG__); if(is_array($__LIST__['list']) || $__LIST__['list'] instanceof \think\Collection || $__LIST__['list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $__LIST__['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($key % 2 );++$key;?>
        <a href="<?php echo $vo['link_url']; ?>" target="_blank" class="px-6 py-2 bg-background-dark rounded-full hover:bg-accent-color hover:text-white transition-all border border-border-color/50"><?php echo $vo['link_name']; ?></a>
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
    
    // 头部滚动效果
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    
    // 图片懒加载
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('img');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.classList.add('fade-in');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
        
        // 平滑滚动
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });
    
    // 卡片悬停效果增强
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // 滚动列表功能
    function scrollList(button, direction) {
        const scrollList = button.closest('.scroll-list');
        const scrollContainer = scrollList.querySelector('.scroll-container');
        const scrollAmount = direction * 300; // 每次滚动300px
        
        scrollContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
    
    // 滚动控制按钮显示/隐藏
    document.querySelectorAll('.scroll-list').forEach(list => {
        const container = list.querySelector('.scroll-container');
        const leftButton = list.querySelector('.scroll-control-left');
        const rightButton = list.querySelector('.scroll-control-right');
        
        // 初始检查
        updateScrollButtons(list);
        
        // 滚动时检查
        container.addEventListener('scroll', () => {
            updateScrollButtons(list);
        });
    });
    
    function updateScrollButtons(list) {
        const container = list.querySelector('.scroll-container');
        const leftButton = list.querySelector('.scroll-control-left');
        const rightButton = list.querySelector('.scroll-control-right');
        
        // 检查是否可以向左滚动
        if (container.scrollLeft > 10) {
            leftButton.style.opacity = '1';
            leftButton.style.visibility = 'visible';
        } else {
            leftButton.style.opacity = '0';
            leftButton.style.visibility = 'hidden';
        }
        
        // 检查是否可以向右滚动
        if (container.scrollLeft < container.scrollWidth - container.clientWidth - 10) {
            rightButton.style.opacity = '1';
            rightButton.style.visibility = 'visible';
        } else {
            rightButton.style.opacity = '0';
            rightButton.style.visibility = 'hidden';
        }
    }
    
    // 性能优化：图片懒加载
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('data-src');
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        } else {
            // 降级方案
            images.forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('data-src');
                img.classList.add('loaded');
            });
        }
    });
    
    // 性能优化：减少布局偏移
    document.addEventListener('DOMContentLoaded', function() {
        const aspectRatioElements = document.querySelectorAll('.aspect-ratio');
        aspectRatioElements.forEach(element => {
            const img = element.querySelector('img');
            if (img && img.dataset.src) {
                img.src = img.dataset.src;
                img.classList.remove('data-src');
                img.classList.add('loaded');
            }
        });
    });
</script>
</body>
</html>