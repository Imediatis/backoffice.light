<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Models\DbAdapters\ProfileDbAdapter;
use Digitalis\Core\Models\ViewModels\ProfileViewModel;
use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\Entities\Profile;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * ProfileController Controleur pour la gestion des profiles
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ProfileController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    /**
     * génère la page d'affichage des profiles
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'user-s-profile'));

        $this->render($response, "index");
    }

    /**
     * Methode appelé par le Datable pour afficher les profile
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function listProfile(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'profile';

            // Table's primary key
            $primaryKey = 'prof_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'prof_id', 'dt' => 9),

                array('db' => 'prof_datecreate', 'dt' => 0, 'formatter' => function ($d) {
                    return (new \DateTime($d))->format('Y-m-d H:i:s');
                }),
                array('db' => 'prof_status', 'dt' => 1, 'formatter' => function ($d, $row) {
                    $mask = '<span style="cursor:pointer;" class="badge badge-%s" data-toggle="tooltip" data-placement="top" title="%s" onclick="activateProfile(%d)">%s</span>';
                    $tbadge = $d ? 'primary' : 'danger';
                    $r = $d ? 'active' : 'inactive';
                    return sprintf($mask, $tbadge, Lexique::GetString(CUR_LANG, 'activate-disable'), $row['prof_id'], Lexique::GetString(CUR_LANG, $r));
                }),
                array('db' => 'prof_code', 'dt' => 2),
                array('db' => 'prof_desc', 'dt' => 3),
                array('db' => 'prof_id', 'dt' => 4, 'formatter' => function ($d) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
                    $edit = sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-pencil"></i> %s</a>', $router->pathFor('profile.update', ['id' => $d]), Lexique::GetString(CUR_LANG, 'update'), Lexique::GetString(CUR_LANG, 'update'));
                    $delete =  sprintf('<a href="#" class="text-success" data-toggle="tooltip" data-placement="top" title="%s" onclick="deleteProfile(%d)"><i class="fa fa-trash"></i> %s</a>', Lexique::GetString(CUR_LANG, 'delete'), $d, Lexique::GetString(CUR_LANG, 'delete'));
                    $habilitations = sprintf('<a href="%s" data-toggle="tooltip" data-placement="top" title="%s"><i class="fa fa-users"></i> %s</a>', '#', Lexique::GetString(CUR_LANG, 'set-habilitations'), Lexique::GetString(CUR_LANG, 'set-habilitations'));
                    return sprintf($mask, $edit, $habilitations, $delete);
                })
            );
            $output = DataTable::complex($_POST, DBase::paramsPDO(), $table, $primaryKey, $columns);
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
            $output['error'] = 'Une erreur s\'produite';
        }
        return $this->renderJson($response, $output);
    }

    public function create(Request $request, Response $response)
    {
        $this->render($response, "create");
    }

    public function postCreate(Request $request, Response $response)
    {
        try {
            $model = new ProfileViewModel();
            $model = InputValidator::BuildModelFromRequest($model, $request);
            SessionManager::set(SysConst::MODEL, $model->toArray());
            if (ModelState::isValid()) {
                $nprofil = $model->convertToEntity();
                if (ProfileDbAdapter::save($nprofil)) {
                    SessionManager::remove(SysConst::MODEL);
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
                    SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
                }
            } else {
                SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
            }
        } catch (\Exception $exc) {
            \Digitalis\Core\Models\Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            \Digitalis\Core\Handlers\ErrorHandler::writeLog($exc);
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
        }
        return $this->redirect($response, 'profile.create');
    }

    public function activate(Request $request, Response $response)
    {
        $id = InputValidator::getInt('id');
        $output = new JsonResponse();
        if (ProfileDbAdapter::activate($id)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }
        return $this->renderJson($response, $output->asArray());
    }

    public function update(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));
        $id = filter_var($request->getAttribute('id'), FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            return $this->redirect($response, 'profile.read');
        }
        $profile = ProfileDbAdapter::getById($id);
        if (!$profile) {
            return $this->redirect($response, 'profile.read');
        }
        $model = ProfileViewModel::buildFromEntity($profile);

        return $this->render($response, 'update', false, [SysConst::MODEL => $model->toArray()]);
    }

    public function postUpdate(Request $request, Response $response)
    {
        try {
            $model = new ProfileViewModel();
            $model = InputValidator::BuildModelFromRequest($model, $request);

            if (ModelState::isValid()) {
                $nprofil = $model->convertToEntity();
                if (ProfileDbAdapter::update($nprofil)) {
                    $model = new ProfileViewModel();
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                } else {
                    SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
                }
            } else {
                SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
            }
        } catch (\Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
            ErrorHandler::writeLog($exc);
            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, an_error_occured));
        }
        return $this->render($response, 'update', false, ['Model' => $model->toArray()]);
    }

    public function delete(Request $request, Response $response)
    {
        $id = InputValidator::getInt('id');
        $output = new JsonResponse();

        if (ProfileDbAdapter::delete(Profile::class, $id)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }
        return $this->renderJson($response, $output->asArray());
    }
}
