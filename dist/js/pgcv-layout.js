/* Distribución y navegación de PGCV sobre el tema habitual. */
(function ($) {
  'use strict';

  const sidebarButton = '[data-pgcv-sidebar]';
  const menuLink = '[data-pgcv-menu] .treeview > a';
  const mobile = window.matchMedia('(max-width: 767px)');
  const body = $('body');

  function fixLayout() {
    const headerHeight = $('.main-header').outerHeight() || 0;
    const footerHeight = $('.main-footer').outerHeight() || 0;
    const sidebarHeight = $('.sidebar').height() || 0;
    $('.content-wrapper').css('min-height', Math.max(
      $(window).height() - headerHeight - footerHeight, sidebarHeight
    ));
  }

  function syncSidebar() {
    const open = mobile.matches ? body.hasClass('sidebar-open') : !body.hasClass('sidebar-collapse');
    $(sidebarButton).attr('aria-expanded', String(open));
  }

  function closeSidebar() {
    if (mobile.matches) body.removeClass('sidebar-open sidebar-collapse');
    else body.addClass('sidebar-collapse');
    syncSidebar();
  }

  function setSubmenu(item, open, animate) {
    const submenu = item.children('.treeview-menu');
    item.toggleClass('menu-open', open);
    item.children('a').attr('aria-expanded', String(open));
    // Terminar la animación anterior evita colas al pulsar varias veces.
    submenu.stop(true, true);
    if (animate) submenu[open ? 'slideDown' : 'slideUp'](500, fixLayout);
    else submenu.toggle(open);
  }

  function initialize() {
    $('html, body, .wrapper').css({height: 'auto', 'min-height': '100%'});
    $('.sidebar').css('height', 'auto');
    $('.sidebar-menu a').filter(function () {
      return this.href === window.location.href;
    }).parent().addClass('active').parents('.treeview').addClass('active');
    $('[data-pgcv-menu]').addClass('tree').find('.treeview').each(function (index) {
      const item = $(this);
      const submenu = item.children('.treeview-menu');
      if (!submenu.length) return;
      const id = 'pgcv-submenu-' + index;
      submenu.attr('id', id);
      item.children('a').attr({'aria-controls': id, role: 'button'});
      setSubmenu(item, item.hasClass('active'), false);
    });
    fixLayout();
    syncSidebar();
    body.removeClass('hold-transition');
  }

  $(document).on('click', sidebarButton, function (event) {
    event.preventDefault();
    if (mobile.matches) body.toggleClass('sidebar-open');
    else body.toggleClass('sidebar-collapse');
    syncSidebar();
  });

  $(document).on('click', menuLink, function (event) {
    const item = $(this).parent();
    if (!item.children('.treeview-menu').length) return;
    event.preventDefault();
    const open = !item.hasClass('menu-open');
    if (open) {
      item.siblings('.treeview.menu-open').each(function () {
        setSubmenu($(this), false, true);
      });
    }
    setSubmenu(item, open, true);
  });

  $(document).on('keydown', sidebarButton + ', ' + menuLink, function (event) {
    if (event.key === ' ') {
      event.preventDefault();
      $(this).trigger('click');
    }
  });

  $(document).on('click', '.content-wrapper', function () {
    if (mobile.matches && body.hasClass('sidebar-open')) closeSidebar();
  });
  $(document).on('keydown', function (event) {
    if (event.key === 'Escape' && !event.isDefaultPrevented() && mobile.matches
      && body.hasClass('sidebar-open') && !body.hasClass('modal-open')
      && !$('.modal.show, .dropdown-menu.show').length) {
      event.preventDefault();
      closeSidebar();
      $(sidebarButton).trigger('focus');
    }
  });

  $(window).on('resize', function () {
    fixLayout();
    syncSidebar();
  });
  // Imágenes y fuentes pueden cambiar la altura después de DOMContentLoaded.
  if (document.readyState === 'complete') initialize();
  else $(window).one('load', initialize);
})(jQuery);
