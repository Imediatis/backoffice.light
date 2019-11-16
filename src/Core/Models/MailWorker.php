<?php

namespace Digitalis\Core\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Imediatis\EntityAnnotation\Security\InputValidator;


/**
 * MailWorker Utilitaire pour la validation des adresses mail
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class MailWorker
{

    /**
     * Détermine si une adresse email est suceptible de passer
     *
     * @param string $email
     * @return boolean
     */
    public static function isValidDomain($email)
    {
        if (InputValidator::isEmail($email)) {
            $temail = explode('@', $email);
            $domaine = $temail[1];
            return checkdnsrr($domaine, 'MX') && count(dns_get_record($domaine, DNS_MX)) > 0;
        }
        return false;
    }

    /**
     * Pemer d'envoyer un mail
     *
     * @param Mail $mailData Les données constituant le mail à envoyer
     * @return boolean
     */
    public static function send(Mail $mailData)
    {
        try {
            $PhpMailer = new PHPMailer(true);
            $PhpMailer->CharSet = 'UTF-8';

            $PhpMailer->setFrom($mailData->senderMail, $mailData->senderName);

            $PhpMailer->addAddress($mailData->destMail, $mailData->destName);

            $PhpMailer->isHTML(true);
            $PhpMailer->Subject = $mailData->subject;
            $PhpMailer->Body = $mailData->mailBody;

            return $PhpMailer->send();
        } catch (Exception $exc) {
            Data::setErrorMessage(Lexique::GetString(CUR_LANG, an_error_occured));
        }
        return false;
    }

    public static function mail(Mail $mailData)
    {
        $headers = array(
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset="UTF-8";',
            'Content-Transfer-Encoding: 8bit',
            'Date: ' . date('r', $_SERVER['REQUEST_TIME']),
            'Message-ID: <' . $_SERVER['REQUEST_TIME'] . md5($_SERVER['REQUEST_TIME']) . '@' . $_SERVER['SERVER_NAME'] . '>',
            'From: ' . $mailData->senderMail,
            'X-Mailer: PHP v' . phpversion(),
            'X-Originating-IP: ' . $_SERVER['SERVER_ADDR'],
        );
        return mail($mailData->destMail, $mailData->subject, $mailData->mailBody, implode("\n", $headers));
    }

    /**
     * Permet de récupérer l'adresse IP du client
     *
     * @return string
     */
    public static function getIpAddress()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            return $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            return $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            return $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            return $_SERVER['REMOTE_ADDR'];
        else
            return 'UNKNOWN';
    }

    /**
     * Permet de récupérer les informations de géolocalisation d'une adresse IP
     *
     * @param string $ipadresse
     * @return array|null Cette methode retourne un tableau lorsque l'adresse ip est valide et null le cas contraire
     */
    public static function getLocalisation($ipadresse)
    {
        if (filter_var($ipadresse, FILTER_VALIDATE_IP)) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://freegeoip.app/json/" . $ipadresse,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json",
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return []; //echo "cURL Error #:" . $err;
            } else {
                return json_decode($response, true);
            }
        } else {
            return [];
        }

    }
}
