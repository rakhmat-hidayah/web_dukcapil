<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds username + enterprise security columns to users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Username as primary login credential
            $table->string('username', 30)->unique()->nullable()->after('name');
            $table->index('username');

            // Account status
            $table->boolean('is_active')->default(true)->after('username');

            // Security audit fields
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->unsignedTinyInteger('failed_login_count')->default(0)->after('last_login_ip');
            $table->timestamp('locked_until')->nullable()->after('failed_login_count');
        });

        // Back-fill username from email for existing users
        // e.g. admin@dompukab.go.id → admin
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $base = strtolower(preg_replace('/[^a-z0-9_]/', '_', explode('@', $user->email)[0]));
            // Ensure min 4 chars
            if (strlen($base) < 4) {
                $base = str_pad($base, 4, '0', STR_PAD_RIGHT);
            }
            // Ensure max 30 chars
            $base = substr($base, 0, 30);

            // Ensure uniqueness: append _N if taken
            $username = $base;
            $counter  = 1;
            while (DB::table('users')->where('username', $username)->where('id', '<>', $user->id)->exists()) {
                $suffix   = '_' . $counter++;
                $username = substr($base, 0, 30 - strlen($suffix)) . $suffix;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        // Make username NOT NULL after back-fill
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropColumn([
                'username',
                'is_active',
                'last_login_at',
                'last_login_ip',
                'failed_login_count',
                'locked_until',
            ]);
        });
    }
};
