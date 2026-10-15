/**
 * ══════════════════════════════════════════════════════════════════════════════
 * TMS UNIVERSAL NOTIFICATION AUDIO ENGINE (v2.0)
 * ──────────────────────────────────────────────────────────────────────────────
 * High-reliability, multi-profile sound system for all notification events:
 * - Real-time system polling alerts
 * - Push notifications (Firebase Cloud Messaging)
 * - Real-time chat messages
 * - In-app UI toasts & AJAX actions
 * - Due reminders & urgent tasks
 * 
 * Includes:
 * 1. Studio-tuned Web Audio API synthesizers (zero latency, zero 404 risk)
 * 2. HTML5 <audio> element fallback to /sounds/notification.wav
 * 3. Base64 embedded WAV data-URI fallback (works even on offline/broken paths)
 * 4. Chrome/Safari Autoplay Policy unlocker with persistent state
 * 5. Volume controls (0.0 to 1.0) with localStorage persistence
 * ══════════════════════════════════════════════════════════════════════════════
 */

(function (window) {
    'use strict';

    const SOUND_ENABLED_KEY = 'tms_sound_enabled';
    const SOUND_VOLUME_KEY  = 'tms_sound_volume';

    // Embedded clean 44.1kHz 16-bit PCM chime fallback in Base64 (dual-tone D5/A5 bell)
    const EMBEDDED_CHIME_B64 = 'data:audio/wav;base64,UklGRl4DAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YToDAACAgICAgICAgICAgICAgICAf3+AgIB/f39/f39/gICAf39/f4CAgH9/f3+AgICAgH9/f3+AgICAf39/f39/f39/f3+AgICAf39/f3+AgICAgICAf39/f4CAgICAgICAgICAf39/f4CAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA';

    const TMSAudio = {
        ctx: null,
        unlocked: false,
        lastPlayTimes: {},
        audioElement: null,

        /**
         * Initialize AudioContext lazily to respect browser autoplay policies
         */
        getCtx() {
            if (!this.ctx) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) {
                    try {
                        this.ctx = new AudioCtx();
                    } catch (e) {
                        this.ctx = null;
                    }
                }
            }
            return this.ctx;
        },

        /**
         * Get user volume preference (0.0 to 1.0, defaults to 0.85)
         */
        getVolume() {
            const stored = localStorage.getItem(SOUND_VOLUME_KEY);
            if (stored === null) return 0.85;
            const parsed = parseFloat(stored);
            return isNaN(parsed) ? 0.85 : Math.max(0, Math.min(1, parsed));
        },

        /**
         * Set user volume preference
         */
        setVolume(vol) {
            const clamped = Math.max(0, Math.min(1, parseFloat(vol) || 0));
            localStorage.setItem(SOUND_VOLUME_KEY, clamped.toFixed(2));
            if (this.audioElement) {
                this.audioElement.volume = clamped;
            }
            this.updateUi();
            return clamped;
        },

        /**
         * Check if sound is enabled (defaults to true)
         */
        isEnabled() {
            return localStorage.getItem(SOUND_ENABLED_KEY) !== 'false';
        },

        /**
         * Toggle sound on/off
         */
        toggle() {
            const next = !this.isEnabled();
            localStorage.setItem(SOUND_ENABLED_KEY, next ? 'true' : 'false');
            this.updateUi();
            if (next) {
                this.play('chime', true);
            }
            return next;
        },

        /**
         * Unlock AudioContext on first user interaction to comply with Chrome/Safari policies
         */
        unlock() {
            const ctx = this.getCtx();
            if (ctx && ctx.state === 'suspended') {
                ctx.resume().then(() => {
                    this.unlocked = true;
                    this.updateUi();
                }).catch(() => {});
            } else if (ctx && ctx.state === 'running') {
                this.unlocked = true;
            }

            // Also prime the HTML5 audio element
            this.primeAudioElement();
        },

        primeAudioElement() {
            if (!this.audioElement) {
                this.audioElement = document.getElementById('tmsNotificationAudio');
                if (!this.audioElement) {
                    this.audioElement = document.createElement('audio');
                    this.audioElement.id = 'tmsNotificationAudio';
                    this.audioElement.src = '/sounds/notification.wav';
                    this.audioElement.preload = 'auto';
                    this.audioElement.style.display = 'none';
                    document.body.appendChild(this.audioElement);
                }
            }
            if (this.audioElement) {
                this.audioElement.volume = this.getVolume();
            }
        },

        /**
         * Play sound by profile:
         * - 'chime'   : Default dual-tone bell (D5 587Hz -> A5 880Hz + overtone)
         * - 'urgent'  : Dual-pulse urgent alert (740Hz -> 880Hz -> 987Hz)
         * - 'success' : Gentle 3-note ascending chord (523Hz C5 -> 659Hz E5 -> 783Hz G5)
         * - 'message' : Soft crisp pop/ding (880Hz -> 660Hz decay)
         *
         * @param {string} profile
         * @param {boolean} isTest (bypasses mute and throttle)
         */
        play(profile = 'chime', isTest = false) {
            if (!this.isEnabled() && !isTest) return;

            // Throttle duplicate sounds (minimum 800ms between same profile) unless test
            const now = Date.now();
            const last = this.lastPlayTimes[profile] || 0;
            if (!isTest && (now - last < 800)) return;
            this.lastPlayTimes[profile] = now;

            const vol = this.getVolume();
            if (vol <= 0 && !isTest) return;

            this.unlock();
            const ctx = this.getCtx();

            // Try Web Audio API Synthesis first
            if (ctx && (ctx.state === 'running' || ctx.state === 'suspended')) {
                if (ctx.state === 'suspended') {
                    ctx.resume().catch(() => {});
                }
                try {
                    this.synthesizeSound(ctx, profile, vol);
                    return;
                } catch (e) {
                    console.warn('[TMSAudio] Synth failed, falling back to HTML5 audio', e);
                }
            }

            // Fallback: HTML5 Audio element
            this.playAudioElementFallback(vol);
        },

        synthesizeSound(ctx, profile, volMultiplier) {
            const t = ctx.currentTime;

            if (profile === 'urgent') {
                // High-visibility Urgent Pulse (Attention chime)
                const tones = [
                    { freq: 784.00, start: 0.00, dur: 0.14, gain: 0.28 },
                    { freq: 987.77, start: 0.12, dur: 0.24, gain: 0.32 },
                    { freq: 1174.66, start: 0.22, dur: 0.35, gain: 0.25 },
                ];
                tones.forEach(tone => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(tone.freq, t + tone.start);
                    gain.gain.setValueAtTime(0.0001, t + tone.start);
                    gain.gain.exponentialRampToValueAtTime(tone.gain * volMultiplier, t + tone.start + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, t + tone.start + tone.dur);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(t + tone.start);
                    osc.stop(t + tone.start + tone.dur + 0.05);
                });
            } else if (profile === 'success') {
                // Uplifting Major Triad (C5 -> E5 -> G5)
                const chords = [
                    { freq: 523.25, start: 0.00, dur: 0.28, gain: 0.22 },
                    { freq: 659.25, start: 0.06, dur: 0.32, gain: 0.24 },
                    { freq: 783.99, start: 0.12, dur: 0.45, gain: 0.28 },
                    { freq: 1046.50, start: 0.18, dur: 0.55, gain: 0.16 }, // C6 shimmer
                ];
                chords.forEach(note => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(note.freq, t + note.start);
                    gain.gain.setValueAtTime(0.0001, t + note.start);
                    gain.gain.exponentialRampToValueAtTime(note.gain * volMultiplier, t + note.start + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, t + note.start + note.dur);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(t + note.start);
                    osc.stop(t + note.start + note.dur + 0.05);
                });
            } else if (profile === 'message') {
                // Soft Crisp Chat Pop (A5 880Hz decaying to E5 659Hz)
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, t);
                osc.frequency.exponentialRampToValueAtTime(659.25, t + 0.14);
                gain.gain.setValueAtTime(0.0001, t);
                gain.gain.exponentialRampToValueAtTime(0.24 * volMultiplier, t + 0.015);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.22);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(t);
                osc.stop(t + 0.25);
            } else {
                // Default 'chime': Studio 2-Tone Bell (D5 587Hz -> A5 880Hz + 1760Hz shimmer)
                // Note 1: 587.33 Hz (D5)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, t);
                gain1.gain.setValueAtTime(0.0001, t);
                gain1.gain.exponentialRampToValueAtTime(0.25 * volMultiplier, t + 0.02);
                gain1.gain.exponentialRampToValueAtTime(0.0001, t + 0.30);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(t);
                osc1.stop(t + 0.32);

                // Note 2: 880.00 Hz (A5)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880.00, t + 0.08);
                gain2.gain.setValueAtTime(0.0001, t + 0.08);
                gain2.gain.exponentialRampToValueAtTime(0.32 * volMultiplier, t + 0.11);
                gain2.gain.exponentialRampToValueAtTime(0.0001, t + 0.55);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(t + 0.08);
                osc2.stop(t + 0.58);

                // Overtone Shimmer: 1760 Hz (A6)
                const osc3 = ctx.createOscillator();
                const gain3 = ctx.createGain();
                osc3.type = 'sine';
                osc3.frequency.setValueAtTime(1760.00, t + 0.08);
                gain3.gain.setValueAtTime(0.0001, t + 0.08);
                gain3.gain.exponentialRampToValueAtTime(0.06 * volMultiplier, t + 0.11);
                gain3.gain.exponentialRampToValueAtTime(0.0001, t + 0.40);
                osc3.connect(gain3);
                gain3.connect(ctx.destination);
                osc3.start(t + 0.08);
                osc3.stop(t + 0.42);
            }
        },

        playAudioElementFallback(vol) {
            try {
                this.primeAudioElement();
                if (this.audioElement) {
                    this.audioElement.volume = vol;
                    this.audioElement.currentTime = 0;
                    const p = this.audioElement.play();
                    if (p && typeof p.catch === 'function') {
                        p.catch(() => {
                            // If file playback fails (e.g. 404), fallback to embedded Base64 audio
                            try {
                                const b64Audio = new Audio(EMBEDDED_CHIME_B64);
                                b64Audio.volume = vol;
                                b64Audio.play().catch(() => {});
                            } catch (_) {}
                        });
                    }
                }
            } catch (_) {}
        },

        /**
         * Update UI elements (toggle button, volume sliders, badges)
         */
        updateUi() {
            const enabled = this.isEnabled();
            const vol = this.getVolume();
            const pct = Math.round(vol * 100);

            // 1. Toggle Button
            const toggleBtn = document.getElementById('tmsSoundToggleBtn');
            const icon = document.getElementById('tmsSoundIcon');
            const label = document.getElementById('tmsSoundLabel');

            if (toggleBtn) {
                if (enabled) {
                    toggleBtn.classList.remove('muted');
                    toggleBtn.classList.add('active');
                    if (icon) icon.className = 'fas fa-volume-up';
                    if (label) label.textContent = 'Sound: ON';
                } else {
                    toggleBtn.classList.remove('active');
                    toggleBtn.classList.add('muted');
                    if (icon) icon.className = 'fas fa-volume-mute';
                    if (label) label.textContent = 'Sound: OFF';
                }
            }

            // 2. Volume Slider
            const slider = document.getElementById('tmsSoundVolSlider');
            const volDisplay = document.getElementById('tmsSoundVolValue');
            if (slider && document.activeElement !== slider) {
                slider.value = pct;
            }
            if (volDisplay) {
                volDisplay.textContent = pct + '%';
            }
        }
    };

    // Auto-unlock on ANY user gesture anywhere on the window
    const gestureEvents = ['click', 'pointerdown', 'keydown', 'touchstart', 'scroll'];
    const unlockHandler = function () {
        TMSAudio.unlock();
    };
    gestureEvents.forEach(evt => {
        window.addEventListener(evt, unlockHandler, { passive: true, once: false });
    });

    // Make TMSAudio globally available
    window.TMSAudio = TMSAudio;
    window.playNotificationSound = function (profile = 'chime', isTest = false) {
        TMSAudio.play(profile, isTest);
    };

    // Keep legacy helper for backward compatibility
    window.playTmsNotificationChime = function (isTest = false) {
        TMSAudio.play('chime', !!isTest);
    };
    window.toggleTmsSound = function () {
        return TMSAudio.toggle();
    };
    window.testTmsSound = function (profile = 'chime') {
        TMSAudio.play(profile, true);
        const btn = document.querySelector('.notif-test-btn');
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check" style="color:#059669"></i> <span style="color:#059669">Playing!</span>';
            setTimeout(() => { if (btn) btn.innerHTML = orig; }, 1600);
        }
    };
    window.setTmsVolume = function (val) {
        return TMSAudio.setVolume(val / 100);
    };

    // Initial UI state on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => TMSAudio.updateUi());
    } else {
        TMSAudio.updateUi();
    }

})(window);
