/**
 * Front-end media + defensive boot:
 * - Kill Elementor page-transition preloader (stops endless URL-bar loading)
 * - Apply admin footer background image when PHP didn't inject it
 * - Apply inner-page hero fallback image from admin settings
 */
(function () {
    function apiUrl(file) {
        var scripts = document.getElementsByTagName('script');
        for (var i = scripts.length - 1; i >= 0; i--) {
            var src = scripts[i].src || '';
            if (src.indexOf('page-hero-fallback.js') !== -1) {
                return src.replace(/js\/page-hero-fallback\.js.*$/, 'api/' + file);
            }
        }
        var path = window.location.pathname || '/';
        var markers = ['/admin/', '/client/', '/api/', '/devs/', '/about-us/', '/packages/', '/destinations/'];
        for (var m = 0; m < markers.length; m++) {
            var idx = path.indexOf(markers[m]);
            if (idx >= 0) {
                return path.slice(0, idx) + '/api/' + file;
            }
        }
        if (/\/Pentagon(?:%20| )Quest(?:%20| )UI/i.test(path)) {
            var base = path.match(/^(\/Pentagon(?:%20| )Quest(?:%20| )UI)/i);
            return decodeURIComponent(base[1]) + '/api/' + file;
        }
        return '/api/' + file;
    }

    // Stop the endless browser loading spinner from Elementor page transitions
    function killPageTransition() {
        document.querySelectorAll('e-page-transition, .e-page-transition').forEach(function (el) {
            el.classList.remove('e-page-transition--entering', 'e-page-transition--exiting');
            el.remove();
        });
        document.documentElement.classList.remove('e-page-transition--entering');
        document.body && document.body.classList.remove('e-page-transition--entering');
    }
    killPageTransition();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', killPageTransition);
    }
    window.addEventListener('load', killPageTransition);

    function applyFooterBackground(url) {
        if (!url) return;
        var footerRoot = document.querySelector('.elementor-location-footer .elementor-element-fce0653')
            || document.querySelector('.elementor-location-footer .e-con.e-parent');
        if (!footerRoot || footerRoot.querySelector('.pq-footer-bg-override')) return;
        footerRoot.style.position = footerRoot.style.position || 'relative';
        var bg = document.createElement('div');
        bg.className = 'pq-footer-bg-override';
        bg.style.backgroundImage = "url('" + url.replace(/'/g, "%27") + "')";
        footerRoot.insertBefore(bg, footerRoot.firstChild);
        var inner = footerRoot.querySelector('.e-con-inner');
        if (inner) {
            inner.style.position = 'relative';
            inner.style.zIndex = '1';
        }
    }

    function applyPageHero(url) {
        var target = document.querySelector('[data-settings*="&quot;background_background&quot;:&quot;slideshow&quot;"]');
        if (!target || !url || target.querySelector('.pq-hero-bg-override')) return;
        target.style.position = target.style.position || 'relative';
        var bg = document.createElement('div');
        bg.className = 'pq-hero-bg-override';
        bg.style.backgroundImage = "url('" + url.replace(/'/g, "%27") + "')";
        target.insertBefore(bg, target.firstChild);
        var inner = target.querySelector('.e-con-inner');
        if (inner) {
            inner.style.position = 'relative';
            inner.style.zIndex = '1';
        }
    }

    function firstOwnSlideImage() {
        var target = document.querySelector('[data-settings*="&quot;background_background&quot;:&quot;slideshow&quot;"]');
        if (!target) return null;
        try {
            var settings = JSON.parse(target.getAttribute('data-settings') || '{}');
            var gallery = settings.background_slideshow_gallery;
            return gallery && gallery[0] ? gallery[0].url : null;
        } catch (e) {
            return null;
        }
    }

    fetch(apiUrl('site-settings.php'))
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res || res.status !== 'success' || !res.data) {
                applyPageHero(firstOwnSlideImage());
                return;
            }
            applyFooterBackground(res.data.footer_image_url || '');
            applyPageHero(res.data.page_hero_image_url || firstOwnSlideImage());
        })
        .catch(function () {
            applyPageHero(firstOwnSlideImage());
        });
})();
