(() => {
  if (window.TravianZ && window.TravianZ.__booted) {
    return;
  }

  window.TravianZ = window.TravianZ || {};
  window.TravianZ.__booted = true;

  const controllerName = document.body && document.body.dataset && document.body.dataset.controller;

  const controllerMap = {
    index: "assets/js/controllers/indexPage.js",
    notification: "assets/js/controllers/notificationPage.js",
  };

  const readVersionFromSrc = (src) => {
    if (!src) return "";
    const match = src.match(/[?&]v=([^&]+)/);
    if (!match) return "";
    try {
      return decodeURIComponent(match[1]);
    } catch {
      return match[1];
    }
  };

  const bootstrapSrc =
    (document.currentScript && document.currentScript.src) ||
    Array.from(document.getElementsByTagName("script"))
      .map((s) => s.src || "")
      .find((src) => src.includes("/assets/js/app/bootstrap.js") || src.includes("\\assets\\js\\app\\bootstrap.js")) ||
    "";

  const version = readVersionFromSrc(bootstrapSrc);

  const withVersion = (url) => {
    if (!version) return url;
    const join = url.includes("?") ? "&" : "?";
    return `${url}${join}v=${encodeURIComponent(version)}`;
  };

  const loadScript = (url) =>
    new Promise((resolve, reject) => {
      const el = document.createElement("script");
      el.src = withVersion(url);
      el.defer = true;
      el.onload = () => resolve();
      el.onerror = () => reject(new Error(`Failed to load ${url}`));
      document.head.appendChild(el);
    });

  const boot = async () => {
    await loadScript("assets/js/core/dom.js");
    await loadScript("assets/js/core/events.js");
    await loadScript("assets/js/core/i18n.js");
    await loadScript("assets/js/bridge/legacy.js");

    if (!controllerName) {
      return;
    }

    const controllerPath = controllerMap[controllerName];
    if (!controllerPath) {
      return;
    }

    await loadScript(controllerPath);
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      boot().catch(() => {});
    });
  } else {
    boot().catch(() => {});
  }
})();
