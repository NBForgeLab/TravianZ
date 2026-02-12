(() => {
  function setCookie(name, value, expiresUtc) {
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expiresUtc}; path=/`;
  }

  function on(elements, eventName, handler) {
    elements.forEach((el) => el.addEventListener(eventName, handler));
  }

  function initDynamicImages() {
    const imgs = Array.from(document.querySelectorAll('.dynamic_img'));
    if (imgs.length === 0) return;

    on(imgs, 'mouseenter', function () {
      this.classList.add('over');
    });

    on(imgs, 'mouseleave', function () {
      this.classList.remove('over');
      this.classList.remove('clicked');
    });

    on(imgs, 'mousedown', function () {
      this.classList.remove('over');
      this.classList.add('clicked');
    });
  }

  function initTableSwitches() {
    const switches = Array.from(document.querySelectorAll('img.tSwitch'));
    if (switches.length === 0) return;

    on(switches, 'mousedown', function () {
      const thead = this.closest('thead');
      const table = this.closest('table');
      const tbody = thead ? thead.nextElementSibling : table ? table.querySelector('tbody') : null;
      if (!tbody) return;

      tbody.classList.toggle('hide');

      const tableId = table && table.id ? table.id : '';
      if (tbody.classList.contains('hide')) {
        if (tableId) setCookie(`t3${tableId}`, '1', 'Wed, 1 Jan 2030 00:00:00 GMT');
        this.classList.remove('opened');
        this.classList.add('closed');
      } else {
        if (tableId) setCookie(`t3${tableId}`, '1', 'Thu, 01-Jan-1970 00:00:01 GMT');
        this.classList.remove('closed');
        this.classList.add('opened');
      }
    });
  }

  function initRowTableHover() {
    const rows = Array.from(document.querySelectorAll('table.row_table_data tbody tr'));
    if (rows.length === 0) return;

    on(rows, 'mouseenter', function () {
      this.classList.add('hlight');
    });

    on(rows, 'mouseleave', function () {
      this.classList.remove('hlight');
    });

    on(rows, 'mousedown', function () {
      this.classList.toggle('marked');
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initDynamicImages();
    initTableSwitches();
    initRowTableHover();
  });
})();
