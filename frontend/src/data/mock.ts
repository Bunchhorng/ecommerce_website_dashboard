import type {
  Address,
  AdminCoupon,
  AppNotification,
  Brand,
  Category,
  CategorySales,
  Coupon,
  Customer,
  Order,
  OrderStatusCount,
  Product,
  ProductImage,
  ProductVariant,
  Review,
  ShippingMethod,
  Testimonial,
  TimerangePoint
} from '@/types'

const pic = (seed: string, w = 600): string => `https://picsum.photos/seed/${seed}/${w}/${w}`

const img = (seed: string): ProductImage[] => [
  { id: `${seed}-1`, url: pic(seed, 800), alt: `${seed} view 1` },
  { id: `${seed}-2`, url: pic(`${seed}-2`, 800), alt: `${seed} view 2` },
  { id: `${seed}-3`, url: pic(`${seed}-3`, 800), alt: `${seed} view 3` },
  { id: `${seed}-4`, url: pic(`${seed}-4`, 800), alt: `${seed} view 4` }
]

export const BRANDS: Brand[] = [
  { id: 'br1', name: 'TechNova', slug: 'technova' },
  { id: 'br2', name: 'CloudStep', slug: 'cloudstep' },
  { id: 'br3', name: 'Urban Threads', slug: 'urban-threads' },
  { id: 'br4', name: 'Elara Beauty', slug: 'elara-beauty' },
  { id: 'br5', name: 'Voltex', slug: 'voltex' },
  { id: 'br6', name: 'Northpeek', slug: 'northpeek' },
  { id: 'br7', name: 'Hearth & Home', slug: 'hearth-home' }
]

export const CATEGORIES: Category[] = [
  { id: 'cat-electronics', name: 'Electronics', slug: 'electronics', icon: 'Smartphone' },
  { id: 'cat-fashion', name: 'Fashion', slug: 'fashion', icon: 'Shirt' },
  { id: 'cat-shoes', name: 'Shoes', slug: 'shoes', icon: 'Footprints' },
  { id: 'cat-beauty', name: 'Beauty', slug: 'beauty', icon: 'Flower2' },
  { id: 'cat-accessories', name: 'Accessories', slug: 'accessories', icon: 'Watch' },
  { id: 'cat-home', name: 'Home', slug: 'home', icon: 'Home' }
]

export const NAV_CATEGORIES: Category[] = [
  ...CATEGORIES,
  { id: 'cat-sale', name: 'Sale', slug: 'sale', icon: 'Tag' }
]

const cat = (slug: string): Category => CATEGORIES.find((c) => c.slug === slug) ?? CATEGORIES[0]
const brandOf = (slug: string): Brand => BRANDS.find((b) => b.slug === slug) ?? BRANDS[0]

function variant(
  seed: string,
  attributes: ProductVariant['attributes'],
  price: number,
  stock: number,
  compareAtPrice: number | null = null
): ProductVariant {
  return {
    id: `v-${seed}`,
    sku: `SKU-${seed.toUpperCase()}`,
    attributes,
    price,
    compareAtPrice,
    stockQuantity: stock,
    isInStock: stock > 0,
    imageId: `${seed}-1`
  }
}

function discount(product: { price: number; compareAtPrice: number | null; discountPercent?: number | null }): number {
  if (product.compareAtPrice && product.compareAtPrice > product.price) {
    return Math.round(((product.compareAtPrice - product.price) / product.compareAtPrice) * 100)
  }
  return product.discountPercent ?? 0
}

