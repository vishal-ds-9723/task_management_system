@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Publishing Command Queue Styling ─────────────────────────── */
.pq-container {
    max-width: 1600px;
    margin: 0 auto;
    padding-bottom: 60px;
}

/* Page Header */
.pq-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.pq-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.pq-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--primary) 0%, #6882F5 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    box-shadow: 0 8px 20px rgba(79, 109, 240, 0.28);
}
.pq-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.4px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    line-height: 1.2;
}
.pq-subtitle {
    font-size: 13px;
    color: var(--text3);
    margin-top: 4px;
    font-weight: 500;
}
.pq-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Stats Overview Grid */
.pq-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.pq-stat {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.pq-stat:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}
.pq-stat-active {
    border-color: var(--primary) !important;
    background: var(--card);
    box-shadow: 0 0 0 3px rgba(79, 109, 240, 0.12), var(--shadow);
}
.pq-stat-active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary);
}
.pq-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    transition: transform 0.2s;
}
.pq-stat:hover .pq-stat-icon {
    transform: scale(1.08);
}
.pq-stat-val {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.1;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.pq-stat-label {
    font-size: 11px;
    color: var(--text3);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 3px;
}
.pq-stat-pill {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 9.5px;
    font-weight: 800;
    margin-left: 4px;
    vertical-align: middle;
}

/* Control & Filter Bar */
.pq-filter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 18px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    box-shadow: var(--shadow-sm);
}
.pq-filter-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}
.pq-search-wrap {
    position: relative;
    min-width: 260px;
    max-width: 380px;
    flex: 1;
}
.pq-search-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text3);
    font-size: 13px;
    pointer-events: none;
}
.pq-search-wrap input {
    width: 100%;
    height: 38px;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0 12px 0 34px;
    font-size: 13px;
    background: var(--bg);
    color: var(--text);
    transition: all 0.2s;
    outline: none;
}
.pq-search-wrap input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 109, 240, 0.1);
    background: var(--card);
}
.pq-filter-select {
    height: 38px;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0 12px;
    font-size: 12.5px;
    background: var(--bg);
    color: var(--text);
    transition: border-color 0.2s;
    outline: none;
    font-weight: 600;
    cursor: pointer;
}
.pq-filter-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 109, 240, 0.1);
}
.pq-filter-right {
    display: flex;
    align-items: center;
    gap: 8px;
}
.pq-view-toggle {
    display: inline-flex;
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 3px;
}
.pq-view-btn {
    padding: 5px 11px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: var(--text3);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.pq-view-btn.active {
    background: var(--card);
    color: var(--primary);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* Generic Buttons */
.pq-btn {
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    white-space: nowrap;
}
.pq-btn-primary {
    background: var(--primary);
    color: #fff;
}
.pq-btn-primary:hover {
    background: var(--primary-hover);
    box-shadow: 0 3px 10px rgba(79, 109, 240, 0.25);
    transform: translateY(-1px);
}
.pq-btn-outline {
    background: var(--card);
    color: var(--text2);
    border: 1px solid var(--border);
}
.pq-btn-outline:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.pq-btn-ghost {
    background: transparent;
    color: var(--text3);
}
.pq-btn-ghost:hover {
    color: var(--primary);
    background: var(--primary-dim);
}

/* ── Rich Card View ─────────────────────────────── */
.pq-cards-wrap {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.pq-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}
.pq-card:hover {
    border-color: rgba(79, 109, 240, 0.35);
    box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
}
.pq-card-overdue {
    border-left: 4px solid #EF4444;
}
.pq-card-published {
    border-left: 4px solid #10B981;
}

/* Card Top Strip */
.pq-card-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    background: var(--card2);
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 10px;
}
.pq-topbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.pq-client-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--card);
    border: 1px solid var(--border);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
}
.pq-client-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--primary);
}
.pq-type-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.pq-type-post { background: rgba(59, 130, 246, 0.12); color: #2563EB; }
.pq-type-reel { background: rgba(168, 85, 247, 0.12); color: #9333EA; }
.pq-type-story { background: rgba(245, 158, 11, 0.12); color: #D97706; }
.pq-type-video { background: rgba(239, 68, 68, 0.12); color: #DC2626; }
.pq-creator-tag {
    font-size: 11.5px;
    color: var(--text3);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.pq-topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* Date Pills */
.pq-date-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 8px;
}
.pq-date-overdue {
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
    animation: pqPulse 2s infinite ease-in-out;
}
.pq-date-today {
    background: rgba(79, 109, 240, 0.1);
    color: var(--primary);
    border: 1px solid rgba(79, 109, 240, 0.25);
    font-weight: 800;
}
.pq-date-upcoming {
    background: var(--card);
    color: var(--text2);
    border: 1px solid var(--border);
}
.pq-date-published {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.2);
}
@keyframes pqPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.75; }
}

/* Client Approval Toggle */
.pq-client-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--card);
    padding: 3px 6px 3px 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
}
.pq-client-status {
    font-size: 11.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.pq-client-status.is-yes { color: #059669; }
.pq-client-status.is-no { color: #EF4444; }
.pq-client-toggle-btn {
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.18s;
    background: var(--card2);
    color: var(--text2);
}
.pq-client-toggle-btn:hover {
    transform: translateY(-1px);
}
.pq-client-toggle-btn.is-no {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border-color: rgba(16, 185, 129, 0.3);
}
.pq-client-toggle-btn.is-no:hover {
    background: #10B981;
    color: #fff;
}
.pq-client-toggle-btn.is-yes {
    background: var(--card2);
    color: var(--text3);
}
.pq-client-toggle-btn.is-yes:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #EF4444;
    border-color: rgba(239, 68, 68, 0.3);
}

/* Card Main Body Grid */
.pq-card-body {
    display: grid;
    grid-template-columns: 200px 1fr 240px;
    gap: 22px;
    padding: 20px;
    align-items: stretch;
}

/* Column 1: Media Showcase */
.pq-media-col {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.pq-thumb-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 1/1;
    border-radius: 14px;
    overflow: hidden;
    background: var(--card2);
    border: 1px solid var(--border);
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 0.25s ease;
}
.pq-thumb-wrapper:hover {
    transform: scale(1.03);
    border-color: var(--primary);
    box-shadow: 0 6px 20px rgba(79, 109, 240, 0.25);
}
.pq-thumb-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.pq-thumb-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(79, 109, 240, 0.08) 0%, rgba(104, 130, 245, 0.04) 100%);
    color: var(--text3);
}
.pq-thumb-fallback i {
    font-size: 28px;
    color: var(--primary);
    opacity: 0.7;
}
.pq-thumb-fallback span {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.pq-thumb-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    opacity: 0;
    transition: opacity 0.2s;
    backdrop-filter: blur(2px);
}
.pq-thumb-wrapper:hover .pq-thumb-overlay {
    opacity: 1;
}
.pq-thumb-badge-video {
    position: absolute;
    bottom: 6px;
    right: 6px;
    background: rgba(0, 0, 0, 0.8);
    color: #fff;
    font-size: 9px;
    font-weight: 800;
    padding: 3px 6px;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.pq-thumb-badge-count {
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(15, 23, 42, 0.85);
    color: #fff;
    font-size: 9.5px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 5px;
}
.pq-media-subactions {
    display: flex;
    gap: 6px;
}
.pq-media-subbtn {
    flex: 1;
    background: var(--card2);
    border: 1px solid var(--border);
    color: var(--text2);
    padding: 5px 8px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    transition: all 0.2s;
}
.pq-media-subbtn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-dim);
}

