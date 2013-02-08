/*!
 * sitegear.management.js
 * Common utility functions for sitegear.
 * Note this is not a plugin, but a set of functions added to jQuery.
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery, Node */

(function($) {
	"use strict";

	$.extend(true, $.sitegear, {

		/**
		 * These are set by the calling code.  Some fallback defaults are given.
		 */
		siteParameters: {
			/**
			 * URL settings.  Should be overridden.
			 */
			url: {
				base: '',
				root: '',
				absolute: '',
				relative: ''
			},

			/**
			 * Platform details.  Used for display purposes.
			 */
			platform: {
				path: 'libraries/sitegear/',
				appName: 'Sitegear',
				release: 'unknown',
				licenseInfo: 'Please see website',
				websiteUrl: 'http://sitegear.org/'
			},

			/**
			 * Site name and logo URL.  Used for display purposes.
			 */
			site: {
				name: null,
				logoUrl: null
			},

			/**
			 * User detauls.  Used for display purposes.
			 */
			user: {
				name: null
			},

			/**
			 * Command URL path settings.  Used to generate command URLs.
			 */
			commandUrlPath: {
				root: 'sitegear',
				adapter: {
					root: 'adapter',
					commands: {
						describeData: 'describe',
						countData: 'count',
						loadData: 'load',
						saveData: 'save',
						deleteData: 'delete',
						copyData: 'copy',
						moveData: 'move'
					}
				},
				administration: {
					root: 'admin',
					commands: {
						getSelectorsForPublishing: 'get-selectors',
						publish: 'publish',
						revert: 'revert'
					}
				},
				params: {
					selector: 'selector',
					recursive: 'recursive',
					includeLeafNodes: 'includeLeafNodes',
					targetSelector: 'targetSelector'
				}
			},

			/**
			 * Cookie settings.  Used to pass to the $.cookie() plugin. Should be overridden with the actual root path.
			 */
			cookie: {
				path: '/'
			}
		},

		/**
		 * Edit mode, either 'basic', 'advanced' or 'off'.
		 */
		editMode: $.cookie('editMode') || 'basic',

		/**
		 * Edit mode locked flag; set to true whenever something is being edited, so that the edit mode cannot change
		 * half way through an edit operation.
		 */
		editModeLocked: false,

		/**
		 * Array of callback functions executed when the edit mode is changed.
		 */
		editModeCallbacks: [],

		/**
		 * Get the derived key from the given selector, that is, everything following the final '/' and with anything
		 * after the first ':' removed.
		 *
		 * @param selector Selector to get the key from.
		 *
		 * @return string Key.
		 */
		getSelectorKey: function(selector) {
			return selector.replace(/^.*\//, '').replace('/:.*$/', '');
		},

		/**
		 * Load data structure information from the sitegear backend via AJAX.
		 * 
		 * @param adapter Adapter key.
		 * @param selector Data selector to pass to adapter.
		 * @param recursive Flag to pass to the backend.
		 * @param includeLeafNodes Flag to pass to the backend.
		 * @param callback (optional) Function to execute on success.
		 */
		describeData: function(adapter, selector, recursive, includeLeafNodes, callback) {
			console.log('$.sitegear.describeData()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.adapter.root + '/' + encodeURIComponent(adapter) + '/' + u.adapter.commands.describeData + '?' + u.params.selector + '=' + encodeURIComponent(selector) + '&' + u.params.recursive + '=' + (recursive ? 1 : 0) + '&' + u.params.includeLeafNodes + '=' + (includeLeafNodes ? 1 : 0),
				type: 'GET',
				dataType: 'json',
				success: $.isFunction(callback) ? callback: $.noop
			});
		},

		/**
		 * Get the data count from the sitegear backend via AJAX.
		 *
		 * @param adapter Adapter key.
		 * @param selector Data selector to pass to adapter.
		 * @param callback (optional) Function to execute on success.
		 */
		countData: function(adapter, selector, callback) {
			console.log('$.sitegear.countData()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.adapter.root + '/' + encodeURIComponent(adapter) + '/' + u.adapter.commands.countData + '?' + u.params.selector + '=' + encodeURIComponent(selector),
				type: 'GET',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Load data from the sitegear backend via AJAX.
		 *
		 * @param adapter Adapter key.
		 * @param selector Data selector to pass to adapter.
		 * @param callback (optional) Function to execute on success.
		 */
		loadData: function(adapter, selector, callback) {
			console.log('$.sitegear.loadData()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.adapter.root + '/' + encodeURIComponent(adapter) + '/' + u.adapter.commands.loadData + '?' + u.params.selector + '=' + encodeURIComponent(selector),
				type: 'GET',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Save data to the sitegear backend via AJAX.
		 * 
		 * @param adapter Adapter key.
		 * @param selector Data selector to pass to adapter.
		 * @param data Data to save.
		 * @param callback (optional) Function to execute on success.
		 */
		saveData: function(adapter, selector, data, callback) {
			console.log('$.sitegear.saveData()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.adapter.root + '/' + encodeURIComponent(adapter) + '/' + u.adapter.commands.saveData + '?' + u.params.selector + '=' + encodeURIComponent(selector),
				type: 'POST',
				dataType: 'json',
				data: { data: JSON.stringify(data) },
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/* For uploadData(), see sitegear.upload-manager.js */

		/**
		 * Delete data from the sitegear backend via AJAX.
		 * 
		 * @param adapter Adapter key.
		 * @param selector Data selector to pass to adapter.
		 * @param callback (optional) Function to execute on success.
		 */
		deleteData: function(adapter, selector, callback) {
			console.log('$.sitegear.deleteData()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.adapter.root + '/' + encodeURIComponent(adapter) + '/' + u.adapter.commands.deleteData + '?' + u.params.selector + '=' + encodeURIComponent(selector),
				type: 'POST',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Move data from the sitegear backend via AJAX.
		 * 
		 * @param adapter Adapter key.
		 * @param selector Source data selector to pass to adapter.
		 * @param targetSelector Target data selector to pass to adapter.
		 * @param callback (optional) Function to execute on success.
		 */
		copyData: function(adapter, selector, targetSelector, callback) {
			console.log('$.sitegear.copyData()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.adapter.root + '/' + encodeURIComponent(adapter) + '/' + u.adapter.commands.copyData + '?' + u.params.selector + '=' + encodeURIComponent(selector) + '&' + u.params.targetSelector + '=' + encodeURIComponent(targetSelector),
				type: 'POST',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Move data from the sitegear backend via AJAX.
		 * 
		 * @param adapter Adapter key.
		 * @param selector Source data selector to pass to adapter.
		 * @param targetSelector Target data selector to pass to adapter.
		 * @param callback (optional) Function to execute on success.
		 */
		moveData: function(adapter, selector, targetSelector, callback) {
			console.log('$.sitegear.moveData()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.adapter.root + '/' + encodeURIComponent(adapter) + '/' + u.adapter.commands.moveData + '?' + u.params.selector + '=' + encodeURIComponent(selector) + '&' + u.params.targetSelector + '=' + encodeURIComponent(targetSelector),
				type: 'POST',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Query the backend for the map of selectors that are ready for publishing.  This is passed to the supplied
		 * callback.
		 * 
		 * @param callback (optional) Function to execute on receipt of data.
		 */
		getSelectorsForPublishing: function(callback) {
			console.log('$.sitegear.getSelectorsForPublishing()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.administration.root + '/' + u.administration.commands.getSelectorsForPublishing,
				type: 'GET',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Publish all changes currently in preview mode.  This calls the backend's administration functionality.
		 * 
		 * @param callback (optional) Function to execute on success.
		 */
		publish: function(callback) {
			console.log('$.sitegear.publish()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.administration.root + '/' + u.administration.commands.publish,
				type: 'POST',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Revert all changes currently in preview mode.  This calls the backend's administration functionality.
		 * 
		 * @param callback (optional) Function to execute on success.
		 */
		revert: function(callback) {
			console.log('$.sitegear.revert()');
			var u = $.sitegear.siteParameters.commandUrlPath;
			$.ajax({
				url: u.root + '/' + u.administration.root + '/' + u.administration.commands.revert,
				type: 'POST',
				dataType: 'json',
				success: $.isFunction(callback) ? callback : $.noop
			});
		},

		/**
		 * Create HTML describing the given map of selectors. The passed-in structure must be an object with
		 * descriptive keys mapped to arrays of selectors.
		 * 
		 * @param selectorsData Data to describe.
		 * 
		 * @return string HTML
		 */
		describeGroupedSelectorsHTML: function(selectorsData) {
			console.log('$.sitegear.describeGroupedSelectorsHTML(selectorsData)');
			var html = '<dl>';
			$.each(selectorsData, function(key, value) {
				if (value.length && (value.length > 0)) {
					html += '<dt>' + key + ' (' + value.length + ' ' + ((value.length === 1) ? 'item' : 'items') + ')</dt><dd><ul>';
					$.each(value, function(index, selector) {
						html += '<li>' + selector + '</li>';
					});
					html += '</ul></dd>';
				}
			});
			html += '</dl>';
			return html;
		},

		/**
		 * Add a callback to be called whenever edit mode is changed.
		 * 
		 * @param callback Function to be called when the edit mode is changed.  The function should take one
		 *   parameter, which is the new edit mode.
		 */
		addEditModeCallback: function(callback) {
			this.editModeCallbacks.push(callback);
		},

		/**
		 * Remove a callback that was previously added to be called whenever edit mode is changed.
		 *
		 * @param callback Function to be removed from the edit mode callback list.
		 */
		removeEditModeCallback: function(callback) {
			var i;
			for (i=0; i<this.editModeCallbacks.length; i) {
				if (callback === this.editModeCallbacks[i]) {
					this.editModeCallbacks.splice(i, 1);
				} else {
					i++;
				}
			}
		},

		/**
		 * Change the edit mode, and call any registered callbacks.
		 * 
		 * @param editMode Edit mode value to set, and to pass to any callbacks.
		 */
		setEditMode: function(editMode) {
			this.editMode = editMode;
			$.cookie('editMode', this.editMode, { path: '/' });
			$.each(this.editModeCallbacks, function(index, callback) {
				callback(editMode);
			});
		},

		/**
		 * Show a dialog asking to change edit mode.
		 */
		showEditModeDialog: function() {
			if ($.sitegear.editModeLocked) {
				return $.sitegear.showMessageDialog('Cannot Change Edit Mode', '<p>Sorry, you cannot change edit mode during an edit.  Please finish editing this page and try again.</p>');
			}
			var $dialog = $('<div></div>'),
				buttons = [],
				currentModeIndex = 0;
			$.each([ 'basic', 'advanced', 'off' ], function(index, mode) {
				buttons.push({
					text: mode.substring(0, 1).toUpperCase() + mode.substring(1),
					click:function () {
						$.sitegear.setEditMode(mode.toLowerCase());
						$dialog.dialog('close');
						$dialog.remove();
					}
				});
				if (mode === $.sitegear.editMode) {
					currentModeIndex = index;
				}
			});
			buttons.push({
				text: 'Cancel',
				click: function() {
					$dialog.dialog('close');
					$dialog.remove();
				}
			});
			return $dialog.append('<p>Please select an edit mode:</p>')
				.append('<ul><li>Basic: Content is edited using common, non-technical tools.</li><li>Advanced: Content is edited using more technical, code-oriented tools.</li><li>Off: Switch off content editing.</li></ul>')
				.append('<p>Your current edit mode is: ' + $.sitegear.editMode.substring(0, 1).toUpperCase() + $.sitegear.editMode.substring(1) + '</p>')
				.dialog({
					title: 'Change Edit Mode',
					width: 'auto',
					height: 'auto',
					modal: true,
					resizable: false,
					buttons: buttons,
					open: function() {
						$('.ui-dialog-buttonset button:eq(' + currentModeIndex + ')', $dialog.dialog('widget')).focus();
					}
				});
		}
	});
}(jQuery));
