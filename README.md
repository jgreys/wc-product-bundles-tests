# WooCommerce Product Bundles PHPUnit Framework

A comprehensive PHPUnit testing framework for WooCommerce Product Bundles. This package provides helper classes, custom assertions, and utilities to make testing Product Bundles functionality easier and more reliable.

## Features

- **Base Test Case**: Specialized `UnitTestCase` with automatic cleanup
- **Helper Classes**: Create bundles and bundled items with realistic data
- **Custom Assertions**: Domain-specific assertions for bundle testing
- **Database Support**: Works with Product Bundles custom tables
- **Realistic Test Data**: Examples using production-like data patterns

## Installation

```bash
composer require --dev greys/woocommerce-product-bundles-phpunit-framework
```

## Requirements

- PHP 7.4 or higher
- PHPUnit 9.x
- WooCommerce
- WooCommerce Product Bundles
- `greys/woocommerce-phpunit-framework`

## Quick Start

### 1. Extend the Base Test Case

```php
<?php
namespace YourPlugin\Tests;

use Greys\WooCommerce\ProductBundles\Tests\UnitTestCase;

class YourBundleTest extends UnitTestCase {

    public function test_create_bundle_with_products() {
        // Arrange.
        $product1 = $this->factory->product->create( array( 'price' => 10 ) );
        $product2 = $this->factory->product->create( array( 'price' => 20 ) );

        // Act.
        $bundle          = $this->create_bundle( array( 'name' => 'Test Bundle' ) );
        $bundled_item_id = $this->add_bundled_item( $bundle->get_id(), $product1 );

        // Assert.
        $this->assertIsBundle( $bundle );
        $this->assertBundleHasItems( 1, $bundle );
        $this->assertProductInBundle( $product1, $bundle );
    }
}
```

### 2. Use Helper Classes

```php
<?php
use Greys\WooCommerce\ProductBundles\Tests\Helpers\Bundle;

// Create bundle product
$bundle = Bundle::create_bundle( array(
    'name'           => 'Premium Bundle',
    'regular_price'  => 100,
    'virtual_bundle' => 'yes',
    'layout'         => 'tabular',
) );

// Add bundled items
$bundled_item_id = Bundle::add_bundled_item(
    $bundle->get_id(),
    $product_id,
    array(
        'quantity_min'        => 1,
        'quantity_max'        => 5,
        'quantity_default'    => 2,
        'priced_individually' => 'yes',
        'discount'            => 10,
        'optional'            => 'no',
    )
);

// Set stock
Bundle::set_bundle_stock( $bundle, array(
    'manage_stock'    => true,
    'stock_quantity'  => 10,
    'stock_status'    => 'instock',
) );
```

### 3. Use Custom Assertions

```php
<?php
// Assert bundle properties
$this->assertIsBundle( $bundle );
$this->assertBundleHasItems( 3, $bundle );
$this->assertBundleVirtual( $bundle );
$this->assertBundleLayout( 'tabular', $bundle );
$this->assertBundleGroupMode( 'parent', $bundle );

// Assert bundled item properties
$this->assertProductInBundle( $product_id, $bundle );
$this->assertBundledItemQuantities( $bundled_item_id, 1, 5, 2 );
$this->assertBundledItemOptional( $bundled_item_id );
$this->assertBundledItemPricedIndividually( $bundled_item_id );
$this->assertBundledItemDiscount( 10.0, $bundled_item_id );

// Assert stock
$this->assertBundleStockQuantity( 10, $bundle );
$this->assertBundledItemsStockStatus( 'instock', $bundle );
```

## Helper Methods

### Bundle

#### Bundle Creation
```php
// Create bundle product
create_bundle( $args = array() )

// Arguments:
'name'                      => 'Test Bundle',
'sku'                       => 'BUNDLE-123',
'regular_price'             => 100,
'sale_price'                => 80,
'virtual_bundle'            => 'yes'|'no',
'layout'                    => 'default'|'tabular',
'group_mode'                => 'parent'|'noindent',
'editable_in_cart'          => 'yes'|'no',
'aggregate_weight'          => 'yes'|'no',
```

#### Bundled Items
```php
// Add bundled item
add_bundled_item( $bundle_id, $product_id, $args = array() )

// Arguments:
'menu_order'                    => 0,
'quantity_min'                  => 1,
'quantity_max'                  => 10,
'quantity_default'              => 1,
'priced_individually'           => 'yes'|'no',
'shipped_individually'          => 'yes'|'no',
'discount'                      => 10.0,      // Percentage
'optional'                      => 'yes'|'no',
'override_title'                => 'yes'|'no',
'title'                         => 'Custom Title',
'override_description'          => 'yes'|'no',
'description'                   => 'Description',
'single_product_visibility'     => 'visible'|'hidden',
'cart_visibility'               => 'visible'|'hidden',
'order_visibility'              => 'visible'|'hidden',
```

