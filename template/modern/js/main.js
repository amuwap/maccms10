$(document).ready(function() {
    initSearch();
    initUserBehavior();
    initPlayer();
    initScrollEffects();
    initModal();
});

function initSearch() {
    $('.search-btn').on('click', function() {
        const wd = $('.search-input').val().trim();
        if (wd) {
            window.location.href = '/index.php/vod/search.html?wd=' + encodeURIComponent(wd);
        }
    });
    
    $('.search-input').on('keypress', function(e) {
        if (e.which === 13) {
            $('.search-btn').click();
        }
    });
}

function initUserBehavior() {
    $('.btn-favorite, .btn-follow, .btn-like').on('click', function() {
        const $this = $(this);
        const type = $this.data('type');
        const vodId = $this.data('vod-id');
        
        $.ajax({
            url: '/index.php/user/behavior.html',
            type: 'POST',
            data: {
                type: type,
                vod_id: vodId,
                action: $this.hasClass('active') ? 'remove' : 'add'
            },
            dataType: 'json',
            success: function(res) {
                if (res.code === 1) {
                    $this.toggleClass('active');
                    if (res.count !== undefined) {
                        $this.find('.count').text(res.count);
                    }
                    showToast(res.msg, 'success');
                } else {
                    showToast(res.msg, 'error');
                }
            },
            error: function() {
                showToast('操作失败，请先登录', 'error');
            }
        });
    });
}

function initPlayer() {
    if ($('.video-player').length > 0) {
        const player = new DPlayer({
            container: document.querySelector('.video-player'),
            video: {
                url: $('.video-player').data('url'),
                pic: $('.video-player').data('pic')
            },
            danmaku: {
                id: 'danmaku-' + $('.video-player').data('vod-id'),
                api: '/index.php/danmaku/api.html'
            }
        });
        
        recordPlayHistory($('.video-player').data('vod-id'), $('.video-player').data('episodes'));
    }
}

function recordPlayHistory(vodId, episodes) {
    $.ajax({
        url: '/index.php/user/play_history.html',
        type: 'POST',
        data: {
            vod_id: vodId,
            episodes: episodes
        }
    });
}

function initScrollEffects() {
    $(window).on('scroll', function() {
        const scrollTop = $(this).scrollTop();
        
        if (scrollTop > 100) {
            $('.header').addClass('scrolled');
        } else {
            $('.header').removeClass('scrolled');
        }
        
        if (scrollTop > 500) {
            $('.scroll-top').fadeIn();
        } else {
            $('.scroll-top').fadeOut();
        }
    });
    
    $('.scroll-top').on('click', function() {
        $('html, body').animate({scrollTop: 0}, 300);
    });
}

function initModal() {
    $('.modal-close, .modal-overlay').on('click', function() {
        $('.modal, .modal-overlay').removeClass('active');
    });
}

function showToast(message, type = 'info') {
    const toast = $('<div class="toast toast-' + type + '"></div>').text(message);
    $('body').append(toast);
    
    setTimeout(function() {
        toast.addClass('show');
    }, 10);
    
    setTimeout(function() {
        toast.removeClass('show');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

function playVideo(url) {
    window.location.href = url;
}

function switchEpisode(vodId, episodes) {
    window.location.href = '/index.php/vod/play/id/' + vodId + '/episodes/' + episodes + '.html';
}

function reportPlayError(vodId) {
    $('.modal-overlay, .modal-error').addClass('active');
    $('.modal-error .vod-id').val(vodId);
}

function submitPlayError() {
    const vodId = $('.modal-error .vod-id').val();
    const content = $('.modal-error .error-content').val().trim();
    
    if (!content) {
        showToast('请输入错误描述', 'error');
        return;
    }
    
    $.ajax({
        url: '/index.php/vod/report_error.html',
        type: 'POST',
        data: {
            vod_id: vodId,
            content: content
        },
        dataType: 'json',
        success: function(res) {
            if (res.code === 1) {
                showToast(res.msg, 'success');
                $('.modal-overlay, .modal-error').removeClass('active');
            } else {
                showToast(res.msg, 'error');
            }
        },
        error: function() {
            showToast('提交失败', 'error');
        }
    });
}
