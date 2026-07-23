-- Mascardi Lifestyle — initial seed data (safe to commit: no credentials here)
-- Run once after schema.sql on a fresh database, then run seed_admin.sql
-- (see database/seed_admin.example.sql) to create your first admin login.

-- The 8 pillars, seeded with brief-derived copy. image_path left NULL —
-- admin uploads the real brand photography from the Pillars module.
INSERT INTO pillars (slug, name, description, sort_order, is_active) VALUES
('sports-wellness', 'Mascardi Sports & Wellness', 'Golf, polo, running, football, fitness, boxing and more — including Move with Mascardi™, our fitness and wellness movement.', 1, 1),
('residences-living', 'Mascardi Residences & Living', 'Access and preferential terms with Kenya''s top luxury real estate brands.', 2, 1),
('stays', 'Mascardi Stays', 'Preferred rates and experiences with five-star hotels and boutique lodges.', 3, 1),
('dining-social', 'Mascardi Dining & Social', 'The best tables and curated social experiences.', 4, 1),
('style-fashion', 'Mascardi Style & Fashion', 'Partnerships with eyewear, apparel, and styling brands.', 5, 1),
('escapes', 'Mascardi Escapes', 'Curated travel and destination experiences.', 6, 1),
('elite-travel-concierge', 'Mascardi Elite Travel Concierge', 'A white-glove concierge layer for private aviation, VIP travel access, and event travel logistics.', 7, 1),
('club', 'Mascardi Club', 'The formalized membership tier that unites the whole ecosystem for our most loyal clients.', 8, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Core site settings. hero_youtube_id is the ID portion of the client-supplied
-- launch video URL (https://youtu.be/SMDJ9W_eQJc). Secrets never go here.
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Mascardi Lifestyle'),
('hero_youtube_id', 'SMDJ9W_eQJc'),
('hero_overlay_text', 'Belong to the Difference'),
('footer_tagline', 'Experience the Difference.'),
('footer_phone', ''),
('footer_email', ''),
('footer_address', 'Lavington & Spring Valley, Nairobi'),
('social_instagram_url', ''),
('social_facebook_url', ''),
('social_linkedin_url', '')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
