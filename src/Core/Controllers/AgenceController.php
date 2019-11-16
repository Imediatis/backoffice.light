<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\AgenceDbAdapter;
use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\DbAdapters\CityDbAdapter;
use Digitalis\Core\Models\DbAdapters\CountryDbAdapter;
use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\DbAdapters\RegionDbAdapter;
use Digitalis\Core\Models\Entities\Agence;
use Digitalis\Core\Models\Entities\Caisse;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\AgenceViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * AgenceController Gestionnaire des actions de l'utilisateur
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class AgenceController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'branches'));

        return $this->render($response, 'index', true);
    }

    public function listBranches(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'view_branches';

            // Table's primary key
            $primaryKey = 'agc_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'agc_id', 'dt' => 9),
                array('db' => 'coun_enname', 'dt' => 10),
                array('db' => 'agc_isopened', 'dt' => 0, 'formatter' => function ($d, $row) {
                    $mask = '<span class="badge badge-%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-%s"></i></span>';
                    $color = $d == 1 ? "primary" : "danger";
                    $title = $d == 1 ? "opened" : "closed";
                    $icon = $d == 1 ? "check" : "times";
                    return sprintf($mask, $color, Lexique::GetString(CUR_LANG, $title), $icon);
                }),
                array('db' => 'agc_datecreate', 'dt' => 1, 'formatter' => function ($d) {
                    return (new \DateTime($d))->format('Y-m-d H:i:s');
                }),
                array('db' => 'ent_name', 'dt' => 2),
                array('db' => 'agc_code', 'dt' => 3, 'formatter' => function ($d) {
                    $agence = AgenceDbAdapter::getByCode($d);
                    return  $d . ' (' . number_format(count($agence->getCodeCaisses()), 0, '.', ' ') . ' ' . Lexique::GetString(CUR_LANG, 'teller') . '(s))';
                }),
                array('db' => 'agc_key', 'dt' => 4),
                array('db' => 'agc_label', 'dt' => 5),
                array('db' => 'agc_code', 'dt' => 6, 'formatter' => function ($d, $row) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li>%s</li>
									<li>%s</li>
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
                    $routeOpenClose = $router->pathFor('branch.openclose', ['code' => base64_encode($d), 'action' => base64_encode($row['agc_isopened'] ? 'close' : 'open')]);
                    $textoc = Lexique::GetString(CUR_LANG, $row['agc_isopened'] ? 'close' : 'open');
                    $openclose = sprintf('<a href="%s" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-exchange"></i> %s</a>', $routeOpenClose, $textoc, $textoc);
                    $edit = sprintf('<a href="%s" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('branch.update', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
                    $detail = sprintf('<a href="%s" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-eye"></i> %s</a>', $router->pathFor('branch.details', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'details'), Lexique::GetString(CUR_LANG, 'details'));
                    $regenkey = sprintf('<a href="#" class="text-danger" onclick="regkBranch(\'%s\')" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-qrcode"></i> %s</a>', base64_encode($d), Lexique::GetString(CUR_LANG, 'reset-key'), Lexique::GetString(CUR_LANG, 'reset-key'));
                    $delete = sprintf('<a href="#" class="text-danger" onclick="deleteBranch(\'%s\')" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-trash"></i> %s</a>', base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'), Lexique::GetString(CUR_LANG, 'delete'));
                    return sprintf($mask, $openclose, $edit, $regenkey, $detail, $delete);
                })
            );
            $output = DataTable::complex($_POST, DBase::paramsPDO(), $table, $primaryKey, $columns);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
            $output['error'] = Lexique::GetString(CUR_LANG, an_error_occured);
        }
        return $this->renderJson($response, $output);
    }

    public function branchDdchg(Request $request, Response $response)
    {
        $id = InputValidator::getInt('id');

        $output = new JsonResponse();
        $output->data = CaisseDbAdapter::getBoxesForOptions(null, ['agence' => $id]);
        $output->isSuccess = true;
        return $this->renderJson($response, $output->asArray());
    }

    public function create(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'new-branch'));
        $smodel = SessionManager::get(SysConst::MODEL);
        $model = !is_null($smodel) ? $smodel : new AgenceViewModel();

        $sentreprise = EntrepriseDbAdapter::getById($model->tb_ag_entrep);
        $entreprises = EntrepriseDbAdapter::getEnterprisesForOptions(!is_null($sentreprise) ? $sentreprise->getId() : null);
        $countries = CountryDbAdapter::getCountriesForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getRegion()->getCountry()->getId() : null);
        $regions = RegionDbAdapter::getRegionsForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getRegion()->getId() : null);
        $cities = CityDbAdapter::getCitiesForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getId() : null);

        return $this->render($response, 'create', true, [SysConst::MODEL => $model->toArray(), 'countries' => $countries, 'regions' => $regions, 'cities' => $cities, 'entreprises' => $entreprises]);
    }

    public function postCreate(Request $request, Response $response)
    {
        $model = new AgenceViewModel();
        $loggedUser = SessionManager::getLoggedUser();
        $model = InputValidator::BuildModelFromRequest($model, $request);

        SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_ag_entrep);
        SessionManager::set(SysConst::MODEL, $model->toArray());
        try {
            if (ModelState::isValid()) {
                $nagence = $model->convertToEntity();
                $nagence->setKey(AgenceDbAdapter::genAgenceKey());
                //
                //AJOUT DES CAISSES
                //
                for ($i = 1; $i <= $model->tb_ag_nb_caisse; $i++) {
                    $codecaisse  = $nagence->getCode() . $i;
                    $ncaisse = Caisse::getInstance($codecaisse);
                    $ncaisse->setUserCreate($loggedUser->getLogin());
                    $nagence->addCaisse($ncaisse);
                }
                if (AgenceDbAdapter::save($nagence)) {
                    $model = new AgenceViewModel();
                    SessionManager::set(SysConst::MODEL, null);
                    SessionManager::set(SysConst::SELECTED_ITEM, null);
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
                    SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
                }
            } else {
                SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
            }
        } catch (\Exception $exc) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }

        $sentreprise = EntrepriseDbAdapter::getById($model->tb_ag_entrep);
        $entreprises = EntrepriseDbAdapter::getEnterprisesForOptions(!is_null($sentreprise) ? $sentreprise->getId() : null);
        $countries = CountryDbAdapter::getCountriesForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getRegion()->getCountry()->getId() : null);
        $regions = RegionDbAdapter::getRegionsForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getRegion()->getId() : null);
        $cities = CityDbAdapter::getCitiesForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getId() : null);

        return $this->render($response, 'create', true, [SysConst::MODEL => $model->toArray(), SysConst::MODEL_ERRORS => ModelState::getErrors(), 'countries' => $countries, 'regions' => $regions, 'cities' => $cities, 'entreprises' => $entreprises]);
    }

    public function update(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update-branch'));
        $code = $request->getAttribute('code');
        if (is_null($code)) {
            $this->redirect($response, 'branch.read', 301);
        }

        $agence = AgenceDbAdapter::getByCode(base64_decode($code));
        if (is_null($agence)) {
            $this->redirect($response, 'branch.read', 301);
        }

        $smodel = SessionManager::get(SysConst::MODEL);
        $model = !is_null($smodel) ? $smodel : AgenceViewModel::buildFromEntity($agence);

        $sentreprise = EntrepriseDbAdapter::getById($model->tb_ag_entrep);
        $entreprises = EntrepriseDbAdapter::getEnterprisesForOptions(!is_null($sentreprise) ? $sentreprise->getId() : null);
        $countries = CountryDbAdapter::getCountriesForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getRegion()->getCountry()->getId() : null);
        $regions = RegionDbAdapter::getRegionsForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getRegion()->getId() : null);
        $cities = CityDbAdapter::getCitiesForOptions(!is_null($sentreprise) ? $sentreprise->getCity()->getId() : null);

        return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray(), 'countries' => $countries, 'regions' => $regions, 'cities' => $cities, 'entreprises' => $entreprises]);
    }

    public function postUpdate(Request $request, Response $response)
    {
        $model = new AgenceViewModel();
        $loggedUser = SessionManager::getLoggedUser();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        $code = $model->tb_ag_code;
        SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_ag_entrep);
        SessionManager::set(SysConst::MODEL, $model->toArray());
        try {
            if (ModelState::isValid()) {
                $nagence = $model->convertToEntity();
                $nagence->setKey(AgenceDbAdapter::genAgenceKey());
                //
                //AJOUT DES CAISSES nouvelles caisse
                //
                for ($i = 1; $i <= $model->tb_ag_nb_caisse; $i++) {
                    $codecaisse  = $nagence->getCode() . $i;
                    $ncaisse = Caisse::getInstance($codecaisse);
                    $ncaisse->setUserCreate($loggedUser->getLogin());
                    $nagence->addCaisse($ncaisse);
                }
                if (AgenceDbAdapter::update($nagence)) {
                    $model = new AgenceViewModel();
                    SessionManager::set(SysConst::MODEL, null);
                    SessionManager::set(SysConst::SELECTED_ITEM, null);
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
                    SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
                }
            } else {
                SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
            }
        } catch (\Exception $exc) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }


        return $this->redirect($response, 'branch.update', 301, ['code' => base64_encode($code)]);
    }

    public function branchOpenClose(Request $request, Response $response)
    {
        $action = base64_decode($request->getAttribute('action'));
        if (is_null($action)) {
            return $this->redirect($response, 'branch.read');
        }
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'branch.read');
        }
        $agence = AgenceDbAdapter::getByCode($code);
        if (is_null($agence)) {
            return $this->redirect($response, 'branch.read');
        }
        $this->title(Lexique::GetString(CUR_LANG, $action) . ' ' . Lexique::GetString(CUR_LANG, 'branch'));

        return $this->render($response, 'branchopenclose', true, [SysConst::MODEL => $agence->toArray(), 'labelAction' => $agence->getIsOpened() ? "close" : "open", 'color' => $action == 'open' ? 'primary' : 'danger']);
    }

    public function details(Request $request, Response $response)
    {
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'branch.read');
        }
        $agence = AgenceDbAdapter::getByCode($code);
        if (is_null($agence)) {
            return $this->redirect($response, 'branch.read');
        }
        $this->title(Lexique::GetString(CUR_LANG, 'details'));

        return $this->render($response, 'details', true, [SysConst::MODEL => $agence->toArray()]);
    }

    public function pbranchOpenClose(Request $request, Response $response)
    {
        $action = InputValidator::getString('action');
        $code = InputValidator::getString('code');
        $agence = AgenceDbAdapter::getByCode($code);
        if (is_null($agence)) {
            return $this->redirect($response, 'branch.read');
        }
        if (AgenceDbAdapter::openCloseBranch($code)) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
            $action = $agence->getIsOpened() ? 'close' : 'open';
        } else {
            SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
        }

        return $this->redirect($response, 'branch.openclose', 301, ['code' => base64_encode($code), 'action' => base64_encode($action)]);
    }

    public function delete(Request $request, Response $response)
    {
        $code = base64_decode(InputValidator::GetString('code'));
        $output = new JsonResponse();
        $agence = AgenceDbAdapter::getByCode($code);
        if ($agence) {
            if (AgenceDbAdapter::delete(Agence::class, $agence->getId())) {
                $output->message = Lexique::GetString(CUR_LANG, operation_success);
            } else {
                $output->isSuccess = false;
                $output->message = Data::getErrorMessage();
            }
        } else {
            $output->isSuccess = false;
            $output->message = Lexique::GetString(CUR_LANG, data_unavailable);
        }

        return $this->renderJson($response, $output->asArray());
    }

    public function keyGen(Request $request, Response $response)
    {
        $code = base64_decode(InputValidator::GetString('code'));
        $output = new JsonResponse();

        if (AgenceDbAdapter::changeAgenceKey($code)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }

        return $this->renderJson($response, $output->asArray());
    }

    public function activities(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        $code = base64_decode(InputValidator::getString('code'));
        try {
            // DB table to use
            $table = 'branchboxlogs';

            // Table's primary key
            $primaryKey = 'log_id';
            // indexes
            $columns = array(
                array('db' => 'log_id', 'dt' => 9),
                array('db' => 'log_type', 'dt' => 10),
                array('db' => 'log_dateaction', 'dt' => 0, 'formatter' => function ($d) {
                    return (new \DateTime($d))->format('Y-m-d H:i:s');
                }),
                array('db' => 'log_action', 'dt' => 1),
                array('db' => 'log_useraction', 'dt' => 2),
                array('db' => 'log_location', 'dt' => 3)
            );
            $where = $code ? "log_codeunit = '$code'" : null;
            $output = DataTable::complex($_POST, DBase::paramsPDO(), $table, $primaryKey, $columns, $where, $where);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
            $output['error'] = Lexique::GetString(CUR_LANG, an_error_occured);
        }
        return $this->renderJson($response, $output);
    }
}
