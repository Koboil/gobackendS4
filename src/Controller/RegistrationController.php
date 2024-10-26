<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Event\UserEvent;
use App\Form\RegistrationType;
use App\Repository\UserRepository as RepositoryUserRepository;
use App\Security\EmailVerifier;
 use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[AsController]
class RegistrationController extends AbstractController
{
    private $emailVerifier;
    private $noReplyEmail;

    public function __construct(EmailVerifier $emailVerifier, $noReplyEmail)
    {
        $this->emailVerifier = $emailVerifier;
        $this->noReplyEmail = $noReplyEmail;
    }

    #[Route("/register", name: "app_register", priority: 100)]
    public function submit(
        Request                     $request,
        EventDispatcherInterface    $dispatcher,
        ManagerRegistry             $managerRegistry,
         UserPasswordHasherInterface $hasher,
        TranslatorInterface         $translator,
        TokenStorageInterface       $tokenStorage
    ): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser instanceof User) {
            return $this->redirect("/");
        }
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $managerRegistry->getManager();

            $user->setPassword(
                $form->has('plainPassword') ? $hasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ) : ''
            );
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                $translator->trans('Votre compte a été créé avec succès')
            );

            $userEvent = new UserEvent($user);
            $dispatcher->dispatch($userEvent, UserEvent::CONFIRM_EMAIL);

            // Check if the redirect_url is present in the query parameters
            $redirectUrl = $request->query->get('redirect_url');

            if ($redirectUrl) {
                // Manually authenticate the user
                $token = new UsernamePasswordToken($user, "main", $user->getRoles());
                $tokenStorage->setToken($token);

                return new RedirectResponse($redirectUrl);
            }


            return $this->redirect("/");
        }
        return $this->render('/auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: "app_verify_email")]
    public function verifyUserEmail(Request $request, RepositoryUserRepository $userRepository): Response
    {
        $id = $request->get('id'); // retrieve the user id from the url

        // Verify the user id exists and is not null
        if (null === $id) {
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($id);
        // Ensure the user exists in persistence
        if (null === $user) {
            return $this->redirectToRoute('app_account');
        }
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirect('/account');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        //$this->addFlash('success', 'Votre email a été bien vérifié');

        return $this->redirectToRoute('app_account');
    }

    #[Route('/register/resend-email/{id}', name: 'app_register_resend_email')]
    public function resendEmail($noReplyEmail, User $user): \Symfony\Component\HttpFoundation\JsonResponse
    {
        if ($user === null) {
            return $this->json([
                "message" => "You are not allowed"
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from($this->noReplyEmail)
                ->to($user->getEmail())
                ->subject('Confirm your email')
                ->htmlTemplate('emails/confirmation_email.html.twig')
        );

        //$this->addFlash('success', 'Veuillez vérifier votre adresse email.');
        return $this->json([
            "message" => "The link was resent. Please check your mailbox"
        ]);
    }
}
