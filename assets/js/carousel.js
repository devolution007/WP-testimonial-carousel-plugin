(function () {
  'use strict';

  function initializeCarousel(carousel) {
    if (carousel.dataset.ready === 'true') return;

    var viewport = carousel.querySelector('.bmtc-viewport');
    var track = carousel.querySelector('.bmtc-track');
    var modal = carousel.querySelector('.bmtc-modal');
    var modalPanel = modal ? modal.querySelector('.bmtc-modal-panel') : null;
    var modalQuote = modal ? modal.querySelector('.bmtc-modal-quote') : null;
    var modalAuthorCopy = modal ? modal.querySelector('.bmtc-modal-author-copy') : null;
    var modalClose = modal ? modal.querySelector('.bmtc-modal-close') : null;

    if (!viewport || !track || !modal || !modalPanel || !modalQuote || !modalAuthorCopy || !modalClose) return;

    carousel.dataset.ready = 'true';
    var timer;
    var closeTimer;
    var returnFocus = null;
    var oldBodyOverflow = '';
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    var closeDelay = reduceMotion.matches ? 0 : 280;
    var interval = Math.max(2000, parseInt(carousel.dataset.interval || '4500', 10));
    var autoplay = carousel.dataset.autoplay !== 'false';

    document.body.appendChild(modal);

    function distance() {
      var card = track.querySelector('.bmtc-card');
      if (!card) return Math.max(280, viewport.clientWidth * 0.85);
      var gap = parseFloat(window.getComputedStyle(track).gap) || 0;
      return card.getBoundingClientRect().width + gap;
    }

    function move(direction) {
      viewport.scrollBy({ left: distance() * direction, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
    }

    function step() {
      var atEnd = viewport.scrollLeft + viewport.clientWidth >= viewport.scrollWidth - 4;
      viewport.scrollTo({ left: atEnd ? 0 : viewport.scrollLeft + distance(), behavior: reduceMotion.matches ? 'auto' : 'smooth' });
    }

    function stop() {
      window.clearInterval(timer);
    }

    function start() {
      stop();
      if (!autoplay || reduceMotion.matches || !modal.hidden) return;
      timer = window.setInterval(step, interval);
    }

    function openModal(card, trigger) {
      var text = card.querySelector('.bmtc-text');
      var authorCopy = card.querySelector('.bmtc-author-copy');
      if (!text || !authorCopy) return;

      returnFocus = trigger;
      modalQuote.innerHTML = text.innerHTML;
      modalAuthorCopy.innerHTML = authorCopy.innerHTML;
      window.clearTimeout(closeTimer);
      modal.hidden = false;
      oldBodyOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      stop();
      window.requestAnimationFrame(function () {
        modal.classList.add('is-open');
        modalPanel.focus();
      });
    }

    function closeModal() {
      if (modal.hidden) return;
      modal.classList.remove('is-open');
      window.clearTimeout(closeTimer);
      closeTimer = window.setTimeout(function () {
        modal.hidden = true;
        document.body.style.overflow = oldBodyOverflow;
        if (returnFocus) returnFocus.focus();
        start();
      }, closeDelay);
    }

    track.querySelectorAll('.bmtc-more').forEach(function (button) {
      button.addEventListener('click', function () {
        openModal(button.closest('.bmtc-card'), button);
      });
    });

    carousel.querySelector('.bmtc-prev').addEventListener('click', function () { move(-1); });
    carousel.querySelector('.bmtc-next').addEventListener('click', function () { move(1); });
    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('pointerdown', function (event) {
      if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !modal.hidden) closeModal();
    });

    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('focusin', stop);
    carousel.addEventListener('focusout', function (event) {
      if (!carousel.contains(event.relatedTarget)) start();
    });
    viewport.addEventListener('pointerdown', stop);
    viewport.addEventListener('pointerup', start);
    viewport.addEventListener('touchend', start, { passive: true });
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) stop(); else start();
    });
    start();
  }

  function boot() {
    document.querySelectorAll('.bmtc-carousel').forEach(initializeCarousel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
}());

