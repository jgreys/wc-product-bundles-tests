<?php
/**
 * Product Bundle Assertions Trait
 *
 * Provides custom assertions for Product Bundles testing based on actual plugin implementation.
 *
 * @package Greys\WooCommerce\ProductBundles\Tests\Traits
 * @since   1.0.0
 */

namespace Greys\WooCommerce\ProductBundles\Tests\Traits;

use WC_Product;
use WC_Product_Bundle;
use Greys\WooCommerce\ProductBundles\Tests\Helpers\Bundle as BundleHelper;

/**
 * Product Bundle Assertions trait.
 *
 * @since 1.0.0
 */
trait Assertions {

	/**
	 * Assert product is a bundle.
	 *
	 * @since 1.0.0
	 * @param WC_Product $product Product object.
	 * @param string     $message Optional. Message to display on failure.
	 * @return void
	 */
	public function assertIsBundle( $product, $message = '' ) {
		$this->assertInstanceOf(
			WC_Product_Bundle::class,
			$product,
			$message ?: 'Failed asserting product is a bundle.'
		);

		$this->assertSame(
			'bundle',
			$product->get_type(),
			$message ?: 'Failed asserting product type is bundle.'
		);
	}

	/**
	 * Assert bundle has specific number of bundled items.
	 *
	 * @since 1.0.0
	 * @param int                $expected Expected item count.
	 * @param WC_Product_Bundle  $bundle   Bundle product.
	 * @param string             $message  Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleHasItems( $expected, $bundle, $message = '' ) {
		$bundled_items = $bundle->get_bundled_items();
		$this->assertCount(
			$expected,
			$bundled_items,
			$message ?: "Failed asserting bundle has {$expected} items."
		);
	}

	/**
	 * Assert product is in bundle.
	 *
	 * @since 1.0.0
	 * @param int                $product_id Product ID to check.
	 * @param WC_Product_Bundle  $bundle     Bundle product.
	 * @param string             $message    Optional. Message to display on failure.
	 * @return void
	 */
	public function assertProductInBundle( $product_id, $bundle, $message = '' ) {
		$bundled_items = $bundle->get_bundled_items();
		$product_ids   = [];

		foreach ( $bundled_items as $item ) {
			$product_ids[] = $item->get_product_id();
		}

		$this->assertContains(
			$product_id,
			$product_ids,
			$message ?: "Failed asserting product {$product_id} is in bundle."
		);
	}

