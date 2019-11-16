<?php

use Digitalis\Core\Controllers\ProfileController;
use Digitalis\Core\Controllers\UserController;
use Digitalis\Core\Controllers\AgenceController;
use Digitalis\Core\Controllers\CaisseController;
use Digitalis\Core\Controllers\CityController;
use Digitalis\Core\Controllers\CountryController;
use Digitalis\Core\Controllers\EntrepriseController;
use Digitalis\Core\Controllers\OperatorController;
use Digitalis\Core\Controllers\PartnerController;
use Digitalis\Core\Controllers\RegionController;
use Digitalis\Core\Controllers\TariffGridController;
use Digitalis\Core\Controllers\TypePieceController;
use Digitalis\Core\Middlewares\AuthenticationMiddleware;
use Digitalis\Core\Middlewares\BranchBoxLogMiddleware;
use Digitalis\Core\Middlewares\TraceAffectationMiddleware;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\Menu\MenuItem;
use Digitalis\Core\Models\Menu\MenuManager;
use Digitalis\Core\Models\Security\CsrfMiddleware;
use Slim\App;

//$app = new \Slim\App();

$c = $app->getContainer();

$app->group(R_CUR_LANG, function (App $app) {
    $app->group('/Profile', function (App $app) {
        $app->get('', ProfileController::class . ':index')->setName('profile.read');
        $app->get('/Create', ProfileController::class . ':create')->setName('profile.create');
        $app->post('/Create', ProfileController::class . ':postCreate')->setName('profile.pcreate');
        $app->get('/Update/{id}', ProfileController::class . ':update')->setName('profile.update');
        $app->post('/Update', ProfileController::class . ':postUpdate')->setName('profile.pupdate');
        $app->post('/Activate', ProfileController::class . ':activate')->setName('profile.activate');
        $app->post('/Delete', ProfileController::class . ':delete')->setName('profile.delete');
    });

    $app->group('/User', function (App $app) {
        $app->get('', UserController::class . ':index')->setName('user.read');
        $app->get('/Create', UserController::class . ':create')->setName('user.create');
        $app->post('/Create', UserController::class . ':postCreate')->setName('user.pcreate');
        $app->get('/Update/{login}', UserController::class . ':update')->setName('user.update');
        $app->post('/Update', UserController::class . ':postUpdate')->setName('user.pupdate');
        $app->post('/Activate', UserController::class . ':activate')->setName('user.activate');
    });

    $app->group('/Country', function (App $app) {
        $app->get('', CountryController::class . ':index')->setName('country.read');
        $app->get('/Create', CountryController::class . ':create')->setName('country.create');
        $app->post('/Create', CountryController::class . ':postCreate')->setName('country.pcreate');
        $app->get('/Update/{id}', CountryController::class . ':update')->setName('country.update');
        $app->post('/Update', CountryController::class . ':postUpdate')->setName('country.pupdate');
        $app->post('/Delete', CountryController::class . ':delete')->setName('country.delete');
    });

    $app->group('/Region', function (App $app) {
        $app->get('', RegionController::class . ':index')->setName('region.read');
        $app->get('/Create', RegionController::class . ':create')->setName('region.create');
        $app->post('/Create', RegionController::class . ':postCreate')->setName('region.pcreate');
        $app->get('/Update/{code}', RegionController::class . ':update')->setName('region.update');
        $app->post('/Update', RegionController::class . ':postUpdate')->setName('region.pupdate');
        $app->post('/Delete', RegionController::class . ':delete')->setName('region.delete');
    });

    $app->group('/City', function (App $app) {
        $app->get('', CityController::class . ':index')->setName('city.read');
        $app->get('/Create', CityController::class . ':create')->setName('city.create');
        $app->post('/Create', CityController::class . ':postCreate')->setName('city.pcreate');
        $app->get('/Update/{code}', CityController::class . ':update')->setName('city.update');
        $app->post('/Update', CityController::class . ':postUpdate')->setName('city.pupdate');
        $app->post('/Delete', CityController::class . ':delete')->setName('city.delete');
    });

    $app->group('/TPiece', function (App $app) {
        $app->get('', TypePieceController::class . ':index')->setName('tpiece.read');
        $app->get('/Create', TypePieceController::class . ':create')->setName('tpiece.create');
        $app->post('/Create', TypePieceController::class . ':postCreate')->setName('tpiece.pcreate');
        $app->get('/Update/{code}', TypePieceController::class . ':update')->setName('tpiece.update');
        $app->post('/Update', TypePieceController::class . ':postUpdate')->setName('tpiece.pupdate');
        $app->post('/Delete', TypePieceController::class . ':delete')->setName('tpiece.delete');
    });

    $app->group('/Entreprise', function (App $app) {
        $app->get('', EntrepriseController::class . ':index')->setName('entreprise.read');
        $app->get('/Create', EntrepriseController::class . ':create')->setName('entreprise.create');
        $app->post('/Create', EntrepriseController::class . ':postCreate')->setName('entreprise.pcreate');
        $app->get('/Update/{code}', EntrepriseController::class . ':update')->setName('entreprise.update');
        $app->post('/Update', EntrepriseController::class . ':postUpdate')->setName('entreprise.pupdate');
        $app->get('/Details/{code}', EntrepriseController::class . ':details')->setName('entreprise.details');
        $app->post('/Delete', EntrepriseController::class . ':delete')->setName('entreprise.delete');

        $app->get('/Affectation', EntrepriseController::class . ':affectation')->setName('affectation.read');
        $app->get('/Affectation/Create', EntrepriseController::class . ':createAffectation')->setName('affectation.create');
        $app->post('/Affectation/Create', EntrepriseController::class . ':postCreateAffectation')->setName('affectation.pcreate');
        $app->post('/Affectation/Delete', EntrepriseController::class . ':deleteAffectation')->setName('affectation.delete');
    });

    $app->group('/Branch', function (App $app) {
        $app->get('', AgenceController::class . ':index')->setName('branch.read');
        $app->get('/Create', AgenceController::class . ':create')->setName('branch.create');
        $app->post('/Create', AgenceController::class . ':postCreate')->setName('branch.pcreate');
        $app->get('/Update/{code}', AgenceController::class . ':update')->setName('branch.update');
        $app->post('/Update', AgenceController::class . ':postUpdate')->setName('branch.pupdate');
        $app->get('/Details/{code}', AgenceController::class . ':details')->setName('branch.details');
        $app->get('/OpenClose/{code}/{action}', AgenceController::class . ':branchOpenClose')->setName('branch.openclose');
        $app->post('/OpenClose', AgenceController::class . ':pbranchOpenClose')->setName('branch.popenclose');
        $app->post('/Delete', AgenceController::class . ':delete')->setName('branch.delete');
        $app->post('/KeyGen', AgenceController::class . ':keyGen')->setName('branch.keygen');
    })->add(new BranchBoxLogMiddleware($app->getContainer()));

    $app->group('/Box', function (App $app) {
        $app->get('', CaisseController::class . ':index')->setName('box.read');
        $app->get('/Create', CaisseController::class . ':create')->setName('box.create');
        $app->post('/Create', CaisseController::class . ':postCreate')->setName('box.pcreate');
        $app->get('/Update/{code}', CaisseController::class . ':update')->setName('box.update');
        $app->post('/Update', CaisseController::class . ':postUpdate')->setName('box.pupdate');
        $app->get('/Details/{code}', CaisseController::class . ':details')->setName('box.details');
        $app->get('/OpenClose/{code}/{action}', CaisseController::class . ':boxOpenClose')->setName('box.openclose');
        $app->post('/OpenClose', CaisseController::class . ':pboxOpenClose')->setName('box.popenclose');
        $app->post('/Delete', CaisseController::class . ':delete')->setName('box.delete');
        $app->post('/KeyGen', CaisseController::class . ':keyGen')->setName('box.keygen');
    })->add(new BranchBoxLogMiddleware($app->getContainer()));

    $app->group('/Operator', function (App $app) {
        $app->get('', OperatorController::class . ':index')->setName('operator.read');
        $app->get('/Create', OperatorController::class . ':create')->setName('operator.create');
        $app->post('/Create', OperatorController::class . ':postCreate')->setName('operator.pcreate');
        $app->get('/Update/{login}', OperatorController::class . ':update')->setName('operator.update');
        $app->post('/Update', OperatorController::class . ':postUpdate')->setName('operator.pupdate');
        $app->post('/Activate', OperatorController::class . ':activate')->setName('operator.activate');
        $app->post('/Bologout', OperatorController::class . ':bologout')->setName('operator.bologout');
        $app->post('/ResetPwd', OperatorController::class . ':resetPwd')->setName('operator.resetpwd');
        $app->get('/SetOperator[/{code}]', OperatorController::class . ':setOperator')->setName('aoperator.setoperator');
        $app->post('/SetOperator[/{code}]', OperatorController::class . ':psetOperator')->setName('aoperator.psetoperator')->add(new TraceAffectationMiddleware($app->getContainer()));
    });

    $app->group('/TariffGrid', function (App $app) {
        $app->get('', TariffGridController::class . ':index')->setName('tariffgrid.read');
        $app->get('/Create', TariffGridController::class . ':create')->setName('tariffgrid.create');
        $app->post('/Create', TariffGridController::class . ':postCreate')->setName('tariffgrid.pcreate');
        $app->get('/Update/{id}', TariffGridController::class . ':update')->setName('tariffgrid.update');
        $app->post('/Update', TariffGridController::class . ':postUpdate')->setName('tariffgrid.pupdate');
        $app->post('/Delete', TariffGridController::class . ':delete')->setName('tariffgrid.delete');
    });

    $app->group('/Partner', function (App $app) {
        $app->get('', PartnerController::class . ':index')->setName('partner.read');
        $app->get('/Create', PartnerController::class . ':create')->setName('partner.create');
        $app->post('/Create', PartnerController::class . ':postCreate')->setName('partner.pcreate');
        $app->get('/Update/{id}', PartnerController::class . ':update')->setName('partner.update');
        $app->post('/Update', PartnerController::class . ':postUpdate')->setName('partner.pupdate');
        $app->post('/Delete', PartnerController::class . ':delete')->setName('partner.delete');
        $app->post('/Activate', PartnerController::class . ':activate')->setName('partner.activate');
    });
})->add(new AuthenticationMiddleware($c))->add(new CsrfMiddleware($c));

