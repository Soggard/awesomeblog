<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Author;
use App\Entity\Category;
use App\Form\ArticleType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleController extends FOSRestController
{

    /**
     * @FOSRest\Get("/articles")
     *
     * @param ObjectManager $manager
     *
     * @return Response
     */
    public function getArticlesAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $articleRepository = $manager->getRepository(Articles::class);
        $articles = $articleRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($articles, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/articles/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return Response
     */
    public function getArticleAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $articleRepository = $manager->getRepository(Articles::class);
        $articles = $articleRepository->find($id);

        if (!$articles instanceof  Articles) {
            $this->json([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($articles, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/articles")
     *
     * @ParamConverter("article", converter="fos_rest.request_body")
     *
     * @param ObjectManager $manager
     * @param Articles $article
     * @param ValidatorInterface $validator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postArticleAction(Articles $article, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $errors = $validator->validate($article);

        if (!count($errors)) {
            $authorRepository = $manager->getRepository(Author::class);
            $author = $authorRepository->find(3);

            $categoryRepository = $manager->getRepository(Category::class);
            $category = $categoryRepository->find(3);

            if (!empty($author) && !empty($category)) {
                $article->setAuthor($author);
                $article->setCategory($category);
                $manager->persist($article);
                $manager->flush();
            } else {
                // If the author ID or the category ID is incorrect
                return $this->json([
                    'success' => false,
                    'error' => 'The author or the category provided is incorrect'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Serialize the object in Json
            $jsonObject = $serializer->serialize($article, 'json', [
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
     * @FOSRest\Delete("/articles/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteArticleAction(ObjectManager $manager, $id)
    {
        $articleRepository = $manager->getRepository(Articles::class);
        $article = $articleRepository->find($id);

        if ($article instanceof Articles) {
            $manager->remove($article);
            $manager->flush();

            return $this->json([
                'success' => true
            ], Response::HTTP_OK);
        } else {
            return $this->json([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @FOSRest\Put("/articles/{id}")
     *
     * @ParamConverter("article", converter="fos_rest.request_body")
     *
     * @param ObjectManager $manager
     * @param $id
     * @param Request $request
     * @param Articles $article
     * @param ValidatorInterface $validator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putArticleAction(Request $request, Articles $article, ObjectManager $manager, $id, ValidatorInterface $validator)
    {
        $articleRepository = $manager->getRepository(Articles::class);
        $savedArticle = $articleRepository->find($id);

        if (!$article instanceof Articles) {
            return $this->json([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $articleForm = $this->createForm(ArticleType::class, $savedArticle);
        $articleForm->submit($request->request->all());

        $errors = $validator->validate($article);

        if (!count($errors) ) {
            // The article is updated
            $manager->persist($savedArticle);
            $manager->flush();
            return $this->json($article, Response::HTTP_CREATED);
        } else {
            return $this->json([
                'success' => false,
                'error' => $errors[0]->getMessage() . ' (' . $errors[0]->getPropertyPath(). ')'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
