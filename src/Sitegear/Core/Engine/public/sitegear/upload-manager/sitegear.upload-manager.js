/*!
 * sitegear.upload-manager.js
 * Sitegear Widget - File Upload Manager - JavaScript
 * File upload manager widget
 * 
 * Sitegear (c) Ben New, Leftclick.com.au
 * See LICENSE file in main directory for information, or visit:
 * http://sitegear.org/
 */

/*jslint devel: true, bitwise: true, regexp: true, browser: true, unparam: true, evil: true, white: true, nomen: true, plusplus: true, maxerr: 50, indent: 4 */
/*global jQuery */

(function($) {
	"use strict";

	$.widget('sitegear.uploadManager', $.sitegear.cookieDialog, {
		options: {
			/**
			 * The path to save the uploaded files to.  To be supplied by calling code.
			 */
			path: '',

			/**
			 * If null (the default), the name of the uploaded file will be preserved.  If set to a string value, then
			 * that value is used for the filename of every file uploaded (used for replacing images etc inline).  Any
			 * other value is an error.
			 */
			forceFilename: null,

			/**
			 * Text for the upload button.
			 */
			uploadButtonText: 'Upload or Drop File',

			/**
			 * Whether or not to show the file chooser dialog initially.  Default is true.
			 */
			showFileOpenDialog: true,

			/**
			 * Button text settings for dialog.
			 */
			dialogButton: {
				upload: 'Upload File...',
				close: 'Close'
			},

			/**
			 * CSS class settings for markup.
			 */
			cssClass: {
				dialog: 'upload-manager',
				uploadedFilesTable: 'uploaded-files ui-widget-content',
				noDataRow: 'no-data',
				progressBar: 'upload-manager-progress-bar',
				progressText: 'upload-manager-progress-text',
				uploadingTo: 'upload-manager-uploading-to'
			}
		},

		/**
		 * Widget constructor.
		 */
		_create: function() {
			var self = this, $dialog = $(this.element);

			$.extend(true, this.options, {
				title: 'Upload Manager',
				width: 'auto',
				height: 'auto',
				modal: true,
				autoOpen: false,
				resizable: true,
				cookie: 'uploadManagerDialog',
				create: function() {
					var o = self.options;
					self.path = o.path.replace(/\/*$/, '/');
					self.$uploadedFilesTable = $('<table></table>').addClass(o.cssClass.uploadedFilesTable).append(
						$('<thead></thead>').append(
							$('<tr></tr>')
								.append($('<th></th>').text('Upload Path'))
								.append($('<th></th>').text('Filename'))
								.append($('<th></th>').text('Size'))
								.append($('<th></th>').text('Progress'))
						)
					);
					self.$uploadedFilesTableBody = $('<tbody></tbody>').appendTo(self.$uploadedFilesTable);
					self.$uploadedFilesNoDataRow = $('<tr></tr>').addClass(o.cssClass.noDataRow).append(
						$('<td></td>').attr({ colspan: 4 }).text('No files uploaded yet')
					).appendTo(self.$uploadedFilesTableBody);

					self.$fileInputHidden = $('<input />').attr({
						type: 'hidden',
						name: 'filename'
					});

					// Put the dialog together
					$dialog.empty().addClass(o.cssClass.dialog)
						.append($('<div></div>').append(self.$uploadedFilesTable));

					self.open();
				},
				open: function() {
					// Setup the file input and proxy
					self.$fileInputProxy = $dialog.parent().find('.ui-dialog-buttonpane button.ui-button:eq(0)');
					self._createFileInput();
					// Start with the file dialog open, unless switched off
					if (self.options.showFileOpenDialog) {
						self.$fileInput.click();
					}
				},
				close: function() {
					self.destroy();
				},
				buttons: [
					{
						text: this.options.dialogButton.upload,
						click: function() {
							self._createFileInput();
							self.$fileInput.click();
							return false;
						}
					},
					{
						text: this.options.dialogButton.close,
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
			$.sitegear.cookieDialog.prototype.destroy.call(this);
		},

		/**
		 * Setup the fileupload plugin
		 */
		_createFileInput: function() {
			var self = this, $dialog = $(this.element);
			if (this.$fileInput && $.isFunction(this.$fileInput.remove)) {
				this.$fileInput.hide().remove();
			}
			this.$fileInput = $('<input />').attr({ 
				type: 'file', 
				name: 'file'
			}).css({
				position: 'absolute',
				top: this.$fileInputProxy.position().top + 'px',
				left: this.$fileInputProxy.position().left + 'px',
				opacity: 0
			}).fileupload({
				url: 'sitegear/adapter/file/upload?selector=' + this.path,
				paramName: 'file',
				dataType: 'json',
				maxChunkSize: 512 * 1024, // 512kB chunks
				add: function(evt, data) {
					self._startUpload(data);
				},
				progress: function(evt, data) {
					self._updateUpload(data);
				},
				done: function(evt, data) {
					self._finishUpload(data);
				},
				fail: function(evt, data) {
					self._showError(data);
				}
			}).appendTo($dialog.parent());
		},

		/**
		 * Start the upload process.
		 *
		 * @param data Data describing files being uploaded, and progress.
		 */
		_startUpload: function(data) {
//			console.log('startUpload: ', data);
			var o = this.options,
				$row = $('<tr></tr>'),
				$progressContainer = $('<div></div>').css({ position: 'relative' });

			this.$progressBar = $('<div></div>').addClass(o.cssClass.progressBar).progressbar().appendTo($progressContainer);
			this.$progressText = $('<div></div>').addClass(o.cssClass.progressText).text('...').appendTo($progressContainer).css({
				position: 'absolute',
				top: 0,
				bottom: 0,
				left: 0,
				right: 0
			});

			this.$uploadedFilesNoDataRow.hide().remove();
			$row.append($('<td></td>').text(this.path))
				.append($('<td></td>').text(data.files[0].name))
				.append($('<td></td>').text($.formatBytes(data.files[0].size)).attr({ title: data.files[0].size + ' bytes' }))
				.append($('<td></td>').append($progressContainer))
				.appendTo(this.$uploadedFilesTableBody);

			this.$fileInputHidden.val(data.files[0].name);
			data.submit();
		},

		/**
		 * Update the upload progress bar.
		 *
		 * @param data Data describing files being uploaded, and progress.
		 */
		_updateUpload: function(data) {
//			console.log('updateUpload: ', data);
			var progress = Math.floor(data.loaded / data.total * 100);
			this.$progressText.text(progress + '%');
			this.$progressBar.progressbar('option', 'value', progress);
		},

		/**
		 * Complete an upload process.
		 *
		 * @param data Data describing files being uploaded, and progress.
		 */
		_finishUpload: function(data) {
//			console.log('finishUpload: ', data);
			var self = this;
			if (data.result === null || data.result[0].error > 0 || data.result[0].size === 0) {
				this._showError(data);
			} else {
				this.$progressBar.progressbar('option', 'value', 100).fadeOut('normal', function() {
					self.$progressText.text('Done').css({ position: 'static' });
				});
			}
		},

		/**
		 * Display an error message.
		 *
		 * @param data Data describing the error.
		 */
		_showError: function(data) {
			this.$fileInput.val('');
			this.$fileInputHidden.val('');
			this.$progressText.text('Error: file could not be uploaded').addClass('error');
			this.$progressBar.progressbar('option', 'value', 0).hide();
			$.sitegear.showMessageDialog('Error Uploading File', '<p>The file you selected, &ldquo;' + data.files[0].name + '&rdquo; could not be uploaded.  This is usually caused by exceeding the file size limit.</p><p>Please check the file and try again</p>');
		}
	});
}(jQuery));
