<?php
namespace Digitalis\Core\Controllers;

use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Controllers\Controller;
use Imediatis\EntityAnnotation\ModelState;
use Digitalis\Core\Models\Security\LoggedUser;
use Digitalis\Core\Models\ViewModels\LoginViewModel;
use Digitalis\Core\Models\Security\ChangePwdViewModel;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Digitalis\Core\Models\DbAdapters\UserDbAdapter;


/**
 * AccountController Description of AccountController here
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class AccountController extends Controller
{
    public function __construct($container)
    {
        parent::__construct($container);
        parent::setCurrentController(__class__);
    }

    /**
     * Appelle le formulaire de connexion à l'application
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function index(Request $request, Response $response)
    {
        $this->title('Login');
        $loggedUser = SessionManager::getLoggedUser();
        if ($loggedUser) {
            return $this->redirect($response, SysConst::HOME, 301);
        }
        //TODO : implémenter les instructions pour vider les éléments de session ici
        return $this->render($response, "login", true);
    }

    /**
     * Authentifie un utilisateur
     *
     * @param Request $resquest
     * @param Response $response
     * @return void
     */
    public function login(Request $request, Response $response)
    {
        $model = new LoginViewModel();
        $model = InputValidator::BuildModelFromRequest($model, $request);
        SessionManager::set(SysConst::MODEL, $model->toArray());
        if (ModelState::isValid()) {
            $dbuser = UserDbAdapter::checkLogin($model->email);
            if ($dbuser) {
                if (password_verify($model->password, $dbuser->getPassword())) {
                    if ($dbuser->getStatus() == 2) {
                        return $this->redirect($response, "account.changepwd", 301, ['login' => base64_encode($dbuser->getLogin())]);
                    }
                    if ($dbuser->getIsLogged()) {
                        $laction = $dbuser->getLastAction();
                        if ($laction) {
                            $now = new \DateTime();
                            $diff = $now->diff($laction);
                            if ($diff->i < 15) {
                                UserDbAdapter::setLastLogout($dbuser->getLogin());
                                //UserDbAdapter::deactivateUser($dbuser->getLogin());
                                session_destroy();
                                SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, 'user-name-or-password-inc'), DANGER);
                            } else {
                                UserDbAdapter::setLastLogout($dbuser->getLogin());
                                goto LOGGUSER;
                            }
                        } else {
                            UserDbAdapter::setLastLogout($dbuser->getLogin());
                            //UserDbAdapter::deactivateUser($dbuser->getLogin());
                            session_destroy();
                            SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, 'user-name-or-password-inc'), DANGER);
                        }
                    } else {
                        LOGGUSER: UserDbAdapter::setLastLogin($dbuser->getLogin());
                        $loggeduser = new LoggedUser(
                            $dbuser->getLogin(),
                            $dbuser->getLastName(),
                            $dbuser->getFirstName(),
                            $dbuser->getProfile()->getDescription(),
                            $dbuser->getFunction()
                        );
                        SessionManager::set(SysConst::AUTH_USER, serialize($loggeduser));
                        return $this->redirect($response, SysConst::HOME, 301);
                    }
                } else {
                    SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, 'user-name-or-password-inc'), DANGER);
                }
            } else {
                SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, 'user-name-or-password-inc'), DANGER);
            }
        } else {
            SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
        }
        return $this->redirect($response, SysConst::R_G_LOGIN);
    }

    /**
     * Déconnexion de l'utilisateur
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function logout(Request $request, Response $response)
    {
        $loggeduser = SessionManager::getLoggedUser();
        if ($loggeduser) {
            UserDbAdapter::setLastLogout($loggeduser->login);
        }
        SessionManager::remove(SysConst::AUTH_USER);
        session_destroy();
        return $this->redirect($response, SysConst::R_G_LOGIN);
    }

    public function checkAccount(Request $request, Response $response)
    {
        $body = new Body(fopen('php://temp', 'r+'));
        $out = new JsonResponse();
        $out->message = "Session Active";
        $out->isSuccess = true;
        $body->write(json_encode($out, JSON_PRETTY_PRINT));
        return $response->withStatus(200)
            ->withHeader('Content-type', 'application/json')
            ->withBody($body);
    }

    /**
     * Déconnecter l'utilisateur dont le login est passé
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function forceLogout(Request $request, Response $response)
    {
        $login = InputValidator::getString('login');
        if ($login) {
            UserDbAdapter::setLastLogout($login);
        }
        SessionManager::remove(SysConst::AUTH_USER);
        session_destroy();
        return $this->redirect($response, SysConst::R_G_LOGIN);
    }

    public function changepwd(Request $request, Response $response)
    {
        $this->title(Lexique::GetString(CUR_LANG, 'update'));
        $login = base64_decode($request->getAttribute('login'));
        $model = new ChangePwdViewModel($login);
        return $this->render($response, 'changepwd', true, [SysConst::MODEL => $model->toArray()]);
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
                return $this->render($response, 'changepwd', true, ['logginSucces' => true]);
            } else {
                SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
            }
        } else {
            SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
        }
        return $this->redirect($response, 'account.changepwd', 301, ['login' => base64_encode($login)]);
    }

    public function bologout(Request $request, Response $response)
    {
        $login = base64_decode(InputValidator::getString('login'));
        $output = new JsonResponse();
        if (UserDbAdapter::setLastLogout($login)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }
        return $this->renderJson($response, $output->asArray());
    }

    public function resetPwd(Request $request, Response $response)
    {
        $login = base64_decode(InputValidator::getString('login'));
        $output = new JsonResponse();
        if (UserDbAdapter::resetPwd($login)) {
            $output->message = Lexique::GetString(CUR_LANG, operation_success);
        } else {
            $output->isSuccess = false;
            $output->message = Data::getErrorMessage();
        }
        return $this->renderJson($response, $output->asArray());
    }
}
