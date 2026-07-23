-- Creates your first admin login. This file is a TEMPLATE — copy it to
-- seed_admin.sql (gitignored, never commit real credentials) and fill in
-- your own hash before running it.
--
-- Generate a bcrypt hash for your chosen password:
--   php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
--
-- Then paste the output below in place of the placeholder hash.

INSERT INTO admin_users (name, email, password_hash, role, is_active)
VALUES (
    'Your Name',
    'you@example.com',
    '$2y$10$REPLACE.WITH.YOUR.OWN.GENERATED.BCRYPT.HASH.xxxxxxxxxxxxxx',
    'super_admin',
    1
)
ON DUPLICATE KEY UPDATE email = email;
