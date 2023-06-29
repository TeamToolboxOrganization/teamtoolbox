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

use App\Entity\Category;
use App\Entity\CustomColor;
use App\Entity\User;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\CustomColorRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 *
 * @Route("/custom")
 *
 */
class CategoryController extends AbstractController
{
    #[Route("/",name: "user_customisation", defaults: ["page" => "1", "_format" => "html"])]
    public function index(CustomColorRepository $customColorRepository, CategoryRepository $categoryRepository): Response
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();
        $categories = $this->getCustomColorList($customColorRepository,$categoryRepository);
        $defaultColor = $categoryRepository->findAll();
        $defaultColorList = [];
        foreach($defaultColor as $color){
            $defaultColorList[$color->getId()] = $color->getDefaultColor();
        }
        return new Response(
            $this->renderView('customisation/categoriesList.html.twig', [
                'user' => $user,
                'categories' => $categories,
                'defaultColor' => $defaultColorList,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );

    }

    public function getCustomColorList(CustomColorRepository $customColorRepository, CategoryRepository $categoryRepository): array
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();
        $result = [];
        $customColorList = [];
        $customColors = $customColorRepository->findBy(['userId' => $user->getId() ], ['categoryId' => 'ASC']);
        $defaultColors = $categoryRepository->findAll();
        if($customColors == null){
            for($i = 0; $i<sizeof($defaultColors);$i++){
                $result[$defaultColors[$i]->getId()] = [$defaultColors[$i]->getName(),$defaultColors[$i]->getDefaultColor()];
            }
        }
        else{
            foreach($customColors as $cat){
                $customColorList[$cat->getCategoryId()]=$cat->getCustomColor();
            }
            for($i = 0; $i<sizeof($defaultColors);$i++){
                if(array_key_exists($defaultColors[$i]->getId(), $customColorList)){
                    $customColor = $customColorList[$defaultColors[$i]->getId()];
                }
                else{
                    $customColor = $defaultColors[$i]->getDefaultColor();
                }
                $result[$defaultColors[$i]->getId()] = [$defaultColors[$i]->getName(),$customColor];
            }
        }
        return $result;
    }

    #[Route("/new",name: "category_new", methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, ManagerRegistry $managerRegistry): Response
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category,array('mode' => 'edition'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $emCategory = $managerRegistry->getManagerForClass(Category::class);
            $emCategory->persist($category);
            $emCategory->flush();

            $this->addFlash('success', 'Création de ['.$category->getName().'] effectuée');
            return $this->redirectToRoute('user_customisation');
        }

        return new Response(
            $this->renderView('customisation/categoryEdit.html.twig', [
                'user' => $user,
                'category' => $category,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                //'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/editCategory/{categoryId}",name: "edit_category", methods: ['GET', 'POST'])]
    public function edit(ManagerRegistry $managerRegistry, Request $request, CategoryRepository $categoryRepository, int $categoryId = null): Response
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();
        $category = $categoryRepository->find($categoryId);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emCategory = $managerRegistry->getManagerForClass(Category::class);
            $emCategory->flush();

            $this->addFlash('success', 'Mise à jour de ['.$category->getName().'] effectuée');
            return $this->redirectToRoute('user_customisation');
        }

        return new Response(
            $this->renderView('customisation/categoryEdit.html.twig', [
                'user' => $user,
                'category' => $category,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                //'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/delete/{categoryId}",name: "category_delete", methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(ManagerRegistry $managerRegistry, CustomColorRepository $customColorRepository, CategoryRepository $categoryRepository, int $categoryId): Response
    {
        $category = $categoryRepository->find($categoryId);

        $emCategory = $managerRegistry->getManagerForClass(Category::class);
        $emCustomColor = $managerRegistry->getManagerForClass(CustomColor::class);

        $customsColors = $customColorRepository->findBy(['categoryId' => $categoryId]);
        foreach ($customsColors as $customColor){
            $emCustomColor->remove($customColor);
        }

        $categoryName = $category->getName();

        $emCategory->remove($category);
        $emCategory->flush();
        $emCustomColor->flush();

        $this->addFlash('success', 'La catégorie [ ' . $categoryName . '] a bien été supprimée');
        return $this->redirectToRoute('user_customisation');
    }

    #[Route("/editCustomColor",name: "edit_custom", methods: ['GET', 'POST'])]
    public function editCustomColor(Request $request, ManagerRegistry $managerRegistry, CategoryRepository $categoryRepository, CustomColorRepository $customColorRepository, ValidatorInterface $validatorInterface): Response
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();
        $postData = json_decode($request->getContent(), true);

        $customColor = $customColorRepository->findOneBy(["userId"=>$user->getId(),"categoryId"=>$postData["id"]]);
        $isExist = $customColor!=null;
        if(!$isExist){
            $customColor = new CustomColor();
        }
        $customColor->setUserId($user->getId());
        $customColor->setCategoryId($postData["id"]);
        $customColor->setCustomColor($postData["value"]);

        $emCustomColor = $managerRegistry->getManagerForClass(CustomColor::class);
        if(!$isExist){
            $emCustomColor->persist($customColor);
        }
        $emCustomColor->flush();

        $category = $categoryRepository->findOneBy(["id"=>$postData["id"]]);
        return new Response("Saved custom color for category : ".$category->getName());
    }
}