//
//Pour ne ne pas contrôler le Requestforgery token pour le data table
//mettre uniquement les liens vers les datata table dans cette zone
//
$app->group(R_CUR_LANG, function (App $app) {
    $app->group('/Profile', function (App $app) {
        $app->post('', ProfileController::class . ':listProfile')->setName('profile.dt');
    });

    $app->group('/User', function (App $app) {
        $app->post('', UserController::class . ':listUser')->setName('user.dt');
    });

    $app->group('/Country', function (App $app) {
        $app->post('', CountryController::class . ':listcountry')->setName('country.dt');
        $app->post('/Change', CountryController::class . ':countryDdchg')->setName('country.ddchg');
    });

    $app->group('/Region', function (App $app) {
        $app->post('', RegionController::class . ':listregions')->setName('region.dt');
        $app->post('/Change', RegionController::class . ':regionDdchg')->setName('region.ddchg');
    });

    $app->group('/City', function (App $app) {
        $app->post('', CityController::class . ':listcities')->setName('city.dt');
        $app->post('/Change', CityController::class . ':cityDdchg')->setName('city.ddchg');
    });

    $app->group('/TPiece', function (App $app) {
        $app->post('', TypePieceController::class . ':listTPieces')->setName('tpiece.dt');
    });

    $app->group('/Entreprise', function (App $app) {
        $app->post('', EntrepriseController::class . ':listEnterprises')->setName('entreprise.dt');
        $app->post('/Affectation', EntrepriseController::class . ':listAffectations')->setName('affectation.dt');
        $app->post('/Change', EntrepriseController::class . ':entrepDdchg')->setName('entreprise.ddchg');
    });

    $app->group('/Branch', function (App $app) {
        $app->post('', AgenceController::class . ':listBranches')->setName('branch.dt');
        $app->post('/Change', AgenceController::class . ':branchDdchg')->setName('branch.ddchg');
        $app->post('/Activities', AgenceController::class . ':activities')->setName('branch.activities');
    });

    $app->group('/Box', function (App $app) {
        $app->post('', CaisseController::class . ':listCaisses')->setName('box.dt');
        $app->post('/Activities', CaisseController::class . ':activities')->setName('box.activities');
    });

    $app->group('/Operator', function (App $app) {
        $app->post('', OperatorController::class . ':listOperators')->setName('operator.dt');
        $app->post('/dtoperators', OperatorController::class . ':listEntOperators')->setName('operator.lentoperator');
    });

    $app->group('/TariffGrid', function (App $app) {
        $app->post('', TariffGridController::class . ':listTariffGrid')->setName('tariffgrid.dt');
        $app->post('entTariffGrid', TariffGridController::class . ':listTariffGridCompany')->setName('tariffgrid.dt_ent');
    });

    $app->group('/Partner', function (App $app) {
        $app->post('', PartnerController::class . ':listPartners')->setName('partner.dt');
    });
});

