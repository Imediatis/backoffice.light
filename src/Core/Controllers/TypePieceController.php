<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\TypePieceDbAdapter;
use Digitalis\Core\Models\Entities\TypePiece;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\TypePieceViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * TypePieceController Gestionnaire des actions des utilisateur
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class TypePieceController extends Controller
{
	public function __construct($container)
	{
		parent::__construct($container);
		parent::setCurrentController(__class__);
	}

	public function index(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'type-of-document'));
		$tpieces = TypePieceDbAdapter::getAssoc();
		return $this->render($response, 'index', true, ['tpieces' => $tpieces]);
	}

	public function listTPieces(Request $request, Response $response)
	{
		$output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
		try {
			// DB table to use
			$table = 'documenttypes';

			// Table's primary key
			$primaryKey = 'tdoc_id';
			$router = $this->router;
			// indexes
			$columns = array(
				array('db' => 'tdoc_code', 'dt' => 0),
				array('db' => 'tdoc_label', 'dt' => 1),
				array('db' => 'tdoc_code', 'dt' => 2, 'formatter' => function ($d) use ($router) {
					$mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
					$edit =  sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('tpiece.update', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
					$delete = sprintf('<a class="text-danger" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteTpiece(\'%s\')"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), base64_encode($d), Lexique::GetString(CUR_LANG, 'delete'));
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
		$this->title(Lexique::GetString(CUR_LANG, 'new-type-of-document'));

		return $this->render($response, 'create', true);
	}

	public function postCreate(Request $request, Response $response)
	{
		$model = new TypePieceViewModel();
		$model = InputValidator::BuildModelFromRequest($model, $request);
		if (ModelState::isValid()) {
			$ntpiece = $model->convertToEntity();
			if (TypePieceDbAdapter::save($ntpiece)) {
				SessionManager::set(SysConst::FLASH, operation_success);
				$model = new TypePieceViewModel();
			} else {
				SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
			}
		} else {
			SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors(), DANGER);
		}
		SessionManager::set(SysConst::MODEL, $model->toArray());
		return $this->redirect($response, 'tpiece.create');
	}

	public function update(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'update-type-of-document'));
		$code = base64_decode($request->getAttribute('code'));
		if (Data::isDecodeString($code)) {
			$tpiece = TypePieceDbAdapter::getByCode($code);
			if ($tpiece) {
				$model = TypePieceViewModel::buildFromEntity($tpiece);
				return $this->render($response, 'update', true, [SysConst::MODEL => $model->toArray()]);
			} else {
				return $this->redirect($response, 'tpiece.read');
			}
		} else {
			return $this->redirect($response, 'tpiece.read');
		}
	}

	public function postUpdate(Request $request, Response $response)
	{
		$model = new TypePieceViewModel();
		$model = InputValidator::BuildModelFromRequest($model, $request);
		$code = $model->tb_tdoc_code;
		if (ModelState::isValid()) {
			$ntpiece = $model->convertToEntity();
			if (TypePieceDbAdapter::update($ntpiece)) {
				SessionManager::set(SysConst::FLASH, operation_success);
				$model = new TypePieceViewModel();
			} else {
				SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
			}
		} else {
			SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors(), DANGER);
		}
		SessionManager::set(SysConst::MODEL, $model->toArray());
		return $this->redirect($response, 'tpiece.update', 302, ['code' => base64_encode($code)]);
	}

	public function delete(Request $request, Response $response)
	{
		$code = base64_decode(InputValidator::GetString('code'));
		$output = new JsonResponse();
		$tpiece = TypePieceDbAdapter::getByCode($code);
		if ($tpiece) {
			if (TypePieceDbAdapter::delete(TypePiece::class, $tpiece->getId())) {
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