/* Column 2: Content, Copywriting & Hook */
.pq-content-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0;
}
.pq-task-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
    line-height: 1.35;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.pq-task-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}
.pq-task-title a:hover {
    color: var(--primary);
}

/* Headline Hook Callout */
.pq-hook-box {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 9px 12px;
    background: rgba(79, 109, 240, 0.05);
    border: 1px dashed rgba(79, 109, 240, 0.3);
    border-radius: 10px;
    font-size: 12.5px;
    color: var(--text);
}
.pq-hook-icon {
    color: var(--primary);
    font-size: 13px;
    margin-top: 2px;
}
.pq-hook-text strong {
    color: var(--primary);
    font-weight: 800;
    margin-right: 4px;
}

/* Marketing Copy Box */
.pq-caption-box {
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 14px;
    position: relative;
}
.pq-caption-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 8px;
}
.pq-caption-label {
    font-size: 10.5px;
    font-weight: 800;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.pq-copy-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.pq-copy-chip {
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text2);
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.pq-copy-chip:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-dim);
}
.pq-copy-chip.copied {
    background: #10B981 !important;
    color: #fff !important;
    border-color: #10B981 !important;
}
.pq-caption-text {
    font-size: 12.5px;
    color: var(--text);
    line-height: 1.55;
    white-space: pre-wrap;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: all 0.25s;
}
.pq-caption-text.expanded {
    -webkit-line-clamp: unset;
    overflow: visible;
}
.pq-caption-empty {
    font-size: 12px;
    color: var(--text3);
    font-style: italic;
    padding: 4px 0;
}
.pq-expand-btn {
    background: transparent;
    border: none;
    color: var(--primary);
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    padding: 4px 0 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.pq-expand-btn:hover {
    text-decoration: underline;
}

/* Hashtags Cloud */
.pq-hashtags-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid var(--border);
}
.pq-hashtag-lead {
    font-size: 11px;
    color: var(--primary);
    margin-top: 3px;
}
.pq-tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.pq-tag-pill {
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--primary);
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 6px;
}

/* Platforms Row */
.pq-platforms-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 4px;
}
.pq-platforms-label {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text3);
}
.pq-platform-pills {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.pq-platform-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text2);
}
.pq-platform-pill.is-posted {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border-color: rgba(16, 185, 129, 0.25);
}
.pq-posted-icon {
    font-size: 10px;
    color: #059669;
}
.pq-live-link {
    color: var(--primary);
    text-decoration: none;
    margin-left: 2px;
}
.pq-live-link:hover {
    color: var(--primary-hover);
}

/* Column 3: Actions Hub */
.pq-actions-col {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 14px;
    border-left: 1px solid var(--border);
    padding-left: 20px;
}
.pq-status-box {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.pq-status-title {
    font-size: 10.5px;
    font-weight: 800;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.pq-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    width: 100%;
    justify-content: center;
}
.pq-status-badge.is-published {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.pq-status-badge.is-ready {
    background: rgba(245, 158, 11, 0.12);
    color: #D97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}
.pq-status-badge.is-overdue {
    background: rgba(239, 68, 68, 0.12);
    color: #EF4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.pq-btn-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: auto;
}
.pq-btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    width: 100%;
}
.pq-btn-publish {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
}
.pq-btn-publish:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
    transform: translateY(-1px);
}
.pq-btn-revert {
    background: var(--card2);
    color: var(--text3);
    border: 1px solid var(--border);
}
.pq-btn-revert:hover {
    border-color: rgba(239, 68, 68, 0.4);
    color: #EF4444;
    background: rgba(239, 68, 68, 0.05);
}
.pq-btn-inspect {
    background: var(--card);
    color: var(--text);
    border: 1px solid var(--border);
}
.pq-btn-inspect:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-dim);
}
.pq-btn-hub {
    background: var(--card2);
    color: var(--text2);
    border: 1px solid var(--border);
}
.pq-btn-hub:hover {
    border-color: var(--primary);
    color: var(--primary);
}

/* ── Compact Table View ────────────────────────────── */
.pq-table-wrap {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.pq-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.pq-table th {
    background: var(--card2);
    padding: 12px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 800;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border);
}
.pq-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.pq-table tr:last-child td {
    border-bottom: none;
}
.pq-table tr:hover td {
    background: rgba(79, 109, 240, 0.02);
}
.pq-table-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--card2);
    border: 1px solid var(--border);
    cursor: pointer;
    flex-shrink: 0;
}
.pq-table-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ── Slide-Over Drawer ─────────────────────────────── */
.pq-drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1050;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
}
.pq-drawer-backdrop.active {
    opacity: 1;
    pointer-events: auto;
}
.pq-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 580px;
    max-width: 90vw;
    background: var(--card);
    box-shadow: -8px 0 32px rgba(0, 0, 0, 0.18);
    z-index: 1051;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
}
.pq-drawer-backdrop.active .pq-drawer {
    transform: translateX(0);
}
.pq-drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    background: var(--card2);
}
.pq-drawer-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.pq-drawer-close {
    background: transparent;
    border: none;
    color: var(--text3);
    font-size: 18px;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.pq-drawer-close:hover {
    background: var(--border);
    color: var(--text);
}
.pq-drawer-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.pq-drawer-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    background: var(--card2);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Floating Toast Notification */
.pq-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #0F172A;
    color: #fff;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    z-index: 1100;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}
.pq-toast.active {
    transform: translateY(0);
    opacity: 1;
}
.pq-toast i {
    color: #10B981;
    font-size: 15px;
}

/* Empty State */
.pq-empty {
    text-align: center;
    padding: 80px 20px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
}
.pq-empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: var(--card2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: var(--text3);
    margin: 0 auto 16px;
}
.pq-empty-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 6px;
}
.pq-empty-text {
    font-size: 13px;
    color: var(--text3);
    max-width: 360px;
    margin: 0 auto;
}

