<?php
namespace Digitalis\Core\Controllers;

use Digitalis\Core\Models\DbAdapters\ProfileDbAdapter;
use Digitalis\Core\Controllers\Controller;
use Digitalis\Core\Handlers\ErrorHandler;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\DataBase\DBase;
use Digitalis\Core\Models\DataTable;
use Digitalis\Core\Models\DbAdapters\CaisseDbAdapter;
use Digitalis\Core\Models\DbAdapters\CityDbAdapter;
use Digitalis\Core\Models\DbAdapters\CountryDbAdapter;
use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\DbAdapters\OperatorDbAdapter;
use Digitalis\Core\Models\DbAdapters\RegionDbAdapter;
use Digitalis\Core\Models\JsonResponse;
use Digitalis\Core\Models\Lexique;
use Digitalis\Core\Models\Mail;
use Digitalis\Core\Models\MailWorker;
use Digitalis\Core\Models\Security\ChangePwdViewModel;
use Digitalis\Core\Models\SessionManager;
use Digitalis\Core\Models\SysConst;
use Digitalis\Core\Models\ViewModels\OperatorViewModel;
use Imediatis\EntityAnnotation\ModelState;
use Imediatis\EntityAnnotation\Security\InputValidator;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * OperatorController Gestionnaire des actions des utilisateurs
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
 */
class OperatorController extends Controller
{
	public function __construct($container)
	{
		parent::__construct($container);
		parent::setCurrentController(__class__);
	}

