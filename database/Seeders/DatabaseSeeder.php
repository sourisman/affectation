<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Env;
use App\Core\Schema;
use PDO;

/**
 * Jeu de démonstration complet et cohérent : utilisateurs, lieux, employés,
 * affectations, messages de contact et journal d'activité.
 */
final class DatabaseSeeder
{
    /** @var array<string, int> */
    private array $counts = [];

    /** @var list<array{role:string,email:string,password:string}> */
    private array $accounts = [];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array{skipped:bool,counts:array<string,int>,accounts:list<array{role:string,email:string,password:string}>} */
    public function run(bool $force = false): array
    {
        $existing = (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

        if ($existing > 0 && !$force) {
            return ['skipped' => true, 'counts' => [], 'accounts' => []];
        }

        if ($force) {
            $this->purge();
        }

        $this->seedUsers();
        $this->seedSettings();
        $this->seedLieux();
        $this->seedEmployes();
        $this->seedAffectations();
        $this->seedContactMessages();
        $this->seedActivity();

        return ['skipped' => false, 'counts' => $this->counts, 'accounts' => $this->accounts];
    }

    private function purge(): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->pdo->exec('PRAGMA foreign_keys = OFF');
        } else {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        }

        foreach (['activity_logs', 'contact_messages', 'affectations', 'employes', 'lieux', 'users', 'settings', 'login_attempts', 'password_resets'] as $table) {
            $this->pdo->exec("DELETE FROM {$table}");
        }

        if ($driver === 'sqlite') {
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        // Réarme aussi les compteurs de limitation de débit (stockage fichier).
        foreach (glob(BASE_PATH . '/storage/cache/throttle/*.json') ?: [] as $file) {
            @unlink($file);
        }
    }

