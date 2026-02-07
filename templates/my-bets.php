<?php
/**
 * @var array $bets
 * @var array $user
 */
?>
<section class="rates">
    <h2>Мои ставки</h2>
    <?php if (!empty($bets)) : ?>
        <table class="rates__list">
            <?php foreach ($bets as $bet) :
                [$hours, $minutes] = getTimeRemaining($bet['end_at']);
                [$message, $modifier] = getBetStatus($hours, $minutes, $bet['winner_id'], $user['id']);
                ?>
                <tr class="rates__item <?= $modifier === '' || $modifier === 'finishing' ? '' : 'rates__item--' . $modifier; ?>">
                    <td class="rates__info">
                        <div class="rates__img">
                            <img src="/<?= $bet['img_url']; ?>" width="54" height="40" alt="Сноуборд">
                        </div>
                        <div>
                            <h3 class="rates__title"><a
                                    href="/lot.php?id=<?= $bet['lot_id']; ?>"><?= htmlspecialchars($bet['title']); ?></a>
                            </h3>
                            <?php if ($modifier === 'win') : ?>
                                <p><?= $bet['contacts']; ?></p>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="rates__category">
                        <?= htmlspecialchars($bet['category']); ?>
                    </td>
                    <td class="rates__timer">
                        <div
                            class="timer <?= $modifier === '' ? '' : 'timer--' . $modifier; ?>"><?= $message; ?></div>
                    </td>
                    <td class="rates__price">
                        <?= $bet['price']; ?> р
                    </td>
                    <td class="rates__time">
                        <?= formatElapsedTime($bet['created_at']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <p>Вы еще не делали ставки.</p>'
    <?php endif; ?>
</section>
