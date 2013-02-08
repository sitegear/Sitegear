/*!
 * sitegear.cookie-dialog.js
 * Sitegear Widget - Cookie Dialog - JavaScript
 * Dialog widget that remembers its size and position using cookies.
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	$.widget('sitegear.cookieDialog', $.ui.dialog, {

		options: {
			/**
			 * The name of the cookie to save the position and size data to.  The value supplied here is a default
			 * only, and should be overridden by calling or implementing code.
			 */
			cookie: 'cookieDialog',

			/**
			 * Whether or not to store the position of the dialog using cookies, and load the dialog's initial position
			 * from the cookie values.  True by default.
			 */
			storePosition: true,

			/**
			 * Whether or not to store the size (width and height) of the dialog using cookies, and load the dialog's
			 * initial size from the cookie values.  True by default.
			 */
			storeSize: true,

			/**
			 * Additional parameters to store.  This is an array of strings, each of which must be the name of an
			 * option that is available from the object's option() method.  By default, only size and position are
			 * stored.
			 */
			storeAdditional: null
		},

		/**
		 * Widget constructor.
		 */
		_create: function() {
			var self = this,
				originalOpen = this.options.open,
				originalBeforeClose = this.options.beforeClose,
				originalClose = this.options.close,
				originalDragStop = this.options.dragStop,
				originalResizeStop = this.options.resizeStop;
			this._loadDialogLayoutData();
			$.extend(this.options, {
				open: function(evt, ui) {
					self.widget().fixed();
					$(window).bind('scroll', function() {
						self._saveDialogLayoutData();
					});
					if (originalOpen && $.isFunction(originalOpen)) {
						originalOpen.call(self, evt, ui);
					}
				},
				beforeClose: function(evt, ui) {
					if (originalBeforeClose && $.isFunction(originalBeforeClose)) {
						originalBeforeClose.call(self, evt, ui);
					}
					self._saveDialogLayoutData();
				},
				close: function(evt, ui) {
					$(window).unbind('scroll', self._saveDialogLayoutData);
					if (originalClose && $.isFunction(originalClose)) {
						originalClose.call(self, evt, ui);
					}
				},
				dragStop: function(evt, ui) {
					if (originalDragStop && $.isFunction(originalDragStop)) {
						originalDragStop.call(self, evt, ui);
					}
					self._saveDialogLayoutData();
				},
				resizeStop: function(evt, ui) {
					if (originalResizeStop && $.isFunction(originalResizeStop)) {
						originalResizeStop.call(self, evt, ui);
					}
					self._saveDialogLayoutData();
				}
			});
			$.ui.dialog.prototype._create.call(this);
		},

		/**
		 * Widget destructor.
		 */
		destroy: function() {
			$(window).unbind('scroll', this._saveDialogLayoutData);
			$.ui.dialog.prototype.destroy.call(this);
		},

		/**
		 * Load the dialog layout data from the cookies.
		 */
		_loadDialogLayoutData: function() {
			var self = this, o = this.options,
				cookieData = $.parseJSON($.cookie(o.cookie));
			if (o.storePosition && cookieData) {
				if (cookieData.hasOwnProperty('left') && cookieData.hasOwnProperty('top')) {
					this.options.position = [
						parseInt(cookieData.left.toString(), 10) - $(window).scrollLeft(),
						parseInt(cookieData.top.toString(), 10) - $(window).scrollTop()
					];
				}
			}
			if (o.storeSize && cookieData) {
				if (cookieData.hasOwnProperty('width')) {
					this.options.width = parseInt(cookieData.width.toString(), 10);
				}
				if (cookieData.hasOwnProperty('height')) {
					this.options.height = parseInt(cookieData.height.toString(), 10);
				}
			}
			if ($.isArray(o.storeAdditional) && cookieData) {
				$.each(o.storeAdditional, function(index, additional) {
					if (cookieData.hasOwnProperty(additional)) {
						self.options[additional] = cookieData[additional];
					}
				});
			}
		},

		/**
		 * Save the dialog layout data to the cookies.
		 */
		_saveDialogLayoutData: function() {
			var self = this, $dialog = this.widget(), o = this.options, dialogOffset,
				cookieData = {};
			if (o.storePosition) {
				dialogOffset = $dialog.dialog('widget').offset();
				cookieData.left = dialogOffset.left;
				cookieData.top = dialogOffset.top;
			}
			if (o.storeSize) {
				cookieData.width = Math.round($dialog.width());
				cookieData.height = Math.round($dialog.height());
			}
			if ($.isArray(o.storeAdditional)) {
				$.each(o.storeAdditional, function(index, additional) {
					cookieData[additional] = self.option(additional);
				});
			}
			$.cookie(o.cookie, JSON.stringify(cookieData), $.sitegear.siteParameters.cookie);
		},

		/**
		 * Ensure that the correct element is returned by the widget() method.
		 */
		widget: function() {
			return $.ui.dialog.prototype.widget.call(this);
		}
	});
}(jQuery));
