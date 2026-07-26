/**
 * Reliable fallback for the per-page "hero" banner (destinations, packages,
 * etc). These sections use an Elementor JS-driven image slideshow that may
 * not render — this adds a plain, guaranteed-visible background behind the
 * page title, using the admin's configured override image if set, or the
 * page's own first slideshow image otherwise. Runs on every page; it's a
 * silent no-op on pages with no matching hero element.
 */
(function () {
    var target = document.querySelector('[data-settings*="&quot;background_background&quot;:&quot;slideshow&quot;"]');
    if (!target) return;

    function applyBackground(url) {
        if (!url || target.querySelector('.pq-hero-bg-override')) return;
        target.style.position = target.style.position || 'relative';
        var bg = document.createElement('div');
        bg.className = 'pq-hero-bg-override';
        bg.style.backgroundImage = 'url(' + url + ')';
        target.insertBefore(bg, target.firstChild);

        var inner = target.querySelector('.e-con-inner');
        if (inner) {
            inner.style.position = 'relative';
            inner.style.zIndex = '1';
        }
    }

    function firstOwnSlideImage() {
        try {
            var settings = JSON.parse(target.getAttribute('data-settings') || '{}');
            var gallery = settings.background_slideshow_gallery;
            return gallery && gallery[0] ? gallery[0].url : null;
        } catch (e) {
            return null;
        }
    }

    fetch('api/site-settings.php')
        .then(function (r) { return r.json(); })
        .then(function (res) {
            var override = res.status === 'success' && res.data ? res.data.page_hero_image_url : '';
            applyBackground(override || firstOwnSlideImage());
        })
        .catch(function () {
            applyBackground(firstOwnSlideImage());
        });
})();
