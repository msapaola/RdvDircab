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
        Schema::create('blocked_slots', function (Blueprint $table) {
            $table->id();
            
            // Informations du créneau bloqué
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            
            // Type de blocage
            $table->enum('type', [
                'manual',       // Blocage manuel par l'admin
                'lunch',        // Pause déjeuner automatique
                'holiday',      // Jour férié
                'maintenance',  // Maintenance
                'meeting',      // Réunion
                'other'         // Autre
            ])->default('manual');
            
            // Raison du blocage
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            
            // Relations
            $table->foreignId('blocked_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Récurrence (pour les blocages récurrents)
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly'])->nullable();
            $table->date('recurrence_end_date')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['date', 'start_time', 'end_time']);
            $table->index(['type', 'date']);
            $table->index('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_slots');
    }
};
