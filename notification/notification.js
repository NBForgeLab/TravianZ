(function () {
	if (!window.Travian) window.Travian = {};

	if (!window.Travian.Translation) {
		window.Travian.Translation = {
			dict: {},
			add: function (entries) {
				if (!entries) return;
				for (var key in entries) {
					if (Object.prototype.hasOwnProperty.call(entries, key)) {
						this.dict[key] = entries[key];
					}
				}
			}
		};
	}

	var Notification = {
		screenTitel: [],
		screenText: [],
		_images: ['featureLookHead', 'featureHeroHead', 'featureMapHead', 'featureNatarsHead', 'featurePlayersHead'],
		_currentImg: 0,
		_imgCounter: 1,
		_timer: null,

		_setButtonActive: function (index, active) {
			var el = document.getElementById('but_div_' + index);
			if (!el) return;
			el.className = active ? 'but_div_active' : 'but_div';
		},

		_setFeatureText: function (index) {
			var title = document.getElementById('featureTitel');
			if (title) title.textContent = (this.screenTitel && this.screenTitel[index]) ? this.screenTitel[index] : '';
			var text = document.getElementById('featureText');
			if (text) text.textContent = (this.screenText && this.screenText[index]) ? this.screenText[index] : '';
		},

		_swapImageId: function (fromIndex, toIndex) {
			var fromId = this._images[fromIndex];
			var toId = this._images[toIndex];
			var el = document.getElementById(fromId);
			if (el) el.id = toId;
		},

		_scheduleNext: function () {
			var self = this;
			if (self._timer) window.clearTimeout(self._timer);
			self._timer = window.setTimeout(function () { self.nextImage(); }, 4000);
		},

		screenInit: function () {
			this._currentImg = 0;
			this._imgCounter = 1;
			this._timer = null;
			this._scheduleNext();
		},

		nextImage: function () {
			var len = this._images.length;
			if (!len) return;

			if (this._currentImg >= len) this._currentImg = 0;
			if (this._imgCounter >= len) this._imgCounter = 0;

			var next = this._imgCounter;

			this._swapImageId(this._currentImg, next);
			this._setFeatureText(next);
			this._setButtonActive(next, true);
			this._setButtonActive(this._currentImg, false);

			this._currentImg = next;
			this._imgCounter = next + 1;

			this._scheduleNext();
		},

		screenNav: function (index) {
			var len = this._images.length;
			if (!len) return;
			var next = index;
			if (next < 0) next = 0;
			if (next >= len) next = len - 1;

			if (this._timer) window.clearTimeout(this._timer);

			this._setButtonActive(next, true);
			this._setButtonActive(this._currentImg, false);
			this._swapImageId(this._currentImg, next);
			this._setFeatureText(next);

			this._currentImg = next;
			this._imgCounter = next + 1;
		},

		contslide: function () {
			this._scheduleNext();
		}
	};

	window.Travian.Notification = Notification;
})();
