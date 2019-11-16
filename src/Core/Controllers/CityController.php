<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\CityDbAdapter;
use Digitalis\Core\Models\DbAdapters\CountryDbAdapter;
use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\DbAdapters\RegionDbAdapter;
use Digitalis\Core\Models\Entities\City;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\CityViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * CityController Gestionnaire des actions des utilisateurs
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class CityController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'cities'));

        return $this->render($response, 'index', true);
    }

    public function listcities(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'view_cities';

            // Table's primary key
            $primaryKey = 'cty_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'cty_id', 'dt' => 9),
                array('db' => 'reg_code', 'dt' => 0, 'formatter' => function ($d) {
                    //$city = RegionDbAdapter::getById($d);
                    //return !is_null($city) ? $city->getCode() : '...';
                    return $d;
                }),
                array('db' => 'cty_code', 'dt' => 1),
                array('db' => 'cty_label', 'dt' => 2),
                array('db' => 'cty_code', 'dt' => 3, 'formatter' => function ($d) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
                    $edit =  sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('city.update', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
                    $delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteCity(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
                    return sprintf($mask, $edit, $delete);
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

    public function create(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'create'));
        $regions = RegionDbAdapter::getRegionsForOptions(SessionManager::get(SysConst::SELECTED_ITEM));

        return $this->render($response, 'create', true, ['regions' => $regions]);
    }

    public function postCreate(Request $request, Response $response)
    {
        $model = new CityViewModel();

        $model = InputValidator::BuildModelFromRequest($model, $request);
        SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_cty_region);

        SessionManager::set(SysConst::MODEL, $model->toArray());
        try {
            if (ModelState::isValid()) {
                $ncity = $model->convertToEntity();
                if (CityDbAdapter::save($ncity)) {
                    $model = new CityViewModel();

                    SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_cty_region);
                    SessionManager::set(SysConst::MODEL, $model->toArray());
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
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

        return $this->redirect($response, 'city.create');
    }

    public function delete(Request $request, Response $response)
    {
        $code = base64_decode(InputValidator::GetString('code'));
        $output = new JsonResponse();
        $city = CityDbAdapter::getByCode($code);
        if ($city) {
            if (CityDbAdapter::delete(City::class, $city->getId())) {
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

    public function cityDdchg(Request $request, Response $response)
    {
        $id = InputValidator::getInt('id');
        $branch = InputValidator::getInt('branch');

        $output = new JsonResponse();
        $output->data = $branch == 1 ? 'Les agences' : EntrepriseDbAdapter::getEnterprisesForOptions(null, ['city' => $id]);
        $output->isSuccess = true;
        return $this->renderJson($response, $output->asArray());
    }

    public function update(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'city.read');
        }
        $city = CityDbAdapter::getByCode($code);
        if (is_null($city)) {
            return $this->redirect($response, 'city.read');
        }

        $model = CityViewModel::buildFromEntity($city);
        $regions = RegionDbAdapter::getRegionsForOptions($model->tb_cty_region);
        return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray(), 'regions' => $regions]);
    }

    public function postUpdate(Request $request, Response $response)
    {
        $model = new CityViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        $code = $model->tb_cty_code;
        try {
            if (ModelState::isValid()) {
                $ncity = $model->convertToEntity();
                if (CityDbAdapter::update($ncity)) {
                    $model = new CityViewModel();
                    SessionManager::set(SysConst::MODEL, $model->toArray());
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
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

        return $this->redirect($response, 'city.update', 302, ['code' => base64_encode($code)]);
    }
}
