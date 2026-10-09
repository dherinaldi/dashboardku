// Full TV mode: auto-scroll halus ke bawah, mentok balik atas, loop terus.
// Tidak mengganggu AJAX refresh (data tetap realtime).
$(function () {
    const SPEED_PX   = 1;     // pixel per frame saat bergulir (makin besar makin cepat)
    const STEP_MS    = 16;    // interval antar langkah (~60fps)
    const PAUSE_TOP  = 4000;  // jeda di paling atas (ms)
    const PAUSE_BOT  = 4000;  // jeda di paling bawah (ms)

    const doc = document.documentElement;
    let dir = 1;              // 1 = turun, -1 = naik
    let paused = true;        // mulai dengan jeda di atas

    function maxScroll() {
        return Math.max(0, doc.scrollHeight - window.innerHeight);
    }

    function step() {
        if (paused) return;
        const max = maxScroll();
        if (max <= 0) return;                     // konten belum perlu scroll

        const y = window.scrollY + dir * SPEED_PX;

        if (y >= max) {                           // sampai bawah
            window.scrollTo(0, max);
            holdThen(() => { dir = -1; }, PAUSE_BOT);
            return;
        }
        if (y <= 0) {                             // sampai atas
            window.scrollTo(0, 0);
            holdThen(() => { dir = 1; }, PAUSE_TOP);
            return;
        }
        window.scrollTo(0, y);
    }

    function holdThen(fn, ms) {
        paused = true;
        setTimeout(() => { fn(); paused = false; }, ms);
    }

    // Mulai: jeda sejenak di atas, lalu gulir
    setTimeout(() => { paused = false; }, PAUSE_TOP);
    setInterval(step, STEP_MS);
});