	public function index(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'operators'));

		return $this->render($response, 'index', true);
	}

	public function listOperators(Request $request, Response $response)
	{
		$output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
		try {
			// DB table to use
			$table = 'operators';

			// Table's primary key
			$primaryKey = 'ope_id';
			$router = $this->router;
			// indexes
			$columns = array(
				array('db' => 'ope_id', 'dt' => 9),
				array('db' => 'ope_fname', 'dt' => 10),
				array('db' => 'prof_id', 'dt' => 11),
				array('db' => 'ope_lastlogout', 'dt' => 0, 'formatter' => function ($d, $row) {
					$maskcon = '<span><i class="g-ml-5 g-font-size-18 fa fa-%s text-%s" data-toggle="tooltip" data-placement="top" title="%s"></i></span>';
					$out = !is_null($d) ? new \DateTime($d) : null;
					$in = !is_null($row['ope_lastlogin']) ? new \DateTime($row['ope_lastlogin']) : null;

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
				array('db' => 'ope_status', 'dt' => 1, 'formatter' => function ($d, $row) {
					//STATUT 
					//0=Désactivé/supprimé danger
					//2=Dois changer le mot de passe warning (à ce stade peut être uniquement désactivé)
					//1=Actif success
					$fncmask = "activateOperator('%s','%s')";

					$mask = '<span style="cursor:pointer" data-toggle="tooltip" data-placement="top" title="%s" class="badge badge-%s" onclick="%s" >%s</span>';
					switch ($d) {
						case 0:
							$badge = "danger";
							$title = Lexique::GetString(CUR_LANG, 'disabled');
							$text = Lexique::getCode(CUR_LANG, 'disabled');
							$msg = Lexique::GetString(CUR_LANG, 'would-you-activate-this-u');
							break;
						case 1:
							$badge = "primary";
							$title = Lexique::GetString(CUR_LANG, 'active');
							$text = Lexique::getCode(CUR_LANG, 'active');
							$msg = Lexique::GetString(CUR_LANG, 'would-you-disable-this-us');
							break;
						default:
							$badge = "warning";
							$title = Lexique::GetString(CUR_LANG, 'Chg pwd');
							$text = Lexique::getCode(CUR_LANG, 'Chg pwd');
							$msg = Lexique::GetString(CUR_LANG, 'would-you-disable-this-us');
							break;
					}
					$sfncmask = sprintf($fncmask, base64_encode($row['ope_login']), $msg .  ' <br />' . $row['ope_login']);

					return sprintf($mask, $title, $badge, $sfncmask, $text);
				}),
				array('db' => 'ope_lname', 'dt' => 2, 'formatter' => function ($d, $row) {
					return trim($row['ope_fname'] . ' ' . $d);
				}),
				array('db' => 'ope_login', 'dt' => 3, 'formatter' => function ($d, $row) {
					$profile = ProfileDbAdapter::getById($row['prof_id']);
					return $d . ' (<em>' . ($profile ? $profile->getDescription() : "") . '</em>)';
				}),
				array('db' => 'ent_id', 'dt' => 4, 'formatter' => function ($d) {
					$entrep = EntrepriseDbAdapter::getById($d);
					return $entrep ? $entrep->getName() : "";
				}),
				array('db' => 'ope_id', 'dt' => 5, 'formatter' => function ($d) {
					$operator = OperatorDbAdapter::getById($d);
					return $operator ? (!is_null($operator->getCaisse()) ? $operator->getCaisse()->getCode() : null) : null;
				}),
				array('db' => 'ope_lastlogin', 'dt' => 6, 'formatter' => function ($d) {
					if ($d) {
						return (new \DateTime($d))->format('Y-m-d H:i:s');
					} else {
						return '';
					}
				}),
				array('db' => 'ope_lastiplogin', 'dt' => 7),
				array('db' => 'ope_login', 'dt' => 8, 'formatter' => function ($d) use ($router) {
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
					$edit = sprintf($maskEdit, Lexique::GetString(CUR_LANG, 'update'), $router->pathFor('operator.update', ['login' => base64_encode($d)]), Lexique::GetString(CUR_LANG, 'update'));

					$masklo = '<a data-toggle="tooltip" data-placement="top" title="%s" href="#!" onclick="logoutOperator(\'%s\')" class="text-danger" ><i class="fa fa-sign-out"></i> %s</a>';
					$signout = sprintf($masklo, Lexique::GetString(CUR_LANG, 'logout-this-operator'), base64_encode($d), Lexique::GetString(CUR_LANG, 'sign-out'));

					$maskrspwd = '<a data-toggle="tooltip" data-placement="top" title="%s" href="#!" onclick="resetOperatorpwd(\'%s\',\'%s\')" class="text-danger" ><i class="fa fa-sign-out"></i> %s</a>';
					$msgrest = Lexique::GetString(CUR_LANG, 'do-you-want-to-reset-this') . '<br/>Utilisateur : <strong>' . $d . '</strong>';
					$resetpwd = sprintf($maskrspwd, Lexique::GetString(CUR_LANG, 'reset-password'), base64_encode($d), $msgrest, Lexique::GetString(CUR_LANG, 'reset-password'));


					return sprintf($mask, $edit, $resetpwd, $signout);
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
		$this->title(Lexique::GetString(CUR_LANG, 'operators'));

		$countries = CountryDbAdapter::getCountriesForOptions();
		$regions = RegionDbAdapter::getRegionsForOptions();
		$cities = CityDbAdapter::getCitiesForOptions();
		$entreprises = EntrepriseDbAdapter::getEnterprisesForOptions();
		$profiles = ProfileDbAdapter::getOptionForSelect(null);


		return $this->render($response, 'create', true, ['profiles' => $profiles, 'countries' => $countries, 'regions' => $regions, 'cities' => $cities, 'entreprises' => $entreprises]);
	}

	private function sendMail(OperatorViewModel $model)
	{
		$fileMail = $this->container->baseDir . join(DIRECTORY_SEPARATOR, ['public', 'assets', 'email', 'newuser.html']);
		if (file_exists($fileMail)) {
			$rawBody = file_get_contents($fileMail);
			$mail = new Mail();
			$mail->mailBody = preg_replace(
				['/\{USERNAME\}/', '/\{LOGIN\}/', '/\{PWD\}/', '/\{LINKLOGIN\}/'],
				[$model->fullName(), $model->login_ope, $model->pwd_ope, $this->container->router->pathFor(SysConst::HOME)],
				$rawBody
			);
			$mail->senderMail = 'no-reply@imediatis.net';
			$mail->senderName = 'Administrateur';
			$mail->destMail = $model->login_ope;
			$mail->destName = $model->fullName();
			$mail->subject = Lexique::GetString(CUR_LANG, 'account-created');
			return MailWorker::send($mail);
		}
		return false;
	}

	public function postCreate(Request $request, Response $response)
	{
		try {
			$model = new OperatorViewModel();

			$model = InputValidator::BuildModelFromRequest($model, $request);
			$model->login_ope = $request->getParam('login_ope');
			SessionManager::set(SysConst::MODEL, $model->toArray());
			SessionManager::set(SysConst::SELECTED_ITEM, $model->profile_ope);
			if (ModelState::isValid()) {
				$noperator = $model->convertToEntity();
				if (OperatorDbAdapter::save($noperator)) {
					$pmsg = $this->sendMail($model) ? '<br/>' . Lexique::GetString(CUR_LANG, 'and-e-mail-with-your-cred') : null;
					$model = new  OperatorViewModel();
					SessionManager::set(SysConst::MODEL, $model->toArray());
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
		$dentrep = EntrepriseDbAdapter::getById($model->entrep_ope);

		$countries = CountryDbAdapter::getCountriesForOptions(!is_null($dentrep) ? $dentrep->getCity()->getRegion()->getCountry()->getId() : null);
		$regions = RegionDbAdapter::getRegionsForOptions(!is_null($dentrep) ? $dentrep->getCity()->getRegion()->getId() : null);
		$cities = CityDbAdapter::getCitiesForOptions(!is_null($dentrep) ? $dentrep->getCity()->getId() : null);
		$entreprises = EntrepriseDbAdapter::getEnterprisesForOptions(!is_null($dentrep) ? $dentrep->getId() : null);
		$profiles = ProfileDbAdapter::getOptionForSelect(SessionManager::get(SysConst::SELECTED_ITEM));

		return $this->render(
			$response,
			'create',
			true,
			[
				SysConst::MODEL => $model->toArray(),
				SysConst::MODEL_ERRORS => ModelState::getErrors(),
				'profiles' => $profiles, 'countries' => $countries,
				'regions' => $regions, 'cities' => $cities, 'entreprises' => $entreprises
			]
		);
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
		$login = base64_decode(InputValidator::getString('login'));
		$output = new JsonResponse();
		if (OperatorDbAdapter::activate($login)) {
			$output->message = Lexique::GetString(CUR_LANG, operation_success);
		} else {
			$output->isSuccess = false;
			$output->message = Data::getErrorMessage();
		}
		return $this->renderJson($response, $output->asArray());
	}

	public function update(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'update-operator'));
		$login = base64_decode($request->getAttribute('login'));
		if (!$login) {
			return $this->redirect($response, 'operator.read');
		}
		$operator = OperatorDbAdapter::getByLogin($login);
		if (!$operator) {
			return $this->redirect($response, 'operator.read');
		}
		$model = OperatorViewModel::buildFromEntity($operator);
		$dentrep = EntrepriseDbAdapter::getById($model->entrep_ope);

		$countries = CountryDbAdapter::getCountriesForOptions(!is_null($dentrep) ? $dentrep->getCity()->getRegion()->getCountry()->getId() : null);
		$regions = RegionDbAdapter::getRegionsForOptions(!is_null($dentrep) ? $dentrep->getCity()->getRegion()->getId() : null);
		$cities = CityDbAdapter::getCitiesForOptions(!is_null($dentrep) ? $dentrep->getCity()->getId() : null);
		$entreprises = EntrepriseDbAdapter::getEnterprisesForOptions(!is_null($dentrep) ? $dentrep->getId() : null);
		$profiles = ProfileDbAdapter::getOptionForSelect($operator->getProfile()->getId());

		return $this->render(
			$response,
			'update',
			true,
			[
				SysConst::MODEL => $model->toArray(), 'profiles' => $profiles,
				'countries' => $countries, 'regions' => $regions, 'cities' => $cities, 'entreprises' => $entreprises
			]
		);
	}

	public function postUpdate(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'update'));
		try {
			$model = new OperatorViewModel();
			$model = InputValidator::BuildModelFromRequest($model, $request);
			$model->login_ope = $request->getParam('login_ope');

			if (ModelState::isValid()) {
				$noperator = $model->convertToEntity();
				if (OperatorDbAdapter::update($noperator)) {
					$model = new OperatorViewModel();
					SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, operation_success));
					SessionManager::updateLoggedUser($noperator);
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

		$dentrep = EntrepriseDbAdapter::getById($model->entrep_ope);

		$countries = CountryDbAdapter::getCountriesForOptions(!is_null($dentrep) ? $dentrep->getCity()->getRegion()->getCountry()->getId() : null);
		$regions = RegionDbAdapter::getRegionsForOptions(!is_null($dentrep) ? $dentrep->getCity()->getRegion()->getId() : null);
		$cities = CityDbAdapter::getCitiesForOptions(!is_null($dentrep) ? $dentrep->getCity()->getId() : null);
		$entreprises = EntrepriseDbAdapter::getEnterprisesForOptions(!is_null($dentrep) ? $dentrep->getId() : null);
		$profiles = ProfileDbAdapter::getOptionForSelect($model->profile_ope);

		return $this->render(
			$response,
			'update',
			true,
			[
				SysConst::MODEL => $model->toArray(), 'profiles' => $profiles,
				'countries' => $countries, 'regions' => $regions, 'cities' => $cities, 'entreprises' => $entreprises
			]
		);
	}

	public function changepwd(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'change-password'));
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
			if (OperatorDbAdapter::pwdUpdate($model->tb_usr_login, $model->tb_usr_curentPwd, $model->tb_usr_confirmNewPwd)) {
				SessionManager::set(SysConst::FLASH, Lexique::GetString(CUR_LANG, 'your-password-has-been-su'));
				return $this->render($response, 'changepwd', true, ['logginSucces' => true, 'login' => $login]);
			} else {
				SessionManager::set(SysConst::FLASH, Data::getErrorMessage(), DANGER);
			}
		} else {
			SessionManager::set(SysConst::MODEL_ERRORS, ModelState::getErrors());
		}
		return $this->redirect($response, 'operator.changepwd', 301, ['login' => base64_encode($login)]);
	}

	public function resetPwd(Request $request, Response $response)
	{
		$login = base64_decode(InputValidator::getString('login'));
		$output = new JsonResponse();
		if (OperatorDbAdapter::resetPwd($login)) {
			$output->message = Lexique::GetString(CUR_LANG, operation_success);
		} else {
			$output->isSuccess = false;
			$output->message = Data::getErrorMessage();
		}
		return $this->renderJson($response, $output->asArray());
	}

	public function bologout(Request $request, Response $response)
	{
		$login = base64_decode(InputValidator::getString('login'));
		$output = new JsonResponse();
		if (OperatorDbAdapter::setLastLogout($login)) {
			$output->message = Lexique::GetString(CUR_LANG, operation_success);
		} else {
			$output->isSuccess = false;
			$output->message = Data::getErrorMessage();
		}
		return $this->renderJson($response, $output->asArray());
	}


	public function listEntOperators(Request $request, Response $response)
	{
		$output = array("draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
		$identrep = InputValidator::getInt('entrep');
		try {
			// DB table to use
			$table = 'operators';

			// Table's primary key
			$primaryKey = 'ope_id';
			$router = $this->router;
			// indexes
			$columns = array(
				array('db' => 'ope_id', 'dt' => 7),
				array('db' => 'ope_fname', 'dt' => 8),
				array('db' => 'prof_id', 'dt' => 9),
				array('db' => 'ope_id', 'dt' => 0, 'formatter' => function ($d, $row) {
					$name = trim($row['ope_fname'] . ' ' . $row['ope_lname']);
					return '<div class="radio radio-success i-checks">
								<input type="radio" name="oper_id" value="' . $d . '" data-opename="' . $name . '" data-opelogin="' . $row['ope_login'] . '" />
								<label></label>
							</div>';
				}),
				array('db' => 'ope_lname', 'dt' => 1, 'formatter' => function ($d, $row) {
					return trim($row['ope_fname'] . ' ' . $d);
				}),
				array('db' => 'ope_login', 'dt' => 2),
				array('db' => 'prof_id', 'dt' => 3, 'formatter' => function ($d) {
					$profile = ProfileDbAdapter::getById($d);
					return $profile ? $profile->getDescription() : "";
				}),
				array('db' => 'ope_id', 'dt' => 4, 'formatter' => function ($d) {
					$operator = OperatorDbAdapter::getById($d);
					return $operator ? (!is_null($operator->getCaisse()) ? $operator->getCaisse()->getCode() : null) : null;
				})
			);

			$where = !is_null($identrep) ? " ent_id =" . $identrep : null;
			$output = DataTable::complex($_POST, DBase::paramsPDO(), $table, $primaryKey, $columns, $where, $where);
		} catch (\Exception $exc) {
			Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
			ErrorHandler::writeLog($exc);
			$output['error'] = Lexique::GetString(CUR_LANG, an_error_occured);
		}
		return $this->renderJson($response, $output);
	}

	public function setOperator(Request $request, Response $response)
	{
		$this->title(Lexique::GetString(CUR_LANG, 'assigning-an-operator-to-'));
		$code = base64_decode($request->getAttribute('code'));
		$caisse = CaisseDbAdapter::getByCode($code);
		$boxes = CaisseDbAdapter::getBoxesForOptions(!is_null($caisse) ? $caisse->getId() : null);
		$entreprises = EntrepriseDbAdapter::getEnterprisesForOptions(!is_null($caisse) ? $caisse->getAgence()->getEntreprise()->getId() : null);

		return $this->render($response, 'setoperator', true, [
			'entreprises' => $entreprises,
			'boxes' => $boxes,
		]);
	}

	public function psetOperator(Request $request, Response $response)
	{
		$output = new JsonResponse();
		$idCaisse = InputValidator::getInt('idCaisse');
		$idOperateur = InputValidator::getInt('idOperator');

		if (OperatorDbAdapter::setOperatorBox($idOperateur, $idCaisse)) {
			$output->message = Lexique::GetString(CUR_LANG, operation_success);
		} else {
			$output->isSuccess = false;
			$output->message = Data::getErrorMessage();
		}
		return $this->renderJson($response, $output->asArray());
	}
}
