// 主JS文件 - 处理页面交互和动画效果

$(document).ready(function() {
    // 初始化页面动画
    initAnimations();
    
    // 搜索功能
    initSearch();
    
    // 滚动到顶部按钮
    initScrollTop();
    
    // 模态框处理
    initModals();
    
    // 标签切换
    initTabs();
    
    // 分类筛选
    initFilters();
    
    // 收藏功能
    initFavorite();
    
    // 评分显示
    initRatings();
    
    // 加载更多
    initLoadMore();
});

// 初始化页面动画
function initAnimations() {
    // 元素进入视口时的动画
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-scale-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    // 观察所有需要动画的元素
    document.querySelectorAll('.content-item, .person-card, .search-result-card, .art-item').forEach(el => {
        observer.observe(el);
    });
    
    // 鼠标悬停效果
    $('.content-item').on('mouseenter', function() {
        $(this).addClass('animate-float');
    }).on('mouseleave', function() {
        $(this).removeClass('animate-float');
    });
}

// 初始化搜索功能
function initSearch() {
    $('.search-btn').on('click', function() {
        const keyword = $('.search-input').val().trim();
        if (keyword) {
            window.location.href = '{:mac_url("vod/search")}?wd=' + encodeURIComponent(keyword);
        }
    });
    
    $('.search-input').on('keypress', function(e) {
        if (e.which === 13) {
            $('.search-btn').click();
        }
    });
}

// 初始化滚动到顶部按钮
function initScrollTop() {
    const scrollTopBtn = $('.scroll-top');
    
    // 显示/隐藏按钮
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 300) {
            scrollTopBtn.fadeIn();
        } else {
            scrollTopBtn.fadeOut();
        }
    });
    
    // 点击滚动到顶部
    scrollTopBtn.on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 600);
    });
}

// 初始化模态框
function initModals() {
    // 关闭模态框
    $('.modal-close').on('click', function() {
        $('.modal-overlay, .modal').fadeOut();
    });
    
    // 点击背景关闭
    $('.modal-overlay').on('click', function() {
        $('.modal-overlay, .modal').fadeOut();
    });
}

// 初始化标签切换
function initTabs() {
    $('.tab-nav-item').on('click', function() {
        const tab = $(this).data('tab');
        
        // 切换标签状态
        $('.tab-nav-item').removeClass('active');
        $(this).addClass('active');
        
        // 切换内容
        $('.tab-panel').removeClass('active');
        $('#' + tab).addClass('active');
    });
}

// 初始化筛选功能
function initFilters() {
    // 筛选标签点击
    $('.filter-chip, .filter-tag, .sort-chip').on('click', function(e) {
        // 移除其他标签的活动状态
        $(this).siblings().removeClass('active');
        // 添加当前标签的活动状态
        $(this).addClass('active');
    });
}

// 初始化收藏功能
function initFavorite() {
    $('.action-btn').on('click', function() {
        // 这里可以添加收藏的逻辑
        const vodId = $(this).data('id');
        if (vodId) {
            // 发起收藏请求
            $.ajax({
                url: '{:mac_url("user/fav")}',
                type: 'post',
                data: { vod_id: vodId },
                success: function(res) {
                    if (res.code === 1) {
                        layer.msg('收藏成功', { icon: 1 });
                    } else {
                        layer.msg(res.msg, { icon: 2 });
                    }
                }
            });
        }
    });
}

// 初始化评分显示
function initRatings() {
    // 这里可以添加星级评分的逻辑
    $('.star-rating').each(function() {
        const score = parseFloat($(this).data('score'));
        const stars = $(this).find('.star');
        
        stars.each(function(index) {
            if (index < Math.floor(score)) {
                $(this).addClass('star-full');
            } else if (index === Math.floor(score) && score % 1 >= 0.5) {
                $(this).addClass('star-half');
            } else {
                $(this).addClass('star-empty');
            }
        });
    });
}

