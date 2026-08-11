<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
        CREATE TRIGGER trg_receivable_payments_after_insert
        AFTER INSERT ON receivable_payments
        FOR EACH ROW
        BEGIN
            DECLARE old_remaining_balance DECIMAL(15,2);

            /*
             * Simpan sisa piutang SEBELUM pembayaran ini.
             */
            SELECT remaining_balance
            INTO old_remaining_balance
            FROM sales
            WHERE id = NEW.sale_id;

            UPDATE sales
            SET
                /*
                 * Total yang sudah dibayar bertambah.
                 */
                paid_amount =
                    paid_amount + NEW.amount,

                /*
                 * Sisa piutang dihitung dari nilai SEBELUM trigger update.
                 */
                remaining_balance =
                    GREATEST(
                        old_remaining_balance - NEW.amount,
                        0
                    ),

                /*
                 * Status juga menggunakan nilai lama,
                 * sehingga NEW.amount tidak terhitung dua kali.
                 */
                payment_status =
                    CASE
                        WHEN old_remaining_balance - NEW.amount <= 0
                            THEN 'paid'
                        ELSE 'partial'
                    END,

                /*
                 * paid_at HANYA terisi saat cicilan ini
                 * benar-benar melunasi piutang.
                 */
                paid_at =
                    CASE
                        WHEN old_remaining_balance - NEW.amount <= 0
                            THEN NEW.paid_at
                        ELSE NULL
                    END,

                updated_at = NOW()

            WHERE id = NEW.sale_id;
        END
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_receivable_payments_after_insert");
    }
};
