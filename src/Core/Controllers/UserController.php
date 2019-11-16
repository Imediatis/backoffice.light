<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Models\DbAdapters\ProfileDbAdapter;
use Digitalis\Core\Models\DbAdapters\UserDbAdapter;
use Digitalis\Core\Models\ViewModels\UserViewModel;
use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\Mail;
use Digitalis\Core\Models\MailWorker;
use Digitalis\Core\Models\Security\ChangePwdViewModel;
use Digitalis\Core\Models\Security\LoggedUser;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;


/**
 * UserController Gestionnaire des utilisateurs
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class UserController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    public function index(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'users'));
        return $this->render($response, "index");
    }


    public function listUser(Request $request, Response $response)
    {
        $output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        try {
            // DB table to use
            $table = 'syst_user';

            // Table's primary key
            $primaryKey = 'usr_id';
            $router = $this->router;
            // indexes
            $columns = array(
                array('db' => 'usr_id', 'dt' => 9),
                array('db' => 'usr_fname', 'dt' => 10),
                array('db' => 'usr_lastlogout', 'dt' => 0, 'formatter' => function ($d, $row) {
                    $maskcon = '<span><i class="g-ml-5 g-font-size-18 fa fa-%s text-%s" data-toggle="tooltip" data-placement="top" title="%s"></i></span>';
                    $out = !is_null($d) ? new \DateTime($d) : null;
                    $in = !is_null($row['usr_lastlogin']) ? new \DateTime($row['usr_lastlogin']) : null;

                    $fa = 'check-circle';
                    $ctexte = 'info';
                    $titlec = Lexique::GetString(CUR_LANG, 'logged');

                    if (is_null($in)) {
                        $fa = 'exclamation-circle';
                        $ctexte = 'warning';
                        $titlec = Lexique::GetString(CUR_LANG, 'never-logged');
                    } elseif ($in < $out) {
                        $fa = "minus-circle";
                        $ctexte = "danger";
                        $titlec = Lexique::GetString(CUR_LANG, 'logged-out');
                    }
                    return sprintf($maskcon, $fa, $ctexte, $titlec);
                }),
                array('db' => 'usr_status', 'dt' => 1, 'formatter' => function ($d, $row) {
                    //STATUT 
                    //0=Désactivé/supprimé danger
                    //2=Dois changer le mot de passe warning (à ce stade peut être uniquement désactivé)
                    //1=Actif success
                    $fncmask = "activateUser('%s','%s')";

                    $mask = '<span style="cursor:pointer" data-toggle="tooltip" data-placement="top" title="%s" class="badge badge-%s" onclick="%s" >%s</span>';
                    switch ($d) {
                        case 0:
                            $badge = "danger";
                            $title = Lexique::GetString(CUR_LANG, 'disabled');
                            $text = Lexique::getCode(CUR_LANG, 'disabled');
                            $sfncmask = sprintf($fncmask, $row['usr_login'], Lexique::GetString(CUR_LANG, 'would-you-activate-this-u'));
                            break;
                        case 1:
                            $badge = "primary";
                            $title = Lexique::GetString(CUR_LANG, 'active');
                            $text = Lexique::getCode(CUR_LANG, 'active');
                            $sfncmask = sprintf($fncmask, $row['usr_login'], Lexique::GetString(CUR_LANG, 'would-you-disable-this-us'));
                            break;
                        default:
                            $badge = "warning";
                            $title = Lexique::GetString(CUR_LANG, 'Chg pwd');
                            $text = Lexique::getCode(CUR_LANG, 'Chg pwd');
                            $sfncmask = sprintf($fncmask, $row['usr_login'], Lexique::GetString(CUR_LANG, 'would-you-disable-this-us'));
                            break;
                    }
                    return sprintf($mask, $title, $badge, $sfncmask, $text);
                }),
                array('db' => 'usr_lname', 'dt' => 2, 'formatter' => function ($d, $row) {
                    return trim($row['usr_fname'] . ' ' . $d);
                }),
                array('db' => 'usr_login', 'dt' => 3),
                array('db' => 'prof_id', 'dt' => 4, 'formatter' => function ($d) {
                    $profile = ProfileDbAdapter::getById($d);
                    return $profile ? $profile->getDescription() : "";
                }),
                array('db' => 'usr_lastlogin', 'dt' => 5, 'formatter' => function ($d) {
                    if ($d) {
                        return (new \DateTime($d))->format('Y-m-d H:i:s');
                    } else {
                        return '';
                    }
                }),
                array('db' => 'usr_lastiplogin', 'dt' => 6),
                array('db' => 'usr_login', 'dt' => 7, 'formatter' => function ($d) use ($router) {
                    $mask = '<div class="btn-group">
								<button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle"><span data-toggle="tooltip" data-placement="top" title="Action"><a class="fa fa-cogs"></a> Action</span> <span class="caret"></span></button>
								<ul class="dropdown-menu action-user">
									<li>%s</li>
									<li>%s</li>
									<li class="divider"></li>
									<li>%s</li>
								</ul>
							</div>';
                    $maskEdit = '<a data-toggle="tooltip" data-placement="top" title="%s" href="%s" ><i class="fa fa-pencil"></i> %s</a>';
                    $edit = sprintf($maskEdit, Lexique::GetString(CUR_LANG, 'update'), $router->pathFor('user.update', ['login' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'));

                    $masklo = '<a data-toggle="tooltip" data-placement="top" title="%s" href="#!" onclick="logoutUser(\'%s\')" class="text-danger" ><i class="fa fa-sign-out"></i> %s</a>';
                    $signout = sprintf($masklo, Lexique::GetString(CUR_LANG, 'logout-this-user'), base64_encode($d), Lexique::GetString(CUR_LANG, 'sign-out'));

                    $maskrspwd = '<a data-toggle="tooltip" data-placement="top" title="%s" href="#!" onclick="resetUserpwd(\'%s\',\'%s\')" class="text-danger" ><i class="fa fa-sign-out"></i> %s</a>';
                    $msgrest = Lexique::GetString(CUR_LANG, 'would-you-reset-this-user') . '<br/>Utilisateur : <strong>' . $d . '</strong>';
                    $resetpwd = sprintf($maskrspwd, Lexique::GetString(CUR_LANG, 'reset-password'), base64_encode($d), $msgrest, Lexique::GetString(CUR_LANG, 'reset-password'));


                    return sprintf($mask, $edit, $resetpwd, $signout);
                })
            );
            $where = " usr_login != '" . SessionManager::getLoggedUser()->getLogin() . "'";
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
        $this->title(Lexique::GetString(CUR_LANG, 'new-user'));
        $profiles = ProfileDbAdapter::getOptionForSelect(SessionManager::get(SysConst::SELECTED_ITEM));

        SessionManager::remove(SysConst::SELECTED_ITEM);
        return $this->render($response, 'create', false, ['profiles' => $profiles]);
    }

    private function sendMail(UserViewModel $model)
    {
        $fileMail = $this->container->baseDir . join(DIRECTORY_SEPARATOR, ['public', 'assets', 'email', 'newuser.html']);
        if (file_exists($fileMail)) {
            $rawBody = file_get_contents($fileMail);
            $mail = new Mail();
            $mail->mailBody = preg_replace(
                ['/\{USERNAME\}/', '/\{LOGIN\}/', '/\{PWD\}/', '/\{LINKLOGIN\}/'],
                [$model->fullName(), $model->login_usr, $model->pwd_usr, $this->container->router->pathFor(SysConst::HOME)],
                $rawBody
            );
            $mail->senderMail = 'no-reply@imediatis.net';
            $mail->senderName = 'Administrateur';
            $mail->destMail = $model->login_usr;
            $mail->destName = $model->fullName();
            $mail->subject = Lexique::GetString(CUR_LANG, 'account-created');
            return MailWorker::send($mail);
        }
        return false;
    }

    public function postCreate(Request $request, Response $response)
    {
        try {
            $model = new UserViewModel();
            $model = InputValidator::BuildModelFromRequest($model, $request);
            $model->login_usr = $request->getParam('login_usr');
            SessionManager::set(SysConst::MODEL, $model->toArray());
            SessionManager::set(SysConst::SELECTED_ITEM, $model->profile_usr);
            if (ModelState::isValid()) {
                $nuser = $model->convertToEntity();
                if (UserDbAdapter::save($nuser)) {
                    $pmsg = $this->sendMail($model) ? '<br/>' . Lexique::GetString(CUR_LANG, 'and-e-mail-with-your-cred') : null;
                    SessionManager::remove(SysConst::MODEL);
                    SessionManager::remove(SysConst::SELECTED_ITEM);
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success) . $pmsg);
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

        return $this->redirect($response, 'user.create');
    }

    /**
     * Active/Désactive un utilisateur
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function activate(Request $request, Response $response)
    {
        $login = InputValidator::getString('login');
        $output = new JsonResponse();
        if (UserDbAdapter::activate($login)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }
        return $this->renderJson($response, $output->asArray());
    }

    public function update(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update-user'));
        $login = base64_decode($request->getAttribute('login'));
        if (!$login) {
            return $this->redirect($response, 'user.read');
        }
        $user = UserDbAdapter::getByLogin($login);
        if (!$user) {
            return $this->redirect($response, 'user.read');
        }
        $model = UserViewModel::buildFromEntity($user);
        $profiles = ProfileDbAdapter::getOptionForSelect($user->getProfile()->getId());

        return $this->render($response, 'update', false, [SysConst::MODEL => $model->toArray(), 'profiles' => $profiles]);
    }

    public function postUpdate(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));
        try {
            $model = new UserViewModel();
            $model = InputValidator::BuildModelFromRequest($model, $request);
            $model->login_usr = $request->getParam('login_usr');

            if (ModelState::isValid()) {
                $nuser = $model->convertToEntity();
                if (UserDbAdapter::update($nuser)) {
                    $model = new UserViewModel();
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
                    SessionManager::updateLoggedUser($nuser);
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

        $profiles = ProfileDbAdapter::getOptionForSelect($model->profile_usr);

        return $this->render($response, 'update', false, [SysConst::MODEL => $model->toArray(), 'profiles' => $profiles]);
    }


    public function profile(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'profile'));
        $login = base64_decode($request->getAttribute('login'));
        if (!$login) {
            $this->redirect($response, SysConst::HOME, 301);
        }
        $dbuser = UserDbAdapter::getByLogin($login);
        if (!$dbuser) {
            $this->redirect($response, SysConst::HOME, 301);
        }
        $loggeduser = new LoggedUser(
            $dbuser->getLogin(),
            $dbuser->getLastName(),
            $dbuser->getFirstName(),
            $dbuser->getProfile()->getDescription(),
            $dbuser->getFunction()
        );

        return $this->render($response, 'profile', false, ['UProfile' => $loggeduser->forTwig()]);
    }

    public function changepwd(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'change-password'));
        $login = base64_decode($request->getAttribute('login'));
        $model = new ChangePwdViewModel($login);
        return $this->render($response, 'changepwd', false, [SysConst::MODEL => $model->toArray()]);
    }

    public function postChangepwd(Request $request, Response $response)
    {
        $model = new ChangePwdViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        $login = $model->tb_usr_login;

        $model->validate();

        if (ModelState::isValid()) { //TODO IMPLEMENTER LE CONTROLE DE COMPLEXITE DU MOT DE PASSE
            if (UserDbAdapter::pwdUpdate($model->tb_usr_login, $model->tb_usr_curentPwd, $model->tb_usr_confirmNewPwd)) {
                SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, 'your-password-has-been-su'));
                return $this->render($response, 'changepwd', false, ['logginSucces' => true, 'login' => $login]);
            } else {
                SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
            }
        } else {
            SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
        }
        return $this->redirect($response, 'user.changepwd', 301, ['login' => base64_encode($login)]);
    }
}
