(() => {
  window.TravianZ = window.TravianZ || {};

  const delegate = (root, eventType, selector, handler, options) => {
    const resolvedRoot = root || document;
    const listener = (event) => {
      const target = event.target;
      if (!(target instanceof Element)) return;
      const matched = target.closest(selector);
      if (!matched) return;
      handler(event, matched);
    };

    resolvedRoot.addEventListener(eventType, listener, options);
    return () => resolvedRoot.removeEventListener(eventType, listener, options);
  };

  const on = (el, eventType, handler, options) => {
    el.addEventListener(eventType, handler, options);
    return () => el.removeEventListener(eventType, handler, options);
  };

  const actions = new Map();

  const registerAction = (name, handler) => {
    if (typeof name !== "string" || !name) return;
    if (typeof handler !== "function") return;
    actions.set(name, handler);
  };

  const initActionDelegation = (root = document) => {
    delegate(root, "click", "[data-action]", (event, el) => {
      const action = el.dataset ? el.dataset.action : "";
      const handler = actions.get(action);
      if (!handler) return;
      handler(event, el);
    });
  };

  initActionDelegation(document);

  window.TravianZ.events = {
    delegate,
    on,
    registerAction,
  };
})();
