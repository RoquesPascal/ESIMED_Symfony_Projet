<?php

namespace App\Controller;

use App\Entity\AdminUser;
use App\Form\AdminUserType;
use App\Repository\AdminUserRepository;
use App\Security\AdminUserHash;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    #[Route('/admin/register', name: 'admin_register')]
    public function register(AdminUserRepository $adminUserRepository,
                             Request $request,
                             UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $adminUser = new AdminUser();
        $form = $this->createForm(AdminUserType::class, $adminUser);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $adminUserHash = new AdminUserHash();
            $hashedPassword = $passwordHasher->hashPassword(
                $adminUserHash,
                $adminUser->getPassword()
            );
            $adminUser->setPassword($hashedPassword);

            $adminUserRepository->save($adminUser, true);
        }

        return $this->render('admin_user/register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
