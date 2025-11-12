<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/customer')]
class CustomerController extends AbstractController
{
    #[Route('/login', name: 'customer_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('customer/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'customer_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CartService $cartService
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->isMethod('POST')) {
            $customer = new Customer();
            $customer->setFirstName($request->get('firstName'));
            $customer->setLastName($request->get('lastName'));
            $customer->setEmail($request->get('email'));
            $customer->setDocumentNumber($request->get('documentNumber'));
            $customer->setPhone($request->get('phone'));
            $customer->setAddress($request->get('address'));

            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword(
                $customer,
                $request->get('password')
            );
            $customer->setPassword($hashedPassword);

            // Validate
            $errors = $validator->validate($customer);
            
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                
                return $this->render('customer/register.html.twig', [
                    'customer' => $customer,
                ]);
            }

            // Check if passwords match
            if ($request->get('password') !== $request->get('confirmPassword')) {
                $this->addFlash('error', 'Las contraseñas no coinciden');
                
                return $this->render('customer/register.html.twig', [
                    'customer' => $customer,
                ]);
            }

            try {
                $entityManager->persist($customer);
                $entityManager->flush();

                $this->addFlash('success', 'Cuenta creada exitosamente. Ya puedes iniciar sesión.');
                
                return $this->redirectToRoute('customer_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al crear la cuenta. Por favor intenta nuevamente.');
            }
        }

        return $this->render('customer/register.html.twig', [
            'customer' => new Customer(),
        ]);
    }

    #[Route('/logout', name: 'customer_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/profile', name: 'customer_profile')]
    public function profile(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        return $this->render('customer/profile.html.twig');
    }
}