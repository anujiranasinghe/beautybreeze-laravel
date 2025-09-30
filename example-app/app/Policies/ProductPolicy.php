<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return ($user->is_admin ?? false) ? true : null;
    }

    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user, Product $product): bool { return true; }
    public function create(User $user): bool { return $user->is_admin ?? false; }
    public function update(User $user, Product $product): bool { return $user->is_admin ?? false; }
    public function delete(User $user, Product $product): bool { return $user->is_admin ?? false; }
}

