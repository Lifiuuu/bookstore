(function($) {
  'use strict';
  $(function() {
    $('[data-toggle="offcanvas"], [data-bs-toggle="offcanvas"]').on("click", function() {
      $('.sidebar-offcanvas').toggleClass('active')
    });
  });
})(jQuery);