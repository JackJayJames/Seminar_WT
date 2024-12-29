<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Pokud je uživatel přihlášen, přesměrujeme ho na domovskou stránku
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Vytvoření nového uživatele a formuláře
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Nastavení role uživatele na "ROLE_USER"
            $user->setRoles(['ROLE_USER']);

            // Získání a hashování hesla
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Uložení uživatele do databáze
            $entityManager->persist($user);
            $entityManager->flush();

            // Přesměrování po úspěšné registraci
            return $this->redirectToRoute('app_home');
        }

        // Zobrazení registračního formuláře
        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

}
