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
}
