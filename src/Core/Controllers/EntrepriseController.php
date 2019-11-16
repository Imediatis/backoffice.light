<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\AffectationDbAdapter;
use Digitalis\Core\Models\DbAdapters\AgenceDbAdapter;
use Digitalis\Core\Models\DbAdapters\CityDbAdapter;
use Digitalis\Core\Models\DbAdapters\CountryDbAdapter;
use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\DbAdapters\PartnerDbAdapter;
use Digitalis\Core\Models\DbAdapters\RegionDbAdapter;
use Digitalis\Core\Models\Entities\Entreprise;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\AffectationViewModel;
use Digitalis\Core\Models\ViewModels\EntrepriseViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * EntrepriseController Controleur de gestion des actions des utilisateurs
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class EntrepriseController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'enterprises'));

        return $this->render($response, 'index', true);
    }

    public function listEnterprises(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'view_enterprises';

            // Table's primary key
            $primaryKey = 'ent_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'reg_id', 'dt' => 8),
                array('db' => 'coun_enname', 'dt' => 9),
                array('db' => 'ent_domain', 'dt' => 10),
                array('db' => 'ent_datecreate', 'dt' => 0, 'formatter' => function ($d) {
                    return (new \DateTime($d))->format('Y-m-d H:i:s');
                }),
                array('db' => 'coun_frname', 'dt' => 1, 'formatter' => function ($d, $row) {
                    return strtolower(CUR_LANG) == 'en' ? $row['coun_enname'] : $d;
                }),
                array('db' => 'reg_code', 'dt' => 2),
                array('db' => 'cty_code', 'dt' => 3),
                array('db' => 'ent_ref', 'dt' => 4),
                array('db' => 'ent_name', 'dt' => 5),
                array('db' => 'ent_ref', 'dt' => 6, 'formatter' => function ($d) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
                    $detail = sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-eye"></i> %s</a>', $router->pathFor('entreprise.details', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'details'), Lexique::GetString(CUR_LANG, 'details'));
                    $edit = sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('entreprise.update', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
                    $delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteEntreprise(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
                    return sprintf($mask, $detail, $edit, $delete);
                })
            );
            $output = DataTable::complex($_POST, DBase::paramsPDO(), $table, $primaryKey, $columns);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
            $output['error'] = Lexique::GetString(CUR_LANG, an_error_occured) . $exc->getMessage();
        }
        return $this->renderJson($response, $output);
    }

    public function entrepDdchg(Request $request, Response $response)
    {
        $id = InputValidator::getInt('id');

        $output = new JsonResponse();
        $output->data = AgenceDbAdapter::getBranchesForOptions(null, ['entreprise' => $id]);
        $output->isSuccess = true;
        return $this->renderJson($response, $output->asArray());
    }

    public function create(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'create'));
        $city = CityDbAdapter::getById(SessionManager::get(SysConst::SELECTED_ITEM));
        $cities = CityDbAdapter::getCitiesForOptions(!is_null($city) ? $city->getId() : null);
        $regions = RegionDbAdapter::getRegionsForOptions(!is_null($city) ? $city->getRegion()->getId() : null);
        $countries = CountryDbAdapter::getCountriesForOptions(!is_null($city) ? $city->getRegion()->getCountry()->getId() : null);

        return $this->render($response, 'create', true, ['countries' => $countries, 'regions' => $regions, 'cities' => $cities]);
    }

    public function postCreate(Request $request, Response $response)
    {
        $model = new EntrepriseViewModel();

        $model = InputValidator::BuildModelFromRequest($model, $request);
        SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_ent_city);

        SessionManager::set(SysConst::MODEL, $model->toArray());
        try {
            if (ModelState::isValid()) {
                $nentrep = $model->convertToEntity();
                if (EntrepriseDbAdapter::save($nentrep)) {
                    $model = new EntrepriseViewModel();

                    SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_ent_city);
                    SessionManager::set(SysConst::MODEL, $model->toArray());
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
                    $dubdom = EntrepriseDbAdapter::getByDomain($nentrep->getDomain());
                    $dubref = EntrepriseDbAdapter::getByReference($nentrep->getReference());
                    if ($dubref) {
                        ModelState::setMessage('tb_ent_ref', Lexique::GetString(CUR_LANG, 'this-reference-is-already'));
                    }
                    if ($dubdom) {
                        ModelState::setMessage('tb_ent_domain_name', Lexique::GetString(CUR_LANG, 'this-domain-name-is-alrea'));
                    }
                    SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
                    SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
                }
            } else {
                SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors(), DANGER);
            }
        } catch (\Exception $exc) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }

        return $this->redirect($response, 'entreprise.create');
    }

    public function delete(Request $request, Response $response)
    {
        $code = base64_decode(InputValidator::GetString('code'));
        $output = new JsonResponse();
        $entrep = EntrepriseDbAdapter::getByReference($code);
        if ($entrep) {
            if (EntrepriseDbAdapter::delete(Entreprise::class, $entrep->getId())) {
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

    public function update(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'entreprise.read');
        }
        $entrep = EntrepriseDbAdapter::getByReference($code);
        if (is_null($entrep)) {
            return $this->redirect($response, 'entreprise.read');
        }

        $model = EntrepriseViewModel::buildFromEntity($entrep);
        $city = CityDbAdapter::getById($model->tb_ent_city);
        $cities = CityDbAdapter::getCitiesForOptions(!is_null($city) ? $city->getId() : null);
        $regions = RegionDbAdapter::getRegionsForOptions(!is_null($city) ? $city->getRegion()->getId() : null);
        $countries = CountryDbAdapter::getCountriesForOptions(!is_null($city) ? $city->getRegion()->getCountry()->getId() : null);

        return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray(), 'countries' => $countries, 'regions' => $regions, 'cities' => $cities]);
    }

    public function details(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'details'));
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'entreprise.read');
        }
        $entrep = EntrepriseDbAdapter::getByReference($code);
        if (is_null($entrep)) {
            return $this->redirect($response, 'entreprise.read');
        }

        return $this->render($response, 'details', true, [SysConst::MODEL => $entrep->toArray()]);
    }

    public function postUpdate(Request $request, Response $response)
    {
        $model = new EntrepriseViewModel();

        $model = InputValidator::BuildModelFromRequest($model, $request);
        $ref = $model->tb_ent_ref;
        SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_ent_city);

        SessionManager::set(SysConst::MODEL, $model->toArray());
        try {
            if (ModelState::isValid()) {
                $nentrep = $model->convertToEntity();
                if (EntrepriseDbAdapter::update($nentrep)) {
                    $model = new EntrepriseViewModel();

                    SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_ent_city);
                    SessionManager::set(SysConst::MODEL, $model->toArray());
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
                    $dubdom = EntrepriseDbAdapter::getByDomain($nentrep->getDomain());
                    $dubref = EntrepriseDbAdapter::getByReference($nentrep->getReference());
                    if ($dubref) {
                        ModelState::setMessage('tb_ent_ref', Lexique::GetString(CUR_LANG, 'this-reference-is-already'));
                    }
                    if ($dubdom) {
                        ModelState::setMessage('tb_ent_domain_name', Lexique::GetString(CUR_LANG, 'this-domain-name-is-alrea'));
                    }
                    SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
                    SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
                }
            } else {
                SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors(), DANGER);
            }
        } catch (\Exception $exc) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }


        return $this->redirect($response, 'entreprise.update', 302, ['code' => base64_encode($ref)]);
    }

    public function affectation(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'affectations'));

        return $this->render($response, 'list_affectation', true);
    }

    public function listAffectations(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'view_affectations';

            // Table's primary key
            $primaryKey = 'aff_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'ent_ref', 'dt' => 8),
                array('db' => 'ent_status', 'dt' => 9),
                array('db' => 'part_status', 'dt' => 10),
                array('db' => 'part_status', 'dt' => 0, 'formatter' => function ($d) {
                    $mask = '<span style="cursor:pointer;" class="badge badge-%s" data-toggle="tooltip" data-placement="top" title="%s">%s</span>';
                    $tbadge = $d ? 'primary' : 'danger';
                    $r = $d ? 'active' : 'inactive';
                    return sprintf($mask, $tbadge, Lexique::GetString(CUR_LANG, 'activate-disable'), Lexique::GetString(CUR_LANG, $r));
                }),
                array('db' => 'aff_datecreate', 'dt' => 1, 'formatter' => function ($d) {
                    return (new \DateTime($d))->format('Y-m-d H:i:s');
                }),
                array('db' => 'ent_name', 'dt' => 2),
                array('db' => 'part_code', 'dt' => 3),
                array('db' => 'part_name', 'dt' => 4),
                array('db' => 'aff_id', 'dt' => 5, 'formatter' => function ($d) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
								</ul>
							</div>';
                    $delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteAffectation(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
                    return sprintf($mask, $delete);
                })
            );
            $output = DataTable::complex($_POST, DBase::paramsPDO(), $table, $primaryKey, $columns);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
            $output['error'] = Lexique::GetString(CUR_LANG, an_error_occured) . $exc->getMessage();
        }
        return $this->renderJson($response, $output);
    }

    public function createAffectation(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'new-affectation'));
        $entreprises = EntrepriseDbAdapter::getEnterprisesForOptions();
        $partners = PartnerDbAdapter::getPartnersForOptions(null, ['status' => 1]);

        return $this->render($response, 'affectation', true, ['entreprises' => $entreprises, 'partners' => $partners]);
    }

    public function postCreateAffectation(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'new-affectation'));
        $model = new AffectationViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        try {
            if (ModelState::isValid()) {
                $naffect = $model->convertToEntity();
                if (AffectationDbAdapter::save($naffect)) {
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
                    SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
                }
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
        }
        $entreprises = EntrepriseDbAdapter::getEnterprisesForOptions();
        $partners = PartnerDbAdapter::getPartnersForOptions(null, ['status' => 1]);

        return $this->render($response, 'affectation', true, ['entreprises' => $entreprises, 'partners' => $partners, SysConst::MODEL_ERRORS => ModelState::getErrors()]);
    }

    public function deleteAffectation(Request $request, Response $response)
    {
        $id = base64_decode(InputValidator::GetString('id'));
        $output = new JsonResponse();

        if (AffectationDbAdapter::remove($id)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }

        return $this->renderJson($response, $output->asArray());
    }
}
