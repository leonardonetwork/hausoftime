<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marketplace: paesi, zone/tariffe di spedizione, coupon, indirizzi,
 * preferiti, ordini, righe ordine (una per orologio/venditore), eventi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('notice_es')->nullable();   // es. aranceles a cargo del comprador
            $t->text('notice_en')->nullable();
            $t->boolean('active')->default(true);
            $t->integer('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('countries', function (Blueprint $t) {
            $t->id();
            $t->char('code', 2)->unique();
            $t->string('name_es');
            $t->string('name_en');
            $t->boolean('enabled')->default(false);           // spedizione/vendita abilitata
            $t->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $t->integer('sort_order')->default(0);
            $t->timestamps();
            $t->index(['enabled', 'sort_order']);
        });

        Schema::create('shipping_rates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipping_zone_id')->constrained('shipping_zones')->cascadeOnDelete();
            $t->string('name_es');
            $t->string('name_en')->nullable();
            $t->string('carrier', 40)->default('fedex');
            $t->decimal('base_price', 10, 2)->default(0);           // per pieza
            $t->decimal('insurance_rate', 6, 3)->default(0);        // % sul valore del pezzo
            $t->decimal('insurance_min', 10, 2)->default(0);
            $t->decimal('free_over', 12, 2)->nullable();            // spedizione gratis sopra (valore pezzo)
            $t->decimal('min_value', 12, 2)->nullable();
            $t->decimal('max_value', 12, 2)->nullable();
            $t->string('delivery_estimate', 60)->nullable();         // "24–72 h"
            $t->boolean('active')->default(true);
            $t->integer('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code', 60)->unique();
            $t->string('description')->nullable();
            $t->enum('type', ['percent', 'fixed'])->default('percent');
            $t->decimal('value', 12, 2);
            $t->decimal('min_subtotal', 12, 2)->nullable();
            $t->decimal('max_discount', 12, 2)->nullable();
            $t->json('brand_ids')->nullable();          // null = tutte le marche
            $t->boolean('first_order_only')->default(false);
            $t->boolean('free_shipping')->default(false);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('usage_limit_per_user')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('label', 60)->nullable();
            $t->string('first_name');
            $t->string('last_name');
            $t->string('company')->nullable();
            $t->string('tax_id', 40)->nullable();
            $t->string('line1');
            $t->string('line2')->nullable();
            $t->string('city', 120);
            $t->string('postal_code', 20);
            $t->string('province', 120)->nullable();
            $t->char('country_code', 2);
            $t->string('phone_prefix', 10)->nullable();
            $t->string('phone', 30)->nullable();
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        Schema::create('favorites', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('watch_id');
            $t->timestamps();
            $t->unique(['user_id', 'watch_id']);
            $t->foreign('watch_id')->references('id')->on('watches')->cascadeOnDelete();
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('number', 30)->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('email');
            $t->string('status', 30)->default('pending_payment')->index();
            $t->char('currency', 3)->default('EUR');
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('discount_total', 12, 2)->default(0);
            $t->decimal('shipping_total', 12, 2)->default(0);
            $t->decimal('total', 12, 2)->default(0);
            $t->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $t->string('coupon_code', 60)->nullable();
            $t->json('shipping_address');
            $t->json('billing_address')->nullable();
            $t->char('shipping_country', 2)->nullable();
            $t->text('customer_notes')->nullable();
            $t->text('admin_notes')->nullable();
            $t->string('stripe_session_id')->nullable()->index();
            $t->string('stripe_payment_intent')->nullable()->index();
            $t->string('stripe_charge_id')->nullable();
            $t->string('payment_method', 40)->nullable();
            $t->timestamp('reserved_until')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->string('locale', 5)->default('es');
            $t->string('ip_address', 45)->nullable();
            $t->timestamps();
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('watch_id')->nullable();
            $t->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status', 30)->default('pending')->index();
            $t->string('brand');
            $t->string('model');
            $t->string('reference')->nullable();
            $t->string('image')->nullable();
            $t->decimal('price', 12, 2);
            $t->decimal('discount', 12, 2)->default(0);
            $t->decimal('shipping', 12, 2)->default(0);
            $t->decimal('insurance', 12, 2)->default(0);
            $t->string('shipping_rate_name')->nullable();
            $t->decimal('commission_rate', 5, 2)->default(0);
            $t->decimal('commission_amount', 12, 2)->default(0);
            $t->decimal('seller_payout', 12, 2)->default(0);
            $t->string('carrier', 40)->nullable();
            $t->string('tracking_number', 80)->nullable();
            $t->string('tracking_url')->nullable();
            $t->timestamp('ship_by')->nullable();
            $t->timestamp('shipped_at')->nullable();
            $t->timestamp('received_at')->nullable();
            $t->timestamp('release_due_at')->nullable();
            $t->timestamp('released_at')->nullable();
            $t->string('stripe_transfer_id')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->decimal('refunded_amount', 12, 2)->default(0);
            $t->string('stripe_refund_id')->nullable();
            $t->timestamp('dispute_opened_at')->nullable();
            $t->text('dispute_reason')->nullable();
            $t->timestamp('dispute_closed_at')->nullable();
            $t->timestamp('last_reminder_at')->nullable();
            $t->timestamps();
            $t->foreign('watch_id')->references('id')->on('watches')->nullOnDelete();
        });

        Schema::create('order_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 40);
            $t->text('message')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('email');
            $t->decimal('amount', 12, 2);
            $t->timestamps();
        });

        Schema::create('stripe_events', function (Blueprint $t) {
            $t->id();
            $t->string('event_id')->unique();
            $t->string('type', 80);
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['stripe_events', 'coupon_redemptions', 'order_events', 'order_items', 'orders', 'favorites',
                  'addresses', 'coupons', 'shipping_rates', 'countries', 'shipping_zones'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
