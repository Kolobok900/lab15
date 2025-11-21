<?php

namespace App\Controller;

use App\Repository\InventoryRepository;
use App\Repository\PlayerRepository;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(InventoryRepository $inventoryRepository, PlayerRepository $playerRepository, ItemRepository $itemRepository): Response
    {
        $players = $playerRepository->findAll();
        $items = $itemRepository->findAll();
        $inventories = $inventoryRepository->createQueryBuilder('inv')
            ->select('inv.id', 'p.name as player', 'i.name as item')
            ->join('inv.player', 'p')
            ->join('inv.item', 'i')
            ->getQuery()
            ->getResult();
        return $this->render('game/index.html.twig', [
            'players' => $players,
            'items' => $items,
            'inventories' => $inventories,
        ]);
    }
}
