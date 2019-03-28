<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends FOSRestController
{

    /**
     * @FOSRest\Get("/api/categories")
     *
     * @param ObjectManager $manager
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getCategoriesAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($categories, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/api/categories/{id}")
     *
     * @param ObjectManager $manager
     * @param SerializerInterface $serializer
     * @param $id
     *
     * @return Response
     */
    public function getCategoryAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $category = $categoryRepository->find($id);

        if (!$category instanceof  Category) {
            $this->json([
                'success' => false,
                'error' => 'Category not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($category, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/api/categories")
     *
     * @ParamConverter("category", converter="fos_rest.request_body")
     *
     * @param Category $category
     * @param ObjectManager $manager
     * @param ValidatorInterface $validator
     *
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCategoryAction(Category $category, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $errors = $validator->validate($category);

        if (!count($errors)) {

            $manager->persist($category);
            $manager->flush();

            // Serialize the object in Json
            $jsonObject = $serializer->serialize($category, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json($jsonObject, Response::HTTP_CREATED);
        } else {
            return $this->json([
                'success' => false,
                'error' => $errors[0]->getMessage() . ' (' . $errors[0]->getPropertyPath(). ')'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @FOSRest\Delete("/api/categories/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteCategoryAction(ObjectManager $manager, $id)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $category = $categoryRepository->find($id);

        if ($category instanceof Category) {
            $manager->remove($category);
            $manager->flush();

            return $this->json([
                'success' => true
            ], Response::HTTP_OK);
        } else {
            return $this->json([
                'success' => false,
                'error' => 'Category not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @FOSRest\Put("/api/categories/{id}")
     *
     * @ParamConverter("category", converter="fos_rest.request_body")
     *
     * @param ObjectManager $manager
     * @param $id
     * @param Request $request
     * @param Category $category
     * @param ValidatorInterface $validator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putCategoryAction(Request $request, Category $category, ObjectManager $manager, $id, ValidatorInterface $validator)
    {
        $categoryRepository = $manager->getRepository(Category::class);
        $savedCategory = $categoryRepository->find($id);

        if (!$savedCategory instanceof Category) {
            return $this->json([
                'success' => false,
                'error' => 'Category not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $categoryForm = $this->createForm(CategoryType::class, $savedCategory);
        $categoryForm->submit($request->request->all());

        $errors = $validator->validate($category);

        if (!count($errors) ) {
            // The category is updated
            $manager->persist($savedCategory);
            $manager->flush();
            return $this->json($savedCategory, Response::HTTP_CREATED);
        } else {
            return $this->json([
                'success' => false,
                'error' => $errors[0]->getMessage() . ' (' . $errors[0]->getPropertyPath(). ')'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