$mhabilitation = new MenuItem('Habilitation', null, true, 1, 'users');
$mhabilitation->addChildren(new MenuItem('Profil', 'profile.read', false, 1, 'id-badge'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'users'), 'user.read', false, 2, 'user'));
MenuManager::add($mhabilitation);

$mlocation = new MenuItem('Localisation', null, true, 2, 'location-arrow');
$mlocation->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'countries'), 'country.read', false, 1, 'flag'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'regions'), 'region.read', false, 2, 'building'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'cities'), 'city.read', false, 3, 'building-o'));
MenuManager::add($mlocation);

$mpartners = new MenuItem(Lexique::GetString(CUR_LANG, 'structure'), null, true, 3, 'industry');
$mpartners->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'enterprises'), 'entreprise.read', false, 1, 'institution'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'affectations'), 'affectation.read', false, 2, 'wechat'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'branches'), 'branch.read', false, 3, 'sitemap'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'tellers'), 'box.read', false, 4, 'codepen'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'price-list'), 'tariffgrid.read', false, 5, 'sliders'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'partners'), 'partner.read', false, 6, 'institution'));
MenuManager::add($mpartners);

$moperators = new MenuItem(Lexique::GetString(CUR_LANG, 'operators'), null, true, 4, 'users');
$moperators
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'operators'), 'operator.read', false, 1, 'users'))
    ->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'assignments'), 'aoperator.setoperator', false, 2, 'user-plus'));

MenuManager::add($moperators);

$paramters = new MenuItem(Lexique::GetString(CUR_LANG, 'parameters'), null, true, 5, 'wrench');
$paramters->addChildren(new MenuItem(Lexique::GetString(CUR_LANG, 'type-of-document'), 'tpiece.read', false, 0, 'id-card'));
MenuManager::add($paramters);
