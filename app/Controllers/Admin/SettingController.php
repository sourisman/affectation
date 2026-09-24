<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Setting;
use App\Services\ActivityAware;

/**
 * Configuration du site : identité éditoriale, SEO, coordonnées publiques.
 * Les valeurs sont stockées en base (table settings) et lues par le front.
 */
final class SettingController extends Controller
{
    use ActivityAware;

    /** Champs éditables, groupés, avec leur type de validation. */
    private const FIELDS = [
        'general' => [
            'site_name'    => ['label' => 'Nom du site', 'rules' => 'required|max:60'],
            'site_tagline' => ['label' => 'Accroche', 'rules' => 'required|max:160'],
            'site_email'   => ['label' => 'Email public', 'rules' => 'required|email|max:190'],
            'site_phone'   => ['label' => 'Téléphone public', 'rules' => 'required|phone|max:40'],
            'site_address' => ['label' => 'Adresse', 'rules' => 'required|max:190'],
        ],
        'seo' => [
            'seo_title'       => ['label' => 'Titre SEO', 'rules' => 'required|max:70'],
            'seo_description' => ['label' => 'Meta description', 'rules' => 'required|max:180'],
            'seo_keywords'    => ['label' => 'Mots clés', 'rules' => 'nullable|max:190'],
        ],
        'contact' => [
            'contact_notice'      => ['label' => 'Message affiché sous le formulaire', 'rules' => 'nullable|max:280'],
            'contact_auto_reply'  => ['label' => 'Réponse automatique (activée)', 'rules' => 'nullable|in:0,1'],
        ],
        'features' => [
            'feature_maintenance' => ['label' => 'Bandeau maintenance', 'rules' => 'nullable|in:0,1'],
        ],
    ];

    public function index(Request $request): Response
    {
        $settings = new Setting();

        return $this->admin('admin.content.index', [
            'meta'      => ['title' => 'Configuration du site — AFFECTA', 'robots' => 'noindex'],
            'groups'    => self::FIELDS,
            'values'    => $settings->groups(),
            'pageTitle' => 'Configuration du site',
            'pageLead'  => 'Contenus éditoriaux, référencement et coordonnées publiques.',
        ]);
    }

    public function update(Request $request): Response
    {
        $settings = new Setting();
        $values = $request->all();
        $errors = [];

        foreach (self::FIELDS as $group => $fields) {
            foreach ($fields as $key => $definition) {
                $validator = $this->validate($request, [$key => $definition['rules']], [$key => $definition['label']]);

                if ($validator->fails()) {
                    $errors[$key] = $validator->errors()[$key] ?? [$validator->firstError()];

                    continue;
                }

                $settings->put($key, (string) ($values[$key] ?? ''), $group);
            }
        }

        if ($errors !== []) {
            return $this->respond($request, [
                'success' => false,
                'message' => 'Certains champs doivent être corrigés.',
                'errors'  => $errors,
            ], '/admin/configuration', $values);
        }

        $this->log('settings.update', 'settings', null, 'Mise à jour de la configuration du site');

        return $this->respond($request, [
            'success' => true,
            'message' => 'Configuration enregistrée.',
        ], '/admin/configuration');
    }
}
