/**
 * BannerMonster - Frontend (vanilla JS, zero dependencies)
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'bm_closed';
	var closed = {};

	function loadClosed() {
		try {
			var raw = localStorage.getItem(STORAGE_KEY);
			if (!raw) { closed = {}; return; }
			var parsed = JSON.parse(raw);
			// Migrate from old array format to object format
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
		if (!(id in closed)) return false;
		return true;
	}

	var debugMode = false;

	function saveClosed(id) {
		closed[id] = Date.now();
		try { localStorage.setItem(STORAGE_KEY, JSON.stringify(closed)); } catch (e) {}
	}

	function canShow(id, reappearMinutes) {
		if (debugMode) return true;
		if (!isClosed(id)) return true;
		// reappearMinutes = 0 means never reappear
		if (!reappearMinutes || reappearMinutes <= 0) return false;
		var closedAt = closed[id];
		if (!closedAt) return false;
		var elapsedMs = Date.now() - closedAt;
		var requiredMs = reappearMinutes * 60 * 1000;
		if (elapsedMs >= requiredMs) {
			// Enough time has passed, allow showing again
			delete closed[id];
			try { localStorage.setItem(STORAGE_KEY, JSON.stringify(closed)); } catch (e) {}
			return true;
		}
		return false;
	}

	function show(el) {
		el.classList.add('bm-visible');
	}

	function hide(id) {
		var el = document.getElementById('bm-' + id);
		if (el) el.classList.remove('bm-visible');
		saveClosed(id);
	}

	function onScroll(el, pct) {
		var fired = false;
		function check() {
			if (fired) return;
			var st = window.pageYOffset || document.documentElement.scrollTop;
			var dh = document.documentElement.scrollHeight - window.innerHeight;
			if (dh > 0 && (st / dh) * 100 >= pct) {
				fired = true;
				window.removeEventListener('scroll', check);
				show(el);
			}
		}
		window.addEventListener('scroll', check, { passive: true });
	}

	function onExitIntent(el) {
		var fired = false;
		function handler(e) {
			if (fired) return;
			if (e.clientY <= 0) {
				fired = true;
				document.removeEventListener('mouseleave', handler);
				show(el);
			}
		}
		document.addEventListener('mouseleave', handler);
	}

	function process(banners) {
		for (var i = 0; i < banners.length; i++) {
			var b = banners[i];
			if (!canShow(b.id, b.reappear)) continue;

			var el = document.getElementById('bm-' + b.id);
			if (!el) continue;

			switch (b.trigger) {
				case 'immediate':
					show(el);
					break;
				case 'timer':
					(function (el, sec) {
						setTimeout(function () { show(el); }, sec * 1000);
					})(el, b.seconds || 5);
					break;
				case 'scroll':
					onScroll(el, b.scroll || 50);
					break;
				case 'exit_intent':
					onExitIntent(el);
					break;
			}
		}
	}

	function bindEvents() {
		document.addEventListener('click', function (e) {
			var x = e.target.closest('.bm-x');
			if (x) {
				var wrap = x.closest('.bm-wrap');
				var id = parseInt(x.getAttribute('data-id'), 10);
				hide(id);
				return;
			}
			var ov = e.target.closest('.bm-overlay');
			if (ov) {
				var ovId = parseInt(ov.getAttribute('data-id'), 10);
				var ovWrap = document.getElementById('bm-' + ovId);
				if (ovWrap && parseInt(ovWrap.getAttribute('data-close'), 10)) {
					hide(ovId);
				}
			}
		});
	}

	function init() {
		loadClosed();
		bindEvents();
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
