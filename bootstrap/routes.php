<?php

use Digitalis\Core\Controllers\UserController;
use Digitalis\Core\Controllers\AccountController;
use Digitalis\Core\Controllers\HomeController;
use Digitalis\Core\Middlewares\AuthenticationMiddleware;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\Menu\MenuItem;
use Digitalis\Core\Models\Menu\MenuManager;
use Digitalis\Core\Models\Security\CsrfMiddleware;
use Digitalis\Core\Models\SysConst;
use Slim\App;

//$app = new \Slim\App();

$c = $app->getContainer();
$app->post(R_CUR_LANG . "/Account/CheckLoggin", AccountController::class . ':checkAccount')->setName('account.checklogin')->add(new AuthenticationMiddleware($c));
$app->get(R_CUR_LANG . "/User/Profile/{login}", UserController::class . ':profile')->setName('user.profile')->add(new AuthenticationMiddleware($c));
$app->get(R_CUR_LANG . "/User/Changepwd/{login}", UserController::class . ':changepwd')->setName('user.changepwd')->add(new AuthenticationMiddleware($c));
$app->post(R_CUR_LANG . "/User/Changepwd", UserController::class . ':postChangepwd')->setName('user.pchangepwd')->add(new AuthenticationMiddleware($c))->add(new CsrfMiddleware($c));

$app->group(R_CUR_LANG, function (App $app) {

    $app->get('', HomeController::class . ':index')->setName(SysConst::HOME);

    $app->group('/Account', function (App $app) {
        $app->get("/Login", AccountController::class . ':index')->setName(SysConst::R_G_LOGIN);
        $app->post("/Login", AccountController::class . ':login')->setName(SysConst::R_P_LOGIN);
        $app->post("/Logout", AccountController::class . ':logout')->setName(SysConst::R_LOGOUT);
        $app->post("/ForceLogout", AccountController::class . ':forceLogout')->setName('account.logout.force');
        $app->get("/Firstlogin/{login}", AccountController::class . ':changepwd')->setName('account.changepwd');
        $app->post("/Firstlogin", AccountController::class . ':postChangepwd')->setName('account.pchangepwd');
        $app->post('/Bologout', AccountController::class . ':bologout')->setName('account.bologout');
        $app->post('/ResetPwd', AccountController::class . ':resetPwd')->setName('account.resetpwd');
    });
})->add(new AuthenticationMiddleware($c))->add(new CsrfMiddleware($c));

MenuManager::initMenu();

MenuManager::add(new MenuItem(Lexique::GetString(CUR_LANG, 'home'), SysConst::HOME, false, 0, 'home'));

$resellerRoutefile = $c->baseDir . join(DIRECTORY_SEPARATOR, ['src', $c->reseller->folder, 'route.php']);
if (file_exists($resellerRoutefile)) {
    include $resellerRoutefile;
}
