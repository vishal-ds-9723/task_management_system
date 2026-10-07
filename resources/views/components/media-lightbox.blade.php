{{-- Universal Interactive Media Lightbox Modal --}}
<div id="mediaLightboxModal" class="mlb-overlay" onclick="closeMediaLightbox(event)" style="display:none;" aria-hidden="true" role="dialog">
    <div class="mlb-dialog" onclick="event.stopPropagation()">
        {{-- Lightbox Header --}}
        <div class="mlb-header">
            <div class="mlb-meta">
                <div class="mlb-badge-wrap">
                    <span id="mlbBadge" class="mlb-badge"><i class="fa-solid fa-photo-film"></i> Creative Preview</span>
                </div>
                <div class="mlb-title-wrap">
                    <h3 id="mlbTitle" class="mlb-title">Media Preview</h3>
                    <span id="mlbClient" class="mlb-client"></span>
                </div>
            </div>
            <div class="mlb-actions">
                <a id="mlbTaskLink" href="#" class="mlb-btn mlb-btn-ghost" title="Open Task Details" style="display:none">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Open Task</span>
                </a>
                <a id="mlbDownloadBtn" href="#" download class="mlb-btn mlb-btn-primary" title="Download High-Res">
                    <i class="fa-solid fa-download"></i> <span>Download</span>
                </a>
                <button type="button" class="mlb-close-btn" onclick="closeMediaLightbox()" title="Close (Esc)" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        {{-- Lightbox Body --}}
        <div class="mlb-body">
            <div id="mlbMediaContainer" class="mlb-content">
                {{-- Dynamically injected <img> or <video> --}}
            </div>
        </div>
    </div>
</div>

<style>
/* ── Universal Media Lightbox ── */
.mlb-overlay {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(9, 13, 24, 0.94);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.25s;
}
.mlb-overlay.show {
    opacity: 1;
    visibility: visible;
}
.mlb-dialog {
    display: flex;
    flex-direction: column;
    max-width: 95vw;
    max-height: 95vh;
    width: auto;
    background: #0f172a;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.08);
    overflow: hidden;
    transform: scale(0.96) translateY(8px);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.mlb-overlay.show .mlb-dialog {
    transform: scale(1) translateY(0);
}
.mlb-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 20px;
    background: rgba(15, 23, 42, 0.95);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    flex-wrap: wrap;
}
.mlb-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
}
.mlb-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 10px;
    border-radius: 99px;
    background: rgba(79, 109, 240, 0.2);
    color: #93c5fd;
    border: 1px solid rgba(147, 197, 253, 0.3);
    white-space: nowrap;
}
.mlb-title-wrap {
    min-width: 0;
    flex: 1;
}
.mlb-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #f8fafc;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}
.mlb-client {
    display: inline-block;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
    font-weight: 500;
}
.mlb-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.mlb-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}
.mlb-btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
}
.mlb-btn-primary:hover {
    background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
    transform: translateY(-1px);
    color: #fff;
}
.mlb-btn-ghost {
    background: rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.mlb-btn-ghost:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    transform: translateY(-1px);
}
.mlb-close-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #f1f5f9;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mlb-close-btn:hover {
    background: rgba(239, 68, 68, 0.35);
    border-color: #ef4444;
    color: #fff;
    transform: rotate(90deg);
}
.mlb-body {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #020617;
    overflow: hidden;
    padding: 16px;
    max-height: calc(90vh - 70px);
}
.mlb-content {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.mlb-content img,
.mlb-content video {
    max-width: 90vw;
    max-height: calc(85vh - 80px);
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    display: block;
}
@media (max-width: 640px) {
    .mlb-overlay { padding: 10px; }
    .mlb-header { padding: 12px 14px; gap: 10px; }
    .mlb-title { max-width: 180px; }
    .mlb-btn span { display: none; }
    .mlb-btn { padding: 8px 10px; }
}
</style>

<script>
window.openMediaLightbox = function(optionsOrUrl, title, isVideo, taskUrl, downloadUrl) {
    let options = {};
    if (typeof optionsOrUrl === 'string') {
        options = {
            url: optionsOrUrl,
            title: title || 'Media Preview',
            isVideo: Boolean(isVideo),
            taskUrl: taskUrl || '',
            downloadUrl: downloadUrl || optionsOrUrl
        };
    } else if (typeof optionsOrUrl === 'object' && optionsOrUrl !== null) {
        options = Object.assign({}, optionsOrUrl);
    }

    if (!options || !options.url) return;

    const overlay = document.getElementById('mediaLightboxModal');
    const container = document.getElementById('mlbMediaContainer');
    const titleEl = document.getElementById('mlbTitle');
    const clientEl = document.getElementById('mlbClient');
    const badgeEl = document.getElementById('mlbBadge');
    const dlBtn = document.getElementById('mlbDownloadBtn');
    const taskLink = document.getElementById('mlbTaskLink');

    if (!overlay || !container) return;

    // Set meta info
    if (titleEl) titleEl.textContent = options.title || 'Media Preview';
    if (clientEl) {
        if (options.client) {
            clientEl.textContent = 'Client: ' + options.client;
            clientEl.style.display = 'inline-block';
        } else {
            clientEl.style.display = 'none';
        }
    }
    if (badgeEl) {
        badgeEl.innerHTML = options.isVideo
            ? '<i class="fa-solid fa-play"></i> Video Preview'
            : (options.isProof ? '<i class="fa-solid fa-check-circle"></i> Published Proof' : '<i class="fa-solid fa-image"></i> Creative Design');
    }

    // Set actions
    if (dlBtn) {
        dlBtn.href = options.downloadUrl || options.url;
        dlBtn.setAttribute('download', options.filename || 'preview');
    }
    if (taskLink) {
        if (options.taskUrl) {
            taskLink.href = options.taskUrl;
            taskLink.style.display = 'inline-flex';
        } else {
            taskLink.style.display = 'none';
        }
    }

    // Render media element
    container.innerHTML = '';
    if (options.isVideo) {
        const video = document.createElement('video');
        video.src = options.url;
        video.controls = true;
        video.autoplay = true;
        video.playsInline = true;
        container.appendChild(video);
    } else {
        const img = document.createElement('img');
        img.src = options.url;
        img.alt = options.title || 'Media Preview';
        img.loading = 'eager';
        container.appendChild(img);
    }

    // Show modal
    overlay.style.display = 'flex';
    requestAnimationFrame(() => {
        overlay.classList.add('show');
    });
    document.body.style.overflow = 'hidden';
};

window.closeMediaLightbox = function(event) {
    if (event && event.target && event.target !== document.getElementById('mediaLightboxModal') && !event.target.closest('.mlb-close-btn')) {
        return;
    }

    const overlay = document.getElementById('mediaLightboxModal');
    if (!overlay) return;

    overlay.classList.remove('show');
    setTimeout(() => {
        overlay.style.display = 'none';
        const container = document.getElementById('mlbMediaContainer');
        if (container) {
            const video = container.querySelector('video');
            if (video) video.pause();
            container.innerHTML = '';
        }
        document.body.style.overflow = '';
    }, 250);
};

// Keyboard ESC listener
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('mediaLightboxModal');
        if (overlay && overlay.classList.contains('show')) {
            window.closeMediaLightbox();
        }
    }
});
</script>
