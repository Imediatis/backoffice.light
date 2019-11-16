<?php
namespace Digitalis\Core\Models;

/**
 * Description of ApiResponse
 *
 * @author Sylvin
 */
class ApiResponse implements \JsonSerializable
{

    const ERROR_MODELSTATE = "Le modèle transmit n'est pas valide. Veuillez consulter 'modelStateError' pour avoir plus de détail";
    const INTERNAL_SERVER_ERROR = "Internal server error";

    public $status;
    public $data;
    public $code;
    public $message;
    public $found;
    public $modelstateerror;
    public $saved;
    public $updated;

    function __construct()
    {
        $this->status = true;
        $this->found = false;
        $this->saved = false;
        $this->updated = false;
    }

    public function jsonSerialize()
    {
        return [
            'message' => $this->message,
            'status' => (boolean)$this->status,
            'data' => $this->data,
            'code' => $this->code,
            'found' => (boolean)$this->found,
            'modelStateError' => $this->modelstateerror,
            'saved' => (boolean)$this->saved,
            'updated' => (boolean)$this->updated
        ];
    }

}
