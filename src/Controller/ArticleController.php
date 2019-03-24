<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticleType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ArticleController extends FOSRestController
{

    /**
     * @FOSRest\Get("/articles")
     *
     * @param ObjectManager $manager
     *
     * @return Response
     */
    public function getArticlesAction(ObjectManager $manager)
    {
        $articleRepository = $manager->getRepository(Articles::class);
        $articles = $articleRepository->findAll();

        return $this->json($articles, Response::HTTP_OK);
    }

    /**
     * @FOSRest\Get("/articles/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return Response
     */
    public function getArticleAction(ObjectManager $manager, $id)
    {
        $articleRepository = $manager->getRepository(Articles::class);
        $articles = $articleRepository->find($id);

        if (!$articles instanceof  Articles) {
            $this->json([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($articles, Response::HTTP_OK);
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
    public function postArticleAction(Articles $article, ObjectManager $manager, ValidatorInterface $validator)
    {
        $errors = $validator->validate($article);

        if (!count($errors)) {
            $manager->persist($article);
            $manager->flush();

            return $this->json($article, Response::HTTP_CREATED);
        } else {
            return $this->json([
                'success' => false,
                'error' => $errors[0]->getMessage() . ' (' . $errors[0]->getPropertyPath(). ')'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @FOSRest\Delete("/api/articles/{id}")
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
     * @FOSRest\Put("/api/products/{id}")
     *
     * @ParamConverter("product", converter="fos_rest.request_body")
     *
     * @param ObjectManager $manager
     * @param $id
     * @param Request $request
     * @param Articles $product
     * @param ValidatorInterface $validator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putArticleAction(Request $request, Articles $product, ObjectManager $manager, $id, ValidatorInterface $validator)
    {
        $productRepository = $manager->getRepository(Articles::class);
        $savedArticle = $productRepository->find($id);

        if ( $product instanceof Articles) {
            return $this->json([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $productForm = $this->createForm(ArticleType::class, $savedArticle);
        $productForm->submit($request->request->all());

        $errors = $validator->validate($product);

        if (!count($errors) ) {
            $manager->persist($savedArticle);
            $manager->flush();

            return $this->json($product, Response::HTTP_CREATED);
        } else {
            return $this->json([
                'success' => false,
                'error' => $errors[0]->getMessage() . ' (' . $errors[0]->getPropertyPath(). ')'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
