<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blog_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->foreignId('spotlight_model_id')->nullable()->constrained('models')->nullOnDelete();
            $table->string('report_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('content')->nullable();
            $table->string('read_time')->default('8 دقائق قراءة');
            $table->string('author_name')->default('فريق أبحاث إدراك Lab');
            $table->string('author_role')->default('مختبر اقتصاديات النماذج العربية');
            $table->string('key_metric_value')->nullable();
            $table->string('key_metric_label')->nullable();
            $table->json('hero_metrics')->nullable();
            $table->json('takeaways')->nullable();
            $table->json('sections')->nullable();
            $table->json('comparison_rows')->nullable();
            $table->json('blocks')->nullable();
            $table->text('code_snippet')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_articles');
    }
};
