<?php
namespace DLV\WebsiteBundle\Services\Header;


/**
 * Class ExternalNavigationItem
 * @package DLV\WebsiteBundle\Services\Header
 */
class ExternalNavigationItem extends NavigationItem
{
    /**
     * @param array $name
     * @param string $url
     */
    public function __construct($name, $url)
    {
        $this->name = $name[self::$language];
        $this->url = $url;
        $this->hasSubPages = false;
    }
}