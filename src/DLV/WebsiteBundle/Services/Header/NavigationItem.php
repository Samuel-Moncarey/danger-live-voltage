<?php
namespace DLV\WebsiteBundle\Services\Header;


use Symfony\Component\Routing\Router;
/**
 * Class NavigationItem
 * @package DLV\WebsiteBundle\Services\Header
 */
class NavigationItem
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $route;
    /**
     * @var array
     */
    protected $parameters;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var boolean
     */
    protected $hasSubPages = false;
    /**
     * @var NavigationItem[]
     */
    protected $subPages = array();
    /**
     * @var boolean
     */
    protected $active = false;
    /**
     * @var Router
     */
    protected static $router;
    /**
     * @var string
     */
    protected static $language;

    /**
     * @param array $name
     * @param string $route
     * @param array $parameters
     */
    public function __construct($name, $route, $parameters)
    {
        $this->name = $name[self::$language];
        $this->route = $route;
        $this->parameters = $parameters;
        $this->url = self::$router->generate($this->route, $parameters, Router::ABSOLUTE_URL);
        $this->hasSubPages = false;
    }

    /**
     * @param Router $router
     */
    public static function setRouter(Router $router)
    {
        self::$router = $router;
    }

    /**
     * @param string $language
     */
    public static function setLanguage($language)
    {
        self::$language = $language;
    }

    /**
     * @param array $name
     * @param array $parameters
     */
    public function addSubPage($name, $parameters) {
        $this->hasSubPages = true;
        $this->subPages[] = new NavigationItem($name, $this->route, $parameters);
    }

    /**
     *
     */
    public function setActive()
    {
        $this->active = true;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return NavigationItem[]
     */
    public function getSubPages()
    {
        return $this->subPages;
    }

    /**
     * @param string $parameter
     * @return string
     */
    public function getParameter($parameter)
    {
        return $this->parameters[$parameter];
    }

    /**
     * @param bool $deep
     * @return array
     */
    public function getData($deep = false)
    {
        $data = array(
            'name'=> $this->name,
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