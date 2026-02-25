<?php

namespace App\Controller\Api;

use App\Entity\Activity;
use App\Entity\Badge;
use App\Entity\ResidEvent;
use App\Entity\User;
use App\Entity\UserActivity;
use App\Repository\UserActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
class ActivitiesController extends AbstractController
{
    /**
     * GET /api/activities
     * Retourne les activités avec l'état de l'utilisateur connecté.
     * Un nouvel utilisateur verra tout à progress=0, started=false.
     */
    #[Route('/activities', name: 'api_activities_list', methods: ['GET'])]
    public function list(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $activities = $em->getRepository(Activity::class)->findAll();

        $result = [];
        foreach ($activities as $act) {
            /** @var UserActivity|null $ua */
            $ua = $em->getRepository(UserActivity::class)
                     ->findOneBy(['user' => $user, 'activity' => $act]);

            $result[] = array_merge($act->toArray(), [
                'started'  => $ua !== null && $ua->getStatus() !== 'not_started',
                'status'   => $ua?->getStatus() ?? 'not_started',
                'progress' => $ua?->getProgress() ?? 0,
            ]);
        }

        return $this->json(['activities' => $result]);
    }

    /**
     * GET /api/events
     * Retourne les événements à venir.
     */
    #[Route('/events', name: 'api_events_list', methods: ['GET'])]
    public function events(EntityManagerInterface $em): JsonResponse
    {
        $events = $em->getRepository(ResidEvent::class)
                     ->findBy([], ['date' => 'ASC']);

        return $this->json([
            'events' => array_map(fn(ResidEvent $e) => $e->toArray(), $events)
        ]);
    }

    /**
     * POST /api/activities/{id}/start
     * Lance une activité pour l'utilisateur connecté.
     */
    #[Route('/activities/{id}/start', name: 'api_activity_start', methods: ['POST'])]
    public function start(
        int $id,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $activity = $em->getRepository(Activity::class)->find($id);

        if (!$activity) {
            return $this->json(['error' => 'Activité introuvable'], Response::HTTP_NOT_FOUND);
        }

        $ua = $em->getRepository(UserActivity::class)
                 ->findOneBy(['user' => $user, 'activity' => $activity]);

        if (!$ua) {
            $ua = new UserActivity();
            $ua->setUser($user)->setActivity($activity);
            $em->persist($ua);
        }

        if ($ua->getStatus() === 'not_started') {
            $ua->setStatus('ongoing')
               ->setStartedAt(new \DateTimeImmutable());
        }

        $em->flush();

        return $this->json(['userActivity' => $ua->toArray()]);
    }

    /**
     * PATCH /api/activities/{id}/progress
     * Met à jour la progression. Si progress=100, attribue l'XP.
     */
    #[Route('/activities/{id}/progress', name: 'api_activity_progress', methods: ['PATCH'])]
    public function updateProgress(
        int $id,
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $data     = json_decode($request->getContent(), true) ?? [];
        $progress = max(0, min(100, (int)($data['progress'] ?? 0)));

        $activity = $em->getRepository(Activity::class)->find($id);
        if (!$activity) {
            return $this->json(['error' => 'Activité introuvable'], Response::HTTP_NOT_FOUND);
        }

        $ua = $em->getRepository(UserActivity::class)
                 ->findOneBy(['user' => $user, 'activity' => $activity]);

        if (!$ua) {
            return $this->json(['error' => "L'activité n'a pas été démarrée"], Response::HTTP_BAD_REQUEST);
        }

        $ua->setProgress($progress);

        $xpEarned  = 0;
        $newBadges = [];

        // Complétion → attribution XP + vérification badges
        if ($progress >= 100 && $ua->getStatus() !== 'completed') {
            $ua->setStatus('completed')
               ->setCompletedAt(new \DateTimeImmutable());

            $xpEarned = $activity->getXpReward();
            $user->addXp($xpEarned);

            $newBadges = $this->checkAndAwardBadges($user, $em);
        }

        $em->flush();

        return $this->json([
            'userActivity' => $ua->toArray(),
            'xpEarned'     => $xpEarned,
            'newBadges'    => $newBadges,
            'user'         => $user->toArray(),
        ]);
    }

    /**
     * Vérifie et attribue automatiquement les badges débloqués.
     */
    private function checkAndAwardBadges(User $user, EntityManagerInterface $em): array
    {
        $newBadges  = [];
        $allBadges  = $em->getRepository(Badge::class)->findAll();

        // Compte les activités complétées
        $completedCount = $em->getRepository(UserActivity::class)
                             ->count(['user' => $user, 'status' => 'completed']);

        foreach ($allBadges as $badge) {
            // Déjà obtenu ?
            if (in_array($badge->getEmoji(), $user->getBadges(), true)) {
                continue;
            }

            // Condition de déverrouillage
            $unlock = false;

            if ($badge->getActivitiesRequired() > 0) {
                $unlock = $completedCount >= $badge->getActivitiesRequired();
            } elseif ($badge->getXpRequired() > 0) {
                $unlock = $user->getXp() >= $badge->getXpRequired();
            }

            if ($unlock) {
                $user->addBadge($badge->getEmoji());
                $newBadges[] = [
                    'id'    => $badge->getId(),
                    'emoji' => $badge->getEmoji(),
                    'name'  => $badge->getName(),
                ];
            }
        }

        return $newBadges;
    }
}
