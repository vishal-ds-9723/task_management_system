<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TheLayout – Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/agencyflow.css') }}">
    <link rel="stylesheet" href="{{ asset('css/premium-ui.css') }}">
    <link rel="icon" href="{{ asset('images/icon.png') }}" type="image/png">
    <link rel="manifest" href="{{ asset('manifest.json') }}" type="application/manifest+json">
    <style>
        .login-logo-img { width: 180px; height: 180px; object-fit: contain; margin: 0 auto 12px; display: block; filter: drop-shadow(0 8px 24px rgba(79, 70, 229, 0.25)); animation: logoFadeIn 0.8s ease; }
        @keyframes logoFadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .login-feature { display:flex;align-items:center;gap:12px;padding:10px 0;font-size:13px;color:var(--text2);transition:all 0.2s ease; }
        .login-feature:hover { transform:translateX(4px); }
        .login-feature-icon { width:36px;height:36px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;transition:all 0.3s cubic-bezier(0.34,1.56,0.64,1); }
        .login-feature:hover .login-feature-icon { transform:scale(1.1) rotate(-5deg); }
        .login-feature-text strong { color:var(--text);display:block;font-size:13.5px;margin-bottom:2px;font-weight:700; }
        .login-feature-text span { font-size:11.5px;color:var(--text3); }
        .login-divider { width:100%;height:1px;background:linear-gradient(90deg,transparent,var(--border),transparent);margin:24px 0; }
        .login-card { animation:loginSlideIn 0.6s cubic-bezier(0.34,1.56,0.64,1); }
        @keyframes loginSlideIn {
            from { opacity:0;transform:translateY(30px) scale(0.95); }
            to { opacity:1;transform:translateY(0) scale(1); }
        }
        /* Animated grid background */
        .login-grid-bg {
            position:absolute;
            inset:0;
            background-image:
                linear-gradient(rgba(79,70,229,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,70,229,0.03) 1px, transparent 1px);
            background-size:60px 60px;
            mask-image:radial-gradient(ellipse 80% 70% at 50% 50%, black, transparent);
            -webkit-mask-image:radial-gradient(ellipse 80% 70% at 50% 50%, black, transparent);
        }
        /* Floating orbs */
        .login-orb {
            position:absolute;
            border-radius:50%;
            pointer-events:none;
            filter:blur(60px);
        }
        .login-orb-1 {
            width:400px;height:400px;
            background:rgba(79,70,229,0.12);
            top:-15%;right:-10%;
            animation:orbFloat1 12s ease-in-out infinite;
        }
        .login-orb-2 {
            width:300px;height:300px;
            background:rgba(16,185,129,0.08);
            bottom:-10%;left:-8%;
            animation:orbFloat2 10s ease-in-out infinite;
        }
        .login-orb-3 {
            width:200px;height:200px;
            background:rgba(139,92,246,0.10);
            top:40%;left:20%;
            animation:orbFloat3 14s ease-in-out infinite;
        }
        @keyframes orbFloat1 { 0%,100% { transform:translate(0,0) scale(1); } 33% { transform:translate(-30px,20px) scale(1.05); } 66% { transform:translate(15px,-15px) scale(0.95); } }
        @keyframes orbFloat2 { 0%,100% { transform:translate(0,0) scale(1); } 50% { transform:translate(25px,-20px) scale(1.08); } }
        @keyframes orbFloat3 { 0%,100% { transform:translate(0,0); } 25% { transform:translate(20px,15px); } 75% { transform:translate(-15px,-10px); } }
        /* Version badge */
        .login-version {
            position:absolute;
            bottom:24px;
            left:50%;
            transform:translateX(-50%);
            font-size:11px;
            color:var(--text3);
            opacity:0.5;
            letter-spacing:0.5px;
        }


.password-wrapper {
    position: relative;
}

.password-wrapper input {
    width: 100%;
    padding-right: 40px; /* space for icon */
}

.toggle-password {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--text3);
}

/* ── Login Welcome Overlay ── */
.login-welcome-overlay {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at 20% 20%, rgba(79,70,229,0.28) 0%, rgba(8,12,24,0.92) 70%);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}
.login-welcome-overlay.show {
    display: flex;
    animation: lwcFadeIn 0.28s ease;
}
@keyframes lwcFadeIn { from { opacity:0; } to { opacity:1; } }

