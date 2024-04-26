<?php

namespace App\Controller\Api;

use App\Entity\Artist;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class ArtistController extends AbstractController
{
    public function __construct(
        private ArtistRepository $artistRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    )
    {
        // ...
    }

    #[Route('/api/artists', name: 'app_api_artist', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Artist::class, groups: ['artist:read'])
    )]
    public function index(): JsonResponse
    {
        $artists = $this->artistRepository->findAll();

        return $this->json([
            'artists' => $artists,
        ], 200, [], ['groups' => ['artist:read']]);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist_get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Artist::class, groups: ['artist:read'])
    )]
    public function get(?Artist $artist = null): JsonResponse
    {
        if(!$artist)
        {
            return $this->json([
                'error' => 'Resource does not exist',
            ], 404);
        }

        return $this->json($artist, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artists', name: 'app_api_artist_add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $artist = $this->serializer->deserialize($data, Artist::class, 'json');

        $this->em->persist($artist);
        $this->em->flush();

        return $this->json($artist, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist_update', methods: ['PUT'])]
    public function update(Artist $artist, Request $request): JsonResponse
    {
        $data = $request->getContent();
        $this->serializer->deserialize($data, Artist::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $artist
        ]);

        $this->em->flush();

        return $this->json($artist, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist_delete', methods: ['DELETE'])]
    public function delete(Artist $artist): JsonResponse
    {
        $this->em->remove($artist);
        $this->em->flush();

        return $this->json([
            'message' => 'Artist deleted successfully'
        ], 200);
    }
}
