# Taharat Agro — Phase 2 Catalog and Inventory

Phase 2 adds categories, simple and variant products, secure product images, inventory movements, computed-stock packages, catalog permissions, and public catalog previews. It intentionally contains no cart, checkout, order, payment, courier, coupon, review, SMS, or reporting workflow.

## Upgrade

```bash
php spark migrate
php spark db:seed CatalogPermissionSeeder
```

For a fresh installation, `php spark db:seed DatabaseSeeder` includes the catalog permissions. Optional category demo data is development-only:

```bash
php spark db:seed CatalogSeeder
```

The public pages are `/products`, `/products/{slug}`, `/category/{slug}`, `/packages`, and `/packages/{slug}`. Uploaded images are MIME checked, randomly named, limited to 5 MB, and stored below `public/uploads/{module}/YYYY/MM`.

## Phase 3 integration notes

- Simple stock is authoritative in `products.stock_quantity`; variant stock is authoritative in `product_variants.stock_quantity`.
- Package stock is never duplicated. `PackageService::getPackageAvailableQuantity()` computes buildable quantity from components.
- Future order placement must use `InventoryService` inside the order transaction and record `sale`/`return` movements.
- Public catalog data never selects or renders purchase costs.