// 初始化加载更多
function initLoadMore() {
    // 这里可以添加无限滚动加载的逻辑
    let loading = false;
    
    $(window).on('scroll', function() {
        if (loading) return;
        
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();
        const scrollTop = $(window).scrollTop();
        
        // 当滚动到距离底部100px时加载更多
        if (documentHeight - scrollTop - windowHeight < 100) {
            loading = true;
            
            // 这里可以添加加载更多的逻辑
            // 例如：加载下一页数据并追加到列表
            
            setTimeout(() => {
                loading = false;
            }, 1000);
        }
    });
}

// 播放报错提交
function submitPlayError() {
    const vodId = $('.vod-id').val();
    const content = $('.error-content').val().trim();
    
    if (!content) {
        layer.msg('请描述您遇到的错误', { icon: 2 });
        return;
    }
    
    // 这里可以添加提交报错的逻辑
    $.ajax({
        url: '{:mac_url("vod/report")}',
        type: 'post',
        data: { vod_id: vodId, content: content },
        success: function(res) {
            if (res.code === 1) {
                layer.msg('报错提交成功', { icon: 1 });
                $('.modal-overlay, .modal-error').fadeOut();
                $('.error-content').val('');
            } else {
                layer.msg(res.msg, { icon: 2 });
            }
        }
    });
}

// 分享功能
function shareContent(url, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // 复制链接到剪贴板
        navigator.clipboard.writeText(url).then(() => {
            layer.msg('链接已复制到剪贴板', { icon: 1 });
        }).catch(err => {
            layer.msg('复制失败，请手动复制', { icon: 2 });
        });
    }
}

// 平滑滚动
function smoothScrollTo(element) {
    $('html, body').animate({
        scrollTop: $(element).offset().top - 100
    }, 600);
}

// 数字动画
function animateNumber(element, target, duration = 1000) {
    const start = parseInt($(element).text()) || 0;
    const increment = (target - start) / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
            $(element).text(Math.round(target));
            clearInterval(timer);
        } else {
            $(element).text(Math.round(current));
        }
    }, 16);
}

// 响应式菜单
function initResponsiveMenu() {
    const menuToggle = $('.menu-toggle');
    const navMain = $('.nav-main');
    
    menuToggle.on('click', function() {
        navMain.toggleClass('active');
        // 切换菜单图标
        const svg = $(this).find('svg');
        if (navMain.hasClass('active')) {
            svg.html('<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>');
        } else {
            svg.html('<line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>');
        }
    });
    
    // 点击菜单项后关闭菜单
    $('.nav-item a').on('click', function() {
        if (window.innerWidth < 768 && navMain.hasClass('active')) {
            navMain.removeClass('active');
            // 恢复菜单图标
            const svg = menuToggle.find('svg');
            svg.html('<line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>');
        }
    });
    
    // 点击菜单外部关闭菜单
    $(document).on('click', function(e) {
        if (window.innerWidth < 768 && navMain.hasClass('active') && !menuToggle.is(e.target) && !menuToggle.find('*').is(e.target) && !navMain.is(e.target) && !navMain.find('*').is(e.target)) {
            navMain.removeClass('active');
            // 恢复菜单图标
            const svg = menuToggle.find('svg');
            svg.html('<line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>');
        }
    });
}

// 图片懒加载
function initLazyLoad() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const image = entry.target;
                    image.src = image.dataset.src;
                    image.classList.remove('lazy');
                    imageObserver.unobserve(image);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // 回退方案
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
}

// 页面加载完成后初始化
window.addEventListener('load', function() {
    // 初始化响应式菜单
    initResponsiveMenu();
    
    // 初始化图片懒加载
    initLazyLoad();
    
    // 初始化数字动画
    $('.stat-value').each(function() {
        const target = parseInt($(this).text());
        animateNumber(this, target);
    });
});

// 处理页面可见性变化
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // 页面重新可见时的处理
        initAnimations();
    }
});

// 处理键盘快捷键
document.addEventListener('keydown', function(e) {
    // ESC键关闭模态框
    if (e.key === 'Escape') {
        $('.modal-overlay, .modal').fadeOut();
    }
    
    // Ctrl+F 聚焦搜索框
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        $('.search-input').focus();
    }
});

// 处理错误
window.addEventListener('error', function(e) {
    console.error('Error:', e.error);
});

// 处理未捕获的异常
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
});
