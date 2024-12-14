<?php

namespace App\Controller;
use App\Form\DeviceFormType;
use App\Entity\Device;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeviceController extends AbstractController
{
    #[Route('/device', name: 'route_to_device_list')]
    public function listDevices(EntityManagerInterface $em): Response
    {
        $devices = $em->getRepository(Device::class)->findAll();

        return $this->render('device/index.html.twig', [
            'devices' => $devices,
        ]);
    }

    #[Route('/device/add', name: 'route_to_add_device')]
    public function addDevice(Request $request, EntityManagerInterface $em): Response
    {
        $device = new Device();

        $form = $this->createForm(DeviceFormType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($device);
            $em->flush();

            $this->addFlash('success', 'Zařízení bylo úspěšně přidáno.');

            return $this->redirectToRoute('route_to_device_list'); // Přesměrování na seznam zařízení
        }

        return $this->render('device/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/device/{id}/edit', name: 'route_to_edit_device')]
    public function editDevice(Device $device, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DeviceFormType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Zařízení bylo úspěšně upraveno.');

            return $this->redirectToRoute('route_to_device_list');
        }

        return $this->render('device/edit.html.twig', [
            'form' => $form->createView(),
            'device' => $device,
        ]);
    }
    #[Route('/device/{id}/delete', name: 'route_to_delete_device', methods: ['POST'])]
    public function deleteDevice(Request $request, Device $device, EntityManagerInterface $em): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $device->getId(), $submittedToken)) {
            $em->remove($device);
            $em->flush();

            $this->addFlash('success', 'Zařízení bylo úspěšně smazáno.');
        } else {
            $this->addFlash('error', 'CSRF token je neplatný.');
        }

        return $this->redirectToRoute('route_to_device_list');
    }


}
