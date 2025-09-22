<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW event_movements_view AS
            SELECT 
                em.id,
                em.bussines_id,
                em.event_id,
                em.club_id,
                em.method_payment_id,
                em.category_income_id,
                em.category_egress_id,
                em.currency_id,
                em.supplier_id,
                em.expense_id,
                em.amount,
                em.date,
                em.description,
                em.status,
                em.type,
                em.user_id,
                em.created_at,
                em.updated_at,
                
                -- Club information
                c.name as club_name,
                c.logo as club_logo,
                c.cuit as club_cuit,
                c.responsible as club_responsible,
                c.phone as club_phone,
                c.email as club_email,
                
                -- Currency information
                curr.name as currency_name,
                curr.symbol as currency_symbol,
                
                -- Method Payment information
                mp.account_holder as method_payment_account_holder,
                mp.account_number as method_payment_account_number,
                mp.cbu_cvu as method_payment_cbu_cvu,
                mp.alias as method_payment_alias,
                mp.type_account as method_payment_type_account,
                mp.initial_balance as method_payment_initial_balance,
                mp.current_balance as method_payment_current_balance,
                
                -- Entity information (from method_payment)
                e.name as entity_name,
                
                -- Supplier information
                s.name as supplier_name,
                s.representant as supplier_representant,
                s.phone as supplier_phone,
                s.description as supplier_description,
                
                -- Account Receivable Payment information
                arp.id as account_receivable_payment_id,
                arp.amount as account_receivable_payment_amount,
                arp.date as account_receivable_payment_date,
                
                -- Account Payable Payment information
                app.id as account_payable_payment_id,
                app.amount as account_payable_payment_amount,
                app.date as account_payable_payment_date
                
            FROM event_movements em
            LEFT JOIN clubs c ON em.club_id = c.id
            LEFT JOIN currencies curr ON em.currency_id = curr.id
            LEFT JOIN method_payments mp ON em.method_payment_id = mp.id
            LEFT JOIN entities e ON mp.entity_id = e.id
            LEFT JOIN suppliers s ON em.supplier_id = s.id
            LEFT JOIN account_receivable_payments arp ON em.account_receivable_payment_id = arp.id
            LEFT JOIN account_payable_payments app ON em.account_payable_payment_id = app.id
            WHERE em.status != 'Cancelado'
            ORDER BY em.date DESC, em.created_at DESC
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS event_movements_view");
    }
};
