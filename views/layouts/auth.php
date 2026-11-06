<?php
/**
 * Split-screen layout for sign-in and registration.
 *
 * @var string $content
 * @var string $title
 */
use App\Utilities\Security\Session;

$flash = Session::pullFlash();
?>
<!doctype html>
<html lang="en">
<head>
    <?= partial('layout/head', ['title' => $title ?? '']) ?>
</head>
<body<?= $flash ? ' data-flash="' . e(json_encode($flash)) . '"' : '' ?>>
<?= partial('ui/icons') ?>
<?= partial('layout/guestNav', ['currentPath' => $currentPath ?? '/']) ?>
<div class="auth">
    <div class="auth__panel">
        <div class="auth__back">
            <?= button('Back', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'chevron-left', 'href' => url('/stadiums'), 'attrs' => ['data-back' => true]]) ?>
        </div>
        <div class="auth__card card anim-scale-in"><?= $content ?></div>
    </div>
    <aside class="auth__aside">
        <div class="pitch"><span class="pitch__line"></span></div>
        <div class="auth__aside-content anim-fade-up">
            <h2>Your game, your time.</h2>
            <p>Find a stadium, pick a free slot or full day, and play — no double bookings, ever.</p>
        </div>
    </aside>
</div>
<div class="toasts" data-toasts aria-live="polite"></div>
</body>
</html>
