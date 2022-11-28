<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'admin_category_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $listCategories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'listCategories' => $listCategories,
        ]);
    }

    #[Route('/admin/category/add', name: 'admin_category_add')]
    public function add_category(CategoryRepository $categoryRepository, Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $categoryRepository->save($category, true);
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('category/add_edit.html.twig', [
            'ajout' => true,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/category/edit/{id}', name: 'admin_category_edit')]
    public function edit(CategoryRepository $categoryRepository, Request $request): Response
    {
        $category = $categoryRepository->find($request->attributes->get('id'));
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $categoryRepository->save($category, true);
            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('category/add_edit.html.twig', [
            'ajout' => false,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/category/delete/{id}', name: 'admin_category_delete')]
    public function delete(CategoryRepository $categoryRepository, Request $request): Response
    {
        $category = $categoryRepository->find($request->attributes->get('id'));
        if($category)
            $categoryRepository->remove($category, true);

        return $this->redirectToRoute('admin_category_index');
    }
}