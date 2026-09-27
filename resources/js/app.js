import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';
import { createIcons, icons } from 'lucide';
import 'preline';

window.Html5Qrcode = Html5Qrcode;
window.Html5QrcodeSupportedFormats = Html5QrcodeSupportedFormats;

// Safe wrapper ensuring icons object is always passed
const safeCreateIcons = (options = {}) => {
    return createIcons({
        icons: options.icons || icons,
        nameAttr: options.nameAttr || 'data-lucide',
        attrs: options.attrs || {},
        ...options
    });
};

window.lucide = {
    createIcons: safeCreateIcons,
    icons: icons
};

window.renderLucide = () => {
    try {
        safeCreateIcons();
    } catch (e) {
        console.error('Lucide icon rendering error:', e);
    }
};

// Immediate render if DOM is already ready
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(window.renderLucide, 1);
} else {
    document.addEventListener('DOMContentLoaded', () => {
        window.renderLucide();
    });
}

document.addEventListener('livewire:navigated', () => {
    window.renderLucide();
});

document.addEventListener('livewire:initialized', () => {
    window.renderLucide();
});

// Synthesizer Audio Feedback (Web Audio API)
window.playBeep = (type = 'success') => {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();

        if (type === 'success') {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // High chime A5
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.18);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.18);
        } else if (type === 'late') {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        } else {
            // Error, duplicate, or wrong class double low buzz
            const now = ctx.currentTime;
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sawtooth';
            osc1.frequency.setValueAtTime(260, now);
            gain1.gain.setValueAtTime(0.2, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.12);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sawtooth';
            osc2.frequency.setValueAtTime(200, now + 0.14);
            gain2.gain.setValueAtTime(0.2, now + 0.14);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.14);
            osc2.stop(now + 0.28);
        }
    } catch (e) {
        console.warn('Audio feedback error', e);
    }
};
