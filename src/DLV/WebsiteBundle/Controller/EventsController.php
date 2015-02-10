<?php
/**
 * Created by PhpStorm.
 * User: samuel.moncarey
 * Date: 31-12-2014
 * Time: 4:56
 */

namespace DLV\WebsiteBundle\Controller;

use DLV\WebsiteBundle\Services\Header\Header;


class EventsController extends DefaultController
{
    public function eventAction($slug)
    {
        ob_start();
        $this->initialize(true);
        $header = new Header($this->request, $this->get('router'), $this->language, $this->user, $this->facebookLoginUrl);
        $this->template_data['header'] = $header->toArray();
        $this->template_data['pagetitle'] = $header->getPageTitle();
        $this->template_data['vardump'] = ob_get_clean();
        return $this->render('DLVWebsiteBundle:Default:events/summary.html.twig', $this->template_data);
    }

}