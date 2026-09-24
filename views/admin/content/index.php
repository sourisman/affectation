<?php

declare(strict_types=1);

/**
 * Configuration du site : contenus éditoriaux, SEO et coordonnées publiques.
 *
 * @var array<string, array<string, array{label:string,rules:string}>> $groups
 * @var array<string, array<string, string>> $values
 */

$groupLabels = [
    'general'  => ['label' => 'Identité du site', 'icon' => 'sparkles', 'desc' => 'Ces informations alimentent l\'en-tête, le pied de page et les données structurées.'],
    'seo'      => ['label' => 'Référencement', 'icon' => 'search', 'desc' => 'Balises utilisées par les moteurs de recherche et les partages sur les réseaux sociaux.'],
    'contact'  => ['label' => 'Formulaire de contact', 'icon' => 'mail', 'desc' => 'Messages affichés aux visiteurs et comportement des réponses automatiques.'],
    'features' => ['label' => 'Options de la plateforme', 'icon' => 'settings', 'desc' => 'Bandeaux d\'information et fonctionnalités globales.'],
];

$longFields = ['seo_description', 'contact_notice', 'site_address', 'site_tagline'];
?>
<form data-ajax action="/admin/configuration" method="post" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">

    <div class="stack">
        <?php foreach ($groups as $group => $fields): ?>
            <?php $groupMeta = $groupLabels[$group] ?? ['label' => ucfirst($group), 'icon' => 'settings', 'desc' => '']; ?>
            <section class="panel">
                <header class="panel__head">
                    <div style="display:flex;align-items:center;gap:.8rem">
                        <span class="kpi__icon" aria-hidden="true"><?= icon($groupMeta['icon'], 16) ?></span>
                        <div>
                            <div class="panel__title"><?= e($groupMeta['label']) ?></div>
                            <div class="text-muted" style="font-size:.8rem"><?= e($groupMeta['desc']) ?></div>
                        </div>
                    </div>
                </header>

                <div class="panel__body">
                    <div class="form-grid-2">
                        <?php foreach ($fields as $key => $definition): ?>
                            <?php
                            $value = (string) ($values[$group][$key] ?? '');
                            $isToggle = str_ends_with($definition['rules'], 'in:0,1');
                            $isLong = in_array($key, $longFields, true);
                            ?>
                            <div class="field <?= ($isLong || $isToggle) ? 'field--full' : '' ?>">
                                <label class="field__label" for="<?= e($key) ?>"><?= e($definition['label']) ?></label>

                                <?php if ($isToggle): ?>
                                    <label class="checkbox" for="<?= e($key) ?>">
                                        <input type="checkbox" id="<?= e($key) ?>" name="<?= e($key) ?>" value="1"
                                               <?= $value === '1' ? 'checked' : '' ?>>
                                        <span>Activer <?= e(mb_strtolower($definition['label'])) ?></span>
                                    </label>
                                    <input type="hidden" name="<?= e($key) ?>_flag" value="1">
                                <?php elseif ($isLong): ?>
                                    <textarea class="textarea" id="<?= e($key) ?>" name="<?= e($key) ?>" rows="3"
                                              style="min-height:96px"><?= e($value) ?></textarea>
                                <?php else: ?>
                                    <input class="input" id="<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($value) ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <div class="form-foot" style="position:sticky;bottom:1rem;background:var(--bg-elevated);border:1px solid var(--line);border-radius:var(--radius-md);margin-top:1.4rem;padding:1rem 1.2rem">
        <span class="text-muted" style="font-size:.82rem;margin-right:auto;display:inline-flex;align-items:center;gap:.4rem">
            <?= icon('info', 14) ?>
            Les modifications sont appliquées immédiatement sur le site public.
        </span>
        <button class="btn btn--ghost" type="reset">Réinitialiser</button>
        <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Enregistrer la configuration</button>
    </div>
</form>