#### Stock Management
```php
// Set bundle stock
set_bundle_stock( $bundle, $stock_settings = array() )

// Arguments:
'manage_stock'         => true,
'stock_quantity'       => 10,
'stock_status'         => 'instock',
'bundled_stock_status' => 'instock',
'stock_sync_status'    => 'synced',
```

#### Size Limits
```php
// Set min/max bundle size (for optional items)
set_bundle_size_limits( $bundle, $min_size = 2, $max_size = 5 )
```

#### Variation Filters
```php
// Filter variations for bundled variable product
set_variation_filters( $bundled_item_id, $allowed_variations, $default_attributes )
```

#### Cleanup
```php
// Delete bundled item
delete_bundled_item( $bundled_item_id )

// Delete all bundled items for a bundle
delete_all_bundled_items( $bundle_id )
```

#### Meta Operations
```php
// Get bundled item meta
get_bundled_item_meta( $bundled_item_id, $meta_key, $single = true )

// Update bundled item meta
update_bundled_item_meta( $bundled_item_id, $meta_key, $meta_value )
```

## Available Assertions

### Bundle Assertions

| Assertion | Description |
|-----------|-------------|
| `assertIsBundle( $product )` | Assert product is a bundle |
| `assertBundleHasItems( $count, $bundle )` | Assert bundle has specific number of items |
| `assertProductInBundle( $product_id, $bundle )` | Assert product is in bundle |
| `assertBundleVirtual( $bundle )` | Assert bundle is virtual |
| `assertBundleLayout( $expected, $bundle )` | Assert bundle layout style |
| `assertBundleGroupMode( $expected, $bundle )` | Assert bundle group mode |
| `assertBundleEditableInCart( $bundle )` | Assert bundle is editable in cart |
| `assertBundleStockQuantity( $expected, $bundle )` | Assert bundle stock quantity |
| `assertBundledItemsStockStatus( $expected, $bundle )` | Assert bundled items stock status |
| `assertBundleBasePrice( $expected, $bundle )` | Assert bundle base price |
| `assertBundleSizeLimits( $min, $max, $bundle )` | Assert min/max bundle size |

### Bundled Item Assertions

| Assertion | Description |
|-----------|-------------|
| `assertBundledItemQuantities( $id, $min, $max, $default )` | Assert item quantities |
| `assertBundledItemOptional( $bundled_item_id )` | Assert item is optional |
| `assertBundledItemPricedIndividually( $bundled_item_id )` | Assert item priced individually |
| `assertBundledItemDiscount( $discount, $bundled_item_id )` | Assert item discount percentage |
| `assertBundledItemTitle( $title, $bundled_item_id )` | Assert custom title |
| `assertBundledItemVisibility( $context, $expected, $bundled_item_id )` | Assert visibility in context |

## Complete Test Example

