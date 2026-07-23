(function () {
    "use strict";

    var PICKER_URL = "/admin/index.php?module=media&action=picker";

    var backdrop = null;
    var body = null;
    var searchInput = null;
    var currentField = null;
    var currentMode = null;
    var searchTimer = null;

    function ensureModal() {
        if (backdrop) {
            return;
        }

        backdrop = document.createElement("div");
        backdrop.className = "media-modal-backdrop";
        backdrop.innerHTML =
            '<div class="media-modal">' +
                '<div class="media-modal__header">' +
                    '<h3>Choose from Library</h3>' +
                    '<button type="button" class="media-modal__close" aria-label="Close">&times;</button>' +
                '</div>' +
                '<div class="media-modal__search">' +
                    '<input type="search" class="form-control" placeholder="Search by filename...">' +
                '</div>' +
                '<div class="media-modal__body"></div>' +
            '</div>';
        document.body.appendChild(backdrop);

        body = backdrop.querySelector(".media-modal__body");
        searchInput = backdrop.querySelector(".media-modal__search input");

        backdrop.addEventListener("click", function (e) {
            if (e.target === backdrop) {
                closeModal();
            }
        });
        backdrop.querySelector(".media-modal__close").addEventListener("click", closeModal);

        searchInput.addEventListener("input", function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                loadGrid(searchInput.value);
            }, 300);
        });

        body.addEventListener("click", function (e) {
            var item = e.target.closest(".media-grid__item");
            if (item) {
                pick(item);
            }
        });
    }

    function loadGrid(search) {
        var url = PICKER_URL + (search ? "&search=" + encodeURIComponent(search) : "");
        body.innerHTML = "<p>Loading...</p>";
        fetch(url)
            .then(function (res) { return res.text(); })
            .then(function (html) { body.innerHTML = html; })
            .catch(function () { body.innerHTML = "<p>Could not load the library. Please try again.</p>"; });
    }

    function openModal(field, mode) {
        ensureModal();
        currentField = field;
        currentMode = mode;
        searchInput.value = "";
        backdrop.classList.add("is-open");
        loadGrid("");
    }

    function closeModal() {
        if (backdrop) {
            backdrop.classList.remove("is-open");
        }
        currentField = null;
        currentMode = null;
    }

    function pick(item) {
        var mediaId = item.getAttribute("data-media-id");
        var preview = item.getAttribute("data-preview");
        if (!currentField || !mediaId) {
            return;
        }

        if (currentMode === "multi") {
            addChip(currentField, mediaId, preview);
            return; // leave modal open for further picks
        }

        var hidden = currentField.querySelector('input[name="picked_media_id"]');
        var previewImg = currentField.querySelector(".js-media-preview");
        var fileInput = currentField.querySelector('input[type="file"]');

        if (hidden) {
            hidden.value = mediaId;
        }
        if (previewImg) {
            previewImg.src = preview;
            previewImg.style.display = "";
        }
        if (fileInput) {
            fileInput.value = "";
        }

        closeModal();
    }

    function addChip(field, mediaId, preview) {
        var list = field.querySelector(".js-picked-list");
        if (!list) {
            return;
        }
        if (list.querySelector('input[value="' + mediaId + '"]')) {
            return; // already picked
        }

        var chip = document.createElement("span");
        chip.className = "media-picked-chip";
        chip.innerHTML =
            '<img src="' + preview + '" alt="">' +
            '<input type="hidden" name="picked_media_ids[]" value="' + mediaId + '">' +
            '<button type="button" aria-label="Remove">&times;</button>';
        chip.querySelector("button").addEventListener("click", function () {
            chip.remove();
        });
        list.appendChild(chip);
    }

    document.querySelectorAll(".js-open-media-picker").forEach(function (button) {
        button.addEventListener("click", function () {
            var field = button.closest(".js-media-field");
            if (field) {
                openModal(field, button.getAttribute("data-picker-mode") || "single");
            }
        });
    });

    // Switching back to a raw file upload after having picked from the
    // library should not silently keep the stale picked_media_id around.
    document.querySelectorAll(".js-media-field input[type=\"file\"]").forEach(function (input) {
        input.addEventListener("change", function () {
            var field = input.closest(".js-media-field");
            var hidden = field && field.querySelector('input[name="picked_media_id"]');
            if (hidden) {
                hidden.value = "";
            }
        });
    });
})();
