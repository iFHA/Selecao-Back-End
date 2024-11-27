<?php

namespace App\Http\Controllers;

use App\Adapters\ApiAdapter;
use App\DTO\Comment\CreateUpdateCommentDTO;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService
    ) {}

    public function findAll(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
            'perPage' => 'integer|min:1|max:50'
        ]);

        $page = $request->page ?? 1;
        $perPage = $request->perPage ?? 15;
        $filter = $request->filter;
        $result = $this->commentService->findAll(
            $page,
            $perPage,
            $filter
        );
        return ApiAdapter::toJson($result);
    }

    public function findById(Request $request): JsonResponse
    {
        $this->validateRequestCommentId($request);
        $dto = $this->commentService->findById($request->commentId);
        return response()->json($dto->toArray());
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate(['comment' => 'required']);

        $dto = $this->commentService->create(CreateUpdateCommentDTO::fromRequest($request));

        $route = route('comment.show', ['commentId' => $dto->getId()]);

        return response()->json($dto->toArray())
            ->header('Location', $route)
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function update(Request $request): JsonResponse
    {
        $this->validateRequestCommentId($request);
        $request->validate([
            'comment' => 'required'
        ]);

        $dto = $this->commentService->update($request->commentId, CreateUpdateCommentDTO::fromRequest($request));
        return response()->json($dto->toArray());
    }

    public function delete(Request $request)
    {
        $this->validateRequestCommentId($request);

        $this->commentService->delete($request->commentId);
        return response(status: JsonResponse::HTTP_NO_CONTENT);
    }

    public function deleteAll(Request $request)
    {
        $request->validate(['confirm' => 'required|accepted']);
        $this->commentService->deleteAll();
        return response(status: JsonResponse::HTTP_NO_CONTENT);
    }

    public function history(Request $request): JsonResponse
    {
        $this->validateRequestCommentId($request);
        return response()->json($this->commentService->getHistory($request->commentId));
    }

    private function validateRequestCommentId(Request $request)
    {
        $request->merge(['commentId' => $request->route('commentId')]);
        $request->validate([
            'commentId' => 'integer|required'
        ]);
    }
}
