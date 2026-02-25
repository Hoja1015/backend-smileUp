<?php

namespace App\Controller\Api;

use App\Entity\Badge;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
class BadgesController extends AbstractController
{
    /**
     * GET /api/badges
     * Retourne tous les badges avec l'état unlocked pour l'utilisateur connecté.
     */
    #[Route('/badges', name: 'api_badges_list', methods: ['GET'])]
    public function list(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $allBadges  = $em->getRepository(Badge::class)->findAll();
        $userBadges = $user?->getBadges() ?? [];

        $result = array_map(
            fn(Badge $b) => $b->toArray(in_array($b->getEmoji(), $userBadges, true)),
            $allBadges
        );

        return $this->json(['badges' => $result]);
    }
}
