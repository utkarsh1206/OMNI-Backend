<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ecdial_list_field_dependencies')) {
            return;
        }

        if (Schema::hasColumn('ecdial_list_field_dependencies', 'list_id')) {
            return;
        }

        Schema::table('ecdial_list_field_dependencies', function (Blueprint $table) {
            $table->unsignedBigInteger('list_id')->nullable()->after('id');
            $table->string('child_field_name', 255)->nullable()->after('list_id');
            $table->string('parent_field_name', 255)->nullable()->after('child_field_name');
        });

        DB::statement("\n            UPDATE ecdial_list_field_dependencies d\n            JOIN ecdial_lists_fields c ON c.id = d.child_field_id\n            SET d.list_id = c.list_id, d.child_field_name = c.field_name\n        ");

        DB::statement("\n            UPDATE ecdial_list_field_dependencies d\n            LEFT JOIN ecdial_lists_fields p ON p.id = d.parent_field_id\n            SET d.parent_field_name = p.field_name\n        ");

        DB::statement('ALTER TABLE ecdial_list_field_dependencies MODIFY list_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE ecdial_list_field_dependencies MODIFY child_field_name VARCHAR(255) NOT NULL');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        Schema::table('ecdial_list_field_dependencies', function (Blueprint $table) {
            $table->dropUnique('uniq_field_dependency_param');
            $table->dropIndex('idx_field_dependency_order');
            $table->dropForeign(['child_field_id']);
            $table->dropForeign(['parent_field_id']);
            $table->dropColumn(['child_field_id', 'parent_field_id']);

            $table->unique(['list_id', 'child_field_name', 'param_name', 'param_type'], 'uniq_field_dependency_name_param');
            $table->index(['list_id', 'child_field_name', 'sort_order'], 'idx_field_dependency_name_order');
            $table->index(['list_id', 'parent_field_name'], 'idx_field_dependency_parent_name');
        });
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecdial_list_field_dependencies', function (Blueprint $table) {
            $table->unsignedBigInteger('child_field_id')->nullable()->after('id');
            $table->unsignedBigInteger('parent_field_id')->nullable()->after('child_field_id');
        });

        DB::statement("\n            UPDATE ecdial_list_field_dependencies d\n            JOIN ecdial_lists_fields c\n                ON c.list_id = d.list_id\n               AND c.field_name = d.child_field_name\n            SET d.child_field_id = c.id\n        ");

        DB::statement("\n            UPDATE ecdial_list_field_dependencies d\n            LEFT JOIN ecdial_lists_fields p\n                ON p.list_id = d.list_id\n               AND p.field_name = d.parent_field_name\n            SET d.parent_field_id = p.id\n        ");

        DB::statement('ALTER TABLE ecdial_list_field_dependencies MODIFY child_field_id BIGINT UNSIGNED NOT NULL');

        Schema::table('ecdial_list_field_dependencies', function (Blueprint $table) {
            $table->dropUnique('uniq_field_dependency_name_param');
            $table->dropIndex('idx_field_dependency_name_order');
            $table->dropIndex('idx_field_dependency_parent_name');

            $table->foreign('child_field_id')
                ->references('id')
                ->on('ecdial_lists_fields')
                ->cascadeOnDelete();

            $table->foreign('parent_field_id')
                ->references('id')
                ->on('ecdial_lists_fields')
                ->nullOnDelete();

            $table->unique(['child_field_id', 'param_name', 'param_type'], 'uniq_field_dependency_param');
            $table->index(['child_field_id', 'sort_order'], 'idx_field_dependency_order');

            $table->dropColumn(['list_id', 'child_field_name', 'parent_field_name']);
        });
    }
};
