<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use App\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Route('/api')]
final class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {
        $data = $request->toArray();
        $email = $data['email'] ?? null;
        $plainPassword = $data['password'] ?? null;

        if (!$email || !$plainPassword) {
            return $this->json(
                ['message' => 'email et password sont requis'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($userRepository->findOneBy(['email' => mb_strtolower($email)])) {
            return $this->json(
                ['message' => 'Cet email est déjà utilisé'],
                Response::HTTP_CONFLICT
            );
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        $eventDispatcher->dispatch(new UserCreatedEvent($user), UserCreatedEvent::NAME);

        return $this->json(
            ['message' => 'Utilisateur créé'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/me', name: 'app_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['message' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/login_cookie', name: 'app_auth_login_cookie', methods: ['POST'])]
    public function loginCookie(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = $request->toArray();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['message' => 'email et password sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => mb_strtolower($email)]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Identifiants invalides'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);

        $response = $this->json(['message' => 'Connexion réussie']);
        $response->headers->setCookie(
        Cookie::create(
            'BEARER',
            $token,
            time() + 3600,
            '/',
            null,     // ✅ pas de domaine
            true,     // ✅ secure obligatoire si https
            true,     // ✅ httponly
            false,
            Cookie::SAMESITE_NONE  // ✅ obligatoire en cross-origin
        )
    );
        return $response;
    }

    #[Route('/logout', name: 'app_auth_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $response = $this->json(['message' => 'Déconnexion réussie']);
        $response->headers->setCookie(
            new Cookie(
                'BEARER',
                '', // valeur vide
                1,  // expiration passée (timestamp 1)
                '/',
                null, // pas de domaine pour compatibilité
                false, // secure
                true,  // httponly
                false  // samesite
            )
        );
        return $response;
    }
}