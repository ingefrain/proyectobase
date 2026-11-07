<?php

namespace App\Services\Documents;

use App\Models\ApplicationBranding;
use App\Models\Document;
use App\Models\Location;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Default owner-level authorization. Derived projects may replace this service
 * to scope additional polymorphic owners through their own policies.
 */
class DocumentOwnerAccess
{
    public function allows(User $actor, Document $document): bool
    {
        $owner = $document->documentable;
        if (! $owner instanceof Model) {
            return false;
        }

        if ($owner instanceof User) {
            return $actor->is($owner);
        }
        if ($owner instanceof ApplicationBranding) {
            return $actor->can('branding.manage') && (string) $owner->getKey() === '1';
        }

        $gate = Gate::forUser($actor);

        return $gate->getPolicyFor($owner) !== null && $gate->allows('view', $owner);
    }

    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        return $query->where(function (Builder $builder) use ($actor): void {
            $builder->where(function (Builder $users) use ($actor): void {
                $users->where('documentable_type', (new User)->getMorphClass())
                    ->where('documentable_id', (string) $actor->getKey());
            });
            if ($actor->can('branding.manage')) {
                $builder->orWhere(function (Builder $branding): void {
                    $branding->where('documentable_type', (new ApplicationBranding)->getMorphClass())
                        ->where('documentable_id', '1');
                });
            }
            if ($actor->can('organizations.view')) {
                $builder->orWhere('documentable_type', (new Organization)->getMorphClass());
            }
            if ($actor->can('locations.view')) {
                $builder->orWhere('documentable_type', (new Location)->getMorphClass());
            }
        });
    }

    public function canManage(User $actor, Document $document): bool
    {
        return $this->allows($actor, $document);
    }
}
