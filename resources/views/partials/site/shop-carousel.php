<?php
/** @var array $products */
use App\Core\View;

$pages = array_chunk($products, 8);
?>
<section class="section" id="shop">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <span class="section-head__eyebrow">Shop Mascardi</span>
            <h2 class="section-head__title">Curated Merchandise</h2>
            <p class="section-head__subtitle">Exclusive branded pieces and partner products, available to Mascardi owners first.</p>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-note">The shop is launching soon — check back for exclusive Mascardi merchandise.</div>
        <?php else: ?>
            <div class="shop-carousel" data-aos="fade-up">
                <div class="shop-carousel__track" id="shopCarouselTrack">
                    <?php foreach ($pages as $page): ?>
                        <div class="shop-carousel__page">
                            <?php foreach ($page as $product): ?>
                                <?= View::renderPartial('partials/site/product-card', ['product' => $product]) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($pages) > 1): ?>
                    <div class="shop-carousel__nav">
                        <button type="button" class="shop-carousel__nav-btn" id="shopCarouselPrev" aria-label="Previous products">&#8592;</button>
                        <button type="button" class="shop-carousel__nav-btn" id="shopCarouselNext" aria-label="Next products">&#8594;</button>
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align:center;margin-top:36px;">
                <a href="<?= site_url('shop') ?>" class="btn btn-dark">View Full Shop</a>
            </div>
        <?php endif; ?>
    </div>
</section>
