<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlayerController extends AbstractController
{
    #[Route('/player', name: 'app_player')]
    public function index(PlayerRepository $playerRepository): Response
    {
        $players = $playerRepository->findAll();
        return $this->render('player/index.html.twig', [
            'players' => $players,
        ]);
    }
    #[Route('/player/createform', name: 'pformcreate', methods: ['GET'])]
    public function formCreate(Request $request)
    {
        return $this->render('player/create.html.twig');
    }
    #[Route('/player/create', name: 'pcreate', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $player = new Player();
        $player->setName($request->request->get('name'));
        $player->setLevel($request->request->get('level'));
        $errors = $validator->validate($player);
        if (count($errors) > 0) {
            return $this->render('player/errors.html.twig', [
                'errors' => $errors,
            ]);
        }
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->redirectToRoute('app_player');
    }
    #[Route('/player/updateform/{player}', name: 'pupdateform', methods: 'GET')]
    public function formUpdate(Player $player)
    {
        return $this->render('player/update.html.twig', [
            'player' => $player,
        ]);
    }
    #[Route('/player/update/{player}', name: 'player_update', methods: 'POST')]
    public function update(Request $request, Player $player, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $player->setName($request->request->get('name'));
        $player->setLevel($request->request->get('level'));
        $errors = $validator->validate($player);
        if (count($errors) > 0) {
            return $this->render('player/errors.html.twig', [
                'errors' => $errors,
            ]);
        }
        $entityManager->flush();
        return $this->redirectToRoute('app_player');
    }
    #[Route('/player/delete/{player}', name: 'player_delete', methods: 'DELETE')]
    public function delete(Player $player, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($player);
        $entityManager->flush();
        return $this->redirectToRoute('app_player');
    }
}
