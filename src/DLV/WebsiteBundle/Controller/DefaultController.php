<?php
namespace DLV\WebsiteBundle\Controller;


use DLV\WebsiteBundle\Services\Alert;
use DLV\WebsiteBundle\Services\Facebook\FacebookObject;
use DLV\WebsiteBundle\Services\Facebook\FacebookPage;
use DLV\WebsiteBundle\Services\Facebook\FacebookUser;
use DLV\WebsiteBundle\Services\Header\Header;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Facebook\FacebookSession;
use Facebook\GraphObject;
use DLV\WebsiteBundle\Services\Facebook\FacebookLogin;
use DLV\WebsiteBundle\Services\Facebook\FacebookPost;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\GraphUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class DefaultController
 * @package DLV\WebsiteBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @var Request;
     */
    protected $request;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var ParameterBag
     */
    protected $lastRequestAttr;
    /**
     * @var string
     */
    protected $language;
    /**
     * @var Alert
     */
    protected $alert;
    /**
     * @var FacebookSession
     */
    protected $facebookSession;
    /**
     * @var FacebookLogin
     */
    protected $facebookLoginHelper;
    /**
     * @var string
     */
    protected $facebookLoginUrl;
    /**
     * @var bool|FacebookUser
     */
    protected $user = false;

    protected $template;
    protected $template_data;
    protected $response;

    /**
     *
     */
    protected function initialize($register = false)
    {
        $this->request = $this->get('request');
        $this->session = $this->get('session');
        $this->session->start();
        $this->lastRequestAttr = $this->session->get('lastRequestAttr', $this->request->attributes);
        if ($register) {
            $this->session->set('lastRequestAttr', $this->request->attributes);
        }
        $this->language = $this->session->get('language', $this->getLanguageFromHeaders());
        $this->session->set('language', $this->language);
        $this->alert = new Alert();
        if ($this->session->has('alert')) {
            $this->alert = $this->session->get('alert');
            $this->session->remove('alert');
        }
        FacebookSession::setDefaultApplication('754718704577151','b8e5d4aad34184413fed5078a41572bd');
        $this->facebookSession = FacebookSession::newAppSession();
        if ($this->session->has('facebookSession')) {
            $this->facebookSession = $this->session->get('facebookSession');
            $repository = $this->getDoctrine()->getRepository('DLVWebsiteBundle:User');
            $manager = $this->getDoctrine()->getManager();
            $this->user = new FacebookUser($this->facebookSession, $repository, $manager);
        }
        $this->facebookLoginHelper = new FacebookLogin($this->generateUrl('dlv_website_facebook_login', array(), true));
        $this->facebookLoginUrl = $this->facebookLoginHelper->getLoginUrl(array('public_profile','email'));
        FacebookPage::setSession($this->facebookSession);
        FacebookObject::setSession($this->facebookSession);
    }

    /**
     * @return string
     */
    private function getLanguageFromHeaders()
    {
        $acceptLanguage = $this->request->server->get('HTTP_ACCEPT_LANGUAGE', 'en');
        $acceptLanguageArray = explode(',', $acceptLanguage);
        $languageOptions = array('en','nl','fr');
        $languageIndex = 0;
        foreach ($acceptLanguageArray as $language) {
            foreach ($languageOptions as $index => $languageOption) {
                if(preg_match('/' . $languageOption . '/', $language)) {
                    $languageIndex = $index;
                    break 2;
                }
            }
        }
        return $languageOptions[$languageIndex];
    }

    protected function sendResponse()
    {

    }

    /**
     * @return Response
     */
    public function indexAction($page, $subpage)
    {
        ob_start();
        $this->initialize(true);
        $header = new Header($this->request, $this->get('router'), $this->language, $this->user, $this->facebookLoginUrl);
        $this->template_data['header'] = $header->toArray();
        $this->template_data['pagetitle'] = $header->getPageTitle();
        //print_r(FacebookPage::fetchPosts());
        $this->template_data['vardump'] = ob_get_clean();
        return $this->render('DLVWebsiteBundle:Default:' . $page . '/' . $subpage . '.html.twig', $this->template_data);
    }

    public function setLanguageAction($language)
    {
        $this->initialize();
        $this->session->set('language', $language);
        return $this->redirectToRoute($this->lastRequestAttr->get('_route'), $this->lastRequestAttr->get('_route_params'));
    }

    /**
     *
     */
    public function facebookLoginAction()
    {
        $this->initialize();
        $this->facebookSession = $this->facebookLoginHelper->getSessionFromRedirect();
        $this->session->set('facebookSession', $this->facebookSession);
        return $this->redirectToRoute($this->lastRequestAttr->get('_route'), $this->lastRequestAttr->get('_route_params'));
    }

    /**
     *
     */
    public function facebookLogoutAction()
    {
        $this->initialize();
        $this->session->remove('facebookSession');
        return $this->redirectToRoute($this->lastRequestAttr->get('_route'), $this->lastRequestAttr->get('_route_params'));
    }
}
