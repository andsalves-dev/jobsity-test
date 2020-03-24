<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\CurrencyConversionException;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Traits\CollectionValidationTrait;
use App\Validator\Constraints\TransactionConstraints;
use App\Validator\Constraints\UserConstraints;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController {
    use CollectionValidationTrait;

    /** @var UserFactory */
    private $userFactory;

    /**
     * @Route("", name="user_new", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @return Response
     * @throws CurrencyConversionException
     * @throws InvalidArgumentException
     */
    public function new(Request $request, ValidatorInterface $validator, UserRepository $userRepository): Response {
        $data = $request->request->getIterator()->getArrayCopy();

        $this->validateRequest($data, $validator, new UserConstraints($userRepository));
        $user = $this->userFactory->create($data);

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'user' => $user->getClientArrayCopy(),
        ], 201);
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(UserRepository $userRepository): Response {
        return $this->json([
            'users' => array_map(function (User $user) {
                return $user->getClientArrayCopy();
            }, $userRepository->findAll()),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response {
        return $this->json($user->getClientArrayCopy());
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function delete(Request $request, User $user): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    /**
     * @required
     * @param UserFactory $userFactory
     */
    public function setUserFactory(UserFactory $userFactory) {
        $this->userFactory = $userFactory;
    }
}