/* ── Card ── */
.login-welcome-card {
    width: min(430px, 92vw);
    background: var(--card, #fff);
    border: 1px solid var(--border, rgba(0,0,0,0.08));
    border-radius: 22px;
    padding: 32px 28px 24px;
    text-align: center;
    box-shadow: 0 30px 72px rgba(0,0,0,0.32), 0 4px 16px rgba(0,0,0,0.12);
    animation: lwcCardIn 0.38s cubic-bezier(0.22, 1, 0.36, 1);
    position: relative;
    overflow: hidden;
}

/* Animated rainbow bar along the top edge */
.login-welcome-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ef4444, #f97316, #eab308, #10b981, #3b82f6, #8b5cf6, #ef4444);
    background-size: 300% 100%;
    animation: lwcRainbow 2.4s linear infinite;
}
@keyframes lwcRainbow {
    from { background-position: 0% 0%; }
    to   { background-position: 300% 0%; }
}
@keyframes lwcCardIn {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ── Logo with pulsing ripple rings ── */
.lwc-logo-wrap {
    position: relative;
    width: 96px; height: 96px;
    margin: 4px auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lwc-logo {
    width: 70px; height: 70px;
    object-fit: contain;
    position: relative;
    z-index: 2;
    animation: lwcLogoPulse 2.2s ease-in-out infinite;
    filter: drop-shadow(0 4px 14px rgba(239,68,68,0.22));
}
@keyframes lwcLogoPulse {
    0%, 100% { transform: scale(1);    filter: drop-shadow(0 4px 14px rgba(239,68,68,0.22)); }
    50%       { transform: scale(1.06); filter: drop-shadow(0 6px 22px rgba(239,68,68,0.42)); }
}
.lwc-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 1.5px solid rgba(239,68,68,0.3);
    animation: lwcRipple 2.6s ease-out infinite;
}
.lwc-ring-2 { animation-delay: 0.87s; }
.lwc-ring-3 { animation-delay: 1.73s; }
@keyframes lwcRipple {
    0%   { transform: scale(0.7); opacity: 0.9; }
    100% { transform: scale(2.0); opacity: 0; }
}

/* ── Title & Subtitle ── */
.login-welcome-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--text, #111827);
    line-height: 1.2;
    margin-bottom: 5px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    animation: lwcCardIn 0.4s 0.08s both;
}
.login-welcome-subtitle {
    font-size: 13px;
    color: var(--text2, #6B7280);
    margin-bottom: 20px;
    line-height: 1.4;
    animation: lwcCardIn 0.4s 0.14s both;
}

/* ── Progress Box ── */
.lwc-progress-wrap {
    background: var(--card2, #f5f7fb);
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 14px;
    padding: 15px 15px 13px;
    margin-bottom: 13px;
    text-align: left;
    animation: lwcCardIn 0.4s 0.2s both;
}

/* Status row */
.lwc-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    gap: 8px;
}
.lwc-status-left {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text2, #6B7280);
    min-width: 0;
    flex: 1;
}
.lwc-status-left > span:last-child {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.lwc-pulse-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--primary, #ef4444);
    box-shadow: 0 0 0 0 rgba(239,68,68,0.4);
    animation: lwcDotPulse 1.5s ease infinite;
    flex-shrink: 0;
    transition: background 0.4s ease;
}
.lwc-pulse-dot.done {
    background: #10b981;
    animation-name: lwcDotPulseDone;
}
@keyframes lwcDotPulse     { 0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); } 50% { box-shadow: 0 0 0 5px rgba(239,68,68,0); } }
@keyframes lwcDotPulseDone { 0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); } 50% { box-shadow: 0 0 0 5px rgba(16,185,129,0); } }
.lwc-percent {
    font-size: 14px;
    font-weight: 800;
    color: var(--primary, #ef4444);
    font-family: 'Plus Jakarta Sans', sans-serif;
    letter-spacing: 0.3px;
    min-width: 38px;
    text-align: right;
    flex-shrink: 0;
    transition: color 0.4s ease;
}

/* Progress bar */
.lwc-bar-track {
    height: 9px;
    border-radius: 99px;
    background: var(--border, #E5E7EB);
    overflow: hidden;
    position: relative;
    margin-bottom: 14px;
}
.lwc-bar-fill {
    display: block;
    height: 100%;
    width: 0;
    border-radius: 99px;
    background: linear-gradient(90deg, #ef4444, #f97316, #eab308, #10b981);
    background-size: 300% 100%;
    transition: width 150ms linear;
    position: relative;
    animation: lwcBarFlow 2.4s linear infinite;
}
@keyframes lwcBarFlow {
    from { background-position: 0% 0%; }
    to   { background-position: 300% 0%; }
}
/* Shimmer sweep on fill */
.lwc-bar-fill::after {
    content: '';
    position: absolute;
    top: 0; left: -60%;
    width: 45%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
    animation: lwcBarShimmer 1.8s ease-in-out infinite;
}
@keyframes lwcBarShimmer { from { left: -60%; } to { left: 120%; } }

/* Glowing tip at leading edge */
.lwc-bar-fill::before {
    content: '';
    position: absolute;
    top: -1px; right: 0;
    width: 12px; height: calc(100% + 2px);
    background: rgba(255,255,255,0.5);
    border-radius: 99px;
    filter: blur(3px);
}

/* ── Step indicators ── */
.lwc-steps {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    position: relative;
}
.lwc-steps::before {
    content: '';
    position: absolute;
    top: 6px;
    left: 10px; right: 10px;
    height: 1px;
    background: var(--border, #E5E7EB);
    z-index: 0;
}
.lwc-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    position: relative;
    z-index: 1;
    flex: 1;
}
.lwc-step-dot {
    width: 13px; height: 13px;
    border-radius: 50%;
    border: 2px solid var(--border, #E5E7EB);
    background: var(--card, #fff);
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    flex-shrink: 0;
}
.lwc-step.done .lwc-step-dot {
    background: #10b981;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
}
.lwc-step.done .lwc-step-dot::after {
    content: '✓';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    font-size: 7px;
    font-weight: 900;
    color: #fff;
    line-height: 1;
}
.lwc-step.active .lwc-step-dot {
    background: var(--primary, #ef4444);
    border-color: var(--primary, #ef4444);
    animation: lwcActiveDot 1.1s ease infinite;
}
@keyframes lwcActiveDot {
    0%, 100% { box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }
    50%       { box-shadow: 0 0 0 6px rgba(239,68,68,0.08); }
}
.lwc-step span {
    font-size: 9px;
    font-weight: 700;
    color: var(--text3, #9CA3AF);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.lwc-step.done   span { color: #10b981; }
.lwc-step.active span { color: var(--primary, #ef4444); }

/* ── Quote ── */
.lwc-quote {
    font-size: 11.5px;
    color: var(--text3, #9CA3AF);
    line-height: 1.5;
    text-align: left;
    padding: 9px 12px;
    border: 1px solid var(--border, #E5E7EB);
    border-radius: 10px;
    background: var(--card2, #f5f7fb);
    font-style: italic;
    margin-bottom: 10px;
    min-height: 34px;
    animation: lwcCardIn 0.4s 0.26s both;
}

/* ── ETA footer ── */
.lwc-eta-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    animation: lwcCardIn 0.4s 0.32s both;
}
.lwc-spinner { font-size: 11px; color: var(--text3, #9CA3AF); opacity: 0.7; transition: all 0.3s ease; }
.lwc-eta     { font-size: 11px; color: var(--text3, #9CA3AF); }

        
    </style>
</head>
<body>
<div id="loginPanel" class="panel active" style="display:flex">
    <div class="login-grid-bg"></div>
    <div class="login-orb login-orb-1"></div>
    <div class="login-orb login-orb-2"></div>
    <div class="login-orb login-orb-3"></div>
    <div class="login-card">
        <div style="text-align:center;margin-bottom:24px">
            <img src="{{ asset('images/logo.png') }}" alt="TheLayout" class="login-logo-img">
            <div class="login-tagline" style="margin-top:0">Social media task management for your agency</div>
        </div>

        @if(isset($errors) && $errors->any())
            <div style="background:var(--red-dim);color:var(--red);padding:12px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;border:1px solid rgba(239,68,68,0.15)">
                <span><i class="fas fa-exclamation-triangle" style="color:var(--red)"></i></span> {{ $errors->first() }}
            </div>
        @endif

        <div id="loginInlineError" style="display:none;background:var(--red-dim);color:var(--red);padding:12px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:600;align-items:center;gap:8px;border:1px solid rgba(239,68,68,0.15)">
            <span><i class="fas fa-exclamation-triangle" style="color:var(--red)"></i></span>
            <span id="loginInlineErrorText"></span>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@agency.com" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group" style="margin-top:14px">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <span class="toggle-password">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                </div>
            <button id="loginSubmitBtn" type="submit" class="login-btn">Login →</button>
        </form>

        <div class="login-divider"></div>
        <div class="login-forgot">Forgot password? <span>Reset here</span></div>
    </div>
    <div class="login-version">TheLayout TMS v2.0</div>
</div>

<div id="loginWelcomeOverlay" class="login-welcome-overlay" aria-hidden="true">
    <div class="login-welcome-card">

        {{-- Logo with animated ripple rings --}}
        <div class="lwc-logo-wrap">
            <div class="lwc-ring lwc-ring-1"></div>
            <div class="lwc-ring lwc-ring-2"></div>
            <div class="lwc-ring lwc-ring-3"></div>
            <img src="{{ asset('images/logo.png') }}" alt="The Layout" class="lwc-logo">
        </div>

        {{-- Personalized greeting --}}
        <div id="loginWelcomeTitle" class="login-welcome-title">Welcome back!</div>
        <div id="loginWelcomeSubtitle" class="login-welcome-subtitle">Preparing your workspace...</div>

        {{-- Progress section --}}
        <div class="lwc-progress-wrap">
            <div class="lwc-status-row">
                <div class="lwc-status-left">
                    <span class="lwc-pulse-dot"></span>
                    <span id="loginWelcomeStatus">Booting modules...</span>
                </div>
                <span id="loginWelcomePercent" class="lwc-percent">0%</span>
            </div>
            <div class="lwc-bar-track">
                <span id="loginWelcomeProgressBar" class="lwc-bar-fill"></span>
            </div>
            <div class="lwc-steps">
                <div class="lwc-step" id="lwcStep1">
                    <div class="lwc-step-dot"></div>
                    <span>Auth</span>
                </div>
                <div class="lwc-step" id="lwcStep2">
                    <div class="lwc-step-dot"></div>
                    <span>Data</span>
                </div>
                <div class="lwc-step" id="lwcStep3">
                    <div class="lwc-step-dot"></div>
                    <span>Workspace</span>
                </div>
                <div class="lwc-step" id="lwcStep4">
                    <div class="lwc-step-dot"></div>
                    <span>Ready</span>
                </div>
            </div>
        </div>

        {{-- Motivational quote --}}
        <div id="loginWelcomeQuote" class="lwc-quote">"Great work begins with a clear plan."</div>

        {{-- ETA footer --}}
        <div class="lwc-eta-wrap">
            <i class="fas fa-circle-notch fa-spin lwc-spinner"></i>
            <span id="loginWelcomeEta" class="lwc-eta">Connecting...</span>
        </div>

    </div>
</div>


<script>
document.querySelector(".toggle-password").addEventListener("click", function () {
    const passwordInput = document.getElementById("password");
    const icon = this.querySelector("i");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
});

const loginForm           = document.getElementById('loginForm');
const loginOverlay        = document.getElementById('loginWelcomeOverlay');
const loginWelcomeTitle   = document.getElementById('loginWelcomeTitle');
const loginWelcomeSubtitle= document.getElementById('loginWelcomeSubtitle');
const loginWelcomeStatus  = document.getElementById('loginWelcomeStatus');
const loginWelcomePercent = document.getElementById('loginWelcomePercent');
const loginWelcomeProgressBar = document.getElementById('loginWelcomeProgressBar');
const loginWelcomeEta     = document.getElementById('loginWelcomeEta');
const loginWelcomeQuote   = document.getElementById('loginWelcomeQuote');
const loginSubmitBtn      = document.getElementById('loginSubmitBtn');
const loginInlineError    = document.getElementById('loginInlineError');
const loginInlineErrorText= document.getElementById('loginInlineErrorText');
let loginSubmitting = false;

// ── Step indicators ──
const lwcSteps   = ['lwcStep1','lwcStep2','lwcStep3','lwcStep4'].map(id => document.getElementById(id));
const lwcPulseDot= document.querySelector('.lwc-pulse-dot');
const lwcSpinner = document.querySelector('.lwc-spinner');
const lwcThresholds = [0, 25, 50, 75];

const updateSteps = (pct) => {
    lwcSteps.forEach((step, i) => {
        if (!step) return;
        const next = lwcThresholds[i + 1] ?? 100;
        if (pct >= next || (i === lwcSteps.length - 1 && pct >= 99)) {
            step.classList.remove('active');
            step.classList.add('done');
        } else if (pct >= lwcThresholds[i]) {
            step.classList.add('active');
            step.classList.remove('done');
        } else {
            step.classList.remove('active', 'done');
        }
    });
    if (pct >= 99) {
        lwcPulseDot && lwcPulseDot.classList.add('done');
        if (lwcSpinner) {
            lwcSpinner.classList.remove('fa-circle-notch', 'fa-spin');
            lwcSpinner.classList.add('fa-check-circle');
            lwcSpinner.style.color = '#10b981';
            lwcSpinner.style.opacity = '1';
        }
        if (loginWelcomePercent) loginWelcomePercent.style.color = '#10b981';
    }
};

const showLoginError = (message) => {
    if (loginInlineErrorText) {
        loginInlineErrorText.textContent = message || 'Login failed. Please try again.';
    }

    if (loginInlineError) {
        loginInlineError.style.display = 'flex';
    }
};

const clearLoginError = () => {
    if (loginInlineError) {
        loginInlineError.style.display = 'none';
    }
};

const applyServerProgress = (payload) => {
    const pct = typeof payload.pct === 'number' ? Math.max(0, Math.min(100, payload.pct)) : null;

    if (pct !== null) {
        if (loginWelcomePercent)    loginWelcomePercent.textContent = `${pct}%`;
        if (loginWelcomeProgressBar) loginWelcomeProgressBar.style.width = `${pct}%`;
        updateSteps(pct);
    }
    if (payload.status && loginWelcomeStatus) {
        loginWelcomeStatus.textContent = payload.status;
    }
    if (payload.eta && loginWelcomeEta) {
        loginWelcomeEta.textContent = `Ready in ~${payload.eta}`;
    }
    if (payload.quote && loginWelcomeQuote) {
        loginWelcomeQuote.textContent = payload.quote;
    }
};

const startLoginProgress = async (formData, csrfToken) => {
    const response = await fetch("{{ route('login.progress.start') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData,
        credentials: 'same-origin'
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = data?.message || data?.errors?.email?.[0] || 'Login failed. Please try again.';
        throw new Error(message);
    }

    return data;
};

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const pollLoginProgress = async () => {
    const stopAt = Date.now() + 7000;
    let lastPayload = null;

    while (Date.now() < stopAt) {
        const stateResponse = await fetch("{{ route('login.progress.state') }}", {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            cache: 'no-store'
        });

        if (!stateResponse.ok) {
            throw new Error('Unable to read login progress.');
        }

        const payload = await stateResponse.json().catch(() => null);
        if (payload) {
            applyServerProgress(payload);
            lastPayload = payload;

            if (payload.done) {
                return payload;
            }
        }

        await wait(120);
    }

    if (lastPayload && lastPayload.done) {
        return lastPayload;
    }

    throw new Error('Login timed out. Please try again.');
};

if (loginForm) {
    loginForm.addEventListener('submit', async function (e) {
        if (loginSubmitting) {
            return;
        }

        e.preventDefault();

        const emailVal = (loginForm.querySelector('input[name="email"]')?.value || '').trim();
        const rawName = emailVal.includes('@') ? emailVal.split('@')[0] : '';
        const safeName = rawName ? (rawName.charAt(0).toUpperCase() + rawName.slice(1)) : 'back';

        if (loginWelcomeTitle) {
            loginWelcomeTitle.textContent = `Welcome ${safeName}!`;
        }
        if (loginWelcomeSubtitle) {
            loginWelcomeSubtitle.textContent = 'Syncing with server speed...';
        }

        clearLoginError();

        if (loginSubmitBtn) {
            loginSubmitBtn.disabled = true;
            loginSubmitBtn.style.opacity = '0.75';
        }

        loginOverlay?.classList.add('show');
        loginSubmitting = true;

        applyServerProgress({ pct: 3, status: 'Opening secure channel...', eta: '5.0s' });

        const startedAt = performance.now();
        const formData = new FormData(loginForm);
        const csrfToken = loginForm.querySelector('input[name="_token"]')?.value || '';

        try {
            const finalState = await startLoginProgress(formData, csrfToken);

            if (finalState.success && finalState.redirect_url) {
                applyServerProgress({
                    pct: 100,
                    status: 'Authenticated! Loading dashboard...',
                    eta: '0.0s',
                    quote: finalState.quote
                });
                setTimeout(() => {
                    window.location.assign(finalState.redirect_url);
                }, 200);
                return;
            }

            throw new Error(finalState.message || 'Invalid credentials.');
        } catch (error) {
            loginSubmitting = false;

            if (loginSubmitBtn) {
                loginSubmitBtn.disabled = false;
                loginSubmitBtn.style.opacity = '1';
            }

            loginOverlay?.classList.remove('show');
            showLoginError(error?.message || 'Login failed. Please try again.');
        }
    });
}
</script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(() => {
                console.info('PWA service worker registered');
            })
            .catch(err => {
                console.warn('Service worker registration failed:', err);
            });
    });
}
</script>
</body>
</html>
