<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Form\AdvertType;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use App\Service\ManageWorkflow;
use \Mailjet\Resources;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

class AdvertController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(AdvertRepository $advertRepository,
                          CategoryRepository $categoryRepository,
                          Request $request): Response
    {
        $idCategory = $request->request->get('category');
        if($idCategory)
            return $this->redirectToRoute('listbycategories', ['id' => $idCategory]);

        $queryBuilder = $advertRepository
            ->createQueryBuilder('a')
            ->where('a.state = :state')
            ->setParameter('state', 'published')
            ->addOrderBy('a.publishedAt', 'DESC');
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(30);
        $pager->setCurrentPage($request->get('page', 1));

        $listCategoriesWithPublishedAdverts = $categoryRepository->getCategoriesWithPublishedAdverts();
        return $this->render('advert/index.html.twig', [
            'pager' => $pager,
            'listCategoriesWithPublishedAdverts' => $listCategoriesWithPublishedAdverts,
        ]);
    }

    #[Route('/listbycategories/{id}', name: 'listbycategories')]
    public function listbycategories(AdvertRepository $advertRepository, Request $request): Response
    {
        $queryBuilder = $advertRepository
            ->createQueryBuilder('a')
            ->where('a.state = :state')
            ->andWhere('a.category = :idCategory')
            ->setParameter('state', 'published')
            ->setParameter('idCategory', $request->attributes->get('id'))
            ->addOrderBy('a.publishedAt', 'DESC');
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(30);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render('advert/list_by_category.html.twig', [
            'pager' => $pager,
        ]);
    }

    #[Route('/add', name: 'add_advert')]
    public function add(AdvertRepository $advertRepository,
                        CategoryRepository $categoryRepository,
                        Request $request
    ): Response
    {
        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $advert->setState('draft');
            $advert->setCreatedAt(new \DateTime());

            $advertRepository->save($advert, true);




            $adresseEMail = "passekale.raukes@gmail.com";
            $nomEMail = "Passekale";
            $mj = new \Mailjet\Client('4590410afca071cdf92a811a78bd614a', '352ab93f42f27b80f00d8cb7c4dbcc38', true, ['version' => 'v3.1']);
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => $adresseEMail,
                            'Name' => $nomEMail
                        ],
                        'To' => [
                            [
                                'Email' => $adresseEMail,
                                'Name' => $nomEMail
                            ]
                        ],
                        'Subject' => "Greetings from Mailjet.",
                        'TextPart' => "My first Mailjet email",
                        'HTMLPart' => "<h3>Dear passenger 1, welcome to <a href='https://www.mailjet.com/'>Mailjet</a>!</h3><br />May the delivery force be with you!",
                        'CustomID' => "AppGettingStartedTest"
                    ]
                ]
            ];
            $response = $mj->post(Resources::$Email, ['body' => $body]);


            return $this->redirectToRoute('index');
        }

        $listCategories = $categoryRepository->findAll();

        return $this->render('advert/add.html.twig', [
            'form' => $form->createView(),
            'listCategories' => $listCategories,
        ]);
    }

    #[Route('/show/{id}', name: 'show')]
    public function show(AdvertRepository $advertRepository, Request $request): Response
    {
        $advert = $advertRepository->find($request->attributes->get('id'));
        return $this->render('advert/show.html.twig', [
            'advert' => $advert,
        ]);
    }

    #[Route('/admin', name: 'admin_index')]
    public function admin_index(AdvertRepository $advertRepository,
                                CategoryRepository $categoryRepository,
                                Request $request
    ): Response
    {
        $idCategory = $request->request->get('category');
        if($idCategory)
            return $this->redirectToRoute('admin_listbycategories', ['id' => $idCategory]);

        $queryBuilder = $advertRepository
            ->createQueryBuilder('a')
            ->addOrderBy('a.createdAt', 'DESC');
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(30);
        $pager->setCurrentPage($request->get('page', 1));

        $listCategoriesWithAdverts = $categoryRepository->getCategoriesWithAdverts();
        return $this->render('advert/admin_index.html.twig', [
            'pager' => $pager,
            'listCategoriesWithAdverts' => $listCategoriesWithAdverts,
        ]);
    }

    #[Route('/admin/listbycategories/{id}', name: 'admin_listbycategories')]
    public function admin_listbycategories(AdvertRepository $advertRepository, Request $request): Response
    {
        $queryBuilder = $advertRepository
            ->createQueryBuilder('a')
            ->where('a.category = :idCategory')
            ->setParameter('idCategory', $request->attributes->get('id'))
            ->addOrderBy('a.createdAt', 'DESC');
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(30);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render('advert/admin_list_by_category.html.twig', [
            'pager' => $pager,
        ]);
    }

    #[Route('/admin/list/draft', name: 'admin_advert_list_draft')]
    public function admin_advert_list_draft(AdvertRepository $advertRepository, Request $request): Response
    {
        $queryBuilder = $advertRepository
            ->createQueryBuilder('a')
            ->where('a.state = :state')
            ->setParameter('state', 'draft')
            ->addOrderBy('a.createdAt', 'DESC');
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(30);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render('advert/admin_advert_list.html.twig', [
            'pager' => $pager,
            'advertSate' => 'draft',
        ]);
    }

    #[Route('/admin/list/published', name: 'admin_advert_list_published')]
    public function admin_advert_list_published(AdvertRepository $advertRepository, Request $request): Response
    {
        $queryBuilder = $advertRepository
            ->createQueryBuilder('a')
            ->where('a.state = :state')
            ->setParameter('state', 'published')
            ->addOrderBy('a.createdAt', 'DESC');
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(30);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render('advert/admin_advert_list.html.twig', [
            'pager' => $pager,
            'advertSate' => 'published',
        ]);
    }

    #[Route('/admin/list/rejected', name: 'admin_advert_list_rejected')]
    public function admin_advert_list_rejected(AdvertRepository $advertRepository, Request $request): Response
    {
        $queryBuilder = $advertRepository
            ->createQueryBuilder('a')
            ->where('a.state = :state')
            ->setParameter('state', 'rejected')
            ->addOrderBy('a.createdAt', 'DESC');
        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(30);
        $pager->setCurrentPage($request->get('page', 1));

        return $this->render('advert/admin_advert_list.html.twig', [
            'pager' => $pager,
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
            'index'     => $this->redirectToRoute('admin_index'),
            'draft'     => $this->redirectToRoute('admin_advert_list_draft'),
            'published' => $this->redirectToRoute('admin_advert_list_published'),
            'rejected'  => $this->redirectToRoute('admin_advert_list_rejected'),
            default     => $this->redirectToRoute('admin_listbycategories', ['id' => $request->attributes->get('view')]),
        };
    }

    #[Route('/admin/advertvalidation/reject/{id}&{view}', name: 'admin_advertvalidation_reject')]
    public function admin_advertvalidation_reject(AdvertRepository $advertRepository, Registry $registry, Request $request): Response
    {
        ManageWorkflow::Reject($request->attributes->get('id'), $advertRepository, $registry);

        return match($request->attributes->get('view'))
        {
            'index'     => $this->redirectToRoute('admin_index'),
            'draft'     => $this->redirectToRoute('admin_advert_list_draft'),
            'published' => $this->redirectToRoute('admin_advert_list_published'),
            'rejected'  => $this->redirectToRoute('admin_advert_list_rejected'),
            default     => $this->redirectToRoute('admin_listbycategories', ['id' => $request->attributes->get('view')]),
        };
    }
}
