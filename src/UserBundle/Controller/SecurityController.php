<?php
/**
 * Security controller
 *
 * @package Airliners
 * @copyright 2015 Demand Media, Inc. All Rights Reserved.
 */
namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * SecurityController
 *
 * @author Michael Funk <mike.funk@demandmedia.com>
 * @Route(service="user_bundle.controller.security_controller")
 */
class SecurityController extends Controller
{

    /**
     * AuthenticationUtils instance
     *
     * @var AuthenticationUtils $authenticationUtils
     */
    protected $authenticationUtils;

    /**
     * dependency injection
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return void
     */
    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @Route("/login", name="login_form")
     * @Template()
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {

        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return [
            // last username entered by the user
            'last_username' => $lastUsername,
            'error'         => $error,
        ];
    }

    /**
     * check for login auth
     *
     * @Route("/login_check", name="login_check")
     * @return void
     */
    public function loginCheckAction()
    {
        //
    }

    /**
     * logout action
     *
     * @Route("/logout", name="logout")
     * @return void
     */
    public function logoutAction()
    {
        //
    }
}
