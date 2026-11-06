/** Small DOM helpers. Everything user-provided is set with textContent, never innerHTML. */

export const qs = (selector, root = document) => root.querySelector(selector);
export const qsa = (selector, root = document) => Array.from(root.querySelectorAll(selector));

/** Event delegation: handler(event, matchedElement). */
export function on(root, type, selector, handler, options) {
    root.addEventListener(type, (event) => {
        const target = event.target instanceof Element ? event.target.closest(selector) : null;
        if (target && root.contains(target)) handler(event, target);
    }, options);
}

/**
 * el('button', { class: 'btn', dataset: { unit: 'day' }, onclick: fn }, 'Text', childNode)
 */
export function el(tag, props = {}, ...children) {
    const node = document.createElement(tag);
    for (const [key, value] of Object.entries(props)) {
        if (value === null || value === undefined || value === false) continue;
        if (key === 'class') node.className = value;
        else if (key === 'dataset') Object.assign(node.dataset, value);
        else if (key === 'style') node.style.cssText = value;
        else if (key.startsWith('on') && typeof value === 'function') node.addEventListener(key.slice(2), value);
        else if (value === true) node.setAttribute(key, '');
        else node.setAttribute(key, String(value));
    }
    for (const child of children.flat()) {
        if (child === null || child === undefined || child === false) continue;
        node.append(child instanceof Node ? child : document.createTextNode(String(child)));
    }
    return node;
}

/** Inline icon from the SVG sprite rendered by views/partials/ui/icons.php. */
export function icon(name, className = 'icon') {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('class', className);
    svg.setAttribute('aria-hidden', 'true');
    const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
    use.setAttribute('href', `#i-${name}`);
    svg.append(use);
    return svg;
}

export function meta(name) {
    return document.querySelector(`meta[name="${name}"]`)?.getAttribute('content') ?? '';
}

export function debounce(fn, wait = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
}

export function readJson(value, fallback = null) {
    try {
        return value ? JSON.parse(value) : fallback;
    } catch {
        return fallback;
    }
}
