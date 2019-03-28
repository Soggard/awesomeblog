<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends FOSRestController
{

    /**
     * @FOSRest\Get("/api/authors")
     *
     * @param ObjectManager $manager
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getAuthorsAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $authors = $authorRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($authors, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/api/authors/{id}")
     *
     * @param ObjectManager $manager
     * @param SerializerInterface $serializer
     * @param $id
     *
     * @return Response
     */
    public function getAuthorAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $author = $authorRepository->find($id);

        if (!$author instanceof  Author) {
            $this->json([
                'success' => false,
                'error' => 'Author not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($author, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/api/authors")
     *
     * @ParamConverter("author", converter="fos_rest.request_body")
     *
     * @param Author $author
     * @param ObjectManager $manager
     * @param ValidatorInterface $validator
     *
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAuthorAction(Author $author, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $errors = $validator->validate($author);

        if (!count($errors)) {

            $manager->persist($author);
            $manager->flush();

            // Serialize the object in Json
            $jsonObject = $serializer->serialize($author, 'json', [
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
     * @FOSRest\Delete("/api/authors/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAuthorAction(ObjectManager $manager, $id)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $author = $authorRepository->find($id);

        if ($author instanceof Author) {
            $manager->remove($author);
            $manager->flush();

            return $this->json([
                'success' => true
            ], Response::HTTP_OK);
        } else {
            return $this->json([
                'success' => false,
                'error' => 'Author not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @FOSRest\Put("/api/authors/{id}")
     *
     * @ParamConverter("author", converter="fos_rest.request_body")
     *
     * @param ObjectManager $manager
     * @param $id
     * @param Request $request
     * @param Author $author
     * @param ValidatorInterface $validator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putAuthorAction(Request $request, Author $author, ObjectManager $manager, $id, ValidatorInterface $validator)
    {
        $authorRepository = $manager->getRepository(Author::class);
        $savedAuthor = $authorRepository->find($id);

        if (!$savedAuthor instanceof Author) {
            return $this->json([
                'success' => false,
                'error' => 'Author not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $authorForm = $this->createForm(AuthorType::class, $savedAuthor);
        $authorForm->submit($request->request->all());

        $errors = $validator->validate($author);

        if (!count($errors) ) {
            // The author is updated
            $manager->persist($savedAuthor);
            $manager->flush();
            return $this->json($savedAuthor, Response::HTTP_CREATED);
        } else {
            return $this->json([
                'success' => false,
                'error' => $errors[0]->getMessage() . ' (' . $errors[0]->getPropertyPath(). ')'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
