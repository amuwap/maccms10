// 动画和交互效果
$(document).ready(function() {
    // 平滑滚动到锚点
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 500);
        }
    });
    
    // 导航栏滚动效果
    $(window).on('scroll', function() {
        const header = $('header');
        if ($(window).scrollTop() > 50) {
            header.addClass('bg-background-light/90 backdrop-blur-md shadow-md');
        } else {
            header.removeClass('bg-background-light/90 backdrop-blur-md shadow-md');
        }
    });
    
    // 图片懒加载
    $('img').each(function() {
        const img = $(this);
        const src = img.attr('src');
        if (src) {
            img.attr('src', '');
            img.attr('data-src', src);
        }
    });
    
    function lazyLoad() {
        const images = $('img[data-src]');
        const windowHeight = $(window).height();
        const scrollTop = $(window).scrollTop();
        
        images.each(function() {
            const img = $(this);
            const imgTop = img.offset().top;
            
            if (imgTop < scrollTop + windowHeight + 200) {
                const src = img.attr('data-src');
                img.attr('src', src);
                img.removeAttr('data-src');
                img.addClass('opacity-0 transition-opacity duration-500');
                setTimeout(() => {
                    img.addClass('opacity-100');
                }, 100);
            }
        });
    }
    
    // 初始加载和滚动时执行懒加载
    lazyLoad();
    $(window).on('scroll', lazyLoad);
    
    // 卡片悬停效果
    $('.card, .group').on('mouseenter', function() {
        $(this).css('transform', 'translateY(-5px)');
    });
    
    $('.card, .group').on('mouseleave', function() {
        $(this).css('transform', 'translateY(0)');
    });
    
    // 按钮点击效果
    $('button, .btn').on('click', function(e) {
        const btn = $(this);
        btn.addClass('scale-95');
        setTimeout(() => {
            btn.removeClass('scale-95');
        }, 150);
    });
    
    // 滚动到页面顶部按钮
    const scrollTopBtn = $('<button class="fixed bottom-6 right-6 w-12 h-12 bg-accent-color text-white rounded-full flex items-center justify-center shadow-lg opacity-0 invisible transition-all duration-300 z-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>
    </button>');
    
    $('body').append(scrollTopBtn);
    
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 300) {
            scrollTopBtn.removeClass('opacity-0 invisible');
            scrollTopBtn.addClass('opacity-100 visible');
        } else {
            scrollTopBtn.removeClass('opacity-100 visible');
            scrollTopBtn.addClass('opacity-0 invisible');
        }
    });
    
    scrollTopBtn.on('click', function() {
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    });
    
    // 搜索框交互
    const searchInput = $('input[type="search"]');
    searchInput.on('focus', function() {
        $(this).parent().addClass('border-accent-color');
    });
    
    searchInput.on('blur', function() {
        $(this).parent().removeClass('border-accent-color');
    });
    
    // 分类标签悬停效果
    $('.category-card').on('mouseenter', function() {
        const icon = $(this).find('svg');
        icon.addClass('scale-110 rotate-12');
    });
    
    $('.category-card').on('mouseleave', function() {
        const icon = $(this).find('svg');
        icon.removeClass('scale-110 rotate-12');
    });
});