export const PRODUCTS: Product[] = [
  {
    id: 'p1',
    slug: 'aurora-wireless-headphones',
    title: 'Aurora Wireless Headphones',
    brand: brandOf('technova'),
    category: cat('electronics'),
    sku: 'AUR-HP-001',
    description:
      'Immerse yourself in studio-grade sound with the Aurora Wireless Headphones. Active Noise Cancellation, 40-hour battery life, and plush memory foam ear cushions make these perfect for long listening sessions, travel, and deep focus.',
    specifications: [
      { label: 'Driver', value: '40mm dynamic' },
      { label: 'Battery Life', value: '40 hours (ANC on)' },
      { label: 'Bluetooth', value: '5.3' },
      { label: 'ANC', value: 'Active Noise Cancellation' },
      { label: 'Weight', value: '254 g' },
      { label: 'Charging', value: 'USB-C, fast charge' }
    ],
    price: 249.99,
    compareAtPrice: 319.99,
    discountPercent: 22,
    rating: 4.8,
    reviewCount: 1240,
    images: img('aurora'),
    variants: [
      variant('aurora-black', [{ name: 'Color', value: 'Black' }], 249.99, 42, 319.99),
      variant('aurora-white', [{ name: 'Color', value: 'White' }], 249.99, 18, 319.99),
      variant('aurora-silver', [{ name: 'Color', value: 'Silver' }], 259.99, 7, 329.99)
    ],
    stockQuantity: 67,
    isInStock: true,
    isNew: false,
    isBestSeller: true,
    isFeatured: true,
    colors: ['#111827', '#FFFFFF', '#D1D5DB'],
    sizes: []
  },
  {
    id: 'p2',
    slug: 'pulse-smartwatch-pro',
    title: 'Pulse Smartwatch Pro',
    brand: brandOf('technova'),
    category: cat('electronics'),
    sku: 'PLS-SW-002',
    description:
      'Track every heartbeat with the Pulse Smartwatch Pro. Advanced health sensors, GPS, built-in workout modes, and a bright always-on AMOLED display. Water resistant up to 5 ATM with a 14-day battery.',
    specifications: [
      { label: 'Display', value: '1.7" AMOLED, always-on' },
      { label: 'Battery', value: '14 days typical use' },
      { label: 'Health', value: 'HR, SpO2, sleep, stress' },
      { label: 'Water Rating', value: '5 ATM' },
      { label: 'GPS', value: 'Built-in dual band' }
    ],
    price: 329.0,
    compareAtPrice: 379.0,
    discountPercent: 13,
    rating: 4.6,
    reviewCount: 856,
    images: img('pulse'),
    variants: [
      variant('pulse-midnight', [{ name: 'Color', value: 'Midnight' }], 329, 35, 379),
      variant('pulse-sand', [{ name: 'Color', value: 'Sand' }], 329, 21, 379),
      variant('pulse-rose', [{ name: 'Color', value: 'Rose' }], 349, 9, 399)
    ],
    stockQuantity: 65,
    isInStock: true,
    isNew: true,
    isBestSeller: false,
    isFeatured: true,
    colors: ['#0F172A', '#E7D8C9', '#F43F5E'],
    sizes: []
  },
  {
    id: 'p3',
    slug: 'vertex-4k-action-camera',
    title: 'Vertex 4K Action Camera',
    brand: brandOf('voltex'),
    category: cat('electronics'),
    sku: 'VTX-AC-003',
    description:
      'Capture every adventure in stunning 4K/60fps with the Vertex Action Camera. HyperSmooth stabilization, 170° wide-angle lens, waterproof shell to 10m, and a rotating touch screen for vlogs.',
    specifications: [
      { label: 'Video', value: '4K 60fps / 1080p 240fps' },
      { label: 'Stabilization', value: 'HyperSmooth 5.0' },
      { label: 'Waterproof', value: '10m without case' },
      { label: 'Lens', value: '170° f/2.5' },
      { label: 'Storage', value: 'Up to 1TB microSD' }
    ],
    price: 399.99,
    compareAtPrice: null,
    discountPercent: null,
    rating: 4.5,
    reviewCount: 412,
    images: img('vertex'),
    variants: [variant('vertex-base', [], 399.99, 24)],
    stockQuantity: 24,
    isInStock: true,
    isNew: true,
    isBestSeller: false,
    isFeatured: false,
    colors: [],
    sizes: []
  },
  {
    id: 'p4',
    slug: 'aerolite-running-shoes',
    title: 'AeroLite Running Shoes',
    brand: brandOf('cloudstep'),
    category: cat('shoes'),
    sku: 'AERO-RS-004',
    description:
      'Featherlight and springy, the AeroLite running shoes feature a responsive foam midsole and breathable engineered mesh upper. Designed for daily training, tempo runs, and everything in between.',
    specifications: [
      { label: 'Weight', value: '235 g (US 9)' },
      { label: 'Drop', value: '8 mm' },
      { label: 'Midsole', value: 'CloudFoam+ EVA' },
      { label: 'Upper', value: 'Engineered mesh' },
      { label: 'Outsole', value: 'High-abrasion rubber' }
    ],
    price: 139.99,
    compareAtPrice: 169.99,
    discountPercent: 18,
    rating: 4.7,
    reviewCount: 2105,
    images: img('aerolite'),
    variants: [
      variant('aerolite-blk-42', [{ name: 'Color', value: 'Black' }, { name: 'Size', value: 'US 9' }], 139.99, 12, 169.99),
      variant('aerolite-blk-43', [{ name: 'Color', value: 'Black' }, { name: 'Size', value: 'US 10' }], 139.99, 8, 169.99),
      variant('aerolite-blk-44', [{ name: 'Color', value: 'Black' }, { name: 'Size', value: 'US 11' }], 139.99, 4, 169.99),
      variant('aerolite-wh-42', [{ name: 'Color', value: 'White' }, { name: 'Size', value: 'US 9' }], 139.99, 15, 169.99),
      variant('aerolite-wh-43', [{ name: 'Color', value: 'White' }, { name: 'Size', value: 'US 10' }], 139.99, 11, 169.99),
      variant('aerolite-wh-44', [{ name: 'Color', value: 'White' }, { name: 'Size', value: 'US 11' }], 139.99, 0, 169.99)
    ],
    stockQuantity: 50,
    isInStock: true,
    isNew: false,
    isBestSeller: true,
    isFeatured: true,
    colors: ['#111827', '#FFFFFF'],
    sizes: ['US 9', 'US 10', 'US 11']
  },
  {
    id: 'p5',
    slug: 'stride-court-sneakers',
    title: 'Stride Court Sneakers',
    brand: brandOf('cloudstep'),
    category: cat('shoes'),
    sku: 'STR-SN-005',
    description:
      'A modern take on the classic court silhouette. Premium leather upper, cushioned insole, and a vulcanized sole for everyday comfort and effortless street style.',
    specifications: [
      { label: 'Upper', value: 'Premium full-grain leather' },
      { label: 'Sole', value: 'Vulcanized rubber' },
      { label: 'Lining', value: 'Textile, breathable' },
      { label: 'Fit', value: 'True to size' }
    ],
    price: 89.99,
    compareAtPrice: null,
    discountPercent: null,
    rating: 4.3,
    reviewCount: 648,
    images: img('stride'),
    variants: [
      variant('stride-w-9', [{ name: 'Color', value: 'White' }, { name: 'Size', value: 'US 9' }], 89.99, 18),
      variant('stride-w-10', [{ name: 'Color', value: 'White' }, { name: 'Size', value: 'US 10' }], 89.99, 14),
      variant('stride-g-9', [{ name: 'Color', value: 'Green' }, { name: 'Size', value: 'US 9' }], 89.99, 6),
      variant('stride-g-10', [{ name: 'Color', value: 'Green' }, { name: 'Size', value: 'US 10' }], 89.99, 2)
    ],
    stockQuantity: 40,
    isInStock: true,
    isNew: false,
    isBestSeller: false,
    isFeatured: false,
    colors: ['#FFFFFF', '#166534'],
    sizes: ['US 9', 'US 10']
  },
  {
    id: 'p6',
    slug: 'meridian-slim-fit-blazer',
    title: 'Meridian Slim-Fit Blazer',
    brand: brandOf('urban-threads'),
    category: cat('fashion'),
    sku: 'MER-BLZ-006',
    description:
      'Elevate your wardrobe with the Meridian slim-fit blazer. Cut from a breathable wool-linen blend, it drapes cleanly and works from the office to after-hours.',
    specifications: [
      { label: 'Fit', value: 'Slim fit' },
      { label: 'Fabric', value: '55% wool, 45% linen' },
      { label: 'Lining', value: 'Fully lined, cupro' },
      { label: 'Closure', value: 'Two-button, self-covered' }
    ],
    price: 189.99,
    compareAtPrice: 239.99,
    discountPercent: 21,
    rating: 4.6,
    reviewCount: 327,
    images: img('meridian'),
    variants: [
      variant('meridian-navy-s', [{ name: 'Color', value: 'Navy' }, { name: 'Size', value: 'S' }], 189.99, 10, 239.99),
      variant('meridian-navy-m', [{ name: 'Color', value: 'Navy' }, { name: 'Size', value: 'M' }], 189.99, 16, 239.99),
      variant('meridian-navy-l', [{ name: 'Color', value: 'Navy' }, { name: 'Size', value: 'L' }], 189.99, 8, 239.99),
      variant('meridian-grey-s', [{ name: 'Color', value: 'Charcoal' }, { name: 'Size', value: 'S' }], 189.99, 5, 239.99),
      variant('meridian-grey-m', [{ name: 'Color', value: 'Charcoal' }, { name: 'Size', value: 'M' }], 189.99, 12, 239.99),
      variant('meridian-grey-l', [{ name: 'Color', value: 'Charcoal' }, { name: 'Size', value: 'L' }], 189.99, 3, 239.99)
    ],
    stockQuantity: 54,
    isInStock: true,
    isNew: false,
    isBestSeller: true,
    isFeatured: true,
    colors: ['#1E3A8A', '#374151'],
    sizes: ['S', 'M', 'L']
  },
  {
    id: 'p7',
    slug: 'nova-linen-shirt',
    title: 'Nova Relaxed Linen Shirt',
    brand: brandOf('urban-threads'),
    category: cat('fashion'),
    sku: 'NOV-SH-007',
    description:
      'An easy, breezy staple. The Nova relaxed linen shirt is garment-washed for softness and styled to be worn tucked or loose. A year-round essential in warmer climates.',
    specifications: [
      { label: 'Fit', value: 'Relaxed' },
      { label: 'Fabric', value: '100% European flax linen' },
      { label: 'Care', value: 'Machine washable' },
      { label: 'Pockets', value: 'Single chest pocket' }
    ],
    price: 59.99,
    compareAtPrice: 75.0,
    discountPercent: 20,
    rating: 4.4,
    reviewCount: 189,
    images: img('nova'),
    variants: [
      variant('nova-beige-s', [{ name: 'Color', value: 'Beige' }, { name: 'Size', value: 'S' }], 59.99, 22, 75),
      variant('nova-beige-m', [{ name: 'Color', value: 'Beige' }, { name: 'Size', value: 'M' }], 59.99, 30, 75),
      variant('nova-beige-l', [{ name: 'Color', value: 'Beige' }, { name: 'Size', value: 'L' }], 59.99, 12, 75),
      variant('nova-white-m', [{ name: 'Color', value: 'White' }, { name: 'Size', value: 'M' }], 59.99, 20, 75),
      variant('nova-white-l', [{ name: 'Color', value: 'White' }, { name: 'Size', value: 'L' }], 59.99, 9, 75)
    ],
    stockQuantity: 93,
    isInStock: true,
    isNew: true,
    isBestSeller: false,
    isFeatured: false,
    colors: ['#EAD9C8', '#FFFFFF'],
    sizes: ['S', 'M', 'L']
  },
  {
    id: 'p8',
    slug: 'lumiere-hydra-glow-serum',
    title: 'Lumière Hydra Glow Serum',
    brand: brandOf('elara-beauty'),
    category: cat('beauty'),
    sku: 'LUM-SR-008',
    description:
      'A weightless, deeply hydrating serum featuring hyaluronic acid and vitamin C. Wake up to visibly brighter, plumper skin. Dermatologist-tested and fragrance-free.',
    specifications: [
      { label: 'Key Ingredients', value: 'Hyaluronic acid, vitamin C, ceramides' },
      { label: 'Size', value: '30 ml' },
      { label: 'Skin Type', value: 'All, including sensitive' },
      { label: 'Cruelty Free', value: 'Yes, vegan' }
    ],
    price: 42.0,
    compareAtPrice: null,
    discountPercent: null,
    rating: 4.9,
    reviewCount: 1523,
    images: img('lumiere'),
    variants: [variant('lumiere-30ml', [{ name: 'Size', value: '30 ml' }], 42, 120)],
    stockQuantity: 120,
    isInStock: true,
    isNew: false,
    isBestSeller: true,
    isFeatured: true,
    colors: [],
    sizes: ['30 ml']
  },
  {
    id: 'p9',
    slug: 'velvet-matte-lipstick-ruby',
    title: 'Velvet Matte Lipstick, Ruby',
    brand: brandOf('elara-beauty'),
    category: cat('beauty'),
    sku: 'VEL-LP-009',
    description:
      'A rich, velvety matte lipstick in a classic ruby red. Long-wearing comfort formula enriched with jojoba oil that glides on and stays through coffee and conversation.',
    specifications: [
      { label: 'Finish', value: 'Velvet matte' },
      { label: 'Wear Time', value: 'Up to 8 hours' },
      { label: 'Shade', value: 'Ruby' },
      { label: 'Cruelty Free', value: 'Yes' }
    ],
    price: 24.0,
    compareAtPrice: 30.0,
    discountPercent: 20,
    rating: 4.5,
    reviewCount: 874,
    images: img('ruby'),
    variants: [variant('ruby-01', [], 24, 200, 30)],
    stockQuantity: 200,
    isInStock: true,
    isNew: true,
    isBestSeller: false,
    isFeatured: false,
    colors: [],
    sizes: []
  },
  {
    id: 'p10',
    slug: 'orbit-everyday-backpack',
    title: 'Orbit Everyday Backpack',
    brand: brandOf('northpeek'),
    category: cat('accessories'),
    sku: 'ORB-BP-010',
    description:
      'A waterproof, thoughtfully organized carry for work and weekends. Padded 16" laptop sleeve, external water-bottle pocket, and quick-access magnetic closure.',
    specifications: [
      { label: 'Capacity', value: '24 L' },
      { label: 'Fabric', value: 'Recycled water-repellent nylon' },
      { label: 'Laptop', value: '16" padded sleeve' },
      { label: 'Pockets', value: '6 internal, 4 external' }
    ],
    price: 119.99,
    compareAtPrice: 149.99,
    discountPercent: 20,
    rating: 4.7,
    reviewCount: 955,
    images: img('orbit'),
    variants: [
      variant('orbit-black', [{ name: 'Color', value: 'Black' }], 119.99, 44, 149.99),
      variant('orbit-ocean', [{ name: 'Color', value: 'Ocean' }], 119.99, 15, 149.99)
    ],
    stockQuantity: 59,
    isInStock: true,
    isNew: false,
    isBestSeller: true,
    isFeatured: false,
    colors: ['#111827', '#0C4A6E'],
    sizes: []
  },
  {
    id: 'p11',
    slug: 'cascade-leather-tote',
    title: 'Cascade Leather Tote',
    brand: brandOf('northpeek'),
    category: cat('accessories'),
    sku: 'CAS-TT-011',
    description:
      'Handcrafted full-grain leather tote with a structured silhouette, interior zip pocket, and key leash. Ages beautifully with use and carries everything, quietly.',
    specifications: [
      { label: 'Material', value: 'Full-grain vegetable-tanned leather' },
      { label: 'Dimensions', value: '38 × 30 × 13 cm' },
      { label: 'Straps', value: 'Dual handles, carry on shoulder' },
      { label: 'Pockets', value: '1 zip, 2 slip' }
    ],
    price: 219.0,
    compareAtPrice: null,
    discountPercent: null,
    rating: 4.8,
    reviewCount: 233,
    images: img('cascade'),
    variants: [variant('cascade-tan', [{ name: 'Color', value: 'Tan' }], 219, 14)],
    stockQuantity: 14,
    isInStock: true,
    isNew: false,
    isBestSeller: false,
    isFeatured: false,
    colors: ['#B45309'],
    sizes: []
  },
  {
    id: 'p12',
    slug: 'serene-wooden-desk-lamp',
    title: 'Serene Wooden Desk Lamp',
    brand: brandOf('hearth-home'),
    category: cat('home'),
    sku: 'SER-DL-012',
    description:
      'Warm, dimmable LED light on a solid oak base. A sculptural addition to any desk or nightstand, with a minimalist touch dimmer and 3 color temperatures.',
    specifications: [
      { label: 'Material', value: 'Solid oak, aluminum' },
      { label: 'Light', value: 'LED 9W, 350–2900K adapter' },
      { label: 'Dimming', value: 'Touch, stepless' },
      { label: 'Cable', value: '1.8 m, USB-C power' }
    ],
    price: 79.99,
    compareAtPrice: 99.99,
    discountPercent: 20,
    rating: 4.6,
    reviewCount: 512,
    images: img('serene'),
    variants: [variant('serene-oak', [{ name: 'Color', value: 'Oak' }], 79.99, 38, 99.99)],
    stockQuantity: 38,
    isInStock: false,
    isNew: false,
    isBestSeller: false,
    isFeatured: false,
    colors: ['#92400E'],
    sizes: []
  }
].map((p) => ({ ...p, discountPercent: discount(p) }))

