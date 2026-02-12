(() => {
  window.TravianZ = window.TravianZ || {};

  const qs = (selector, root = document) => root.querySelector(selector);
  const qsa = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  const closest = (el, selector) => {
    if (!el) return null;
    return el.closest(selector);
  };

  const ready = (fn) => {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn, { once: true });
      return;
    }
    fn();
  };

  window.TravianZ.dom = {
    qs,
    qsa,
    closest,
    ready,
  };
})();

