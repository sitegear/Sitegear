/*!
 * sitegear.file-manager.js
 * Sitegear Widget - File Manager - JavaScript
 * File manager widget
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	$.widget('sitegear.fileManager', $.sitegear.cookieDialog, {
		options: {
			/**
			 * Data / backend settings.
			 */
			data: {
				/**
				 * Adapter key.  To be supplied by calling code.
				 */
				adapter: null,

				/**
				 * Selector for the root of the files to display in the file manager.  To be supplied by calling code.
				 */
				rootSelector: null
			},

			/**
			 * Array of regular expression patterns that identify files that should be hidden from the file manager,
			 * depending on the current editMode setting.
			 */
			excludedPatterns: {
				advanced: [
					/^\.svn$/,
					/^\.preview\-mode\-/
				],
				basic: [
					/^\./,
					/^libraries$/,
					/^index\.php$/,
					/^environment\.php$/
				]
			},

			/**
			 * Dialog button labels.
			 */
			dialogButtons: {
				createDirectory: 'Create Directory...',
				uploadFiles: 'Upload Files...',
				close: 'Close'
			},

			/**
			 * Details panel boilerplate.
			 */
			detailsPanel: {
				detailsHeading: '<h3>File Details</h3>',
				previewHeading: '<h3>File Preview</h3>',
				actionsHeading: '<h3>File Actions</h3>',
				noSelectionMessage: '<p>Please select a file to see a preview and/or details in this panel.</p>'
			},

			/**
			 * Action buttons for the details panel.
			 */
			actionButtons: {
				previewItem: 'Preview',
				renameItem: 'Rename',
				deleteItem: 'Delete'
			},

			/**
			 * Rename file child dialog settings.
			 */
			renameDialog: {
				title: 'Rename'
			},

			/**
			 * Preview child dialog settings.
			 */
			previewDialog: {
				title: 'Preview'
			},

			/**
			 * Preview child dialog button texts.
			 */
			previewDialogButtons: {
				close: 'Close'
			},

			/**
			 * CSS classes, for markup.
			 */
			cssClass: {
				dialog: 'file-manager-dialog',
				splitter: 'file-manager-splitter full-size-splitter',
				treePanel: 'file-manager-tree-panel ui-widget-content',
				filesPanel: 'file-manager-files-panel ui-widget-content',
				detailsPanel: 'file-manager-details-panel ui-widget-content',
				innerPanel: 'inner-panel',
				actionButton: 'file-manager-action-button',
				item: 'ui-state-default',
				hoverItem: 'hover ui-state-hover',
				selectedItem: 'selected ui-state-active',
				dropTargetActive: 'droppable',
				dropTargetHover: 'hover',
				previewDialog: 'file-manager-preview-dialog'
			},

			/**
			 * Cookie to use to store the splitter positions.
			 */
			splitterCookie: 'fileManagerSplitter',

			/**
			 * Path to icon images for MIME types, for files panel.
			 */
			mimeTypeIconPath: 'libraries/sitegear/management/images/mime-type-icons-64x64/'
		},

		/**
		 * Widget constructor.
		 */
		_create: function() {
			var self = this, $dialog = $(this.element);
			self.dirty = false;
			$.sitegear.showLoadingOverlay();
			$.extend(true, this.options, {
				title: 'File Manager',
				width: 930,
				height: 600,
				modal: true,
				autoOpen: false,
				resizable: true,
				cookie: 'fileManagerDialog',
				create: function() {
					// TODO Should be recursive=true, showLeafOnlyNodes=false
					$.sitegear.describeData(self.options.data.adapter, self.options.data.rootSelector, true, true, function(fileStructure) {
						var o = self.options;
						self.$currentSelectedNode = null;

						// Create the tree widget.
						self._setupFileStructureForJsTree(fileStructure);
						self.$tree = $('<div></div>').jstree({
							core: {
								html_titles: true,
								animation: 0,
								strings: {
									new_node: 'New Directory'
								}
							},
							plugins: [ 'json_data', 'ui', /*TODO support this properly: 'dnd',*/ 'crrm', 'themeroller', 'cookies' ],
							json_data: {
								data: function() {
									return self.treeData;
								}
							},
							ui: {
								selected_parent_open: true,
								select_limit: 1
							},
							cookies: {
								save_opened: 'sitegear_file_manager_jstree_open',
								save_selected: 'sitegear_file_manager_jstree_selected',
								cookie_options: {
									path: '/'
								}
							}
						});

						// When any tree node is selected, update the file panel to
						// show the files in the selected node.
						self.$tree.bind('select_node.jstree', function(evt, data) {
							var nodeData, $selectedNode = data.inst.get_selected();
							data.inst.open_node($selectedNode);
							if ((self.$currentSelectedNode === null) || (self.$currentSelectedNode[0] !== $selectedNode[0])) {
								// TODO This should load the directory contents to display them, on select
								nodeData = $selectedNode.data('jstree');
								nodeData = $.isPlainObject(nodeData) ? nodeData : {
									type: 'unknown',
									dirs: [],
									files: [],
									item: null
								};
								self._displayDirectoryContents(nodeData);
								// TODO Show selected directory details
								self._selectListItem(null);
								self.$currentSelectedNode = $selectedNode;
							}
						});

						// Setup the UI structure.
						self.$detailsPanelMessagePane = $('<div></div>');
						self.$detailsPanelPreviewPane = $('<div></div>');
						self.$detailsPanelActionsPane = $('<div></div>');
						self.$treePanel = $('<div></div>').addClass(o.cssClass.treePanel).append(self.$tree);
						self.$filesPanel = $('<div></div>').addClass(o.cssClass.filesPanel);
						self.$detailsPanel = $('<div></div>').addClass(o.cssClass.detailsPanel)
								.append(self.$detailsPanelMessagePane.addClass(o.cssClass.innerPanel))
								.append(self.$detailsPanelPreviewPane.addClass(o.cssClass.innerPanel))
								.append(self.$detailsPanelActionsPane.addClass(o.cssClass.innerPanel));
						self.$rightSplitter = $('<div></div>').append(self.$filesPanel).append(self.$detailsPanel).addClass(o.cssClass.splitter);
						self.$leftSplitter = $('<div></div>').append(self.$treePanel).append(self.$rightSplitter).addClass(o.cssClass.splitter);
						$dialog.addClass(o.cssClass.dialog).append(self.$leftSplitter);

						self.open();
					});
				},
				open: function() {
					var o = self.options;
					setTimeout(function() {
						self.$tree.find('a:first').click();
						self.$rightSplitter.splitter({
							type: 'v',
							splitbarClass: 'no-resize-vsplitbar',
							sizeRight: 300,
							minRight: 300,
							maxRight: 300
						});
						self.$leftSplitter.splitter({
							type: 'v',
							cookie: o.splitterCookie,
							// TODO Path based on root URL path of site.
							cookiePath: '/',
							sizeLeft: 200
						});
						$.sitegear.hideLoadingOverlay();
					}, 0);
				},
				beforeClose: function() {
					if (self.dirty) {
						$.sitegear.reloadPage();
					}
				},
				close: function() {
					self.destroy();
				},
				resize: function() {
					self.$leftSplitter.resize();
				},
				buttons: [
					{
						text: this.options.dialogButtons.createDirectory,
						click: function() {
							self._createDirectory();
						}
					},
					{
						text: this.options.dialogButtons.uploadFiles,
						click: function() {
							self._uploadFiles();
						}
					},
					{
						text: this.options.dialogButtons.close,
						click: function() {
							self.close();
						}
					}
				]
			});
			$.sitegear.cookieDialog.prototype._create.call(this);
		},

		destroy: function() {
			$.sitegear.cookieDialog.prototype.destroy.call(this);
		},

		_setupFileStructureForJsTree: function(fileStructure) {
			// Add the root node to the directory tree structure.
			var root = [{
				name: this.options.rootLabel,
				path: '',
				id: 'file-manager-root',
				type: 'site-root',
				mimeType: 'directory',
				children: fileStructure
			}];
			this.treeData = this._convertFileStructureForJsTree(root, '');
		},

		/**
		 * Convert the given file structure from the format supplied by
		 * the backend into the format required for jsTree.
		 * 
		 * @param fileStructure File structure (nested arrays) from the
		 *   backend.
		 * @param parentPath Path for the 'up one level' entry, set to null
		 *   for the top level.
		 * 
		 * @return Structure to pass to the jsTree plugin.
		 */
		_convertFileStructureForJsTree: function(fileStructure, parentPath) {
			var self = this, result = [];
			if (parentPath.length > 0) {
				fileStructure.unshift({
					name: '[up one level]',
					path: parentPath,
					type: 'parent-dir',
					mimeType: 'directory',
					icon: 'directory-grey'
				});
			}
			$.each(fileStructure, function(fileStructureIndex, itemData) {
				var resultItem = {
						data: {
							title: itemData.name
						},
						metadata: {
							type: itemData.type,
							itemData: itemData
						},
						attr: {
							id: 'file-manager-node-' + itemData.path.replace(/[\/\s]*/, '-') + itemData.name.replace(/[\/\s]*/, '-')
						}
					};
				if (itemData.type === 'dir' || itemData.type === 'site-root') {
					if (!self._isExcluded(itemData)) {
						if ($.isArray(itemData.children)) {
							// resultItem.children is a recursive structure
							resultItem.children = self._convertFileStructureForJsTree(itemData.children, itemData.path);
							// dirs and files are flat lists of immediate children only, for the files panel
							resultItem.metadata.parentDirs = itemData.children.filter(function(element) {
								return (element.type === 'parent-dir');
							});
							resultItem.metadata.dirs = itemData.children.filter(function(element) {
								return (element.type === 'dir');
							});
							resultItem.metadata.files = itemData.children.filter(function(element) {
								return (element.type === 'file');
							});
						}
						result.push(resultItem);
					}
				}
			});
			return result;
		},

		/**
		 * Determine if the given filename should be excluded, based on the
		 * configuration and the current edit mode.
		 */
		_isExcluded: function(itemData) {
			var o = this.options,
				result = false,
				editMode = $.sitegear.editMode,
				excludedPatterns = o.excludedPatterns[editMode === 'advanced' ? 'advanced' : 'basic'];
			if (itemData.type !== 'site-root') {
				$.each(excludedPatterns, function(i, pattern) {
					result = result || pattern.test(itemData.name);
				});
			}
			return result;
		},

		/**
		 * Update the files panel with the given node data, which 
		 * describes multiple files and directory within the currently
		 * selected path.
		 * 
		 * @param nodeData Data to represent in the files panel.
		 */
		_displayDirectoryContents: function(nodeData) {
			var self = this, o = this.options,
				$list = $('<ul></ul>'),
				index = 0;
			this.$filesPanel.empty().click(function() {
				$(this).find('li span').removeClass(o.cssClass.selectedItem);
				self._selectListItem(null);
			});
			$.each(nodeData.parentDirs, function(i, itemData) {
				if (!self._isExcluded(itemData)) {
					$list.append(self._createFilesPanelItem(index++, itemData, true));
				}
			});
			$.each(nodeData.dirs, function(i, itemData) {
				if (!self._isExcluded(itemData)) {
					$list.append(self._createFilesPanelItem(index++, itemData, true));
				}
			});
			$.each(nodeData.files, function(i, itemData) {
				if (!self._isExcluded(itemData)) {
					$list.append(self._createFilesPanelItem(index++, itemData, false));
				}
			});
			this.$filesPanel.append($list);
			$list.find('li').hover(
				function() {
					$(this).find('span').addClass(o.cssClass.hoverItem);
				},
				function() {
					$(this).find('span').removeClass(o.cssClass.hoverItem);
				}
			).find('span').addClass(o.cssClass.item);
		},

		/**
		 * Create the list item for the files panel, using the given 
		 * details.
		 * 
		 * @param index Index of the item within its array of siblings.
		 * @param itemData Data for the item.
		 * @param dropTarget Flag, true if the item is a directory and 
		 *   should therefore be initialised as a droppable target.
		 * 
		 * @return jQuery wrapped object for a single li element.
		 */
		_createFilesPanelItem: function(index, itemData, dropTarget) {
			var self = this, o = this.options,
				$icon = $('<img />').attr({ src: o.mimeTypeIconPath + itemData.icon + '.png', alt: 'Icon' }),
				$label = $('<span></span>').html(itemData.name.replace(/([\.\-_])/g, '$1<wbr/>')),
				originalZIndex = null,
				$previousSelectedNode,
				$item = $('<li></li>').addClass(itemData.icon).append($icon).append($label).bind({
					click: function() {
						self._selectListItem($(this));
						return false;
					},
					dblclick: function() {
						if (itemData.type === 'dir') {
							$previousSelectedNode = self.$currentSelectedNode;
							self.$tree.jstree('open_node', self.$currentSelectedNode);
							self.$tree.jstree('select_node', self.$currentSelectedNode.find('ul').children()[index], true);
							self.$tree.jstree('deselect_node', $previousSelectedNode);
						} else if (itemData.type === 'parent-dir') {
							$previousSelectedNode = self.$currentSelectedNode;
							self.$tree.jstree('select_node', self.$currentSelectedNode.parent().parent(), true);
							self.$tree.jstree('deselect_node', $previousSelectedNode);
						} else {
							self._showPreview(itemData);
						}
					}
				}).draggable({
					revert: 'invalid',
					start: function() {
						self._selectListItem($(this));
						originalZIndex = $(this).css('zIndex');
						$(this).css({ zIndex: 1000 });
					},
					stop: function() {
						$(this).css({ zIndex: originalZIndex });
					}
				}).data('file-data', itemData);
			if (dropTarget) {
				$item.droppable({
					activeClass: o.cssClass.dropTargetActive,
					hoverClass: o.cssClass.dropTargetHover,
					drop: function(evt, ui) {
						// TODO Options for move or copy
						// TODO Warning for important files
						self._moveItem($(ui.draggable), $item);
					}
				});
			}
			return $item;
		},

		/**
		 * Show the given list item as selected, and display the 
		 * corresponding details in the details panel.
		 * 
		 * @param $item jQuery object wrapping the list item that
		 *   is being selected.
		 */
		_selectListItem: function($item) {
			var self = this, o = this.options,
				$actionsList = $('<ul></ul>'),
				itemData = null, 
				$table;
			this.$filesPanel.find('li span').removeClass(o.cssClass.selectedItem);
			if ($item !== null) {
				$item.find('span').addClass(o.cssClass.selectedItem);
				itemData = $item.data('file-data');
			}
			this.$detailsPanelMessagePane.empty().append($(o.detailsPanel.detailsHeading));
			if (itemData === null) {
				this.$detailsPanelMessagePane.append(o.detailsPanel.noSelectionMessage);
			} else {
				$table = $('<table></table>')
					.append($('<tr></tr>').append($('<td></td>').html('Name:')).append($('<td></td>').html(itemData.name)))
					.append($('<tr></tr>').append($('<td></td>').html('Path:')).append($('<td></td>').html(itemData.path)))
					.append($('<tr></tr>').append($('<td></td>').html('File Size:')).append($('<td></td>').html((itemData.type === 'dir' || itemData.type === 'parent-dir') ? '-' : itemData.size.toLocaleString() + ' bytes')))
					.append($('<tr></tr>').append($('<td></td>').html('Type:')).append($('<td></td>').html(itemData.mimeType)));
				if (!!itemData.previewMode) {
					$table.append($('<tr></tr>').append($('<td></td>').html('Note:')).append($('<td></td>').html('This file has been modified in preview mode, but not yet published.')));
				}
				this.$detailsPanelMessagePane.append($table);
			}
			if (itemData !== null && itemData.mimeType.match(/^image\//)) {
				this.$detailsPanelPreviewPane.empty()
					.append($(o.detailsPanel.previewHeading))
					.append($('<img />').attr({ src: itemData.path.replace(/^\//, '') + itemData.name, alt: 'Preview' }))
					.show();
			} else {
				this.$detailsPanelPreviewPane.hide();
			}
			if (itemData !== null && itemData.path !== '') {
				if (itemData.type !== 'dir') {
					$actionsList.append($('<li></li>').append($('<a></a>').text(o.actionButtons.previewItem + ' ' + itemData.name).button().addClass(o.cssClass.actionButton).click(function() {
						self._showPreview(itemData);
					})));
				}
				$actionsList.append($('<li></li>').append($('<a></a>').text(o.actionButtons.renameItem + ' ' + itemData.name).button().addClass(o.cssClass.actionButton).click(function() {
					self._renameItem($item);
				})));
				$actionsList.append($('<li></li>').append($('<a></a>').text(o.actionButtons.deleteItem + ' ' + itemData.name).button().addClass(o.cssClass.actionButton).click(function() {
					self._deleteItem($item);
				})));
				this.$detailsPanelActionsPane.empty()
						.append($(o.detailsPanel.actionsHeading))
						.append($actionsList)
						.show();
			} else {
				this.$detailsPanelActionsPane.hide();
			}
		},

		/**
		 * Show a dialog with a text input field for the user to rename the
		 * given item.
		 * 
		 * @param $item Item to rename.
		 */
		_renameItem: function($item) {
			var self = this, o = this.options,
				itemData = $item.data('file-data');
			$.sitegear.showInputDialog(
				o.renameDialog.title, 
				'<p>Please provide a new name for ' + itemData.name + '.</p>', 
				itemData.name, 
				function(newName) {
					// Validator
					return newName.match(/^[^\/]+$/) ? true : 'The name &quot;' + newName + '&quot; is not valid, please enter a valid name.';
				}, 
				function(newName) {
					// Callback, only called when valid
					var source = itemData.path + itemData.name,
						target = itemData.path + newName;
					$.sitegear.moveData(o.data.adapter, source, target, function() {
						self.dirty = true;
						self._refreshTree();
					});
				});
		},

		/**
		 * Delete the given item.
		 * 
		 * @param $item Item to delete.
		 */
		_deleteItem: function($item) {
			var self = this, o = this.options,
				itemData = $item.data('file-data'),
				selector = itemData.path + itemData.name;
			$.sitegear.deleteData(o.data.adapter, selector, true, function() {
				$item.fadeOut();
				self.dirty = true;
				self._selectListItem(null);
				self._refreshTree();
			});
		},

		/**
		 * Move the given draggedItem to the new parent item.
		 * 
		 * @param $draggedItem Item being moved.
		 * @param $item Item that is the new parent.
		 */
		_moveItem: function($draggedItem, $item) {
			var self = this, o = this.options,
				itemData = $item.data('file-data'),
				draggedItemData = $draggedItem.data('file-data'),
				source = draggedItemData.path + draggedItemData.name,
				target = itemData.type === 'parent-dir' ? 
						itemData.path + draggedItemData.name : 
						itemData.path + itemData.name + '/' + draggedItemData.name;
			$.sitegear.moveData(o.data.adapter, source, target, function() {
				$draggedItem.fadeOut();
				self.dirty = true;
				self._refreshTree();
			});
		},

		/**
		 * Create a subdirectory within the currently selected directory, 
		 * named by the user via an input dialog.
		 */
		_createDirectory: function() {
			var self = this, o = this.options;
			$.sitegear.showInputDialog(
				o.renameDialog.title, 
				'<p>Please enter a name for the new directory.</p>', 
				'New Directory', 
				function(name) {
					// Validator
					return name.match(/^[^\/]+$/) ? true : 'The name &quot;' + name + '&quot; is not valid, please enter a valid name.';
				}, 
				function(name) {
					// Callback, only called when valid
					// TODO Put flag value somewhere central
					var source = '__EMPTY_DIRECTORY__',
						itemData = self.$currentSelectedNode.data('jstree').itemData,
						target = (itemData.type === 'site-root') ? '/' + name : itemData.path + itemData.name + '/' + name;
					$.sitegear.copyData(o.data.adapter, source, target, function() {
						self.dirty = true;
						self._refreshTree();
					});
				});
		},

		/**
		 * Allow the user to upload files to the current directory.
		 */
		_uploadFiles: function() {
			$('<div></div>').uploadManager();
		},

		/**
		 * Load and display the latest data.
		 */
		_refreshTree: function() {
			var self = this, o = this.options;
			// TODO Should be recursive=true, showLeafOnlyNodes=false
			$.sitegear.describeData(o.data.adapter, o.data.rootSelector, true, true, function(fileStructure) {
				self._setupFileStructureForJsTree(fileStructure, true);
				self.$tree.jstree('refresh');
			});
		},

		/**
		 * Show a preview dialog for the given item.
		 * 
		 * @param itemData Data for the item.
		 */
		_showPreview: function(itemData) {
			var o = this.options,
				$previewDialogInner = $('<div></div>'),
				$previewDialog = $('<div></div>').addClass(o.cssClass.previewDialog).append($previewDialogInner).dialog($.extend(true, {}, o.previewDialog, {
					autoOpen: false,
					modal: false,
					width: 'auto',
					height: 'auto',
					resizable: true,
					buttons: [
						{
							text: o.previewDialogButtons.close,
							click: function() {
								$previewDialog.dialog('close');
								$previewDialog.dialog('destroy');
							}
						}
					]
				}));
			if (itemData.mimeType.match(/^image\//)) {
				$previewDialogInner.append($('<img />').attr({ src: itemData.path.replace(/^\//, '') + itemData.name, alt: 'Preview of ' + itemData.name }).css({ maxWidth: $(window).width() * 0.75, maxHeight: $(window).height() * 0.75 }));
				$previewDialog.dialog('open');
			} else if (itemData.mimeType.match(/^text\//) || itemData.mimeType.match(/^unknown\//) || itemData.mimeType.match(/-php$/) || itemData.mimeType.match(/json/)) {
				// TODO Options?
				$.sitegear.loadData('file', itemData.path.replace(/^\//, '') + itemData.name, function(data) {
					$previewDialogInner.empty().append($('<pre></pre>').text(data[0][itemData.name]).css({ maxWidth: $(window).width() * 0.75, maxHeight: $(window).height() * 0.75 }));
					$previewDialog.dialog('open');
				});
			} else {
				$previewDialogInner.append('<p>Sorry, a preview is not currently available for files of type: ' + itemData.mimeType + '</p>');
				$previewDialog.dialog('open');
			}
		}
	});
}(jQuery));
