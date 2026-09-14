<?php

namespace Modules\CORE\Interfaces;

interface UserRepositoryInterface extends ACLRepositoryInterface
{
    public function findByEmail(string $email): ?\App\Models\User;
}
