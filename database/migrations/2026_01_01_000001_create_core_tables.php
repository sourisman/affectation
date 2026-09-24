<?php

declare(strict_types=1);

use App\Core\Schema;

/**
 * Socle : comptes, sécurité, configuration, messagerie, journal d'activité.
 */
return static function (Schema $schema): void {
    $schema->create('users', [
        $schema->id(),
        $schema->string('name', 120),
        $schema->string('email', 190),
        $schema->string('password', 255),
        $schema->string('role', 20, false, 'admin'),
        $schema->string('job_title', 120, true),
        $schema->string('avatar_path', 255, true),
        $schema->boolean('is_active', true),
        $schema->timestamp('last_login_at', true, false),
        $schema->timestamps(),
    ]);
    $schema->index('users', 'users_email_unique', ['email'], true);
    $schema->index('users', 'users_role_index', ['role']);

    $schema->create('password_resets', [
        $schema->string('email', 190),
        $schema->string('token', 255),
        $schema->timestamp('created_at', false, false),
    ]);
    $schema->index('password_resets', 'password_resets_email_index', ['email']);

    // Tentatives de connexion : détection de bruteforce par email + IP.
    $schema->create('login_attempts', [
        $schema->id(),
        $schema->string('email', 190),
        $schema->string('ip', 45),
        $schema->boolean('successful', false),
        $schema->timestamp('attempted_at', false, false),
    ]);
    $schema->index('login_attempts', 'login_attempts_email_ip_index', ['email', 'ip']);

    // Configuration éditable du site (clé/valeur), consommée par le front.
    $schema->create('settings', [
        $schema->string('setting_key', 100),
        $schema->text('setting_value', true),
        $schema->string('group_name', 50, false, 'general'),
        $schema->timestamps(),
        $schema->primaryKey('setting_key'),
    ]);

    $schema->create('contact_messages', [
        $schema->id(),
        $schema->string('name', 120),
        $schema->string('email', 190),
        $schema->string('phone', 40, true),
        $schema->string('subject', 160),
        $schema->text('message'),
        $schema->string('status', 20, false, 'new'),
        $schema->string('ip', 45, true),
        $schema->string('user_agent', 255, true),
        $schema->timestamp('read_at', true, false),
        $schema->timestamps(),
    ]);
    $schema->index('contact_messages', 'contact_messages_status_index', ['status']);
    $schema->index('contact_messages', 'contact_messages_created_index', ['created_at']);

    $schema->create('activity_logs', [
        $schema->id(),
        $schema->foreignId('user_id', true),
        $schema->string('action', 60),
        $schema->string('entity', 60, true),
        $schema->string('entity_id', 40, true),
        $schema->text('description', true),
        $schema->json('meta'),
        $schema->string('ip', 45, true),
        $schema->timestamp('created_at', false, false),
        $schema->foreignKey('user_id', 'users', 'id', 'SET NULL'),
    ]);
    $schema->index('activity_logs', 'activity_logs_entity_index', ['entity', 'entity_id']);
    $schema->index('activity_logs', 'activity_logs_created_index', ['created_at']);
};
