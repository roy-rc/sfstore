<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/categories')]
class AdminCategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {}

    #[Route('/', name: 'admin_categories_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);
        
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'admin_categories_new')]
    public function new(Request $request, CategoryRepository $categoryRepository, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = new Category();
        $mainCategories = $categoryRepository->findBy(['parent' => null], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $category->setName($request->get('name'));
            $category->setDescription($request->get('description'));
            $category->setIsActive((bool) $request->get('isActive'));
            
            // Handle parent category
            $parentId = $request->get('parent');
            if ($parentId) {
                $parent = $categoryRepository->find($parentId);
                $category->setParent($parent);
            }

            // Generate slug
            $slug = $this->slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            // Validate
            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                try {
                    $this->entityManager->persist($category);
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Categoría creada exitosamente');
                    return $this->redirectToRoute('admin_categories_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al crear la categoría');
                }
            }
        }

        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'main_categories' => $mainCategories,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_categories_edit')]
    public function edit(Category $category, Request $request, CategoryRepository $categoryRepository, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $mainCategories = $categoryRepository->findBy(['parent' => null], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $category->setName($request->get('name'));
            $category->setDescription($request->get('description'));
            $category->setIsActive((bool) $request->get('isActive'));
            
            // Handle parent category
            $parentId = $request->get('parent');
            if ($parentId) {
                $parent = $categoryRepository->find($parentId);
                $category->setParent($parent);
            } else {
                $category->setParent(null);
            }

            // Update slug if name changed
            $slug = $this->slugger->slug($category->getName())->lower();
            $category->setSlug($slug);
            $category->setUpdatedAt(new \DateTimeImmutable());

            // Validate
            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                try {
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Categoría actualizada exitosamente');
                    return $this->redirectToRoute('admin_categories_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al actualizar la categoría');
                }
            }
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'main_categories' => $mainCategories,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_categories_delete', methods: ['POST'])]
    public function delete(Category $category): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Categoría eliminada exitosamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar la categoría. Puede tener productos asociados.');
        }

        return $this->redirectToRoute('admin_categories_index');
    }
}