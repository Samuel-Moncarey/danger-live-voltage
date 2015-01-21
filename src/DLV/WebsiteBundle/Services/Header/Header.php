<?php
namespace DLV\WebsiteBundle\Services\Header;


use DLV\WebsiteBundle\Services\Facebook\FacebookUser;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class Header
 * @package DLV\WebsiteBundle\Services
 */
class Header
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $pageTitle;

    /**
     * @var NavigationItem[]
     */
    protected $pages = array();

    /**
     * @var string
     */
    protected $activePage;

    /**
     * @var NavigationItem[]
     */
    protected $subPages = array();

    /**
     * @var string
     */
    protected $activeSubPage;

    /**
     * @var NavigationItem[]
     */
    protected $breadcrumbs = array();

    /**
     * @var NavigationItem[]|PicturedNavigationItem[]|ExternalNavigationItem[]
     */
    protected $sessionNav = array();

    /**
     * @var string
     */
    protected $languageChooserTitle;

    /**
     * @var NavigationItem[]
     */
    protected $languages;

    /**
     * @var string
     */
    protected $activeLanguage;

    /**
     * @param Request $request
     * @param Router $router
     * @param string $language
     * @param boolean|FacebookUser $user
     * @param string $facebookLoginUrl
     */
    public function __construct(Request $request, Router $router, $language, $user, $facebookLoginUrl)
    {
        $this->request = $request;
        NavigationItem::setRouter($router);
        NavigationItem::setLanguage($language);
        $this->setNavigation();
        $this->setActiveItems();
        $this->setBreadcrumbs();
        $this->setSessionNavigation($user, $facebookLoginUrl);
        $this->setLanguageChooser($language);
        $this->setPageTitle();
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @param string $pageTitle
     */
    public function setPageTitle($pageTitle = null)
    {
        if (is_null($pageTitle)) {
            $lastcrumb = null;
            foreach ($this->breadcrumbs as $key => $breadcrumb) {
                ($key != 'brand') ? $lastcrumb = $breadcrumb : $lastcrumb = $this->pages[$this->activePage];
            }
            $this->pageTitle = $lastcrumb->getData()['name'];
        }
        else {
            $this->pageTitle = $pageTitle;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $headerData = array(
            'breadcrumbs'=> $this->getBreadcrumbData(),
            'sessionNavigation'=> $this->getSessionNavigationData(),
            'languageOptions'=> array(
                'dropdownTitle'=> $this->languageChooserTitle,
                'options'=> $this->getLanguageOptionsData()
            ),
            'pages'=> $this->getPagesData(),
            'subPages'=> $this->getSubPageData()
        );
        return $headerData;
    }

    /**
     * @return array
     */
    public function getPagesData()
    {
        $data = array();
        foreach ($this->pages as $key => $page) {
            $data[$key] = $page->getData();
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getSubPageData()
    {
        $data = array();
        $subpages = $this->pages[$this->activePage]->getSubPages();
        foreach ($subpages as $subpage) {
            $data[$subpage->getParameter('subpage')] = $subpage->getData();
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getBreadcrumbData()
    {
        $data = array();
        foreach ($this->breadcrumbs as $key => $breadcrump) {
            $data[$key] = $breadcrump->getData();
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getSessionNavigationData()
    {
        $data = array();
        foreach ($this->sessionNav as $key => $sessionNavItem) {
            $data[$key] = $sessionNavItem->getData();
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getLanguageOptionsData()
    {
        $data = array();
        $data[$this->activeLanguage] = $this->languages[$this->activeLanguage]->getData();
        foreach ($this->languages as $code => $language) {
            if ($code != $this->activeLanguage) {
                $data[$code] = $language->getData();
            }
        }
        return $data;
    }

    /**
     * @param $activeLanguage
     */
    private function setLanguageChooser($activeLanguage)
    {
        $languages = array('en'=> 'English', 'nl'=> 'Nederlands', 'fr'=> 'Français');
        $languageTitles = array('en'=> 'Language', 'nl'=> 'Taal', 'fr'=> 'Langue');
        $this->activeLanguage = $activeLanguage;
        $this->languageChooserTitle = $languageTitles[$this->activeLanguage];
        foreach ($languages as $code => $language) {
            $this->languages[$code] = new NavigationItem(
                array('en'=> $language, 'nl'=> $language, 'fr'=> $language),
                'dlv_website_set_language',
                array('language'=> $code)
            );
            if($this->activeLanguage == $code) {
                $this->languages[$code]->setActive();
            }
        }

    }

    /**
     * @param boolean|FacebookUser $user
     * @param string $facebookLoginUrl
     */
    private function setSessionNavigation($user, $facebookLoginUrl)
    {
        if ($user instanceof FacebookUser) {
            $this->sessionNav['user'] = new PicturedNavigationItem(
                array('en'=> $user->getName(), 'nl'=> $user->getName(), 'fr'=> $user->getName()),
                'dlv_website_vippage',
                array('page'=> 'vip', 'subpage'=> 'profile'),
                $user->getProfilePictureThumb()
            );
            $this->sessionNav['logout'] = new NavigationItem(
                array('en'=> 'Log Out', 'nl'=> 'Afmelden', 'fr'=> 'Se Déconnecter'),
                'dlv_website_facebook_logout',
                array()
            );
        }
        else {
            $this->sessionNav['login'] = new ExternalNavigationItem(
                array('en'=> 'Connect with Facebook', 'nl'=> 'Verbinden met Facebook', 'fr'=> 'Se connecter avec Facebook'),
                $facebookLoginUrl
            );
        }
    }

    /**
     *
     */
    private function setBreadcrumbs()
    {
        $dlv_brand = new NavigationItem(
            array('en'=> 'Danger Live Voltage', 'nl'=> 'Danger Live Voltage', 'fr'=> 'Danger Live Voltage'),
            'dlv_website_homepage',
            array('page'=> 'home', 'subpage'=> 'summary')
        );
        $this->breadcrumbs['brand'] = $dlv_brand;

        if (!($this->activePage == 'home' && $this->activeSubPage == 'summary')) {
            $this->breadcrumbs['page'] = $this->pages[$this->activePage];
            if ($this->activeSubPage != 'summary') {
                foreach ($this->pages[$this->activePage]->getSubPages() as $subPage) {
                    if ($subPage->isActive()) {
                        $this->breadcrumbs['subpage'] = $subPage;
                    }
                }
            }
            else {

            }
        }
    }

    /**
     *
     */
    private function setActiveItems()
    {
        $this->activePage = $this->request->attributes->get('page');
        $this->pages[$this->activePage]->setActive();
        $this->activeSubPage = $this->request->attributes->get('subpage', 'summary');
        if($this->activeSubPage != 'summary') {
            foreach ($this->pages[$this->activePage]->getSubPages() as $subPage) {
                if ($subPage->getParameter('subpage') == $this->activeSubPage) {
                    $subPage->setActive();
                }
            }
        }
    }

    /**
     *
     */
    private function setNavigation()
    {
        $home = new NavigationItem(
            array('en'=> 'Home', 'nl'=> 'Home', 'fr'=> 'Accueil'),
            'dlv_website_homepage',
            array('page'=> 'home', 'subpage'=> 'summary')
        );
        $home->addSubPage(
                array('en'=> 'Concept', 'nl'=> 'Concept', 'fr'=> 'Concept'),
                array('page'=> 'home', 'subpage'=> 'concept')
            );
        $home->addSubPage(
                array('en'=> 'Behind The Scenes', 'nl'=> 'Achter De Scenes', 'fr'=> 'En Coulisse'),
                array('page'=> 'home', 'subpage'=> 'behind-the-scenes')
            );
        $home->addSubPage(
                array('en'=> 'News', 'nl'=> 'Nieuws', 'fr'=> 'Nouvelles'),
                array('page'=> 'home', 'subpage'=> 'news')
            );
        $this->pages['home'] = $home;

        $events = new NavigationItem(
            array('en'=> 'Events', 'nl'=> 'Events', 'fr'=> 'Evenements'),
            'dlv_website_eventspage',
            array('page'=> 'events', 'subpage'=> 'summary')
        );
        $events->addSubPage(
                array('en'=> 'Upcomming Events', 'nl'=> 'Komende Evenementen', 'fr'=> 'Evénements A Venir'),
                array('page'=> 'events', 'subpage'=> 'upcomming')
            );
        $events->addSubPage(
                array('en'=> 'Previous Events', 'nl'=> 'Vorige Evenementen', 'fr'=> 'Evénements Précédents'),
                array('page'=> 'events', 'subpage'=> 'previous')
            );
        $this->pages['events'] = $events;

        $bookings = new NavigationItem(
            array('en'=> 'Bookings', 'nl'=> 'Bookings', 'fr'=> 'Bookings'),
            'dlv_website_bookingspage',
            array('page'=> 'bookings', 'subpage'=> 'summary')
        );
        $bookings->addSubPage(
                array('en'=> 'Host An Event', 'nl'=> 'Host Een Evenementen', 'fr'=> 'Organisez Un Evénement'),
                array('page'=> 'bookings', 'subpage'=> 'host-an-event')
            );
        $bookings->addSubPage(
                array('en'=> 'Get On Stage', 'nl'=> 'Treed Op', 'fr'=> 'Monter Sur Scène'),
                array('page'=> 'bookings', 'subpage'=> 'get-on-stage')
            );
        $this->pages['bookings'] = $bookings;

        $media = new NavigationItem(
            array('en'=> 'Media', 'nl'=> 'Media', 'fr'=> 'Media'),
            'dlv_website_mediapage',
            array('page'=> 'media', 'subpage'=> 'summary')
        );
        $media->addSubPage(
                array('en'=> 'Pictures', 'nl'=> 'Foto\'s', 'fr'=> 'Fotos'),
                array('page'=> 'media', 'subpage'=> 'pictures')
            );
        $media->addSubPage(
                array('en'=> 'Video\'s', 'nl'=> 'Video\'s', 'fr'=> 'Videos'),
                array('page'=> 'media', 'subpage'=> 'videos')
            );
        $this->pages['media'] = $media;

        $links = new NavigationItem(
            array('en'=> 'Links', 'nl'=> 'Links', 'fr'=> 'Liens'),
            'dlv_website_linkspage',
            array('page'=> 'links', 'subpage'=> 'summary')
        );
        $links->addSubPage(
                array('en'=> 'Sponsors', 'nl'=> 'Sponsors', 'fr'=> 'Sponsors'),
                array('page'=> 'links', 'subpage'=> 'sponsors')
            );
        $links->addSubPage(
                array('en'=> 'Bands', 'nl'=> 'Bands', 'fr'=> 'Groupes'),
                array('page'=> 'links', 'subpage'=> 'bands')
            );
        $links->addSubPage(
                array('en'=> 'Locations', 'nl'=> 'Locaties', 'fr'=> 'Locations'),
                array('page'=> 'links', 'subpage'=> 'locations')
            );
        $this->pages['links'] = $links;

        $vip = new NavigationItem(
            array('en'=> 'VIP', 'nl'=> 'VIP', 'fr'=> 'VIP'),
            'dlv_website_vippage',
            array('page'=> 'vip', 'subpage'=> 'profile')
        );
        $vip->addSubPage(
                array('en'=> 'Profile', 'nl'=> 'Profiel', 'fr'=> 'Profile'),
                array('page'=> 'vip', 'subpage'=> 'profile')
            );
        $vip->addSubPage(
                array('en'=> 'Settings', 'nl'=> 'Instellingen', 'fr'=> 'Paramètres'),
                array('page'=> 'vip', 'subpage'=> 'settings')
            );
        $this->pages['vip'] = $vip;
    }
}