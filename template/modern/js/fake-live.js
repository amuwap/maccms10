/**
 * 无人假直播前端处理
 */
class FakeLive {
    constructor(liveId, container) {
        this.liveId = liveId;
        this.container = container;
        this.player = null;
        this.isPlaying = false;
        this.intervalId = null;
        this.comments = [];
        this.danmakus = [];
        this.gifts = [];
        this.virtualGifts = [];
        this.announcements = [];
        this.comboCount = 0;
        this.lastGiftTime = 0;
        this.userLevel = 1;
        this.lotteryTimer = null;
    }

    async init() {
        await this.loadVirtualGifts();
        await this.loadStreamUrl();
        this.initPlayer();
        this.initInteraction();
        this.startAutoUpdates();
    }

    async loadVirtualGifts() {
        try {
            const response = await fetch('/api/live/gifts');
            const data = await response.json();
            if (data.code === 1) {
                this.virtualGifts = data.data;
                this.renderGiftPanel();
            }
        } catch (error) {
            console.error('加载虚拟礼物失败:', error);
        }
    }

    renderGiftPanel() {
        const giftPanel = this.container.querySelector('.gift-panel');
        if (!giftPanel) return;

        giftPanel.innerHTML = '';
        this.virtualGifts.forEach(gift => {
            const giftItem = document.createElement('div');
            giftItem.className = 'gift-item';
            giftItem.dataset.giftId = gift.id;
            giftItem.innerHTML = `
                <span class="gift-icon">${gift.icon}</span>
                <span class="gift-name">${gift.name}</span>
                <span class="gift-price">${gift.price}</span>
            `;
            giftItem.addEventListener('click', () => this.sendGift(gift.id));
            giftPanel.appendChild(giftItem);
        });
    }

    async loadStreamUrl() {
        try {
            const response = await fetch(`/api/stream?live_id=${this.liveId}`);
            const data = await response.json();
            if (data.code === 1) {
                this.streamUrl = data.url;
            } else {
                console.error('获取直播流失败:', data.msg);
            }
        } catch (error) {
            console.error('加载直播流失败:', error);
        }
    }

    initPlayer() {
        if (!this.streamUrl) return;

        const videoElement = this.container.querySelector('video');
        if (videoElement) {
            videoElement.src = this.streamUrl;
            videoElement.controls = true;
            videoElement.autoplay = true;
            videoElement.muted = false;

            videoElement.addEventListener('play', () => {
                this.isPlaying = true;
                this.simulateViewerInteraction('view');
            });

            videoElement.addEventListener('pause', () => {
                this.isPlaying = false;
            });

            this.player = videoElement;
        }
    }

