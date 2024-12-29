<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'route_to_user_list')]
    public function listUsers(EntityManagerInterface $em): Response
    {
        // Získání všech uživatelů
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/add', name: 'route_to_add_user')]
    public function addUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = new User();

        // Formulář pro vytvoření uživatele
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class, [
                'label' => 'Uživatelské jméno',
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Heslo',
                'mapped' => false, // Neprovádět automatické mapování na entitu
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded' => true, // Zobrazí jako radio buttony
                'multiple' => false, // Pouze jedna role
                'mapped' => false, // Role nebude mapována přímo na entitu
                'label' => 'Role uživatele',
            ])
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Nastavení role z formuláře
            $selectedRole = $form->get('role')->getData();
            $user->setRoles([$selectedRole]);
        
            // Hashování hesla
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        
            $em->persist($user);
            $em->flush();
        
            $this->addFlash('success', 'Uživatel byl úspěšně přidán.');
        
            return $this->redirectToRoute('route_to_user_list');
        }
        

        return $this->render('user/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'route_to_edit_user')]
    public function editUser(
        User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Nemáte oprávnění měnit uživatele.');
        }

        $form = $this->createFormBuilder($user)
            ->add('username', null, ['label' => 'Username'])
            ->add('email', null, ['label' => 'Email'])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded' => true,    // Zobrazí jako radio buttony
                'multiple' => false,   // Pouze jedna role
                'mapped' => false,     // Zakáže automatické mapování
                'label' => 'Role uživatele',
                'data' => $user->getRoles()[0] ?? 'ROLE_USER', // Předáme první roli
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nové heslo (volitelné)',
                'mapped' => false,
                'required' => false,
            ])
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Nastavíme roli manuálně jako pole
            $selectedRole = $form->get('role')->getData();
            $user->setRoles([$selectedRole]);
        
            // Zpracování hesla, pokud bylo zadáno
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
        
            // Uložíme změny do databáze
            $em->flush();
        
            $this->addFlash('success', 'Uživatel byl úspěšně upraven.');
            return $this->redirectToRoute('route_to_user_list');
        }
        

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'route_to_delete_user', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em): Response
    {
        // Získáme aktuálně přihlášeného uživatele
        $currentUser = $this->getUser();

        // Zabránění smazání sebe sama
        if ($currentUser === $user) {
            $this->addFlash('error', 'Nemůžete smazat sami sebe.');
            return $this->redirectToRoute('route_to_user_list');
        }

        // CSRF kontrola
        $submittedToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $submittedToken)) {
            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'Uživatel byl úspěšně smazán.');
        } else {
            $this->addFlash('error', 'CSRF token je neplatný.');
        }

        return $this->redirectToRoute('route_to_user_list');
    }

}
