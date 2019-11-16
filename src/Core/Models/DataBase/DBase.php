<?php

namespace Digitalis\Core\Models\DataBase;

use Doctrine\ORM\EntityManager;

class DBase
{
    /**
     * Entitymanger to work code with
     *
     * @var EntityManager
     */
    private static $entityManager;

    private static $dsn = '%s:host=%s;port=%d;dbname=%s;charset=UTF8;';

    /**
     * Permet de récupérer l'entitymanager
     *
     * @return EntityManager
     */
    public static function getEntityManager()/* : \Doctrine\ORM\EntityManager */
    {
        if (is_null(self::$entityManager) || !(self::$entityManager instanceof EntityManager)) {
            self::$entityManager = require_once(realpath(realpath(__DIR__ . '/../../../../bootstrap/') . DIRECTORY_SEPARATOR . 'bootstrap.php'));
        }
        return self::$entityManager;
    }

    /**
     * Retourne les paramètres de connexion à la base de données
     *
     * @return array
     */
    public static function paramsPDO()
    {
        return [
            'host' => self::getEntityManager()->getConnection()->getHost(),
            'db' => self::getEntityManager()->getConnection()->getDatabase(),
            'user' => self::getEntityManager()->getConnection()->getUsername(),
            'pass' => self::getEntityManager()->getConnection()->getPassword()
        ];
    }

}
