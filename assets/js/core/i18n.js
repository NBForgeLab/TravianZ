(() => {
  window.TravianZ = window.TravianZ || {};

  const state = {
    locale: "en",
    dict: {},
  };

  const setLocale = (locale) => {
    if (typeof locale !== "string" || !locale) return;
    state.locale = locale;
  };

  const add = (locale, entries) => {
    if (typeof locale !== "string" || !locale) return;
    if (!entries || typeof entries !== "object") return;
    state.dict[locale] = state.dict[locale] || {};
    Object.assign(state.dict[locale], entries);
  };

  const t = (key, fallback) => {
    const dict = state.dict[state.locale] || {};
    if (Object.prototype.hasOwnProperty.call(dict, key)) {
      return String(dict[key]);
    }
    if (fallback !== undefined) return String(fallback);
    return String(key);
  };

  window.TravianZ.i18n = {
    setLocale,
    add,
    t,
  };
})();

