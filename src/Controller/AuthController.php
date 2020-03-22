<?php

namespace App\Controller;

use App\Entity\User;
use App\Util\PasswordEncoder;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/auth")
 */
class AuthController extends AbstractController {

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function login(Request $request, ValidatorInterface $validator): Response {
        $this->validateRequest($request, $validator);

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'username' => $request->request->get('username'),
            'password' => PasswordEncoder::encode($request->request->get('password')),
        ]);

        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'Username and/or password incorrect');
        }

        return $this->json([
            'user' => $user->getClientArrayCopy(),
            'jwt' => JWT::encode([
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ], $_ENV['JWT_SECRET_KEY']),
        ]);
    }

    private function validateRequest(Request $request, ValidatorInterface $validator) {
        $violations = $validator->validate(
            $request->request->getIterator()->getArrayCopy(),
            new Collection([ 'username' => [new NotBlank()], 'password' => [new NotBlank()]])
        );

        if ($violations->count()) {
            $message = $violations->get(0)->getPropertyPath() . ': ' . $violations->get(0)->getMessage();
            throw new UnprocessableEntityHttpException($message);
        }
    }
}
