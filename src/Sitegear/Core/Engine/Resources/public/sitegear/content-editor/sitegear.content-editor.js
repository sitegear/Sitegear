/*!
 * sitegear.content-editor.js
 * Sitegear Widget - content editor - jQuery plugin
 * Framework-aware rich text editor for page content, components, etc.
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	$.sitegearContentEditor = {
		globalSettings: {
			cssClass: {
				preview: 'preview',
				currentEditor: 'sitegear-widget-content-editor-current',
				activeEditor: 'sitegear-widget-content-editor-active',
				inactiveEditor: 'sitegear-widget-content-editor-inactive',
				basicEditor: 'sitegear-widget-content-editor-basic',
				advancedEditor: 'sitegear-widget-content-editor-advanced',
				editorPanel: 'sitegear-widget-content-editor-panel',
				toolbar: 'sitegear-widget-content-editor-toolbar',
				container: 'sitegear-widget-content-editor-container',
				buttonBar: 'sitegear-widget-content-editor-button-bar',
				loading: 'sitegear-loading',
				toolbarButtons: {
					all: 'ui-corner-all sitegear-widget-content-editor-button',
					prefix: 'sitegear-widget-content-editor-',
					suffix: '-button'
				},
				buttonBarButtons: {
					all: 'sitegear-widget-content-editor-button-bar-button',
					prefix: 'sitegear-widget-content-editor-',
					suffix: '-button'
				},
				separator: 'sitegear-widget-content-editor-separator',
				linkEditorContainer: 'sitegear-widget-content-editor-link-editor-dialog',
				linkEditorAdvancedPanel: 'sitegear-widget-content-editor-link-editor-advanced-panel',
				linkEditorAdvancedLink: 'sitegear-widget-content-editor-link-editor-advanced-link',
				linkEditorAdvancedLinkHide: 'sitegear-widget-content-editor-link-editor-advanced-link-hide',
				linkEditorAdvancedLinkShow: 'sitegear-widget-content-editor-link-editor-advanced-link-show',
				lockedSectionPreview: 'sitegear-widget-locked-section-preview'
			}
		},
		editing: false
	};

	/**
	 * Function set as an editMode callback, which updates the classes applied to this editor when the editMode is
	 * changed between 'off' and any other mode (i.e. 'advanced' or 'basic').
	 *
	 * @param editMode New edit mode.
	 */
	var updateEditorClasses = function(editMode) {
		var css = $.sitegearContentEditor.globalSettings.cssClass;
		if (editMode !== 'off') {
			$('.' + css.inactiveEditor).addClass(css.activeEditor).removeClass(css.inactiveEditor);
		} else {
			$('.' + css.activeEditor).addClass(css.inactiveEditor).removeClass(css.activeEditor);
		}
	};

	$.widget('sitegear.contentEditor', {
		options: {
			/**
			 * Tooltip for mouseover on editable elements.
			 */
			editableTooltip: 'Click to edit...',

			/**
			 * Content to display instead of empty content, when an empty element is edited.
			 */
			newContent: '<p>New content.</p>',

			/**
			 * Array of commands to display on the toolbar.
			 * 
			 * TODO Override keypress behaviour for ENTER and TAB keys.
			 */
			commands: [
				{ name: 'heading-1', label: 'Heading 1', element: 'h1', type: 'block', tooltip: 'Convert the current block to a top-level heading' },
				{ name: 'heading-2', label: 'Heading 2', element: 'h2', type: 'block', tooltip: 'Convert the current block to a second-level heading' },
				{ name: 'heading-3', label: 'Heading 3', element: 'h3', type: 'block', tooltip: 'Convert the current block to a third-level heading' },
				{ name: 'heading-4', label: 'Heading 4', element: 'h4', type: 'block', tooltip: 'Convert the current block to a fourth-level heading' },
				{ name: 'heading-5', label: 'Heading 5', element: 'h5', type: 'block', tooltip: 'Convert the current block to a fifth-level heading' },
				{ name: 'heading-6', label: 'Heading 6', element: 'h6', type: 'block', tooltip: 'Convert the current block to a sixth-level heading' },
				{ name: 'paragraph', label: 'Paragraph', element: 'p', type: 'block', tooltip: 'Convert the current block to a paragraph' },
				'separator',
				{ name: 'unordered-list', label: 'Unordered List', command: 'insertUnorderedList', tooltip: 'Insert an unordered (bullet point) list' },
				{ name: 'ordered-list', label: 'Ordered List', command: 'insertOrderedList', tooltip: 'Insert an ordered (numbered) list' },
				'separator',
//				{ name: 'table', label: 'Table', command: 'insertTable', tooltip: 'Insert a table' },
//				'separator',
				{ name: 'link', label: 'Link', element: 'a', type: 'link', tooltip: 'Create a link on the currently selected text' },
				'separator',
				{ name: 'bold', label: 'Bold', element: 'strong', type: 'inline', tooltip: 'Make the currently selected text bold' },
				{ name: 'italic', label: 'Italic', element: 'em', type: 'inline', tooltip: 'Make the currently selected text italic' },
				{ name: 'code', label: 'Code', element: 'code', type: 'inline', tooltip: 'Mark the currently selected text as code' },
				{ name: 'superscript', label: 'Superscript', element: 'sup', type: 'inline', toggle: true, tooltip: 'Make the currently selected text superscript' },
				{ name: 'subscript', label: 'Subscript', element: 'sub', type: 'inline', toggle: true, tooltip: 'Make the currently selected text subscript' },
				'separator',
				{ name: 'undo', label: 'Undo', command: 'undo', tooltip: 'Undo the last command', advanced: true },
				{ name: 'redo', label: 'Redo', command: 'redo', tooltip: 'Redo the last undone command', advanced: true }
			],

			/**
			 * This is the list of attributes that are allowed when code is pasted into the Basic mode editor.  Note
			 * that this does not affect the Advanced mode editor at all.
			 */
			allowedAttributes: [
				// Core / Standard attributes (all elements)
				'id', 'class',
				'accesskey', 'dir', 'lang', 'tabindex', 'title', 'charset',
				'xml:lang', 'xml:space', 'xml:base',
				// Data and Metadata
				// 'data-*',  // this is done programmatically as a special case
				'content', 'http-equiv',
				// Links, lists, etc
				'href', 'hreflang', 'target',
				'cite', 'datetime', 'download', 'shape',
				'command', 'icon', 'ping',
				'reversed', 'start',
				// Media
				'src', 'alt', 
				'autoplay', 'controls', 'crossorigin', 'default', 'high',
				'ismap', 'kind', 'loop', 'low', 'max', 'min', 'muted', 'optimum', 
				'media', 'mediagroup', 
				// Forms
				'action', 'method', 'enctype', 
				'name', 'type', 'value', 
				'checked', 'selected', 'disabled', 'readonly', 
				'multiple', 'placeholder', 'required', 
				'maxlength', 'size', 'rows', 'cols', 'dirname', 'list',
				'novalidate', 'pattern', 
				'accept', 'accept-charset',
				'autocomplete', 'autofocus', 'step', 'label',
				// Tables
				'colspan', 'rowspan', 'headers', 'scope'
			],

			/**
			 * Button settings for the button bar.
			 */
			buttonBarButtons: {
				save: {
					label: 'Save',
					title: 'Save changes to preview mode'
				},
				cancel: {
					label: 'Cancel',
					title: 'Cancel changes and return'
				}
			},

			/**
			 * Link editor settings.
			 */
			linkEditor: {
				dialog: {
					title: 'Edit Link',
					width: 480,
					resizable: true,
					modal: true,
					cookie: 'contentEditorLinkEditorDialog'
				},
				dialogButtons: {
					ok: 'OK',
					cancel: 'Cancel',
					removeLink: 'Remove Link'
				},
				linkDefaults: {
					url: '', 
					label: '', 
					tooltip: '', 
					rel: '', 
					className: '',
					component: '' 
				},
				autocomplete: {
					adapter: 'file',
					selector: '.dynamic-content/sections/main'
				}
			}
		},

		/**
		 * Widget constructor: set up the event handlers required to indicate editability and enable edit mode.
		 */
		_create: function() {
//			console.log('sitegearContentEditor._create()');
			var self = this, $editable = $(this.element), o = this.options,
				s = $.sitegearContentEditor.globalSettings;

			// Initialise variables.
			this.executingCommand = false;
			this.adapter = $editable.data('adapter');
			this.selector = $editable.data('selector');
			this.field = $editable.data('field');
			this.contentType = $editable.data('content-type');
			this.originalTitle = $editable.attr('title');
			this.preview = $editable.hasClass(s.cssClass.preview);
			this.autocompleteData = null;

			// Initialise UI.
			$editable.addClass(s.cssClass.activeEditor).attr({ title: o.editableTooltip }).click(function(evt) {
				if (!$(evt.target).is('a') && ($.sitegearContentEditor.editing === false)) {
					self.startEditing();
					return false;
				}
			}).mousemove(function() {
				if ($.sitegearContentEditor.editing === false) {
					$(this).addClass('hover');
				}
			}).hover(function() {
				if ($.sitegearContentEditor.editing === false) {
					$(this).addClass('hover');
				}
			}, function() {
				$(this).removeClass('hover');
			});
			$.sitegear.addEditModeCallback(updateEditorClasses);
			updateEditorClasses($.sitegear.editMode);
		},

		/**
		 * Widget destructor.
		 */
		destroy: function() {
//			console.log('sitegearContentEditor.destroy()');
			this._restoreView();
			$.sitegear.removeEditModeCallback(updateEditorClasses);
		},

		/**
		 * Start editing.  This is invoked when the user clicks and there is no other editing in progress.
		 */
		startEditing: function() {
			console.log('sitegearContentEditor._startEditing()');
			if ($.sitegear.editMode !== 'off') {
				var self = this, $editable = $(this.element), o = this.options,
					s = $.sitegearContentEditor.globalSettings;
				this.dataMap = {};
				this._setupView();
				this._showLoading();
				$.sitegear.loadData(this.adapter, this.selector, function(data) {
					if (data) {
						self._transformForLoad(data);
					} else {
						$editable.html(o.newContent);
					}
					self._initToolbar();
					self._initButtonBar();
					$editable.appendTo(self.$container)
						.removeClass(s.cssClass.activeEditor)
						.attr({ contentEditable: true })
						.addClass(s.cssClass.currentEditor)
						.addClass(s.cssClass[$.sitegear.editMode + 'Editor'])
						.show()
						.keyup(function(evt) {
							if ($.sitegear.editMode === 'advanced') {
								var keyCode = evt.keyCode || evt.which;
								if (keyCode === 13) {
									$.replaceContents('\n');
									return false;
								}
							}
						})
						.blur(function() {
							self._saveCaretPosition();
							self._restoreCaretPosition();
						});
					$('a', $editable).live('click', function() {
						self._editLink($(this));
						return false;
					});
					$('[rel=LockedSection]', $editable).live('click', function() {
						self._editLockedSection($(this));
						return false;
					});
					// TODO This does not allow different toolbars for different content types
					if (self.contentType === 'rich-text') {
						self.$toolbar.show();
					}
					self.$buttonBar.show();
					var availableHeight = $(window).height() - self.$toolbar.outerHeight() - self.$buttonBar.outerHeight() - (self.$container.outerHeight() - self.$container.height()) - (self.$container.parent().outerHeight() - self.$container.parent().height());
					self.$loading.fadeOut();
					self.$container.css({ maxHeight: availableHeight, overflowY: 'scroll' }).fadeIn();
					$(window).scrollTop($editable.parent().parent().offset().top);
					$.sitegearContentEditor.editing = true;
					$.sitegear.editModeLocked = true;
				});
			}
		},

		/**
		 * Save the changes.  This is called dynamically.
		 */
		doSave: function() {
			console.log('sitegearContentEditor.doSave()');
			this._fixContent();
			this._showLoading();
			$.sitegear.saveData(this.adapter, this.selector, this._transformForSave(), function() {
				$.sitegearContentEditor.editing = false;
				$.sitegear.editModeLocked = false;
				$.sitegear.reloadPage();
			});
		},

		/**
		 * Cancel editing and restore the original view.  This is called dynamically.
		 */
		doCancel: function() {
			console.log('sitegearContentEditor.doCancel()');
			var self = this;
			self._restoreView();
			self._showLoading();
			$.sitegearContentEditor.editing = false;
			$.sitegear.editModeLocked = false;
		},

		/**
		 * Initialise the toolbar component
		 *
		 * TODO Convert this to use toolbar() widget.
		 */
		_initToolbar: function() {
			console.log('sitegearContentEditor._initToolbar()');
			var self = this, $editable = $(this.element), o = this.options,
				s = $.sitegearContentEditor.globalSettings,
				lastAddedSeparator = true; // start of toolbar is like a separator
			$.each(o.commands, function(commandIndex, command) {
				// TODO This does not allow differet toolbars for different content types
				if ($.isPlainObject(command) && (command.advanced || $.sitegear.editMode !== 'advanced') && (self.contentType === 'rich-text')) {
					self.$toolbar.append($('<a href="#"></a>').append($('<span></span>').text(command.label)).attr({ title: command.tooltip }).bind({
						mousedown: function(evt) {
							// Prevent the toolbar buttons from stealing focus.
							evt.preventDefault();
							return false;
						},
						click: function(evt) {
							if (!self.executingCommand) {
								console.log('executing command', command);
								self.executingCommand = true;
								if (command.command) {
									console.log('document.execCommand(' + command.command + ', false, ' + command.value + ')');
									document.execCommand(command.command, false, command.value);
								} else if (command.element) {
									if (command.type === 'inline') {
										console.log('$.surroundContents(' + command.element + ')');
										$.surroundContents(command.element);
									} else if (command.type === 'block') {
										console.log('document.execCommand(formatBlock, false, <' + command.element + '>)');
										document.execCommand('formatBlock', false, '<'+command.element+'>');
									} else if (command.type === 'link') {
										console.log('_editLink() with new link element');
										self._editLink($.surroundContents('a', { 'href': '' }));
									} else {
										// TODO what?
										console.log('Unknown command type ' + command.type + '!');
										alert('Unknown command type ' + command.type + '!');
									}
									$editable.focus();
								} else {
									// TODO what?
									console.log('Unknown command ' + command.command + '!');
									alert('Unknown command ' + command.command + '!');
								}
								self.element[0].normalize();
								self.executingCommand = false;
							}
							evt.preventDefault();
							return false;
						}
					}).addClass(s.cssClass.toolbarButtons.all).addClass(s.cssClass.toolbarButtons.prefix + command.name + s.cssClass.toolbarButtons.suffix));
					lastAddedSeparator = false;
				} else if (!lastAddedSeparator) {
					self.$toolbar.append($('<span></span>').text('').addClass(s.cssClass.separator));
					lastAddedSeparator = true;
				}
			});
		},

		/**
		 * Set up the button bar buttons.
		 */
		_initButtonBar: function() {
			console.log('sitegearContentEditor._initButtonBar()');
			var self = this, o = this.options,
				s = $.sitegearContentEditor.globalSettings;
			$.each([ 'save', 'cancel' ], function(index, key) {
				self.$buttonBar.append(
					$('<a href="#"></a>').text(o.buttonBarButtons[key].label).attr({ title: o.buttonBarButtons[key].title }).click(function() {
						eval('self.do' + key.substring(0, 1).toUpperCase() + key.substring(1) + '()');
						return false;
					}).addClass(s.cssClass.buttonBarButtons.all).addClass(s.cssClass.buttonBarButtons.prefix + key + s.cssClass.buttonBarButtons.suffix).button()
				);
			});
		},

		/**
		 * Set up the editing view.
		 */
		_setupView: function() {
			console.log('sitegearContentEditor._setupView()');
			var self = this, $editable = $(this.element),
				s = $.sitegearContentEditor.globalSettings;
			$editable.attr({ title: '' }).removeClass(s.cssClass.preview).hide();
			self.$original = $editable.clone();
			self.$editorPanel = $('<div></div>').addClass(s.cssClass.editorPanel).insertAfter($editable);
			self.$toolbar = $('<div></div>').addClass(s.cssClass.toolbar).hide().appendTo(self.$editorPanel);
			self.$container = $('<div></div>').addClass(s.cssClass.container).hide().appendTo(self.$editorPanel);
			self.$buttonBar = $('<div></div>').addClass(s.cssClass.buttonBar).hide().appendTo(self.$editorPanel);
			self.$loading = $('<div></div>').addClass(s.cssClass.loading).show().appendTo(self.$editorPanel);
		},

		/**
		 * Restore the view from editing view back to normal view.
		 */
		_restoreView: function() {
			console.log('sitegearContentEditor._restoreView()');
			var self = this, $editable = $(this.element),
				s = $.sitegearContentEditor.globalSettings;
			$editable.empty()
					.append(self.$original.contents())
					.attr({ contentEditable: false, title: self.originalTitle })
					.removeClass(s.cssClass.currentEditor)
					.removeClass(s.cssClass.basicEditor)
					.removeClass(s.cssClass.advancedEditor)
					.addClass(s.cssClass.activeEditor)
					.insertAfter(self.$editorPanel);
			if (self.preview) {
				$editable.addClass(s.cssClass.preview);
			}
			self.$editorPanel.empty().remove();
			self.$loading.hide();
		},

		/**
		 * Show the 'loading' animation.
		 */
		_showLoading: function() {
			console.log('sitegearContentEditor._showLoading()');
			this.$toolbar.hide();
			this.$container.hide();
			this.$buttonBar.hide();
			this.$loading.show();
			$(this.element).removeAttr('title');
		},

		/**
		 * Convert the data from the format supplied by the backend into appropriately instrumented HTML.
		 */
		_transformForLoad: function(data) {
			console.log('sitegearContentEditor._transformForLoad(data...)');
			// TODO put this into a method, check is consistent with backend adapters (e.g. FileAdapter)
			var transformed = ($.isArray(data) && $.isPlainObject(data[0])) ? data[0][this.field] : '';
			if ($.sitegear.editMode === 'basic') {
				this.element.html(this._transformForBasicModeLoad(transformed));
			} else if ($.sitegear.editMode === 'advanced') {
				this.element.text(this._transformForAdvancedModeLoad(transformed));
			}
		},

		_transformForBasicModeLoad: function(data) {
			var transformed = data,
				lockedSectionMatch, lockedSectionRegex = /(<\?php\s+\/\*\*\*\s+sitegear\s+LOCK\s+\*\*\*\/\s+\?>(?:.|\n)*<\?php\s+\/\*\*\*\s+sitegear\s+UNLOCK\s+\*\*\*\/\s+\?>)/,
				processingInstruction, processingInstructionMatch, processingInstructionRegex = /(<\?php\s*.*?\s*\?>)/,
				shortFormOutputRegex = /<\?=.*?\?>/, writeMethodRegex = /\$this->write(.*?)\((.*)\);?/,
				writeMethod, writeMethodMatch, writeMethodArgs, id;
			while ((lockedSectionMatch = lockedSectionRegex.exec(transformed)) !== null) {
				if (lockedSectionMatch.length > 0) {
					id = 'content-editor-LockedSection-' + this._storeData('LockedSection', lockedSectionMatch[1]);
					transformed = transformed.replace(lockedSectionRegex, '<span id="' + id + '" contenteditable="false" rel="LockedSection" class="content-editor-server-side content-editor-LockedSection">&lt;?php /* Locked Section - Click for Source Code */ ?&gt;</span>');
				}
			}
			while ((processingInstructionMatch = processingInstructionRegex.exec(transformed)) !== null) {
				processingInstruction = processingInstructionMatch[1];
				writeMethodMatch = writeMethodRegex.exec(processingInstruction);
				if ((writeMethodMatch !== null) && (writeMethodMatch.length >= 2)) {
					writeMethod = writeMethodMatch[1];
					writeMethodArgs = writeMethodMatch.length > 2 ? this._parseMethodArgs(writeMethodMatch[2]) : [];
					id = 'content-editor-' + writeMethod + '-' + this._storeData(writeMethod, processingInstruction);
					transformed = transformed.replace(processingInstructionRegex, '<span id="' + id + '" contenteditable="false" rel="' + writeMethod + '" class="content-editor-server-side content-editor-' + writeMethod.toLowerCase() + '">&lt;?php ' + $('<div></div>').text(writeMethodMatch[0]).html() + ' ?&gt;</span>');
				} else {
					// TODO ?
					transformed = transformed.replace(processingInstructionRegex, '<span contenteditable="false">[unknown script]</span>');
				}
			}
			while (transformed.match(shortFormOutputRegex)) {
				transformed = transformed.replace(shortFormOutputRegex, '<span contenteditable="false">[variable output]</span>');
			}
			return transformed;
		},

		_transformForAdvancedModeLoad: function(data) {
			return data;
		},

		/**
		 * Store the given data against the given key in the internal data map.  This is used internally only.
		 *
		 * @param key string
		 * @param data mixed
		 */
		_storeData: function(key, data) {
			if (!$.isArray(this.dataMap[key])) {
				this.dataMap[key] = [];
			}
			var index = this.dataMap[key].length;
			this.dataMap[key].push(data);
			return index;
		},

		/**
		 * Convert the plain HTML as modified by this component, into the structure required by the backend.
		 */
		_transformForSave: function() {
			console.log('sitegearContentEditor._transformForSave(html...)');
			var record = {}, re, transformed;
			if ($.sitegear.editMode === 'advanced') {
				transformed = this.element.text();
			} else if ($.sitegear.editMode === 'basic') {
				transformed = $('<textarea></textarea>').text(this.element.html()).val();
				console.log(transformed);
				$.each(this.dataMap, function(key, dataMapForKey) {
					$.each(dataMapForKey, function(index, entry) {
						re = new RegExp('<span id="content-editor-' + key + '-' + index + '".*?<\/span>');
						transformed = transformed.replace(re, entry);
						console.log(transformed);
					});
				});
				// TODO put this into a method, check is consistent with backend adapters (e.g. FileAdapter)
			}
			record[this.field] = transformed;
			return [ record ];
		},

		/**
		 * Edit the given link element.
		 *
		 * TODO Get default values, apply some indication / control to override default
		 * TODO Put text in settings
		 */
		_editLink: function($link) {
			console.log('sitegearContentEditor._editLink($link...)', $link);
			var self = this, o = this.options,
				s = $.sitegearContentEditor.globalSettings, 
				$dialog = $('<div></div>').addClass(s.cssClass.linkEditorContainer),
				$advancedPanel = $('<div></div>').addClass(s.cssClass.linkEditorAdvancedPanel).hide(),
				$advancedLink = $('<a></a>').addClass(s.cssClass.linkEditorAdvancedLink, s.cssClass.linkEditorAdvancedLinkShow).text('Show Advanced'),
				$form = $('<form method="post"></form>'),
				$urlField = $('<input type="text" />').val($link.attr('href') || ''),
				$labelField = $('<input type="text" />').val($link.html() || ''),
				$tooltipField = $('<input type="text" />').val($link.attr('title') || ''),
				$relField = $('<input type="text" />').val($link.attr('rel') || ''),
				$classNameField = $('<input type="text" />').val($link.attr('class')|| ''),
				initAutocomplete = function() {
					$urlField.autocomplete({
						source: self.autocompleteData
					});
				};
			if ($.isArray(this.autocompleteData)) {
				initAutocomplete();
			} else {
				$.sitegear.describeData(o.linkEditor.autocomplete.adapter, o.linkEditor.autocomplete.selector, true, true, function(sectionsData) {
					self.autocompleteData = $.flattenRecursive(sectionsData, {
						outputSeparator: '/', 
						childrenProperty: 'children',
						valueProperty: 'name',
						finalTransformation: function(value) {
							return value.replace(/\.phtml$/, '');
						}
					});
					initAutocomplete();
				});
			}
			$form.append($('<div></div>').addClass('field').append($('<label for="url"></label>').text('Link To:')).append($urlField));
			$form.append($('<div></div>').addClass('field').append($('<label for="label"></label>').text('Link Text:')).append($labelField));
			$advancedPanel.append($('<div></div>').addClass('field').append($('<label for="tooltip"></label>').text('Tooltip:')).append($tooltipField));
			$advancedPanel.append($('<div></div>').addClass('field').append($('<label for="rel"></label>').text('Relevance:')).append($relField));
			$advancedPanel.append($('<div></div>').addClass('field').append($('<label for="className"></label>').text('CSS Class Name:')).append($classNameField));
			$form.append($advancedPanel);
			$form.append($advancedLink.click(function() {
				var text = $advancedPanel.css('display') === 'none' ? 'Hide Advanced' : 'Show Advanced';
				$advancedLink.text(text).toggleClass(s.cssClass.linkEditorAdvancedLinkHide, s.cssClass.linkEditorAdvancedLinkShow);
				$advancedPanel.slideToggle();
			}));
			$form.submit(function() {
				var label = $labelField.val(),
					tooltip = $tooltipField.val(),
					rel = $relField.val(),
					className = $classNameField.val();
				if (label !== '') {
					$link.attr({ href: $urlField.val() }).text(label);
					if (tooltip != '') {
						$link.attr({ title: tooltip });
					} else {
						$link.removeAttr('title');
					}
					if (rel != '') {
						$link.attr({ rel: rel });
					} else {
						$link.removeAttr('rel');
					}
					$link.removeClass();
					if (className != '') {
						$link.addClass(className);
					}
				} else {
					$link.remove();
				}
				$dialog.cookieDialog('close');
				return false;
			});
			$dialog.append($form).cookieDialog($.extend(true, {}, o.linkEditor.dialog, {
				open: function() {
					setTimeout(function() {
						$urlField.focus();
					}, 0);
				},
				buttons: [
					{
						text: o.linkEditor.dialogButtons.ok,
						click: function() {
							$form.submit();
						}
					},
					{
						text: o.linkEditor.dialogButtons.cancel,
						click: function() {
							$dialog.cookieDialog('close');
						}
					},
					{
						text:o.linkEditor.dialogButtons.removeLink,
						click: function() {
							self._removeLink($link);
							$dialog.cookieDialog('close');
						}
					}
				]
			}));
		},

		_removeLink: function($link) {
			$link.before($link.html()).remove();
		},

		/**
		 * "Edit" a locked section, by displaying the source code in a message dialog.
		 *
		 * @param $placeholder object Placeholder representing the locked section, the element that was clicked.
		 */
		_editLockedSection: function($placeholder) {
			var id = $placeholder.attr('id').replace(/^sitegear\-content\-editor\-LockedSection\-/, ''),
				data = this.dataMap.LockedSection[parseInt(id, 10)],
				$content = $('<pre class="' + $.sitegearContentEditor.globalSettings.cssClass.lockedSectionPreview + '">' + data.toString().replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</pre>').css({
					maxWidth: $(window).width() * 0.8,
					maxHeight: $(window).height() * 0.6
				});
			$.sitegear.showMessageDialog('Locked Section - Source Code', $content, 'OK');
		},

		/**
		 * Parse the given string into an indexed array of method arguments.
		 *
		 * @param string string PHP method arguments string.
		 */
		_parseMethodArgs: function(string) {
			console.log('sitegearContentEditor._parseMethodArgs(string)');
			var result = [],
				stringArgRegex = /^'([^']*)'\s*(?:,\s*(.*))?$/,
				numericArgRegex = /^([\d\.]+)(?:,\s*(.*))?$/,
				booleanArgRegex = /^(true|false)(?:,\s*(.*))?$/,
				arrayArgRegex = /^array\s*\((.*)\)(?:,\s*(.*))?$/;
			string = string.trim();
			while (string.length > 0) {
				if (string.match(stringArgRegex)) {
					// next arg is a string
					result.push(string.replace(stringArgRegex, '$1'));
					string = string.replace(stringArgRegex, '$2');
				} else if (string.match(numericArgRegex)) {
					// next arg is a number
					result.push(parseInt(string.replace(numericArgRegex, '$1'), 10));
					string = string.replace(numericArgRegex, '$2');
				} else if (string.match(booleanArgRegex)) {
					// next arg is a boolean true or false
					result.push(string.replace(booleanArgRegex, '$1') === 'true');
					string = string.replace(booleanArgRegex, '$2');
				} else if (string.match(arrayArgRegex)) {
					// next arg is an array
					//result.push(parseInt(string.replace(arrayArgRegex, '$1'), 10));
					result.push('array');
					string = string.replace(arrayArgRegex, '$2');
				}
			}
			return result;
		},

		/**
		 * Save the text caret position (cross-browser).
		 *
		 * TODO Should this be in sitegear.common.js or even utilities?
		 */
		_saveCaretPosition: function() {
			if (window.getSelection) {
				this.caretPosition = window.getSelection().getRangeAt(0);
			} else if (document.selection) {
				this.caretPosition = document.selection.createRange();
			}
		},

		/**
		 * Restore the text caret position (cross-browser).
		 *
		 * TODO Should this be in sitegear.common.js or even utilities?
		 */
		_restoreCaretPosition: function() {
			if (this.caretPosition) {
				if (window.getSelection && $.isFunction(window.getSelection)) {
					var s = window.getSelection();
					if (s.rangeCount > 0) {
						s.removeAllRanges();
					}
					s.addRange(this.caretPosition);
				} else if (document.selection && $.isFunction(this.caretPosition.select)) {
					this.caretPosition.select();
				}
			}
		},

		/**
		 * "Fix" the content of the editable element by removing font elements and extraneous attributes (i.e. old
		 * school non-CSS styling nonsense that might have been copied and pasted from another editor).
		 */
		_fixContent: function() {
			var $elem, allowedAttributes = this.options.allowedAttributes;
			this.element.html(this.element.html().replace(/<\/?font\s.*?>/g, ''));
			this.element.find('*').each(function(i, elem) {
				$elem = $(elem);
				$.each($elem.getAttributes(), function(attrName, attrValue) {
					if (attrName.match(/^data\-/) || $.inArray(attrName, allowedAttributes) < 0) {
						$elem.removeAttr(attrName);
					}
				});
			});
		}
	});
}(jQuery));
