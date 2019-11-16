<?php
namespace Digitalis\Core\Models\ViewModels;

use Digitalis\Core\Models\Entities\Entreprise;
use Digitalis\Core\Models\DbAdapters\CityDbAdapter;


/**
 * EntrepriseViewModel Modèle d'interface pour la gestion d'entreprise
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class EntrepriseViewModel implements ViewModelInterface
{

    /**
	 * Identifiant
	 *
	 * @IME\Id
	 * @IME\DataType{"type":"integer","nullable":true}
	 * @var integer
	 */
    public $tb_ent_id;

    /**
	 * Reférence de l'entreprise
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":20,"errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	 * @var string
	 */
    public $tb_ent_ref;

    /**
	 * Nom de l'entreprise
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":120,"errMsg":"Ce champ n'admet pas plus de 120 caractères"}
	 * @var string
	 */
    public $tb_ent_name;

    /**
	 * Nom de domaine de l'entreprise
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\Length{"max":50,"errMsg":"Ce champ n'admet pas plus de 50 caractères"}
	 * @var string
	 */
    public $tb_ent_domain_name;

    /**
	 * Adresse de l'entreprise
	 *
	 * @IME\DataType{"inputType":"textarea","nullable":true}
	 * @IME\Length{"max":255,"errMsg":"Ce champ n'admet pas plus de 255 caractères"}
	 * @var string
	 */
    public $tb_ent_address;

    /**
	 * Ville de localisation de l'entreprise
	 *
	 * @IME\Required{"errMsg":"Ce champ est obligatoire"}
	 * @IME\DataType{"type":"integer"}
	 * @var integer
	 */
    public $tb_ent_city;

    /**
	 * Numéro de téléphone 1
	 *
	 * @IME\Length{"max":20,"errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	 * @IME\DataType{"nullable":true}
	 * @var string
	 */
    public $tb_ent_phone1;

    /**
	 * Numéro de téléphone 2
	 *
	 * @IME\Length{"max":20,"errMsg":"Ce champ n'admet pas plus de 20 caractères"}
	 * @IME\DataType{"nullable":true}
	 * @var string
	 */
    public $tb_ent_phone2;

    /**
	 * Adresse mail 1
	 *
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @IME\DataType{"nullable":true,"type":"email"}
	 * @var string
	 */
    public $tb_ent_email_1;

    /**
	 * Adresse mail 2
	 *
	 * @IME\Length{"max":80,"errMsg":"Ce champ n'admet pas plus de 80 caractères"}
	 * @IME\DataType{"nullable":true,"type":"email"}
	 * @var string
	 */
    public $tb_ent_email_2;

    public function toArray()
    {
        return [
            'id' => $this->tb_ent_id,
            'reference' => $this->tb_ent_ref,
            'name' => $this->tb_ent_name,
            'domain' => $this->tb_ent_domain_name,
            'address' => $this->tb_ent_address,
            'city' => $this->tb_ent_city,
            'phone1' => $this->tb_ent_phone1,
            'phone2' => $this->tb_ent_phone2,
            'email1' => $this->tb_ent_email_1,
            'email2' => $this->tb_ent_email_2
        ];
    }

    /**
	 * Convertit le modèle en entité prêt pour la base de données
	 *
	 * @return Entreprise
	 */
    public function convertToEntity()
    {
        $entrep = new Entreprise();
        $entrep->setId($this->tb_ent_id);
        $entrep->setReference($this->tb_ent_ref);
        $entrep->setName($this->tb_ent_name);
        $entrep->setDomain($this->tb_ent_domain_name);
        $entrep->setAddress($this->tb_ent_address);
        $entrep->setPhone1($this->tb_ent_phone1);
        $entrep->setPhone2($this->tb_ent_phone2);
        $entrep->setEmail1($this->tb_ent_email_1);
        $entrep->setEmail2($this->tb_ent_email_2);
        $city = CityDbAdapter::getById($this->tb_ent_city);
        if ($city) {
            $entrep->setCity($city);
        }
        return $entrep;
    }

    /**
	 * Construit le viewModel à partir de l'entité
	 *
	 * @param Entreprise|null $entrep
	 * @return EntrepriseViewModel
	 */
    public static function buildFromEntity($entrep = null)
    {
        $vmodel = new EntrepriseViewModel();
        if (!is_null($entrep)) {
            $vmodel->tb_ent_id = $entrep->getId();
            $vmodel->tb_ent_ref = $entrep->getReference();
            $vmodel->tb_ent_name = $entrep->getName();
            $vmodel->tb_ent_domain_name = $entrep->getDomain();
            $vmodel->tb_ent_address = $entrep->getAddress();
            $vmodel->tb_ent_city = $entrep->getCity()->getId();
            $vmodel->tb_ent_phone1 = $entrep->getPhone1();
            $vmodel->tb_ent_phone2 = $entrep->getPhone2();
            $vmodel->tb_ent_email_1 = $entrep->getEmail1();
            $vmodel->tb_ent_email_2 = $entrep->getEmail2();
        }
        return $vmodel;
    }
}