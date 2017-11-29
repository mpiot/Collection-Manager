<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\ResetPasswordType;
use AppBundle\Form\Type\ResettingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResettingController extends Controller
{
    /**
     * @Route("/resetting/request", name="user_resetting_request")
     * @Method({"GET", "POST"})
     */
    public function resettingRequestAction(Request $request)
    {
        $form = $this->createForm(ResettingType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];

            // Get user
            $userManager = $this->get('AppBundle\Utils\UserManager');
            $user = $userManager->findUserBy(['email' => $email]);

            if (null !== $user && $user->isEnabled()) {
                // Generate a token, to reset password
                $userManager->generateToken($user);
                $userManager->updateUser($user);

                // Send an email with the reset link
                $this->get('AppBundle\Utils\Mailer')->sendPasswordResetting($user);
            }

            // Alway return the same redirection and flash message
            $this->addFlash('success', 'An email containing the password reset procedure has been sent to you.');

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'user/resetting/resetting_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/resetting/{username}/{token}", name="user_resetting")
     * @Method({"GET", "POST"})
     */
    public function resettingAction($username, $token, Request $request)
    {
        $username = rawurldecode($username);
        $token = rawurldecode($token);

        // Get user
        $userManager = $this->get('AppBundle\Utils\UserManager');
        $user = $userManager->findUserBy(['email' => $username]);

        // Check the User
        if (null === $user
            || !$user->isEnabled()
            || null === $user->getConfirmationToken()
            || !hash_equals($user->getConfirmationToken(), $token)
        ) {
            $this->addFlash('warning', 'The confirmation token is not valid.');

            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Update the password
            $userManager->removeToken($user);
            $userManager->updateUser($user);

            // Add a flash message
            $this->addFlash('success', 'Your password have been successfully changed. You can now log in with this new one.');

            return $this->redirectToRoute('login');
        }

        return $this->render('user/resetting/resetting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
