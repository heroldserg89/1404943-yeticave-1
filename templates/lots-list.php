<?php
/**
 * @var string $title
 * @var array $lots
 */

?>

<ul class="lots__list">
    <?php
    foreach ($lots as $lot) : ?>
        <li class="lots__item lot">
            <div class="lot__image">
                <img src="<?= $lot['img_url']; ?>" width="350" height="260"
                     alt="Фото <?= htmlspecialchars($lot['title']); ?>">
            </div>
            <div class="lot__info">
                <span class="lot__category"><?= htmlspecialchars($lot['category']); ?></span>
                <h3 class="lot__title">
                    <a class="text-link" href="/lot.php?id=<?= $lot['id']; ?>">
                        <?= htmlspecialchars($lot['title']); ?>
                    </a>
                </h3>
                <div class="lot__state">
                    <div class="lot__rate">
                        <span class="lot__amount">Стартовая цена</span>
                        <span class="lot__cost"><?= formatPrice($lot['price_start']); ?></span>
                    </div>
                    <?php
                    [$hours, $minutes] = getTimeRemaining($lot['end_at']); ?>
                    <div class="lot__timer timer <?= $hours === '00' ? 'timer--finishing' : ''; ?>">
                        <?= "{$hours}:{$minutes}"; ?>
                    </div>
                </div>
            </div>
        </li>
        <?php
    endforeach; ?>
</ul>

