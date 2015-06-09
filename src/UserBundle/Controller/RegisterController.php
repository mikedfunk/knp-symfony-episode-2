<?php
/**
 * Register controller
 *
 * @package Airliners
 * @copyright 2015 Demand Media, Inc. All Rights Reserved.
 */
namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use UserBundle\Form\RegisterFormType;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * RegisterController
 *
 * @author Michael Funk <mike.funk@demandmedia.com>
 * @Route(service="user_bundle.controller.register_controller")
 */
class RegisterController extends Controller
{

    /**
     * password encoder
     *
     * @var Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoder;

    /**
     * entity manager
     *
     * @var Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * SecurityContext instance
     *
     * @var SecurityContextInterface $securityContext
     */
    protected $securityContext;

    /**
     * dependency injection
     *
     * @param Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoder
     * @param Doctrine\ORM\EntityManagerInterface $entityManager
     * @param SecurityContextInterface $securityContext
     *
     * @return void
     */
    public function __construct(
        EncoderFactoryInterface $encoder,
        EntityManagerInterface $entityManager,
        SecurityContextInterface $securityContext
    ) {
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
    }

    /**
     * @Route("/register", name="user_register")
     * @Method("GET")
     * @Template()
     * @return array
     */
    public function registerAction()
    {
        $form = $this->getRegisterForm()->createView();

        return compact('form');
    }

    /**
     * create register form
     *
     * @return Symfony\Component\Form\Form
     */
    private function getRegisterForm()
    {
        // defaults. this is crucial to pass to createForm() if you are using
        // entity annotation validation. It can be empty, you don't need to set
        // defaults.
        $user = new User();
        $user->setUsername('Joe');
        return $this->createForm(new RegisterFormType(), $user);

        // // default values
        // $user = new User();
        // $user->setUsername('Joe');

        // // make symfony return a hydrated user when submitting the form
        // $settings = ['data_class' => 'UserBundle\Entity\User'];
        // return $this->createFormBuilder($user, $settings)
            // ->add('username', 'text')
            // // ->add('email', 'email', ['required' => false]) // disable html5 validation
            // ->add('email', 'email')
            // ->add('plainPassword', 'repeated', [
                // 'type' => 'password'
            // ])
            // ->getForm();
    }

    /**
     * post to register
     *
     * @Route("/register", name="user_do_register")
     * @Method("POST")
     * @Template("UserBundle:register:register.html.twig")
     * @return Response|array
     */
    public function doRegisterAction(Request $request)
    {
        $form = $this->getRegisterForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            // create the user in the db
            $user = $form->getData();
            $user->setIsActive(true);
            $user->setPassword($this->encodePassword($user, $user->getPlainPassword()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->authenticateUser($user);

            $request->getSession()->getFlashbag()->add('success', 'A winner is you!');

            // redirect to the homepage
            return $this->redirectToRoute('homepage');
        }

        // send the form to the view
        return ['form' => $form->createView()];
    }

    /**
     * encode the password
     *
     * @param User $user
     * @param string $plainPassword
     * @return string
     */
    private function encodePassword(User $user, $plainPassword)
    {
        $encoder = $this->encoder->getEncoder($user);
        return $encoder->encodePassword($plainPassword, $user->getSalt());
    }

    /**
     * login the user
     *
     * @param User $user
     * @return void
     */
    private function authenticateUser(User $user)
    {
        $providerKey = 'secured_area'; // your firewall name
        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $this->securityContext->setToken($token);
    }
}
