<?php
/** @var array $settings */
/** @var array $pillars */
/** @var array $partners */
/** @var array $products */
use App\Core\View;
?>
<?= View::renderPartial('partials/site/header', ['settings' => $settings]) ?>
<?= View::renderPartial('partials/site/hero', ['settings' => $settings]) ?>
<?= View::renderPartial('partials/site/pillars-grid', ['pillars' => $pillars]) ?>
<?= View::renderPartial('partials/site/partners-grid', ['partners' => $partners]) ?>
<?= View::renderPartial('partials/site/shop-carousel', ['products' => $products]) ?>
<?= View::renderPartial('partials/site/events-list') ?>
<?= View::renderPartial('partials/site/footer', ['settings' => $settings]) ?>
