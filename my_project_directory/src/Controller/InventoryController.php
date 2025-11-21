<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use App\Repository\PlayerRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'app_inventory')]
    public function index(InventoryRepository $inventoryRepository): Response
    {
        $inventories = $inventoryRepository->createQueryBuilder('inv')
            ->select('inv.id', 'p.name as player', 'i.name as item')
            ->join('inv.player', 'p')
            ->join('inv.item', 'i')
            ->getQuery()
            ->getResult();
        return $this->render('inventory/index.html.twig', [
            'inventories' => $inventories,
        ]);
    }
    #[Route('/inventory/inventorycreateform', name: 'downloadinv')]
    public function download(): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/inventories.csv';

        return $this->file($path, 'inventories.csv');
    }
    #[Route('/inventory/inventorycreateform', name: 'invformcreate', methods: ['GET'])]
    public function formCreate(Request $request, PlayerRepository $playerController, ItemRepository $itemController)
    {
        $players = $playerController->findAll();
        $items = $itemController->findAll();
        return $this->render('inventory/create.html.twig', [
            'players' => $players,
            'items' => $items,
        ]);
    }
    #[Route('/inventory/create', name: 'invcreate', methods: ['POST'])]
    public function create(Request $request, InventoryRepository $inventoryRepository, PlayerRepository $playerRepository, ItemRepository $itemRepository, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $inventory = new Inventory();
        $playerId = $request->request->get('player');
        $itemId = $request->request->get('item');
        $player = $playerRepository->find($playerId);
        $item = $itemRepository->find($itemId);
        $inventory->addPlayer($player);
        $inventory->addItem($item);
        $errors = $validator->validate($inventory);
        if (count($errors) > 0) {
            return $this->render('item/errors.html.twig', [
                'errors' => $errors,
            ]);
        }
        $entityManager->persist($inventory);
        $entityManager->flush();

        $inventories = $inventoryRepository->createQueryBuilder('inv')
            ->select('p.name as player', 'i.name as item')
            ->join('inv.player', 'p')
            ->join('inv.item', 'i')
            ->getQuery()
            ->getResult();

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->fromArray($inventories, NULL, 'A1');
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $writer->save("inventories.csv");
        return $this->redirectToRoute('app_inventory');
    }
    #[Route('/inventory/updateform/{id}', name: 'invupdateform', methods: 'GET')]
    public function formUpdate(Inventory $inventory, PlayerRepository $playerRepository, ItemRepository $itemRepository)
    {
        $players = $playerRepository->findAll();
        $items = $itemRepository->findAll();
        return $this->render('inventory/update.html.twig', [
            'inventory' => $inventory,
            'players' => $players,
            'items' => $items,
        ]);
    }
    #[Route('/inventory/update/{id}', name: 'inventory_update', methods: 'POST')]
    public function update(Request $request, InventoryRepository $inventoryRepository, Inventory $inventory, PlayerRepository $playerRepository, ItemRepository $itemRepository, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $playerId = $request->request->get('player');
        $itemId = $request->request->get('item');

        $player = $playerRepository->find($playerId);
        $item = $itemRepository->find($itemId);

        $currentPlayers = $inventory->getPlayer();
        foreach ($currentPlayers as $currentPlayer) {
            $inventory->removePlayer($currentPlayer);
        }

        $currentItems = $inventory->getItem();
        foreach ($currentItems as $currentItem) {
            $inventory->removeItem($currentItem);
        }

        $inventory->addPlayer($player);
        $inventory->addItem($item);
        $errors = $validator->validate($inventory);
        if (count($errors) > 0) {
            return $this->render('item/errors.html.twig', [
                'errors' => $errors,
            ]);
        }
        $entityManager->flush();

        $inventories = $inventoryRepository->createQueryBuilder('inv')
            ->select('p.name as player', 'i.name as item')
            ->join('inv.player', 'p')
            ->join('inv.item', 'i')
            ->getQuery()
            ->getResult();

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->fromArray($inventories, NULL, 'A1');
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $writer->save("inventories.csv");
        return $this->redirectToRoute('app_inventory');
    }
    #[Route('/inventory/delete/{id}', name: 'inventory_delete', methods: 'DELETE')]
    public function delete(Inventory $inventory, InventoryRepository $inventoryRepository, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($inventory);
        $entityManager->flush();

        $inventories = $inventoryRepository->createQueryBuilder('inv')
            ->select('p.name as player', 'i.name as item')
            ->join('inv.player', 'p')
            ->join('inv.item', 'i')
            ->getQuery()
            ->getResult();

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->fromArray($inventories, NULL, 'A1');
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $writer->save("inventories.csv");
        return $this->redirectToRoute('app_inventory');
    }
}
