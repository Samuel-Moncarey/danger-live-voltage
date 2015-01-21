<?php
namespace DLV\WebsiteBundle\Services\Facebook;


use DLV\WebsiteBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\GraphObject;

/**
 * Class FacebookUser
 * @package DLV\WebsiteBundle\Services\Facebook
 */
class FacebookUser
{
    /**
     * @var FacebookSession
     */
    private $session;
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $facebookUserId;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $profilePicture;
    /**
     * @var string
     */
    private $profilePictureThumb;
    /**
     * @var string
     */
    private $type;
    /**
     * @var bool
     */
    private $subscribed;

    /**
     * @param FacebookSession $session
     * @param ObjectRepository $repository
     * @param ObjectManager $manager
     * @throws \Facebook\FacebookRequestException
     */
    public function __construct(FacebookSession $session, ObjectRepository $repository, ObjectManager $manager)
    {
        $this->session = $session;
        $request = new FacebookRequest($this->session, 'GET', '/me');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        $this->facebookUserId = $graphObject->getProperty('id');
        $this->email = $graphObject->getProperty('email');
        $this->name = $graphObject->getProperty('name');
        $localUser = $repository->findOneBy(array('facebookUserId'=> $this->facebookUserId));
        if (is_null($localUser)) {
            $localUser = new User($this->facebookUserId, $this->email);
            $manager->persist($localUser);
            $manager->flush();
        }
        $this->id = $localUser->getId();
        $this->type = $localUser->getType();
        $this->subscribed = $localUser->getSubscribed();

        $profileThumb = new FacebookRequest($this->session, 'GET', '/me/picture', array('redirect'=> false, 'type'=> 'square'));
        $this->profilePictureThumb = $profileThumb->execute()->getGraphObject()->getProperty('url');

        $profilePicture = new FacebookRequest($this->session, 'GET', '/me/picture', array('redirect'=> false, 'type'=> 'large'));
        $this->profilePicture = $profilePicture->execute()->getGraphObject()->getProperty('url');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFacebookUserId()
    {
        return $this->facebookUserId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @return string
     */
    public function getProfilePictureThumb()
    {
        return $this->profilePictureThumb;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isSubscribed()
    {
        return $this->subscribed;
    }
}