<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\RegistrationType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Generate a token, to activate account
            $user->setConfirmationToken(base64_encode(random_bytes(10)));

            // Encode the password
            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('app.mailer')->sendUserConfirmation($user);
            $this->addFlash('success', 'You have been successfully registered.');

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'user/registration/register.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/register/activate-account/{username}/{token}", name="user_activation")
     */
    public function activateAction($username, $token)
    {
        $username = rawurldecode($username);
        $token = rawurldecode($token);

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByEmail($username);

        if ($user->isEnabled()) {
            $this->addFlash('warning', 'Your account is already activated.');

            return $this->redirectToRoute('login');
        }

        if (null === $user->getConfirmationToken() ||
            !hash_equals($user->getConfirmationToken(), $token)
        ) {
            $this->addFlash('warning', 'The confirmation token is not valid.');

            return $this->redirectToRoute('login');
        }

        $user->setIsActive(true);
        $user->setConfirmationToken(null);
        $em->flush();

        $this->addFlash('success', 'Your account have been successfully activated.');

        return $this->redirectToRoute('login');
    }
}
