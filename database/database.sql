-- ========================================================
-- MANON Luxury Abayas Database Schema
-- Database: manon_db
-- Store: MANON | منون للعبايات والأزياء الفاخرة
-- ========================================================

CREATE DATABASE IF NOT EXISTS `manon_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `manon_db`;

-- Drop existing tables if needed (in reverse dependency order)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `colors`;
DROP TABLE IF EXISTS `special_orders`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `discount_codes`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'manager') NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CATEGORIES TABLE
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PRODUCTS TABLE
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `sku` VARCHAR(100) NULL,
  `short_desc` VARCHAR(500) NULL,
  `description` LONGTEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `sale_price` DECIMAL(10,2) NULL,
  `stock_quantity` INT NOT NULL DEFAULT 15,
  `sizes` VARCHAR(150) NOT NULL DEFAULT '52, 54, 56, 58, 60',
  `fabric` VARCHAR(150) NOT NULL DEFAULT 'حرير ملكي كوري فاخر',
  `color` VARCHAR(100) NOT NULL DEFAULT 'أسود فاحم ملكي',
  `main_image` VARCHAR(255) NOT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_new` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `views_count` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`category_id`),
  INDEX (`slug`),
  INDEX (`is_active`),
  INDEX (`is_featured`),
  INDEX (`is_new`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PRODUCT IMAGES TABLE
CREATE TABLE `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`product_id`),
  CONSTRAINT `fk_image_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ORDERS TABLE
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `whatsapp` VARCHAR(50) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `notes` TEXT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `shipping_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_code` VARCHAR(50) NULL,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cod', 'electronic') NOT NULL DEFAULT 'cod',
  `status` ENUM('new', 'confirmed', 'preparing', 'delivered', 'cancelled') NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`order_number`),
  INDEX (`phone`),
  INDEX (`status`),
  INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ORDER ITEMS TABLE
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `selected_size` VARCHAR(50) NULL,
  `selected_color` VARCHAR(100) NULL DEFAULT 'أسود فاحم ملكي',
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `total` DECIMAL(10,2) NOT NULL,
  INDEX (`order_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. SETTINGS TABLE
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. COLORS TABLE (نظام الألوان الدائري)
CREATE TABLE `colors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `hex_code` VARCHAR(10) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. SPECIAL ORDERS TABLE (الطلبات الخاصة والتفصيل)
CREATE TABLE `special_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `preferred_color` VARCHAR(100) NULL,
  `size` VARCHAR(50) NULL,
  `design_description` TEXT NOT NULL,
  `image_path` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `status` ENUM('new', 'contacted', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. REVIEWS TABLE (آراء وتقييمات العملاء)
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_city` VARCHAR(100) NULL,
  `rating` TINYINT(1) NOT NULL DEFAULT 5,
  `review_text` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. DISCOUNT CODES TABLE (عجلة الحظ وقسائم الخصم)
CREATE TABLE `discount_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_percent` INT NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `used_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- INITIAL SEED DATA
-- ========================================================

-- Admin User: admin@manon.com / admin123
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('إدارة منون', 'admin@manon.com', '$2y$10$v7g9FvGqgVfA0z6z4d8f/.g3GgR/tP4G.iC4YqQY3H/T9kU6T.P5.', 'admin');

-- Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'MANON | منون'),
('site_slogan', 'عبايتك فخامة تليق بك'),
('whatsapp_number', '+201273572887'),
('phone_number', '+201273572887'),
('email', 'info@manon-fashion.com'),
('currency', 'ج.م'),
('shipping_fee', '0'),
('address', 'القاهرة - التجمع الخامس، مصر'),
('hero_title', 'MANON'),
('hero_subtitle', 'عبايتك فخامة تليق بك'),
('announcement', 'شحن مجاني على كافة الطلبات هذا الأسبوع | تصميمات استثنائية لأناقة تدوم'),
('wheel_enabled', '1');

-- Colors
INSERT INTO `colors` (`name`, `hex_code`, `is_active`, `sort_order`) VALUES
('أسود فاحم ملكي', '#111111', 1, 1),
('بيج كريمي دافئ', '#D8CBB9', 1, 2),
('بني شوكولاتة فاخر', '#5C4033', 1, 3),
('رمادي دخاني معاصر', '#808080', 1, 4),
('كحلي ليلي داكن', '#0B1B3D', 1, 5),
('أبيض لؤلؤي ناصع', '#FFFFFF', 1, 6);

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `sort_order`, `is_active`) VALUES
(1, 'عبايات سوداء كلاسيكية', 'classic-black', 'مجموعة العبايات السوداء الأيقونية بقصات راقية وأقمشة فاخرة تناسب ذوقك الرفيع', 'assets/images/cat1.jpg', 1, 1),
(2, 'عبايات فاخرة ومطرزة', 'luxury-embroidered', 'تصاميم ملكية بتطريزات يدوية ناعمة وشك دقيق للمناسبات المميزة', 'assets/images/cat2.jpg', 2, 1),
(3, 'عبايات عملية ويومية', 'casual-daily', 'أناقة يومية تجمع بين الراحة والجاذبية العصرية بأقمشة خفيفة وعملية', 'assets/images/cat3.jpg', 3, 1),
(4, 'عبايات سهرة ومناسبات', 'evening-occasions', 'قصات استثنائية ساحرة وخامات غنية تمنحك إطلالة ملكية في كل مناسبة', 'assets/images/cat4.jpg', 4, 1),
(5, 'كولكشن جديد', 'new-collection', 'أحدث ابتكارات منون لموسم 2026 بأرقى تفاصيل الموضة الخليجية والعصرية', 'assets/images/cat5.jpg', 5, 1);

-- Products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `short_desc`, `description`, `price`, `sale_price`, `stock_quantity`, `sizes`, `fabric`, `color`, `main_image`, `is_featured`, `is_new`, `is_active`) VALUES
(1, 1, 'عباية ملكية بقصة كلوش وأكمام مطرزة', 'royal-cloche-abaya', 'MANON-001', 'عباية سوداء كلاسيكية بقصة كلوش فاخرة مع تطريز راقٍ على الأكمام، تمنحك حضوراً آسراً.', 'صُممت هذه العباية الأيقونية من خامة كريب الفرسان الملكية ذات السواد الفاحم، وتتميز بانسيابية راقية وأكمام مزدانة بتطريز خيط حريري أسود ناعم. تأتي مع طرحة متناسقة مجاناً.\r\n\r\n- قماش كريب فاخر ناعم وبارد.\r\n- قصة كلوش انسيابية واسعة.\r\n- طرحة مجانية من الشيفون الليزر الفاخر.', 1450.00, 1250.00, 20, '52, 54, 56, 58, 60', 'كريب فرسان ياباني', 'أسود فاحم ملكي', 'assets/images/abaya1.jpg', 1, 1, 1),
(2, 2, 'عباية منون الفاخرة بلمسات ذهبية راقية', 'manon-signature-gold-abaya', 'MANON-002', 'قطعة فنية استثنائية بتطريز قصب ذهبي خافت وناعم يعكس الفخامة الهادئة.', 'إبداع خالص من بيت أزياء منون، مصنوعة من الحرير الكوري المات مع تدخيلات دانتيل وتطريز يدوي بخيوط الذهب الخافت عند الياقة والأكمام.\r\n\r\n- تصميم بشت عصري.\r\n- أزرار مخفية لسهولة الارتداء.\r\n- تتضمن طرحة طرف دانتيل أنيقة.', 1950.00, 1750.00, 15, '52, 54, 56, 58, 60', 'حرير مات كوري فاخر', 'أسود ملكي مع خيوط ذهبية', 'assets/images/abaya2.jpg', 1, 1, 1),
(3, 1, 'عباية دبل كلوش بياقة قلاب وأكمام بياقة', 'double-cloche-classic', 'MANON-003', 'عباية سوداء راقية وعملية بتصميم دبل كلوش ولمسات أنيقة على الياقة.', 'عباية سوداء كلاسيكية منسوجة بأعلى مقاييس الجودة، تمنحك إطلالة راقية محتشمة وحركة خفيفة طوال اليوم.\r\n\r\n- قماش إنترنت كوري عالي الجودة.\r\n- مقاوم للتجعد وسهل العناية.\r\n- قصة مريحة تليق بكل أوقاتك.', 1150.00, 950.00, 25, '52, 54, 56, 58', 'إنترنت كوري درجة أولى', 'أسود كلاسيكي', 'assets/images/abaya3.jpg', 1, 0, 1),
(4, 3, 'عباية بليزر عصرية للدوام والعمل', 'modern-blazer-daily-abaya', 'MANON-004', 'تصميم بليزر رسمي حديث يمنح المرأة العاملة مظهراً أنيقاً واحترافياً فائق التميز.', 'مزيج فريد بين فخامة العباية الخليجية والقصة الرسمية العصرية، مزودة بجيوب جانبية وياقة حادة وأزرار أنيقة.\r\n\r\n- قماش كريب لينن فاخر.\r\n- مناسبة تماماً للعمل والمقابلات واليوميات.\r\n- قصّة نصف كلوش عملية ومريحة.', 1350.00, NULL, 18, '52, 54, 56, 58, 60', 'كريب صالونا كوري', 'أسود فحمي', 'assets/images/abaya4.jpg', 0, 1, 1),
(5, 4, 'عباية سهرة فخمة بتطريز كريستال وشك يدوي', 'evening-crystal-luxury-abaya', 'MANON-005', 'عباية سهرة ملكية مطرزة بفصوص شواروفسكي وكريستال لامع يسرق الأنظار في المناسبات.', 'صُممت خصيصاً للمناسبات الكبرى وحفلات الزفاف، قماش كريب ملكي ثقيل ومزدانة بشك يدوي فاخر يعكس الأضواء بأناقة هادئة دون مبالغة.\r\n\r\n- تدخيلات حرير شيفون مطوي.\r\n- شك يدوي يدوم لسنوات.\r\n- تشمل طرحة سهرة مطرزة بالكامل.', 2400.00, 2100.00, 10, '54, 56, 58, 60', 'كريب ملكي سهرة', 'أسود داكن ملوكي', 'assets/images/abaya5.jpg', 1, 1, 1),
(6, 5, 'عباية كيمونو بيج وأسود من كولكشن منون الجديد', 'kimono-beige-black-collection', 'MANON-006', 'مستوحاة من ألوان شعار منون بدرجات البيج الدافئ والأسود الملكي الفاخر.', 'قطعة حصرية بتناغم مذهل بين لون البيج الكريمي والأسود الكلاسيكي، تعبر بدقة عن هوية منون المتأصلة في الفخامة.\r\n\r\n- قصة كيمونو انسيابية مفتوحة مع إمكانية الإغلاق بطقطق.\r\n- قماش كريب حريري ثنائي اللون.\r\n- طرحة متطابقة بلونين مجاناً.', 1650.00, 1450.00, 12, '52, 54, 56, 58', 'كريب حرير مزدوج', 'بيج كريمي وأسود', 'assets/images/abaya6.jpg', 1, 1, 1);

-- Product Images Gallery
INSERT INTO `product_images` (`product_id`, `image_path`, `sort_order`) VALUES
(1, 'assets/images/abaya1.jpg', 1),
(1, 'assets/images/abaya2.jpg', 2),
(2, 'assets/images/abaya2.jpg', 1),
(2, 'assets/images/abaya5.jpg', 2),
(3, 'assets/images/abaya3.jpg', 1),
(4, 'assets/images/abaya4.jpg', 1),
(5, 'assets/images/abaya5.jpg', 1),
(6, 'assets/images/abaya6.jpg', 1);

-- Reviews
INSERT INTO `reviews` (`customer_name`, `customer_city`, `rating`, `review_text`, `status`) VALUES
('داليا منصور', 'القاهرة', 5, 'العباية الملكية خيال! السواد فاحم جداً والقصة راقية جداً في اللبس وكل من شافها سألني عنها. والتوصيل كان أسرع مما توقعت.', 'approved'),
('ريم السعيد', 'الإسكندرية', 5, 'خامة الحرير باردة وخفيفة ومريحة جداً لدوام العمل اليومي. المقاس مظبوط بالمللي كأنها متفصلة عشاني، وسهولة الطلب بالواتساب مريحة جداً.', 'approved'),
('هدى العوضي', 'المنصورة', 5, 'التغليف الملكي فتح نفسي قبل ما أفتح العباية! ريحة البخور مع العباية والتفاصيل الذهبية قمة الذوق والاحترافية. شكراً منون 🤍', 'approved');

-- Sample Initial Discount Codes
INSERT INTO `discount_codes` (`code`, `discount_percent`, `expires_at`, `is_used`) VALUES
('MANON10-VIP', 10, DATE_ADD(NOW(), INTERVAL 30 DAY), 0),
('MANON5-WELCOME', 5, DATE_ADD(NOW(), INTERVAL 30 DAY), 0);
