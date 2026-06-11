<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;

/**
 * @internal
 */
final class UserModelTest extends CIUnitTestCase
{
    protected $refresh = false;
    protected $migrate = false;
    protected UserModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UserModel();
    }

    public function testFindByUsernameReturnsAdminUser(): void
    {
        $user = $this->model->findByUsername('admin');
        $this->assertIsArray($user);
        $this->assertSame('admin', $user['username']);
    }

    public function testFindByUsernameReturnsNullForUnknownUser(): void
    {
        $user = $this->model->findByUsername('nonexistent');
        $this->assertNull($user);
    }

    public function testPasswordHashIsValid(): void
    {
        $user = $this->model->findByUsername('admin');
        $this->assertTrue(password_verify('admin123', $user['password']));
    }
}