    initInteraction() {
        const likeBtn = this.container.querySelector('.like-btn');
        if (likeBtn) {
            likeBtn.addEventListener('click', () => {
                this.simulateViewerInteraction('like');
                likeBtn.classList.add('active');
                setTimeout(() => {
                    likeBtn.classList.remove('active');
                }, 500);
            });
        }

        const commentForm = this.container.querySelector('.comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const commentInput = commentForm.querySelector('input');
                const content = commentInput.value.trim();
                if (content) {
                    this.addUserComment(content);
                    commentInput.value = '';
                }
            });
        }

        const danmakuForm = this.container.querySelector('.danmaku-form');
        if (danmakuForm) {
            danmakuForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const danmakuInput = danmakuForm.querySelector('input');
                const content = danmakuInput.value.trim();
                if (content) {
                    this.sendDanmaku(content);
                    danmakuInput.value = '';
                }
            });
        }
    }

    async sendGift(giftId, count = 1) {
        try {
            const response = await fetch('/api/live/send_gift', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `live_id=${this.liveId}&gift_id=${giftId}&count=${count}`
            });
            const data = await response.json();
            if (data.code === 1) {
                this.showGiftAnimation(data.data);
                this.updateCombo();
                this.triggerLottery();
            }
        } catch (error) {
            console.error('发送礼物失败:', error);
        }
    }

    updateCombo() {
        const now = Date.now();
        if (now - this.lastGiftTime < 3000) {
            this.comboCount++;
        } else {
            this.comboCount = 1;
        }
        this.lastGiftTime = now;
        this.showComboEffect();
    }

    showComboEffect() {
        if (this.comboCount >= 3) {
            const comboElement = this.container.querySelector('.combo-effect');
            if (comboElement) {
                comboElement.textContent = `${this.comboCount}连击!`;
                comboElement.classList.add('show');
                setTimeout(() => {
                    comboElement.classList.remove('show');
                }, 2000);
            }
        }
    }

    triggerLottery() {
        if (Math.random() > 0.9) {
            this.startLottery();
        }
    }

    startLottery() {
        const lotteryContainer = this.container.querySelector('.lottery-container');
        if (lotteryContainer) {
            const prizes = ['再来一次', '鲜花x10', '爱心x5', '火箭x1', '钻石x2', '谢谢参与'];
            let count = 0;
            const maxCount = 20;
            
            if (this.lotteryTimer) clearInterval(this.lotteryTimer);
            
            this.lotteryTimer = setInterval(() => {
                const prize = prizes[Math.floor(Math.random() * prizes.length)];
                lotteryContainer.textContent = prize;
                count++;
                
                if (count >= maxCount) {
                    clearInterval(this.lotteryTimer);
                    const finalPrize = prizes[Math.floor(Math.random() * prizes.length)];
                    lotteryContainer.textContent = `恭喜获得: ${finalPrize}`;
                    lotteryContainer.classList.add('win');
                    setTimeout(() => {
                        lotteryContainer.classList.remove('win');
                        lotteryContainer.textContent = '';
                    }, 3000);
                }
            }, 100);
            
            lotteryContainer.classList.add('show');
        }
    }

    showGiftAnimation(giftData) {
        const giftAnimation = this.container.querySelector('.gift-animation');
        if (giftAnimation) {
            const gift = this.virtualGifts.find(g => g.id === giftData.gift_id);
            if (gift) {
                const isSuperGift = gift.price >= 50;
                giftAnimation.innerHTML = `
                    <div class="gift-effect ${isSuperGift ? 'super-gift' : ''}">
                        <span class="gift-icon">${gift.icon}</span>
                        <span class="gift-username">${giftData.username}</span>
                        <span class="gift-name">送出了 ${gift.name} x${giftData.count}</span>
                        ${this.comboCount >= 3 ? `<span class="combo-badge">x${this.comboCount}连击</span>` : ''}
                    </div>
                `;
                giftAnimation.classList.add('show');
                setTimeout(() => {
                    giftAnimation.classList.remove('show');
                }, isSuperGift ? 5000 : 3000);
            }
        }
    }

    async sendDanmaku(content) {
        try {
            const response = await fetch('/api/live/send_danmaku', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `live_id=${this.liveId}&content=${encodeURIComponent(content)}`
            });
            const data = await response.json();
            if (data.code === 1) {
                this.showDanmaku(data.data);
            }
        } catch (error) {
            console.error('发送弹幕失败:', error);
        }
    }

    showDanmaku(danmakuData) {
        const danmakuContainer = this.container.querySelector('.danmaku-container');
        if (danmakuContainer) {
            const danmakuElement = document.createElement('div');
            danmakuElement.className = 'danmaku-item';
            danmakuElement.style.color = danmakuData.color;
            danmakuElement.style.top = Math.random() * 70 + 10 + '%';
            danmakuElement.style.animationDuration = (Math.random() * 5 + 5) + 's';
            danmakuElement.textContent = `${danmakuData.username}: ${danmakuData.content}`;
            danmakuContainer.appendChild(danmakuElement);

            setTimeout(() => {
                danmakuElement.remove();
            }, 10000);
        }
    }

    startAutoUpdates() {
        this.updateComments();
        this.updateStatistics();
        this.updateDanmakus();
        this.updateGifts();
        this.updateAnnouncements();
        this.updateViewerDiversity();
        this.renderGiftRanking();

        this.intervalId = setInterval(() => {
            if (this.isPlaying) {
                this.simulateViewerInteraction('view');
                if (Math.random() > 0.7) {
                    this.simulateViewerInteraction('comment');
                }
                if (Math.random() > 0.8) {
                    this.simulateViewerInteraction('like');
                }
                if (Math.random() > 0.9) {
                    this.simulateViewerInteraction('danmaku');
                }
                if (Math.random() > 0.95) {
                    this.simulateViewerInteraction('gift');
                }
            }
            this.updateComments();
            this.updateStatistics();
            this.updateDanmakus();
            this.updateGifts();
            this.updateAnnouncements();
            this.updateViewerDiversity();
        }, 3000);
    }

    async simulateViewerInteraction(type) {
        try {
            await fetch(`/api/live/auto_interaction?live_id=${this.liveId}&type=${type}`, {
                method: 'POST'
            });
        } catch (error) {
            console.error('模拟互动失败:', error);
        }
    }

    async updateComments() {
        try {
            const response = await fetch(`/api/live/comments?live_id=${this.liveId}`);
            const data = await response.json();
            if (data.code === 1) {
                this.comments = data.data;
                this.renderComments();
            }
        } catch (error) {
            console.error('更新评论失败:', error);
        }
    }

    async updateDanmakus() {
        try {
            const response = await fetch(`/api/live/get_danmaku?live_id=${this.liveId}`);
            const data = await response.json();
            if (data.code === 1) {
                const newDanmakus = data.data.filter(d => !this.danmakus.find(od => od.id === d.id));
                newDanmakus.forEach(danmaku => this.showDanmaku(danmaku));
                this.danmakus = data.data;
            }
        } catch (error) {
            console.error('更新弹幕失败:', error);
        }
    }

    async updateGifts() {
        try {
            const response = await fetch(`/api/live/get_gifts?live_id=${this.liveId}`);
            const data = await response.json();
            if (data.code === 1) {
                const newGifts = data.data.filter(g => !this.gifts.find(og => og.id === g.id));
                newGifts.forEach(gift => this.showGiftAnimation(gift));
                this.gifts = data.data;
                this.renderGiftRanking();
            }
        } catch (error) {
            console.error('更新礼物失败:', error);
        }
    }

    async updateAnnouncements() {
        try {
            const response = await fetch(`/api/live/get_announcements?live_id=${this.liveId}`);
            const data = await response.json();
            if (data.code === 1 && data.data.length > 0) {
                data.data.forEach(announcement => {
                    this.showAnnouncement(announcement);
                });
            }
        } catch (error) {
            console.error('更新公告失败:', error);
        }
    }

    showAnnouncement(announcement) {
        const announcementContainer = this.container.querySelector('.announcement-container');
        if (announcementContainer) {
            const announcementElement = document.createElement('div');
            announcementElement.className = 'announcement-item';
            announcementElement.textContent = announcement.content;
            announcementContainer.appendChild(announcementElement);

            setTimeout(() => {
                announcementElement.remove();
            }, 5000);
        }
    }

    async updateStatistics() {
        try {
            const response = await fetch(`/api/live/statistics?live_id=${this.liveId}`);
            const data = await response.json();
            if (data.code === 1) {
                this.renderStatistics(data.data);
            }
        } catch (error) {
            console.error('更新统计失败:', error);
        }
    }

    renderComments() {
        const commentList = this.container.querySelector('.comment-list');
        if (commentList) {
            commentList.innerHTML = '';
            this.comments.forEach(comment => {
                const commentItem = document.createElement('div');
                commentItem.className = 'comment-item';
                commentItem.innerHTML = `
                    <span class="username">${comment.username}</span>
                    <span class="content">${comment.content}</span>
                    <span class="time">${this.formatTime(comment.time)}</span>
                `;
                commentList.appendChild(commentItem);
            });
            commentList.scrollTop = commentList.scrollHeight;
        }
    }

    renderStatistics(data) {
        const viewerCount = this.container.querySelector('.viewer-count');
        const likeCount = this.container.querySelector('.like-count');
        const commentCount = this.container.querySelector('.comment-count');
        const giftCount = this.container.querySelector('.gift-count');

        if (viewerCount) viewerCount.textContent = data.viewers;
        if (likeCount) likeCount.textContent = data.likes;
        if (commentCount) commentCount.textContent = data.comments;
        if (giftCount) giftCount.textContent = (data.gifts || 0);
    }

    renderGiftRanking() {
        try {
            fetch(`/api/live/gift_ranking?live_id=${this.liveId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 1) {
                        const rankingList = this.container.querySelector('.gift-ranking');
                        if (rankingList) {
                            rankingList.innerHTML = '';
                            data.data.forEach((item, index) => {
                                const rankingItem = document.createElement('div');
                                rankingItem.className = 'ranking-item';
                                rankingItem.innerHTML = `
                                    <span class="rank">${index + 1}</span>
                                    <span class="username">${item.username}</span>
                                    <span class="total">${item.total}</span>
                                `;
                                rankingList.appendChild(rankingItem);
                            });
                        }
                    }
                });
        } catch (error) {
            console.error('获取礼物排行失败:', error);
        }
    }

    addUserComment(content) {
        const commentList = this.container.querySelector('.comment-list');
        if (commentList) {
            const commentItem = document.createElement('div');
            commentItem.className = 'comment-item user-comment';
            commentItem.innerHTML = `
                <span class="username">我</span>
                <span class="content">${content}</span>
                <span class="time">刚刚</span>
            `;
            commentList.appendChild(commentItem);
            commentList.scrollTop = commentList.scrollHeight;
        }
    }

    formatTime(timestamp) {
        const now = Date.now() / 1000;
        const diff = now - timestamp;

        if (diff < 60) return '刚刚';
        if (diff < 3600) return `${Math.floor(diff / 60)}分钟前`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}小时前`;
        return `${Math.floor(diff / 86400)}天前`;
    }

    async updateViewerDiversity() {
        try {
            const response = await fetch(`/api/live/viewer_diversity?live_id=${this.liveId}`);
            const data = await response.json();
            if (data.code === 1) {
                this.renderViewerDiversity(data.data);
            }
        } catch (error) {
            console.error('更新观众多样性失败:', error);
        }
    }

    renderViewerDiversity(diversity) {
        const diversityContainer = this.container.querySelector('.viewer-diversity');
        if (diversityContainer) {
            diversityContainer.innerHTML = '';
            for (const [key, value] of Object.entries(diversity)) {
                const item = document.createElement('div');
                item.className = 'diversity-item';
                item.innerHTML = `
                    <span class="diversity-name">${value.name}</span>
                    <span class="diversity-count">${value.count}</span>
                    <div class="diversity-bar">
                        <div class="diversity-progress" style="width: ${value.percentage}%"></div>
                    </div>
                `;
                diversityContainer.appendChild(item);
            }
        }
    }

    updateUserLevel(points) {
        const levels = [
            { level: 1, name: '新手', minPoints: 0 },
            { level: 2, name: '学徒', minPoints: 100 },
            { level: 3, name: '粉丝', minPoints: 500 },
            { level: 4, name: '铁粉', minPoints: 1000 },
            { level: 5, name: '钻石粉', minPoints: 5000 },
            { level: 6, name: '至尊粉', minPoints: 10000 }
        ];

        for (let i = levels.length - 1; i >= 0; i--) {
            if (points >= levels[i].minPoints) {
                this.userLevel = levels[i].level;
                this.showLevelUpgrade(levels[i]);
                break;
            }
        }
    }

    showLevelUpgrade(levelInfo) {
        const levelBadge = this.container.querySelector('.user-level');
        if (levelBadge) {
            levelBadge.textContent = `Lv.${levelInfo.level} ${levelInfo.name}`;
            levelBadge.classList.add('upgrade');
            setTimeout(() => {
                levelBadge.classList.remove('upgrade');
            }, 2000);
        }
    }

    destroy() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
        if (this.lotteryTimer) {
            clearInterval(this.lotteryTimer);
        }
        if (this.player) {
            this.player.pause();
        }
    }
}

// 初始化无人假直播
function initFakeLive(liveId, containerSelector) {
    const container = document.querySelector(containerSelector);
    if (container) {
        const fakeLive = new FakeLive(liveId, container);
        fakeLive.init();
        return fakeLive;
    }
    return null;
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    const liveId = document.querySelector('meta[name="live-id"]')?.content;
    if (liveId) {
        initFakeLive(liveId, '.fake-live-container');
    }
});
