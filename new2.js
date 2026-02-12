(() => {
  function pxInt(value) {
    const n = parseInt(value, 10);
    return Number.isFinite(n) ? n : 0;
  }

  function getStyleInt(el, prop) {
    return pxInt(getComputedStyle(el)[prop]);
  }

  function setDisplay(selector, display) {
    const el = document.querySelector(selector);
    if (el) el.style.display = display;
  }

  function show(el) {
    if (el) el.style.display = 'block';
  }

  function hide(el) {
    if (el) el.style.display = 'none';
  }

  function handleMove(element, dx, leftXLimit, rightXLimit) {
    const xStart = getStyleInt(element, 'left');
    let xDest = xStart + dx;
    xDest = Math.min(xDest, rightXLimit);
    xDest = Math.max(xDest, leftXLimit);

    setDisplay('#screenshots .next img', xDest === leftXLimit ? 'none' : 'block');
    setDisplay('#screenshots .prev img', xDest === rightXLimit ? 'none' : 'block');

    element.lastDest = xDest;

    if (!element.dataset.trzFxInit) {
      element.style.transition = 'left 0.4s ease-in-out';
      element.dataset.trzFxInit = '1';
    }

    element.style.left = `${xDest}px`;
  }

  function initOverlays() {
    const closers = Array.from(document.querySelectorAll('div.overlay .closer'));
    closers.forEach((btn) => {
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        hide(btn.closest('div.overlay'));
      });
    });

    Array.from(document.querySelectorAll('.signup_link')).forEach((a) => {
      a.addEventListener('click', (event) => {
        if (event) event.preventDefault();
        show(document.getElementById('signup_layer'));
      });
    });

    Array.from(document.querySelectorAll('.login_link')).forEach((a) => {
      a.addEventListener('click', (event) => {
        if (event) event.preventDefault();
        show(document.getElementById('login_layer'));
      });
    });
  }

  function initScreenshotOverlay() {
    const list = document.getElementById('screenshot_list');
    if (!list) return;

    const links = Array.from(list.querySelectorAll('li a'));
    links.forEach((a, index) => {
      a.addEventListener('click', (event) => {
        event.preventDefault();
        if (window.galarie && typeof window.galarie.show === 'function') {
          window.galarie.show(index);
        }
        show(document.getElementById('screenshot_layer'));
      });
    });

    const windowSize = 300;

    const moveRight = () => {
      const w = getStyleInt(list, 'width');
      const leftXLimit = windowSize - w;
      const rightXLimit = 0;
      handleMove(list, 98, leftXLimit, rightXLimit);
    };

    const moveLeft = () => {
      const w = getStyleInt(list, 'width');
      const leftXLimit = windowSize - w;
      const rightXLimit = 0;
      handleMove(list, -98, leftXLimit, rightXLimit);
    };

    const next = document.querySelector('#screenshots .next');
    const prev = document.querySelector('#screenshots .prev');

    if (next) next.addEventListener('click', (e) => { e.preventDefault(); moveLeft(); });
    if (prev) prev.addEventListener('click', (e) => { e.preventDefault(); moveRight(); });

    const dynamicBtns = Array.from(document.querySelectorAll('.dynamic_btn'));
    dynamicBtns.forEach((el) => {
      el.addEventListener('mouseenter', () => el.classList.add('over'));
      el.addEventListener('mouseleave', () => {
        el.classList.remove('over');
        el.classList.remove('clicked');
      });
      el.addEventListener('mousedown', () => {
        el.classList.remove('over');
        el.classList.add('clicked');
      });
      el.addEventListener('mouseup', () => {
        el.classList.remove('clicked');
        el.classList.add('over');
      });
    });

    setDisplay('#screenshots .prev img', 'none');
  }

  function t_format1(el) {
    const parts = (el.textContent || '').split(':');
    const h = parseInt(parts[0] || '0', 10) || 0;
    const m = parseInt(parts[1] || '0', 10) || 0;
    const s = parseInt(parts[2] || '0', 10) || 0;
    return h * 3600 + m * 60 + s;
  }

  function t_format2(seconds) {
    if (seconds <= -1) return '0:00:0?';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor(seconds / 60) % 60;
    const s = seconds % 60;
    const mm = m < 10 ? `0${m}` : `${m}`;
    const ss = s < 10 ? `0${s}` : `${s}`;
    return `${h}:${mm}:${ss}`;
  }

  function t_minus() {
    for (let i = 1; ; i++) {
      const el = document.getElementById(`timer${i}`);
      if (!el) break;

      const next = t_format1(el) - 1;
      if (next < 0) {
        window.setTimeout(() => document.location.reload(), 1000);
      } else {
        el.textContent = t_format2(next);
      }
    }
    window.setTimeout(t_minus, 1000);
  }

  class Screenshots {
    constructor(imageId, headlineId, commentId, elements) {
      this.elements = elements || [];
      this.targetImg = document.getElementById(imageId);
      this.targetHl = document.getElementById(headlineId);
      this.targetDesc = document.getElementById(commentId);
      this.$current = 0;
      this.$length = this.elements.length;
    }

    showNext() {
      let index = this.$current + 1;
      if (index >= this.$length) index = 0;
      this.render(index);
    }

    showPrev() {
      let index = this.$current - 1;
      if (index < 0) index = this.$length - 1;
      this.render(index);
    }

    show(num) {
      this.render(num);
      return this;
    }

    render(index) {
      if (!this.elements || this.elements.length === 0) return;
      const safeIndex = this.elements[index] !== undefined ? index : 0;
      const elem = this.elements[safeIndex];
      if (this.targetImg) this.targetImg.src = elem.img;
      if (this.targetHl) this.targetHl.innerHTML = elem.hl;
      if (this.targetDesc) this.targetDesc.innerHTML = elem.desc;
      this.$current = safeIndex;
    }
  }

  function Popup(i, j, game_url) {
    const frameBox = document.getElementById('frame_box');
    if (!frameBox) return false;

    frameBox.innerHTML = '';
    const src = `${game_url}manual.php?typ=${encodeURIComponent(i)}&s=${encodeURIComponent(j)}`;
    frameBox.innerHTML = `<iframe frameborder="0" id="Frame" src="${src}" width="412" height="440" border="0"></iframe>`;
    show(document.getElementById('iframe_layer'));

    const w = window.innerWidth || 0;
    const h = window.innerHeight || 0;
    const content = document.querySelector('#iframe_layer .overlay_content');
    if (content) {
      content.style.position = w < 700 || h < 700 ? 'absolute' : 'fixed';
    }
    return w < 700 || h < 700;
  }

  window.handleMove = handleMove;
  window.Popup = Popup;
  window.t_minus = t_minus;

  window.TravianScreenshots = Screenshots;

  document.addEventListener('DOMContentLoaded', () => {
    initOverlays();
    initScreenshotOverlay();
    t_minus();
  });
})();
