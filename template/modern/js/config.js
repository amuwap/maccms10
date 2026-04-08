// 模板配置
const TemplateConfig = {
    // 基本配置
    site: {
        title: '{$config.frontend.site_title|default="苹果CMS测试网站"}',
        description: '{$config.frontend.site_description|default="海量高清影视资源，畅享极致观影体验"}',
        keywords: '{$config.frontend.site_keywords|default="短视频,搞笑视频,视频分享,免费视频,在线视频,预告片"}'
    },
    
    // 按钮文字
    btnText: {
        play: '{$config.frontend.btn_text.play|default="播放"}',
        download: '{$config.frontend.btn_text.download|default="下载"}',
        collect: '{$config.frontend.btn_text.collect|default="收藏"}',
        follow: '{$config.frontend.btn_text.follow|default="追剧"}',
        like: '{$config.frontend.btn_text.like|default="点赞"}',
        share: '{$config.frontend.btn_text.share|default="分享"}',
        report: '{$config.frontend.btn_text.report|default="报错"}'
    },
    
    // 状态文字
    statusText: {
        vip: '{$config.frontend.status_text.vip|default="VIP"}',
        hot: '{$config.frontend.status_text.hot|default="热门"}',
        new: '{$config.frontend.status_text.new|default="最新"}',
        recommend: '{$config.frontend.status_text.recommend|default="推荐"}'
    },
    
    // 主题颜色
    themeColor: {
        primary: '{$config.frontend.theme_color.primary|default="#3b82f6"}',
        secondary: '{$config.frontend.theme_color.secondary|default="#8b5cf6"}',
        accent: '{$config.frontend.theme_color.accent|default="#fbbf24"}',
        success: '{$config.frontend.theme_color.success|default="#10b981"}',
        danger: '{$config.frontend.theme_color.danger|default="#ef4444"}',
        warning: '{$config.frontend.theme_color.warning|default="#f59e0b"}',
        info: '{$config.frontend.theme_color.info|default="#06b6d4"}'
    },
    
    // 字体设置
    font: {
        family: '{$config.frontend.font.family|default="Microsoft YaHei, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif"}',
        size: {
            base: '{$config.frontend.font.size.base|default="16px"}',
            heading1: '{$config.frontend.font.size.heading1|default="32px"}',
            heading2: '{$config.frontend.font.size.heading2|default="24px"}',
            heading3: '{$config.frontend.font.size.heading3|default="20px"}'
        }
    },
    
    // 布局设置
    layout: {
        containerWidth: '{$config.frontend.layout.container_width|default="1400px"}',
        sectionSpacing: '{$config.frontend.layout.section_spacing|default="32px"}',
        cardSpacing: '{$config.frontend.layout.card_spacing|default="24px"}'
    },
    
    // 功能开关
    features: {
        banner: {$config.frontend.features.banner|default=true},
        categoryNav: {$config.frontend.features.category_nav|default=true},
        hotRecommend: {$config.frontend.features.hot_recommend|default=true},
        latestUpdate: {$config.frontend.features.latest_update|default=true},
        categoryContent: {$config.frontend.features.category_content|default=true},
        featureSection: {$config.frontend.features.feature_section|default=true},
        pagination: {$config.frontend.features.pagination|default=true},
        search: {$config.frontend.features.search|default=true},
        userMenu: {$config.frontend.features.user_menu|default=true}
    },
    
    // 首页配置
    homepage: {
        bannerCount: {$config.frontend.homepage.banner_count|default=5},
        recommendCount: {$config.frontend.homepage.recommend_count|default=12},
        latestCount: {$config.frontend.homepage.latest_count|default=12},
        categoryCount: {$config.frontend.homepage.category_count|default=8}
    },
    
    // 影视详情页配置
    vodDetail: {
        showRating: {$config.frontend.vod_detail.show_rating|default=true},
        showMeta: {$config.frontend.vod_detail.show_meta|default=true},
        showActions: {$config.frontend.vod_detail.show_actions|default=true},
        showPlot: {$config.frontend.vod_detail.show_plot|default=true},
        showCast: {$config.frontend.vod_detail.show_cast|default=true},
        showRelated: {$config.frontend.vod_detail.show_related|default=true}
    },
    
    // 播放页面配置
    vodPlay: {
        showControls: {$config.frontend.vod_play.show_controls|default=true},
        showEpisodeList: {$config.frontend.vod_play.show_episode_list|default=true},
        showRelated: {$config.frontend.vod_play.show_related|default=true},
        autoPlay: {$config.frontend.vod_play.auto_play|default=false}
    },
    
    // 直播页面配置
    live: {
        showCategory: {$config.frontend.live.show_category|default=true},
        showStats: {$config.frontend.live.show_stats|default=true},
        showSchedule: {$config.frontend.live.show_schedule|default=true},
        autoPlay: {$config.frontend.live.auto_play|default=false}
    },
    
    // 动画设置
    animations: {
        enabled: {$config.frontend.animations.enabled|default=true},
        duration: '{$config.frontend.animations.duration|default="0.3s"}',
        easing: '{$config.frontend.animations.easing|default="ease-out"}'
    },
    
    // 响应式设置
    responsive: {
        mobileBreakpoint: '{$config.frontend.responsive.mobile_breakpoint|default="768px"}',
        tabletBreakpoint: '{$config.frontend.responsive.tablet_breakpoint|default="992px"}',
        desktopBreakpoint: '{$config.frontend.responsive.desktop_breakpoint|default="1200px"}'
    },
    
    // API设置
    api: {
        timeout: {$config.frontend.api.timeout|default=5000},
        retry: {$config.frontend.api.retry|default=3}
    },
    
    // 缓存设置
    cache: {
        enabled: {$config.frontend.cache.enabled|default=true},
        duration: {$config.frontend.cache.duration|default=3600}
    },
    
    // 语言文字
    lang: {
        welcome: '{$config.frontend.lang.zh.welcome|default="欢迎来到{site_name}"}',
        noData: '{$config.frontend.lang.zh.no_data|default="暂无数据"}',
        loading: '{$config.frontend.lang.zh.loading|default="加载中..."}',
        error: '{$config.frontend.lang.zh.error|default="出错了，请稍后重试"}',
        success: '{$config.frontend.lang.zh.success|default="操作成功"}',
        confirm: '{$config.frontend.lang.zh.confirm|default="确认"}',
        cancel: '{$config.frontend.lang.zh.cancel|default="取消"}',
        searchPlaceholder: '{$config.frontend.lang.zh.search_placeholder|default="搜索影片、演员、导演..."}',
        pageNotFound: '{$config.frontend.lang.zh.page_not_found|default="页面不存在"}',
        serverError: '{$config.frontend.lang.zh.server_error|default="服务器错误"}',
        accessDenied: '{$config.frontend.lang.zh.access_denied|default="访问权限不足"}',
        loginRequired: '{$config.frontend.lang.zh.login_required|default="请先登录"}',
        registerSuccess: '{$config.frontend.lang.zh.register_success|default="注册成功"}',
        loginSuccess: '{$config.frontend.lang.zh.login_success|default="登录成功"}',
        logoutSuccess: '{$config.frontend.lang.zh.logout_success|default="退出成功"}',
        collectSuccess: '{$config.frontend.lang.zh.collect_success|default="收藏成功"}',
        collectRemoved: '{$config.frontend.lang.zh.collect_removed|default="取消收藏"}',
        likeSuccess: '{$config.frontend.lang.zh.like_success|default="点赞成功"}',
        shareSuccess: '{$config.frontend.lang.zh.share_success|default="分享成功"}',
        reportSuccess: '{$config.frontend.lang.zh.report_success|default="举报成功"}',
        followSuccess: '{$config.frontend.lang.zh.follow_success|default="追剧成功"}',
        followRemoved: '{$config.frontend.lang.zh.follow_removed|default="取消追剧"}',
        commentSuccess: '{$config.frontend.lang.zh.comment_success|default="评论成功"}',
        commentPending: '{$config.frontend.lang.zh.comment_pending|default="评论待审核"}',
        paymentSuccess: '{$config.frontend.lang.zh.payment_success|default="支付成功"}',
        paymentFailed: '{$config.frontend.lang.zh.payment_failed|default="支付失败"}',
        vipRequired: '{$config.frontend.lang.zh.vip_required|default="需要VIP权限"}',
        pointsRequired: '{$config.frontend.lang.zh.points_required|default="积分不足"}',
        episodeNotFound: '{$config.frontend.lang.zh.episode_not_found|default="剧集不存在"}',
        playerError: '{$config.frontend.lang.zh.player_error|default="播放器错误"}',
        networkError: '{$config.frontend.lang.zh.network_error|default="网络错误"}',
        loadingFailed: '{$config.frontend.lang.zh.loading_failed|default="加载失败"}',
        tryAgain: '{$config.frontend.lang.zh.try_again|default="重试"}',
        backHome: '{$config.frontend.lang.zh.back_home|default="返回首页"}',
        viewMore: '{$config.frontend.lang.zh.view_more|default="查看更多"}',
        latestUpdates: '{$config.frontend.lang.zh.latest_updates|default="最新更新"}',
        hotRecommendations: '{$config.frontend.lang.zh.hot_recommendations|default="热门推荐"}',
        relatedContent: '{$config.frontend.lang.zh.related_content|default="相关推荐"}',
        episodeList: '{$config.frontend.lang.zh.episode_list|default="选集列表"}',
        castInfo: '{$config.frontend.lang.zh.cast_info|default="演职人员"}',
        director: '{$config.frontend.lang.zh.director|default="导演"}',
        actors: '{$config.frontend.lang.zh.actors|default="主演"}',
        plotSummary: '{$config.frontend.lang.zh.plot_summary|default="剧情简介"}',
        episodePlot: '{$config.frontend.lang.zh.episode_plot|default="分集剧情"}',
        playNow: '{$config.frontend.lang.zh.play_now|default="立即播放"}',
        downloadNow: '{$config.frontend.lang.zh.download_now|default="立即下载"}',
        collect: '{$config.frontend.lang.zh.collect|default="收藏"}',
        follow: '{$config.frontend.lang.zh.follow|default="追剧"}',
        like: '{$config.frontend.lang.zh.like|default="点赞"}',
        share: '{$config.frontend.lang.zh.share|default="分享"}',
        report: '{$config.frontend.lang.zh.report|default="报错"}',
        year: '{$config.frontend.lang.zh.year|default="年份"}',
        area: '{$config.frontend.lang.zh.area|default="地区"}',
        language: '{$config.frontend.lang.zh.language|default="语言"}',
        status: '{$config.frontend.lang.zh.status|default="状态"}',
        type: '{$config.frontend.lang.zh.type|default="类型"}',
        views: '{$config.frontend.lang.zh.views|default="浏览"}',
        rating: '{$config.frontend.lang.zh.rating|default="评分"}',
        updateTime: '{$config.frontend.lang.zh.update_time|default="更新时间"}',
        playCount: '{$config.frontend.lang.zh.play_count|default="播放次数"}',
        commentCount: '{$config.frontend.lang.zh.comment_count|default="评论数"}',
        sortBy: '{$config.frontend.lang.zh.sort_by|default="排序方式"}',
        filterBy: '{$config.frontend.lang.zh.filter_by|default="筛选条件"}',
        searchResults: '{$config.frontend.lang.zh.search_results|default="搜索结果"}',
        noResults: '{$config.frontend.lang.zh.no_results|default="没有找到相关内容"}',
        page: '{$config.frontend.lang.zh.page|default="页"}',
        of: '{$config.frontend.lang.zh.of|default="共"}',
        total: '{$config.frontend.lang.zh.total|default="条"}',
        prevPage: '{$config.frontend.lang.zh.prev_page|default="上一页"}',
        nextPage: '{$config.frontend.lang.zh.next_page|default="下一页"}',
        firstPage: '{$config.frontend.lang.zh.first_page|default="首页"}',
        lastPage: '{$config.frontend.lang.zh.last_page|default="末页"}'
    },
    
    // 初始化配置
    init: function() {
        // 应用主题颜色
        this.applyThemeColors();
        
        // 应用字体设置
        this.applyFontSettings();
        
        // 应用布局设置
        this.applyLayoutSettings();
        
        // 应用语言文字
        this.applyLanguage();
    },
    
    // 应用主题颜色
    applyThemeColors: function() {
        const root = document.documentElement;
        for (const [key, value] of Object.entries(this.themeColor)) {
            root.style.setProperty(`--${key}-color`, value);
        }
    },
    
    // 应用字体设置
    applyFontSettings: function() {
        const root = document.documentElement;
        root.style.setProperty('--font-family', this.font.family);
        root.style.setProperty('--font-size-base', this.font.size.base);
        root.style.setProperty('--font-size-h1', this.font.size.heading1);
        root.style.setProperty('--font-size-h2', this.font.size.heading2);
        root.style.setProperty('--font-size-h3', this.font.size.heading3);
    },
    
    // 应用布局设置
    applyLayoutSettings: function() {
        const container = document.querySelector('.container');
        if (container) {
            container.style.maxWidth = this.layout.containerWidth;
        }
    },
    
    // 应用语言文字
    applyLanguage: function() {
        // 替换页面中的语言文字
        document.querySelectorAll('[data-lang]').forEach(element => {
            const key = element.getAttribute('data-lang');
            if (this.lang[key]) {
                element.textContent = this.lang[key].replace('{site_name}', this.site.title);
            }
        });
    },
    
    // 获取配置值
    get: function(key, defaultValue = null) {
        const keys = key.split('.');
        let value = this;
        
        for (const k of keys) {
            if (value[k] === undefined) {
                return defaultValue;
            }
            value = value[k];
        }
        
        return value;
    },
    
    // 检查功能是否启用
    isFeatureEnabled: function(feature) {
        return this.features[feature] || false;
    }
};

// 页面加载完成后初始化
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', TemplateConfig.init.bind(TemplateConfig));
} else {
    TemplateConfig.init();
}
