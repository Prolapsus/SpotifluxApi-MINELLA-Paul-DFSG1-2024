<?php

namespace App\Controller\Api;

use App\Entity\Album;
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


#[OA\Tag(name: "Album")]
class AlbumController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository, 
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
        )
    {

    }

    #[Route('/api/albums', name: 'app_api_album', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Album::class, groups: ['album:read'])
    )]
    public function index(): JsonResponse
    {
        $albums = $this->albumRepository->findAll();

        return $this->json([
            'albums' => $albums,
        ], 200, [], ['groups' => ['album:read']]);
    }

    #[Route('/api/album/{id}', name: 'app_api_album_get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Album::class, groups: ['album:read'])
    )]
    public function get(?Album $album): JsonResponse
    {
        if(!$album)
        {
            return $this->json([
                'error' => 'Album does not exist'
            ], 404);
        }

        return $this->json([
            'movie' => $album,
        ], 200, [], ['groups' => ['album:read']]);
    }

    #[Route('/api/albums', name: 'app_api_album_add', methods: ['POST'])]
    public function add(
        #[MapRequestPayload('json', ['groups' => ['album:create']])] Album $album
    ): JsonResponse
    {
        $this->em->persist($album);
        $this->em->flush();
        
        return $this->json($album, 200, [], [
            'groups' => ['album:read']
        ]);
    }


    #[Route('/api/album/{id}', name: 'app_api_album_update',  methods: ['PUT'])] 
    public function update(Album $album, Request $request): JsonResponse
    {
        
        $data = $request->getContent();
        $this->serializer->deserialize($data, Album::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $album,
            'groups' => ['album:update']
        ]);

        $this->em->flush();

        return $this->json($album, 200, [], ['groups' => ['album:read']]);
    }

    #[Route('/api/album/{id}', name: 'app_api_album_delete',  methods: ['DELETE'])] 
    public function delete(Album $album): JsonResponse
    {
        $this->em->remove($album);
        $this->em->flush();

        return $this->json([
            'message' => 'Album deleted successfully'
        ], 200);
        
    }
}
