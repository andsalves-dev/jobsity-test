<?php

namespace App\Controller;

use App\Entity\User;
use App\Traits\CollectionValidationTrait;

/**
 * Class AbstractController
 * @package App\Controller
 * @method User getUser
 */
abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController {
    use CollectionValidationTrait;
}