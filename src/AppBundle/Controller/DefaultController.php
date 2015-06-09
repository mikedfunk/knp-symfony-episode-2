<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UserBundle\Entity\UserRepository;

/**
 * @Route(service="app_bundle.controller.default_controller")
 */
class DefaultController extends Controller
{

    /**
     * UserRepository instance
     *
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * dependency injection
     *
     * @param UserRepository $userRepository
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        // dump($this->userRepository->findOneByUsernameOrEmail('mike@mikefunk.com'));
        // exit;
        return $this->render('default/index.html.twig');
    }

    /**
     * Description
     *
     * @Route("/new", name="new")
     * @return array
     */
    public function newAction()
    {
        $this->enforceUserSecurity();
        return new Response('test');
    }

    /**
     * authorize/deauthorize user
     *
     * @throws AccessDeniedException
     * @return bool
     */
    private function enforceUserSecurity()
    {
        $securityContext = $this->get('security.context');
        if (!$securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('NEED USER ROLE!');
        }
    }
}