export const FEATURED_PRODUCTS: Product[] = PRODUCTS.filter((p) => p.isFeatured)
export const BEST_SELLERS: Product[] = PRODUCTS.filter((p) => p.isBestSeller)
export const NEW_ARRIVALS: Product[] = PRODUCTS.filter((p) => p.isNew)

export const ADDRESSES: Address[] = [
  {
    id: 'ad1',
    label: 'Home',
    fullName: 'Alex Morgan',
    line1: '482 Meridian Avenue, Apt 3B',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94110',
    country: 'United States',
    phone: '+1 (415) 555-0142',
    isDefault: true
  },
  {
    id: 'ad2',
    label: 'Office',
    fullName: 'Alex Morgan',
    line1: '900 Bryant Street, Suite 220',
    city: 'San Francisco',
    state: 'CA',
    postalCode: '94103',
    country: 'United States',
    phone: '+1 (415) 555-0198',
    isDefault: false
  },
  {
    id: 'ad3',
    label: 'Parents',
    fullName: 'Alex Morgan',
    line1: '12 Oak Hollow Lane',
    city: 'Austin',
    state: 'TX',
    postalCode: '73301',
    country: 'United States',
    phone: '+1 (512) 555-0177',
    isDefault: false
  }
]

export const SHIPPING_METHODS: ShippingMethod[] = [
  { id: 'std', name: 'Standard Shipping', description: 'Free over $100 · 5–7 business days', etaDays: 6, price: 0 },
  { id: 'exp', name: 'Express Shipping', description: '2–3 business days, tracked', etaDays: 3, price: 9.99 },
  { id: 'same', name: 'Same Day Delivery', description: 'Order before 1 PM · local cities only', etaDays: 1, price: 19.99 }
]

