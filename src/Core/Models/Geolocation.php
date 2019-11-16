<?php
namespace Digitalis\Core\Models;

/**
 * Geolocation Permet de géolocaliser les différents clients qui accède aux mail
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class Geolocation
{
    /**
     * Adresse IP utilisé pour géolocaliser
     *
     * @var string
     */
    public $ipAddress;

    /**
     * Code ISO du pays de localisation
     *
     * @var string
     */
    public $countryCode;

    /**
     * Nom du pays où est localiser le client
     *
     * @var string
     */
    public $countryName;

    /**
     * Code de la région où se trouve le client
     *
     * @var string
     */
    public $regionCode;

    /**
     * Nom de la région dans le pays où se trouve le client
     *
     * @var string
     */
    public $regionName;

    /**
     * Ville où se trouve le client
     *
     * @var string
     */
    public $city;

    /**
     * Fuseau horaire du client
     *
     * @var string
     */
    public $timeZone;

    /**
     * Code zip de la ville cu client
     *
     * @var string
     */
    public $zipCode;

    /**
     * Latitude du client
     *
     * @var string
     */
    public $latitude;

    /**
     * Longitude du client
     *
     * @var string
     */
    public $longitude;

    /**
     * Code du metro
     *
     * @var string
     */
    public $metroCode;

    /**
     * Instanciation de l'objet
     *
     * @param string $ipAddress
     */
    public function __construct($ipAddress)
    {
        $data = MailWorker::getLocalisation($ipAddress);

        $this->ipAddress = isset($data['ip']) ? $data['ip'] : null;
        $this->countryCode = isset($data['country_code']) ? $data['country_code'] : null;
        $this->countryName = isset($data['country_name']) ? $data['country_name'] : null;
        $this->regionCode = isset($data['region_code']) ? $data['region_code'] : null;
        $this->regionName = isset($data['region_name']) ? $data['region_name'] : null;
        $this->city = isset($data['city']) ? $data['city'] : null;
        $this->zipCode = isset($data['zip_code']) ? $data['zip_code'] : null;
        $this->timeZone = isset($data['time_zone']) ? $data['time_zone'] : null;
        $this->latitude = isset($data['latitude']) ? $data['latitude'] : null;
        $this->longitude = isset($data['longitude']) ? $data['longitude'] : null;
        $this->metroCode = isset($data['metro_code']) ? $data['metro_code'] : null;
    }

}
