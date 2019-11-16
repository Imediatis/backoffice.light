<?php

namespace Digitalis\Core\Handlers;

use Digitalis\Core\Models\EnvironmentManager as EnvMngr;
use Slim\Http\Body;
use Slim\Http\Request;

class CustomPhpErrorHandler extends AbstractError
{
    const KEY_LOG_FILE = "errorLog";
    const MASKLOG = "[%s][OS: %s][" . APPNAME . "][ACTION:%s][%s]" . PHP_EOL;
    const TITLE_ERROR = APPNAME . '::.Error';

    /**
     *
     * @var Container
     */
    private $container;

    function __construct($container)
    {
        parent::__construct(EnvMngr::displayError());
        $this->container = $container;
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param \Throwable             $error     The caught Throwable object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke($request, $response, \Throwable $error)
    {

        $this->writeLog($request, $error, EnvMngr::getErrorLogFile());


        $contenttype = $this->determineContentType($request);

        $body = new Body(fopen('php://temp', 'r+'));
        switch ($contenttype) {
            case 'application/json':
                $body->write($this->renderJsonErrorMessage($error));
                break;
            case 'application/xml':
            case 'text/xml':
                $body->write($this->renderXmlErrorMessage($error));
                break;
            case 'text/html':
            default:
                $body->write($this->renderHtmlErrorMessage($error));
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
    protected function renderHtmlErrorMessage(\Throwable $error)
    {
        $title = self::TITLE_ERROR;

        if (!EnvMngr::isProduction()) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlError($error);

            while ($error = $error->getPrevious()) {
                $html .= '<h2>Previous error</h2>';
                $html .= $this->renderHtmlError($error);
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
     * @param \Throwable $exception
     *
     * @return string
     */
    protected function renderHtmlError(\Throwable $error)
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($error));

        if (($code = $error->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if (($message = $error->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if (($file = $error->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if (($line = $error->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if (($trace = $error->getTraceAsString()) && EnvMngr::displayErrorTrace()) {
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
    protected function renderJsonErrorMessage(\Throwable $error)
    {
        $output = [
            'title' => self::TITLE_ERROR,
            'message' => EnvMngr::getdefaultMessage(),
            'status' => (boolean)false,
            'data' => null,
            'found' => (boolean)false,
            'modelStateError' => null,
            'code' => null,
            'saved' => (boolean)false,
            'updated' => (boolean)false
        ];

        if (!EnvMngr::isProduction()) {
            $output['error'] = [];

            do {
                $item = [
                    'type' => get_class($error),
                    'code' => $error->getCode(),
                    'message' => $error->getMessage(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                ];
                if (EnvMngr::displayErrorTrace()) {
                    $item['trace'] = explode("\n", $error->getTraceAsString());
                }
                $output['error'][] = $item;
            } while ($error = $error->getPrevious());
        }

        return json_encode($output, JSON_PRETTY_PRINT);
    }

    /**
     * Render XML error
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderXmlErrorMessage(\Throwable $error)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= "<error>\n  <message>" . self::TITLE_ERROR . ": " . EnvMngr::getdefaultMessage() . "</message>\n";
        if (!EnvMngr::isProduction()) {
            do {
                $xml .= "  <error>\n";
                $xml .= "    <type>" . get_class($error) . "</type>\n";
                $xml .= "    <code>" . $error->getCode() . "</code>\n";
                $xml .= "    <message>" . $this->createCdataSection($error->getMessage()) . "</message>\n";
                $xml .= "    <file>" . $error->getFile() . "</file>\n";
                $xml .= "    <line>" . $error->getLine() . "</line>\n";

                $xml .= EnvMngr::displayErrorTrace() ? "    <trace>" . $this->createCdataSection($error->getTraceAsString()) . "</trace>\n" : "";
                $xml .= "  </error>\n";
            } while ($error = $error->getPrevious());
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
