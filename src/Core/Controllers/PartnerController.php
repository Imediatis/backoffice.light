<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\PartnerDbAdapter;
use Digitalis\Core\Models\Entities\Partner;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\PartnerViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * PartnerController Gestionnaire des actions des utilisateurs
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class PartnerController extends Controller
{
	public function __construct($container)
	{
		parent::__construct($container);
		parent::setCurrentController(__class__);
	}

	public function index(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'partner-s-management'));

		return $this->render($response, 'index', true);
	}

	public function listPartners(Request $request, Response $response)
	{
		$output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
		try {
			// DB table to use
			$table = 'partners';

			// Table's primary key
			$primaryKey = 'part_id';
			$router = $this->router;
			// indexes
			$columns = array(
				array('db' => 'part_id', 'dt' => 8),
				array('db' => 'part_status', 'dt' => 0, 'formatter' => function ($d, $row) {
					$mask = '<span style="cursor:pointer;" class="badge badge-%s" data-toggle="tooltip" data-placement="top" title="%s" onclick="activatPartner(\'%s\',\'%s\')">%s</span>';
					$tbadge = $d ? 'primary' : 'danger';
					$r = $d ? 'active' : 'inactive';
					$msg = Lexique::GetString(CUR_LANG, 'would-you') . ' ' . Lexique::GetString(CUR_LANG, $d ? 'deactivate' : 'activate') . ' ' . Lexique::GetString(CUR_LANG, 'this-item') . '?';
					return sprintf($mask, $tbadge, Lexique::GetString(CUR_LANG, 'activate-disable'), base64_encode($row['part_id']), $msg, Lexique::GetString(CUR_LANG, $r));
				}),
				array('db' => 'part_code', 'dt' => 1),
				array('db' => 'part_name', 'dt' => 2),
				array('db' => 'part_id', 'dt' => 3, 'formatter' => function ($d) use ($router) {
					$mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
					$edit = sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('partner.update', ['id' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
					$delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deletePartner(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
					return sprintf($mask, $edit, $delete);
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

	public function create(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'new-partner'));

		return $this->render($response, 'create', true);
	}

	public function postCreate(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'new-partner'));
		$model = new PartnerViewModel();
		$model = InputValidator::BuildModelFromRequest($model, $request);
		$success = false;

		SessionManager::set(SysConst::MODEL, $model->toArray());
		try {
			if (ModelState::isValid()) {
				$npartner = $model->convertToEntity();
				if (PartnerDbAdapter::save($npartner)) {
					$model = new PartnerViewModel();
					SessionManager::set(SysConst::MODEL, $model->toArray());
					SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
					$success = true;
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

		return $success ?  $this->redirect($response, 'partner.create') : $this->render($response, 'create', true, [SysConst::MODEL => $model->toArray()]);
	}

	public function update(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'update-partner'));
		$id = base64_decode($request->getAttribute('id'));
		$partner = PartnerDbAdapter::getById($id);
		if (!$partner) {
			return $this->redirect($response, 'partner.read');
		}
		$model = PartnerViewModel::buildFromEntity($partner);

		return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray()]);
	}

	public function postUpdate(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'update-partner'));
		$model = new PartnerViewModel();
		$model = InputValidator::BuildModelFromRequest($model, $request);

		try {
			if (ModelState::isValid()) {
				$npartner = $model->convertToEntity();
				if (PartnerDbAdapter::update($npartner)) {
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

		return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray()]);
	}

	public function delete(Request $request, Response $response)
	{
		$id = base64_decode(InputValidator::getString('id'));
		$output = new JsonResponse();
		$partner = PartnerDbAdapter::getById($id);
		if ($partner) {
			if (PartnerDbAdapter::delete(Partner::class, $partner->getId())) {
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

	public function activate(Request $request, Response $response)
	{
		$id = base64_decode(InputValidator::getString('id'));
		$output = new JsonResponse();
		if (PartnerDbAdapter::setStatus($id)) {
			$output->message = Lexique::GetString(CUR_LANG, operation_success);
		} else {
			$output->isSuccess = false;
			$output->message = Data::getErrorMessage();
		}

		return $this->renderJson($response, $output->asArray());
	}
}
