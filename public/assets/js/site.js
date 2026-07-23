(function () {
    "use strict";

    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // Sticky header: solid background once the hero has been scrolled past.
    var header = document.getElementById("siteHeader");
    if (header) {
        var toggleHeader = function () {
            if (window.scrollY > 60) {
                header.classList.add("is-scrolled");
            } else {
                header.classList.remove("is-scrolled");
            }
        };
        toggleHeader();
        window.addEventListener("scroll", toggleHeader, { passive: true });
    }

    // Mobile nav toggle.
    var navToggle = document.getElementById("navToggle");
    var siteNav = document.getElementById("siteNav");
    if (navToggle && siteNav) {
        navToggle.addEventListener("click", function () {
            var isOpen = siteNav.classList.toggle("is-open");
            navToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });
        siteNav.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                siteNav.classList.remove("is-open");
                navToggle.setAttribute("aria-expanded", "false");
            });
        });
    }

    // Scroll-reveal animations (self-hosted AOS).
    if (window.AOS && !reduceMotion) {
        window.AOS.init({
            duration: 700,
            easing: "ease-out-cubic",
            once: true,
            offset: 60,
        });
    }

    // Subtle hover tilt on cards (self-hosted vanilla-tilt), skipped for touch/reduced-motion.
    if (window.VanillaTilt && !reduceMotion && window.matchMedia("(hover: hover)").matches) {
        var tiltEls = document.querySelectorAll(".js-tilt");
        if (tiltEls.length) {
            window.VanillaTilt.init(tiltEls, {
                max: 6,
                speed: 400,
                glare: false,
                scale: 1.01,
            });
        }
    }

    // Shop Mascardi homepage carousel: prev/next paginate by one full slide.
    var track = document.getElementById("shopCarouselTrack");
    var prevBtn = document.getElementById("shopCarouselPrev");
    var nextBtn = document.getElementById("shopCarouselNext");
    if (track && (prevBtn || nextBtn)) {
        var scrollByPage = function (direction) {
            track.scrollBy({ left: track.clientWidth * direction, behavior: reduceMotion ? "auto" : "smooth" });
        };
        if (prevBtn) prevBtn.addEventListener("click", function () { scrollByPage(-1); });
        if (nextBtn) nextBtn.addEventListener("click", function () { scrollByPage(1); });

        var updateNavState = function () {
            if (prevBtn) prevBtn.disabled = track.scrollLeft <= 4;
            if (nextBtn) nextBtn.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 4;
        };
        track.addEventListener("scroll", updateNavState, { passive: true });
        updateNavState();
    }

    // Checkout waiting page: poll M-Pesa payment status until it resolves.
    var waitingEl = document.getElementById("paymentWaiting");
    if (waitingEl) {
        var ref = waitingEl.getAttribute("data-ref");
        var statusUrl = waitingEl.getAttribute("data-status-url");
        var confirmationUrl = waitingEl.getAttribute("data-confirmation-url");
        var waitingState = waitingEl.querySelector('[data-state="waiting"]');
        var failedState = waitingEl.querySelector('[data-state="failed"]');

        if (ref && statusUrl) {
            var attempts = 0;
            var maxAttempts = 30; // ~90s at 3s intervals

            var poll = function () {
                attempts++;
                fetch(statusUrl + "?ref=" + encodeURIComponent(ref))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.status === "success") {
                            window.location.href = confirmationUrl;
                            return;
                        }
                        if (data.status === "failed" || data.status === "cancelled" || data.status === "timeout") {
                            if (waitingState) waitingState.style.display = "none";
                            if (failedState) failedState.style.display = "block";
                            return;
                        }
                        if (attempts < maxAttempts) {
                            setTimeout(poll, 3000);
                        } else if (failedState) {
                            if (waitingState) waitingState.style.display = "none";
                            failedState.style.display = "block";
                        }
                    })
                    .catch(function () {
                        if (attempts < maxAttempts) setTimeout(poll, 3000);
                    });
            };

            setTimeout(poll, 3000);
        }
    }

    // Product detail page: click a thumbnail to swap the main image.
    var mainImageWrap = document.getElementById("mainImage");
    document.querySelectorAll(".js-thumb").forEach(function (thumb) {
        thumb.addEventListener("click", function () {
            var full = thumb.getAttribute("data-full");
            var mainImg = mainImageWrap ? mainImageWrap.querySelector("img") : null;
            if (full && mainImg) {
                mainImg.src = full;
            }
        });
    });
})();
