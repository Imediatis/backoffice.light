<?php
namespace Digitalis\Core\Handlers;

use Psr\Http\Message\ServerRequestInterface;


/**
 * Description of AbstractHandler
 *
 * @author sylvin.kamdem
 */
abstract class AbstractHandler
{
    /**
     * Known handled content types
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
    ];

    /**
     * Determine which content type we know about is wanted using Accept header
     *
     * Note: This method is a bare-bones implementation designed specifically for
     * Slim's error handling requirements. Consider a fully-feature solution such
     * as willdurand/negotiation for any other situation.
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request)
    {
        $contenttype = $request->getContentType();

        if (in_array($contenttype, $this->knownContentTypes)) {
            return $contenttype;
        }

        // handle +json and +xml specially
        if (preg_match('/\+(json|xml)/', $contenttype, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, $this->knownContentTypes)) {
                return $mediaType;
            }
        }

        return 'text/html';
    }
}
