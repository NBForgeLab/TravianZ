(() => {
  window.TravianZ = window.TravianZ || {};

  const callIfFn = (fn, args) => {
    if (typeof fn !== "function") return undefined;
    return fn.apply(window, args);
  };

  window.TravianZ.legacy = {
    Popup: (...args) => callIfFn(window.Popup, args),
    show_flags: (...args) => callIfFn(window.show_flags, args),
  };

  const events = window.TravianZ.events;
  if (events && typeof events.registerAction === "function") {
    const parsePopupArgs = (raw) => {
      if (typeof raw !== "string") return [];
      const match = raw.match(/Popup\s*\(([^)]*)\)/);
      if (!match) return [];
      const argsRaw = match[1]
        .split(",")
        .map((s) => s.trim())
        .filter(Boolean)
        .map((s) => {
          if (
            (s.startsWith("'") && s.endsWith("'")) ||
            (s.startsWith('"') && s.endsWith('"'))
          ) {
            return s.slice(1, -1);
          }
          return s;
        });
      return argsRaw;
    };

    events.registerAction("popup", (event, el) => {
      event.preventDefault();
      const raw = (el.dataset && (el.dataset.popupArgs || el.dataset.popup)) || "";
      if (!raw) {
        window.TravianZ.legacy.Popup();
        return;
      }
      let args = [];
      if (raw.trim().startsWith("[")) {
        try {
          args = JSON.parse(raw);
        } catch {
          args = [];
        }
      } else {
        args = raw.split(",").map((s) => s.trim());
      }
      if (!Array.isArray(args)) args = [];
      window.TravianZ.legacy.Popup(...args);
    });

    const neutralizeInlinePopupHandlers = () => {
      const nodes = document.querySelectorAll("[onclick]");
      for (const el of nodes) {
        const raw = el.getAttribute("onclick") || "";
        if (!raw.includes("Popup")) continue;
        const args = parsePopupArgs(raw);
        if (!args.length) continue;
        el.removeAttribute("onclick");
        el.addEventListener(
          "click",
          (event) => {
            event.preventDefault();
            window.TravianZ.legacy.Popup(...args);
          },
          { passive: false }
        );
      }
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", neutralizeInlinePopupHandlers, { once: true });
    } else {
      neutralizeInlinePopupHandlers();
    }
  }
})();
