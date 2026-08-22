/**
 * Poll a Laravel background task URL until status is completed or failed.
 *
 * @param {string} pollUrl - e.g. route('background-tasks.show', taskId)
 * @param {object} [options]
 * @param {number} [options.intervalMs=1500]
 * @param {function(object):void} [options.onUpdate] - called each poll with JSON body
 * @param {AbortSignal} [options.signal] - aborts polling
 * @returns {Promise<object>} final JSON payload
 */
window.pollBackgroundTask = function pollBackgroundTask(pollUrl, options) {
    options = options || {};
    var intervalMs = options.intervalMs || 1500;
    var onUpdate = options.onUpdate;
    var signal = options.signal;

    return new Promise(function (resolve, reject) {
        function stop() {
            if (timer) clearInterval(timer);
        }

        function fatal(err) {
            stop();
            reject(err);
        }

        var timer = setInterval(async function () {
            if (signal && signal.aborted) {
                stop();
                reject(new Error('Aborted'));
                return;
            }
            try {
                var r = await fetch(pollUrl, {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                var data = await r.json();
                if (!r.ok) {
                    stop();
                    reject(new Error(data.error || data.message || 'Poll failed'));
                    return;
                }
                if (typeof onUpdate === 'function') onUpdate(data);
                var st = data.status;
                if (st === 'completed' || st === 'failed') {
                    stop();
                    resolve(data);
                }
            } catch (e) {
                fatal(e);
            }
        }, intervalMs);

        // First fetch immediately
        (async function () {
            try {
                var r = await fetch(pollUrl, {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                var data = await r.json();
                if (!r.ok) {
                    stop();
                    reject(new Error(data.error || data.message || 'Poll failed'));
                    return;
                }
                if (typeof onUpdate === 'function') onUpdate(data);
                var st = data.status;
                if (st === 'completed' || st === 'failed') {
                    stop();
                    resolve(data);
                }
            } catch (e) {
                fatal(e);
            }
        })();
    });
};