export const COUPONS: Coupon[] = [
  { id: 'cp1', code: 'WELCOME10', type: 'percentage', value: 10, minOrderAmount: 50, description: '10% off your first order over $50' },
  { id: 'cp2', code: 'SAVE20', type: 'fixed', value: 20, minOrderAmount: 100, description: '$20 off orders over $100' },
  { id: 'cp3', code: 'FREESHIP', type: 'fixed', value: 10, minOrderAmount: 75, description: '$10 off shipping on orders over $75' }
]

export const REVIEWS: Review[] = [
  { id: 'r1', productId: 'p1', author: 'Marcus Lee', rating: 5, title: 'Sound quality is unreal', body: 'Bass is punchy without overwhelming mids. ANC blocks out my coworker’s keyboard clacking completely.', date: '2026-07-18', verified: true, status: 'approved' },
  { id: 'r2', productId: 'p1', author: 'Priya Shah', rating: 4, title: 'Great for flights', body: 'Very comfortable over 6+ hours. Battery easily survived a transatlantic round trip. Wish the case were smaller.', date: '2026-06-02', verified: true, status: 'approved' },
  { id: 'r3', productId: 'p1', author: 'Tom Barnes', rating: 5, title: 'Best purchase this year', body: 'Upgraded from an older pair and the difference in call quality alone was worth it.', date: '2026-05-20', verified: true, status: 'approved' },
  { id: 'r4', productId: 'p4', author: 'Elena Rodriguez', rating: 5, title: 'Feels like running on clouds', body: 'Immediate comfort out of the box. My daily 5K times improved just from the bounce.', date: '2026-07-25', verified: true, status: 'approved' },
  { id: 'r5', productId: 'p6', author: 'Dan Okafor', rating: 4, title: 'Sharp, modern fit', body: 'Tailored without being tight. The navy pairs with everything. Slight wrinkle on the back after travel.', date: '2026-04-11', verified: true, status: 'approved' },
  { id: 'r6', productId: 'p8', author: 'Sara Kim', rating: 5, title: 'Skin is glowing', body: 'Two weeks in and my dry patches are gone. No breakouts, absorbs instantly.', date: '2026-07-08', verified: true, status: 'approved' },
  { id: 'r7', productId: 'p2', author: 'Jake Miller', rating: 4, title: 'Solid tracking, comfy band', body: 'HR matches my chest strap within a beat or two. Band is soft. GPS locks in about 20 seconds.', date: '2026-06-29', verified: true, status: 'approved' },
  { id: 'r8', productId: 'p4', author: 'Nadia Hassan', rating: 5, title: 'Perfect daily trainer', body: 'Light, breathable, and the white colorway stays surprisingly clean.', date: '2026-05-15', verified: true, status: 'approved' },
  { id: 'r9', productId: 'p12', author: 'George Whitfield', rating: 4, title: 'Beautiful warm light', body: 'The oak looks premium on my desk. Dimmer is very smooth. Slightly heavier than expected. That’s a plus.', date: '2026-03-30', verified: true, status: 'approved' },
  { id: 'r10', productId: 'p1', author: 'Anonymous', rating: 3, title: 'Good but heavy', body: 'Sound is great but they get heavy on my ears after a few hours. Returned for a lighter pair.', date: '2026-08-01', verified: false, status: 'pending' }
]

