<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expense_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        foreach (['Breakfast','Lunch','Dinner','Hotel','Snacks','Other Cost'] as $i => $name) {
            \Illuminate\Support\Facades\DB::table('expense_types')->insert([
                'name' => $name,
                'status' => 1,
                'sort_order' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::create('activity_travels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->string('vehicle')->nullable();
            $table->decimal('distance', 10, 2)->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('activity_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->foreignId('expense_type_id')->nullable()->constrained('expense_types')->nullOnDelete();
            $table->string('expense_type')->nullable(); // snapshot so old activity stays readable
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dateTime('activity_at')->nullable()->after('date');
            $table->foreignId('entered_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        $otherCostId = \Illuminate\Support\Facades\DB::table('expense_types')->where('name','Other Cost')->value('id');
        foreach (\Illuminate\Support\Facades\DB::table('activities')->orderBy('id')->cursor() as $activity) {
            if (!empty($activity->from_location) || !empty($activity->to_location) || !empty($activity->vehicle) || (float)($activity->ta ?? 0) > 0) {
                \Illuminate\Support\Facades\DB::table('activity_travels')->insert([
                    'activity_id'=>$activity->id, 'from_location'=>$activity->from_location, 'to_location'=>$activity->to_location,
                    'vehicle'=>$activity->vehicle, 'distance'=>(float)($activity->distance ?? 0), 'cost'=>(float)($activity->ta ?? 0),
                    'created_at'=>now(), 'updated_at'=>now(),
                ]);
            }
            if ((float)($activity->da ?? 0) > 0) {
                \Illuminate\Support\Facades\DB::table('activity_expenses')->insert([
                    'activity_id'=>$activity->id, 'expense_type_id'=>$otherCostId, 'expense_type'=>'Other Cost',
                    'amount'=>(float)$activity->da, 'note'=>'Migrated from legacy DA', 'created_at'=>now(), 'updated_at'=>now(),
                ]);
            }
        }

        \Illuminate\Support\Facades\DB::table('activities')
            ->whereNull('activity_at')
            ->update(['activity_at' => \Illuminate\Support\Facades\DB::raw('created_at')]);

        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedInteger('no_of_beds')->nullable()->after('name');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('existing_machine')->nullable()->after('subject');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('tax_enabled')->default(false)->after('calculate_tax');
            $table->decimal('tax_rate', 8, 2)->default(0)->after('tax_enabled');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('tax_enabled')->default(false)->after('status_stage_id');
            $table->decimal('tax_rate', 8, 2)->default(0)->after('tax_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('sales', fn (Blueprint $table) => $table->dropColumn(['tax_enabled','tax_rate']));
        Schema::table('quotations', fn (Blueprint $table) => $table->dropColumn(['tax_enabled','tax_rate']));
        Schema::table('leads', fn (Blueprint $table) => $table->dropColumn('existing_machine'));
        Schema::table('organizations', fn (Blueprint $table) => $table->dropColumn('no_of_beds'));
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entered_by');
            $table->dropColumn('activity_at');
        });
        Schema::dropIfExists('activity_expenses');
        Schema::dropIfExists('activity_travels');
        Schema::dropIfExists('expense_types');
    }
};
