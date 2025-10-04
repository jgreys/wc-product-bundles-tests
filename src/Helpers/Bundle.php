<?php
/**
 * WooCommerce Product Bundles Helper - Bundle
 *
 * Helper functions for creating and managing Bundle products in tests.
 * Based on actual WooCommerce Product Bundles plugin structure.
 *
 * @package Greys\WooCommerce\ProductBundles\Tests\Helpers
 * @since   1.0.0
 */

namespace Greys\WooCommerce\ProductBundles\Tests\Helpers;

use WC_Product_Bundle;

/**
 * Product Bundle helper class.
 *
 * @since 1.0.0
 */
class Bundle {

	/**
	 * Create a bundle product.
	 *
	 * Uses actual Product Bundles meta keys:
	 * - _wc_pb_virtual_bundle: Virtual bundle flag
	 * - _wc_pb_layout_style: Layout style
	 * - _wc_pb_group_mode: Cart grouping mode
	 * - _wc_pb_base_price: Base price
	 *
	 * @since 1.0.0
	 * @param array $args Bundle arguments.
	 * @return \WC_Product_Bundle
	 */
	public static function create_bundle( $args = [] ) {
		$defaults = [
			'name'                      => 'Test Bundle',
			'sku'                       => 'BUNDLE-' . time(),
			'regular_price'             => 100,
			'sale_price'                => '',
			'virtual_bundle'            => 'no',
			'layout'                    => 'default',
			'group_mode'                => 'parent',
			'editable_in_cart'          => 'no',
			'aggregate_weight'          => 'no',
			'sold_individually_context' => 'product',
			'add_to_cart_form_location' => 'default',
		];

		$args = wp_parse_args( $args, $defaults );

		// Create the product.
		$bundle = new WC_Product_Bundle();
		$bundle->set_name( $args['name'] );
		$bundle->set_sku( $args['sku'] );
		$bundle->set_regular_price( $args['regular_price'] );

		if ( ! empty( $args['sale_price'] ) ) {
			$bundle->set_sale_price( $args['sale_price'] );
		}

		// Set status.
		$bundle->set_status( 'publish' );

		// Save to get ID.
		$bundle->save();

		// Set Product Bundles specific meta.
		update_post_meta( $bundle->get_id(), '_wc_pb_virtual_bundle', $args['virtual_bundle'] );
		update_post_meta( $bundle->get_id(), '_wc_pb_layout_style', $args['layout'] );
		update_post_meta( $bundle->get_id(), '_wc_pb_group_mode', $args['group_mode'] );
		update_post_meta( $bundle->get_id(), '_wc_pb_edit_in_cart', $args['editable_in_cart'] );
		update_post_meta( $bundle->get_id(), '_wc_pb_aggregate_weight', $args['aggregate_weight'] );
		update_post_meta( $bundle->get_id(), '_wc_pb_sold_individually_context', $args['sold_individually_context'] );
		update_post_meta( $bundle->get_id(), '_wc_pb_add_to_cart_form_location', $args['add_to_cart_form_location'] );

		// Set base prices.
		update_post_meta( $bundle->get_id(), '_wc_pb_base_regular_price', $args['regular_price'] );
		if ( ! empty( $args['sale_price'] ) ) {
			update_post_meta( $bundle->get_id(), '_wc_pb_base_sale_price', $args['sale_price'] );
			update_post_meta( $bundle->get_id(), '_wc_pb_base_price', $args['sale_price'] );
		} else {
			update_post_meta( $bundle->get_id(), '_wc_pb_base_price', $args['regular_price'] );
		}

		// Re-read to get updated data.
		return new WC_Product_Bundle( $bundle->get_id() );
	}

	/**
	 * Add a bundled item to a bundle.
	 *
	 * Creates entry in woocommerce_bundled_items table and sets meta.
	 *
	 * @since 1.0.0
	 * @param int   $bundle_id   Bundle product ID.
	 * @param int   $product_id  Product to add to bundle.
	 * @param array $args        Bundled item arguments.
	 * @return int Bundled item ID.
	 */
	public static function add_bundled_item( $bundle_id, $product_id, $args = [] ) {
		global $wpdb;

		$defaults = [
			'menu_order'                          => 0,
			'quantity_min'                        => 1,
			'quantity_max'                        => '',
			'quantity_default'                    => 1,
			'priced_individually'                 => 'no',
			'shipped_individually'                => 'no',
			'discount'                            => '',
			'optional'                            => 'no',
			'override_title'                      => 'no',
			'title'                               => '',
			'override_description'                => 'no',
			'description'                         => '',
			'single_product_visibility'           => 'visible',
			'cart_visibility'                     => 'visible',
			'order_visibility'                    => 'visible',
			'single_product_price_visibility'     => 'visible',
			'cart_price_visibility'               => 'visible',
			'order_price_visibility'              => 'visible',
		];

		$args = wp_parse_args( $args, $defaults );

		// Insert into bundled_items table.
		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_bundled_items',
			[
				'bundle_id'  => $bundle_id,
				'product_id' => $product_id,
				'menu_order' => $args['menu_order'],
			],
			[ '%d', '%d', '%d' ]
		);