```php
<?php
/**
 * Tests for Product Bundle functionality.
 *
 * @package YourPlugin\Tests
 */

namespace YourPlugin\Tests;

use Greys\WooCommerce\ProductBundles\Tests\UnitTestCase;
use Greys\WooCommerce\ProductBundles\Tests\Helpers\Bundle;

/**
 * Product Bundle Test class.
 */
class ProductBundleTest extends UnitTestCase {

	/**
	 * Test creating bundle with multiple products.
	 *
	 * @return void
	 */
	public function test_create_bundle_with_multiple_products() {
		// Arrange.
		$product1 = $this->factory->product->create( array(
			'name'  => 'Product 1',
			'price' => 10,
		) );

		$product2 = $this->factory->product->create( array(
			'name'  => 'Product 2',
			'price' => 20,
		) );

		$product3 = $this->factory->product->create( array(
			'name'  => 'Product 3',
			'price' => 30,
		) );

		// Act.
		$bundle = $this->create_bundle( array(
			'name'          => 'Complete Bundle',
			'regular_price' => 60,
		) );

		$item1 = $this->add_bundled_item( $bundle->get_id(), $product1 );
		$item2 = $this->add_bundled_item( $bundle->get_id(), $product2 );
		$item3 = $this->add_bundled_item( $bundle->get_id(), $product3 );

		// Re-read bundle to get updated data.
		$bundle = new \WC_Product_Bundle( $bundle->get_id() );

		// Assert.
		$this->assertIsBundle( $bundle );
		$this->assertBundleHasItems( 3, $bundle );
		$this->assertProductInBundle( $product1, $bundle );
		$this->assertProductInBundle( $product2, $bundle );
		$this->assertProductInBundle( $product3, $bundle );
	}

	/**
	 * Test bundled item with custom quantities.
	 *
	 * @return void
	 */
	public function test_bundled_item_custom_quantities() {
		// Arrange.
		$product = $this->factory->product->create( array( 'price' => 25 ) );
		$bundle  = $this->create_bundle();

		// Act.
		$bundled_item_id = $this->add_bundled_item(
			$bundle->get_id(),
			$product,
			array(
				'quantity_min'     => 2,
				'quantity_max'     => 10,
				'quantity_default' => 5,
			)
		);

		// Assert.
		$this->assertBundledItemQuantities( $bundled_item_id, 2, 10, 5 );
	}

	/**
	 * Test optional bundled item.
	 *
	 * @return void
	 */
	public function test_optional_bundled_item() {
		// Arrange.
		$product = $this->factory->product->create();
		$bundle  = $this->create_bundle();

		// Act.
		$bundled_item_id = $this->add_bundled_item(
			$bundle->get_id(),
			$product,
			array( 'optional' => 'yes' )
		);

		// Assert.
		$this->assertBundledItemOptional( $bundled_item_id );
	}

	/**
	 * Test bundled item priced individually with discount.
	 *
	 * @return void
	 */
	public function test_bundled_item_priced_individually_with_discount() {
		// Arrange.
		$product = $this->factory->product->create( array( 'price' => 50 ) );
		$bundle  = $this->create_bundle( array( 'regular_price' => 0 ) );

		// Act.
		$bundled_item_id = $this->add_bundled_item(
			$bundle->get_id(),
			$product,
			array(
				'priced_individually' => 'yes',
				'discount'            => 20,
			)
		);

		// Assert.
		$this->assertBundledItemPricedIndividually( $bundled_item_id );
		$this->assertBundledItemDiscount( 20.0, $bundled_item_id );
	}

	/**
	 * Test virtual bundle.
	 *
	 * @return void
	 */
	public function test_virtual_bundle() {
		// Arrange & Act.
		$bundle = $this->create_bundle( array(
			'virtual_bundle' => 'yes',
		) );

		// Assert.
		$this->assertBundleVirtual( $bundle );
	}

	/**
	 * Test bundle stock management.
	 *
	 * @return void
	 */
	public function test_bundle_stock_management() {
		// Arrange.
		$bundle = $this->create_bundle();

		// Act.
		Bundle::set_bundle_stock( $bundle, array(
			'manage_stock'    => true,
			'stock_quantity'  => 25,
			'stock_status'    => 'instock',
		) );

		// Re-read bundle.
		$bundle = new \WC_Product_Bundle( $bundle->get_id() );

		// Assert.
		$this->assertBundleStockQuantity( 25, $bundle );
		$this->assertTrue( $bundle->managing_stock() );
	}

	/**
	 * Test bundle with custom title override.
	 *
	 * @return void
	 */
	public function test_bundled_item_custom_title() {
		// Arrange.
		$product = $this->factory->product->create();
		$bundle  = $this->create_bundle();

		// Act.
		$bundled_item_id = $this->add_bundled_item(
			$bundle->get_id(),
			$product,
			array(
				'override_title' => 'yes',
				'title'          => 'Custom Product Title',
			)
		);

		// Assert.
		$this->assertBundledItemTitle( 'Custom Product Title', $bundled_item_id );
	}

	/**
	 * Test bundled item visibility settings.
	 *
	 * @return void
	 */
	public function test_bundled_item_visibility() {
		// Arrange.
		$product = $this->factory->product->create();
		$bundle  = $this->create_bundle();

		// Act.
		$bundled_item_id = $this->add_bundled_item(
			$bundle->get_id(),
			$product,
			array(
				'single_product_visibility' => 'visible',
				'cart_visibility'           => 'hidden',
				'order_visibility'          => 'visible',
			)
		);

		// Assert.
		$this->assertBundledItemVisibility( 'single_product', 'visible', $bundled_item_id );
		$this->assertBundledItemVisibility( 'cart', 'hidden', $bundled_item_id );
		$this->assertBundledItemVisibility( 'order', 'visible', $bundled_item_id );
	}
}
```

## Important Notes

### ⚠️ Database Tables

Product Bundles uses **custom database tables**:
- `wp_woocommerce_bundled_items`
- `wp_woocommerce_bundled_itemmeta`

The framework automatically ensures these tables exist during test setup.

### ⚠️ Cleanup

The base test case automatically cleans up:
- All bundled items created with `add_bundled_item()`
- All bundle products created with `create_bundle()`

Manual cleanup is NOT required in most cases.

### ⚠️ Stock Sync

After adding/modifying bundled items, you may need to re-read the bundle to get synced data:

```php
$bundle = new \WC_Product_Bundle( $bundle->get_id() );
```

## Realistic Test Data

Use production-like data for better maintainability:

```php
// Bundle SKUs
'BUNDLE-STARTER'
'BUNDLE-PREMIUM'
'BUNDLE-COMPLETE'

// Product combinations
$laptop  = $this->factory->product->create( array( 'sku' => 'LAPTOP-001' ) );
$mouse   = $this->factory->product->create( array( 'sku' => 'MOUSE-001' ) );
$keyboard = $this->factory->product->create( array( 'sku' => 'KEYBOARD-001' ) );
```

## Contributing

Contributions are welcome! Please:
- Fork the repository
- Create a feature branch
- Submit a pull request with tests

## License

MIT License - See LICENSE file for details.
