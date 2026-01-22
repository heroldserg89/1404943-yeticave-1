<?php
/**
 * @var array $errors
 * @var array $formInputs
 */
?>
<form class="form container" action="sign-up.php" method="post" autocomplete="off">
    <h2>Регистрация нового аккаунта</h2>
    <div class="form__item <?= getErrorClass($errors, 'email'); ?>">
        <label for="email">E-mail <sup>*</sup></label>
        <input id="email" type="text" name="email" placeholder="Введите e-mail"
               value="<?= htmlspecialchars($formInputs['email'] ?? ''); ?>">
        <span class="form__error"><?= $errors['email'] ?? ''; ?></span>
    </div>
    <div class="form__item <?= getErrorClass($errors, 'password'); ?>">
        <label for=" password">Пароль <sup>*</sup></label>
        <input id="password" type="password" name="password" placeholder="Введите пароль"
               value="<?= htmlspecialchars($formInputs['password'] ?? ''); ?>">
        <span class=" form__error"><?= $errors['password'] ?? ''; ?></span>
    </div>
    <div class="form__item <?= getErrorClass($errors, 'name'); ?>">
        <label for="name">Имя <sup>*</sup></label>
        <input id="name" type="text" name="name" placeholder="Введите имя"
               value="<?= htmlspecialchars($formInputs['name'] ?? ''); ?>">
        <span class="form__error"><?= $errors['name'] ?? ''; ?></span>
    </div>
    <div class="form__item <?= getErrorClass($errors, 'message'); ?>">
        <label for="message">Контактные данные <sup>*</sup></label>
        <textarea id="message" name="message"
                  placeholder="Напишите как с вами связаться"><?= htmlspecialchars($formInputs['password'] ?? ''); ?></textarea>
        <span class="form__error"><?= $errors['message'] ?? ''; ?></span>
    </div>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <button type="submit" class="button">Зарегистрироваться</button>
    <a class="text-link" href="login.php">Уже есть аккаунт</a>
</form>

