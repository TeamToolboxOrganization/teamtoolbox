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

use App\Entity\User;
use App\Security\CSPDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route("/tool")]
class IframeController extends AbstractController
{

    #[Route("/{tool}",name: "tool_index")]
    public function index(Request $request, Security $security, string $tool = null): Response
    {
        if($tool == "workadventure"){

            $virtual_office_url = $this->getParameter('virtual_office_url');

            return new Response(
                $this->renderView('iframe/index.html.twig', [
                    'src' => $virtual_office_url,
                    'title' => "Virtual Office"
                ]),
                Response::HTTP_OK,
                [
                    'Content-Security-Policy' => CSPDefinition::defaultRules
                ]
            );
        }

        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser();
        return $this->redirectToRoute('collab_index', ['userId' => $currentUser->getId()]);
    }


}
