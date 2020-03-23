<?php

namespace App\Controller;

use App\Entity\User;
use App\Traits\CollectionValidationTrait;
use App\Util\PasswordEncoder;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/auth")
 */
class AuthController extends AbstractController {
    use CollectionValidationTrait;

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function login(Request $request, ValidatorInterface $validator): Response {
        $data = $request->request->getIterator()->getArrayCopy();
        $this->validateRequest($data, $validator, $this->getConstraints());

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'username' => $data['username'],
            'password' => PasswordEncoder::encode($data['password']),
        ]);

        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'Username and/or password incorrect');
        }

        return $this->json([
            'user' => $user->getClientArrayCopy(),
            'jwt' => $this->createToken($user),
        ]);
    }

    private function getConstraints(): Collection {
        return new Collection([
            'username' => new NotBlank(),
            'password' => new NotBlank(),
        ]);
    }

    private function createToken(User $user): string {
        return JWT::encode([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
        ], $_ENV['JWT_SECRET_KEY']);
    }
}
