<?php
/**
 * Created by PhpStorm.
 * User: samuel.moncarey
 * Date: 25-12-2014
 * Time: 22:34
 */

namespace DLV\WebsiteBundle\Services\Facebook;


use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

class FacebookLogin extends FacebookRedirectLoginHelper
{
    /**
     * Handles a response from Facebook, including a CSRF check, and returns a
     *   FacebookSession.
     *
     * @return FacebookSession|null
     */
    public function getSessionFromRedirect()
    {
        $params = array(
            'client_id' => FacebookSession::_getTargetAppId($this->appId),
            'redirect_uri' => $this->redirectUrl,
            'client_secret' =>
                FacebookSession::_getTargetAppSecret($this->appSecret),
            'code' => $this->getCode()
        );
        $response = (new FacebookRequest(
            FacebookSession::newAppSession($this->appId, $this->appSecret),
            'GET',
            '/oauth/access_token',
            $params
        ))->execute()->getResponse();
        if (isset($response['access_token'])) {
            return new FacebookSession($response['access_token']);
        }
        return null;
    }
}