<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\CountryDbAdapter;
use Digitalis\Core\Models\DbAdapters\RegionDbAdapter;
use Digitalis\Core\Models\Entities\Country;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\CountryViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * CountryController Description of CountryController here
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class CountryController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'countries'));

        return $this->render($response, 'index', true);
    }

    public function listcountry(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'countries';

            // Table's primary key
            $primaryKey = 'coun_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'coun_id', 'dt' => 9),
                array('db' => 'coun_alpha2', 'dt' => 0),
                array('db' => 'coun_alpha3', 'dt' => 1),
                array('db' => 'coun_enname', 'dt' => 2),
                array('db' => 'coun_frname', 'dt' => 3),
                array('db' => 'coun_dialcode', 'dt' => 4),
                array('db' => 'coun_id', 'dt' => 5, 'formatter' => function ($d, $row) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
                    $edit = sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('country.update', ['id' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
                    $delete = sprintf('<a href="#" class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteCountry(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($row['coun_alpha3']), Lexique::GetString(CUR_LANG, 'delete'));
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

        return $this->render($response, 'create', true);
    }

    public function postCreate(Request $request, Response $response)
    {
        $model = new CountryViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);

        SessionManager::set(SysConst::MODEL, $model->toArray());
        try {
            if (ModelState::isValid()) {
                $ncountry = $model->convertToEntity();
                if (CountryDbAdapter::save($ncountry)) {
                    $model = new CountryViewModel();
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

        return $this->redirect($response, 'country.create');
    }

    public function delete(Request $request, Response $response)
    {
        $code = base64_decode(InputValidator::getString('code'));
        $output = new JsonResponse();
        $country = CountryDbAdapter::getByCode($code, $code);
        if ($country) {
            if (CountryDbAdapter::delete(Country::class, $country->getId())) {
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

    public function countryDdchg(Request $request, Response $response)
    {
        $id = InputValidator::getInt('id');
        $output = new JsonResponse();
        $output->data = RegionDbAdapter::getRegionsForOptions(null, ['country' => $id]);
        $output->isSuccess = true;
        return $this->renderJson($response, $output->asArray());
    }

    public function update(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));
        $id = base64_decode($request->getAttribute('id'));
        if (is_null($id)) {
            return $this->redirect($response, 'country.read');
        }
        $country = CountryDbAdapter::getById($id);
        if (is_null($country)) {
            return $this->redirect($response, 'country.read');
        }
        $model = CountryViewModel::buildFromEntity($country);
        return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray()]);
    }

    public function postUpdate(Request $request, Response $response)
    {
        $model = new CountryViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        $id = $model->tb_coun_id;
        try {
            if (ModelState::isValid()) {
                $ncountry = $model->convertToEntity();
                if (CountryDbAdapter::update($ncountry)) {
                    $model = new CountryViewModel();
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

        return $this->redirect($response, 'country.update', 302, ['id' => base64_encode($id)]);
    }
}
