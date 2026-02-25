<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
class LeaderboardController extends AbstractController
{
    /**
     * GET /api/leaderboard
     * Retourne le classement trié par XP décroissant.
     */
    #[Route('/leaderboard', name: 'api_leaderboard', methods: ['GET'])]
    public function index(
        #[CurrentUser] ?User $currentUser,
        UserRepository $userRepo
    ): JsonResponse {
        $users  = $userRepo->findBy([], ['xp' => 'DESC']);
        $myRank = 0;

        $entries = [];
        foreach ($users as $index => $u) {
            $rank  = $index + 1;
            $isMe  = $currentUser && $u->getId() === $currentUser->getId();
            if ($isMe) {
                $myRank = $rank;
            }

            $entries[] = [
                'rank'       => $rank,
                'id'         => $u->getId(),
                'name'       => $u->getFirstName(),
                'fullName'   => $u->getName(),
                'initials'   => $u->getInitials(),
                'color'      => $u->getColor(),
                'pts'        => $u->getXp(),
                'level'      => $u->getLevel(),
                'badgeCount' => count($u->getBadges()),
                'isMe'       => $isMe,
            ];
        }

        return $this->json([
            'leaderboard' => $entries,
            'myRank'      => $myRank,
            'total'       => count($users),
        ]);
    }
}
