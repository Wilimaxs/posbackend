<?php

namespace App\Services\V1\Receivable;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReceivableListService
{
    public function getReceivables(
        int   $storeId,
        array $filters
    ): LengthAwarePaginator
    {
        $query = Sale::query()
            ->with([
                'customer:id,name,phone',
            ])
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'status',
                'completed'
            )
            ->where(
                'remaining_balance',
                '>',
                0
            );

        $this->applySearch(
            query: $query,
            search: $filters['search'] ?? null,
        );

        $this->applyDueStatus(
            query: $query,
            dueStatus: $filters['due_status'] ?? null,
        );

        $this->applySort(
            query: $query,
            sort: $filters['sort'] ?? 'newest',
        );

        $query->orderByDesc('id');

        return $query->paginate(
            (int)($filters['per_page'] ?? 20)
        );
    }

    private function applySearch(
        Builder $query,
        ?string $search
    ): void
    {
        if (
            $search === null
            || trim($search) === ''
        ) {
            return;
        }

        $search = trim($search);

        $query->where(
            function (Builder $query) use ($search) {
                $query
                    ->where(
                        'invoice_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'customer',
                        function (
                            Builder $customerQuery
                        ) use ($search) {
                            $customerQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
            }
        );
    }

    private function applyDueStatus(
        Builder $query,
        ?string $dueStatus
    ): void
    {
        if ($dueStatus === null) {
            return;
        }

        match ($dueStatus) {
            'active' =>
            $query->whereDate(
                'due_date',
                '>',
                today()
            ),
            'due_today' =>
            $query->whereDate(
                'due_date',
                today()
            ),
            'overdue' =>
            $query->whereDate(
                'due_date',
                '<',
                today()
            ),

            default =>
            null,
        };
    }

    private function applySort(
        Builder $query,
        string  $sort
    ): void
    {
        match ($sort) {
            'oldest' =>
            $query->orderBy(
                'created_at'
            ),

            'due_date_asc' =>
            $query->orderBy(
                'due_date'
            ),

            'due_date_desc' =>
            $query->orderByDesc(
                'due_date'
            ),

            'balance_asc' =>
            $query->orderBy(
                'remaining_balance'
            ),

            'balance_desc' =>
            $query->orderByDesc(
                'remaining_balance'
            ),

            default =>
            $query->orderByDesc(
                'created_at'
            ),
        };
    }
}
