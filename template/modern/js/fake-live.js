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
    }

    async init() {
        await this.loadStreamUrl();
        this.initPlayer();
        this.initInteraction();
        this.startAutoUpdates();
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
    }

    startAutoUpdates() {
        this.updateComments();
        this.updateStatistics();

        this.intervalId = setInterval(() => {
            if (this.isPlaying) {
                this.simulateViewerInteraction('view');
                if (Math.random() > 0.7) {
                    this.simulateViewerInteraction('comment');
                }
                if (Math.random() > 0.8) {
                    this.simulateViewerInteraction('like');
                }
            }
            this.updateComments();
            this.updateStatistics();
        }, 5000);
    }

    async simulateViewerInteraction(type) {
        try {
            await fetch(`/api/live/interaction?live_id=${this.liveId}&type=${type}`);
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

        if (viewerCount) viewerCount.textContent = data.viewers;
        if (likeCount) likeCount.textContent = data.likes;
        if (commentCount) commentCount.textContent = data.comments;
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

    destroy() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
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
