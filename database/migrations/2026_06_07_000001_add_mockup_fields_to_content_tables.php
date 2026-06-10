<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('category')->default('umum')->index()->after('slug');
            $table->text('summary')->nullable()->after('category');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('category')->default('edukasi')->index()->after('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->default('produk_gigi')->index()->after('slug');
            $table->json('benefits')->nullable()->after('description');
            $table->text('doctor_tips')->nullable()->after('usage_instructions');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->string('tag')->nullable()->after('subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['category', 'summary']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['category', 'benefits', 'doctor_tips']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'tag']);
        });
    }
};
