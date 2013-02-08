/*!
 * sitegear.command-dialog.js
 * Sitegear Widget - Command Dialog - JavaScript
 * Overlay widget that gives a menu of options provided by other widgets, 
 * displayed as buttons in a jQuery UI dialog.
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	$.widget('sitegear.commandDialog', $.sitegear.cookieDialog, {
		options: {
			/**
			 * Key-value map of module names to arrays of commands.  To be supplied by calling code.
			 */
			commands: {},

			/**
			 * CSS classes for markup.
			 */
			cssClass: {
				container: 'sitegear-widget-command-dialog',
				siteHeader: 'sitegear-widget-command-dialog-site-header',
				siteLogo: 'sitegear-widget-command-dialog-site-logo',
				moduleName: 'sitegear-widget-command-dialog-module-name',
				commandLink: 'sitegear-widget-command-dialog-command-link',
				footer: 'sitegear-widget-command-dialog-footer'
			},

			/**
			 * Number of columns to arrange the modules and commands into.
			 */
			columns: 2,

			minimised: false
		},

		/**
		 * Widget constructor.
		 */
		_create: function() {
			var self = this, $dialog = $(this.element);
			this._resetPositionCalculationData();
			$.extend(true, this.options, {
				title: self._createDialogTitle(),
				autoOpen: false,
				closeOnEscape: false,
				resizable: false,
				draggable: false, // set up manually as below
				position: [ 'right', 'top' ],
				width: 'auto',
				height: 'auto',
				dialogClass: 'sitegear-widget-command-dialog-dialog',
				cookie: 'commandDialog',
				storeSize: false,
				storeAdditional: [ 'minimised' ],
				create: function() {
					// Setup the dialog
					$dialog.addClass(self.options.cssClass.container)
						.append(self._createSiteHeader())
						.append(self._createList())
						.append(self._createFooter());

					// Setup draggable behaviour (draggable by the whole dialog)
					self.widget().draggable({
						containment: 'document',
						start: function() {
							self._resetPositionCalculationData();
						},
						stop: function() {
							self._saveDialogLayoutData();
						}
					}).css({ cursor: 'move' }).fixed();

					// Setup window events
					$(window).resize(function() {
						self._windowResize();
					});

					// Open the dialog.  Command dialog does not support autoOpen: false.
					self.open();
				},
				open: function() {
					// Fix the width of the dialog, for when minimised.
					self.option('width', Math.ceil(self.widget().width()));
					if (self.option('minimised')) {
						$dialog.hide();
					}
					$dialog.find('a:first').blur();
				},
				beforeClose: function(evt) {
					self.option('minimised', !self.option('minimised'));
					self._updateDialog();
					$.sitegear.cookieDialog.prototype._saveDialogLayoutData.call(this);
					evt.preventDefault();
					return false;
				}
			});
			$.sitegear.cookieDialog.prototype._create.call(this);
		},

		/**
		 * Widget destructor.
		 */
		destroy: function() {
			$(this.element).removeClass(this.options.cssClass.container);
			$.sitegear.cookieDialog.prototype.destroy.call(this);
		},

		/**
		 * Reset the position calculation data, i.e. whether the dialog is pinned to the right and/or bottom edges (or
		 * left and/or top as per default), and the distance from that edge for calculation.
		 */
		_resetPositionCalculationData: function() {
			this._pinRight = null;
			this._pinBottom = null;
			this._distanceToRight = null;
			this._distanceToBottom = null;
		},

		/**
		 * Create a title for the dialog based on the site name and CMS name.
		 */
		_createDialogTitle: function() {
			var title = $.sitegear.siteParameters.platform.appName;
			if ($.sitegear.siteParameters.site.name) {
				title = $.sitegear.siteParameters.site.name + ' &mdash; ' + title;
			}
			return title;
		},

		/**
		 * Create a header with the site name and/or logo.
		 */
		_createSiteHeader: function() {
			var $container = $('<div></div>'), o = this.options;
			$container.addClass(o.cssClass.siteHeader);
			if ($.sitegear.siteParameters.site.logoUrl && $.sitegear.siteParameters.site.name) {
				$container.append($('<img src="' + $.sitegear.siteParameters.site.logoUrl + '" alt="' + $.sitegear.siteParameters.site.name + '" class="' + o.cssClass.siteLogo + '" />'));
			} else if ($.sitegear.siteParameters.site.logoUrl) {
				$container.append($('<img src="' + $.sitegear.siteParameters.site.logoUrl + '" alt="[site logo]" class="' + o.cssClass.siteLogo + '" />'));
			} else if ($.sitegear.siteParameters.site.name) {
				$container.append($('<span class="' + o.cssClass.siteLogo + '"></span>').text($.sitegear.siteParameters.site.name));
			}
			return $container;
		},

		/**
		 * Display the available commands.
		 */
		_createList: function() {
			var i, o = this.options,
				$list = $('<ul></ul>').addClass('top-level ui-helper-clearfix'), $columnLists = [], $columnList,
				totalRows = 0, rowsUsed = 0, listIndex = 0, numColumns = o.columns, optimalRowsPerColumn;
			$.each(o.commands, function(moduleKey, moduleCommandSettings) {
				totalRows += 1 + moduleCommandSettings.commands.length; // 1 for the heading
			});
			optimalRowsPerColumn = Math.round(totalRows / numColumns);
			for (i=0; i<numColumns; i++) {
				$columnList = $('<dl></dl>');
				$list.append($('<li></li>').addClass('column-list-item').append($columnList));
				$columnLists.push($columnList);
			}
			$.each(o.commands, function(moduleKey, moduleCommandSettings) {
				var $moduleList = $('<ul></ul>'),
					$moduleName = $('<dt></dt>').addClass(o.cssClass.moduleName).text(moduleCommandSettings.moduleName),
					// This determines which is greater, between the difference between the current position and the
					// start of the current list, or the position at the end of this module and the end of the next
					// list.  If the current list has a greater difference then the module will go onto the next list.
					differenceSameList = Math.abs(rowsUsed + moduleCommandSettings.commands.length + 1 - (optimalRowsPerColumn * listIndex)),
					differenceNextList = Math.abs(rowsUsed + moduleCommandSettings.commands.length + 1 - (optimalRowsPerColumn * (listIndex + 2)));
				if ((rowsUsed > 0) && (listIndex + 1 < numColumns) && (differenceSameList > differenceNextList)) {
					listIndex++;
				}
				$.each(moduleCommandSettings.commands, function(commandIndex, command) {
					var $commandLink = $('<a></a>').addClass(o.cssClass.commandLink).attr({ href: '#' }).text(command.label).button({
						disabled: command.disabled || false
					}).bind({
						click: function() {
							// Call the relevant script.
							//noinspection JSUnresolvedVariable
							var f = new Function('params', '$target', command.scriptContent);
							f(command.params, $commandLink);
							$(this).blur();
							return false;
						},
						mousedown: function(evt) {
							// Prevent dragging the dialog by the buttons.
							evt.preventDefault();
							evt.stopPropagation();
						}
					});
					$moduleList.append($('<li></li>').append($commandLink));
				});
				$columnLists[listIndex].append($moduleName).append($('<dd></dd>').append($moduleList));
				rowsUsed += moduleCommandSettings.commands.length + 1; // +1 for the heading
			});
			return $list;
		},

		/**
		 * Create the footer.
		 */
		_createFooter: function() {
			return $('<div></div>').addClass(this.options.cssClass.footer).append('Logged in as: ').append($.sitegear.siteParameters.user.name);
		},

		/**
		 * Show or hide the dialog body as required (leaving the title bar visible).
		 */
		_updateDialog: function() {
			$(this.element).slideToggle(this.minimised);
		},

		/**
		 * Pins the command dialog to the nearest window corner, when the window is resized.
		 */
		_windowResize: function() {
			var $dialog = this.widget(),
				dialogOffset = $dialog.offset(),
				dialogLeft = Math.round(dialogOffset.left),
				dialogTop = Math.round(dialogOffset.top),
				distanceToRight = Math.round($(window).width()) - dialogLeft - Math.round($dialog.width()),
				distanceToBottom = Math.round($(window).height()) - dialogTop - Math.round($dialog.height());
			if (this._pinRight === null || this._pinBottom === null) {
				this._pinRight = (distanceToRight > 0 && dialogLeft > distanceToRight);
				this._pinBottom = (distanceToBottom > 0 && dialogTop > distanceToBottom);
				this._distanceToRight = distanceToRight;
				this._distanceToBottom = distanceToBottom;
			} else {
				if (this._pinRight) {
					$dialog.css({ left: dialogLeft - this._distanceToRight + distanceToRight + 'px' });
				}
				if (this._pinBottom) {
					$dialog.css({ top: dialogTop - this._distanceToBottom + distanceToBottom + 'px' });
				}
			}
			this._saveDialogLayoutData();
		}
	});
}(jQuery));