export const PENDING_REVIEWS: Review[] = REVIEWS.filter((r) => r.status === 'pending')

export const REVIEWS_BY_PRODUCT: Record<string, Review[]> = REVIEWS.reduce<Record<string, Review[]>>(
  (acc, r) => {
    acc[r.productId] = acc[r.productId] ?? []
    if (r.status === 'approved') acc[r.productId].push(r)
    return acc
  },
  {}
)

export const TESTIMONIALS: Testimonial[] = [
  { id: 't1', name: 'Olivia Bennett', role: 'Verified Buyer', avatar: pic('avatar-o', 100), quote: 'The checkout was buttery smooth and my order arrived two days early. This is now my go-to store.', rating: 5 },
  { id: 't2', name: 'James Carter', role: 'Frequent Customer', avatar: pic('avatar-j', 100), quote: 'Easily the best online shopping experience I have had. Real-time tracking made the wait enjoyable.', rating: 5 },
  { id: 't3', name: 'Mei-Lin Chen', role: 'Verified Buyer', avatar: pic('avatar-m', 100), quote: 'Quality products, honest prices, and the wishlist saved me when my size restocked.', rating: 4 }
]

export const NOTIFICATIONS: AppNotification[] = [
  { id: 'n1', type: 'order', title: 'Order Shipped 🎉', message: 'Your order #SV-2026-1042 has shipped and is on its way.', date: '2026-08-30T09:12:00', read: false },
  { id: 'n2', type: 'promo', title: '30% off accessories', message: 'Countdown sale on bags, watches and more. Ends Sunday.', date: '2026-08-29T18:00:00', read: false },
  { id: 'n3', type: 'system', title: 'Password security', message: 'Enable two-factor authentication to protect your account.', date: '2026-08-27T12:30:00', read: true },
  { id: 'n4', type: 'order', title: 'Payment received', message: 'We received your payment for order #SV-2026-1038.', date: '2026-08-24T16:45:00', read: true }
]

