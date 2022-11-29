<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Form\AdvertType;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdvertController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('advert/index.html.twig', [
            'controller_name' => 'AdvertController',
        ]);
    }

    #[Route('/add', name: 'add_advert')]
    public function add(AdvertRepository $advertRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $advert->setState('draft');
            $advert->setCreatedAt(new \DateTime());

            $advertRepository->save($advert, true);
            return $this->redirectToRoute('index');
        }

        $listCategories = $categoryRepository->findAll();

        return $this->render('advert/add.html.twig', [
            'form' => $form->createView(),
            'listCategories' => $listCategories,
        ]);
    }

    #[Route('/admin', name: 'admin_index')]
    public function admin_index(AdvertRepository $advertRepository): Response
    {
        $listAdverts = $advertRepository->findAll();
        return $this->render('advert/admin_index.html.twig', [
            'controller_name' => 'AdvertController',
            'listAdverts' => $listAdverts,
        ]);
    }

    #[Route('/admin/advertvalidation', name: 'admin_advertvalidation')]
    public function admin_advertvalidation(AdvertRepository $advertRepository): Response
    {
        $listAdvertsDraft = $advertRepository->findBy(['state' => 'draft']);
        return $this->render('advert/admin_advertvalidation.html.twig', [
            'controller_name' => 'AdvertController',
            'listAdvertsDraft' => $listAdvertsDraft,
        ]);
    }
}
