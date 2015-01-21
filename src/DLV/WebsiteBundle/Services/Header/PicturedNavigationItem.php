<?php
namespace DLV\WebsiteBundle\Services\Header;


use Symfony\Component\Routing\Router;

/**
 * Class PicturedNavigationItem
 * @package DLV\WebsiteBundle\Services\Header
 */
class PicturedNavigationItem extends NavigationItem
{
    /**
     * @var string
     */
    protected $picture;

    /**
     * @param array $name
     * @param string $route
     * @param array $parameters
     * @param string $picture
     */
    public function __construct($name, $route, $parameters, $picture)
    {
        $this->name = $name[self::$language];
        $this->route = $route;
        $this->parameters = $parameters;
        $this->picture = $picture;
        $this->url = self::$router->generate($this->route, $parameters, Router::ABSOLUTE_URL);
        $this->hasSubPages = false;
    }

    /**
     * @param bool $deep
     * @return array
     */
    public function getData($deep = false)
    {
        $data = array(
            'name'=> $this->name,
            'picture'=> $this->picture,
            'url'=> $this->url,
            'active'=> $this->active
        );
        if ($deep && $this->hasSubPages) {
            foreach ($this->subPages as $subPage) {
                $data['subPages'][] = $subPage->getData($deep);
            }
        }
        return $data;
    }
}