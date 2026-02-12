(() => {
  const { dom, legacy } = window.TravianZ || {};
  if (!dom || !legacy) return;

  dom.ready(() => {
    const container = document.getElementById("country_select");
    const raw = container && container.dataset ? container.dataset.regionList : "";
    const regions = raw
      ? raw.split(",").map((s) => s.trim()).filter(Boolean)
      : ["Europe", "America", "Asia", "Middle East", "Africa", "Oceania"];

    legacy.show_flags("", "", regions);
  });
})();

