<?php

namespace App\Controller;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ItemController extends AbstractController
{
    #[Route('/item', name: 'app_item')]
    public function index(ItemRepository $itemRepository): Response
    {
        $items = $itemRepository->findAll();
        return $this->render('item/index.html.twig', [
            'items' => $items,
        ]);
    }
    #[Route('/item/itemcreateform', name: 'iformcreate', methods: ['GET'])]
    public function formCreate(Request $request)
    {
        return $this->render('item/create.html.twig');
    }
    #[Route('/item/create', name: 'icreate', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $item = new Item();
        $item->setName($request->request->get('name'));
        $item->setRarity($request->request->get('rarity'));
        $item->setPrice($request->request->get('price'));
        $errors = $validator->validate($item);
        if (count($errors) > 0) {
            return $this->render('item/errors.html.twig', [
                'errors' => $errors,
            ]);
        }
        $entityManager->persist($item);
        $entityManager->flush();
        return new Response('Успешно добавлен предмет с названием: ' . $item->getName());
    }
    #[Route('/item/updateform/{item}', name: 'iupdateform', methods: 'GET')]
    public function formUpdate(Item $item)
    {
        return $this->render('item/update.html.twig', [
            'item' => $item,
        ]);
    }
    #[Route('/item/update/{item}', name: 'item_update', methods: 'POST')]
    public function update(Request $request, Item $item, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $item->setName($request->request->get('name'));
        $item->setRarity($request->request->get('rarity'));
        $item->setPrice($request->request->get('price'));
        $errors = $validator->validate($item);
        if (count($errors) > 0) {
            return $this->render('item/errors.html.twig', [
                'errors' => $errors,
            ]);
        }
        $entityManager->flush();
        return new Response("Успешно обновлен предмет с названием: " . $item->getName());
    }
    #[Route('/item/delete/{item}', name: 'item_delete', methods: 'DELETE')]
    public function delete(Item $item, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($item);
        $entityManager->flush();
        return new Response("Успешно удален предмет с названием: " . $item->getName());
    }
}
