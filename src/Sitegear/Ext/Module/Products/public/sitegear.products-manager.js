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

	$.widget('sitegear.productsManager', $.sitegear.treePanelDialog, {

		options: {
			/**
			 * Data / backend settings.
			 */
			data: {
				/**
				 * Adapter key for products data.
				 */
				adapter: 'database',

				/**
				 * Selectors.
				 */
				selector: {
					'productCategories': 'product_categories',
					'productCategoryAssignments': 'product_category_assignments_view:product_category_id='
				}
			}
		},

		/**
		 * Widget constructor.
		 */
		_create: function() {
			this.deletedCategories = [];
			$.extend(true, this.options, {
				title: 'Products Manager',
				cookie: 'productsManager',
				width: 600,
				height: 400,
				treeToolbarCommands: {
					iconSrcPrefix: $.sitegear.siteParameters.platform.path + 'modules/products/management/images/product-manager-toolbar-icons-32x32/',
					createItem: {
						text: 'Create',
						tooltip: 'Create Product Category',
						icon: {
							src: 'create-product-category.png'
						}
					},
					deleteItem: {
						text: 'Delete',
						tooltip: 'Delete Product Category',
						icon: {
							src: 'delete-product-category.png'
						}
					},
					editItem: {
						text: 'Edit',
						tooltip: 'Edit Product Category',
						icon: {
							src: 'edit-product-category.png'
						}
					}
				},
				dataEditor: {
					dataTypeDefinition: {
						name: {
							label: 'Category Name',
							defaultValue: 'New Product Category'
						},
						tooltip: {
							label: 'Tooltip',
							defaultValue: ''
						},
						url_path: {
							label: 'URL Path',
							defaultValue: ''
						}
					}
				},
				labelField: 'name',
				dataEditorDialog: {
					title: 'Product Category Editor',
					cookie: 'productCategoryEditor'
				},
				splitterCookie: 'productsManagerSplitter',
				cancelConfirmationDialog: {
					question: 'Are you sure you wish to cancel and lose changes you have made to the products data?'
				},
				id: {
					dialog: 'sitegear-products-manager-dialog',
					dataEditor: 'sitegear-products-manager-editor'
				},
				cssClass: {
					splitter: 'sitegear-products-manager-splitter full-size-splitter',
					treePanelSplitter: 'sitegear-products-manager-tree-panel-splitter full-size-splitter',
					detailsPanel: 'sitegear-products-manager-details-panel',
					detailsPanelWrapper: 'sitegear-products-manager-details-panel-wrapper ui-widget-content'
				}
			});
			$.sitegear.treePanelDialog.prototype._create.call(this);
		},

		/**
		 * @inheritDoc
		 */
		_loadData: function(callback) {
			var self = this;
			$.sitegear.describeData(self.options.data.adapter, self.options.data.selector.productCategories + ':display_sequence+', true, false, function(data) {
				if (callback && $.isFunction(callback)) {
					callback.call(this, self._convertProductCategoryDataForJsTree(data));
				}
			});
		},

		/**
		 * Convert from the data format provided by PHP to the format required by the jsTree widget.
		 */
		_convertProductCategoryDataForJsTree: function(data) {
			var self = this, result = [];
			$.each(data, function(productIndex, item) {
				var r = {
					data: {
						title: item.name
					},
					metadata: item
				};
				if ($.isArray(item.children)) {
					r.children = self._convertProductCategoryDataForJsTree(item.children);
				}
				result.push(r);
			});
			return result;
		},

		/**
		 * @inheritDoc
		 */
		_saveData: function() {
			var self = this;
			this._saveDeletedCategories(this.deletedCategories, function() {
				// Save other items (new and modified)
				var categoriesData = self._convertProductCategoryDataForBackend($.jstree._reference(self.$tree)._get_children(-1), null);
				self._saveAllCategoriesData(categoriesData, function() {
					self.setClean();
					self.close();
					$.sitegear.reloadPage();
				});
			});
		},

		/**
		 * Convert the data from the given nodes into the format required by the PHP code.
		 */
		_convertProductCategoryDataForBackend: function(nodes, parentId) {
			var self = this, o = this.options,
				nodeData, data = [];
			$.each(nodes, function(index, node) {
				nodeData = $(node).data('jstree');
				data.push({
					adapter: o.data.adapter,
					selector: o.data.selector.productCategories + (nodeData.id ? '/' + nodeData.id : ''),
					data: {
						name: nodeData.name,
						url_path: nodeData.url_path,
						parent_id: parentId,
						display_sequence: index * 10
					}
				});
				var children = $.jstree._reference(self.$tree)._get_children(node);
				if (children && children.length > 0) {
					data = data.concat(self._convertProductCategoryDataForBackend(children, nodeData.id));
				}
			});
			return data;
		},

		/**
		 * Save the deletion of categories.
		 *
		 * @param deletedCategories Array of deleted categories.
		 * @param callback Callback when all deletions are complete.
		 */
		_saveDeletedCategories: function(deletedCategories, callback) {
			var self = this, record;
			if (deletedCategories.length === 0) {
				if ($.isFunction(callback)) {
					callback();
				}
			} else {
				record = deletedCategories.pop();
				$.sitegear.deleteData(record.adapter, record.selector + '/' + record.url_path, function() {
					self._saveDeletedCategories(deletedCategories, callback);
				});
			}
		},

		/**
		 * Save category data.
		 *
		 * @param categoriesData Data to save.
		 * @param callback Callback when saving is complete.
		 */
		_saveAllCategoriesData: function(categoriesData, callback) {
			var self = this, record;
			if (categoriesData.length === 0) {
				if ($.isFunction(callback)) {
					callback();
				}
			} else {
				record = categoriesData.pop();
				$.sitegear.saveData(record.adapter, record.selector, [ record.data ], function() {
					self._saveAllCategoriesData(categoriesData, callback);
				});
			}
		},

		/**
		 * Show product list for the selected category.
		 *
		 * @param nodeData
		 */
		_updateDetailsPanel: function(nodeData) {
			var self = this, o = this.options;
			$.sitegear.loadData(o.data.adapter, o.data.selector.productCategoryAssignments + nodeData.id + ':display_sequence+;product_url_path+', function(data) {
				var $table = $('<table></table>')
						.append($('<thead></thead>')
							.append($('<tr></tr>')
								.append($('<th></th>').text('Product Name'))
								.append($('<th></th>').attr({ colspan: 2 }).text('Actions')))),
					$tableBody = $('<tbody></tbody>').appendTo($table),
					getActive = function(active) {
						return active == '1';
					},
					getToggleActiveLabel = function(active) {
						return active ? 'deactivate' : 'activate';
					};
				$.each(data, function(i, record) {
					var active = getActive(record.product_active);
					$('<tr></tr>')
						.append($('<td></td>').html(record.product_short_name))
						.append($('<td></td>')
							.append(
								$('<a></a>').text('view').attr({
									// TODO Fix this hardcoded "shop"
									href: 'shop/' + nodeData.url_path + '/' + record.product_url_path,
									title: 'View this product\'s details page in a new window or tab...'
								}).click(function(evt) {
									window.open($(this).attr('href'));
									evt.preventDefault();
									return false;
								}).button())
//							.append(
//								$('<a></a>').text('edit').attr({
//									href: '#',
//									title: 'Edit this product\'s details'
//								}).click(function() {
//									self._displayProduct(record);
//									return false;
//								}).button()
//							)
							.append(
								$('<a></a>').text(getToggleActiveLabel(active)).attr({
									href: '#',
									title: 'Toggle this product\'s active status'
								}).click(function() {
									var $toggleActiveButton = $(this).button('option', 'label', '...');
									$.sitegear.saveData('database', 'products/' + record.product_id, [{ active: !active }], function() {
										$.sitegear.loadData('database', 'products/' + record.product_id, function(reloadData) {
											active = getActive(reloadData[0]['active']);
											$toggleActiveButton.button('option', 'label', getToggleActiveLabel(active))
										});
									});
								}).button()
							)
//							.append(
//								$('<a></a>').text('delete').attr({
//									href: '#',
//									title: 'Delete this product completely'
//								}).click(function(evt) {
//									$.sitegear.showConfirmationDialog('Delete Product?', 'Are you sure you wish to completely delete the product "' + record.product_short_name + '"?', function() {
//										$.sitegear.deleteData('database', 'product_category_assignments:product_id=' + record.product_id, function() {
//											$.sitegear.deleteData('database', 'product_attribute_assignments:product_id=' + record.product_id, function() {
//												$.sitegear.deleteData('database', 'product_specifications:product_id=' + record.product_id, function() {
//													$.sitegear.deleteData('database', 'product_relationships:product_id=' + record.product_id, function() {
//														$.sitegear.deleteData('database', 'product_relationships:related_product_id=' + record.product_id, function() {
//															$.sitegear.deleteData('database', 'products/' + record.product_id, function() {
//																$tableRow.fadeOut();
//															});
//														});
//													});
//												});
//											});
//										});
//									});
//									return false;
//								}).button()
//							)
						)
						.appendTo($tableBody);
				});
				self.$detailsPanel.empty()
					.append('<h2>Products in ' + nodeData.name + '</h2>')
					.append($table);
			});
		},

		_displayProduct: function(product) {
			var self = this, attributes, specifications, relatedProducts;
			$.sitegear.loadData('database', 'product_attribute_view:product_id=' + product.product_id, function(data) {
				attributes = data;
				$.sitegear.loadData('database', 'product_specifications_view:product_id=' + product.product_id, function(data) {
					specifications = data;
					$.sitegear.loadData('database', 'product_relationships_view:product_id=' + product.product_id, function(data) {
						relatedProducts = data;
						self._displayProductDetails(product, attributes, specifications, relatedProducts);
					});
				});
			});
		},

		_displayProductDetails: function(product, attributes, specifications, relatedProducts) {
			var $detailsTable = $('<table></table>'),
				$attributesTable = $('<table></table>'),
				$specificationsTable = $('<table></table>'),
				$relatedProductsTable = $('<table></table>');

			// Product details
			$detailsTable
				.append($('<tr></tr>').append($('<td>ID</td>')).append($('<td></td>').text(product.product_id)))
				.append($('<tr></tr>').append($('<td>URL Path</td>')).append($('<td></td>').text(product.product_url_path)))
				.append($('<tr></tr>').append($('<td>Short Name</td>')).append($('<td></td>').text(product.product_short_name)))
				.append($('<tr></tr>').append($('<td>Long Name</td>')).append($('<td></td>').text(product.product_long_name)))
				.append($('<tr></tr>').append($('<td>Display Sequence</td>')).append($('<td></td>').html(product.display_sequence ? product.display_sequence : '<em><small>Not Set</small></em>')))
				.append($('<tr></tr>').append($('<td>Active?</td>')).append($('<td></td>').text(product.product_active ? 'Yes' : 'No')));

			// Product attributes
			if (attributes && attributes.length > 0) {
				$.each(attributes, function(attributeIndex, attribute) {
					$attributesTable.append($('<tr></tr>')
						.append($('<td></td>').text(attribute.label))
						.append($('<td></td>').text(attribute.value))
					);
				});
			} else {
				$attributesTable.append($('<tr></tr>').append($('<td></td>').attr({ colspan: 2 }).text('No attributes')));
			}

			// Product specifications
			if (specifications && specifications.length > 0) {
				$.each(specifications, function(specificationIndex, specification) {
					$specificationsTable.append($('<tr></tr>')
						.append($('<td></td>').text(specification.label))
						.append($('<td></td>').text(specification.value))
					);
				});
			} else {
				$specificationsTable.append($('<tr></tr>').append($('<td></td>').attr({ colspan: 2 }).text('No specifications')));
			}

			// Related products
			if (relatedProducts && relatedProducts.length > 0) {
				$.each(relatedProducts, function(relatedProductIndex, relatedProduct) {
					$relatedProductsTable.append($('<tr></tr>')
						.append($('<td></td>').text(relatedProduct.label))
						.append($('<td></td>').text(relatedProduct.value))
					);
				});
			} else {
				$relatedProductsTable.append($('<tr></tr>').append($('<td></td>').attr({ colspan: 2 }).text('No related products')));
			}

			// Add everything to the UI, replacing the products list
			this.$detailsPanel.empty()
				.append('<h2>Product Detail: ' + product.product_short_name + '</h2>')
				.append($('<div></div>')
					.append($('<h3><a href="#">Product</a></h3>'))
					.append($('<div></div>').append($detailsTable))
					.append($('<h3><a href="#">Attributes</a></h3>'))
					.append($('<div></div>').append($attributesTable))
					.append($('<h3><a href="#">Specifications</a></h3>'))
					.append($('<div></div>').append($specificationsTable))
					.append($('<h3><a href="#">Related Products</a></h3>'))
					.append($('<div></div>').append($relatedProductsTable))
					.accordion({ autoHeight: false }));
		}
	});
}(jQuery));
