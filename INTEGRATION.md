# WooCommerce Product Bundles - Plugin Analysis

**Plugin Version:** 7.1.2
**Analysis Date:** 2025-10-04
**Purpose:** Document actual plugin implementation for framework accuracy

---

## Table of Contents

1. [Overview](#overview)
2. [Database Structure](#database-structure)
3. [Meta Keys Reference](#meta-keys-reference)
4. [Framework Verification](#framework-verification)

---

## Overview

WooCommerce Product Bundles allows selling multiple products together as a bundle. Unlike other WooCommerce extensions, Product Bundles uses **custom database tables** instead of just post meta.

### Key Concepts

- **Bundle Product** = WC_Product_Bundle (extends WC_Product)
- **Bundled Item** = Individual product within a bundle
- **Custom Tables** = Separate database tables for bundled items and their meta

---

## Database Structure

### Custom Tables

Product Bundles uses two custom database tables:

#### `wp_woocommerce_bundled_items`
Stores bundled item associations:

| Column | Type | Description |
|--------|------|-------------|
| `bundled_item_id` | BIGINT | Primary key |
| `product_id` | BIGINT | Bundled product ID |
| `bundle_id` | BIGINT | Parent bundle product ID |
| `menu_order` | INT | Display order |

#### `wp_woocommerce_bundled_itemmeta`
Stores bundled item meta data:

| Column | Type | Description |
|--------|------|-------------|
| `meta_id` | BIGINT | Primary key |
| `bundled_item_id` | BIGINT | Foreign key to bundled_items |
| `meta_key` | VARCHAR | Meta key |
| `meta_value` | LONGTEXT | Meta value (serialized if array) |

---

## Meta Keys Reference

### Bundle Product Meta (Post Meta)

Prefix: `_wc_pb_`

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_wc_pb_virtual_bundle` | string | Bundle is virtual | `'yes'`, `'no'` |
| `_wc_pb_layout_style` | string | Layout style | `'default'`, `'tabular'` |
| `_wc_pb_group_mode` | string | Grouping mode | `'parent'`, `'noindent'` |
| `_wc_pb_base_price` | string | Base price | `'10.00'` |
| `_wc_pb_base_regular_price` | string | Base regular price | `'15.00'` |
| `_wc_pb_base_sale_price` | string | Base sale price | `'10.00'` |
| `_wc_pb_bundle_stock_quantity` | int | Bundle stock level | `100` |
| `_wc_pb_bundled_items_stock_status` | string | Aggregated stock | `'instock'`, `'outofstock'` |
| `_wc_pb_edit_in_cart` | string | Allow cart editing | `'yes'`, `'no'` |
| `_wc_pb_aggregate_weight` | string | Aggregate weight | `'yes'`, `'no'` |

### Bundled Item Meta (Custom Table)

Stored in `wp_woocommerce_bundled_itemmeta`:

#### Quantity Settings
| Meta Key | Type | Description |
|----------|------|-------------|
| `quantity_min` | int | Minimum quantity |
| `quantity_max` | int | Maximum quantity |
| `quantity_default` | int | Default quantity |

#### Pricing & Shipping
| Meta Key | Type | Description |
|----------|------|-------------|
| `priced_individually` | string | Price separately (`'yes'`/`'no'`) |
| `shipped_individually` | string | Ship separately (`'yes'`/`'no'`) |
| `discount` | float | Discount percentage |

#### Display Settings
| Meta Key | Type | Description |
|----------|------|-------------|
| `optional` | string | Item is optional (`'yes'`/`'no'`) |
| `override_title` | string | Override title (`'yes'`/`'no'`) |
| `title` | string | Custom title |
| `override_description` | string | Override description (`'yes'`/`'no'`) |
| `description` | string | Custom description |

#### Visibility Settings
| Meta Key | Type | Description |
|----------|------|-------------|
| `single_product_visibility` | string | Show on product page |
| `cart_visibility` | string | Show in cart |
| `order_visibility` | string | Show in orders |

#### Variation Filtering
| Meta Key | Type | Description |
|----------|------|-------------|
| `allowed_variations` | array | Allowed variation IDs |
| `default_variation_attributes` | array | Default attribute selections |

---

## Framework Verification

### ✅ Verified Correct

All framework components verified against actual plugin:

#### Helper Methods
- ✅ `create_bundle()` - Uses correct bundle meta keys with `_wc_pb_` prefix
- ✅ `add_bundled_item()` - Inserts into custom `bundled_items` table
- ✅ `update_bundled_item_meta()` - Inserts into `bundled_itemmeta` table
- ✅ `get_bundled_item_meta()` - Queries custom table correctly
- ✅ `delete_bundled_item()` - Removes from both custom tables

#### Database Operations
- ✅ Uses `$wpdb->insert()` for custom tables
- ✅ Uses `$wpdb->delete()` for cleanup
- ✅ Properly serializes array values with `maybe_serialize()`
- ✅ Table name format: `$wpdb->prefix . 'woocommerce_bundled_items'`

#### Assertions
- ✅ `assertBundleHasItems()` - Uses `$bundle->get_bundled_items()`
- ✅ `assertBundledItemQuantities()` - Queries custom table meta
- ✅ `assertBundledItemPricedIndividually()` - Checks itemmeta table
- ✅ `assertBundledItemOptional()` - Checks `optional` meta in custom table

### Important Notes

1. **Custom Tables** - Not standard post meta, requires direct `$wpdb` queries
2. **Table Creation** - Tables created by `WC_PB_Install::create_tables()`
3. **Meta Format** - Array values must use `maybe_serialize()` before storage
4. **Cleanup** - Must delete from both `bundled_items` AND `bundled_itemmeta` tables
5. **Testing** - Base test case checks table existence in `maybe_create_tables()`

---

## Core Classes

- **WC_Product_Bundle** - Bundle product class
- **WC_Bundled_Item** - Wrapper for bundled products
- **WC_Bundled_Item_Data** - Low-level database object
- **WC_PB_DB** - Database utility class
- **WC_PB_Install** - Handles table creation

---

## Source Files Analyzed

- `includes/class-wc-product-bundle.php` - Bundle product class
- `includes/class-wc-bundled-item.php` - Bundled item wrapper
- `includes/class-wc-bundled-item-data.php` - Database operations
- `includes/class-wc-pb-db.php` - Database utility methods
- `includes/class-wc-pb-install.php` - Table creation

---

**Status:** ✅ Framework verified accurate against plugin v7.1.2
