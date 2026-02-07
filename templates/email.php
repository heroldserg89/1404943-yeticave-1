<?php

/**
* @var string $winner
 * @var string $lot
 * @var string $baseUrl
 */
?>
<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?=$winner['name']; ?></p>
<p>Ваша ставка для лота <a href="<?=$baseUrl; ?>/lot.php?id=<?=$lot['id']?>"><?=$lot['title']?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?=$baseUrl; ?>/my-bets.php">мои ставки</a>,
чтобы связаться с автором объявления</p>
<small>Интернет-Аукцион "YetiCave"</small>
