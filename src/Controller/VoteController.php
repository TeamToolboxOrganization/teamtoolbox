<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\MindsetDTO;
use App\Entity\Squad;
use App\Entity\User;
use App\Form\SquadType;
use App\Repository\MindsetRepository;
use App\Repository\SquadRepository;
use App\Security\CSPDefinition;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/vote")]
class VoteController extends AbstractController
{
    #[Route("/",name: "vote_index", methods: ["GET"])]
    public function index(Security $security): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        return new Response(
            $this->renderView('vote/index.html.twig', [

            ]),
            Response::HTTP_OK,
            [
                //'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );

    }

}
