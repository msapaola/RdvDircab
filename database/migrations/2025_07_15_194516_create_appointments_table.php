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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // Informations du demandeur
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('subject');
            $table->text('message')->nullable();
            
            // Détails du rendez-vous
            $table->date('preferred_date');
            $table->time('preferred_time');
            $table->enum('priority', ['normal', 'urgent', 'official'])->default('normal');
            $table->enum('status', [
                'pending',      // En attente
                'accepted',     // Accepté
                'rejected',     // Refusé
                'canceled',     // Annulé par l'admin
                'canceled_by_requester', // Annulé par le demandeur
                'expired',      // Expiré
                'completed'     // Terminé
            ])->default('pending');
            
            // Sécurité et suivi
            $table->uuid('secure_token')->unique();
            $table->boolean('canceled_by_requester')->default(false);
            
            // Relations
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            
            // Pièces jointes (stockées en JSON)
            $table->json('attachments')->nullable();
            
            // Messages et notes
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Métadonnées
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Index pour les performances
            $table->index(['status', 'preferred_date']);
            $table->index(['priority', 'status']);
            $table->index(['email', 'created_at']);
            $table->index('secure_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
