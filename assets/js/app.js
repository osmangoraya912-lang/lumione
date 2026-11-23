// LumiCore One - live clock + delete confirmations only. No libraries.
(function () {
    function tickClock() {
        var clock = document.getElementById('live-clock');
        var dateEl = document.getElementById('live-date');
        if (!clock) {
            return;
        }
        var now = new Date();
        clock.textContent = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        }
    }
    tickClock();
    setInterval(tickClock, 1000);

    document.addEventListener('click', function (ev) {
        var el = ev.target.closest('[data-confirm]');
        if (!el) {
            return;
        }
        if (!window.confirm(el.getAttribute('data-confirm') || 'Are you sure?')) {
            ev.preventDefault();
        }
    });
})();
