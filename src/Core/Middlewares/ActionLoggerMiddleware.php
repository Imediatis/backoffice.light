<?php


namespace Digitalis\Core\Middlewares;

use DateTime;
use Digitalis\Core\Models\Data;
use Digitalis\Core\Models\EnvironmentManager as EnvMngr;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Route;

/**
 * Description of ActionLoggerMiddleware
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class ActionLoggerMiddleware
{

    const ALLOWED_METHODE = "ALLOWED_METHODE";
    const NOT_ALLOWED_METHODE = "NOT_ALLOWED_METHODE";
    const TARGET_NOT_FOUND = "TARGET_NOT_FOUND";
    const MASKLOG = "[%s][client %s:%s][OS: %s][" . APPNAME . "]%s|%s[%s][status:%s]\n";

    //put your code here
    private $container;

    /**
     *
     * @var Route
     */
    private $route;

    function __construct($container)
    {
        $this->container = $container;
    }



    private function extractParams($params)
    {
        $output = [];
        foreach ($params as $key => $value) {
            $output[] = $key . '="' . (is_array($value) ? json_encode($value) : $value) . '"';
        }
        return join(";", $output);
    }

    public function writeLog(RequestInterface $request, $status = null)
    {
        $port = $request->getServerParam("REMOTE_PORT");
        $ipAddress = $this->container->ipAddress;


        $date = (new DateTime())->format('D M d H:i:s.u Y');
        $os = Data::cgetOS($request->getServerParam("HTTP_USER_AGENT"));
        $methode = $request->getMethod();
        $action = $request->getUri()->getPath();
        $params = "";//$this->extractParams($request->getParams());

        $lstatut = is_null($status) ? self::ALLOWED_METHODE : $status;
        //
        //CREATION DU FICHIER S'IL N'EXISTE PAS
        //
        try {
            $fp = @fopen(EnvMngr::getActionLogFile(), 'a');
            if ($fp) {
                if (flock($fp, LOCK_EX | LOCK_NB)) {
                    fwrite($fp, sprintf(self::MASKLOG, $date, $ipAddress, $port, $os, $methode, $action, $params, $lstatut));
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage(), $exc->getCode());
        }
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, $next)
    {
        $this->writeLog($request);
        return $next($request, $response);
    }
}
