<!-- plugins:js -->
<script src="/assets/vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- inject:js -->
<script src="/assets/js/off-canvas.js"></script>
<script src="/assets/js/misc.js"></script>
<script src="/assets/js/settings.js"></script>
<script src="/assets/js/todolist.js"></script>
<script src="/assets/js/jquery.cookie.js"></script>
<!-- endinject -->

<script>
	// Fallback collapse toggler for sidebar groups (works if Bootstrap JS not present)
	document.addEventListener('DOMContentLoaded', function () {
		var sidebar = document.getElementById('sidebar');
		if (!sidebar) return;
		var toggles = sidebar.querySelectorAll('.nav-link[href^="#"]');
		toggles.forEach(function (t) {
			t.addEventListener('click', function (ev) {
				ev.preventDefault();
				var target = t.getAttribute('href');
				if (!target) return;
				var el = document.querySelector(target);
				if (!el) return;
				el.classList.toggle('show');
				var expanded = el.classList.contains('show');
				t.setAttribute('aria-expanded', expanded ? 'true' : 'false');
			});
		});
	});
</script>
