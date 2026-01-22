<?php

/**
 * @var array $lot
 * @var array $bets
 */

?>
<section class="lot-item container">
    <h2><?= $lot['title']; ?></h2>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <img src="<?= $lot['img_url']; ?>" width="730" height="548"
                     alt="<?= htmlspecialchars($lot['title']); ?>">
            </div>
            <p class="lot-item__category">Категория: <span><?= htmlspecialchars($lot['category_name']); ?></span></p>
            <p class="lot-item__description"><?= htmlspecialchars($lot['description']); ?></p>
        </div>
        <div class="lot-item__right">
            <div class="lot-item__state">
                <?php
                [$hours, $minutes] = getTimeRemaining($lot['end_at']); ?>
                <div class="lot-item__timer timer  <?= $hours === '00' ? 'timer--finishing' : ''; ?>">
                    <?= "{$hours}:{$minutes}"; ?>
                </div>
                <div class="lot-item__cost-state">
                    <div class="lot-item__rate">
                        <span class="lot-item__amount">Текущая цена</span>
                        <span class="lot-item__cost"><?= htmlspecialchars($lot['current_price']); ?></span>
                    </div>
                    <div class="lot-item__min-cost">
                        Мин. ставка <span><?= htmlspecialchars($lot['min_bid']); ?> р</span>
                    </div>
                </div>
                <form class="lot-item__form" action="https://echo.htmlacademy.ru" method="post" autocomplete="off">
                    <p class="lot-item__form-item form__item form__item--invalid">
                        <label for="cost">Ваша ставка</label>
                        <input id="cost" type="text" name="cost"
                               placeholder="<?= htmlspecialchars($lot['min_bid']); ?>">
                        <span class="form__error">Введите наименование лота</span>
                    </p>
                    <button type="submit" class="button">Сделать ставку</button>
                </form>
            </div>
            <div class="history">
                <h3>История ставок (<span><?= count($bets) ?></span>)</h3>
                <table class="history__list">
                    <?php foreach ($bets as $bet) : ?>
                        <tr class="history__item">
                            <td class="history__name"><?= htmlspecialchars($bet['user_name']) ?></td>
                            <td class="history__price"><?= htmlspecialchars($bet['price']); ?> р</td>
                            <td class="history__time"><?= htmlspecialchars($bet['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</section>
</main>
