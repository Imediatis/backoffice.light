<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\DbAdapters\TariffGridDbAdapter;
use Digitalis\Core\Models\Entities\TariffGrid;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\TariffGridViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * GrilleTariffaireController Gestionnaire des actions des utilisateurs
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TariffGridController extends Controller
{
	public function __construct($container)
	{
		parent::__construct($container);
		parent::setCurrentController(__class__);
	}

	public function index(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'management-of-tariff-grid'));

		return $this->render($response, 'index', true);
	}

	public function listTariffGrid(Request $request, Response $response)
	{
		$output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
		try {
			// DB table to use
			$table = 'view_tariffgrids';

			// Table's primary key
			$primaryKey = 'trfg_id';
			$router = $this->router;
			// indexes
			$columns = array(
				array('db' => 'trfg_id', 'dt' => 8),
				array('db' => 'trfg_nature', 'dt' => 9),
				array('db' => 'ent_id', 'dt' => 10),
				array('db' => 'ent_ref', 'dt' => 11),
				array('db' => 'trfg_min', 'dt' => 0, 'formatter' => function ($d) {
					return number_format($d, 0, ",", " ");
				}),
				array('db' => 'trfg_max', 'dt' => 1, 'formatter' => function ($d) {
					return number_format($d, 0, ",", " ");
				}),
				array('db' => 'trfg_value', 'dt' => 2, 'formatter' => function ($d, $row) {
					return number_format($d, 2, ",", " ") . ($row['trfg_nature'] != 1 ? "%" : "");
				}),
				array('db' => 'ent_name', 'dt' => 3),
				array('db' => 'trfg_id', 'dt' => 4, 'formatter' => function ($d) {
					$mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
								</ul>
							</div>';
					$delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteTariffGrid(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
					return sprintf($mask,  $delete);
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

	public function listTariffGridCompany(Request $request, Response $response)
	{
		$output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
		try {
			// DB table to use
			$table = 'view_tariffgrids';
			$entId = InputValidator::getInt('entId');
			// Table's primary key
			$primaryKey = 'trfg_id';
			// indexes
			$columns = array(
				array('db' => 'trfg_id', 'dt' => 8),
				array('db' => 'trfg_nature', 'dt' => 9),
				array('db' => 'ent_name', 'dt' => 5),
				array('db' => 'ent_id', 'dt' => 10),
				array('db' => 'ent_ref', 'dt' => 11),
				array('db' => 'trfg_min', 'dt' => 0, 'formatter' => function ($d) {
					return number_format($d, 0, ",", " ");
				}),
				array('db' => 'trfg_max', 'dt' => 1, 'formatter' => function ($d) {
					return number_format($d, 0, ",", " ");
				}),
				array('db' => 'trfg_value', 'dt' => 2, 'formatter' => function ($d, $row) {
					return number_format($d, 2, ",", " ") . ($row['trfg_nature'] != 1 ? "%" : "");
				}),
				array('db' => 'trfg_id', 'dt' => 3, 'formatter' => function ($d) {
					$mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
								</ul>
							</div>';
					$delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteTariffGrid(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
					return sprintf($mask,  $delete);
				})
			);
			$entId = is_null($entId) ? 0 : $entId;

			$where = "ent_id = $entId";
			$output = DataTable::complex($_POST, DBase::paramsPDO(), $table, $primaryKey, $columns, $where, $where);
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
			$output['error'] = Lexique::GetString(CUR_LANG, an_error_occured);
		}
		return $this->renderJson($response, $output);
	}

	public function create(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'new-price'));
		$entrises = EntrepriseDbAdapter::getEnterprisesForOptions();
		$model = new TariffGridViewModel();
		return $this->render($response, 'create', true, ['entreprises' => $entrises, SysConst::MODEL => $model->toArray()]);
	}

	public function postCreate(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'management-of-tariff-grid'));
		$model = new TariffGridViewModel();
		$entId = $model->ld_ent_id;
		$model = InputValidator::BuildModelFromRequest($model, $request);
		$model->validateModel();
		try {
			if (ModelState::isValid()) {
				if (TariffGridDbAdapter::save($model->convertToEntity())) {
					SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
					$model = new TariffGridViewModel();
					$model->ld_ent_id = $entId;
				} else {
					SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
				}
			} else {
				SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
			}
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
			SessionManager::set(SysConst::FLASH, "Une erreur s'est produite lors du traitement", DANGER);
		}
		$entreprises = EntrepriseDbAdapter::getEnterprisesForOptions($model->ld_ent_id);

		return $this->render($response, 'create', true, ['entreprises' => $entreprises, SysConst::MODEL => $model->toArray(), SysConst::MODEL_ERRORS => ModelState::getErrors()]);
	}

	public function delete(Request $request, Response $response)
	{
		$id = base64_decode(InputValidator::getString('id'));
		$output = new JsonResponse();
		$tarif = TariffGridDbAdapter::getById($id);
		if ($tarif) {
			if (TariffGridDbAdapter::delete(TariffGrid::class, $tarif->getId())) {
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
}
