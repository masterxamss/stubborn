<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\EntityManagerInterface;

class AdminProductController extends AbstractController
{
    /**
     * Display all products with their edit form
     *
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/admin', name: 'app_admin')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(ProductsType::class, new Products());

        $getAllProducts = $entityManager->getRepository(Products::class)->findAll();

        $editForms = [];

        foreach ($getAllProducts as $product) {
            $editForms[$product->getId()] = $this->createForm(ProductsType::class, $product)->createView();
        }

        return $this->render('admin/admin.html.twig', [
            'form' => $form->createView(),
            'editForms' => $editForms,
            'products' => $getAllProducts
        ]);
    }

    /**
     * Creates a new product, given a submitted form.
     *
     * @param Request $request The request that triggered this action.
     * @param EntityManagerInterface $entityManager The entity manager.
     *
     * @return Response The response to send back, which will be a redirect if the form was valid.
     */
    #[Route('/admin/create', name: 'app_admin_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $product);
            $this->updateStock($form, $product);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès');

            return $this->redirectToRoute('app_admin');
        }
        $getAllProducts = $entityManager->getRepository(Products::class)->findAll();

        $editForms = [];

        foreach ($getAllProducts as $product) {
            $editForms[$product->getId()] = $this->createForm(ProductsType::class, $product)->createView();
        }

        return $this->render('admin/admin.html.twig', [
            'form' => $form->createView(),
            'editForms' => $editForms,
            'products' => $getAllProducts
        ]);
    }

    /**
     * Edit an existing product based on the provided ID.
     * 
     * This function retrieves the product by its ID and displays an edit form.
     * If the form is submitted and valid, it updates the product details, handles
     * image upload, updates stock, and saves changes to the database.
     * Otherwise, it flashes an error message and redisplays the form.
     * 
     * @param Request $request The request object containing the form data.
     * @param EntityManagerInterface $entityManager The entity manager for database operations.
     * @param int $id The ID of the product to edit.
     * 
     * @return Response The response object containing the rendered view or a redirect.
     */
    #[Route('/admin/{id}/edit', name: 'app_admin_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Products::class)->find($id);
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_admin');
        }

        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->handleImageUpload($form, $product, $product->getImage());
            $this->updateStock($form, $product);

            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès.');

            return $this->redirectToRoute('app_admin');
        } else {
            $this->addFlash('error', 'Échec de la modification du produit.');
        }

        $emptyForm = $this->createForm(ProductsType::class, new Products());
        $getAllProducts = $entityManager->getRepository(Products::class)->findAll();
        $editForms = [];
        foreach ($getAllProducts as $existingProduct) {
            if ($existingProduct->getId() === $id) {
                $editForms[$existingProduct->getId()] = $form->createView();
            } else {
                $editForms[$existingProduct->getId()] = $this->createForm(ProductsType::class, $existingProduct)->createView();
            }
        }

        return $this->render('admin/admin.html.twig', [
            'form' => $emptyForm->createView(),
            'editForms' => $editForms,
            'products' => $getAllProducts,
            'errorFormId' => $id,
        ]);
    }

    /**
     * Deletes a product, given its id.
     * 
     * This function is called by a POST request, and redirects to the admin page.
     * If the product is not found, it will add an error flash message.
     * If the product is found, it will remove it and add a success flash message.
     * 
     * @param EntityManagerInterface $entityManager The entity manager.
     * @param int $id The id of the product to delete.
     * @return Response The response to send back, which will be a redirect.
     */
    #[Route('/admin/{id}/delete', name: 'app_admin_delete',)]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Products::class)->find($id);
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_admin');
        } else {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit supprimé avec succès');
        }
        return $this->redirectToRoute('app_admin');
    }

    /**
     * Handles the image upload of the product.
     * 
     * This function will move the uploaded image to the upload directory and update
     * the product's image field with the new filename. If the product already has
     * an image, it will also delete the old image.
     * 
     * @param FormInterface $form The form containing the image data.
     * @param Products $product The product to update.
     * @param string $oldImage The filename of the old image, if any.
     * 
     * @throws \Exception If there is an error while uploading the image.
     * 
     * @return void
     */
    private function handleImageUpload($form, Products $product, ?string $oldImage = null): void
    {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $uploadDirectory = $this->getParameter('upload_directory');
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            if ($oldImage) {
                $oldImagePath = $uploadDirectory . '/' . $oldImage;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            try {
                $imageFile->move($uploadDirectory, $newFilename);
                $product->setImage($newFilename);
            } catch (FileException $e) {
                throw new \Exception('Erreur lors du téléchargement de l\'image.');
            }
        }
    }

    /**
     * Updates the stock levels of a product based on the form data.
     *
     * This function retrieves stock data from the form and updates the 
     * corresponding product's stock levels for each size (XS, S, M, L, XL).
     * If a size is not present in the form data, it defaults to 0.
     *
     * @param FormInterface $form The form containing the stock data.
     * @param Products $product The product entity to update.
     *
     * @return void
     */
    private function updateStock($form, Products $product): void
    {
        $stockData = $form->get('stock')->getData();
        $product->setStock([
            'XS' => $stockData['XS'] ?? 0,
            'S' => $stockData['S'] ?? 0,
            'M' => $stockData['M'] ?? 0,
            'L' => $stockData['L'] ?? 0,
            'XL' => $stockData['XL'] ?? 0,
        ]);
    }
}
