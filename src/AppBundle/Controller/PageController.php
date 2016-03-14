<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Page controller.
 */
class PageController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('page/index.html.twig', []);
    }
}
