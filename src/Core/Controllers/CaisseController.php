<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\Entities\Caisse;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * CaisseController Gestionnaire d'interface des caisses
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class CaisseController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'tellers'));

        return $this->render($response, 'index', true);
    }

    public function listCaisses(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'view_boxes';

            // Table's primary key
            $primaryKey = 'box_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'box_id', 'dt' => 9),
                array('db' => 'agc_label', 'dt' => 10),
                array('db' => 'box_isopened', 'dt' => 0, 'formatter' => function ($d, $row) {
                    $mask = '<span class="badge badge-%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-%s"></i></span>';
                    $color = $d == 1 ? "primary" : "danger";
                    $title = $d == 1 ? "opened" : "closed";
                    $icon = $d == 1 ? "check" : "times";
                    return sprintf($mask, $color, Lexique::GetString(CUR_LANG, $title), $icon);
                }),
                array('db' => 'box_status', 'dt' => 1, 'formatter' => function ($d) {
                    $mask = '<span class="badge badge-%s" data-toggle="tooltip" data-placement="top" title="%s">%s</span>';
                    $color = $d == 0 ? "danger" : "primary";
                    $title = Lexique::GetString(CUR_LANG, $d == 0 ? "deactivated" : "activated");
                    return sprintf($mask, $color, $title, $title);
                }),
                array('db' => 'box_datecreate', 'dt' => 2, 'formatter' => function ($d) {
                    return (new \DateTime($d))->format('Y-m-d H:i:s');
                }),
                array('db' => 'agc_label', 'dt' => 3),
                array('db' => 'agc_code', 'dt' => 4),
                array('db' => 'box_code', 'dt' => 5),
                array('db' => 'box_key', 'dt' => 6),
                array('db' => 'box_code', 'dt' => 7, 'formatter' => function ($d, $row) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action">...</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-agence">
									<li>%s</li>
									<li>%s</li>
									<li>%s</li>
									<li>%s</li>
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
                            </div>';

                    $routeOpenClose = $router->pathFor('box.openclose', ['code' => base64_encode($d), 'action' => base64_encode($row['box_isopened'] ? 'close' : 'open')]);
                    $textoc = Lexique::GetString(CUR_LANG, $row['box_isopened'] ? 'close' : 'open');
                    $openclose = sprintf('<a href="%s" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-exchange"></i> %s</a>', $routeOpenClose, $textoc, $textoc);
                    $edit = sprintf('<a href="%s" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('box.update', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
                    $regenkey = sprintf('<a href="#" class="text-danger" onclick="regkBox(\'%s\')" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-qrcode"></i> %s</a>', base64_encode($d), Lexique::GetString(CUR_LANG, 'reset-key'), Lexique::GetString(CUR_LANG, 'reset-key'));
                    $detail = sprintf('<a href="%s" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-eye"></i> %s</a>', $router->pathFor('box.details', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'details'), Lexique::GetString(CUR_LANG, 'details'));
                    $affectOp = sprintf('<a href="%s" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-user-plus"></i> %s</a>', $router->pathFor('aoperator.setoperator', ['code' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'set-operator'), Lexique::GetString(CUR_LANG, 'operator'));
                    $msgDelete = Lexique::GetString(CUR_LANG, 'would-you-delete-this-ite') . '<br/>Agence : <strong>' . $row['agc_label'] . ' (' . $row['agc_code'] . ')</strong>;<br/>Caisse : <strong>' . $d . '</strong> ';
                    $delete = sprintf('<a href="#" class="text-danger" onclick="deleteBox(\'%s\',\'%s\')" data-toggle="tooltip" data-placement="left" title="%s"><i class="fa fa-trash"></i> %s</a>', base64_encode($d), $msgDelete, Lexique::GetString(CUR_LANG, 'delete'), Lexique::GetString(CUR_LANG, 'delete'));
                    return sprintf($mask, $openclose, $edit, $regenkey, $detail, $affectOp, $delete);
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

    public function delete(Request $request, Response $response)
    {
        $code = base64_decode(InputValidator::GetString('code'));
        $output = new JsonResponse();
        $caisse = CaisseDbAdapter::getByCode($code);
        if ($caisse) {
            if (CaisseDbAdapter::delete(Caisse::class, $caisse->getId())) {
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

        if (CaisseDbAdapter::changeBoxKey($code)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }

        return $this->renderJson($response, $output->asArray());
    }

    public function boxOpenClose(Request $request, Response $response)
    {
        $action = base64_decode($request->getAttribute('action'));
        if (is_null($action)) {
            return $this->redirect($response, 'box.read');
        }
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'box.read');
        }
        $caisse = CaisseDbAdapter::getByCode($code);
        if (is_null($caisse)) {
            return $this->redirect($response, 'box.read');
        }
        $this->title(Lexique::GetString(CUR_LANG, $action) . ' ' . Lexique::GetString(CUR_LANG, 'teller'));

        return $this->render($response, 'boxopenclose', true, [SysConst::MODEL => $caisse->toArray(), 'labelAction' => $caisse->getIsOpened() ? "close" : "open", 'color' => $action == 'open' ? 'primary' : 'danger']);
    }

    public function pboxOpenClose(Request $request, Response $response)
    {
        $action = InputValidator::getString('action');
        $code = InputValidator::getString('code');
        $caisse = CaisseDbAdapter::getByCode($code);
        if (is_null($caisse)) {
            return $this->redirect($response, 'box.read');
        }
        if (CaisseDbAdapter::openCloseBox($code)) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
            $action = $caisse->getIsOpened() ? 'close' : 'open';
        } else {
            SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
        }

        return $this->redirect($response, 'box.openclose', 301, ['code' => base64_encode($code), 'action' => base64_encode($action)]);
    }

    public function details(Request $request, Response $response)
    {
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'box.read');
        }
        $caisse = CaisseDbAdapter::getByCode($code);
        if (is_null($caisse)) {
            return $this->redirect($response, 'box.read');
        }
        $this->title(Lexique::GetString(CUR_LANG, 'details'));

        return $this->render($response, 'details', true, [SysConst::MODEL => $caisse->toArray()]);
    }

    public function update(Request $request, Response $response)
    {
        $code = base64_decode($request->getAttribute('code'));
        if (is_null($code)) {
            return $this->redirect($response, 'box.read');
        }
        $caisse = CaisseDbAdapter::getByCode($code);
        if (is_null($caisse)) {
            return $this->redirect($response, 'box.read');
        }
        $this->title(Lexique::GetString(CUR_LANG, 'update'));

        return $this->render($response, 'update', true, [SysConst::MODEL => $caisse->toArray()]);
    }

    public function postupdate(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));

        $maxamout = InputValidator::getInt('tb_max_amount');
        $idCaisse = InputValidator::getInt('tb_box_id');
        $statutCaisse = InputValidator::getInt('tb_box_active');
        $statutCaisse = !is_null($statutCaisse) ? $statutCaisse : 0;
        if (CaisseDbAdapter::updateMaxAmount($idCaisse, $maxamout, $statutCaisse)) {
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
        } else {
            SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
        }

        $caisse = CaisseDbAdapter::getById($idCaisse);
        if (is_null($caisse)) {
            return $this->redirect($response, 'box.read');
        }

        return $this->render($response, 'update', true, [SysConst::MODEL => $caisse->toArray()]);
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
