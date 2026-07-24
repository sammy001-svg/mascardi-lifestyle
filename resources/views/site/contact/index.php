<?php
/** @var array $settings */
use App\Core\View;

$heading = $settings['contact_heading'] ?? 'Get in Touch';
$intro = $settings['contact_intro'] ?? 'Questions about membership, partnerships, or an upcoming event? Send us a message and our team will respond shortly.';
$phone = $settings['footer_phone'] ?? '';
$email = $settings['footer_email'] ?? '';
$address = $settings['footer_address'] ?? '';
$hours = $settings['contact_hours'] ?? '';
$mapEmbed = $settings['contact_map_embed'] ?? '';
$success = flash_message('success');
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>

<section class="section section--offset-header" id="contact-form">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <span class="section-head__eyebrow">Contact</span>
            <h1 class="section-head__title"><?= e($heading) ?></h1>
            <?php if ($intro !== ''): ?>
                <p class="section-head__subtitle"><?= e($intro) ?></p>
            <?php endif; ?>
        </div>

        <div class="contact-layout">
            <aside class="contact-info" data-aos="fade-up">
                <h2 class="contact-info__title">Reach Us</h2>
                <ul class="contact-info__list">
                    <?php if ($phone !== ''): ?>
                        <li class="contact-info__item">
                            <span class="contact-info__label">Phone</span>
                            <a class="contact-info__value" href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                        <li class="contact-info__item">
                            <span class="contact-info__label">Email</span>
                            <a class="contact-info__value" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($address !== ''): ?>
                        <li class="contact-info__item">
                            <span class="contact-info__label">Address</span>
                            <span class="contact-info__value"><?= nl2br(e($address)) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($hours !== ''): ?>
                        <li class="contact-info__item">
                            <span class="contact-info__label">Hours</span>
                            <span class="contact-info__value"><?= nl2br(e($hours)) ?></span>
                        </li>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($settings['social_instagram_url']) || !empty($settings['social_facebook_url']) || !empty($settings['social_linkedin_url'])): ?>
                    <div class="contact-info__social">
                        <?php if (!empty($settings['social_instagram_url'])): ?><a href="<?= e($settings['social_instagram_url']) ?>" target="_blank" rel="noopener" aria-label="Instagram">IG</a><?php endif; ?>
                        <?php if (!empty($settings['social_facebook_url'])): ?><a href="<?= e($settings['social_facebook_url']) ?>" target="_blank" rel="noopener" aria-label="Facebook">FB</a><?php endif; ?>
                        <?php if (!empty($settings['social_linkedin_url'])): ?><a href="<?= e($settings['social_linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">IN</a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </aside>

            <div class="contact-form-wrap" data-aos="fade-up" data-aos-delay="100">
                <?php if ($success): ?>
                    <div class="contact-alert contact-alert--success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('contact') ?>" class="contact-form" novalidate>
                    <?= csrf_field() ?>
                    <?php /* Honeypot — hidden from humans, catches bots. */ ?>
                    <div class="contact-hp" aria-hidden="true">
                        <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="name">Name</label>
                            <input class="field__input<?= has_field_error('name') ? ' field__input--error' : '' ?>" type="text" id="name" name="name" value="<?= old('name') ?>" required>
                            <?php if ($err = field_errors('name')): ?><span class="field__error"><?= e($err[0]) ?></span><?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="field__label" for="email">Email</label>
                            <input class="field__input<?= has_field_error('email') ? ' field__input--error' : '' ?>" type="email" id="email" name="email" value="<?= old('email') ?>" required>
                            <?php if ($err = field_errors('email')): ?><span class="field__error"><?= e($err[0]) ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field__label" for="phone">Phone <span class="field__opt">(optional)</span></label>
                            <input class="field__input" type="tel" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="07XX XXX XXX">
                        </div>
                        <div class="field">
                            <label class="field__label" for="subject">Subject <span class="field__opt">(optional)</span></label>
                            <input class="field__input<?= has_field_error('subject') ? ' field__input--error' : '' ?>" type="text" id="subject" name="subject" value="<?= old('subject') ?>">
                            <?php if ($err = field_errors('subject')): ?><span class="field__error"><?= e($err[0]) ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="message">Message</label>
                        <textarea class="field__input field__textarea<?= has_field_error('message') ? ' field__input--error' : '' ?>" id="message" name="message" rows="6" required><?= old('message') ?></textarea>
                        <?php if ($err = field_errors('message')): ?><span class="field__error"><?= e($err[0]) ?></span><?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-dark contact-form__submit">Send Message</button>
                </form>
            </div>
        </div>

        <?php if ($mapEmbed !== ''): ?>
            <div class="contact-map" data-aos="fade-up">
                <iframe src="<?= e($mapEmbed) ?>" width="100%" height="420" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
