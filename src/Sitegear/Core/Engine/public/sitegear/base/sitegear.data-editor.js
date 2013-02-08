/*!
 * sitegear.data-editor.js
 * Sitegear Widget - Associative Data Editor - JavaScript
 * Provides a generic interface for editing key-value data such as that contained in a relational database.
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	$.widget('sitegear.dataEditor', {
		options: {
			data: {
				adapter: null,
				selector: null
			},
			dataTypeDefinition: null,
			initialRecord: null, 
			success: null
		},

		/**
		 * Widget constructor
		 */
		_create: function() {
			var self = this, $editor = $(this.element), o = this.options;
			this.additionalCallback = null;

			this.$form = $('<form></form>').attr({ method: 'POST', action: '.' }).submit(function() {
				var submitData = self.data();
				$.sitegear.saveData(
					o.data.adapter,
					o.data.selector,
					[ submitData ],
					function(responseData) {
						if ($.isFunction(o.success)) {
							o.success($editor, self.$form, submitData, responseData);
						}
						if ($.isFunction(self.additionalCallback)) {
							self.additionalCallback($editor, self.$form, submitData, responseData);
						}
						self.additionalCallback = null; // Additional callback is use-once only
					}
				);
				return false;
			});

			$.each(o.dataTypeDefinition, function(key, fieldDefinition) {
				var $label = $('<label></label>').attr({ 'for': key }).text(fieldDefinition.label),
					value = (o.initialRecord ? o.initialRecord[key] : false) || fieldDefinition.defaultValue || '',
					$input = $('<input type="text" />').attr({ name: key }).val(value);
				if (fieldDefinition.editable === false) {
					$input.attr({ readonly: true });
				}
				self.$form.append($('<div></div>').append($label).append($input));
			});

			return $editor.addClass('sitegear-data-editor ui-widget-content').append(this.$form);
		},

		destroy: function() {
			this.$form.remove();
			$(this.element).removeClass().removeClass('sitegear-data-editor ui-widget-content');
		},

		submitForm: function(callback) {
			this.additionalCallback = callback;
			this.$form.submit();
			return this;
		},

		defaultRecord: function() {
			var record = {}, o = this.options;
			$.each(o.dataTypeDefinition, function(key, def) {
				record[key] = (o.initialRecord ? o.initialRecord[key] : false) || def.defaultValue;
			});
			return record;
		},

		data: function(record) {
			return (record === undefined) ? this._getData() : this._setData(record);
		},

		_getData: function() {
			var result = {};
			this.$form.find('input[type=text]').each(function(i, elem) {
				result[$(elem).attr('name')] = $(elem).val();
			});
			return result;
		},

		_setData: function(record) {
			var self = this;
			$.each(record, function(key, value) {
				$('input[name=' + key + ']', self.$form).val(value);
			});
			return this;
		}
	});
}(jQuery));
