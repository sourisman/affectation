<?php

declare(strict_types=1);

/**
 * Contact : formulaire AJAX avec validation frontend et backend,
 * protection CSRF, pot de miel anti-robot et horodatage anti-spam.
 */

$company = company_profile();
$channels = [
    ['icon' => 'mail', 'label' => 'Email', 'value' => (string) ($company['email'] ?? ''), 'href' => 'mailto:' . ($company['email'] ?? '')],
    ['icon' => 'phone', 'label' => 'Téléphone', 'value' => (string) ($company['phone'] ?? ''), 'href' => 'tel:' . preg_replace('/\s+/', '', (string) ($company['phone'] ?? ''))],
    ['icon' => 'map-pin', 'label' => 'Adresse', 'value' => (string) ($company['address'] ?? ''), 'href' => null],
];
?>
<section class="section" id="contact">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Contact</span>
            <h2>Parlons de votre projet.</h2>
            <p>
                Décrivez votre contexte : nous revenons vers vous sous 24 heures ouvrées avec une
                première lecture et, si le besoin est confirmé, un créneau de démonstration.
            </p>
        </div>

        <div class="contact">
            <aside class="contact__aside" data-reveal="left">
                <?php foreach ($channels as $channel): ?>
                    <?php if ($channel['href'] !== null): ?>
                        <a class="contact__channel" href="<?= e($channel['href']) ?>">
                            <?= icon($channel['icon'], 20) ?>
                            <div>
                                <small><?= e($channel['label']) ?></small>
                                <strong><?= e($channel['value']) ?></strong>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="contact__channel">
                            <?= icon($channel['icon'], 20) ?>
                            <div>
                                <small><?= e($channel['label']) ?></small>
                                <strong><?= e($channel['value']) ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="card">
                    <h3 style="font-size:1.05rem">Délai de réponse</h3>
                    <div class="kpi-line" style="margin-top:.8rem"><span>Demande commerciale</span><b>&lt; 24 h</b></div>
                    <div class="kpi-line"><span>Support technique</span><b>&lt; 4 h</b></div>
                    <div class="kpi-line"><span>Astreinte Enterprise</span><b>8 h / 7 j</b></div>
                </div>
            </aside>

            <div class="form-card" data-reveal="right">
                <form data-ajax action="/api/contact" method="post" novalidate data-ajax-reload="false">
                    <?= csrf_field() ?>

                    <!-- Pot de miel : invisible pour les humains, rempli par les robots -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Ne pas remplir ce champ</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <input type="hidden" name="form_started_at" value="0">

                    <div class="form-grid">
                        <div class="field">
                            <label class="field__label" for="name">Nom complet <span class="req" aria-hidden="true">*</span></label>
                            <input class="input" type="text" id="name" name="name" required minlength="2" autocomplete="name"
                                   placeholder="Ex. Miora Rasoanaivo" value="<?= e((string) old('name')) ?>">
                        </div>

                        <div class="field">
                            <label class="field__label" for="email">Email professionnel <span class="req" aria-hidden="true">*</span></label>
                            <input class="input" type="email" id="email" name="email" required autocomplete="email"
                                   placeholder="prenom.nom@entreprise.mg" value="<?= e((string) old('email')) ?>">
                        </div>

                        <div class="field">
                            <label class="field__label" for="phone">Téléphone</label>
                            <input class="input" type="tel" id="phone" name="phone" autocomplete="tel"
                                   placeholder="+261 34 00 000 00" value="<?= e((string) old('phone')) ?>">
                        </div>

                        <div class="field">
                            <label class="field__label" for="subject">Sujet <span class="req" aria-hidden="true">*</span></label>
                            <select class="select" id="subject" name="subject" required>
                                <option value="">Choisir un sujet…</option>
                                <option value="Demande de démonstration">Demande de démonstration</option>
                                <option value="Devis et tarification">Devis et tarification</option>
                                <option value="Refonte du portail interne">Refonte du portail interne</option>
                                <option value="Interfaçage paie / SIRH">Interfaçage paie / SIRH</option>
                                <option value="Sécurité et données personnelles">Sécurité et données personnelles</option>
                                <option value="Support technique">Support technique</option>
                                <option value="Autre demande">Autre demande</option>
                            </select>
                        </div>

                        <div class="field field--full">
                            <label class="field__label" for="message">Votre message <span class="req" aria-hidden="true">*</span></label>
                            <textarea class="textarea" id="message" name="message" required minlength="20" maxlength="4000"
                                      placeholder="Nombre d'agents concernés, sites, contraintes actuelles, échéance souhaitée…"><?= e((string) old('message')) ?></textarea>
                            <span class="field__hint">20 caractères minimum. Plus vous êtes précis, plus notre réponse sera utile.</span>
                        </div>

                        <div class="field field--full">
                            <label class="checkbox" for="consent">
                                <input type="checkbox" id="consent" name="consent" value="1" required>
                                <span>
                                    J'accepte d'être recontacté au sujet de ma demande. Les données transmises
                                    ne sont utilisées que pour y répondre et sont conservées 24 mois maximum.
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="form-card__foot">
                        <span class="text-muted" style="font-size:.8rem">
                            <?= icon('lock', 14) ?> Connexion chiffrée · Aucun transfert à des tiers
                        </span>

                        <button class="btn btn--primary" type="submit">
                            <?= icon('arrow-right', 18) ?>
                            Envoyer la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section section--flush-top" id="demarrer">
    <div class="container">
        <div class="cta-final" data-reveal="scale">
            <span class="eyebrow eyebrow--boxed" style="margin-inline:auto">Prêt à démarrer</span>
            <h2 style="margin-top:1.2rem">Reprenez la main sur vos effectifs.</h2>
            <p>
                Déploiement en trois semaines, reprise d'historique incluse et formation de vos équipes.
                Vous gardez la propriété complète de vos données.
            </p>

            <div class="cluster">
                <a class="btn btn--primary btn--lg btn--magnetic" href="#contact">
                    Commencer maintenant
                    <?= icon('arrow-right', 18) ?>
                </a>
                <a class="btn btn--outline btn--lg" href="/login">
                    <?= icon('key', 18) ?>
                    Accéder à la console
                </a>
            </div>
        </div>
    </div>
</section>
