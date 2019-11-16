<?php
namespace Digitalis\Core\Handlers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Digitalis\Core\Models\EnvironmentManager as EnvMngr;
use Slim\Http\Body;
/**
 * Description of CustomErrorHandler
 * Permet de gérér les exceptions qui se produisent sur le serveur
 *
 * @author sylvin.kamdem
 */
class CustomErrorHandler extends AbstractError
{
    const KEY_LOG_FILE = "errorLog";
    const MASKLOG = "[%s][OS: %s][" . APPNAME . "][ACTION:%s][%s]" . PHP_EOL;
    const TITLE_ERROR = APPNAME . '::.Error';

    /**
     *
     * @var ContainerInterface
     */
    private $container;

    function __construct(ContainerInterface $container)
    {
        parent::__construct(EnvMngr::displayError());
        $this->container = $container;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {

        $this->writeLog($request, $exception, EnvMngr::getErrorLogFile());

        $contenttype = $this->determineContentType($request);

        $body = new Body(fopen('php://temp', 'r+'));
        switch ($contenttype) {
            case 'application/json':
                $body->write($this->renderJsonErrorMessage($exception));
                break;
            case 'application/xml':
            case 'text/xml':
                $body->write($this->renderXmlErrorMessage($exception));
                break;
            case 'text/html':
            default:
                $body->write($this->renderHtmlErrorMessage($exception));
                break;
        }
        return $response->withStatus(500)->withHeader('Content-type', $contenttype)
            ->withBody($body);
    }


    /**
     * Render HTML error page
     *
     * @param  \Exception $exception
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(\Exception $exception)
    {
        $title = self::TITLE_ERROR;

        if (!EnvMngr::isProduction()) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlException($exception);

            while ($exception = $exception->getPrevious()) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlExceptionOrError($exception);
            }
        } else {
            $html = '<p>' . EnvMngr::getdefaultMessage() . '</p>';
        }
        $formatMessage = EnvMngr::getformatHtmlError();
        $output = sprintf("<h2>%s</h2>%s", $title, $html);

        return str_replace('#ERRORMESSAGE#', $output, $formatMessage);
    }

    /**
     * Render exception as HTML.
     *
     * Provided for backwards compatibility; use renderHtmlExceptionOrError().
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderHtmlException(\Exception $exception)
    {
        return $this->renderHtmlExceptionOrError($exception);
    }

    /**
     * Render exception or error as HTML.
     *
     * @param \Exception|\Error $exception
     *
     * @return string
     */
    protected function renderHtmlExceptionOrError($exception)
    {
        if (!$exception instanceof \Exception && !$exception instanceof \Error) {
            throw new \RuntimeException("Unexpected type. Expected Exception or Error.");
        }

        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

        if (($code = $exception->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if (($message = $exception->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if (($file = $exception->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if (($line = $exception->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if (($trace = $exception->getTraceAsString()) && EnvMngr::displayErrorTrace()) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

    /**
     * Render JSON error
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Exception $exception)
    {
        $error = [
            'title' => self::TITLE_ERROR,
            'message' => EnvMngr::getdefaultMessage(),
            'status' => (boolean)false,
            'data' => null,
            'code' => null,
            'found' => (boolean)false,
            'modelStateError' => null,
            'saved' => (boolean)false,
            'updated' => (boolean)false
        ];

        if (!EnvMngr::isProduction()) {
            $error['exception'] = [];

            do {
                $item = [
                    'type' => get_class($exception),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
                if (EnvMngr::displayErrorTrace()) {
                    $item['trace'] = explode("\n", $exception->getTraceAsString());
                }
                $error['exception'][] = $item;
            } while ($exception = $exception->getPrevious());
        }

        return json_encode($error, JSON_PRETTY_PRINT);
    }

    /**
     * Render XML error
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderXmlErrorMessage(\Exception $exception)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= "<error>\n  <message>" . self::TITLE_ERROR . ": " . EnvMngr::getdefaultMessage() . "</message>\n";
        if (!EnvMngr::isProduction()) {
            do {
                $xml .= "  <exception>\n";
                $xml .= "    <type>" . get_class($exception) . "</type>\n";
                $xml .= "    <code>" . $exception->getCode() . "</code>\n";
                $xml .= "    <message>" . $this->createCdataSection($exception->getMessage()) . "</message>\n";
                $xml .= "    <file>" . $exception->getFile() . "</file>\n";
                $xml .= "    <line>" . $exception->getLine() . "</line>\n";
                $xml .= EnvMngr::displayErrorTrace() ? "    <trace>" . $this->createCdataSection($exception->getTraceAsString()) . "</trace>\n" : "";
                $xml .= "  </exception>\n";
            } while ($exception = $exception->getPrevious());
        }
        $xml .= "</error>";

        return $xml;
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param  string $content
     * @return string
     */
    private function createCdataSection($content)
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }
}
