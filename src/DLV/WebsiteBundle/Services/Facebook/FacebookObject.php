<?php
/**
 * Created by PhpStorm.
 * User: samuel.moncarey
 * Date: 9-1-2015
 * Time: 17:33
 */

namespace DLV\WebsiteBundle\Services\Facebook;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;

/**
 * Class FacebookObjectCache
 * @package DLV\WebsiteBundle\Services\Facebook
 */
class FacebookObject {

    /**
     * @var GraphObject[]
     */
    public static $loadedObjects = array();

    protected static $session;

    /**
     * @return FacebookSession
     */
    public static function getSession()
    {
        return self::$session;
    }

    /**
     * @param FacebookSession $session
     */
    public static function setSession($session)
    {
        self::$session = $session;
    }

    /**
     * @param $objectPath
     * @return GraphObject
     * @throws \Facebook\FacebookRequestException
     */
    public static function load($objectPath, $options = array())
    {
        $objectKey = $objectPath . ((count($options))? '?' . http_build_query($options) : '');
        if (array_key_exists($objectKey, self::$loadedObjects)) {
            $object = self::$loadedObjects[$objectKey];
        }
        else {
            $request = new FacebookRequest(self::getSession(), 'GET', $objectPath, $options);
            $object = $request->execute()->getGraphObject();
            self::register($object, $objectKey);
        }
        return $object;
    }

    /**
     * @param GraphObject $object
     */
    public static function register(GraphObject $object, $objectKey = null)
    {
        if (!is_null($objectKey)) {
            self::$loadedObjects[$objectKey] = $object;
        }
        else {
            self::$loadedObjects['/' . $object->getProperty('id')] = $object;
        }
    }

}