/* ---------- Orders ---------- */
const orderItem = (p: Product, qty: number) => ({
  id: `oi-${p.id}`,
  productId: p.id,
  title: p.title,
  brand: p.brand.name,
  image: p.images[0].url,
  unitPrice: p.price,
  quantity: qty,
  variant: null
})

export const ORDERS: Order[] = [
  {
    id: 'o1',
    number: 'SV-2026-1042',
    items: [orderItem(PRODUCTS[0], 1), orderItem(PRODUCTS[7], 2)],
    subtotal: 249.99 + 84,
    discount: 0,
    shipping: 0,
    tax: 33.4,
    total: 249.99 + 84 + 33.4,
    status: 'Shipped',
    placedAt: '2026-08-28T10:24:00',
    estimatedDelivery: '2026-09-04T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-08-28T10:24:00' },
      { status: 'Confirmed', at: '2026-08-28T10:31:00' },
      { status: 'Processing', at: '2026-08-28T14:05:00' },
      { status: 'Shipped', at: '2026-08-30T08:47:00' }
    ],
    shippingAddress: ADDRESSES[0],
    paymentMethod: 'card'
  },
  {
    id: 'o2',
    number: 'SV-2026-0997',
    items: [orderItem(PRODUCTS[3], 1)],
    subtotal: 139.99,
    discount: 25.2,
    shipping: 9.99,
    tax: 11.48,
    total: 139.99 - 25.2 + 9.99 + 11.48,
    status: 'Delivered',
    placedAt: '2026-08-12T15:02:00',
    estimatedDelivery: '2026-08-18T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-08-12T15:02:00' },
      { status: 'Confirmed', at: '2026-08-12T15:10:00' },
      { status: 'Processing', at: '2026-08-13T09:00:00' },
      { status: 'Shipped', at: '2026-08-14T11:30:00' },
      { status: 'Delivered', at: '2026-08-18T13:22:00' }
    ],
    shippingAddress: ADDRESSES[1],
    paymentMethod: 'cod'
  },
  {
    id: 'o3',
    number: 'SV-2026-0812',
    items: [orderItem(PRODUCTS[8], 3), orderItem(PRODUCTS[5], 1)],
    subtotal: 72 + 189.99,
    discount: 14.4,
    shipping: 0,
    tax: 24.76,
    total: 72 + 189.99 - 14.4 + 24.76,
    status: 'Processing',
    placedAt: '2026-08-05T19:41:00',
    estimatedDelivery: '2026-08-12T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-08-05T19:41:00' },
      { status: 'Confirmed', at: '2026-08-05T19:55:00' },
      { status: 'Processing', at: '2026-08-06T10:18:00' }
    ],
    shippingAddress: ADDRESSES[2],
    paymentMethod: 'bank'
  },
  {
    id: 'o4',
    number: 'SV-2026-0721',
    items: [orderItem(PRODUCTS[9], 1)],
    subtotal: 119.99,
    discount: 24,
    shipping: 0,
    tax: 9.6,
    total: 119.99 - 24 + 9.6,
    status: 'Delivered',
    placedAt: '2026-07-22T11:09:00',
    estimatedDelivery: '2026-07-29T17:00:00',
    trackingEvents: [
      { status: 'Pending', at: '2026-07-22T11:09:00' },
      { status: 'Confirmed', at: '2026-07-22T11:20:00' },
      { status: 'Processing', at: '2026-07-23T08:15:00' },
      { status: 'Shipped', at: '2026-07-24T13:40:00' },
      { status: 'Delivered', at: '2026-07-29T10:05:00' }
    ],
    shippingAddress: ADDRESSES[0],
    paymentMethod: 'gateway'
  }
]

