/**
 * VidaNexus AI - Progressive Web App Client Controller
 */
(function () {
    'use strict';

    let deferredPrompt = null;
    let isPwaInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function (registration) {
                    // Check for SW updates
                    registration.addEventListener('updatefound', function () {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', function () {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('[VidaNexus PWA] New update available.');
                                }
                            });
                        }
                    });
                })
                .catch(function (error) {
                    console.warn('[VidaNexus PWA] ServiceWorker registration failed:', error);
                });
        });
    }

    // 2. Capture Install Prompt Event (Chrome / Edge / Android)
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;

        // Show install button or notify UI if element exists
        const installBtns = document.querySelectorAll('.pwa-install-btn');
        installBtns.forEach(function (btn) {
            btn.style.display = 'inline-flex';
            btn.classList.remove('hidden');
        });

        // Trigger custom event so any Blade/Vue/Alpine component can react
        window.dispatchEvent(new CustomEvent('pwa-installable', { detail: { prompt: e } }));
    });

    // 3. Track App Installed Event
    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        isPwaInstalled = true;
        const installBtns = document.querySelectorAll('.pwa-install-btn');
        installBtns.forEach(function (btn) {
            btn.style.display = 'none';
        });
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'تم تثبيت التطبيق بنجاح!',
                showConfirmButton: false,
                timer: 2500,
                background: '#0f172a',
                color: '#fff'
            });
        }
    });

    // 4. Global Function to Trigger Installation
    window.installPWA = function () {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function (choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    console.log('[VidaNexus PWA] User accepted installation.');
                } else {
                    console.log('[VidaNexus PWA] User dismissed installation.');
                }
                deferredPrompt = null;
            });
            return;
        }

        // iOS Safari Instructions Fallback
        const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
        if (isIos && !isPwaInstalled) {
            if (window.Swal) {
                Swal.fire({
                    title: 'تثبيت التطبيق على آيفون',
                    html: `
                        <div class="text-right text-sm space-y-3 py-2 text-slate-200">
                            <p>لتثبيت منصة <b>VidaNexus AI</b> على شاشتك الرئيسية:</p>
                            <div class="p-3 bg-white/5 border border-white/10 rounded-xl space-y-2">
                                <p class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">1</span>
                                    <span>اضغط على زر المشاركة <i class="fas fa-arrow-up-from-bracket text-cyan-400"></i> أسفل متصفح Safari.</span>
                                </p>
                                <p class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">2</span>
                                    <span>اختر <b>"إضافة إلى الصفحة الرئيسية"</b> (Add to Home Screen).</span>
                                </p>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'فهمت',
                    confirmButtonColor: '#0ea5e9',
                    background: '#0f172a',
                    color: '#fff'
                });
            } else {
                alert('لتثبيت التطبيق: اضغط على زر المشاركة في Safari ثم اختر "إضافة إلى الصفحة الرئيسية"');
            }
            return;
        }

        if (isPwaInstalled) {
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'التطبيق مثبت بالفعل على جهازك!',
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#0f172a',
                    color: '#fff'
                });
            }
            return;
        }

        // Fallback guidance for desktop / other browsers
        if (window.Swal) {
            Swal.fire({
                title: 'تثبيت تطبيق VidaNexus AI',
                html: `
                    <div class="text-right text-sm space-y-3 py-2 text-slate-200">
                        <p>يمكنك تثبيت المنصة كتطبيق سطح مكتب أو هاتف بسهولة:</p>
                        <div class="p-3 bg-white/5 border border-white/10 rounded-xl space-y-2">
                            <p class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">1</span>
                                <span>اضغط على أيقونة التثبيت <i class="fas fa-desktop text-cyan-400"></i> في شريط العنوان (URL bar) بأعلى المتصفح.</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 font-bold flex items-center justify-center text-xs">2</span>
                                <span>أو افتح قائمة خيارات المتصفح <i class="fas fa-ellipsis-vertical text-cyan-400"></i> واضغط <b>"تثبيت التطبيق" (Install)</b>.</span>
                            </p>
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'فهمت',
                confirmButtonColor: '#0ea5e9',
                background: '#0f172a',
                color: '#fff'
            });
        }
    };

    // 5. Network Connectivity Notifications
    window.addEventListener('online', function () {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'عادت شبكة الإنترنت للعمل 🟢',
                showConfirmButton: false,
                timer: 2000,
                background: '#0f172a',
                color: '#fff'
            });
        }
    });

    window.addEventListener('offline', function () {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'انقطع الاتصال بالإنترنت ⚠️',
                showConfirmButton: false,
                timer: 3000,
                background: '#0f172a',
                color: '#fff'
            });
        }
    });

})();
