<?php
/**
 * @var array $pages
 * @var int $curPage
 */
?>

<?php if (count($pages) > 1) : ?>
    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev">
            <?php if ($curPage === 1) : ?>
                <a>Назад</a>
            <?php else : ?>
                <a href="<?= buildPaginationLink($curPage - 1); ?>">Назад</a>
            <?php endif; ?>
        </li>
        <?php foreach ($pages as $page) : ?>
            <li class="pagination-item <?= $page === $curPage ? 'pagination-item-active' : ''; ?>"><a
                    href="<?= buildPaginationLink($page); ?>"><?= $page; ?></a></li>
        <?php endforeach; ?>
        <li class="pagination-item pagination-item-next">
            <?php if ($curPage === count($pages)) : ?>
                <a>Вперед</a>
            <?php else : ?>
                <a href="<?= buildPaginationLink($curPage + 1); ?>">Вперед</a>
            <?php endif; ?>
        </li>
    </ul>

<?php endif; ?>