/* Responsive adjustments */
@media(max-width: 1200px) {
    .pq-card-body {
        grid-template-columns: 180px 1fr;
    }
    .pq-actions-col {
        grid-column: 1 / -1;
        border-left: none;
        border-top: 1px solid var(--border);
        padding-left: 0;
        padding-top: 16px;
        flex-direction: row;
        align-items: center;
    }
    .pq-status-box {
        flex: 1;
    }
    .pq-btn-group {
        flex-direction: row;
        flex: 2;
        margin-top: 0;
    }
}
@media(max-width: 800px) {
    .pq-card-body {
        grid-template-columns: 1fr;
    }
    .pq-actions-col {
        flex-direction: column;
    }
    .pq-btn-group {
        flex-direction: column;
        width: 100%;
    }
    .pq-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .pq-search-wrap {
        max-width: 100%;
    }
    .pq-filter-right {
        justify-content: flex-end;
    }
}
</style>
@endpush

@section('content')
<div class="pq-container">

    {{-- Page Header --}}
    <div class="pq-header">
        <div class="pq-header-left">
            <div class="pq-icon-wrap"><i class="fa-solid fa-share-nodes"></i></div>
            <div>
                <div class="pq-title">Publishing Command Queue</div>
                <div class="pq-subtitle">Verify creative artwork, copy marketing captions, review client sign-offs & publish live</div>
            </div>
        </div>
        <div class="pq-header-actions">
            <a href="{{ route('strategist.tracking') }}" class="pq-btn pq-btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Back to Tracking
            </a>
        </div>
    </div>

    {{-- Interactive Stats Bar --}}
    <div class="pq-stats">
        <a href="{{ route('strategist.publishing') }}" class="pq-stat {{ !request('filter') ? 'pq-stat-active' : '' }}" title="View all tasks in publishing queue">
            <div class="pq-stat-icon" style="background:var(--primary-dim);color:var(--primary)">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="pq-stat-val">{{ $totalCount ?? $totalCompleted }}</div>
                <div class="pq-stat-label">All In Queue</div>
            </div>
        </a>

        <a href="{{ route('strategist.publishing', ['filter' => 'pending']) }}" class="pq-stat {{ in_array(request('filter'), ['pending', 'awaiting']) ? 'pq-stat-active' : '' }}" title="Tasks ready to be published">
            <div class="pq-stat-icon" style="background:rgba(245, 158, 11, 0.12);color:#D97706">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <div class="pq-stat-val">{{ $pendingCount ?? $awaitingCount }}</div>
                <div class="pq-stat-label">Ready to Publish</div>
            </div>
        </a>

        <a href="{{ route('strategist.publishing', ['filter' => 'overdue']) }}" class="pq-stat {{ request('filter') === 'overdue' ? 'pq-stat-active' : '' }}" title="Tasks past scheduled post date">
            <div class="pq-stat-icon" style="background:rgba(239, 68, 68, 0.12);color:#EF4444">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="pq-stat-val" style="color:#EF4444">{{ $overdueCount ?? 0 }}</div>
                <div class="pq-stat-label">Due / Overdue</div>
            </div>
        </a>

        <a href="{{ route('strategist.publishing', ['filter' => 'today']) }}" class="pq-stat {{ request('filter') === 'today' ? 'pq-stat-active' : '' }}" title="Scheduled for posting today">
            <div class="pq-stat-icon" style="background:rgba(79, 109, 240, 0.12);color:var(--primary)">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <div class="pq-stat-val" style="color:var(--primary)">{{ $todayCount ?? 0 }}</div>
                <div class="pq-stat-label">Post Today</div>
            </div>
        </a>

        <a href="{{ route('strategist.publishing', ['filter' => 'client_yes']) }}" class="pq-stat {{ request('filter') === 'client_yes' ? 'pq-stat-active' : '' }}" title="Tasks signed off by client">
            <div class="pq-stat-icon" style="background:rgba(16, 185, 129, 0.12);color:#059669">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="pq-stat-val" style="color:#059669">{{ $clientApprovedCount ?? 0 }}</div>
                <div class="pq-stat-label">Client: Yes</div>
            </div>
        </a>

        <a href="{{ route('strategist.publishing', ['filter' => 'published']) }}" class="pq-stat {{ request('filter') === 'published' ? 'pq-stat-active' : '' }}" title="Tasks successfully published">
            <div class="pq-stat-icon" style="background:rgba(20, 184, 166, 0.12);color:#0D9488">
                <i class="fa-solid fa-rocket"></i>
            </div>
            <div>
                <div class="pq-stat-val" style="color:#0D9488">{{ $publishedCount }}</div>
                <div class="pq-stat-label">Published Live</div>
            </div>
        </a>
    </div>

    {{-- Control & Filter Bar --}}
    <form class="pq-filter-bar" method="GET" id="pqFilterForm">
        <div class="pq-filter-left">
            <div class="pq-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, caption, hashtags, client..." autocomplete="off">
            </div>

            <select name="filter" class="pq-filter-select" onchange="this.form.submit()">
                <option value="" {{ !request('filter') ? 'selected' : '' }}>All Statuses</option>
                <option value="pending" {{ in_array(request('filter'), ['pending', 'awaiting']) ? 'selected' : '' }}>Ready to Publish</option>
                <option value="overdue" {{ request('filter') === 'overdue' ? 'selected' : '' }}>⚠️ Due / Overdue</option>
                <option value="today" {{ request('filter') === 'today' ? 'selected' : '' }}>⚡ Due Today</option>
                <option value="client_yes" {{ request('filter') === 'client_yes' ? 'selected' : '' }}>Client Approved: Yes</option>
                <option value="client_no" {{ request('filter') === 'client_no' ? 'selected' : '' }}>Client Approved: No</option>
                <option value="published" {{ request('filter') === 'published' ? 'selected' : '' }}>Published Live</option>
            </select>

            <select name="client" class="pq-filter-select" onchange="this.form.submit()">
                <option value="">All Clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                @endforeach
            </select>

            <select name="platform" class="pq-filter-select" onchange="this.form.submit()">
                <option value="">All Platforms</option>
                <option value="instagram" {{ request('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                <option value="facebook" {{ request('platform') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="linkedin" {{ request('platform') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                <option value="twitter" {{ request('platform') === 'twitter' ? 'selected' : '' }}>Twitter / X</option>
                <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
            </select>

            <button type="submit" class="pq-btn pq-btn-primary">
                <i class="fa-solid fa-filter"></i> Apply
            </button>

            @if(request()->hasAny(['search', 'client', 'filter', 'platform']))
                <a href="{{ route('strategist.publishing') }}" class="pq-btn pq-btn-ghost" title="Reset all filters">
                    <i class="fa-solid fa-xmark"></i> Reset
                </a>
            @endif
        </div>

        <div class="pq-filter-right">
            <div class="pq-view-toggle">
                <button type="button" class="pq-view-btn active" id="viewBtnCards" onclick="switchViewMode('cards')" title="Rich visual cards view">
                    <i class="fa-solid fa-table-cells-large"></i> Cards
                </button>
                <button type="button" class="pq-view-btn" id="viewBtnTable" onclick="switchViewMode('table')" title="Compact table view">
                    <i class="fa-solid fa-list-check"></i> Table
                </button>
            </div>
        </div>
    </form>

    {{-- Content Layout Container --}}
    @php
        $platformIcons = [
            'instagram'  => 'fa-brands fa-instagram',
            'facebook'   => 'fa-brands fa-facebook-f',
            'linkedin'   => 'fa-brands fa-linkedin-in',
            'twitter'    => 'fa-brands fa-x-twitter',
            'tiktok'     => 'fa-brands fa-tiktok',
            'youtube'    => 'fa-brands fa-youtube',
            'pinterest'  => 'fa-brands fa-pinterest-p',
            'threads'    => 'fa-brands fa-threads',
            'whatsapp'   => 'fa-brands fa-whatsapp',
            'telegram'   => 'fa-brands fa-telegram',
            'reddit'     => 'fa-brands fa-reddit-alien',
            'discord'    => 'fa-brands fa-discord',
            'behance'    => 'fa-brands fa-behance',
            'dribbble'   => 'fa-brands fa-dribbble',
        ];
    @endphp

    @if($tasks->isEmpty())
        <div class="pq-empty">
            <div class="pq-empty-icon"><i class="fa-regular fa-paper-plane"></i></div>
            <div class="pq-empty-title">{{ request()->hasAny(['search', 'filter', 'client', 'platform']) ? 'No matching tasks' : 'Publishing Queue is Clear!' }}</div>
            <div class="pq-empty-text">
                {{ request()->hasAny(['search', 'filter', 'client', 'platform']) 
                    ? 'No tasks matched your search or filter criteria. Try resetting filters.' 
                    : 'There are currently no approved tasks waiting to be published. Check back when artwork is approved!' }}
            </div>
        </div>
    @else
        {{-- 1. Rich Visual Cards View --}}
        <div class="pq-cards-wrap" id="pqCardsContainer">
            @foreach($tasks as $task)
                @php
                    $isPublished = $task->status === 'published';
                    $postDateCarbon = $task->post_date ? \Illuminate\Support\Carbon::parse($task->post_date) : null;
                    $isOverdue = !$isPublished && $postDateCarbon && $postDateCarbon->isPast() && !$postDateCarbon->isToday();
                    $isToday = !$isPublished && $postDateCarbon && $postDateCarbon->isToday();
                    $isClientApproved = $task->isClientApproved();

                    // Platforms
                    $rawPlatforms = $task->platform;
                    if (is_string($rawPlatforms)) {
                        $decoded = json_decode($rawPlatforms, true);
                        $platforms = is_array($decoded) ? $decoded : (strlen(trim($rawPlatforms)) ? [trim($rawPlatforms)] : []);
                    } elseif (is_array($rawPlatforms)) {
                        $platforms = $rawPlatforms;
                    } else {
                        $platforms = [];
                    }

                    $postedPlatforms = $task->socialMediaPosts->pluck('platform')->toArray();
                    $thumb = $task->getPreviewThumbnail();
                    $hasThumb = !empty($thumb);
                    $detailUrl = route('strategist.publishing.show', $task);

                    $typeIcon = match(strtolower($task->type ?? 'post')) {
                        'reel' => 'fa-clapperboard',
                        'story' => 'fa-clock-rotate-left',
                        'video' => 'fa-video',
                        default => 'fa-image',
                    };
                @endphp

                <div class="pq-card {{ $isOverdue ? 'pq-card-overdue' : '' }} {{ $isPublished ? 'pq-card-published' : '' }}" id="task-card-{{ $task->id }}">
                    {{-- Hidden Storage for 1-Click Copy and Fast Inspection --}}
                    <div id="task-copy-data-{{ $task->id }}" 
                         data-caption="{{ $task->caption ?? '' }}" 
                         data-hashtags="{{ $task->hashtags ?? '' }}" 
                         style="display:none"></div>

                    {{-- Top Strip --}}
                    <div class="pq-card-topbar">
                        <div class="pq-topbar-left">
                            <span class="pq-client-badge">
                                <span class="pq-client-dot"></span>
                                <span>{{ $task->client->name ?? 'Internal Client' }}</span>
                            </span>

                            <span class="pq-type-pill pq-type-{{ strtolower($task->type ?: 'post') }}">
                                <i class="fa-solid {{ $typeIcon }}"></i> {{ strtoupper($task->type ?: 'POST') }}
                            </span>

                            @if($task->creator)
                                <span class="pq-creator-tag">
                                    <i class="fa-solid fa-user-pen"></i> By: {{ $task->creator->name }}
                                </span>
                            @endif

                            @if($task->completed_at)
                                <span class="pq-creator-tag" style="color:var(--text3)">
                                    <i class="fa-solid fa-check"></i> Approved {{ $task->completed_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>

                        <div class="pq-topbar-right">
                            {{-- Timing Pill --}}
                            @if($isPublished)
                                <span class="pq-date-pill pq-date-published">
                                    <i class="fa-solid fa-circle-check"></i> Published Live
                                </span>
                            @elseif($isOverdue)
                                <span class="pq-date-pill pq-date-overdue">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Due to Post ({{ $postDateCarbon->format('M d') }})
                                </span>
                            @elseif($isToday)
                                <span class="pq-date-pill pq-date-today">
                                    <i class="fa-solid fa-bolt"></i> Post Today
                                </span>
                            @elseif($postDateCarbon)
                                <span class="pq-date-pill pq-date-upcoming">
                                    <i class="fa-regular fa-calendar-check"></i> Post: {{ $postDateCarbon->format('M d, Y') }}
                                </span>
                            @endif

                            {{-- Client Approval Toggle Group --}}
                            <div class="pq-client-group">
                                <span class="pq-client-status {{ $isClientApproved ? 'is-yes' : 'is-no' }}" id="client-badge-{{ $task->id }}">
                                    <i class="fa-solid {{ $isClientApproved ? 'fa-check' : 'fa-xmark' }}"></i>
                                    Client: <strong>{{ $isClientApproved ? 'Yes' : 'No' }}</strong>
                                </span>
                                <button type="button" 
                                        class="pq-client-toggle-btn {{ $isClientApproved ? 'is-yes' : 'is-no' }}"
                                        id="client-toggle-btn-{{ $task->id }}"
                                        onclick="toggleClientApproval({{ $task->id }}, this)"
                                        title="{{ $isClientApproved ? 'Click to mark as No' : 'Click to mark as Yes' }}">
                                    {{ $isClientApproved ? 'Mark No' : 'Mark Yes' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Card Main Body --}}
                    <div class="pq-card-body">
                        {{-- 1. Media Preview Showcase --}}
                        <div class="pq-media-col">
                            <div class="pq-thumb-wrapper" 
                                 onclick="{{ $hasThumb ? "window.openMediaLightbox('".addslashes($thumb['full_url'])."', '".addslashes($task->title ?: $thumb['name'])."', ".($thumb['is_video'] ? 'true' : 'false').", '".$detailUrl."')" : "openQuickDrawer({$task->id})" }}"
                                 title="Click to view full preview">
                                @if($hasThumb)
                                    <img src="{{ $thumb['url'] }}" 
                                         alt="" 
                                         loading="lazy" 
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                    <div class="pq-thumb-fallback" style="display:none">
                                        <i class="fa-solid {{ $typeIcon }}"></i>
                                        <span>{{ strtoupper($task->type ?: 'POST') }}</span>
                                    </div>
                                    <div class="pq-thumb-overlay">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                                    </div>
                                    @if($thumb['is_video'])
                                        <span class="pq-thumb-badge-video"><i class="fa-solid fa-play"></i> Video</span>
                                    @endif
                                    @if(($thumb['count'] ?? 1) > 1)
                                        <span class="pq-thumb-badge-count">+{{ $thumb['count'] - 1 }} slides</span>
                                    @endif
                                @else
                                    <div class="pq-thumb-fallback">
                                        <i class="fa-solid {{ $typeIcon }}"></i>
                                        <span>{{ strtoupper($task->type ?: 'POST') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="pq-media-subactions">
                                @if($hasThumb)
                                    <button type="button" class="pq-media-subbtn" onclick="window.openMediaLightbox('{{ addslashes($thumb['full_url']) }}', '{{ addslashes($task->title ?: $thumb['name']) }}', {{ $thumb['is_video'] ? 'true' : 'false' }}, '{{ $detailUrl }}')">
                                        <i class="fa-solid fa-expand"></i> Zoom
                                    </button>
                                @endif
                                <button type="button" class="pq-media-subbtn" onclick="openQuickDrawer({{ $task->id }})">
                                    <i class="fa-solid fa-layer-group"></i> {{ $thumb['count'] ?? ($task->media ? $task->media->count() : 0) }} {{ ($thumb['count'] ?? ($task->media ? $task->media->count() : 0)) === 1 ? 'Asset' : 'Assets' }}
                                </button>
                            </div>
                        </div>

                        {{-- 2. Content Identity, Hook & Caption --}}
                        <div class="pq-content-col">
                            <h3 class="pq-task-title">
                                <a href="{{ $detailUrl }}">{{ $task->title ?: 'Untitled Task' }}</a>
                            </h3>

                            {{-- Headline Hook Callout --}}
                            @if($task->headline_copy)
                                <div class="pq-hook-box">
                                    <div class="pq-hook-icon"><i class="fa-solid fa-quote-left"></i></div>
                                    <div class="pq-hook-text">
                                        <strong>Visual Hook:</strong> "{{ $task->headline_copy }}"
                                    </div>
                                </div>
                            @endif

                            {{-- Marketing Caption Card --}}
                            <div class="pq-caption-box">
                                <div class="pq-caption-header">
                                    <span class="pq-caption-label">
                                        <i class="fa-solid fa-pen-nib"></i> Marketing Caption & Copy
                                    </span>
                                    <div class="pq-copy-actions">
                                        <button type="button" class="pq-copy-chip" onclick="copyPublishContent('all', {{ $task->id }}, this)" title="Copy Caption & Hashtags to clipboard">
                                            <i class="fa-regular fa-copy"></i> Copy All
                                        </button>
                                        @if($task->caption)
                                            <button type="button" class="pq-copy-chip" onclick="copyPublishContent('caption', {{ $task->id }}, this)" title="Copy Caption only">
                                                <i class="fa-regular fa-file-lines"></i> Copy Caption
                                            </button>
                                        @endif
                                        @if($task->hashtags)
                                            <button type="button" class="pq-copy-chip" onclick="copyPublishContent('tags', {{ $task->id }}, this)" title="Copy Hashtags only">
                                                <i class="fa-solid fa-hashtag"></i> Copy Tags
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if($task->caption)
                                    <div class="pq-caption-text" id="caption-text-{{ $task->id }}">{{ $task->caption }}</div>
                                    @if(strlen($task->caption) > 160)
                                        <button type="button" class="pq-expand-btn" onclick="toggleCaptionExpand({{ $task->id }}, this)">
                                            <span>Read full caption</span> <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                    @endif
                                @else
                                    <div class="pq-caption-empty">
                                        <i class="fa-regular fa-message"></i> No caption written yet. Click Quick Inspect or Hub to review brief.
                                    </div>
                                @endif

                                {{-- Hashtags Display --}}
                                @if($task->hashtags)
                                    <div class="pq-hashtags-row">
                                        <span class="pq-hashtag-lead"><i class="fa-solid fa-tags"></i></span>
                                        <div class="pq-tags-cloud">
                                            @foreach(preg_split('/[\s,]+/', trim($task->hashtags)) as $tag)
                                                @if(strlen($tag) > 1)
                                                    <span class="pq-tag-pill">{{ str_starts_with($tag, '#') ? $tag : '#'.$tag }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Social Platforms Status --}}
                            <div class="pq-platforms-row">
                                <span class="pq-platforms-label">Platforms:</span>
                                <div class="pq-platform-pills">
                                    @forelse($platforms as $platform)
                                        @php
                                            $isPosted = $task->status === 'published' || in_array($platform, $postedPlatforms);
                                            $pPost = $task->socialMediaPosts->firstWhere('platform', $platform);
                                        @endphp
                                        <span class="pq-platform-pill {{ $isPosted ? 'is-posted' : '' }}">
                                            <i class="{{ $platformIcons[$platform] ?? 'fa-solid fa-globe' }}"></i>
                                            <span>{{ ucfirst($platform) }}</span>
                                            @if($isPosted)
                                                <i class="fa-solid fa-circle-check pq-posted-icon" title="Published"></i>
                                                @if($pPost && $pPost->post_url)
                                                    <a href="{{ $pPost->post_url }}" target="_blank" class="pq-live-link" title="Open live post in new tab">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        </span>
                                    @empty
                                        <span class="pq-platform-pill" style="opacity:0.6">No platform specified</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- 3. Actions Hub --}}
                        <div class="pq-actions-col">
                            <div class="pq-status-box">
                                <span class="pq-status-title">Publishing State</span>
                                <div class="pq-status-badge {{ $task->status === 'published' ? 'is-published' : ($isOverdue ? 'is-overdue' : 'is-ready') }}">
                                    <i class="fa-solid {{ $task->status === 'published' ? 'fa-circle-check' : ($isOverdue ? 'fa-triangle-exclamation' : 'fa-hourglass-half') }}"></i>
                                    {{ $task->status === 'published' ? 'Live & Published' : ($isOverdue ? 'Due to Post' : 'Ready to Publish') }}
                                </div>
                            </div>

                            <div class="pq-btn-group">
                                @if($task->status === 'published')
                                    <form method="POST" action="{{ route('strategist.publishing.unpublish', $task) }}" onsubmit="return confirm('Revert publishing status back to Ready to Publish?')" style="margin:0;width:100%">
                                        @csrf
                                        <button type="submit" class="pq-btn-action pq-btn-revert">
                                            <i class="fa-solid fa-rotate-left"></i> Revert Status
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('strategist.publishing.publish', $task) }}" onsubmit="return confirmPublishWithClientCheck(event, this, {{ $isClientApproved ? 'true' : 'false' }})" style="margin:0;width:100%">
                                        @csrf
                                        <button type="submit" class="pq-btn-action pq-btn-publish">
                                            <i class="fa-solid fa-rocket"></i> Mark Published
                                        </button>
                                    </form>
                                @endif

                                <button type="button" class="pq-btn-action pq-btn-inspect" onclick="openQuickDrawer({{ $task->id }})">
                                    <i class="fa-solid fa-bolt"></i> Quick Inspect
                                </button>

                                <a href="{{ $detailUrl }}" class="pq-btn-action pq-btn-hub">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Hub & Proofs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hidden Data for Slide-Over Drawer --}}
                <template id="task-drawer-data-{{ $task->id }}">
                    <div class="pq-drawer-content">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap">
                            <span class="pq-client-badge">
                                <span class="pq-client-dot"></span>
                                <strong>{{ $task->client->name ?? 'Internal' }}</strong>
                            </span>
                            <span class="pq-type-pill pq-type-{{ strtolower($task->type ?: 'post') }}">
                                <i class="fa-solid {{ $typeIcon }}"></i> {{ strtoupper($task->type ?: 'POST') }}
                            </span>
                            @if($task->creator)
                                <span class="pq-creator-tag"><i class="fa-solid fa-user-pen"></i> By {{ $task->creator->name }}</span>
                            @endif
                        </div>

                        <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 16px;line-height:1.35">
                            {{ $task->title ?: 'Untitled Task' }}
                        </h2>

                        @if($task->headline_copy)
                            <div class="pq-hook-box" style="margin-bottom:16px">
                                <div class="pq-hook-icon"><i class="fa-solid fa-quote-left"></i></div>
                                <div class="pq-hook-text"><strong>Headline Hook:</strong> "{{ $task->headline_copy }}"</div>
                            </div>
                        @endif

                        {{-- Creative Assets Gallery --}}
                        <div style="margin-bottom:20px">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                                <span style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">
                                    <i class="fa-solid fa-photo-film"></i> Deliverable Creatives
                                </span>
                                <span style="font-size:11.5px;font-weight:700;color:var(--text3)">
                                    {{ $task->media ? $task->media->count() : 0 }} files
                                </span>
                            </div>

                            @if($task->media && $task->media->count())
                                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:10px">
                                    @foreach($task->media as $mediaItem)
                                        @php
                                            $isVid = (bool) $mediaItem->isVideo();
                                            $mediaUrl = $mediaItem->getUrl();
                                        @endphp
                                        <div style="position:relative;border-radius:10px;overflow:hidden;background:var(--card2);border:1px solid var(--border);aspect-ratio:1/1;cursor:pointer"
                                             onclick="window.openMediaLightbox('{{ addslashes($mediaUrl) }}', '{{ addslashes($task->title) }}', {{ $isVid ? 'true' : 'false' }}, '{{ $detailUrl }}')"
                                             title="Click to view full preview">
                                            @if($isVid)
                                                <video src="{{ $mediaUrl }}" muted style="width:100%;height:100%;object-fit:cover"></video>
                                                <span style="position:absolute;bottom:4px;right:4px;background:rgba(0,0,0,0.8);color:#fff;font-size:8px;padding:2px 5px;border-radius:4px">
                                                    <i class="fa-solid fa-play"></i> Video
                                                </span>
                                            @else
                                                <img src="{{ $mediaUrl }}" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover" onerror="this.src='/images/placeholder-image.png'" />
                                            @endif
                                            <div style="position:absolute;inset:0;background:rgba(15,23,42,0.4);display:flex;align-items:center;justify-content:center;color:#fff;opacity:0;transition:opacity 0.2s" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="padding:24px;text-align:center;background:var(--card2);border-radius:12px;border:1px dashed var(--border);color:var(--text3);font-size:12.5px">
                                    <i class="fa-regular fa-image" style="font-size:24px;display:block;margin-bottom:6px;opacity:0.5"></i>
                                    No creative artwork files attached yet.
                                </div>
                            @endif
                        </div>

                        {{-- Marketing Caption --}}
                        <div class="pq-caption-box" style="margin-bottom:20px">
                            <div class="pq-caption-header">
                                <span class="pq-caption-label"><i class="fa-solid fa-align-left"></i> Marketing Caption</span>
                                <div class="pq-copy-actions">
                                    <button type="button" class="pq-copy-chip" onclick="copyPublishContent('all', {{ $task->id }}, this)">
                                        <i class="fa-regular fa-copy"></i> Copy All
                                    </button>
                                    @if($task->caption)
                                        <button type="button" class="pq-copy-chip" onclick="copyPublishContent('caption', {{ $task->id }}, this)">
                                            <i class="fa-regular fa-file-lines"></i> Copy Caption
                                        </button>
                                    @endif
                                    @if($task->hashtags)
                                        <button type="button" class="pq-copy-chip" onclick="copyPublishContent('tags', {{ $task->id }}, this)">
                                            <i class="fa-solid fa-hashtag"></i> Copy Tags
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div style="font-size:13px;line-height:1.6;color:var(--text);white-space:pre-wrap;word-break:break-word">
                                {{ $task->caption ?: 'No caption provided.' }}
                            </div>

                            @if($task->hashtags)
                                <div class="pq-hashtags-row" style="margin-top:12px">
                                    <span class="pq-hashtag-lead"><i class="fa-solid fa-tags"></i></span>
                                    <div class="pq-tags-cloud">
                                        @foreach(preg_split('/[\s,]+/', trim($task->hashtags)) as $tag)
                                            @if(strlen($tag) > 1)
                                                <span class="pq-tag-pill">{{ str_starts_with($tag, '#') ? $tag : '#'.$tag }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Brief Notes if available --}}
                        @if($task->brief)
                            <div style="margin-bottom:20px">
                                <span style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px">
                                    <i class="fa-solid fa-clipboard-list"></i> Brief & Strategy
                                </span>
                                <div style="font-size:12.5px;color:var(--text2);background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:12px;white-space:pre-wrap">
                                    {{ $task->brief }}
                                </div>
                            </div>
                        @endif

                        {{-- Quick Link to Hub --}}
                        <div style="margin-top:auto;display:flex;gap:10px">
                            <a href="{{ $detailUrl }}" class="pq-btn pq-btn-primary" style="flex:1;justify-content:center">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Full Publishing Hub
                            </a>
                        </div>
                    </div>
                </template>
            @endforeach
        </div>

        {{-- 2. Compact Table View --}}
        <div class="pq-table-wrap" id="pqTableContainer" style="display:none">
            <table class="pq-table">
                <thead>
                    <tr>
                        <th style="width:60px">Preview</th>
                        <th>Task & Client</th>
                        <th>Type</th>
                        <th style="min-width:300px">Marketing Copy Snippet</th>
                        <th>Platforms</th>
                        <th>Scheduled Date</th>
                        <th>Client Sign-Off</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        @php
                            $isPublished = $task->status === 'published';
                            $postDateCarbon = $task->post_date ? \Illuminate\Support\Carbon::parse($task->post_date) : null;
                            $isOverdue = !$isPublished && $postDateCarbon && $postDateCarbon->isPast() && !$postDateCarbon->isToday();
                            $isToday = !$isPublished && $postDateCarbon && $postDateCarbon->isToday();
                            $isClientApproved = $task->isClientApproved();
                            $thumb = $task->getPreviewThumbnail();
                            $hasThumb = !empty($thumb);
                            $detailUrl = route('strategist.publishing.show', $task);

                            $rawPlatforms = $task->platform;
                            if (is_string($rawPlatforms)) {
                                $decoded = json_decode($rawPlatforms, true);
                                $platforms = is_array($decoded) ? $decoded : (strlen(trim($rawPlatforms)) ? [trim($rawPlatforms)] : []);
                            } elseif (is_array($rawPlatforms)) {
                                $platforms = $rawPlatforms;
                            } else {
                                $platforms = [];
                            }

                            $typeIcon = match(strtolower($task->type ?? 'post')) {
                                'reel' => 'fa-clapperboard',
                                'story' => 'fa-clock-rotate-left',
                                'video' => 'fa-video',
                                default => 'fa-image',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="pq-table-thumb" 
                                     onclick="{{ $hasThumb ? "window.openMediaLightbox('".addslashes($thumb['full_url'])."', '".addslashes($task->title ?: $thumb['name'])."', ".($thumb['is_video'] ? 'true' : 'false').", '".$detailUrl."')" : "openQuickDrawer({$task->id})" }}"
                                     title="Click to view full preview">
                                    @if($hasThumb)
                                        <img src="{{ $thumb['url'] }}" alt="" loading="lazy" onerror="this.src='/images/placeholder-image.png'" />
                                    @else
                                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:16px">
                                            <i class="fa-solid {{ $typeIcon }}"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:800;color:var(--text)">
                                    <a href="{{ $detailUrl }}" style="color:inherit;text-decoration:none">{{ $task->title ?: 'Untitled' }}</a>
                                </div>
                                <div style="font-size:11.5px;color:var(--text3);margin-top:2px">
                                    {{ $task->client->name ?? '—' }}
                                    @if($task->creator) • By {{ $task->creator->name }} @endif
                                </div>
                            </td>
                            <td>
                                <span class="pq-type-pill pq-type-{{ strtolower($task->type ?: 'post') }}">
                                    {{ strtoupper($task->type ?: 'POST') }}
                                </span>
                            </td>
                            <td>
                                @if($task->caption)
                                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                                        <button type="button" class="pq-copy-chip" onclick="copyPublishContent('all', {{ $task->id }}, this)" title="Copy Copy & Tags">
                                            <i class="fa-regular fa-copy"></i> Copy
                                        </button>
                                        <span style="font-size:12px;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:280px">
                                            {{ Str::limit($task->caption, 80) }}
                                        </span>
                                    </div>
                                @else
                                    <span style="font-size:11.5px;color:var(--text3);font-style:italic">No caption</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap">
                                    @foreach($platforms as $p)
                                        <span class="pq-platform-pill" style="padding:2px 7px;font-size:10px">
                                            <i class="{{ $platformIcons[$p] ?? 'fa-solid fa-globe' }}"></i> {{ ucfirst(substr($p, 0, 4)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($isPublished)
                                    <span class="pq-date-pill pq-date-published">Published</span>
                                @elseif($isOverdue)
                                    <span class="pq-date-pill pq-date-overdue">{{ $postDateCarbon->format('M d') }} (Due)</span>
                                @elseif($isToday)
                                    <span class="pq-date-pill pq-date-today">Today</span>
                                @elseif($postDateCarbon)
                                    <span class="pq-date-pill pq-date-upcoming">{{ $postDateCarbon->format('M d') }}</span>
                                @else
                                    <span style="color:var(--text3);font-size:11px">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="pq-client-group">
                                    <span class="pq-client-status {{ $isClientApproved ? 'is-yes' : 'is-no' }}" id="client-badge-tbl-{{ $task->id }}">
                                        {{ $isClientApproved ? 'Yes' : 'No' }}
                                    </span>
                                    <button type="button" 
                                            class="pq-client-toggle-btn {{ $isClientApproved ? 'is-yes' : 'is-no' }}"
                                            id="client-toggle-btn-tbl-{{ $task->id }}"
                                            onclick="toggleClientApproval({{ $task->id }}, this)">
                                        {{ $isClientApproved ? 'Mark No' : 'Mark Yes' }}
                                    </button>
                                </div>
                            </td>
                            <td style="text-align:right">
                                <div style="display:inline-flex;gap:6px">
                                    @if(!$isPublished)
                                        <form method="POST" action="{{ route('strategist.publishing.publish', $task) }}" onsubmit="return confirmPublishWithClientCheck(event, this, {{ $isClientApproved ? 'true' : 'false' }})" style="margin:0;display:inline">
                                            @csrf
                                            <button type="submit" class="pq-btn-action pq-btn-publish" style="padding:5px 10px;font-size:11px" title="Mark Published">
                                                <i class="fa-solid fa-rocket"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="pq-btn-action pq-btn-inspect" style="padding:5px 10px;font-size:11px" onclick="openQuickDrawer({{ $task->id }})" title="Quick Inspect">
                                        <i class="fa-solid fa-bolt"></i>
                                    </button>
                                    <a href="{{ $detailUrl }}" class="pq-btn-action pq-btn-hub" style="padding:5px 10px;font-size:11px" title="Open Full Hub">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tasks->hasPages())
            <div style="margin-top:28px;display:flex;justify-content:center">
                {{ $tasks->links() }}
            </div>
        @endif
    @endif

</div>

{{-- Slide-Over Drawer Modal --}}
<div class="pq-drawer-backdrop" id="pqDrawerBackdrop" onclick="handleDrawerBackdropClick(event)">
    <div class="pq-drawer" id="pqDrawer">
        <div class="pq-drawer-header">
            <h3 class="pq-drawer-title">Quick Content Inspection</h3>
            <button type="button" class="pq-drawer-close" onclick="closeQuickDrawer()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="pq-drawer-body" id="pqDrawerBody">
            <!-- Dynamic Content Injected Here -->
        </div>
        <div class="pq-drawer-footer">
            <button type="button" class="pq-btn pq-btn-outline" onclick="closeQuickDrawer()">Close</button>
        </div>
    </div>
</div>

{{-- Floating Toast Notification --}}
<div class="pq-toast" id="pqToast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="pqToastText">Copied to clipboard!</span>
</div>

@endsection

@push('scripts')
<script>
// 1. Client Approval Toggle via AJAX
async function toggleClientApproval(taskId, btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const response = await fetch(`/tasks/${taskId}/toggle-client-approval`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();
        if (data.success) {
            // Update Card Elements
            const badgeCard = document.getElementById(`client-badge-${taskId}`);
            const btnCard = document.getElementById(`client-toggle-btn-${taskId}`);
            // Update Table Elements
            const badgeTbl = document.getElementById(`client-badge-tbl-${taskId}`);
            const btnTbl = document.getElementById(`client-toggle-btn-tbl-${taskId}`);

            const isApp = data.is_approved;

            if (badgeCard) {
                badgeCard.className = `pq-client-status ${isApp ? 'is-yes' : 'is-no'}`;
                badgeCard.innerHTML = `<i class="fa-solid ${isApp ? 'fa-check' : 'fa-xmark'}"></i> Client: <strong>${isApp ? 'Yes' : 'No'}</strong>`;
            }
            if (btnCard) {
                btnCard.className = `pq-client-toggle-btn ${isApp ? 'is-yes' : 'is-no'}`;
                btnCard.innerHTML = isApp ? 'Mark No' : 'Mark Yes';
            }

            if (badgeTbl) {
                badgeTbl.className = `pq-client-status ${isApp ? 'is-yes' : 'is-no'}`;
                badgeTbl.innerHTML = isApp ? 'Yes' : 'No';
            }
            if (btnTbl) {
                btnTbl.className = `pq-client-toggle-btn ${isApp ? 'is-yes' : 'is-no'}`;
                btnTbl.innerHTML = isApp ? 'Mark No' : 'Mark Yes';
            }

            showToast(isApp ? 'Client sign-off confirmed (Yes)' : 'Client sign-off changed to (No)');
        } else {
            alert(data.message || 'Failed to update client approval status');
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error(e);
        btn.innerHTML = originalText;
    } finally {
        btn.disabled = false;
    }
}

// 2. Publish Confirmation with Client Check
function confirmPublishWithClientCheck(event, form, isApproved) {
    if (!isApproved) {
        return confirm('⚠️ Note: Client approval is currently marked as NO.\n\nAre you sure you want to mark this task as Published?');
    }
    return confirm('Mark this task as Published on social media?');
}

// 3. 1-Click Copy Publishing Content & Hashtags
async function copyPublishContent(type, taskId, btn) {
    let caption = '';
    let hashtags = '';

    const copyDataEl = document.getElementById(`task-copy-data-${taskId}`);
    if (copyDataEl) {
        caption = copyDataEl.getAttribute('data-caption') || '';
        hashtags = copyDataEl.getAttribute('data-hashtags') || '';
    } else {
        const card = document.getElementById(`task-card-${taskId}`);
        if (card) {
            const captionEl = card.querySelector(`#caption-text-${taskId}`);
            caption = captionEl ? captionEl.innerText.trim() : '';
            const tagEls = card.querySelectorAll('.pq-tag-pill');
            let tags = [];
            tagEls.forEach(el => tags.push(el.innerText.trim()));
            hashtags = tags.join(' ');
        }
    }

    let textToCopy = '';
    if (type === 'all') {
        textToCopy = (caption ? caption + (hashtags ? "\n\n" : "") : "") + hashtags;
    } else if (type === 'caption') {
        textToCopy = caption;
    } else if (type === 'tags') {
        textToCopy = hashtags;
    }

    if (!textToCopy || !textToCopy.trim()) {
        showToast('No marketing copy available to copy for this task.');
        return;
    }

    try {
        await navigator.clipboard.writeText(textToCopy.trim());
        const origHtml = btn.innerHTML;
        btn.classList.add('copied');
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';

        showToast(type === 'all' ? 'Caption & Hashtags copied!' : (type === 'tags' ? 'Hashtags copied!' : 'Caption copied!'));

        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = origHtml;
        }, 1800);
    } catch (err) {
        console.error('Clipboard copy failed:', err);
        const tempArea = document.createElement('textarea');
        tempArea.value = textToCopy.trim();
        document.body.appendChild(tempArea);
        tempArea.select();
        document.execCommand('copy');
        document.body.removeChild(tempArea);
        showToast('Copied to clipboard!');
    }
}

// 4. Expand / Collapse Caption
function toggleCaptionExpand(taskId, btn) {
    const captionEl = document.getElementById(`caption-text-${taskId}`);
    if (!captionEl) return;

    if (captionEl.classList.contains('expanded')) {
        captionEl.classList.remove('expanded');
        btn.innerHTML = '<span>Read full caption</span> <i class="fa-solid fa-chevron-down"></i>';
    } else {
        captionEl.classList.add('expanded');
        btn.innerHTML = '<span>Show less</span> <i class="fa-solid fa-chevron-up"></i>';
    }
}

// 5. Slide-Over Quick Drawer
function openQuickDrawer(taskId) {
    const template = document.getElementById(`task-drawer-data-${taskId}`);
    const drawerBody = document.getElementById('pqDrawerBody');
    const backdrop = document.getElementById('pqDrawerBackdrop');

    if (!template || !drawerBody || !backdrop) return;

    drawerBody.innerHTML = '';
    if (template.content) {
        drawerBody.appendChild(template.content.cloneNode(true));
    } else {
        drawerBody.innerHTML = template.innerHTML;
    }
    backdrop.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeQuickDrawer() {
    const backdrop = document.getElementById('pqDrawerBackdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
    }
    document.body.style.overflow = '';
}

function handleDrawerBackdropClick(event) {
    if (event.target.id === 'pqDrawerBackdrop') {
        closeQuickDrawer();
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeQuickDrawer();
    }
});

// 6. View Switcher (Cards vs Table)
function switchViewMode(mode) {
    const cards = document.getElementById('pqCardsContainer');
    const table = document.getElementById('pqTableContainer');
    const btnCards = document.getElementById('viewBtnCards');
    const btnTable = document.getElementById('viewBtnTable');

    if (mode === 'table') {
        if (cards) cards.style.display = 'none';
        if (table) table.style.display = 'block';
        if (btnCards) btnCards.classList.remove('active');
        if (btnTable) btnTable.classList.add('active');
        localStorage.setItem('publishing_view_mode', 'table');
    } else {
        if (cards) cards.style.display = 'flex';
        if (table) table.style.display = 'none';
        if (btnCards) btnCards.classList.add('active');
        if (btnTable) btnTable.classList.remove('active');
        localStorage.setItem('publishing_view_mode', 'cards');
    }
}

// Restore user view preference
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('publishing_view_mode');
    if (saved === 'table') {
        switchViewMode('table');
    }
});

// 7. Toast Notification Utility
let toastTimer = null;
function showToast(text) {
    const toast = document.getElementById('pqToast');
    const toastText = document.getElementById('pqToastText');
    if (!toast || !toastText) return;

    toastText.innerText = text;
    toast.classList.add('active');

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.remove('active');
    }, 2400);
}
</script>
@endpush
