<?php

namespace App\Controller;

use App\Factory\TransactionFactory;
use App\Validator\Constraints\TransactionConstraints;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class TransactionController
 *
 * @Route("/transactions")
 */
class TransactionController extends AbstractController {
    /** @var TransactionFactory */
    private $transactionFactory;

    /**
     * @Route("/", name="transaction_create", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function new(Request $request, ValidatorInterface $validator): Response {
        $data = $request->request->getIterator()->getArrayCopy();
        $this->validateRequest($data, $validator, new TransactionConstraints());
        $transaction = $this->transactionFactory->create($data, $this->getUser());

        $this->getDoctrine()->getManager()->persist($transaction);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'id' => $transaction->getId(),
            'balance_after' => $transaction->getBalanceAfter(),
        ], 201);
    }

    public function __construct(TransactionFactory $transactionFactory) {
        $this->transactionFactory = $transactionFactory;
    }
}