export function getOrderById(id: string): Order | undefined {
  return ORDERS.find((o) => o.id === id || o.number === id)
}

export const CURRENT_USER = {
  id: 'u1',
  name: 'Alex Morgan',
  email: 'alex.morgan@example.com',
  phone: '+1 (415) 555-0142',
  role: 'customer',
  joinedAt: '2024-03-11'
}

/* ---------- Admin ---------- */
export const REVENUE_TREND: TimerangePoint[] = [
  { label: 'Jan', revenue: 41200, orders: 312 },
  { label: 'Feb', revenue: 38800, orders: 289 },
  { label: 'Mar', revenue: 45700, orders: 341 },
  { label: 'Apr', revenue: 52100, orders: 372 },
  { label: 'May', revenue: 48900, orders: 358 },
  { label: 'Jun', revenue: 60300, orders: 415 },
  { label: 'Jul', revenue: 58700, orders: 402 },
  { label: 'Aug', revenue: 68400, orders: 468 },
  { label: 'Sep', revenue: 72250, orders: 499 },
  { label: 'Oct', revenue: 69800, orders: 476 },
  { label: 'Nov', revenue: 94300, orders: 621 },
  { label: 'Dec', revenue: 112400, orders: 703 }
]

export const ORDER_STATUS_DISTRIBUTION: OrderStatusCount[] = [
  { status: 'Pending', count: 23 },
  { status: 'Confirmed', count: 41 },
  { status: 'Processing', count: 57 },
  { status: 'Shipped', count: 88 },
  { status: 'Delivered', count: 264 },
  { status: 'Cancelled', count: 12 }
]

