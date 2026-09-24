<?php

declare(strict_types=1);

/**
 * Page d'accueil — composition narrative :
 * hero → constat → fonctionnalités → démonstration → chiffres → services
 * → technologies → méthode → témoignages → tarifs → FAQ → contact → CTA.
 *
 * @var list<array<string, mixed>> $features
 * @var list<array<string, mixed>> $stats
 * @var list<array<string, mixed>> $services
 * @var list<array<string,string>> $technologies
 * @var list<array<string,string>> $process
 * @var list<array<string, mixed>> $testimonials
 * @var list<array<string, mixed>> $plans
 * @var list<array{question:string,answer:string}> $faq
 */

require BASE_PATH . '/views/components/sections/hero.php';
require BASE_PATH . '/views/components/sections/problem.php';
require BASE_PATH . '/views/components/sections/features.php';
?>
<hr class="hairline container">
<?php
require BASE_PATH . '/views/components/sections/showcase.php';
require BASE_PATH . '/views/components/sections/stats.php';
require BASE_PATH . '/views/components/sections/services.php';
require BASE_PATH . '/views/components/sections/technologies.php';
require BASE_PATH . '/views/components/sections/process.php';
require BASE_PATH . '/views/components/sections/testimonials.php';
require BASE_PATH . '/views/components/sections/pricing.php';
require BASE_PATH . '/views/components/sections/faq.php';
require BASE_PATH . '/views/components/sections/contact.php';
