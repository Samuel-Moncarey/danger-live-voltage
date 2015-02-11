<?php
namespace DLV\WebsiteBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="lineup")
 */
class Lineup
{
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Event",  inversedBy="bands")
     * @ORM\JoinColumn(name="event", referencedColumnName="id")
     **/
    protected $event;
    
    /**
     * @ORM\ManyToOne(targetEntity="Band")
     * @ORM\JoinColumn(name="band", referencedColumnName="id")
     **/
    protected $band;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $time;
    

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
     * Set time
     *
     * @param \DateTime $time
     * @return Lineup
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set event
     *
     * @param \DLV\WebsiteBundle\Entity\Event $event
     * @return Lineup
     */
    public function setEvent(\DLV\WebsiteBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \DLV\WebsiteBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set band
     *
     * @param \DLV\WebsiteBundle\Entity\Band $band
     * @return Lineup
     */
    public function setBand(\DLV\WebsiteBundle\Entity\Band $band = null)
    {
        $this->band = $band;

        return $this;
    }

    /**
     * Get band
     *
     * @return \DLV\WebsiteBundle\Entity\Band 
     */
    public function getBand()
    {
        return $this->band;
    }
}
