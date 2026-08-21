<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
    CREATE TRIGGER trg_receivable_payments_after_insert
    AFTER INSERT ON receivable_payments
    FOR EACH ROW
    BEGIN
        DECLARE new_remaining DECIMAL(15, 2);

        SELECT GREATEST(remaining_balance - NEW.amount, 0)
        INTO new_remaining
        FROM sales
        WHERE id = NEW.sale_id;

        UPDATE sales
        SET
            remaining_balance = new_remaining,

            paid_at = CASE
                WHEN new_remaining = 0
                    THEN NEW.created_at
                ELSE NULL
            END,

            updated_at = NOW()
        WHERE id = NEW.sale_id;
    END
    ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_receivable_payments_after_insert');
    }
};
