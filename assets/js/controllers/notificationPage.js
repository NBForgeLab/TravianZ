(() => {
  const { dom } = window.TravianZ || {};
  if (!dom) return;

  const addTranslations = () => {
    const raw = document.body && document.body.dataset ? document.body.dataset.trzTranslations : "";
    if (!raw) return;
    let entries;
    try {
      entries = JSON.parse(raw);
    } catch {
      return;
    }
    if (!entries || typeof entries !== "object") return;
    const travian = window.Travian;
    if (!travian || !travian.Translation || typeof travian.Translation.add !== "function") return;
    travian.Translation.add(entries);
  };

  dom.ready(() => {
    addTranslations();
  });
})();

