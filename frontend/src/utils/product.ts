import type { CatalogProduct } from '@/api/catalog'
import type { Product } from '@/types'

export function mapCatalogProduct(cp: CatalogProduct): Product {
  return {
    id: String(cp.id),
    slug: cp.slug,
    title: cp.name,
    brand: cp.brand ? { id: cp.brand.slug, name: cp.brand.name } : { id: '', name: '' },
    category: cp.category ? { id: cp.category.slug, name: cp.category.name, slug: cp.category.slug } : { id: '', name: '', slug: '' },
    sku: '',
    description: cp.short_description ?? '',
    specifications: [],
    price: cp.price,
    compareAtPrice: cp.compare_at_price,
    discountPercent: cp.compare_at_price && cp.compare_at_price > cp.price
      ? Math.round((1 - cp.price / cp.compare_at_price) * 100)
      : null,
    rating: cp.rating_avg,
    reviewCount: cp.rating_count,
    images: cp.cover_image ? [{ id: '1', url: cp.cover_image, alt: cp.name }] : [],
    variants: (cp.variants ?? []).map((v) => ({
      id: String(v.id),
      sku: v.sku ?? '',
      attributes: [],
      price: v.price ?? cp.price,
      compareAtPrice: cp.compare_at_price,
      stockQuantity: v.in_stock ? 1 : 0,
      isInStock: v.in_stock,
      imageId: null
    })),
    stockQuantity: cp.variants?.some((v) => v.in_stock) ? 1 : 0,
    isInStock: cp.in_stock,
    isNew: false,
    isBestSeller: false,
    isFeatured: cp.is_featured,
    colors: [],
    sizes: []
  }
}