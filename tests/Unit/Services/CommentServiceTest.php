<?php

namespace Tests\Unit\Services;

use App\DTO\Comment\CommentDetailsDTO;
use App\DTO\Comment\CreateUpdateCommentDTO;
use App\DTO\CommentHistory\CommentHistoryDetailsDTO;
use App\Exceptions\NotOwnerException;
use App\Exceptions\RecordNotFoundException;
use App\Models\Comment;
use App\Models\CommentHistory;
use App\Models\User;
use App\Repositories\Contracts\CommentRepository;
use App\Repositories\Contracts\Impl\PaginationPresenter;
use App\Repositories\Contracts\PaginationInterface;
use App\Services\AuthService;
use App\Services\CommentService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CommentServiceTest extends TestCase
{
    private $commentService;
    private $commentRepository;
    private $authService;
    private $existingId;
    private $existingComment;
    private $nonExistingId;
    private $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository = Mockery::mock(CommentRepository::class);
        $this->authService = Mockery::mock(AuthService::class);
        $this->existingComment = Mockery::mock(Comment::class);
        $this->author = Mockery::mock(User::class);
        $this->existingId = 1;
        $this->nonExistingId = 100;

        $this->commentService = new CommentService(
            $this->commentRepository,
            $this->authService
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testFindAllShouldReturnPaginationInterface()
    {
        $page = 1;
        $perPage = 15;
        $filter = 'test';

        $paginationInterface = Mockery::mock(PaginationInterface::class);

        $this->commentRepository
            ->shouldReceive('findAllPaged')
            ->once()
            ->with($page, $perPage, $filter, Mockery::type('callable'))
            ->andReturn($paginationInterface);

        $result = $this->commentService->findAll($page, $perPage, $filter);

        $this->assertInstanceOf(PaginationInterface::class, $result);
        $this->assertTrue(method_exists($result, 'items'));
        $this->assertTrue(method_exists($result, 'total'));
        $this->assertTrue(method_exists($result, 'isFirstPage'));
        $this->assertTrue(method_exists($result, 'isLastPage'));
        $this->assertTrue(method_exists($result, 'currentPage'));
        $this->assertTrue(method_exists($result, 'getPreviousPageNumber'));
        $this->assertTrue(method_exists($result, 'getNextPageNumber'));
    }

    public function testFindByIdShouldReturnCommentDetailsDtoWhenIdExists()
    {
        $createdAt = $updatedAt = now()->format('Y-m-d H:i:s');
        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($this->existingId);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('comment')
            ->andReturn('test');

        $this->author->shouldReceive('getAttribute')
            ->once()
            ->with('name')
            ->andReturn('test');

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('author')
            ->andReturn($this->author);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('created_at')
            ->andReturn($createdAt);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('updated_at')
            ->andReturn($updatedAt);

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->existingId)
            ->andReturn($this->existingComment);

        $result = $this->commentService->findById($this->existingId);

        $this->assertInstanceOf(CommentDetailsDTO::class, $result);
    }
    public function testFindByIdShouldThrowRecordNotFoundExceptionWhenIdDoesNotExist()
    {
        $this->expectException(RecordNotFoundException::class);

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->nonExistingId)
            ->andThrow(RecordNotFoundException::class);

        $this->commentService->findById($this->nonExistingId);
    }

    public function testCreateShouldReturnCommentDetailsDto()
    {
        $comment = 'teste';
        $dto = new CreateUpdateCommentDTO($comment);

        $createdAt = $updatedAt = now()->format('Y-m-d H:i:s');
        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($this->existingId);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('comment')
            ->andReturn($comment);

        $this->author->shouldReceive('getAttribute')
            ->once()
            ->with('name')
            ->andReturn('test');

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('author')
            ->andReturn($this->author);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('created_at')
            ->andReturn($createdAt);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('updated_at')
            ->andReturn($updatedAt);

        $this->commentRepository
            ->shouldReceive('create')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->existingComment);

        $result = $this->commentService->create($dto);

        $this->assertInstanceOf(CommentDetailsDTO::class, $result);
        $this->assertEquals($comment, $result->getComment());
    }

    public function testDeleteShouldDeleteCommentWhenIdExistsAndUserIsOwnerAndNotAdmin()
    {
        $commentServiceMock = Mockery::mock(CommentService::class, [
            $this->commentRepository,
            $this->authService
        ])->makePartial();

        $commentServiceMock->shouldAllowMockingProtectedMethods();
        $commentServiceMock->shouldReceive('validateOwner')
            ->once()
            ->with($this->existingId)
            ->andReturn(null);

        $this->authService
            ->shouldReceive('isLoggedUserAdmin')
            ->once()
            ->andReturn(false);

        $this->commentRepository
            ->shouldReceive('delete')
            ->once()
            ->with($this->existingId)
            ->andReturn(null);

        $commentServiceMock->delete($this->existingId);

        $this->assertTrue(true); // Apenas valida que não ocorreu exceção
    }
    public function testDeleteShouldDeleteCommentWhenIdExistsAndUserIsNotOwnerButIsAdmin()
    {
        $this->authService
            ->shouldReceive('isLoggedUserAdmin')
            ->once()
            ->andReturn(true);

        $this->commentRepository
            ->shouldReceive('delete')
            ->once()
            ->with($this->existingId)
            ->andReturn(null);

        $this->commentService->delete($this->existingId);

        $this->assertTrue(true); // Apenas valida que não ocorreu exceção
    }
    public function testDeleteShouldThrowNotOwnerExceptionWhenIdExistsAndUserIsNotOwnerAndNotAdmin()
    {
        $this->expectException(NotOwnerException::class);

        $commentServiceMock = Mockery::mock(CommentService::class, [
            $this->commentRepository,
            $this->authService
        ])->makePartial();

        $commentServiceMock->shouldAllowMockingProtectedMethods();
        $commentServiceMock->shouldReceive('validateOwner')
            ->once()
            ->with($this->nonExistingId)
            ->andThrow(NotOwnerException::class);

        $this->authService
            ->shouldReceive('isLoggedUserAdmin')
            ->once()
            ->andReturn(false);

        $commentServiceMock->delete($this->nonExistingId);
    }
    public function testDeleteShouldThrowRecordNotFoundExceptionWhenIdDoesNotExist()
    {
        $this->expectException(RecordNotFoundException::class);

        $this->authService
            ->shouldReceive('isLoggedUserAdmin')
            ->once()
            ->andReturn(true);

        $this->commentRepository
            ->shouldReceive('delete')
            ->once()
            ->with($this->nonExistingId)
            ->andThrow(RecordNotFoundException::class);

        $this->commentService->delete($this->nonExistingId);
    }

    public function testGetHistoryShouldReturnCommentHistoryDetailsDtoWhenCommentExistsAndUserIsOwner()
    {
        $now = now()->format('Y-m-d H:i:s');
        $commentHistory1Mock = Mockery::mock(CommentHistory::class);
        $commentHistory1Mock->shouldReceive('getAttribute')
            ->once()
            ->with('comment')
            ->andReturn('comment1');
        $commentHistory1Mock->shouldReceive('getAttribute')
            ->once()
            ->with('created_at')
            ->andReturn($now);

        $commentHistory2Mock = Mockery::mock(CommentHistory::class);
        $commentHistory2Mock->shouldReceive('getAttribute')
            ->once()
            ->with('comment')
            ->andReturn('comment2');
        $commentHistory2Mock->shouldReceive('getAttribute')
            ->once()
            ->with('created_at')
            ->andReturn($now);

        $history = [$commentHistory1Mock, $commentHistory2Mock];

        $commentServiceMock = Mockery::mock(CommentService::class, [
            $this->commentRepository,
            $this->authService
        ])->makePartial();

        $commentServiceMock->shouldAllowMockingProtectedMethods();
        $commentServiceMock->shouldReceive('validateOwner')
            ->once()
            ->with($this->existingId)
            ->andReturn(null);

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->existingId)
            ->andReturn($this->existingComment);

        $this->existingComment->shouldReceive('getAttribute')
            ->twice()
            ->with('history')
            ->andReturn($history);

        $result = $commentServiceMock->getHistory($this->existingId);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(CommentHistoryDetailsDTO::class, $result[0]);
        $this->assertEquals('comment1', $result[0]->comment);
    }

    public function testGetHistoryShouldThrowNotOwnerExceptionWhenCommentExistsAndUserIsNotOwner()
    {
        $this->expectException(NotOwnerException::class);

        $commentServiceMock = Mockery::mock(CommentService::class, [
            $this->commentRepository,
            $this->authService
        ])->makePartial();

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->existingId)
            ->andReturn($this->existingComment);

        $commentServiceMock->shouldAllowMockingProtectedMethods();
        $commentServiceMock->shouldReceive('validateOwner')
            ->once()
            ->with($this->existingId)
            ->andThrow(NotOwnerException::class);

        $commentServiceMock->getHistory($this->existingId);
    }

    public function testGetHistoryShouldThrowRecordNotFoundExceptionWhenCommentDoesNotExist()
    {
        $this->expectException(RecordNotFoundException::class);

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->nonExistingId)
            ->andThrow(RecordNotFoundException::class);

        $this->commentService->getHistory($this->nonExistingId);
    }

    public function testUpdateShouldReturnCommentDetailsDtoWhenIdExistsAndUserIsTheOwner()
    {
        $commentServiceMock = Mockery::mock(CommentService::class, [
            $this->commentRepository,
            $this->authService
        ])->makePartial();

        $commentServiceMock->shouldAllowMockingProtectedMethods();
        $commentServiceMock->shouldReceive('validateOwner')
            ->once()
            ->with($this->existingId)
            ->andReturn(null);

        $this->commentRepository
            ->shouldReceive('update')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->existingComment);

        $createdAt = $updatedAt = now()->format('Y-m-d H:i:s');
        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($this->existingId);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('comment')
            ->andReturn('test');

        $this->author->shouldReceive('getAttribute')
            ->once()
            ->with('name')
            ->andReturn('test');

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('author')
            ->andReturn($this->author);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('created_at')
            ->andReturn($createdAt);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('updated_at')
            ->andReturn($updatedAt);

        $dto = new CreateUpdateCommentDTO('test');
        $result = $commentServiceMock->update($this->existingId, $dto);

        $this->assertInstanceOf(CommentDetailsDTO::class, $result);
        $this->assertEquals('test', $result->getComment());
    }

    public function testUpdateShouldThrowNotOwnerExceptionWhenUserIsNotTheOwner()
    {
        $this->expectException(NotOwnerException::class);
        $commentServiceMock = Mockery::mock(CommentService::class, [
            $this->commentRepository,
            $this->authService
        ])->makePartial();

        $commentServiceMock->shouldAllowMockingProtectedMethods();
        $commentServiceMock->shouldReceive('validateOwner')
            ->once()
            ->with($this->existingId)
            ->andThrow(NotOwnerException::class);

        $dto = new CreateUpdateCommentDTO('test');
        $commentServiceMock->update($this->existingId, $dto);
    }

    public function testUpdateShouldThrowRecordNotFoundExceptionWhenIdDoesNotExist()
    {
        $this->expectException(RecordNotFoundException::class);
        $commentServiceMock = Mockery::mock(CommentService::class, [
            $this->commentRepository,
            $this->authService
        ])->makePartial();

        $commentServiceMock->shouldAllowMockingProtectedMethods();
        $commentServiceMock->shouldReceive('validateOwner')
            ->once()
            ->with($this->nonExistingId)
            ->andThrow(RecordNotFoundException::class);

        $dto = new CreateUpdateCommentDTO('test');
        $commentServiceMock->update($this->nonExistingId, $dto);
    }

    public function testDeleteAllShouldDeleteWhenUserIsAdmin()
    {
        $this->authService
            ->shouldReceive('validateAdmin')
            ->once()
            ->andReturn(null);

        $this->commentRepository
            ->shouldReceive('deleteAll')
            ->once();

        $this->commentService->deleteAll();

        $this->assertTrue(true); // Apenas para validar que não foi lançada exceção
    }

    public function testDeleteAllShouldThrowNotAdminExceptionWhenUserIsNotAdmin()
    {
        $this->expectException(NotOwnerException::class);

        $this->authService
            ->shouldReceive('validateAdmin')
            ->once()
            ->andThrow(NotOwnerException::class);

        $this->commentService->deleteAll();
    }

    public function testValidateOwnerShouldDoNothingWhenCommentExistsAndUserIsTheOwner()
    {
        $this->author->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn(1);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('author')
            ->andReturn($this->author);

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->existingId)
            ->andReturn($this->existingComment);

        Gate::shouldReceive('denies')
            ->once()
            ->withAnyArgs()
            ->andReturn(false);

        $method = new ReflectionMethod(CommentService::class, 'validateOwner');
        $method->setAccessible(true);

        $method->invoke($this->commentService, $this->existingId);
        $this->assertTrue(true);
    }

    public function testValidateOwnerShouldThrowRecordNotFoundExceptionWhenCommentDoesNotExist()
    {
        $this->expectException(RecordNotFoundException::class);

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->nonExistingId)
            ->andThrow(RecordNotFoundException::class);

        $method = new ReflectionMethod(CommentService::class, 'validateOwner');
        $method->setAccessible(true);

        $method->invoke($this->commentService, $this->nonExistingId);
    }

    public function testValidateOwnerShouldThrowNotOwnerExceptionWhenCommentExistsAndUserIsNotTheOwner()
    {
        $this->expectException(NotOwnerException::class);

        $this->author->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn(1);

        $this->existingComment->shouldReceive('getAttribute')
            ->once()
            ->with('author')
            ->andReturn($this->author);

        $this->commentRepository
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->with($this->existingId)
            ->andReturn($this->existingComment);

        Gate::shouldReceive('denies')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $method = new ReflectionMethod(CommentService::class, 'validateOwner');
        $method->setAccessible(true);

        $method->invoke($this->commentService, $this->existingId);
    }
}
