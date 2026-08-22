/**
 * VidaNexus — Live Credit Counter
 * ---------------------------------------------------------------
 * Keeps every CRS / credit display on the page (navbar chip, dashboard
 * stats, billing panel, tool usage badges) in sync with the server's
 * wallet balance, without needing a full page refresh.
 *
 * Three integration paths (in order of preference):
 *   1) Generic: `window.VidaCredits.apply(responseJson)`
 *      - Reads `balance` from the JSON if present, otherwise falls back
 *        to a network refresh. This is the recommended one-liner for
 *        any tool's success callback.
 *   2) Action JSON already includes the new balance → call
 *      `window.VidaCredits.updateAll(balance)` after a successful fetch.
 *   3) Action JSON does not include it (e.g. legacy module endpoints) →
 *      call `window.VidaCredits.refresh()` after the fetch and the module
 *      will pull the current balance from /dashboard/credits/balance.
 *
 * Listeners:
 *   - dispatching `new CustomEvent('credits:request-refresh')` on
 *     `document` triggers a refresh.
 *   - elements get a flash + animated number on every update.
 *
 * Targeted DOM:
 *   Any element with class `js-credit-balance`. Optional data attributes:
 *     data-decimals  (default 2)  - decimal digits in the rendered number
 *     data-prefix                  - text prepended to the number
 *     data-suffix                  - text appended (e.g. " Credits")
 *     data-credit-value            - last known numeric value (kept in sync)
 */
(function () {
    if (window.VidaCredits) return;

    var SELECTOR = '.js-credit-balance';
    var lastKnownBalance = null;
    var refreshUrl = null;
    var refreshing = false;
    var ANIMATION_MS = 700;

    function readMeta(name) {
        var el = document.querySelector('meta[name="' + name + '"]');
        return el ? el.getAttribute('content') : null;
    }

    function format(value, decimals) {
        var n = Number(value);
        if (isNaN(n)) n = 0;
        try {
            return n.toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
        } catch (e) {
            return n.toFixed(decimals);
        }
    }

    function render(el, value) {
        var decimals = parseInt(el.dataset.decimals || '2', 10);
        if (isNaN(decimals) || decimals < 0) decimals = 2;
        var prefix = el.dataset.prefix || '';
        var suffix = el.dataset.suffix || '';
        el.textContent = prefix + format(value, decimals) + suffix;
    }

    function animateElement(el, toValue) {
        var fromRaw = parseFloat(el.dataset.creditValue);
        var to = Number(toValue);
        if (isNaN(to)) return;

        var from = isNaN(fromRaw) ? to : fromRaw;
        el.dataset.creditValue = String(to);

        if (from === to) {
            render(el, to);
            return;
        }

        var dirClass = to < from ? 'credit-flash-down' : 'credit-flash-up';
        el.classList.remove('credit-flash-down', 'credit-flash-up');
        // force reflow so the class re-application re-triggers any CSS transition
        void el.offsetWidth;
        el.classList.add(dirClass);
        window.setTimeout(function () {
            el.classList.remove(dirClass);
        }, 900);

        var start = null;
        function step(ts) {
            if (start === null) start = ts;
            var t = Math.min(1, (ts - start) / ANIMATION_MS);
            var eased = 1 - Math.pow(1 - t, 3); // ease-out cubic
            var current = from + (to - from) * eased;
            render(el, current);
            if (t < 1) {
                window.requestAnimationFrame(step);
            } else {
                render(el, to);
            }
        }
        window.requestAnimationFrame(step);
    }

    function updateAll(value) {
        var v = Number(value);
        if (isNaN(v)) return;
        lastKnownBalance = v;
        var nodes = document.querySelectorAll(SELECTOR);
        for (var i = 0; i < nodes.length; i++) {
            animateElement(nodes[i], v);
        }
        document.dispatchEvent(new CustomEvent('credits:changed', {
            detail: { balance: v },
        }));
    }

    function refresh() {
        if (refreshing || !refreshUrl) {
            return Promise.resolve(lastKnownBalance);
        }
        refreshing = true;
        return fetch(refreshUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            cache: 'no-store',
        })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (data && typeof data.balance !== 'undefined') {
                    updateAll(data.balance);
                }
                return data ? data.balance : null;
            })
            .catch(function () {
                return null;
            })
            .then(function (result) {
                refreshing = false;
                return result;
            });
    }

    function readInitial() {
        var el = document.querySelector(SELECTOR + '[data-credit-value]');
        if (!el) return null;
        var v = parseFloat(el.dataset.creditValue);
        return isNaN(v) ? null : v;
    }

    function init() {
        refreshUrl = readMeta('credits-balance-url');
        lastKnownBalance = readInitial();
        document.addEventListener('credits:request-refresh', function () {
            refresh();
        });
    }

    /**
     * Drop-in helper for tool success callbacks. Pass the response JSON
     * (or anything with a numeric `balance` key) and the chip animates;
     * if `balance` is missing/null (e.g. cached responses that did not
     * deduct anything), we fall back to /dashboard/credits/balance.
     */
    function apply(data) {
        if (data && typeof data === 'object'
            && typeof data.balance !== 'undefined'
            && data.balance !== null) {
            var n = Number(data.balance);
            if (!isNaN(n) && n >= 0) {
                updateAll(n);
                return Promise.resolve(n);
            }
        }
        return refresh();
    }

    window.VidaCredits = {
        updateAll: updateAll,
        refresh: refresh,
        apply: apply,
        getBalance: function () { return lastKnownBalance; },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