    private function seedUsers(): void
    {
        $adminEmail = (string) Env::get('SEED_ADMIN_EMAIL', 'admin@affecta.dev');
        $adminPassword = (string) Env::get('SEED_ADMIN_PASSWORD', '') ?: 'Admin@2026';
        $managerEmail = 'manager@affecta.dev';
        $managerPassword = 'Manager@2026';

        $users = [
            ['name' => 'Nadia Rakoto', 'email' => $adminEmail, 'password' => $adminPassword, 'role' => 'admin', 'job_title' => 'Directrice des opérations'],
            ['name' => 'Hery Andrianina', 'email' => $managerEmail, 'password' => $managerPassword, 'role' => 'manager', 'job_title' => 'Responsable mobilité interne'],
        ];

        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, role, job_title, is_active, created_at, updated_at)
             VALUES (:name, :email, :password, :role, :job_title, 1, :now, :now)',
        );

        $now = date('Y-m-d H:i:s');

        foreach ($users as $user) {
            $statement->execute([
                'name'      => $user['name'],
                'email'     => mb_strtolower($user['email']),
                'password'  => password_hash($user['password'], PASSWORD_DEFAULT),
                'role'      => $user['role'],
                'job_title' => $user['job_title'],
                'now'       => $now,
            ]);

            $this->accounts[] = [
                'role'     => $user['role'],
                'email'    => mb_strtolower($user['email']),
                'password' => $user['password'],
            ];
        }

        $this->counts['users'] = count($users);
    }

    /** Configuration éditable du site (/admin/contenus) : clés alignées sur SettingController::FIELDS. */
    private function seedSettings(): void
    {
        $settings = [
            ['site_name', 'AFFECTA', 'general'],
            ['site_tagline', 'Construisons le futur du digital.', 'general'],
            ['site_email', 'contact@affecta.dev', 'general'],
            ['site_phone', '+261 34 12 345 67', 'general'],
            ['site_address', 'Immeuble Horizon, Ankorondrano, Antananarivo 101', 'general'],
            ['seo_title', 'AFFECTA — Plateforme de pilotage des affectations', 'seo'],
            ['seo_description', 'Centralisez les agents, les lieux et les affectations : décisions rapides, données fiables, mobilité interne maîtrisée.', 'seo'],
            ['seo_keywords', 'affectations, mobilité interne, gestion des effectifs, Madagascar', 'seo'],
            ['contact_notice', 'Vos données ne sont utilisées que pour répondre à votre demande.', 'contact'],
            ['contact_auto_reply', '1', 'contact'],
            ['feature_maintenance', '0', 'features'],
        ];

        $statement = $this->pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value, group_name, created_at, updated_at)
             VALUES (:key, :value, :group, :now, :now)',
        );

        $now = date('Y-m-d H:i:s');

        foreach ($settings as [$key, $value, $group]) {
            $statement->execute(['key' => $key, 'value' => $value, 'group' => $group, 'now' => $now]);
        }

        $this->counts['settings'] = count($settings);
    }

    private function seedLieux(): void
    {
        $lieux = [
            ['idlieu' => 'LIEU-001', 'design' => 'Siège Antananarivo', 'province' => 'Antananarivo', 'capacite' => 120, 'code' => 'TNR-HQ'],
            ['idlieu' => 'LIEU-002', 'design' => 'Antenne Toamasina', 'province' => 'Toamasina', 'capacite' => 45, 'code' => 'TMM-01'],
            ['idlieu' => 'LIEU-003', 'design' => 'Antenne Mahajanga', 'province' => 'Mahajanga', 'capacite' => 38, 'code' => 'MJG-01'],
            ['idlieu' => 'LIEU-004', 'design' => 'Antenne Fianarantsoa', 'province' => 'Fianarantsoa', 'capacite' => 32, 'code' => 'FIA-01'],
            ['idlieu' => 'LIEU-005', 'design' => 'Antenne Toliara', 'province' => 'Toliara', 'capacite' => 25, 'code' => 'TLE-01'],
            ['idlieu' => 'LIEU-006', 'design' => 'Centre Antsirabe', 'province' => 'Antananarivo', 'capacite' => 28, 'code' => 'ATB-01'],
            ['idlieu' => 'LIEU-007', 'design' => 'Hub Antsiranana', 'province' => 'Antsiranana', 'capacite' => 22, 'code' => 'DIE-01'],
        ];

        $statement = $this->pdo->prepare(
            'INSERT INTO lieux (idlieu, design, province, code_analytique, capacite, is_active, created_at, updated_at)
             VALUES (:idlieu, :design, :province, :code, :capacite, 1, :now, :now)',
        );

        $now = date('Y-m-d H:i:s');

        foreach ($lieux as $lieu) {
            $statement->execute([
                'idlieu'   => $lieu['idlieu'],
                'design'   => $lieu['design'],
                'province' => $lieu['province'],
                'code'     => $lieu['code'],
                'capacite' => $lieu['capacite'],
                'now'      => $now,
            ]);
        }

        $this->counts['lieux'] = count($lieux);
    }

    private function seedEmployes(): void
    {
        $prenoms = ['Andry', 'Miora', 'Tahina', 'Fanja', 'Rija', 'Lalaina', 'Sitraka', 'Hasina', 'Fenosoa', 'Toky',
            'Nomena', 'Aina', 'Vatosoa', 'Herinjaka', 'Rasoa', 'Mahefa', 'Tsiory', 'Zo', 'Lova', 'Njaka',
            'Fara', 'Sanda', 'Hery', 'Ony', 'Mickaël', 'Volana', 'Rado', 'Soa', 'Tantely', 'Vaovao'];
        $noms = ['Rakoto', 'Rasoa', 'Andriamana', 'Razafy', 'Ravaka', 'Randrianasolo', 'Rakotondrabe', 'Ramanana',
            'Raharison', 'Rasoanaivo', 'Andriatsitohaina', 'Ratsimba', 'Rabemananjara', 'Razanamalala', 'Rakotovao'];
        $postes = ['Ingénieur logiciel', 'Analyste financier', 'Chargé de clientèle', 'Technicien réseau',
            'Responsable logistique', 'Comptable', 'Chef de projet', 'Data analyst', 'Agent administratif',
            'Responsable RH', 'Développeur web', 'Contrôleur qualité', 'Assistant de direction', 'Auditeur interne'];
        $lieux = ['LIEU-001', 'LIEU-002', 'LIEU-003', 'LIEU-004', 'LIEU-005', 'LIEU-006', 'LIEU-007'];

        $statement = $this->pdo->prepare(
            'INSERT INTO employes (numEmp, civilite, nom, prenom, mail, telephone, poste, lieu, date_embauche, is_active, created_at, updated_at)
             VALUES (:numEmp, :civilite, :nom, :prenom, :mail, :telephone, :poste, :lieu, :embauche, :actif, :now, :now)',
        );

        $now = date('Y-m-d H:i:s');
        $count = 42;
        mt_srand(20260924); // génération reproductible

        for ($i = 1; $i <= $count; $i++) {
            $civilite = $i % 3 === 0 ? 'Mme' : ($i % 7 === 0 ? 'Mlle' : 'M.');
            $prenom = $prenoms[array_rand($prenoms)];
            $nom = $noms[array_rand($noms)];
            $poste = $postes[array_rand($postes)];
            $lieu = $lieux[array_rand($lieux)];

            $statement->execute([
                'numEmp'     => sprintf('EMP-%04d', $i),
                'civilite'   => $civilite,
                'nom'        => $nom,
                'prenom'     => $prenom,
                'mail'       => mb_strtolower($prenom . '.' . $nom . $i . '@affecta.dev'),
                'telephone'  => '+261 3' . random_int(2, 4) . ' ' . random_int(10, 99) . ' ' . random_int(100, 999) . ' ' . random_int(10, 99),
                'poste'      => $poste,
                'lieu'       => $lieu,
                'embauche'   => date('Y-m-d', strtotime('-' . random_int(60, 3200) . ' days')),
                'actif'      => $i % 19 === 0 ? 0 : 1,
                'now'        => $now,
            ]);
        }

        $this->counts['employes'] = $count;
    }

    private function seedAffectations(): void
    {
        $employes = $this->pdo->query('SELECT numEmp, lieu FROM employes ORDER BY numEmp')->fetchAll(PDO::FETCH_ASSOC);
        $lieux = array_column($this->pdo->query('SELECT idlieu FROM lieux')->fetchAll(PDO::FETCH_ASSOC), 'idlieu');
        $motifs = ['mutation', 'promotion', 'besoin_service', 'demande_agent', 'reorganisation', 'affectation_initiale'];

        $statement = $this->pdo->prepare(
            'INSERT INTO affectations (numAffect, numEmp, ancienLieu, nouveauLieu, dateAffect, datePriseService,
                                       motif, observation, statut, created_by, created_at, updated_at)
             VALUES (:numAffect, :numEmp, :ancien, :nouveau, :dateAffect, :datePrise, :motif, :observation, :statut, 1, :now, :now)',
        );

        $now = date('Y-m-d H:i:s');
        $count = 0;
        $sequence = 1;

        foreach ($employes as $index => $employe) {
            // ~2/3 des employés ont un historique d'affectation.
            if ($index % 3 === 2) {
                continue;
            }

            $dateAffect = date('Y-m-d', strtotime('-' . random_int(15, 700) . ' days'));
            $lieuOrigine = (string) $employe['lieu'];
            $candidats = array_values(array_filter($lieux, static fn (string $lieu): bool => $lieu !== $lieuOrigine));
            $nouveau = $candidats[array_rand($candidats)];

            // Les affectations passées sont appliquées, les récentes restent planifiées.
            $statut = strtotime($dateAffect) < strtotime('-45 days') ? 'applique' : 'planifie';

            if ($statut === 'annule' || ($count > 0 && $count % 11 === 0)) {
                $statut = 'annule';
            }

            $statement->execute([
                'numAffect'   => sprintf('AFF-%s-%04d', substr($dateAffect, 0, 4), $sequence),
                'numEmp'      => $employe['numEmp'],
                'ancien'      => $lieuOrigine,
                'nouveau'     => $nouveau,
                'dateAffect'  => $dateAffect,
                'datePrise'   => date('Y-m-d', strtotime($dateAffect . ' +' . random_int(10, 45) . ' days')),
                'motif'       => $motifs[array_rand($motifs)],
                'observation' => $count % 4 === 0 ? 'Dossier validé par la direction des ressources humaines.' : null,
                'statut'      => $statut,
                'now'         => $now,
            ]);

            // L'employé suit son affectation appliquée la plus récente.
            if ($statut === 'applique') {
                $this->pdo->prepare('UPDATE employes SET lieu = :lieu WHERE numEmp = :numEmp')
                    ->execute(['lieu' => $nouveau, 'numEmp' => $employe['numEmp']]);
            }

            $sequence++;
            $count++;
        }

        $this->counts['affectations'] = $count;
    }

    private function seedContactMessages(): void
    {
        $messages = [
            ['Miarisoa Ravelo', 'miarisoa.ravelo@example.mg', 'Refonte du portail interne', "Bonjour,\nNous souhaitons moderniser notre portail RH interne. Pouvez-vous nous transmettre une estimation pour 250 collaborateurs ?"],
            ['Nicolas Perrin', 'n.perrin@example.fr', 'Démonstration plateforme', "Bonjour,\nSeriez-vous disponible pour une démonstration la semaine prochaine ? Nous sommes un groupe de 4 filiales."],
            ['Hanta Ratsima', 'hanta.ratsima@example.mg', 'Interfaçage paie', "Bonsoir,\nEst-il possible de connecter la plateforme à notre logiciel de paie existant ? Merci."],
            ['David Osei', 'd.osei@example.com', 'Sécurité des données', "Hello,\nQuelles certifications couvrez-vous concernant la protection des données personnelles ?"],
            ['Lucas Andry', 'lucas.andry@example.mg', 'Devis maintenance annuelle', "Bonjour,\nPouvez-vous nous envoyer un devis pour un contrat de maintenance annuelle avec SLA 8h ?"],
        ];

        $statement = $this->pdo->prepare(
            'INSERT INTO contact_messages (name, email, phone, subject, message, status, ip, user_agent, created_at, updated_at)
             VALUES (:name, :email, :phone, :subject, :message, :status, :ip, :agent, :created, :created)',
        );

        foreach ($messages as $index => $message) {
            $createdAt = date('Y-m-d H:i:s', strtotime('-' . (($index + 1) * 31) . ' hours'));

            $statement->execute([
                'name'    => $message[0],
                'email'   => $message[1],
                'phone'   => '+261 34 12 345 6' . $index,
                'subject' => $message[2],
                'message' => $message[3],
                'status'  => $index < 2 ? 'new' : ($index < 4 ? 'read' : 'answered'),
                'ip'      => '196.192.0.' . (10 + $index),
                'agent'   => 'Seeder/1.0 (démonstration)',
                'created' => $createdAt,
            ]);
        }

        $this->counts['contact_messages'] = count($messages);
    }

    private function seedActivity(): void
    {
        $actions = [
            ['affectation.create', 'affectation', 'Nouvelle affectation enregistrée'],
            ['employe.create', 'employe', 'Création d\'une fiche employé'],
            ['lieu.update', 'lieu', 'Mise à jour d\'un lieu d\'affectation'],
            ['auth.login', 'user', 'Connexion réussie au tableau de bord'],
            ['affectation.apply', 'affectation', 'Affectation appliquée'],
            ['auth.login', 'user', 'Connexion réussie au tableau de bord'],
        ];

        $statement = $this->pdo->prepare(
            'INSERT INTO activity_logs (user_id, action, entity, entity_id, description, ip, created_at)
             VALUES (:user_id, :action, :entity, :entity_id, :description, :ip, :created)',
        );

        $count = 0;
        mt_srand(777);

        for ($day = 13; $day >= 0; $day--) {
            $perDay = random_int(1, 5);

            for ($i = 0; $i < $perDay; $i++) {
                $action = $actions[array_rand($actions)];

                $statement->execute([
                    'user_id'     => random_int(1, 2),
                    'action'      => $action[0],
                    'entity'      => $action[1],
                    'entity_id'   => (string) random_int(1, 40),
                    'description' => $action[2],
                    'ip'          => '196.192.0.' . random_int(2, 90),
                    'created'     => date('Y-m-d H:i:s', strtotime("-{$day} days +" . random_int(7, 18) . ' hours')),
                ]);
                $count++;
            }
        }

        $this->counts['activity_logs'] = $count;
    }
}