		$bundled_item_id = $wpdb->insert_id;

		if ( ! $bundled_item_id ) {
			return 0;
		}

		// Add meta data to bundled_itemmeta table.
		$meta_keys = [
			'quantity_min',
			'quantity_max',
			'quantity_default',
			'priced_individually',
			'shipped_individually',
			'discount',
			'optional',
			'override_title',
			'title',
			'override_description',
			'description',
			'single_product_visibility',
			'cart_visibility',
			'order_visibility',
			'single_product_price_visibility',
			'cart_price_visibility',
			'order_price_visibility',
		];

		foreach ( $meta_keys as $key ) {
			if ( isset( $args[ $key ] ) && '' !== $args[ $key ] ) {
				$wpdb->insert(
					$wpdb->prefix . 'woocommerce_bundled_itemmeta',
					[
						'bundled_item_id' => $bundled_item_id,
						'meta_key'        => $key,
						'meta_value'      => maybe_serialize( $args[ $key ] ),
					],
					[ '%d', '%s', '%s' ]
				);
			}
		}

		return $bundled_item_id;
	}

	/**
	 * Set bundle stock settings.
	 *
	 * Uses actual Product Bundles meta keys:
	 * - _wc_pb_bundle_stock_quantity: Bundle-level stock
	 * - _wc_pb_bundled_items_stock_status: Aggregated stock status
	 * - _wc_pb_bundled_items_stock_sync_status: Sync status
	 *
	 * @since 1.0.0
	 * @param \WC_Product_Bundle $bundle         Bundle product.
	 * @param array              $stock_settings Stock settings.
	 * @return void
	 */
	public static function set_bundle_stock( $bundle, $stock_settings = [] ) {
		$defaults = [
			'manage_stock'         => false,
			'stock_quantity'       => '',
			'stock_status'         => 'instock',
			'bundled_stock_status' => 'instock',
			'stock_sync_status'    => 'synced',
		];

		$settings = wp_parse_args( $stock_settings, $defaults );

		// Standard WooCommerce stock settings.
		$bundle->set_manage_stock( $settings['manage_stock'] );
		$bundle->set_stock_status( $settings['stock_status'] );

		if ( $settings['manage_stock'] && ! empty( $settings['stock_quantity'] ) ) {
			$bundle->set_stock_quantity( $settings['stock_quantity'] );
		}

		$bundle->save();

		// Product Bundles specific stock meta.
		if ( ! empty( $settings['stock_quantity'] ) ) {
			update_post_meta( $bundle->get_id(), '_wc_pb_bundle_stock_quantity', $settings['stock_quantity'] );
		}

		update_post_meta( $bundle->get_id(), '_wc_pb_bundled_items_stock_status', $settings['bundled_stock_status'] );
		update_post_meta( $bundle->get_id(), '_wc_pb_bundled_items_stock_sync_status', $settings['stock_sync_status'] );
	}

	/**
	 * Set min/max bundle size (for optional items).
	 *
	 * Requires Min/Max Items extension but we can set the meta for testing.
	 *
	 * @since 1.0.0
	 * @param \WC_Product_Bundle $bundle  Bundle product.
	 * @param int                $min_size Minimum bundle size.
	 * @param int                $max_size Maximum bundle size.
	 * @return void
	 */
	public static function set_bundle_size_limits( $bundle, $min_size = '', $max_size = '' ) {
		if ( '' !== $min_size ) {
			update_post_meta( $bundle->get_id(), '_wc_pb_min_bundle_size', absint( $min_size ) );
		}

		if ( '' !== $max_size ) {
			update_post_meta( $bundle->get_id(), '_wc_pb_max_bundle_size', absint( $max_size ) );
		}
	}

	/**
	 * Delete a bundled item.
	 *
	 * Removes from both bundled_items and bundled_itemmeta tables.
	 *
	 * @since 1.0.0
	 * @param int $bundled_item_id Bundled item ID.
	 * @return bool Success.
	 */
	public static function delete_bundled_item( $bundled_item_id ) {
		global $wpdb;

		// Delete meta.
		$wpdb->delete(
			$wpdb->prefix . 'woocommerce_bundled_itemmeta',
			[ 'bundled_item_id' => $bundled_item_id ],
			[ '%d' ]
		);

		// Delete item.
		$result = $wpdb->delete(
			$wpdb->prefix . 'woocommerce_bundled_items',
			[ 'bundled_item_id' => $bundled_item_id ],
			[ '%d' ]
		);

		return false !== $result;
	}

	/**
	 * Delete all bundled items for a bundle.
	 *
	 * @since 1.0.0
	 * @param int $bundle_id Bundle product ID.
	 * @return void
	 */
	public static function delete_all_bundled_items( $bundle_id ) {
		global $wpdb;

		// Get all bundled item IDs.
		$bundled_item_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT bundled_item_id FROM {$wpdb->prefix}woocommerce_bundled_items WHERE bundle_id = %d",
				$bundle_id
			)
		);

		// Delete each item.
		foreach ( $bundled_item_ids as $id ) {
			self::delete_bundled_item( $id );
		}
	}

	/**
	 * Get bundled item meta value.
	 *
	 * @since 1.0.0
	 * @param int    $bundled_item_id Bundled item ID.
	 * @param string $meta_key        Meta key.
	 * @param bool   $single          Return single value.
	 * @return mixed Meta value.
	 */
	public static function get_bundled_item_meta( $bundled_item_id, $meta_key, $single = true ) {
		global $wpdb;

		if ( $single ) {
			$meta_value = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->prefix}woocommerce_bundled_itemmeta
					WHERE bundled_item_id = %d AND meta_key = %s LIMIT 1",
					$bundled_item_id,
					$meta_key
				)
			);

			return maybe_unserialize( $meta_value );
		}

		$meta_values = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->prefix}woocommerce_bundled_itemmeta
				WHERE bundled_item_id = %d AND meta_key = %s",
				$bundled_item_id,
				$meta_key
			)
		);

		return array_map( 'maybe_unserialize', $meta_values );
	}

	/**
	 * Update bundled item meta value.
	 *
	 * @since 1.0.0
	 * @param int    $bundled_item_id Bundled item ID.
	 * @param string $meta_key        Meta key.
	 * @param mixed  $meta_value      Meta value.
	 * @return bool Success.
	 */
	public static function update_bundled_item_meta( $bundled_item_id, $meta_key, $meta_value ) {
		global $wpdb;

		// Check if meta exists.
		$meta_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->prefix}woocommerce_bundled_itemmeta
				WHERE bundled_item_id = %d AND meta_key = %s LIMIT 1",
				$bundled_item_id,
				$meta_key
			)
		);

		if ( $meta_id ) {
			// Update existing.
			$result = $wpdb->update(
				$wpdb->prefix . 'woocommerce_bundled_itemmeta',
				[ 'meta_value' => maybe_serialize( $meta_value ) ],
				[ 'meta_id' => $meta_id ],
				[ '%s' ],
				[ '%d' ]
			);
		} else {
			// Insert new.
			$result = $wpdb->insert(
				$wpdb->prefix . 'woocommerce_bundled_itemmeta',
				[
					'bundled_item_id' => $bundled_item_id,
					'meta_key'        => $meta_key,
					'meta_value'      => maybe_serialize( $meta_value ),
				],
				[ '%d', '%s', '%s' ]
			);
		}

		return false !== $result;
	}

	/**
	 * Set variation filters for a bundled variable product.
	 *
	 * @since 1.0.0
	 * @param int   $bundled_item_id     Bundled item ID.
	 * @param array $allowed_variations  Allowed variation IDs.
	 * @param array $default_attributes  Default variation attributes.
	 * @return void
	 */
	public static function set_variation_filters( $bundled_item_id, $allowed_variations = [], $default_attributes = [] ) {
		if ( ! empty( $allowed_variations ) ) {
			self::update_bundled_item_meta( $bundled_item_id, 'override_variations', 'yes' );
			self::update_bundled_item_meta( $bundled_item_id, 'allowed_variations', $allowed_variations );
		}

		if ( ! empty( $default_attributes ) ) {
			self::update_bundled_item_meta( $bundled_item_id, 'override_default_variation_attributes', 'yes' );
			self::update_bundled_item_meta( $bundled_item_id, 'default_variation_attributes', $default_attributes );
		}
	}
}
