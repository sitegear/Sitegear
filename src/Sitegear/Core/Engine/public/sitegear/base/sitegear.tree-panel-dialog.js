/*!
 * sitegear.tree-panel-dialog.js
 * Sitegear Widget - Tree Panel Dialog - JavaScript
 * Abstract dialog extension that creates a tree and a details panel, contained in a splitter, and a separate dialog
 * containing a data editor.  Both dialogs are cookie (position saving) dialogs.
 *
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	$.widget('sitegear.treePanelDialog', $.sitegear.cookieDialog, {

		options: {
			/**
			 * Dialog button labels.
			 */
			dialogButtons: {
				saveAndClose: 'Save & Close',
				cancel: 'Cancel'
			},

			/**
			 * Command settings for the toolbar.
			 *
			 * These settings should be overridden.
			 */
			treeToolbarCommands: {
				iconSrcPrefix: $.sitegear.siteParameters.platform.path + 'management/images/tree-panel-dialog-default-icons-32x32/',
				createItem: {
					text: 'Create',
					tooltip: 'Create Item',
					icon: {
						src: 'create-item.png',
						alt: '[Create]'
					}
				},
				deleteItem: {
					text: 'Delete',
					tooltip: 'Delete Item',
					icon: {
						src: 'delete-item.png',
						alt: '[Delete]'
					}
				},
				editItem: {
					text: 'Edit',
					tooltip: 'Edit Item',
					icon: {
						src: 'edit-item.png',
						alt: '[Edit]'
					}
				}
			},

			/**
			 * Data editor settings.
			 *
			 * This should be overridden to provide at least the dataTypeDefinition child key.
			 */
			dataEditor: null,

			/**
			 * Name of the field, from the data editor's data type definition, that is used for labels in the tree.
			 */
			labelField: 'label',

			/**
			 * Data editor child dialog settings.
			 *
			 * The default values for title and cookie should be overridden.
			 */
			dataEditorDialog: {
				title: 'Item Editor',
				autoOpen: false,
				resizable: true,
				modal: true,
				width: 'auto',
				height: 'auto',
				cookie: 'treePanelDialogItemEditor'
			},

			/**
			 * Data editor child dialog button labels.
			 */
			dataEditorDialogButtons: {
				ok: 'OK'
			},

			/**
			 * Cookie name for the main splitter widget.
			 *
			 * This default value should be overridden.
			 */
			splitterCookie: 'treePanelDialogSplitter',

			/**
			 * Initial width of the left-hand side of the splitter, when there is no cookie value saved.
			 */
			treePanelWidth: 200,

			/**
			 * Settings for the Cancel button confirmation dialog.
			 */
			cancelConfirmationDialog: {
				title: 'Lose Changes?',
				question: 'Are you sure you wish to cancel and lose changes you have made?'
			},

			/**
			 * ID attribute values, for markup.
			 *
			 * These default values should be overridden.
			 */
			id: {
				dialog: 'sitegear-tree-panel-dialog',
				dataEditor: 'sitegear-tree-panel-dialog-data-editor'
			},

			/**
			 * CSS classes, for markup.
			 *
			 * These default values should be overridden.
			 */
			cssClass: {
				splitter: 'sitegear-tree-panel-dialog-splitter full-size-splitter',
				treePanelSplitter: 'sitegear-tree-panel-dialog-secondary-splitter full-size-splitter',
				detailsPanel: 'sitegear-tree-panel-dialog-details-panel',
				detailsPanelWrapper: 'ui-widget-content'
			}
		},

		/**
		 * Widget constructor.
		 */
		_create: function() {
			var self = this, $dialog = $(this.element),
				$currentSelectedNode = null,
				originalAutoOpen = this.options.autoOpen !== false,
				originalCreate = this.options.create,
				originalOpen = this.options.open,
				originalResize = this.options.resize,
				originalBeforeClose = this.options.beforeClose,
				originalClose = this.options.close;
			this.setClean();
			$.sitegear.showLoadingOverlay();
			$.extend(true, this.options, {
				modal: true,
				autoOpen: false,
				create: function(evt, ui) {
					self._loadData(function(data) {
						var o = self.options;

						// Build the data editor widget
						self.$dataEditor = $('<div></div>').attr({ id: o.id.dataEditor }).dataEditor(o.dataEditor);

						// Build the tree widget
						self.$tree = $('<div></div>').bind('select_node.jstree', function(evt, data) {
							var nodeData, $selectedNode = data.inst.get_selected();
							if (($currentSelectedNode === null) || ($currentSelectedNode[0] !== $selectedNode[0])) {
								nodeData = $selectedNode.data('jstree');
								if (!$.isPlainObject(nodeData)) {
									nodeData = self.$dataEditor.dataEditor('defaultRecord');
									nodeData.index = null;
									$selectedNode.data('jstree', nodeData);
								}
								self.$dataEditor.dataEditor('data', nodeData);
								self._updateDetailsPanel(nodeData);
								$currentSelectedNode = $selectedNode;
							}
						}).jstree({
							core: {
								html_titles: true,
								animation: 0,
								strings: {
									new_node: o.dataEditor.dataTypeDefinition[o.labelField].defaultValue
								}
							},
							plugins: [ 'json_data', 'ui', 'dnd', 'crrm', 'themeroller' ],
							json_data: {
								data: data
							},
							ui: {
								select_limit: 1
							}
						});

						// Create an element for the tree toolbar.
						self.$treeToolbar = $('<div></div>');

						// Build a dialog for the data editor.
						self.$dataEditorDialog = $('<div></div>').append(self.$dataEditor).cookieDialog($.extend(true, {}, o.dataEditorDialog, {
							buttons: [
								{
									text: o.dataEditorDialogButtons.ok,
									click: function() {
										self.$dataEditorDialog.cookieDialog('close');
									}
								}
							]
						}));

						// Update the tree when the editor fields are changed.
						$(document).delegate('#' + o.id.dataEditor + ' input', {
							change: function() {
								var $selectedNode = self.$tree.jstree('get_selected'),
									nodeData = $selectedNode.data('jstree'),
									fieldName = $(this).attr('name'),
									value = $(this).val();
								nodeData[fieldName] = value;
								$selectedNode.data('jstree', nodeData);
								if (fieldName === o.labelField) {
									self.$tree.jstree('rename_node', $selectedNode, value);
								}
								self.setDirty();
							},
							keyup: function() {
								self.setDirty();
							}
						});

						// Update the selected node and then open the editor dialog, when a node is created.
						self.$tree.bind('create_node.jstree', function(evt, data) {
							data.rslt.obj.find('a').click();
							self.editItem();
						});

						// Double click in the tree to edit the item.
						$(document).delegate('#' + o.id.dialog + ' a.jstree-clicked', {
							dblclick: function() {
								self.editItem();
							}
						});

						// Put the UI components together.
						self.$treePanelSplitter = $('<div></div>').addClass(o.cssClass.treePanelSplitter).append(self.$tree).append(self.$treeToolbar);
						self.$detailsPanel = $('<div></div>').addClass(o.cssClass.detailsPanel);
						self.$splitter = $('<div></div>').addClass(o.cssClass.splitter).append(
							self.$treePanelSplitter
						).append(
							$('<div></div>').addClass(o.cssClass.detailsPanelWrapper).append(self.$detailsPanel)
						);
						$dialog.append(self.$splitter).attr({ id: o.id.dialog });

						if (originalCreate && $.isFunction(originalCreate)) {
							originalCreate.call(this, evt, ui);
						}

						// Display the dialog.
						if (originalAutoOpen) {
							self.open();
						}
					});
				},
				open: function(evt, ui) {
					var o = self.options,
						$initialItem = self._getInitialTreeItem();
					setTimeout(function() {
						if (originalOpen && $.isFunction(originalOpen)) {
							originalOpen.call(this, evt, ui);
						}

						// Initialise the toolbar
						self.$treeToolbar.toolbar({
							iconSrcPrefix: o.treeToolbarCommands.iconSrcPrefix,
							items: [
								{
									id: 'create',
									properties: $.extend(true, {}, o.treeToolbarCommands.createItem, {
										action: function() {
											self.createItem();
											self.setDirty();
										}
									})
								},
								{
									id: 'delete',
									properties: $.extend(true, {}, o.treeToolbarCommands.deleteItem, {
										action: function() {
											self.deleteItem();
											self.setDirty();
										}
									})
								},
								{
									id: 'edit',
									properties: $.extend(true, {}, o.treeToolbarCommands.editItem, {
										action: function() {
											self.editItem();
											self.setDirty();
										}
									})
								}
							]
						});

						// Initialise the two splitters.
						self.$treePanelSplitter.splitter({
							type: 'h',
							sizeBottom: self.$treeToolbar.outerHeight(),
							minBottom: self.$treeToolbar.outerHeight(),
							maxBottom: self.$treeToolbar.outerHeight(),
							splitbarClass: 'no-resize-vsplitbar'
						});
						self.$splitter.splitter({
							type: 'v',
							cookie: o.splitterCookie,
							cookiePath: $.sitegear.siteParameters.cookie.path,
							sizeLeft: o.treePanelWidth
						});

						$.sitegear.hideLoadingOverlay(function() {
							self.$tree.jstree('select_node', $initialItem);
						});
					}, 0);
				},
				resize: function(evt, ui) {
					self.$treePanelSplitter.trigger('resize');
					self.$splitter.trigger('resize');

					if (originalResize && $.isFunction(originalResize)) {
						originalResize.call(this, evt, ui);
					}
				},
				beforeClose: function(evt, ui) {
					var result = true;
					if (originalBeforeClose && $.isFunction(originalBeforeClose)) {
						result = originalBeforeClose.call(this, evt, ui);
					}
					if (result && self.isDirty()) {
						$.sitegear.showConfirmationDialog(self.options.cancelConfirmationDialog.title, self.options.cancelConfirmationDialog.question, function() {
							self.setClean();
							self.close();
						});
						result = false;
					}
					if (!result) {
						evt.preventDefault();
					}
					return result;
				},
				close: function(evt, ui) {
					if (originalClose && $.isFunction(originalClose)) {
						originalClose.call(this, evt, ui);
					}
					self.destroy();
				},
				buttons: [
					{
						text: this.options.dialogButtons.saveAndClose,
						click: function() {
							self._saveData();
						}
					},
					{
						text: this.options.dialogButtons.cancel,
						click: function() {
							self.close();
						}
					}
				]
			});
			$.sitegear.cookieDialog.prototype._create.call(this);
		},

		/**
		 * Widget destructor.
		 */
		destroy: function() {
			var o = this.options;
			$(document).undelegate('#' + o.id.dialog + ' a.jstree-clicked');
			$(document).undelegate('#' + o.id.dataEditor + ' input');
			this.$splitter.splitter('destroy');
			this.$treePanelSplitter.splitter('destroy');
			this.$dataEditor.dataEditor('destroy');
			this.$dataEditorDialog.cookieDialog('destroy');
			this.$treeToolbar.toolbar('destroy');
			this.$tree.jstree('destroy');
			$.sitegear.cookieDialog.prototype.destroy.call(this);
		},

		/**
		 * Create a new item.
		 */
		createItem: function() {
			console.log('createItem()');
			var o = this.options;
			this.$tree.jstree('create', this.$tree.jstree('get_selected'), 'after', {
				data: {
					title: o.dataEditor.dataTypeDefinition[o.labelField].defaultValue,
					attr: $.extend({
						index: null
					}, this.$dataEditor.dataEditor('defaultRecord'))
				}
			}, null, true);
		},

		/**
		 * Delete the item currently selected in the tree (including any children).
		 */
		deleteItem: function() {
			this.$tree.jstree('remove');
		},

		/**
		 * Edit the item currently selected in the tree.
		 */
		editItem: function() {
			this.$dataEditorDialog.cookieDialog('open');
		},

		/**
		 * Set the dirty flag to on.
		 */
		setDirty: function() {
			this.dirty = true;
		},

		/**
		 * Clear the dirty flag to off.
		 */
		setClean: function() {
			this.dirty = false;
		},

		/**
		 * Determine if the data is dirty.
		 */
		isDirty: function() {
			return this.dirty;
		},

		/**
		 * Refresh the data from the server.
		 *
		 * This default implementation, which simply calls the callback with null data, should be overridden to provide
		 * implementation specific data retrieval.
		 *
		 * The implementation should load the data asynchronously and then call the callback with a data object
		 * suitable for the jstree widget, derived from the loaded data.
		 *
		 * @param callback Callback to call when the data is retrieved and converted.
		 */
		_loadData: function(callback) {
			if ($.isFunction(callback)) {
				callback.call(this, null);
			}
		},

		/**
		 * Save the data back to the server.
		 *
		 * This default no-op should be overridden to provide implementation specific data update.
		 */
		_saveData: function() {
		},

		/**
		 * Update the details panel with the given data.  Used internally only.
		 *
		 * This simple default implementation should be overridden.
		 *
		 * @param nodeData Data to update the panel from.
		 */
		_updateDetailsPanel: function(nodeData) {
			this.$detailsPanel.append(nodeData.toString());
		},

		/**
		 * Retrieve a reference to the tree item that should be initially selected, when the dialog is opened.
		 *
		 * This default implementation just selects the first item.  Override if there is something better.
		 */
		_getInitialTreeItem: function() {
			return this.$tree.find('a:first');
		}
	});
}(jQuery));
