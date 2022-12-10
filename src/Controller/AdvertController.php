<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Form\AdvertType;
use App\Repository\AdminUserRepository;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use App\Service\ManageWorkflow;
use DateInterval;
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
    public function add(AdminUserRepository $adminUserRepository,
                        AdvertRepository $advertRepository,
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
            $dateNow = new \DateTime();
            $dateNow->add(new DateInterval('PT1H'));
            $advert->setCreatedAt($dateNow);

            $advertRepository->save($advert, true);

            $sendTo = [];
            $listAdmin = $adminUserRepository->findAll();
            foreach ($listAdmin as $admin)
            {
                $sendTo[] = [
                    'Email' => $admin->getEmail(),
                ];
            }

            $this->sendEmail($advert, "emailNewAdvert", $sendTo);

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

        $advert = $advertRepository->find($request->attributes->get('id'));
        $sendTo = [];
        $sendTo[] = [
            'Email' => $advert->getEmail(),
        ];

        $this->sendEmail($advert, "emailAdvertPublished", $sendTo);

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

    private function sendEmail(Advert $advert, String $emailType, array $sendTo)
    {
        switch($emailType)
        {
            case 'emailNewAdvert':
                $subject = "Nouvelle annonce !";
                $emailBody = $this->writeEmailNewAdvert($advert);
                break;
            case 'emailAdvertPublished':
                $subject = "Votre annonce a été publiée !";
                $emailBody = $this->writeEmailAdvertPublished($advert);
                break;
            default:
                return;
        }

        $mj = new \Mailjet\Client('4590410afca071cdf92a811a78bd614a', '352ab93f42f27b80f00d8cb7c4dbcc38', true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "passekale.raukes@gmail.com",
                        'Name' => "Passekale"
                    ],
                    'To' => $sendTo,
                    'Subject' => $subject,
                    'HTMLPart' => $emailBody,
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
    }

    private function writeEmailAdvertPublished(Advert $advert) : string
    {
        return '
            <div class="globalContainer">
                <h2>Votre annonce &agrave; &eacute;t&eacute; publi&eacute;e le <b>'.$advert->getPublishedAt()->format("d-m-Y").'</b> &agrave; <b>'.$advert->getPublishedAt()->format("H:i:s").'</b></h2>
            
                <div class="blockButtons">
                    <a href="http://localhost:8000/show/'.$advert->getId().'"><button>Consulter</button></a>
                </div>
            
                <div class="blockAdvert">
                    <h3>Descriptif de l\'annonce : </h3>
                    <ul>
                        <li><b>Cat&eacute;gorie :</b> '.$advert->getCategory()->getName().'</li>
                        <li><b>Titre :</b> '.$advert->getTitle().'</li>
                        <li><b>Contenu :</b> '.$advert->getContent().'</li>
                        <li><b>Prix :</b> '.$advert->getPrice().'</li>
                        <li><b>Date de publication :</b> le '.$advert->getPublishedAt()->format("d-m-Y").' &agrave; '.$advert->getPublishedAt()->format("H:i:s").'</li>
                    </ul>
                </div>
            </div>
        ';
    }

    private function writeEmailNewAdvert(Advert $advert) : string
    {
        return '
            <div class="globalContainer">
                <h2>Une nouvelle annonce &agrave; &eacute;t&eacute; cr&eacute;&eacute;e le <b>'.$advert->getCreatedAt()->format("d-m-Y").'</b> &agrave; <b>'.$advert->getCreatedAt()->format("H:i:s").'</b></h2>
            
                <div class="blockButtons">
                    <a href="http://localhost:8000/admin/show/'.$advert->getId().'"><button>Consulter</button></a>
                    <a href="http://localhost:8000/admin/advertvalidation/validate/'.$advert->getId().'&index"><button>Publier</button></a>
                    <a href="http://localhost:8000/admin/advertvalidation/reject/'.$advert->getId().'&index"><button>Rejeter</button></a>
                </div>
            
                <div class="blockAdvert">
                    <h3>Descriptif de l\'annonce : </h3>
                    <ul>
                        <li><b>id :</b> '.$advert->getId().'</li>
                        <li><b>Cat&eacute;gorie :</b></li>
                            <ul>
                                <li><b>id :</b> '.$advert->getCategory()->getId().'</li>
                                <li><b>Libell&eacute; :</b> '.$advert->getCategory()->getName().'</li>
                            </ul>
                        <li><b>Titre :</b> '.$advert->getTitle().'</li>
                        <li><b>Contenu :</b> '.$advert->getContent().'</li>
                        <li><b>Auteur :</b> '.$advert->getAuthor().'</li>
                        <li><b>Email :</b> '.$advert->getEmail().'</li>
                        <li><b>Prix :</b> '.$advert->getPrice().'</li>
                        <li><b>Date de cr&eacute;ation :</b> le '.$advert->getCreatedAt()->format("d-m-Y").' &agrave; '.$advert->getCreatedAt()->format("H:i:s").'</li>
                    </ul>
                </div>
            </div>
        ';
    }
}
