<?php

namespace App\Controller;

use App\Entity\Contacto;
use App\Form\ContactoFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{
    #[Route('/contacto/{codigo}', name: 'contacto', requirements: ['codigo' => '\d+'])]
    public function ficha(ManagerRegistry $doctrine, int $codigo = 1): Response
    {
        if (!$this->getUser()) {
            return $this->redirect('/index');
        }

        $contacto = $doctrine->getRepository(Contacto::class)->find($codigo);
        return $this->render('ficha.html.twig', ['contacto' => $contacto]);
    }

    #[Route('/contacto/nuevo', name: 'nuevo')]
    public function nuevo(ManagerRegistry $doctrine, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirect('/index');
        }

        $contacto = new Contacto();
        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($contacto);
            $em->flush();
            return $this->redirectToRoute('contacto', ['codigo' => $contacto->getId()]);
        }

        return $this->render('nuevo.html.twig', ['formulario' => $formulario->createView()]);
    }

    #[Route('/contacto/editar/{codigo}', name: 'editar', requirements: ['codigo' => '\d+'])]
    public function editar(ManagerRegistry $doctrine, Request $request, int $codigo): Response
    {
        if (!$this->getUser()) {
            return $this->redirect('/index');
        }

        $contacto = $doctrine->getRepository(Contacto::class)->find($codigo);
        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $em = $doctrine->getManager();

            // Comprobar qué botón se pulsó
            if ($formulario->get('save')->isClicked()) {
                $em->flush();
                return $this->redirectToRoute('contacto', ['codigo' => $contacto->getId()]);
            }

            if ($formulario->get('delete')->isClicked()) {
                $em->remove($contacto);
                $em->flush();
                return $this->redirectToRoute('inicio');
            }
        }

        return $this->render('editar.html.twig', [
            'formulario' => $formulario->createView(),
            'contacto' => $contacto
        ]);
    }

    #[Route('/contacto/borrar/{codigo}', name: 'borrar')]
    public function borrar(ManagerRegistry $doctrine, int $codigo): Response
    {
        if (!$this->getUser()) {
            return $this->redirect('/index');
        }

        $contacto = $doctrine->getRepository(Contacto::class)->find($codigo);
        if ($contacto) {
            $em = $doctrine->getManager();
            $em->remove($contacto);
            $em->flush();
        }

        return $this->redirectToRoute('inicio');
    }
}