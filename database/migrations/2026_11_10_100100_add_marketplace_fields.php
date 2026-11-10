<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watches', function (Blueprint $t) {
            if (!Schema::hasColumn('watches', 'slug')) {
                $t->string('slug')->nullable()->unique()->after('model');
            }
            $t->text('description_en')->nullable()->after('description');
            $t->string('case_size', 40)->nullable()->after('reference');      // "44,25 mm"
            $t->string('material', 160)->nullable()->after('case_size');      // "Cerámica gris con pulsera de caucho"
            $t->char('country_code', 2)->nullable()->after('currency');       // ubicación del reloj
            $t->string('city', 120)->nullable()->after('country_code');
            $t->boolean('is_certified')->default(false)->after('status');     // sello HAUS OF TIME
            $t->boolean('is_featured')->default(false)->after('is_certified');
            $t->integer('sort_order')->default(0)->after('is_featured');
            $t->unsignedInteger('views')->default(0)->after('sort_order');
            $t->timestamp('reserved_until')->nullable()->after('views');
            $t->unsignedBigInteger('reserved_order_id')->nullable()->after('reserved_until');
            $t->timestamp('sold_at')->nullable()->after('approved_at');
            $t->index(['status', 'deleted_at']);
        });

        Schema::table('users', function (Blueprint $t) {
            $t->string('city', 120)->nullable()->after('country');
            $t->text('address')->nullable()->after('city');
            $t->string('stripe_account_id')->nullable()->after('locale');
            $t->char('stripe_country', 2)->nullable()->after('stripe_account_id');
            $t->boolean('stripe_details_submitted')->default(false)->after('stripe_country');
            $t->boolean('stripe_payouts_enabled')->default(false)->after('stripe_details_submitted');
            $t->boolean('marketing_opt_in')->default(false)->after('stripe_payouts_enabled');
            $t->timestamp('last_login_at')->nullable()->after('marketing_opt_in');
        });

        Schema::table('watch_brands', function (Blueprint $t) {
            $t->string('logo')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('watches', function (Blueprint $t) {
            $t->dropIndex(['status', 'deleted_at']);
            $t->dropColumn(['slug', 'description_en', 'case_size', 'material', 'country_code', 'city', 'is_certified',
                'is_featured', 'sort_order', 'views', 'reserved_until', 'reserved_order_id', 'sold_at']);
        });
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['city', 'address', 'stripe_account_id', 'stripe_country', 'stripe_details_submitted',
                'stripe_payouts_enabled', 'marketing_opt_in', 'last_login_at']);
        });
        Schema::table('watch_brands', fn (Blueprint $t) => $t->dropColumn('logo'));
    }
};
