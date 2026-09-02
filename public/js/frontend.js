/**
 * BannerMonster - Frontend (vanilla JS, zero dependencies)
 * WCAG 2.2 compliant — usa <dialog> HTML nativo
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'bm_closed';
	var closed = {};
	var debugMode = false;

	// --- localStorage ---

	function loadClosed() {
		try {
			var raw = localStorage.getItem(STORAGE_KEY);
			if (!raw) { closed = {}; return; }
			var parsed = JSON.parse(raw);
			if (Array.isArray(parsed)) {
				closed = {};
				for (var i = 0; i < parsed.length; i++) {
					closed[parsed[i]] = 0;
				}
				try { localStorage.setItem(STORAGE_KEY, JSON.stringify(closed)); } catch (e) {}
			} else {
				closed = parsed;
			}
		} catch (e) {
			closed = {};
		}
	}

	function isClosed(id) {
		return id in closed;
	}

	function saveClosed(id) {
		closed[id] = Date.now();
		try { localStorage.setItem(STORAGE_KEY, JSON.stringify(closed)); } catch (e) {}
	}

	function canShow(id, reappearMinutes) {
		if (debugMode) return true;
		if (!isClosed(id)) return true;
		if (!reappearMinutes || reappearMinutes <= 0) return false;
		var closedAt = closed[id];
		if (!closedAt) return false;
		var elapsedMs = Date.now() - closedAt;
		var requiredMs = reappearMinutes * 60 * 1000;
		if (elapsedMs >= requiredMs) {
			delete closed[id];
			try { localStorage.setItem(STORAGE_KEY, JSON.stringify(closed)); } catch (e) {}
			return true;
		}
		return false;
	}

	// --- Dialog helpers ---

	function isModal(el) {
		return el.classList.contains('bm-popup_center') ||
		       el.classList.contains('bm-popup_bottom_right') ||
		       el.classList.contains('bm-popup_bottom_left');
	}

	function setupBackdropClose(dialog, id, backdropClose) {
		dialog.addEventListener('click', function (e) {
			if (e.target === dialog && backdropClose) {
				dialog.close();
				saveClosed(id);
			}
		});

		dialog.addEventListener('cancel', function (e) {
			e.preventDefault();
			saveClosed(id);
			dialog.close();
		});
	}

	// --- Triggers ---

	function onScroll(el, pct, isPopup) {
		var fired = false;
		function check() {
			if (fired) return;
			var st = window.pageYOffset || document.documentElement.scrollTop;
			var dh = document.documentElement.scrollHeight - window.innerHeight;
			if (dh > 0 && (st / dh) * 100 >= pct) {
				fired = true;
				window.removeEventListener('scroll', check);
				if (isPopup) {
					el.showModal();
				} else {
					el.open = true;
				}
			}
		}
		window.addEventListener('scroll', check, { passive: true });
	}

	function onExitIntent(el, isPopup) {
		var fired = false;
		function handler(e) {
			if (fired) return;
			if (e.clientY <= 0) {
				fired = true;
				document.removeEventListener('mouseleave', handler);
				if (isPopup) {
					el.showModal();
				} else {
					el.open = true;
				}
			}
		}
		document.addEventListener('mouseleave', handler);
	}

	// --- Main ---

	function process(banners) {
		for (var i = 0; i < banners.length; i++) {
			var b = banners[i];
			if (!canShow(b.id, b.reappear)) continue;

			var el = document.getElementById('bm-' + b.id);
			if (!el) continue;

			var popup = isModal(el);

			if (popup) {
				setupBackdropClose(el, b.id, b.backdrop_close);

				switch (b.trigger) {
					case 'immediate':
						el.showModal();
						break;
					case 'timer':
						(function (el, sec) {
							setTimeout(function () { el.showModal(); }, sec * 1000);
						})(el, b.seconds || 5);
						break;
					case 'scroll':
						onScroll(el, b.scroll || 50, true);
						break;
					case 'exit_intent':
						onExitIntent(el, true);
						break;
				}
			} else {
				switch (b.trigger) {
					case 'immediate':
						el.open = true;
						break;
					case 'timer':
						(function (el, sec) {
							setTimeout(function () { el.open = true; }, sec * 1000);
						})(el, b.seconds || 5);
						break;
					case 'scroll':
						onScroll(el, b.scroll || 50, false);
						break;
					case 'exit_intent':
						onExitIntent(el, false);
						break;
				}
			}
		}
	}

	function init() {
		loadClosed();
		if (typeof bmData !== 'undefined') {
			debugMode = !!bmData.debug;
			if (debugMode) {
				console.log('[BannerMonster] Debug mode active - localStorage bypassed');
			}
			if (bmData.banners) {
				process(bmData.banners);
			}
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
