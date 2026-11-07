<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Services\Documents\DocumentOwnerAccess;

class DocumentPolicy
{
    public function __construct(private readonly DocumentOwnerAccess $ownerAccess) {}

    public function download(User $user, Document $document): bool
    {
        return $user->can('documents.download') && $this->ownerAccess->allows($user, $document);
    }

    public function update(User $user, Document $document): bool
    {
        return $user->can('documents.update') && $this->ownerAccess->canManage($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('documents.delete') && $this->ownerAccess->canManage($user, $document);
    }
}
