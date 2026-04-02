<?php

namespace Tests;

use App\Models\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function roleId(string $name): int
    {
        $id = Role::idFor($name);
        $this->assertNotNull($id, "Rôle manquant en base : {$name}");

        return (int) $id;
    }
}
