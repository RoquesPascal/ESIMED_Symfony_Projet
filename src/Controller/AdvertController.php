<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Form\AdvertType;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use App\Service\ManageWorkflow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

class AdvertController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(AdvertRepository $advertRepository): Response
    {
        $listAdvertsPublished = $advertRepository->findBy(['state' => 'published']);
        return $this->render('advert/index.html.twig', [
            'listAdvertsPublished' => $listAdvertsPublished,
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
            'listAdverts' => $listAdverts,
        ]);
    }

    #[Route('/admin/list/draft', name: 'admin_advert_list_draft')]
    public function admin_advert_list_draft(AdvertRepository $advertRepository): Response
    {
        $listAdverts = $advertRepository->findBy(['state' => 'draft']);
        return $this->render('advert/admin_advert_list.html.twig', [
            'listAdverts' => $listAdverts,
            'advertSate' => 'draft',
        ]);
    }

    #[Route('/admin/list/published', name: 'admin_advert_list_published')]
    public function admin_advert_list_published(AdvertRepository $advertRepository): Response
    {
        $listAdverts = $advertRepository->findBy(['state' => 'published']);
        return $this->render('advert/admin_advert_list.html.twig', [
            'listAdverts' => $listAdverts,
            'advertSate' => 'published',
        ]);
    }


    #[Route('/admin/list/rejected', name: 'admin_advert_list_rejected')]
    public function admin_advert_list_rejected(AdvertRepository $advertRepository): Response
    {
        $listAdverts = $advertRepository->findBy(['state' => 'rejected']);
        return $this->render('advert/admin_advert_list.html.twig', [
            'listAdverts' => $listAdverts,
            'advertSate' => 'rejected',
        ]);
    }

    #[Route('/admin/show/{id}', name: 'admin_show')]
    public function admin_show(AdvertRepository $advertRepository, Request $request): Response
    {
        $advert = $advertRepository->find($request->attributes->get('id'));
        return $this->render('advert/admin_show.html.twig', [
            'advert' => $advert,
        ]);
    }

    #[Route('/admin/advertvalidation/validate/{id}&{view}', name: 'admin_advertvalidation_validate')]
    public function admin_advertvalidation_validate(AdvertRepository $advertRepository, Registry $registry, Request $request): Response
    {
        ManageWorkflow::Publish($request->attributes->get('id'), $advertRepository, $registry);

        return match($request->attributes->get('view'))
        {
            'draft'     => $this->redirectToRoute('admin_advert_list_draft'),
            'published' => $this->redirectToRoute('admin_advert_list_published'),
            'rejected'  => $this->redirectToRoute('admin_advert_list_rejected'),
            default     => $this->redirectToRoute('admin_index'),
        };
    }

    #[Route('/admin/advertvalidation/reject/{id}&{view}', name: 'admin_advertvalidation_reject')]
    public function admin_advertvalidation_reject(AdvertRepository $advertRepository, Registry $registry, Request $request): Response
    {
        ManageWorkflow::Reject($request->attributes->get('id'), $advertRepository, $registry);

        return match($request->attributes->get('view'))
        {
            'draft'     => $this->redirectToRoute('admin_advert_list_draft'),
            'published' => $this->redirectToRoute('admin_advert_list_published'),
            'rejected'  => $this->redirectToRoute('admin_advert_list_rejected'),
            default     => $this->redirectToRoute('admin_index'),
        };
    }
}
