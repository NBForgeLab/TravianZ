(() => {
  function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + (days || 365) * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${encodeURIComponent(value)};expires=${d.toUTCString()};path=/`;
  }

  function getCookie(name) {
    const prefix = `${name}=`;
    const parts = document.cookie.split(';');
    for (let c of parts) {
      c = c.trim();
      if (c.indexOf(prefix) === 0) {
        return decodeURIComponent(c.substring(prefix.length));
      }
    }
    return '';
  }

  function initAccordionMenu() {
    const menu = document.querySelector('#menu');
    if (!menu) return;

    const subLinks = Array.from(menu.querySelectorAll(':scope > li.sub > a'));
    const subLists = Array.from(menu.querySelectorAll(':scope > li.sub > ul'));

    const savedNav = getCookie('sub-nav');
    if (savedNav !== '') {
      const idx = parseInt(savedNav, 10);
      const link = subLinks[idx];
      if (link && link.nextElementSibling) {
        link.classList.add('active');
        link.nextElementSibling.style.display = 'block';
      }
    }

    subLinks.forEach((link, i) => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        setCookie('sub-nav', i, 365);

        subLists.forEach((ul) => {
          ul.style.display = 'none';
        });

        const next = link.nextElementSibling;
        if (next && next.tagName === 'UL') {
          next.style.display = next.style.display === 'block' ? 'none' : 'block';
        }

        const allLinks = Array.from(menu.querySelectorAll('a'));
        allLinks.forEach((a) => a.classList.remove('active'));
        link.classList.add('active');
      });
    });

    const deepLinks = Array.from(menu.querySelectorAll(':scope > li.sub > ul li a'));
    const savedSubLink = getCookie('sub-link');
    if (savedSubLink !== '') {
      const idx = parseInt(savedSubLink, 10);
      const a = deepLinks[idx];
      if (a) a.classList.add('active');
    }

    deepLinks.forEach((a, i) => {
      a.addEventListener('click', () => {
        setCookie('sub-link', i, 365);
        deepLinks.forEach((x) => x.classList.remove('active'));
        a.classList.add('active');
      });
    });
  }

  document.addEventListener('DOMContentLoaded', initAccordionMenu);
})();
