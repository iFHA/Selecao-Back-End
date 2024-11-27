<?php

namespace Tests\Unit\Services;

use App\DTO\Auth\AuthUserDTO;
use App\DTO\Auth\ChangePasswordDTO;
use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\DTO\User\UserDetailsDTO;
use App\Exceptions\NotAdminException;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private $authService;
    private $userRepositoryMock;

    private $existingUser;
    private $existingUserId;
    private $existingUserName;
    private $existingEmail;
    private $validPassword;
    private $newPassword;

    private $nonExistingEmail;
    private $wrongPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->existingUserId = 1;
        $this->existingUserName = 'maria';
        $this->existingUser = Mockery::mock(User::class);
        $this->authService = new AuthService($this->userRepositoryMock);
        $this->existingEmail = 'email@gmail.com';
        $this->validPassword = 'password';

        $this->nonExistingEmail = 'invalid@email.com';
        $this->wrongPassword = 'wrong';
        $this->newPassword = 'new';
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    public function testExistingUserShouldBeAbleToAuthenticate()
    {
        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($this->existingEmail)
            ->andReturn($this->existingUser);

        Hash::shouldReceive('check')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        $this->existingUser->shouldReceive('tokens->delete')
            ->once();

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('email')
            ->andReturn($this->existingEmail);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('password')
            ->andReturn($this->validPassword);

        $this->existingUser->shouldReceive('createToken')
            ->once()
            ->with($this->existingEmail)
            ->andReturn((object)['plainTextToken' => 'test']);

        $dto = new AuthUserDTO($this->existingEmail, $this->validPassword);
        $token = $this->authService->auth($dto);

        $this->assertEquals('test', $token);
    }

    public function testAuthenticationWithNonExistingEmailShouldThrowsValidationException()
    {
        $this->expectException(ValidationException::class);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($this->nonExistingEmail)
            ->andReturn(null);

        $dto = new AuthUserDTO($this->nonExistingEmail, $this->wrongPassword);
        $this->authService->auth($dto);
    }

    public function testAuthenticationWithExistingEmailAndWrongPasswordShouldThrowsValidationException()
    {
        $this->expectException(ValidationException::class);

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($this->existingEmail)
            ->andReturn($this->existingUser);

        Hash::shouldReceive('check')
            ->once()
            ->withAnyArgs()
            ->andReturn(false);

        $this->existingUser->shouldNotReceive('tokens->delete');

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('password')
            ->andReturn($this->validPassword);

        $dto = new AuthUserDTO($this->existingEmail, $this->wrongPassword);
        $this->authService->auth($dto);
    }

    public function testUserShouldBeAbleToLogout()
    {
        $authServiceMock = Mockery::mock(AuthService::class, [$this->userRepositoryMock])
            ->makePartial();

        $authServiceMock->shouldAllowMockingProtectedMethods();

        $authServiceMock
            ->shouldReceive('getLoggedUserEntity')
            ->once()
            ->andReturn($this->existingUser);

        $this->existingUser->shouldReceive('tokens->delete')
            ->once();

        $authServiceMock->logout();
        $this->assertTrue(true);
    }

    public function testGetLoggedUserEntityShouldReturnLoggedUser()
    {
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($this->existingUser);

        $this->userRepositoryMock
            ->shouldReceive('findByIdOrFail')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->existingUser);

        $this->existingUser
            ->shouldReceive('getAuthIdentifier')
            ->once()
            ->andReturn($this->existingUserId);

        $method = new ReflectionMethod(AuthService::class, 'getLoggedUserEntity');
        $method->setAccessible(true);

        $loggedUserEntity = $method->invoke($this->authService);
        $this->assertInstanceOf(User::class, $loggedUserEntity);
    }

    public function testUserShouldBeAbleToChangePassword()
    {
        $authServiceMock = Mockery::mock(AuthService::class, [$this->userRepositoryMock])
            ->makePartial();

        $authServiceMock->shouldAllowMockingProtectedMethods();

        $authServiceMock
            ->shouldReceive('getLoggedUserEntity')
            ->once()
            ->andReturn($this->existingUser);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($this->existingUserId);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('password')
            ->andReturn($this->validPassword);

        Hash::shouldReceive('check')
            ->once()
            ->withAnyArgs()
            ->andReturn(true);

        Hash::shouldReceive('make')
            ->once()
            ->with($this->newPassword)
            ->andReturn($this->newPassword);

        $this->userRepositoryMock
            ->shouldReceive('changePassword')
            ->once()
            ->with($this->existingUserId, $this->newPassword);

        $dto = new ChangePasswordDTO('current-password', $this->newPassword);
        $authServiceMock->changePassword($dto);
        $this->assertTrue(true);
    }

    public function testChangePasswordShouldThrowValidationExceptionWhenCurrentPasswordIsIncorrect()
    {
        $this->expectException(ValidationException::class);

        $authServiceMock = Mockery::mock(AuthService::class, [$this->userRepositoryMock])
            ->makePartial();

        $authServiceMock->shouldAllowMockingProtectedMethods();

        $authServiceMock
            ->shouldReceive('getLoggedUserEntity')
            ->once()
            ->andReturn($this->existingUser);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('password')
            ->andReturn($this->validPassword);

        Hash::shouldReceive('check')
            ->once()
            ->withAnyArgs()
            ->andReturn(false);

        $dto = new ChangePasswordDTO('wrong-password', 'new-password');
        $authServiceMock->changePassword($dto);
    }

    public function testMeShouldReturnLoggedUserDetails()
    {
        $authServiceMock = Mockery::mock(AuthService::class, [$this->userRepositoryMock])
            ->makePartial();

        $authServiceMock->shouldAllowMockingProtectedMethods();

        $authServiceMock
            ->shouldReceive('getLoggedUserEntity')
            ->once()
            ->andReturn($this->existingUser);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($this->existingUserId);
        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('name')
            ->andReturn($this->existingUserName);
        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('email')
            ->andReturn($this->existingEmail);

        $dto = $authServiceMock->me();

        $this->assertEquals($this->existingUserId, $dto->getId());
        $this->assertEquals($this->existingUserName, $dto->getName());
        $this->assertEquals($this->existingEmail, $dto->getEmail());
    }

    public function testUserShouldBeAbleToRegister()
    {
        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('id')
            ->andReturn($this->existingUserId);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('name')
            ->andReturn($this->existingUserName);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('email')
            ->andReturn($this->existingEmail);

        Hash::shouldReceive('make')
            ->with('pswd')
            ->andReturn('pswd');

        $this->userRepositoryMock->shouldReceive('create')
            ->withAnyArgs()
            ->andReturn($this->existingUser);

        $newUser = new CreateUserDTO($this->existingUserName, $this->existingEmail, 'pswd');
        $createdUser = $this->authService->register($newUser);

        $this->assertEquals($this->existingUserName, $createdUser->getName());
        $this->assertEquals($this->existingEmail, $createdUser->getEmail());
        $this->assertEquals($this->existingUserId, $createdUser->getId());
    }

    public function testUpdateMeShouldReturnUserDetailsDto()
    {
        $authServiceMock = Mockery::mock(AuthService::class, [$this->userRepositoryMock])
            ->makePartial();

        $authServiceMock->shouldAllowMockingProtectedMethods();

        $this->existingUser->shouldReceive('getAttribute')
            ->twice()
            ->with('id')
            ->andReturn($this->existingUserId);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('name')
            ->andReturn($this->existingUserName);

        $this->existingUser->shouldReceive('getAttribute')
            ->once()
            ->with('email')
            ->andReturn($this->existingEmail);

        $authServiceMock
            ->shouldReceive('getLoggedUserEntity')
            ->once()
            ->andReturn($this->existingUser);

        $this->userRepositoryMock->shouldReceive('update')
            ->withAnyArgs()
            ->andReturn($this->existingUser);

        $user = new UpdateUserDTO($this->existingUserName, $this->existingEmail);
        $updatedUser = $authServiceMock->updateMe($user);

        $this->assertInstanceOf(UserDetailsDTO::class, $updatedUser);
        $this->assertEquals($this->existingUserName, $updatedUser->getName());
        $this->assertEquals($this->existingEmail, $updatedUser->getEmail());
        $this->assertEquals($this->existingUserId, $updatedUser->getId());
    }

    public function testValidateAdminShouldThrowsNotAdminExceptionWhenLoggedUserIsNotAdmin()
    {
        $this->expectException(NotAdminException::class);

        $authServiceMock = Mockery::mock(AuthService::class, [$this->userRepositoryMock])
            ->makePartial();

        $authServiceMock
            ->shouldReceive('isLoggedUserAdmin')
            ->once()
            ->andReturn(false);

        $authServiceMock->validateAdmin();
    }

    public function testValidateAdminShouldDoNothingWhenLoggedUserIsAdmin()
    {
        $authServiceMock = Mockery::mock(AuthService::class, [$this->userRepositoryMock])
            ->makePartial();

        $authServiceMock
            ->shouldReceive('isLoggedUserAdmin')
            ->once()
            ->andReturn(true);

        $authServiceMock->validateAdmin();
        $this->assertTrue(true);
    }

    public function testIsLoggedUserAdminShouldReturnTrueWhenLoggedUserIsAdmin()
    {
        Auth::shouldReceive('user')
            ->andReturn($this->existingUser);

        $this->existingUser
            ->shouldReceive('getAttribute')
            ->with('is_admin')
            ->andReturn(true);

        $isAdmin = $this->authService->isLoggedUserAdmin();
        $this->assertTrue($isAdmin);
    }

    public function testIsLoggedUserAdminShouldReturnFalseWhenLoggedUserIsNotAdmin()
    {
        Auth::shouldReceive('user')
            ->andReturn($this->existingUser);

        $this->existingUser
            ->shouldReceive('getAttribute')
            ->with('is_admin')
            ->andReturn(false);

        $isAdmin = $this->authService->isLoggedUserAdmin();
        $this->assertTrue(!$isAdmin);
    }
}
