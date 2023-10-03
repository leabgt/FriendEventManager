<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Stripe\Stripe;
use Stripe\Customer;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;

class RegistrationController extends AbstractController
{
    // private $params;

    // public function __construct(ParameterBagInterface $params)
    // {
    //     $this->params = $params;
    // }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        // Récupérez la clé secrète Stripe depuis $_ENV
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];

        // Configurez la clé secrète Stripe
        Stripe::setApiKey($stripeSecretKey);

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créez un client Stripe et récupérez l'ID client
            $stripeCustomer = Customer::create([
                'email' => $user->getEmail(), // Supposons que l'email de l'utilisateur est également son email Stripe.
                'name' => $user->getFirstName() . ' ' . $user->getLastName(), // Combinez le prénom et le nom.
            ]);

            // Enregistrez l'ID client Stripe dans l'objet User
            $user->setStripeCustomerId($stripeCustomer->id);

            // Encodez le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $birthDateString = $form->get('birthDate')->getData();
            $birthDate = \DateTime::createFromFormat('Y-m-d', $birthDateString);
            $user->setBirthDate($birthDate);

            $entityManager->persist($user);
            $entityManager->flush();
            // Faites tout ce dont vous avez besoin ici, comme envoyer un e-mail.

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
