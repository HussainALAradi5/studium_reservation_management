/** Reveals [data-animate] elements when they scroll into view (CSS in components/animations.css). */
import { qsa } from '../core/dom.js';

export function initAnimations(root = document) {
    const targets = qsa('[data-animate]:not(.is-visible)', root);
    if (!('IntersectionObserver' in window)) {
        targets.forEach((target) => target.classList.add('is-visible'));
        return;
    }
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -40px 0px', threshold: 0.05 });
    targets.forEach((target) => observer.observe(target));
}
