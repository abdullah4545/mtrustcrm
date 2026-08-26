<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Existing users may have no phone. Give them a temporary unique value so the
        // NOT NULL/UNIQUE migration can run; admins can replace it from User Edit.
        DB::table('users')->where(function ($q) {
            $q->whereNull('phone')->orWhere('phone', '');
        })->orderBy('id')->get(['id'])->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['phone' => 'legacy-'.$user->id]);
        });

        // Normalize any old duplicate phone values so adding the unique index is safe.
        $duplicates = DB::table('users')->select('phone')->groupBy('phone')->havingRaw('COUNT(*) > 1')->pluck('phone');
        foreach ($duplicates as $phone) {
            $ids = DB::table('users')->where('phone', $phone)->orderBy('id')->pluck('id');
            foreach ($ids->slice(1) as $id) {
                DB::table('users')->where('id', $id)->update(['phone' => 'legacy-'.$id]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone', 30)->nullable(false)->change();
        });

        // Add a unique index only if it does not already exist.
        $indexes = collect(DB::select("SHOW INDEX FROM users WHERE Column_name = 'phone'"));
        if (!$indexes->contains(fn($i) => (int)$i->Non_unique === 0)) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('phone', 'users_phone_unique');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_phone_unique'"));
        if ($indexes->isNotEmpty()) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_phone_unique');
            });
        }
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->change();
            $table->string('email')->nullable(false)->change();
        });
    }
};
