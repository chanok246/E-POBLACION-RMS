<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();

            $table->string('document_type');
            $table->string('template_name');
            $table->string('file_path');
            $table->string('file_type', 20);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(
                ['document_type', 'is_active'],
                'document_templates_type_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
