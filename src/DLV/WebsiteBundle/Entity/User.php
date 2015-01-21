<?php

namespace DLV\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fb_user_id", type="string", length=255)
     */
    private $facebookUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10)
     */
    private $type = 'user';

    /**
     * @var boolean
     *
     * @ORM\Column(name="subscribed", type="boolean")
     */
    private $subscribed = false;

    /**
     * @param string $facebookUserId
     * @param string $email
     */
    function __construct($facebookUserId, $email)
    {
        $this->facebookUserId = $facebookUserId;
        $this->email = $email;
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set facebookUserId
     *
     * @param string $facebookUserId
     * @return User
     */
    public function setFacebookUserId($facebookUserId)
    {
        $this->facebookUserId = $facebookUserId;

        return $this;
    }

    /**
     * Get facebookUserId
     *
     * @return string
     */
    public function getFacebookUserId()
    {
        return $this->facebookUserId;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set subscribed
     *
     * @param boolean $subscribed
     * @return User
     */
    public function setSubscribed($subscribed)
    {
        $this->subscribed = $subscribed;

        return $this;
    }

    /**
     * Get subscribed
     *
     * @return boolean 
     */
    public function getSubscribed()
    {
        return $this->subscribed;
    }
}
