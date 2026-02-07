<?php
/**
 * @var array $lotsBlock
 * @var array $pagination
 * @var array $lots
 * @var string $title
 */
?>

<section class="lots">
    <div class="lots__header">
        <h2>Все лоты в категории «<span><?= $title; ?></span>»</h2>
    </div>

    <?php if (empty($lots)) : ?>
        <p>В данной категории нет лотов!</p>
    <?php else : ?>
        <?= $lotsBlock; ?>
        <?= $pagination; ?>
    <?php endif; ?>

</section>
