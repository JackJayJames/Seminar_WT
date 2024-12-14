<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeFormType;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TypeController extends AbstractController
{
    #[Route('/type/add', name: 'route_to_add_type')]
    public function addType(Request $request, EntityManagerInterface $em): Response
    {
        // Vytvoření nové instance typu
        $type = new Type();

        // Vytvoření formuláře
        $form = $this->createForm(TypeFormType::class, $type);

        // Zpracování odeslaného formuláře
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($type); // Uložení do databáze
            $em->flush();

            // Přesměrování zpět na seznam
            return $this->redirectToRoute('route_to_type_list'); // Upravte na správnou routu
        }

        // Zobrazení formuláře v šabloně
        return $this->render('type/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/type/list', name: 'route_to_type_list')]
    public function list(TypeRepository $typeRepository): Response
    {
        $types = $typeRepository->findAll();

        return $this->render('type/index.html.twig', [
            'types' => $types,
        ]);
    }
    #[Route('/type/{id}/edit', name: 'route_to_edit_type')]
    public function editType(Type $type, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TypeFormType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Typ byl úspěšně upraven.');
            return $this->redirectToRoute('route_to_type_list');
        }

        return $this->render('type/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }
    #[Route('/type/{id}/delete', name: 'route_to_delete_type', methods: ['POST'])]
    public function deleteType(Request $request, Type $type, EntityManagerInterface $em): Response
    {
        // Ověření CSRF tokenu
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $type->getId(), $submittedToken)) {
            $em->remove($type);
            $em->flush();

            $this->addFlash('success', 'Typ byl úspěšně smazán.');
        } else {
            $this->addFlash('error', 'Neplatný CSRF token.');
        }

        return $this->redirectToRoute('route_to_type_list');
    }


}

