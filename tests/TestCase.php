<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Automatically seed the database (roles, users, segments, machines)
     * so factory-created users satisfy the role_id foreign key.
     */
    protected $seed = true;
}