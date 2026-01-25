// Session Keep Alive - تحديث الـ session كل 5 دقائق لمنع انتهاء الجلسة
document.addEventListener("DOMContentLoaded", function () {
    // فقط للمستخدمين المسجلين
    if (document.body.dataset.authenticated === "true") {
        setInterval(
            () => {
                fetch("/keep-alive", {
                    method: "GET",
                    credentials: "same-origin",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                })
                    .then((response) => {
                        if (response.ok) {
                            console.log(
                                "[Session] Refreshed at",
                                new Date().toLocaleTimeString(),
                            );
                        }
                    })
                    .catch((err) => {
                        console.warn("[Session] Keep-alive failed:", err);
                    });
            },
            5 * 60 * 1000,
        ); // كل 5 دقائق
    }
});

// Livewire specific - إعادة تحديث عند اتصال Livewire
if (typeof Livewire !== "undefined") {
    document.addEventListener("livewire:init", () => {
        // تحديث مباشر عند بدء Livewire
        Livewire.hook("request", ({ fail }) => {
            fail(({ status }) => {
                // إذا انتهت الجلسة (419 = CSRF token expired, 401 = Unauthorized)
                if (status === 419 || status === 401) {
                    // إعادة تحميل الصفحة لتجديد الـ session
                    window.location.reload();
                }
            });
        });
    });
}
