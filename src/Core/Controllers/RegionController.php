<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\CityDbAdapter;
use Digitalis\Core\Models\DbAdapters\CountryDbAdapter;
use Digitalis\Core\Models\DbAdapters\RegionDbAdapter;
use Digitalis\Core\Models\Entities\Region;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\RegionViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * RegionController Controleur de gestion des régions
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class RegionController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'regions'));

        return $this->render($response, 'index', true);
    }

    public function listregions(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'view_regions';

            // Table's primary key
            $primaryKey = 'reg_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'reg_id', 'dt' => 9),
                array('db' => 'coun_enname', 'dt' => 10),
                array('db' => 'coun_frname', 'dt' => 0, 'formatter' => function ($d, $row) {
                    return strtolower(CUR_LANG) == 'en' ? $row['coun_enname'] : $d;
                }),
                array('db' => 'reg_code', 'dt' => 1),
                array('db' => 'reg_label', 'dt' => 2),
                array('db' => 'reg_code', 'dt' => 3, 'formatter' => function ($d) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
                    $edit = sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('region.update', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
                    $delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteRegion(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
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
        $countries = CountryDbAdapter::getCountriesForOptions(SessionManager::get(SysConst::SELECTED_ITEM));

        return $this->render($response, 'create', true, ['countries' => $countries]);
    }

    public function postCreate(Request $request, Response $response)
    {
        $model = new RegionViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_rg_country);

        SessionManager::set(SysConst::MODEL, $model->toArray());
        try {
            if (ModelState::isValid()) {
                $nregion = $model->convertToEntity();
                if (RegionDbAdapter::save($nregion)) {
                    $model = new RegionViewModel();

                    SessionManager::set(SysConst::SELECTED_ITEM, $model->tb_rg_country);
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

        return $this->redirect($response, 'region.create');
    }

    public function delete(Request $request, Response $response)
    {
        $code = base64_decode(InputValidator::getString('code'));
        $output = new JsonResponse();
        $region = RegionDbAdapter::getByCode($code);
        if ($region) {
            if (RegionDbAdapter::delete(Region::class, $region->getId())) {
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

    public function regionDdchg(Request $request, Response $response)
    {
        $id = InputValidator::getInt('id');
        $output = new JsonResponse();
        $output->data = CityDbAdapter::getCitiesForOptions(null, ['region' => $id]);
        $output->isSuccess = true;
        return $this->renderJson($response, $output->asArray());
    }

    public function update(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'region.read');
        }
        $region = RegionDbAdapter::getByCode($code);
        if (is_null($region)) {
            return $this->redirect($response, 'region.read');
        }

        $model = RegionViewModel::buildFromEntity($region);
        $countries = CountryDbAdapter::getCountriesForOptions($model->tb_rg_country);
        return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray(), 'countries' => $countries]);
    }

    public function postUpdate(Request $request, Response $response)
    {
        $model = new RegionViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        $code = $model->tb_rg_code;
        try {
            if (ModelState::isValid()) {
                $nregion = $model->convertToEntity();
                if (RegionDbAdapter::update($nregion)) {
                    $model = new RegionViewModel();
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

        return $this->redirect($response, 'region.update', 302, ['code' => base64_encode($code)]);
    }
}
