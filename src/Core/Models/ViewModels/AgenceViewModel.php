<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\DbAdapters\CityDbAdapter;
use Digitalis\Core\Models\DbAdapters\EntrepriseDbAdapter;
use Digitalis\Core\Models\Entities\Agence;


/**
  * AgenceViewModel Modèle de gestion des agences
  *
  * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
  * @license    Intellectual property rights of IMEDIATIS SARL
  * @version    Release: 1.0
  * @author     Sylvin KAMDEM <sylvin@imediatis.net> (Back-end Developper)
  */
class AgenceViewModel implements ViewModelInterface
{

    /**
	  * Identifiant
	  *
	  * @IME\Id
	  * @IME\DataType{"type":"integer","nullable":true}
	  * @var integer
	  */
    public $tb_ag_id;

    /**
	  * Code de l'agence
	  *
	  * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	  * @IME\Length{"max":10,"errMsg":"Ce champ n'admet pas plus de 10 caractères"}
	  * @var string
	  */
    public $tb_ag_code;

    /**
	  * Nom de l'agence
	  *
	  * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	  * @IME\Length{"max":155,"errMsg":"Ce champ n'admet pas plus de 155 caractères"}
	  * @var string
	  */
    public $tb_ag_label;

    /**
	  * Adresse de l'agence
	  *
	  * @IME\DataType{"nullable":true}
	  * @IME\Length{"max":300,"errMsg":"Ce champ n'admet pas plus de 300 caractères"}
	  * @var string
	  */
    public $tb_ag_address;

    /**
	  * Téléphone 1 de l'agence
	  *
      * @IME\Required{"errMsg":"Ce champ est obligatoire"}
      * @IME\DataType{"errMsg":"Ce champ est obligatoire"}
	  * @IME\Lenght{"max":20,"errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	  * @var string
	  */
    public $tb_ag_phone_1;

    /**
	  * Téléphone N2 de l'agence
	  *
	  * @IME\DataType{"nullable":true}
	  * @IME\Length{"max":20,"errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	  * @var string
	  */
    public $tb_ag_phone_2;

    /**
	  * Adresse mail de l'agence
	  *
	  * @IME\DataType{"type":"email","errMsg":"Valeur invalide pour ce champ","nullable":true}
      * @IME\Length{"max":50,"errMsg":"Ce champ n'ademet pas plus de 50 caractères"}
	  * @var string
	  */
    public $tb_ag_email;

    /**
	  * Nombre de caisse par défaut
	  *
	  * @IME\DataType{"type":"integer"}
	  * @var integer
	  */
    public $tb_ag_nb_caisse;

    /**
	 * Entreprise d'appartenance de l'agence
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer"}
	 * @var integer
	 */
    public $tb_ag_entrep;

    /**
	 * Vilel de localisation de l'agence
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer"}
	 * @var integer
	 */
    public $tb_ag_city;

    public function __construct()
    {
        $this->tb_ag_nb_caisse = 3;
    }

    public function toArray()
    {
        return [
            'id' => $this->tb_ag_id,
            'code' => $this->tb_ag_code,
            'label' => $this->tb_ag_label,
            'address' => $this->tb_ag_address,
            'phone1' => $this->tb_ag_phone_1,
            'phone2' => $this->tb_ag_phone_2,
            'email' => $this->tb_ag_email,
            'nbCaisse' => $this->tb_ag_nb_caisse,
            'entreprise' => $this->tb_ag_entrep,
            'city' => $this->tb_ag_city
        ];
    }

    /**
	 * Convertit en Agence pour l'enregistrement de la base de données
	 *
	 * @return Agence
	 */
    public function convertToEntity()
    {
        $nagence = new Agence();
        $nagence->setId($this->tb_ag_id);
        $nagence->setCode($this->tb_ag_code);
        $nagence->setLabel($this->tb_ag_label);
        $nagence->setAddress($this->tb_ag_address);
        $nagence->setPhone1($this->tb_ag_phone_1);
        $nagence->setPhone2($this->tb_ag_phone_2);
        $nagence->setEmail($this->tb_ag_email);
        $entrep = EntrepriseDbAdapter::getById($this->tb_ag_entrep);
        if ($entrep) {
            $nagence->setEntreprise($entrep);
        }
        $city = CityDbAdapter::getById($this->tb_ag_city);
        if ($city) {
            $nagence->setCity($city);
        }
        return $nagence;
    }

    /**
	 * Construit à partir de l'objet agence
	 *
	 * @param Agence $agence
	 * @return AgenceViewModel
	 */
    public static function buildFromEntity($agence = null)
    {
        $vmodel = new AgenceViewModel();
        if (!is_null($agence)) {
            $vmodel->tb_ag_id = $agence->getId();
            $vmodel->tb_ag_code = $agence->getCode();
            $vmodel->tb_ag_label = $agence->getLabel();
            $vmodel->tb_ag_address = $agence->getAddress();
            $vmodel->tb_ag_phone_1 = $agence->getPhone1();
            $vmodel->tb_ag_phone_2 = $agence->getPhone2();
            $vmodel->tb_ag_email = $agence->getEmail();
            $vmodel->tb_ag_entrep = $agence->getEntreprise()->getId();
            $vmodel->tb_ag_city = $agence->getCity()->getId();
            $nbcaisse = count($agence->getCaisses());
            $vmodel->tb_ag_nb_caisse = $nbcaisse < 1 ? 3 : $nbcaisse;
        }
        return $vmodel;
    }
}
