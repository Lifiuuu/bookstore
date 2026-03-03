<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'id_google')) {
                $table->string('id_google', 256)->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'otp')) {
                $table->string('otp', 6)->nullable()->after('id_google');
            }
            if (! Schema::hasColumn('users', 'email')) {
                $table->string('email')->unique()->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'otp')) {
                $table->dropColumn('otp');
            }
            if (Schema::hasColumn('users', 'id_google')) {
                $table->dropColumn('id_google');
            }
            // don't drop email to avoid data loss
        });
    }
};
