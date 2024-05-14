<?php

namespace App\Controller;

use OpenApi\Attributes\Tag;
use App\Manager\UserManager;
use OpenApi\Attributes\Schema;
use App\Manager\StorageManager;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Parameter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response as ContentResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class MediaController
 *
 * Main media content get controller
 *
 * @package App\Controller
 */
class MediaController extends AbstractController
{
    private UserManager $userManager;
    private StorageManager $storageManager;

    public function __construct(UserManager $userManager, StorageManager $storageManager)
    {
        $this->userManager = $userManager;
        $this->storageManager = $storageManager;
    }

    /**
     * Retrieves the content of the media associated with the provided user ID and token.
     *
     * @param Request $request The request instance.
     * @param Security $security The security instance.
     *
     * @return ContentResponse The response containing the media content.
     */
    #[Tag(name: "Resources")]
    #[Response(response: 200, description: 'The success photo content resource')]
    #[Response(response: 400, description: 'The token parameter not found in requets')]
    #[Response(response: 404, description: 'The media not found error')]
    #[Parameter(name: 'token', in: 'query', schema: new Schema(type: 'string'), description: 'Media token', required: true)]
    #[Route('/api/media/content', methods: ['GET'], name: 'api_media_content')]
    public function getContent(Request $request, Security $security): ContentResponse
    {
        // get logged user ID
        $userId = $this->userManager->getUserData($security)->getId();

        // get token from request
        $token = $request->get('token');

        // check if token set
        if (!isset($token)) {
            return $this->json([
                'status' => 'error',
                'code' => JsonResponse::HTTP_BAD_REQUEST,
                'message' => 'token parameter is required'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if media exist
        if (!$this->storageManager->isMediaExist($userId, $token)) {
            return $this->json([
                'status' => 'error',
                'code' => JsonResponse::HTTP_NOT_FOUND,
                'message' => 'media token: ' . $token . ' not found'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // get content
        $content = $this->storageManager->getMediaContent($userId, $token);

        // create a streamed response with image content
        return new StreamedResponse(function () use ($content) {
            echo $content;
        }, ContentResponse::HTTP_OK, [
            'Content-Type' => $this->storageManager->getMediaType($token),
        ]);
    }

    /**
     * Retrieves the thumbnail of a media resource based on provided parameters.
     *
     * @param Security $security The security service for handling user authentication.
     * @param Request $request The HTTP request object containing query parameters.
     *
     * @return ContentResponse The response containing the thumbnail image content.
     */
    #[Tag(name: "Resources")]
    #[Response(response: 200, description: 'The success photo thumbnail resource type jpg')]
    #[Response(response: 400, description: 'The token, width or height parameters not found in requets')]
    #[Response(response: 404, description: 'The media not found error')]
    #[Parameter(name: 'width', in: 'query', schema: new Schema(type: 'string'), description: 'Media width', required: true)]
    #[Parameter(name: 'height', in: 'query', schema: new Schema(type: 'string'), description: 'Media height', required: true)]
    #[Parameter(name: 'token', in: 'query', schema: new Schema(type: 'string'), description: 'Media token', required: true)]
    #[Route(['/api/media/thumbnail'], methods: ['GET'], name: 'api_media_thumbnail')]
    public function getThumbnail(Security $security, Request $request): ContentResponse
    {
        // get logged user ID
        $userId = $this->userManager->getUserData($security)->getId();

        // get data from token
        $token = $request->get('token');
        $width = $request->get('width');
        $height = $request->get('height');

        // check if token set
        if (!isset($token)) {
            return $this->json([
               'status' => 'error',
               'code' => JsonResponse::HTTP_BAD_REQUEST,
               'message' => 'token, width and height parameters is required'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // check if media exist
        if (!$this->storageManager->isMediaExist($userId, $token)) {
            return $this->json([
               'status' => 'error',
               'code' => JsonResponse::HTTP_NOT_FOUND,
               'message' => 'media token: ' . $token . ' not found'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // get content
        $content = $this->storageManager->getMediaThumbnail($userId, $token, $width, $height);

        // create a streamed response with image thumbnail content
        return new StreamedResponse(function () use ($content) {
            echo $content;
        }, ContentResponse::HTTP_OK, [
            'Content-Type' => 'image/jpg',
        ]);
    }
}
