<?php
/**
 * Base Test Case for WooCommerce Product Bundles Tests
 *
 * @package Greys\WooCommerce\ProductBundles\Tests
 * @since   1.0.0
 */

namespace Greys\WooCommerce\ProductBundles\Tests;

use WC_Unit_Test_Case;

/**
 * WCPB Unit Test Case class.
 *
 * Extends WC_Unit_Test_Case with Product Bundles-specific functionality.
 *
 * @since 1.0.0
 */
class UnitTestCase extends WC_Unit_Test_Case {

	use Traits\Assertions;

	/**
	 * Bundle IDs created during tests.
	 *
	 * @var array
	 */
	protected $bundle_ids = [];

	/**
	 * Bundled item IDs created during tests.
	 *
	 * @var array
	 */
	protected $bundled_item_ids = [];

	/**
	 * Set up test case.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure Product Bundles tables exist.
		$this->maybe_create_tables();
	}

	/**
	 * Tear down test case.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// Clean up bundled items.
		foreach ( $this->bundled_item_ids as $id ) {
			Helpers\Bundle::delete_bundled_item( $id );
		}

		// Clean up bundle products.
		foreach ( $this->bundle_ids as $id ) {
			Helpers\Bundle::delete_all_bundled_items( $id );
			wp_delete_post( $id, true );
		}

		parent::tearDown();
	}

	/**
	 * Helper to create bundle product.
	 *
	 * @param array $args Bundle arguments.
	 * @return \WC_Product_Bundle
	 */
	protected function create_bundle( $args = [] ) {
		$bundle               = Helpers\Bundle::create_bundle( $args );
		$this->bundle_ids[] = $bundle->get_id();
		return $bundle;
	}

	/**
	 * Helper to add bundled item.
	 *
	 * @param int   $bundle_id  Bundle product ID.
	 * @param int   $product_id Product to add.
	 * @param array $args       Bundled item arguments.
	 * @return int Bundled item ID.
	 */
	protected function add_bundled_item( $bundle_id, $product_id, $args = [] ) {
		$bundled_item_id            = Helpers\Bundle::add_bundled_item( $bundle_id, $product_id, $args );
		$this->bundled_item_ids[] = $bundled_item_id;
		return $bundled_item_id;
	}

	/**
	 * Ensure Product Bundles database tables exist.
	 *
	 * @return void
	 */
	protected function maybe_create_tables() {
		global $wpdb;

		// Check if tables exist.
		$bundled_items_table = $wpdb->prefix . 'woocommerce_bundled_items';
		$table_exists        = $wpdb->get_var( "SHOW TABLES LIKE '{$bundled_items_table}'" );

		if ( ! $table_exists ) {
			// Tables don't exist, try to create them.
			if ( class_exists( 'WC_PB_Install' ) ) {
				\WC_PB_Install::create_tables();
			}
		}
	}
}