	/**
	 * Assert bundled item has specific quantities.
	 *
	 * @since 1.0.0
	 * @param int    $bundled_item_id Bundled item ID.
	 * @param int    $min             Expected min quantity.
	 * @param int    $max             Expected max quantity.
	 * @param int    $default         Expected default quantity.
	 * @param string $message         Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundledItemQuantities( $bundled_item_id, $min, $max, $default, $message = '' ) {
		$actual_min     = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'quantity_min' );
		$actual_max     = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'quantity_max' );
		$actual_default = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'quantity_default' );

		$this->assertEquals( $min, $actual_min, $message ?: "Failed asserting bundled item min quantity is {$min}." );
		$this->assertEquals( $max, $actual_max, $message ?: "Failed asserting bundled item max quantity is {$max}." );
		$this->assertEquals( $default, $actual_default, $message ?: "Failed asserting bundled item default quantity is {$default}." );
	}

	/**
	 * Assert bundled item is optional.
	 *
	 * @since 1.0.0
	 * @param int    $bundled_item_id Bundled item ID.
	 * @param string $message         Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundledItemOptional( $bundled_item_id, $message = '' ) {
		$optional = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'optional' );
		$this->assertSame(
			'yes',
			$optional,
			$message ?: 'Failed asserting bundled item is optional.'
		);
	}

	/**
	 * Assert bundled item is priced individually.
	 *
	 * @since 1.0.0
	 * @param int    $bundled_item_id Bundled item ID.
	 * @param string $message         Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundledItemPricedIndividually( $bundled_item_id, $message = '' ) {
		$priced_individually = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'priced_individually' );
		$this->assertSame(
			'yes',
			$priced_individually,
			$message ?: 'Failed asserting bundled item is priced individually.'
		);
	}

	/**
	 * Assert bundled item has discount.
	 *
	 * @since 1.0.0
	 * @param float  $expected_discount Expected discount percentage.
	 * @param int    $bundled_item_id   Bundled item ID.
	 * @param string $message           Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundledItemDiscount( $expected_discount, $bundled_item_id, $message = '' ) {
		$discount = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'discount' );
		$this->assertEquals(
			$expected_discount,
			floatval( $discount ),
			$message ?: "Failed asserting bundled item has {$expected_discount}% discount."
		);
	}

	/**
	 * Assert bundle is virtual.
	 *
	 * @since 1.0.0
	 * @param WC_Product_Bundle $bundle  Bundle product.
	 * @param string            $message Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleVirtual( $bundle, $message = '' ) {
		$virtual = get_post_meta( $bundle->get_id(), '_wc_pb_virtual_bundle', true );
		$this->assertSame(
			'yes',
			$virtual,
			$message ?: 'Failed asserting bundle is virtual.'
		);
	}

	/**
	 * Assert bundle layout style.
	 *
	 * @since 1.0.0
	 * @param string            $expected Expected layout.
	 * @param WC_Product_Bundle $bundle   Bundle product.
	 * @param string            $message  Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleLayout( $expected, $bundle, $message = '' ) {
		$layout = get_post_meta( $bundle->get_id(), '_wc_pb_layout_style', true );
		$this->assertSame(
			$expected,
			$layout,
			$message ?: "Failed asserting bundle layout is {$expected}."
		);
	}

	/**
	 * Assert bundle group mode.
	 *
	 * @since 1.0.0
	 * @param string            $expected Expected group mode.
	 * @param WC_Product_Bundle $bundle   Bundle product.
	 * @param string            $message  Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleGroupMode( $expected, $bundle, $message = '' ) {
		$group_mode = get_post_meta( $bundle->get_id(), '_wc_pb_group_mode', true );
		$this->assertSame(
			$expected,
			$group_mode,
			$message ?: "Failed asserting bundle group mode is {$expected}."
		);
	}

	/**
	 * Assert bundle is editable in cart.
	 *
	 * @since 1.0.0
	 * @param WC_Product_Bundle $bundle  Bundle product.
	 * @param string            $message Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleEditableInCart( $bundle, $message = '' ) {
		$editable = get_post_meta( $bundle->get_id(), '_wc_pb_edit_in_cart', true );
		$this->assertSame(
			'yes',
			$editable,
			$message ?: 'Failed asserting bundle is editable in cart.'
		);
	}

	/**
	 * Assert bundle stock quantity.
	 *
	 * @since 1.0.0
	 * @param int               $expected Expected stock quantity.
	 * @param WC_Product_Bundle $bundle   Bundle product.
	 * @param string            $message  Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleStockQuantity( $expected, $bundle, $message = '' ) {
		$stock = get_post_meta( $bundle->get_id(), '_wc_pb_bundle_stock_quantity', true );
		$this->assertEquals(
			$expected,
			absint( $stock ),
			$message ?: "Failed asserting bundle stock quantity is {$expected}."
		);
	}

	/**
	 * Assert bundled items stock status.
	 *
	 * @since 1.0.0
	 * @param string            $expected Expected stock status.
	 * @param WC_Product_Bundle $bundle   Bundle product.
	 * @param string            $message  Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundledItemsStockStatus( $expected, $bundle, $message = '' ) {
		$stock_status = get_post_meta( $bundle->get_id(), '_wc_pb_bundled_items_stock_status', true );
		$this->assertSame(
			$expected,
			$stock_status,
			$message ?: "Failed asserting bundled items stock status is {$expected}."
		);
	}

	/**
	 * Assert bundle base price.
	 *
	 * @since 1.0.0
	 * @param float             $expected Expected base price.
	 * @param WC_Product_Bundle $bundle   Bundle product.
	 * @param string            $message  Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleBasePrice( $expected, $bundle, $message = '' ) {
		$base_price = get_post_meta( $bundle->get_id(), '_wc_pb_base_price', true );
		$this->assertEquals(
			$expected,
			floatval( $base_price ),
			$message ?: "Failed asserting bundle base price is {$expected}."
		);
	}

	/**
	 * Assert bundled item has title override.
	 *
	 * @since 1.0.0
	 * @param string $expected_title   Expected title.
	 * @param int    $bundled_item_id  Bundled item ID.
	 * @param string $message          Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundledItemTitle( $expected_title, $bundled_item_id, $message = '' ) {
		$override = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'override_title' );
		$title    = BundleHelper::get_bundled_item_meta( $bundled_item_id, 'title' );

		$this->assertSame( 'yes', $override, 'Title override should be enabled.' );
		$this->assertSame(
			$expected_title,
			$title,
			$message ?: "Failed asserting bundled item title is {$expected_title}."
		);
	}

	/**
	 * Assert bundled item visibility.
	 *
	 * @since 1.0.0
	 * @param string $context          Context (single_product|cart|order).
	 * @param string $expected         Expected visibility (visible|hidden).
	 * @param int    $bundled_item_id  Bundled item ID.
	 * @param string $message          Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundledItemVisibility( $context, $expected, $bundled_item_id, $message = '' ) {
		$meta_key   = $context . '_visibility';
		$visibility = BundleHelper::get_bundled_item_meta( $bundled_item_id, $meta_key );

		$this->assertSame(
			$expected,
			$visibility,
			$message ?: "Failed asserting bundled item {$context} visibility is {$expected}."
		);
	}

	/**
	 * Assert bundle has min/max size limits.
	 *
	 * @since 1.0.0
	 * @param int               $min     Expected min size.
	 * @param int               $max     Expected max size.
	 * @param WC_Product_Bundle $bundle  Bundle product.
	 * @param string            $message Optional. Message to display on failure.
	 * @return void
	 */
	public function assertBundleSizeLimits( $min, $max, $bundle, $message = '' ) {
		$min_size = get_post_meta( $bundle->get_id(), '_wc_pb_min_bundle_size', true );
		$max_size = get_post_meta( $bundle->get_id(), '_wc_pb_max_bundle_size', true );

		$this->assertEquals( $min, absint( $min_size ), $message ?: "Failed asserting bundle min size is {$min}." );
		$this->assertEquals( $max, absint( $max_size ), $message ?: "Failed asserting bundle max size is {$max}." );
	}
}
