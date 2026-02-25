<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwt,
        UserRepository $userRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['email']) || empty($data['password']) || empty($data['firstName'])) {
            return $this->json(['error' => 'Les champs email, password et firstName sont obligatoires'], Response::HTTP_BAD_REQUEST);
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Format email invalide'], Response::HTTP_BAD_REQUEST);
        }
        if (strlen($data['password']) < 6) {
            return $this->json(['error' => 'Le mot de passe doit faire au moins 6 caractères'], Response::HTTP_BAD_REQUEST);
        }
        if ($userRepo->findOneBy(['email' => $data['email']])) {
            return $this->json(['error' => 'Cet email est déjà utilisé'], Response::HTTP_CONFLICT);
        }

        $colors = ['#6c63ff', '#ff6584', '#43e97b', '#f093fb', '#4facfe', '#ffd700'];
        $user   = new User();
        $user->setEmail($data['email'])
             ->setFirstName(trim($data['firstName']))
             ->setLastName(isset($data['lastName']) ? trim($data['lastName']) : null)
             ->setResidenceCode($data['code'] ?? null)
             ->setColor($colors[array_rand($colors)])
             ->setPassword($hasher->hashPassword($user, $data['password']));

        $em->persist($user);
        $em->flush();

        return $this->json([
            'token' => $jwt->create($user),
            'user'  => $user->toArray(rank: 0),
        ], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'api_me_get', methods: ['GET'])]
    public function me(
        #[CurrentUser] ?User $user,
        UserRepository $userRepo
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }
        $rank = $userRepo->getRankForUser($user);
        return $this->json([
            'user'        => $user->toArray($rank),
            'checkinDone' => false,
            'checkinXP'   => 10,
        ]);
    }

    /**
     * PATCH /api/me
     * Permet de modifier : firstName, lastName, email, room, building, password
     */
    #[Route('/me', name: 'api_me_patch', methods: ['PATCH'])]
    public function updateMe(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepo,
        JWTTokenManagerInterface $jwt
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        // Mise à jour prénom
        if (!empty($data['firstName'])) {
            $user->setFirstName(trim($data['firstName']));
        }

        // Mise à jour nom
        if (array_key_exists('lastName', $data)) {
            $user->setLastName(trim($data['lastName']));
        }

        // Mise à jour email (vérifie unicité)
        if (!empty($data['email']) && $data['email'] !== $user->getEmail()) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->json(['error' => 'Format email invalide'], Response::HTTP_BAD_REQUEST);
            }
            $existing = $userRepo->findOneBy(['email' => $data['email']]);
            if ($existing && $existing->getId() !== $user->getId()) {
                return $this->json(['error' => 'Cet email est déjà utilisé'], Response::HTTP_CONFLICT);
            }
            $user->setEmail($data['email']);
        }

        // Mise à jour chambre / bâtiment
        if (array_key_exists('room', $data))     $user->setRoom($data['room']);
        if (array_key_exists('building', $data)) $user->setBuilding($data['building']);

        // Mise à jour mot de passe
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return $this->json(['error' => 'Le mot de passe doit faire au moins 6 caractères'], Response::HTTP_BAD_REQUEST);
            }
            $user->setPassword($hasher->hashPassword($user, $data['password']));
        }

        $em->flush();

        $rank = $userRepo->getRankForUser($user);
        return $this->json(['user' => $user->toArray($rank)]);
    }
}