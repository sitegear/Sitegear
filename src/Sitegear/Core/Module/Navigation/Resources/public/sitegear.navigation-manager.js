/*!
 * sitegear.navigation-manager.js
 * Sitegear Widget - Navigation Manager - JavaScript
 * Navigation management widget
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	/**
	 * Global settings and utility functions for the navigation manager.
	 */
	$.sitegearNavigationManager = {

		globalSettings: {
			/**
			 * Data / backend settings.
			 *
			 * TODO Actually get these from the backend.
			 */
			data: {
				/**
				 * Configuration settings.
				 */
				config: {
					/**
					 * Adapter key for configuration data.
					 */
					adapter: 'file-json',

					/**
					 * File to save configuration data to.
					 */
					selector: '.private/navigation.json'
				},

				/**
				 * Section settings.
				 */
				sections: {
					/**
					 * Adapter key for section file data.
					 */
					adapter: 'file-php',

					/**
					 * Directory root of section files.
					 */
					path: 'sections',

					/**
					 * Extensions for section files.
					 */
					extension: '.phtml'
				}
			}
		},

		/**
		 * Create a page with the given URL, which is assumed not to exist.
		 *
		 * @param url URL to create.
		 * @param heading Initial heading text.
		 * @param sectionContent Section content to create, as a key-value array where the keys are the section keys
		 *   and the values are the initial content.  Only those sections present in this array will be created.  The
		 *   values may contain the following tokens: $url, which is replaced by the value of the url parameter, and
		 *   $heading, which is replaced by the value of the heading parameter.
		 * @param callback Callback function to call after all data manipulation is complete.
		 */
		createPage: function(url, heading, sectionContent, callback) {
			var s = $.sitegearNavigationManager.globalSettings, saveSection, processSection;
			saveSection = function(section, content) {
				var data = {};
				data[url + s.data.sections.extension] = content;
				$.sitegear.saveData(
					s.data.sections.adapter,
					s.data.sections.path + '/' + section + '/' + url + s.data.sections.extension,
					[ data ],
					processSection
				);
			};
			processSection = function() {
				var content, section = $.firstKey(sectionContent);
				if (section === null) {
					if ($.isFunction(callback)) {
						callback();
					}
				} else {
					content = sectionContent[section].replace(/\$url/g, url).replace(/\$heading/g, heading);
					delete sectionContent[section];
					saveSection(section, content);
				}
			};
			processSection();
		},

		/**
		 * Create a navigation item with the given settings, parented at the given index.
		 *
		 * @param url URL for the new navigation item.
		 * @param label Label for the new navigation item.
		 * @param tooltip Tooltip for the new navigation item.
		 * @param parentNavigationIndex Navigation index of the parent item, as a string.
		 * @param callback Callback function to call after the update is complete.
		 */
		createNavigationItem: function(url, label, tooltip, parentNavigationIndex, callback) {
			var s = $.sitegearNavigationManager.globalSettings;
			$.sitegear.saveData(
				s.data.config.adapter,
				s.data.config.selector + '.' + parentNavigationIndex + '.children+',
				{ url: url, label: label, tooltip: tooltip },
				function() {
					if ($.isFunction(callback)) {
						callback();
					}
				}
			);
		},

		/**
		 * Change the URL of a page.
		 *
		 * @param url Original URL of the page.
		 * @param targetUrl New URL for the page.
		 * @param callback Callback function to call after the update is complete.
		 */
		movePage: function(url, targetUrl, callback) {
			var s = $.sitegearNavigationManager.globalSettings;
			$.sitegear.describeData(
				s.data.sections.adapter,
				s.data.sections.path,
				false,
				false,
				function(data) {
					var sections = data, moveSection, processSection;
					moveSection = function(section) {
						$.sitegear.moveData(
							s.data.sections.adapter,
							s.data.sections.path + '/' + section.name + '/' + url + s.data.sections.extension,
							s.data.sections.path + '/' + section.name + '/' + targetUrl + s.data.sections.extension,
							processSection
						);
					};
					processSection = function() {
						if (sections.length === 0) {
							if ($.isFunction(callback)) {
								callback();
							}
						} else {
							var section = sections.pop();
							moveSection(section);
						}
					};
					processSection();
				}
			);
		},

		/**
		 * Change the details of a navigation item.
		 *
		 * @param navigationIndex Navigation index, as a string.
		 * @param url New URL for the navigation item, or null to ignore.
		 * @param label New label for the navigation item, or null to ignore.
		 * @param tooltip New tooltip for the navigation item, or null to ignore.
		 * @param callback Callback function to call after the update is complete.
		 */
		updateNavigationItem: function(navigationIndex, url, label, tooltip, callback) {
			var s = $.sitegearNavigationManager.globalSettings, data = {};
			if (url) {
				data.url = url;
			}
			if (label) {
				data.label = label;
			}
			if (tooltip) {
				data.tooltip = tooltip;
			}
			$.sitegear.saveData(
				s.data.config.adapter,
				s.data.config.selector + '.' + navigationIndex + '+',
				data,
				function() {
					if ($.isFunction(callback)) {
						callback();
					}
				}
			);
		},

		/**
		 * Delete the page with the given URL, which is assumed to exist.
		 *
		 * @param url URL to delete.
		 * @param callback Callback function to call after all data manipulation is complete.
		 */
		deletePage: function(url, callback) {
			var s = $.sitegearNavigationManager.globalSettings, sections, deleteSection, processSection;
			deleteSection = function(section) {
				$.sitegear.deleteData(
					s.data.sections.adapter,
					s.data.sections.path + '/' + section.name + '/' + url + s.data.sections.extension,
					processSection
				);
			};
			processSection = function() {
				if (sections.length === 0) {
					if ($.isFunction(callback)) {
						callback();
					}
				} else {
					var section = sections.pop();
					deleteSection(section);
				}
			};
			$.sitegear.describeData(
				s.data.sections.adapter,
				s.data.sections.path,
				false,
				false,
				function(data) {
					sections = data;
					processSection();
				}
			);
		},

		/**
		 * Delete the specified navigation item.
		 *
		 * @param navigationIndex Index of the navigation item to delete, as a string.
		 * @param callback Callback function to call when the update is complete.
		 */
		deleteNavigationItem: function(navigationIndex, callback) {
			var s = $.sitegearNavigationManager.globalSettings;
			$.sitegear.deleteData(
				s.data.config.adapter,
				s.data.config.selector + '.' + navigationIndex,
				function() {
					if ($.isFunction(callback)) {
						callback();
					}
				}
			);
		}
	};

	$.widget('sitegear.navigationManager', $.sitegear.treePanelDialog, {

		options: {
			/**
			 * Additional CSS classes.
			 */
			cssClass: {
				instructionMessage: 'sitegear-navigation-manager-instruction'
			}
		},

		/**
		 * Widget constructor.
		 */
		_create: function() {
			$.extend(true, this.options, {
				title: 'Navigation Manager',
				cookie: 'navigationManager',
				width: 600,
				height: 400,
				treeToolbarCommands: {
					iconSrcPrefix: $.sitegear.siteParameters.platform.path + 'modules/navigation/management/images/images/',
					createItem: {
						text: 'Create',
						tooltip: 'Create Navigation Item',
						icon: {
							src: 'create-navigation-item.png'
						}
					},
					deleteItem: {
						text: 'Delete',
						tooltip: 'Delete Navigation Item',
						icon: {
							src: 'delete-navigation-item.png'
						}
					},
					editItem: {
						text: 'Edit',
						tooltip: 'Edit Navigation Item',
						icon: {
							src: 'edit-navigation-item.png'
						}
					}
				},
				dataEditor: {
					dataTypeDefinition: {
						label: {
							label: 'Label',
							defaultValue: 'New Menu Item'
						},
						tooltip: {
							label: 'Tooltip',
							defaultValue: ''
						},
						url: {
							label: 'URL',
							defaultValue: ''
						}
					}
				},
				dataEditorDialog: {
					title: 'Navigation Item Editor',
					cookie: 'navigationItemEditor'
				},
				splitterCookie: 'navigationManagerSplitter',
				cancelConfirmationDialog: {
					question: 'Are you sure you wish to cancel and lose changes you have made to the site navigation?'
				},
				id: {
					dialog: 'sitegear-navigation-manager-dialog',
					dataEditor: 'sitegear-navigation-manager-data-editor'
				},
				cssClass: {
					splitter: 'sitegear-navigation-manager-splitter full-size-splitter',
					treePanelSplitter: 'sitegear-navigation-manager-tree-panel-splitter full-size-splitter',
					detailsPanel: 'sitegear-navigation-manager-details-panel',
					detailsPanelWrapper: 'ui-widget-content'
				}
			});
			$.sitegear.treePanelDialog.prototype._create.call(this);
		},

		/**
		 * @inheritDoc
		 */
		_loadData: function(callback) {
			var self = this, s = $.sitegearNavigationManager.globalSettings;
			$.sitegear.loadData(s.data.config.adapter, s.data.config.selector, function(data) {
				if (callback && $.isFunction(callback)) {
					callback.call(this, self._convertNavigationDataForJsTree(data));
				}
			});
		},

		/**
		 * Convert from the data format provided by PHP to the format required by the jsTree widget.
		 */
		_convertNavigationDataForJsTree: function(data) {
			var self = this, result = [];
			$.each(data, function(navigationIndex, item) {
				var r = {
					data: {
						title: item.label || item.url
					},
					metadata: item
				};
				if ($.isArray(item.children)) {
					r.children = self._convertNavigationDataForJsTree(item.children);
				}
				result.push(r);
			});
			return result;
		},

		/**
		 * @inheritDoc
		 */
		_saveData: function() {
			var self = this, s = $.sitegearNavigationManager.globalSettings,
				navigation = self._convertNavigationDataForBackend($.jstree._reference(self.$tree)._get_children(-1));
			$.sitegear.saveData(
				s.data.config.adapter,
				s.data.config.selector,
				navigation,
				function() {
					self.setClean();
					self.close();
					$.sitegear.reloadPage();
				}
			);
		},

		/**
		 * Convert the data from the given nodes into the format required by the PHP code.
		 */
		_convertNavigationDataForBackend: function(nodes) {
			var self = this,
				children, nodeData, result = [];
			$.each(nodes, function(index, node) {
				children = $.jstree._reference(self.$tree)._get_children(node);
				nodeData = $(node).data('jstree');
				if (children && children.length > 0) {
					nodeData.children = self._convertNavigationDataForBackend(children);
				}
				result.push(nodeData);
			});
			return result;
		},

		/**
		 * @inheritDoc
		 */
		_updateDetailsPanel: function(nodeData) {
			this.$detailsPanel.empty().append($('<h2></h2>').text('Navigation Item Details'));
			if (nodeData.hasOwnProperty('url')) {
				var fullUrl = $.sitegear.siteParameters.url.base + nodeData.url,
					depth, matches,
					$previewLink = $('<a></a>').text(fullUrl).attr({
						href: fullUrl,
						title: 'Open this page in a new window'
					}).click(function() {
						window.open($(this).attr('href'));
						return false;
					});
				this.$detailsPanel.append($('<p></p>').append('Full URL of Page: ').append($previewLink));
				if (nodeData.url === '') {
					$('<p></p>').text('This is a link to the home page.').appendTo(this.$detailsPanel);
				} else {
					depth = (nodeData.url.split('/').length);
					$('<p></p>').text('This is a link to a normal page, with a URL ' + depth + ' ' + (depth === 1 ? 'level' : 'levels') + ' deep.').appendTo(this.$detailsPanel);
				}
			} else {
				$('<p></p>').text('This is a label only, it does not link to any page').appendTo(this.$detailsPanel);
			}
			if (typeof nodeData.children === 'string') {
				matches = nodeData.children.match(/^(.+?)s:(.+?):(.+)$/);
				$('<p></p>').text('This menu item has children determined by the ' + matches[2] + ' ' + matches[1] + ' (' + matches[3] + ').').appendTo(this.$detailsPanel);
			} else if ($.isArray(nodeData.children)) {
				$('<p></p>').text('This menu item has ' + nodeData.children.length + ' children as displayed in the tree.').appendTo(this.$detailsPanel);
			} else {
				$('<p></p>').text('This menu item has no children.').appendTo(this.$detailsPanel);
			}
		},

		/**
		 * Select the node that links to the current page.  If no such node is found, select the first node.
		 */
		_getInitialTreeItem: function() {
			var $item, data, $link = null,
				relativeUrl = $.sitegear.siteParameters.url.relative;
			this.$tree.find('a').each(function(i, item) {
				$item = $(item);
				data = $item.parent().data('jstree');
				if ($link === null || (data && data.url && (relativeUrl === data.url))) {
					$link = $item;
				}
			});
			return $link;
		}
	});
}(jQuery));
