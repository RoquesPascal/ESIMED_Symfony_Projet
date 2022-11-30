<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Form\AdminUserType;
use App\Repository\AdminUserRepository;
use App\Security\AdminUserHash;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminUserController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return $this->redirectToRoute('admin_index');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $adminUser = $this->getUser();

        return $this->render('admin_user/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'adminUser' => $adminUser,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/logout', name: 'admin_logout', methods: ['GET'])]
    public function logout()
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function admin_users(AdminUserRepository $adminUserRepository): Response
    {
        $listAdminUsers = $adminUserRepository->findAll();
        return $this->render('admin_user/list.html.twig', [
            'listAdminUsers' => $listAdminUsers,
        ]);
    }

    #[Route('/admin/user/add', name: 'admin_users_add')]
    public function admin_users_add(AdminUserRepository $adminUserRepository,
                                    Request $request,
                                    UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $adminUser = new AdminUser();
        $form = $this->createForm(AdminUserType::class, $adminUser);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $hashedPassword = $passwordHasher->hashPassword(
                new AdminUserHash(),
                $adminUser->getPlainPassword()
            );
            $adminUser->setPassword($hashedPassword);

            $adminUserRepository->save($adminUser, true);

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin_user/add_edit.twig', [
            'ajout' => true,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/user/edit/{id}', name: 'admin_users_edit')]
    public function admin_users_edit(AdminUserRepository $adminUserRepository,
                                     Request $request,
                                     UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $adminUser = $adminUserRepository->find($request->attributes->get('id'));
        $form = $this->createForm(AdminUserType::class, $adminUser);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $hashedPassword = $passwordHasher->hashPassword(
                new AdminUserHash(),
                $adminUser->getPlainPassword()
            );
            $adminUser->setPassword($hashedPassword);

            $adminUserRepository->save($adminUser, true);

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin_user/add_edit.twig', [
            'ajout' => false,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/users/delete/{id}', name: 'admin_users_delete')]
    public function admin_users_delete(AdminUserRepository $adminUserRepository, Request $request): Response
    {
        $adminUser = $adminUserRepository->find($request->attributes->get('id'));
        if($adminUser)
            $adminUserRepository->remove($adminUser, true);

        return $this->redirectToRoute('admin_users');
    }
}
