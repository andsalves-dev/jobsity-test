<?php

namespace App\Controller;

use App\Entity\Message;
use App\Factory\MessageFactory;
use App\Repository\MessageRepository;
use App\Service\MessageInterpreter;
use App\Validator\Constraints\MessageConstraints;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/messages")
 */
class MessageController extends AbstractController {
    /** @var MessageFactory */
    private $messageFactory;
    /** @var MessageInterpreter */
    private $messageInterpreter;

    /**
     * @Route(name="messages_index", methods={"GET"})
     * @param MessageRepository $messageRepository
     * @param Request $request
     * @return Response
     */
    public function index(MessageRepository $messageRepository, Request $request): Response {
        $qb = $messageRepository->createQueryBuilder('m')
            ->orderBy('m.date', 'ASC');

        if ($fromDate = $request->query->get('from_date')) {
            $expr = $qb->expr();
            $qb->where($expr->gte('m.date', $expr->literal($fromDate)));
        }

        $results = $qb->setMaxResults(20)
            ->getQuery()->getResult();

        return $this->json([
            'messages' => array_map(function (Message $message) {
                return $message->getClientArrayCopy();
            }, $results)
        ]);
    }

    /**
     * @Route(name="message_new", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, ValidatorInterface $validator): Response {
        $data = $request->request->getIterator()->getArrayCopy();
        $this->validateRequest($data, $validator, new MessageConstraints());

        /** @var Connection $connection */
        $connection = $this->getDoctrine()->getConnection();
        $connection->beginTransaction();

        try {
            $message = $this->messageFactory->create($data, $this->getUser());

            $this->getDoctrine()->getManager()->persist($message);
            $this->getDoctrine()->getManager()->flush();

            $actionRunner = $this->messageInterpreter->findActionRunner($message->getText());

            $responseMessage = null;
            if ($actionRunner) {
                $responseMessage = $actionRunner->runAction($message);
            } else {
                $responseMessage = $this->messageFactory->create([
                    'text' => 'Sorry, I could not understand your request. Could you please try other keywords?',
                    'is_bot' => true,
                ], $message->getUser());
                $this->getDoctrine()->getManager()->persist($responseMessage);
                $this->getDoctrine()->getManager()->flush();
            }

            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();
            throw $exception;
        }

        return $this->json([
            'message' => $message->getClientArrayCopy(),
            'response_message' => $responseMessage->getClientArrayCopy(),
        ], 201);
    }

    public function __construct(MessageFactory $messageFactory, MessageInterpreter $messageInterpreter) {
        $this->messageFactory = $messageFactory;
        $this->messageInterpreter = $messageInterpreter;
    }
}
