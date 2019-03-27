<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentController extends FOSRestController
{

    /**
     * @FOSRest\Get("/comments")
     *
     * @param ObjectManager $manager
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getCommentsAction(ObjectManager $manager, SerializerInterface $serializer)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $comments = $commentRepository->findAll();

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($comments, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Get("/comments/{id}")
     *
     * @param ObjectManager $manager
     * @param SerializerInterface $serializer
     * @param $id
     *
     * @return Response
     */
    public function getCommentAction(ObjectManager $manager, SerializerInterface $serializer, $id)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $comment = $commentRepository->find($id);

        if (!$comment instanceof  Comment) {
            $this->json([
                'success' => false,
                'error' => 'Comment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Serialize the object in Json
        $jsonObject = $serializer->serialize($comment, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonObject, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @FOSRest\Post("/comments")
     *
     * @ParamConverter("comment", converter="fos_rest.request_body")
     *
     * @param Comment $comment
     * @param ObjectManager $manager
     * @param ValidatorInterface $validator
     *
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postCommentAction(Comment $comment, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer, Request $request)
    {
        $newComment = new Comment();

        if (!$newComment instanceof Comment) {
            return $this->json([
                'success' => false,
                'error' => 'Comment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $commentForm = $this->createForm(CommentType::class, $newComment);
        $commentForm->submit($request->request->all());

        $errors = $validator->validate($comment);

        if (!count($errors) ) {
            // The comment is updated
            $manager->persist($newComment);
            $manager->flush();

            // Serialize the object in Json
            $jsonObject = $serializer->serialize($newComment, 'json', [
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
     * @FOSRest\Delete("/comments/{id}")
     *
     * @param ObjectManager $manager
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteCommentAction(ObjectManager $manager, $id)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $comment = $commentRepository->find($id);

        if ($comment instanceof Comment) {
            $manager->remove($comment);
            $manager->flush();

            return $this->json([
                'success' => true
            ], Response::HTTP_OK);
        } else {
            return $this->json([
                'success' => false,
                'error' => 'Comment not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @FOSRest\Put("/comments/{id}")
     *
     * @ParamConverter("comment", converter="fos_rest.request_body")
     *
     * @param ObjectManager $manager
     * @param $id
     * @param Request $request
     * @param Comment $comment
     * @param ValidatorInterface $validator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putCommentAction(Request $request, Comment $comment, ObjectManager $manager, $id, ValidatorInterface $validator)
    {
        $commentRepository = $manager->getRepository(Comment::class);
        $savedComment = $commentRepository->find($id);

        if (!$savedComment instanceof Comment) {
            return $this->json([
                'success' => false,
                'error' => 'Comment not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $commentForm = $this->createForm(CommentType::class, $savedComment);
        $commentForm->submit($request->request->all());

        $errors = $validator->validate($comment);

        if (!count($errors) ) {
            // The comment is updated
            $manager->persist($savedComment);
            $manager->flush();
            return $this->json($comment, Response::HTTP_CREATED);
        } else {
            return $this->json([
                'success' => false,
                'error' => $errors[0]->getMessage() . ' (' . $errors[0]->getPropertyPath(). ')'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
