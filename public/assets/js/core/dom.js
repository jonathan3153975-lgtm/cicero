export const qs = (selector, scope = document) => scope.querySelector(selector);

export const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

export function on(target, event, handler, options = {}) {
    if (!target) {
        return () => {};
    }

    target.addEventListener(event, handler, options);

    return () => target.removeEventListener(event, handler, options);
}
