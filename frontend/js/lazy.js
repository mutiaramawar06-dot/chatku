/**
 * Lazy Loading Module (FR-24, Kriteria 5)
 * Defers loading of images in contacts and message bubbles until they enter the viewport
 * using IntersectionObserver.
 */

let observer = null;

function getObserver() {
    if (!observer && 'IntersectionObserver' in window) {
        observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const dataSrc = img.getAttribute('data-src');
                    if (dataSrc) {
                        img.src = dataSrc;
                        img.removeAttribute('data-src');
                        img.classList.add('image-loaded');
                    }
                    obs.unobserve(img);
                }
            });
        }, {
            root: null,
            rootMargin: '50px',
            threshold: 0.1,
        });
    }
    return observer;
}

export function observeLazyImages(container = document) {
    const lazyImages = container.querySelectorAll('img[data-src]');
    const obs = getObserver();

    lazyImages.forEach(img => {
        if (obs) {
            obs.observe(img);
        } else {
            // Fallback for environments without IntersectionObserver
            const dataSrc = img.getAttribute('data-src');
            if (dataSrc) {
                img.src = dataSrc;
                img.removeAttribute('data-src');
            }
        }
    });
}
