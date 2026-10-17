let sharedAudioCtx = null;

/**
 * Get or resume the shared Web Audio context.
 * Modern browsers suspend AudioContext until user interaction; this safely resumes it.
 */
function getAudioContext() {
  try {
    if (!sharedAudioCtx) {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (AudioContextClass) {
        sharedAudioCtx = new AudioContextClass();
      }
    }
    if (sharedAudioCtx && sharedAudioCtx.state === 'suspended') {
      sharedAudioCtx.resume();
    }
    return sharedAudioCtx;
  } catch (e) {
    console.warn('AudioContext initialization error:', e);
    return null;
  }
}

/**
 * Melodic chime sound using the browser's Web Audio API.
 * Synthesizes a high-definition 3-tone notification chime without any external audio file dependencies.
 */
export function playChimeSound() {
  try {
    const ctx = getAudioContext();
    if (!ctx) {
      playFallbackBeep();
      return;
    }

    // Ensure audio context is running
    if (ctx.state === 'suspended') {
      ctx.resume();
    }

    const now = ctx.currentTime;

    // 3-tone melodic chime: E5 (659.25Hz), G#5 (830.61Hz), B5 (987.77Hz)
    const notes = [
      { freq: 659.25, start: 0.0, end: 0.24 },
      { freq: 830.61, start: 0.14, end: 0.40 },
      { freq: 987.77, start: 0.28, end: 0.85 },
    ];

    notes.forEach(({ freq, start, end }) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(freq, now + start);

      gain.gain.setValueAtTime(0.0001, now + start);
      gain.gain.linearRampToValueAtTime(0.35, now + start + 0.04);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + end);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(now + start);
      osc.stop(now + end);
    });
  } catch (e) {
    console.warn('Web Audio notification chime error:', e);
    playFallbackBeep();
  }
}

/**
 * Lightweight fallback audio beep if Web Audio context is unavailable.
 */
function playFallbackBeep() {
  try {
    // 800Hz beep data URI
    const snd = new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU' + 'A'.repeat(800));
    snd.volume = 0.5;
    snd.play().catch(() => {});
  } catch (e) {
    // Ignore fallback failure
  }
}

/**
 * Request permission for desktop browser notifications.
 * Supports modern Promises and legacy callback signatures for all browsers.
 */
export async function requestNotificationPermission() {
  if (!('Notification' in window)) return 'unsupported';
  if (Notification.permission === 'granted') return 'granted';
  if (Notification.permission === 'denied') return 'denied';

  try {
    let result = await new Promise((resolve) => {
      let resolved = false;
      const callback = (perm) => {
        if (!resolved) {
          resolved = true;
          resolve(perm);
        }
      };

      try {
        const promise = Notification.requestPermission(callback);
        if (promise && typeof promise.then === 'function') {
          promise.then((perm) => {
            if (!resolved) {
              resolved = true;
              resolve(perm);
            }
          }).catch(() => resolve(Notification.permission));
        }
      } catch (err) {
        resolve(Notification.permission);
      }
    });

    return result || Notification.permission;
  } catch (e) {
    return Notification.permission || 'denied';
  }
}

/**
 * Dispatch desktop notification with sound alert.
 */
export function sendBrowserNotification(title, options = {}) {
  // Always play melodic audio alert
  playChimeSound();

  // If permitted, trigger system level notification
  if ('Notification' in window && Notification.permission === 'granted') {
    try {
      const notif = new Notification(title, {
        icon: '/images/logo/elbildad-logo.png',
        badge: '/images/logo/elbildad-logo.png',
        silent: true, // We synthesize our custom melodic chime
        ...options,
      });

      notif.onclick = () => {
        window.focus();
        window.location.href = options.url || '/tasks';
      };

      return notif;
    } catch (e) {
      console.warn('Desktop notification dispatch failed:', e);
    }
  }
}
