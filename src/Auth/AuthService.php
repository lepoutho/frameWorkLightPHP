<?php

/**
 * Gère l'inscription, la connexion, la déconnexion et l'état de session
 * d'un utilisateur (table `users`).
 *
 * Sécurité :
 * - mot de passe jamais stocké en clair (password_hash / password_verify)
 * - messages d'erreur volontairement génériques à la connexion
 *   (ne pas révéler si c'est l'email ou le mot de passe qui est faux)
 * - regénération de l'identifiant de session à la connexion
 *   (protection contre la fixation de session)
 */
class AuthService
{
    /** @var Database */
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Inscrit un nouvel utilisateur.
     * @return array{success: bool, error?: string, user?: array}
     */
    public function register(string $email, string $password): array
    {
        $email = trim($email);

        if ($email === '' || $password === '') {
            return ['success' => false, 'error' => "Email et mot de passe sont requis"];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => "Format d'email invalide"];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'error' => "Le mot de passe doit contenir au moins 8 caractères"];
        }

        $existing = $this->db->fetchOne('SELECT id FROM users WHERE email = :email', ['email' => $email]);
        if ($existing !== null) {
            return ['success' => false, 'error' => "Un compte existe déjà avec cet email"];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->db->run(
            'INSERT INTO users (email, password_hash) VALUES (:email, :hash)',
            ['email' => $email, 'hash' => $hash]
        );

        return [
            'success' => true,
            'user' => ['id' => (int) $this->db->lastInsertId(), 'email' => $email],
        ];
    }

    /**
     * Vérifie les identifiants et ouvre la session si valides.
     * @return array{success: bool, error?: string, user?: array}
     */
    public function login(string $email, string $password): array
    {
        $row = $this->db->fetchOne(
            'SELECT id, email, password_hash FROM users WHERE email = :email',
            ['email' => trim($email)]
        );

        // Message volontairement identique que l'email existe ou non,
        // pour ne pas laisser un attaquant deviner les emails inscrits.
        if ($row === null || !password_verify($password, $row['password_hash'])) {
            return ['success' => false, 'error' => "Email ou mot de passe incorrect"];
        }

        // Régénère l'id de session : évite qu'un identifiant de session
        // connu avant connexion reste valide après (session fixation).
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $row['id'];
        $_SESSION['email']   = $row['email'];

        return ['success' => true, 'user' => ['id' => (int) $row['id'], 'email' => $row['email']]];
    }

    public function logout(): array
    {
        $_SESSION = [];
        session_destroy();

        return ['success' => true, 'message' => 'Déconnecté'];
    }

    /**
     * Retourne l'utilisateur courant si une session valide existe, sinon null.
     */
    public function currentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return ['id' => $_SESSION['user_id'], 'email' => $_SESSION['email']];
    }
}