export const SALES_BY_CATEGORY: CategorySales[] = [
  { category: 'Electronics', sales: 38600 },
  { category: 'Fashion', sales: 27400 },
  { category: 'Shoes', sales: 21900 },
  { category: 'Beauty', sales: 15300 },
  { category: 'Accessories', sales: 11800 },
  { category: 'Home', sales: 8700 }
]

export const ADMIN_METRICS = {
  totalRevenue: 742850.5,
  revenueDelta: 12.4,
  totalOrders: 1523,
  ordersDelta: 8.1,
  totalCustomers: 9842,
  customersDelta: 5.6,
  lowStockAlert: 14
}

export const LOW_STOCK_PRODUCTS: Product[] = PRODUCTS.filter((p) => p.stockQuantity <= 20)

export const CUSTOMERS: Customer[] = Array.from({ length: 24 }, (_, i) => ({
  id: `cust-${i + 1}`,
  name: ['Olivia Bennett', 'Marcus Lee', 'Priya Shah', 'Jake Miller', 'Elena Rodriguez', 'Dan Okafor', 'Sara Kim', 'Tom Barnes', 'Mei-Lin Chen', 'Nadia Hassan'][i % 10],
  email: `customer${i + 1}@example.com`,
  phone: `+1 (555) 01${String(i).padStart(2, '0')}`,
  orders: (i * 3) % 17 + 1,
  totalSpent: Math.round((i * 137.5 + 99) * 100) / 100,
  status: i % 5 === 3 ? 'inactive' : 'active',
  joinedAt: new Date(2025, (i % 12), (i % 28) + 1).toISOString(),
  avatar: pic(`cust-${i}`, 100)
}))

export const ADMIN_COUPONS: AdminCoupon[] = [
  { id: 'ac1', code: 'WELCOME10', type: 'percentage', value: 10, minOrderAmount: 50, usageLimit: 5000, usedCount: 3821, expiresAt: '2026-12-31', status: 'active' },
  { id: 'ac2', code: 'SAVE20', type: 'fixed', value: 20, minOrderAmount: 100, usageLimit: 2000, usedCount: 1404, expiresAt: '2026-11-30', status: 'active' },
  { id: 'ac3', code: 'SUMMER25', type: 'percentage', value: 25, minOrderAmount: 150, usageLimit: 1000, usedCount: 1000, expiresAt: '2026-08-31', status: 'expired' },
  { id: 'ac4', code: 'FREESHIP', type: 'fixed', value: 10, minOrderAmount: 75, usageLimit: 3000, usedCount: 2211, expiresAt: '2026-12-31', status: 'active' },
  { id: 'ac5', code: 'VIP15', type: 'percentage', value: 15, minOrderAmount: 200, usageLimit: 500, usedCount: 87, expiresAt: '2027-03-31', status: 'draft' }
]

/* ---------- Helpers ---------- */
export function getProductById(id: string): Product | undefined {
  return PRODUCTS.find((p) => p.id === id)
}

export function getProductBySlug(slug: string): Product | undefined {
  return PRODUCTS.find((p) => p.slug === slug)
}

export function getReviewsForProduct(productId: string): Review[] {
  return REVIEWS_BY_PRODUCT[productId] ?? []
}