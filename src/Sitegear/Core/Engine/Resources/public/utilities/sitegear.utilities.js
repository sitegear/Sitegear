/*!
 * sitegear.utilities.js
 * Utilities for Sitegear useful for both management tools and potentially within client websites.
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

	if (!$.isFunction(Array.prototype.filter)) {
		Array.prototype.filter = function(fun /*, thisp */) {
			if (!$.isFunction(fun)) {
				throw new TypeError();
			}
			var res = [];
			$.each(this, function(i, val) {
				if (fun.call(this, val, i, this)) {
					res.push(val);
				}
			});
			return res;
		};
	}

	/**
	 * Apply fixed positioning to the selected element(s), while retaining existing current on-screen location(s).
	 */
	$.fn.fixed = function() {
		return $(this).each(function() {
			return $(this).css({ 
				position: 'fixed',
				left: $(this).position().left - $(window).scrollLeft() + 'px',
				top: $(this).position().top - $(window).scrollTop() + 'px'
			});
		});
	};

	/**
	 * Get attributes of the first matching element as a key-value array.  All other matching elements are ignored.
	 *
	 * For example, given a HTML document containing:
	 *
	 * &lt;div id="demo1" class="test-class another-class" title="foo bar"&gt;Some content&lt;div&gt;
	 *
	 * Then the call <code>$('#demo1').getAttributes()</code> would yield:
	 *
	 * <code>{ id: 'demo1', 'class': 'test-class another-class', title: 'foo bar' }</code>
	 *
	 * @return Key-value array containing the attributes of the matched element, with attribute names as keys and
	 *   attribute values as values.
	 */
	$.fn.getAttributes = function() {
		if (!this.length) {
			return null;
		}
		var attributes = {}; 
		$.each(this[0].attributes, function(index, attr) {
			attributes[attr.name] = attr.value;
		}); 
		return attributes;
	};

	$.extend(true, {
		/**
		 * Surround the current selection with an element with the given name, or, if an element with the given name is
		 * already surrounding the current selection, remove it.  If nothing is selected, create or remove an empty
		 * element with the given name at the caret position.
		 *
		 * This method handles browser inconsistencies in implementation of ranges.
		 * 
		 * @param elementName Name of the element, e.g. 'span', 'strong'.
		 * @param attributes Name-value map of attributes.
		 */
		surroundContents: function(elementName, attributes) {
			var range, i, selection, original, elementRegExp, replacement, isTempId, $elem;
			if (!$.isPlainObject(attributes)) {
				attributes = {};
			}
			if (!attributes.id) {
				isTempId = true;
				attributes.id = 'tmp_' + elementName + '_' + (Math.round(Math.random() * 900000) + 100000);
			}
			if ($.isFunction(window.getSelection)) {
				// Standards compliant
				selection = window.getSelection();
				for (i=0; i<selection.rangeCount; i++) {
					// TODO Make this work for: <b>text [text] text</b> --> <b>text</b> text <b>text</b>
					range = selection.getRangeAt(i);
					replacement = document.createElement(elementName);
					$.each(attributes, function(name, value) {
						replacement.setAttribute(name, value);
					});
					range.surroundContents(replacement);
					selection.addRange(range);
				}
			} else if (document.selection && document.selection.createRange) {
				// Internet Explorer
				range = document.selection.createRange();
				if (range && range.pasteHTML) {
					original = range.htmlText;
					elementRegExp = new RegExp('^\\s*\\<' + elementName + '\\s*.*\\>(.*)\\<\\/' + elementName + '\\>\\s*$', 'i');
					if (original.match(elementRegExp)) {
						replacement = original.replace(elementRegExp, '$1');
					} else {
						replacement = '<' + elementName;
						$.each(attributes, function(name, value) {
							replacement += ' ' + name + '="' + value + '"';
						});
						replacement += '>' + original + '</' + elementName + '>';
					}
					range.pasteHTML(''); // This is required to remove previous formatting elements
					range.pasteHTML(replacement);
					// TODO IE: This doesn't seem to work?
					range.select();
				}
			}
			$elem = $(elementName + '#' + attributes.id);
//			if (isTempId) {
//				$elem.removeAttr('id');
//			}
			return $elem;
		},

		/**
		 * Replace the current text selection with the given contents.
		 *
		 * @param contents Contents to replace the current selection.
		 */
		replaceContents: function(contents) {
			var range, i, selection, replacement;
			if ($.isFunction(window.getSelection)) {
				// Standards compliant
				selection = window.getSelection();
				for (i=0; i<selection.rangeCount; i++) {
					range = selection.getRangeAt(i);
					replacement = document.createTextNode(contents);
					range.deleteContents();
					range.insertNode(replacement);
					range.setStartAfter(replacement);
					range.setEndAfter(replacement);
				}
			} else if (document.selection && document.selection.createRange) {
				// Internet Explorer
				// TODO IE
				console.log('Internet Explorer not yet supported');
			}
		},

		/**
		 * Convert a complex, recursive data structure to a flat list of strings.  The data consists of nested arrays
		 * of objects, each of which contains the property specified by the valueProperty option.  Each object may also
		 * optionally contain a nested array of children in the property specified by the childrenProperty option.  The
		 * returned array contains the values of the properties specified by valueProperty, separated by the value of
		 * the outputSeparator option, according to the structure of the input array.
		 * 
		 * @param data Data to convert.
		 * @param options Options hash.
		 * 
		 * @return Flattened array of strings.
		 */
		flattenRecursive: function(data, options) {
			var result = [],
				defaults = {
					outputSeparator: '.', 
					childrenProperty: 'children',
					valueProperty: 'value',
					finalTransformation: null
				};
			options = $.extend(true, {}, defaults, options);
			$.each(data, function(index, entry) {
				if ($.isArray(entry[options.childrenProperty])) {
					$.extend(true, result, $.flattenRecursive(
						entry[options.childrenProperty], 
						$.extend(true, {}, options, { 
							prefix: options.prefix + options.outputSeparator + entry[options.valueProperty]
						})
					));
				} else {
					var value = entry[options.valueProperty];
					result.push(
						$.isFunction(options.finalTransformation) ? 
							options.finalTransformation(value) : 
							value
					);
				}
			});
			return result;
		},

		/**
		 * Get keys of an object.  Taken from:
		 * http://www.jquery4u.com/tag/jquery-get-object-keys/
		 *
		 * @param obj
		 */
		keys: function(obj) {
			var a = [];
			$.each(obj, function(k) { 
				a.push(k);
			});
			return a;
		},

		/**
		 * Get the first key from an object.
		 *
		 * @param obj
		 */
		firstKey: function(obj) {
			var a = null;
			$.each(obj, function(k) {
				if (a === null) {
					a = k;
				}
			});
			return a;
		},

		/**
		 * Format the argument as a file or data size value, i.e. X bytes, X kB, X MB, X GB or X TB, as appropriate so
		 * that X is between 0 and 1,024 (inclusive).
		 *
		 * @param bytes Value to format.
		 *
		 * @return Formatted value.
		 */
		formatBytes: function(bytes) {
			var result = bytes, scale = 0, scales = [ 'bytes', 'kB', 'MB', 'GB', 'TB' ];
			while (result >= 1024) {
				result = result >> 10;
				scale++;
			}
			return result + ' ' + scales[scale];
		},

		/**
		 * Generic, sitegear related functionality.
		 */
		sitegear: {

			/**
			 * Element used by the showLoadingOverlay() and hideOverlay() methods.
			 */
			$loadingOverlay: $('<div></div>')
				.addClass('ui-widget-overlay sitegear-loading-overlay')
				.append($('<div></div>').addClass('sitegear-loading-modal ui-widget-content ui-state-default'))
				.hide()
				.appendTo($(document.body)),

			/**
			 * Display a (modal) message dialog.
			 * 
			 * @param title Title of the dialog.
			 * @param message Text to display in the dialog.  May contain markup.
			 * @param okButtonText (optional) Text for the negative response button, 'OK' by default.
			 * @param additionalButtons (optional) Array of button definitions to additionally display in the dialog's
			 *   button bar, each an object containing 'text' and 'click' keys.
			 *
			 * @return The newly created (and displayed) dialog widget.
			 */
			showMessageDialog: function(title, message, okButtonText, additionalButtons) {
//				console.log('$.sitegear.showMessageDialog()');
				var $messageDialog = $('<div></div>').append(message),
					buttons = (additionalButtons && $.isArray(additionalButtons)) ? additionalButtons : [];
				return $messageDialog.dialog({
					title: title,
					width: 'auto',
					height: 'auto',
					modal: true,
					resizable: false,
					open: function() {
						$(this).dialog('widget').fixed();
					},
					buttons: buttons.concat([
						{
							text: okButtonText || 'OK',
							click: function() {
								$messageDialog.dialog('close');
								$messageDialog.dialog('destroy');
							}
						}
					])
				});
			},

			/**
			 * Display a (modal) confirmation dialog.
			 * 
			 * @param title Title of the dialog.
			 * @param question Question to display in the dialog.  May contain markup.
			 * @param callback (optional) Function to call when the OK button is clicked.
			 * @param yesButtonText (optional) Text for the positive response button, 'Yes' by default.
			 * @param noButtonText (optional) Text for the negative response button, 'No' by default.
			 * @param additionalButtons (optional) Array of button definitions to additionally display in the dialog's
			 *   button bar, each an object containing 'text' and 'click' keys.
			 *
			 * @return The newly created (and displayed) dialog widget.
			 */
			showConfirmationDialog: function(title, question, callback, yesButtonText, noButtonText, additionalButtons) {
//				console.log('$.sitegear.showConfirmationDialog()');
				var $confirmationDialog = $('<div></div>').append(question),
					buttons = (additionalButtons && $.isArray(additionalButtons)) ? additionalButtons : [];
				return $confirmationDialog.dialog({
					title: title,
					width: 'auto',
					height: 'auto',
					modal: true,
					resizable: false,
					open: function() {
						$(this).dialog('widget').fixed();
					},
					buttons: buttons.concat([
						{
							text: yesButtonText || 'Yes',
							click: function() {
								if ($.isFunction(callback)) {
									callback();
								}
								$confirmationDialog.dialog('close');
								$confirmationDialog.dialog('destroy');
							}
						},
						{
							text: noButtonText || 'No',
							click: function() {
								$confirmationDialog.dialog('close');
								$confirmationDialog.dialog('destroy');
							}
						}
					])
				});
			},

			/**
			 * Display a modal dialog requesting some user input in a regular text field.  The input may be validated
			 * by the validator function.
			 * 
			 * @param title Title of the dialog.
			 * @param question Question to display in the dialog.  May contain markup.
			 * @param initialValue The initial value of the input field.
			 * @param validator (optional) Function to call to validate the input.  This function receives the new
			 *   value as its single parameter, and returns true to indicate a valid value, or either false or a string
			 *   (error message) to indicate failure.
			 * @param callback (optional) Function to call when the OK button is clicked.
			 * @param okButtonText (optional) Text for the OK button, 'OK' by default.
			 * @param cancelButtonText (optional) Text for the Cancel button, 'Cancel' by default.
			 * @param additionalButtons (optional) Array of button definitions to additionally display in the dialog's
			 *   button bar, each an object containing 'text' and 'click' keys.
			 *
			 * @return The newly created (and displayed) dialog widget.
			 */
			showInputDialog: function(title, question, initialValue, validator, callback, okButtonText, cancelButtonText, additionalButtons) {
//				console.log('$.sitegear.showInputDialog()');
				var $inputDialog, $form, $inputField = $('<input type="text"/>').attr({ name: 'dialogField' }).val(initialValue),
					buttons = (additionalButtons && $.isArray(additionalButtons)) ? additionalButtons : [];
				$form = $('<form></form>').append($inputField).submit(function() {
					$inputDialog.dialog('widget').find('.ui-dialog-buttonset button:eq(0)').click();
					return false;
				});
				$inputDialog = $('<div></div>').append(question).append($form);
				return $inputDialog.dialog({
					title: title,
					width: 'auto',
					height: 'auto',
					modal: true,
					resizable: false,
					open: function() {
						$(this).dialog('widget').fixed();
						$inputField.select();
					},
					buttons: buttons.concat([
						{
							text: okButtonText || 'OK',
							click: function() {
								var newValue = $inputField.val(),
									valid = !$.isFunction(validator) || validator(newValue);
								if (valid === true) {
									if ($.isFunction(callback)) {
										callback(newValue);
									}
									$inputDialog.dialog('close');
									$inputDialog.dialog('destroy');
								} else {
									$.sitegear.showMessageDialog('Error', (typeof valid === 'string') ? valid : 'That value is not valid, please try again.');
									$(this).blur();
								}
							}
						},
						{
							text: cancelButtonText || 'Cancel',
							click: function() {
								$inputDialog.dialog('close');
								$inputDialog.dialog('destroy');
							}
						}
					])
				});
			},

			/**
			 * Display a modal dialog requesting a user selection from a set of buttons.
			 * 
			 * @param title Title of the dialog.
			 * @param question Question to display in the dialog.  May contain markup.
			 * @param options Array of options, each an object containing 'text' and 'click' keys.
			 * @param cancelButtonText (optional) Text for the Cancel button, 'Cancel' by default.
			 * @param additionalButtons (optional) Array of button definitions to additionally display in the dialog's
			 *   button bar, each an object containing 'text' and 'click' keys.
			 *
			 * @return The newly created (and displayed) dialog widget.
			 */
			 showOptionsDialog: function(title, question, options, cancelButtonText, additionalButtons) {
//				console.log('$.sitegear.showOptionsDialog()');
				cancelButtonText = cancelButtonText || 'Cancel';
				var $optionsPanel = $('<ul></ul>').addClass('ui-helper-reset'),
					$optionsDialog = $('<div></div>').append(question).append($optionsPanel),
					buttons = (additionalButtons && $.isArray(additionalButtons)) ? additionalButtons : [],
					$button;
				$.each(options, function(i, option) {
					$button = $('<button></button>').addClass('sitegear-options-dialog-option-button').text(option.text).click(function() {
						option.click();
					}).button({
						disabled: option.disabled || false
					});
					$optionsPanel.append($('<li></li>').append($button));
				});
				return $optionsDialog.dialog({
					title: title,
					width: 'auto',
					height: 'auto',
					modal: true,
					resizable: false,
					open: function() {
						$(this).dialog('widget').fixed();
						$optionsPanel.find('button:first').select();
					},
					buttons: buttons.concat([
						{
							text: cancelButtonText,
							click: function() {
								$optionsDialog.dialog('close');
								$optionsDialog.dialog('destroy');
							}
						}
					])
				});
			},

			/**
			 * Show the login dialog.
			 *
			 * @param url URL of the login form (optional, default is the correct URL in all known cases).
			 * @param data Data when used for callback (internal use only).
			 */
			showLoginDialog: function(url, data) {
				url = url || 'sitegear/auth/login';
				$.ajax({
					url: url,
					type: 'POST',
					data: data,
					success: function(response) {
						var $loginDialog = $('<div></div>').html(response),
							$loginForm = $loginDialog.find('form#login-form');
						$('h1', $loginDialog).hide();
						$loginForm.submit(function() {
							$loginDialog.dialog('close');
							$loginDialog.dialog('destroy');
							$.sitegear.showLoginDialog(url, $(this).serializeArray());
							return false;
						});
						$loginDialog.dialog({
							title: 'Login',
							modal: true,
							buttons: $loginForm.length === 0 ? [] : [
								{
									text: 'Login',
									click: function() {
										$loginForm.submit();
									}
								},
								{
									text: 'Cancel',
									click: function() {
										$loginDialog.dialog('close');
										$loginDialog.dialog('destroy');
									}
								}
							]
						}).find('input[type=submit]').hide();
					}
				});
			},

			/**
			 * Display the sitegear about dialog.
			 *
			 * @return The newly created (and displayed) dialog widget.
			 */
			showAboutDialog: function() {
//				console.log('$.sitegear.showAboutDialog()');
				var p = $.sitegear.siteParameters.platform,
					$aboutDialog = $('<div></div>')
						.addClass('sitegear-about-dialog')
						.append($('<p></p>').addClass('title').append($('<a></a>').attr({ href: p.websiteUrl, rel: 'external' }).addClass('external').html(p.appName).click(function() {
							window.open($(this).attr('href'));
							return false;
						})))
						.append($('<p></p>').addClass('release').html(p.release))
						.append($('<p></p>').addClass('license').html(p.licenseInfo))
						.append($('<p></p>').addClass('website-url').append($('<a></a>').attr({ href: p.websiteUrl, rel: 'external' }).addClass('external').text(p.websiteUrl).click(function() {
							window.open($(this).attr('href'));
							return false;
						})));
				return $aboutDialog.dialog({
					title: 'About ' + p.appName,
					width: 'auto',
					height: 'auto',
					modal: true,
					resizable: false,
					open: function() {
						$(this).dialog('widget').fixed();
					},
					buttons: [
						{
							text: 'Visit Website',
							click: function() {
								window.open(p.websiteUrl);
							}
						},
						{
							text: 'Close',
							click: function() {
								$aboutDialog.dialog('close');
								$aboutDialog.dialog('destroy');
							}
						}
					]
				});
			},

			/**
			 * Show the modal "loading" overlay.
			 *
			 * @param callback (optional) Callback to pass to the fadeIn() function.
			 */
			showLoadingOverlay: function(callback) {
				$.sitegear.$loadingOverlay.fadeIn('fast', callback);
			},

			/**
			 * Hide the modal "loading" overlay.
			 *
			 * @param callback (optional) Callback to pass to the fadeOut() function.
			 */
			hideLoadingOverlay: function(callback) {
				$.sitegear.$loadingOverlay.fadeOut('fast', callback);
			},

			/**
			 * Show the modal "loading" overlay, and reload the page.
			 */
			reloadPage: function(url) {
				$.sitegear.showLoadingOverlay();
				if (url) {
					window.location.href = url;
				} else {
					window.location.reload();
				}
			},

			/**
			 * Show a 'flash tip', which appears at the given coordinates
			 */
			showFlashTip: function(text, evt, timeout, widgetClass, iconClass) {
				timeout = timeout || 500;
				widgetClass = widgetClass || 'sitegear-flash-tip ui-widget-content ui-state-error';
				iconClass = iconClass || 'sitegear-flash-tip-icon ui-icon ui-icon-alert';
				var $elem = $('<div></div>').addClass(widgetClass).css({
					position: 'absolute',
					top: evt.clientY + 'px',
					left: evt.clientX + 'px',
					zIndex: 32764
				}).append($('<span></span>').addClass(iconClass)).append(text).appendTo($(evt.view.document.body));
				setTimeout(function() {
					$elem.fadeOut(function() {
						$elem.remove();
					});
				}, timeout);
			}
		}
	});
}(jQuery));
