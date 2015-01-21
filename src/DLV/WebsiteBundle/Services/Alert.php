<?php
/**
 * Created by PhpStorm.
 * User: samuel.moncarey
 * Date: 30-12-2014
 * Time: 19:45
 */

namespace DLV\WebsiteBundle\Services;


/**
 * Class Alert
 * @package DLV\WebsiteBundle\Services
 */
/**
 * Class Alert
 * @package DLV\WebsiteBundle\Services
 */
class Alert
{

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $type;
    /**
     * @var boolean
     */
    private $is_set;

    function __construct()
    {
        $this->is_set = false;
    }

    /**
     * @param string $title
     * @param string $message
     * @param string $type
     * @return Alert
     */
    public static function create($title, $message, $type)
    {
        $alert = new Alert();
        $alert->set($title, $message, $type);
        return $alert;
    }

    /**
     * @return boolean
     */
    public function is_Set()
    {
        return $this->is_set;
    }

    /**
     * @param string $title
     * @param string $message
     * @param string $type
     */
    public function set($title, $message, $type)
    {
        $this->setTitle($title)->setMessage($message)->setType($type);
        $this->is_set = true;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     * @return $this
     */
    private function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     * @return $this
     */
    private function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    private function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}