<div>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form class="login-form center flex-column" method="POST" action="/auth/login">
        <h1 class="header mb-4"><?= hs(app()->getConfig()->get('appName')); ?></h1>
        <div class="form-group w-100 mb-3">
            <input autofocus type="email" required name="email" class="form-control" placeholder="Email" aria-label="Username" aria-describedby="basic-addon1">
        </div>
        <div class="form-group w-100 mb-3">
            <input type="password" required name="password" class="form-control" placeholder="Password" aria-label="Username" aria-describedby="basic-addon1">
        </div>
        <?= (new \app\core\src\tokens\CsrfToken())->insertHiddenToken(); ?>
            <button type="submit" class="btn btn-success btn-lg mt-2 w-100"><?= ths('Log in'); ?></button>
        <a href="signup" class="btn btn-info btn-lg mt-2 w-100"><?= ths('Create account'); ?></a>
    </form>
</div>