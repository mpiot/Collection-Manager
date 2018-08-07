<?php

namespace App\Controller;

use App\Form\Type\ResetPasswordType;
use App\Form\Type\ResettingType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ResettingController extends Controller
{
    /**
     * @Route("/resetting/request", name="user_resetting_request", methods={"GET", "POST"})
     */
    public function resettingRequestAction(Request $request)
    {
        $form = $this->createForm(ResettingType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];

            // Get user
            $userManager = $this->get('App\Utils\UserManager');
            $user = $userManager->findUserBy(['email' => $email]);

            if (null !== $user && $user->isEnabled()) {
                // Generate a token, to reset password
                $userManager->generateToken($user);
                $userManager->updateUser($user);

                // Send an email with the reset link
                $this->get('App\Utils\Mailer')->sendPasswordResetting($user);
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
     * @Route("/resetting/{username}/{token}", name="user_resetting", methods={"GET", "POST"})
     */
    public function resettingAction($username, $token, Request $request)
    {
        $username = rawurldecode($username);
        $token = rawurldecode($token);

        // Get user
        $userManager = $this->get('App\Utils\UserManager');
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
