<?php
/**
 * @var array $lotsBlock
 * @var array $pagination
 * @var int $lotsCount
 * @var string $title
 */
?>

<section class="lots">
    <div class="lots__header">
        <h2>Результаты поиска по запросу «<span><?= $title; ?></span>»</h2>
    </div>

    <?php if (empty($lots)) : ?>
        <p>По вашему запросу ничего не найдено</p>
    <?php else : ?>
        <?= $lotsBlock; ?>
        <?= $pagination; ?>
    <?php endif; ?>

</section>
