<?php
require_once __DIR__ . '/../Model/Book.php';

if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
}

class BookController
{
    private const SESSION_KEY = 'marathons_catalog';
    private const USERS_KEY = 'marathons_users';
    private const AUTH_KEY = 'marathons_auth';
    private const PARTICIPATIONS_KEY = 'marathons_participations';
    private const CART_KEY = 'marathons_carts';
    private const DRAFT_SPONSORS_KEY = 'marathons_draft_sponsors';
    private const ORDERS_KEY = 'marathons_orders';
    private const ORDER_ISSUES_KEY = 'marathons_order_issues';

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION[self::SESSION_KEY] ??= $this->getDefaultMarathons();
        $_SESSION[self::USERS_KEY] ??= $this->getDefaultUsers();
        $_SESSION[self::AUTH_KEY] ??= [
            'username' => null,
            'role' => 'visiteur',
            'name' => 'Visiteur',
        ];
        $_SESSION[self::PARTICIPATIONS_KEY] ??= [];
        $_SESSION[self::CART_KEY] ??= [];
        $_SESSION[self::DRAFT_SPONSORS_KEY] ??= [];
        $_SESSION[self::ORDERS_KEY] ??= [];
        $_SESSION[self::ORDER_ISSUES_KEY] ??= [];
    }

    public function showBook(Book $book): string
    {
        return '<table border="1" cellpadding="8" cellspacing="0">'
            . '<tr><th>ID</th><td>' . htmlspecialchars((string) $book->getID()) . '</td></tr>'
            . '<tr><th>Marathon</th><td>' . htmlspecialchars((string) $book->getName()) . '</td></tr>'
            . '<tr><th>Ville</th><td>' . htmlspecialchars((string) $book->getLocation()) . '</td></tr>'
            . '<tr><th>Date</th><td>' . htmlspecialchars((string) $book->getEventDate()) . '</td></tr>'
            . '<tr><th>Distance</th><td>' . htmlspecialchars((string) $book->getDistance()) . '</td></tr>'
            . '<tr><th>Statut</th><td>' . htmlspecialchars((string) $book->getStatus()) . '</td></tr>'
            . '<tr><th>Places</th><td>' . htmlspecialchars((string) $book->getSlots()) . '</td></tr>'
            . '<tr><th>Type</th><td>' . htmlspecialchars((string) $book->getCategory()) . '</td></tr>'
            . '<tr><th>Organisateur</th><td>' . htmlspecialchars((string) $book->getOrganizer()) . '</td></tr>'
            . '<tr><th>Prix</th><td>' . htmlspecialchars(number_format((float) $book->getPrice(), 2)) . ' TND</td></tr>'
            . '</table>';
    }

    public function listBooks(): array
    {
        return array_values($_SESSION[self::SESSION_KEY]);
    }

    public function listVisibleBooks(): array
    {
        return array_values(array_filter(
            $_SESSION[self::SESSION_KEY],
            static fn(array $marathon): bool => (bool) ($marathon['visible'] ?? true)
        ));
    }

    public function addBook(Book $book): bool
    {
        if (!$this->canManageMarathons()) {
            return false;
        }

        $marathons = $_SESSION[self::SESSION_KEY];
        $nextId = empty($marathons) ? 1 : (max(array_column($marathons, 'id')) + 1);
        $marathons[] = $this->bookToRow($book, $nextId);
        $_SESSION[self::SESSION_KEY] = $marathons;

        return true;
    }

    public function saveUploadedMarathonImage(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmpName = $file['tmp_name'] ?? '';
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return null;
        }

        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            return null;
        }

        $targetDir = __DIR__ . '/../View/FrontOffice/images/uploads';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            return null;
        }

        $filename = 'marathon_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            return null;
        }

        return 'images/uploads/' . $filename;
    }

    public function deleteBook(int $id): bool
    {
        if (!$this->canDeleteMarathon($id)) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = array_values(array_filter(
            $_SESSION[self::SESSION_KEY],
            static fn(array $marathon): bool => (int) $marathon['id'] !== $id
        ));

        return true;
    }

    public function getBook(int $id): ?array
    {
        foreach ($_SESSION[self::SESSION_KEY] as $marathon) {
            if ((int) $marathon['id'] === $id) {
                return $marathon;
            }
        }

        return null;
    }

    public function getVisibleBook(int $id): ?array
    {
        $marathon = $this->getBook($id);
        if ($marathon === null || !(bool) ($marathon['visible'] ?? true)) {
            return null;
        }

        return $marathon;
    }

    public function updateBook(Book $book, int $id): bool
    {
        if (!$this->canEditMarathon($id)) {
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $index => $marathon) {
            if ((int) $marathon['id'] === $id) {
                $updated = $this->bookToRow($book, $id);
                $updated['sponsors'] = $marathon['sponsors'] ?? [];
                $updated['routes'] = $marathon['routes'] ?? [];
                $updated['stands'] = $marathon['stands'] ?? [];
                $_SESSION[self::SESSION_KEY][$index] = $updated;
                return true;
            }
        }

        return false;
    }

    public function getHomepageStats(): array
    {
        $marathons = $this->listBooks();
        $availableSlots = array_sum(array_map(static fn(array $marathon): int => (int) $marathon['copies'], $marathons));

        return [
            ['value' => count($marathons), 'label' => 'Marathons actifs'],
            ['value' => $availableSlots, 'label' => 'Places disponibles'],
            ['value' => $this->countRoutes(), 'label' => 'Parcours'],
            ['value' => $this->countStands(), 'label' => 'Stands partenaires'],
        ];
    }

    public function getRolesOverview(): array
    {
        return [
            [
                'role' => 'Admin',
                'description' => 'Supervise toute la plateforme, les utilisateurs, les marathons, les parcours et les stands.',
                'actions' => ['Consulter tous les marathons', 'Voir tous les parcours', 'Voir tous les stands'],
            ],
            [
                'role' => 'Organisateur',
                'description' => 'Cree et pilote ses marathons avec parcours, stands et catalogue produits.',
                'actions' => ['Ajouter des parcours', 'Ajouter des stands', 'Mettre des produits'],
            ],
            [
                'role' => 'Participant',
                'description' => 'Choisit un parcours, s inscrit et ajoute des produits de stands au panier.',
                'actions' => ['Choisir un parcours', 'Participer', 'Acheter dans les stands'],
            ],
            [
                'role' => 'Visiteur',
                'description' => 'Consulte les marathons, les parcours et les stands.',
                'actions' => ['Voir le catalogue', 'Voir les parcours', 'Voir les stands'],
            ],
        ];
    }

    public function getCurrentUser(): array
    {
        return $_SESSION[self::AUTH_KEY];
    }

    public function isVisitor(): bool
    {
        return $this->getCurrentUser()['role'] === 'visiteur';
    }

    public function isParticipant(): bool
    {
        return $this->getCurrentUser()['role'] === 'participant';
    }

    public function isOrganizer(): bool
    {
        return $this->getCurrentUser()['role'] === 'organisateur';
    }

    public function isAdmin(): bool
    {
        return $this->getCurrentUser()['role'] === 'admin';
    }

    public function canAccessDashboard(): bool
    {
        return $this->isAdmin() || $this->isOrganizer();
    }

    public function canManageMarathons(): bool
    {
        return $this->canAccessDashboard();
    }

    public function canParticipate(array $marathon): bool
    {
        return $this->isParticipant() && in_array($marathon['status'], ['Inscriptions ouvertes', 'Places limitees', 'Premium', 'Nouveau'], true);
    }

    public function canEditMarathon(int $id): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isOrganizer()) {
            return false;
        }

        $marathon = $this->getBook($id);
        return $marathon !== null && ($marathon['owner_username'] ?? null) === $this->getCurrentUser()['username'];
    }

    public function canDeleteMarathon(int $id): bool
    {
        return $this->canEditMarathon($id);
    }

    public function canManageMarathonContent(int $id): bool
    {
        return $this->canEditMarathon($id);
    }

    public function getVisibleBackOfficeMarathons(): array
    {
        $marathons = array_values($_SESSION[self::SESSION_KEY]);
        if ($this->isAdmin()) {
            return $marathons;
        }

        if ($this->isOrganizer()) {
            $username = $this->getCurrentUser()['username'];
            return array_values(array_filter(
                $marathons,
                static fn(array $marathon): bool => ($marathon['owner_username'] ?? null) === $username
            ));
        }

        return [];
    }

    public function toggleMarathonVisibility(int $id): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $index => $marathon) {
            if ((int) ($marathon['id'] ?? 0) !== $id) {
                continue;
            }

            $_SESSION[self::SESSION_KEY][$index]['visible'] = !((bool) ($marathon['visible'] ?? true));
            return true;
        }

        return false;
    }

    public function getAdminVisibleUsers(): array
    {
        if (!$this->isAdmin()) {
            return [];
        }

        return array_values(array_filter(
            $_SESSION[self::USERS_KEY],
            static fn(array $user): bool => in_array((string) ($user['role'] ?? ''), ['participant', 'organisateur'], true)
        ));
    }

    public function getAllRoutesOverview(): array
    {
        if (!$this->isAdmin()) {
            return [];
        }

        $rows = [];
        foreach ($this->listBooks() as $marathon) {
            foreach (($marathon['routes'] ?? []) as $route) {
                $rows[] = [
                    'marathon_id' => (int) ($marathon['id'] ?? 0),
                    'marathon_title' => (string) ($marathon['title'] ?? 'Marathon'),
                    'route_id' => (int) ($route['id'] ?? 0),
                    'name' => (string) ($route['name'] ?? ''),
                    'distance' => (string) ($route['distance'] ?? ''),
                    'difficulty' => (string) ($route['difficulty'] ?? ''),
                    'zone' => (string) ($route['zone'] ?? ''),
                ];
            }
        }

        return $rows;
    }

    public function getAllStandsOverview(): array
    {
        if (!$this->isAdmin()) {
            return [];
        }

        $rows = [];
        foreach ($this->listBooks() as $marathon) {
            foreach (($marathon['stands'] ?? []) as $stand) {
                $rows[] = [
                    'marathon_id' => (int) ($marathon['id'] ?? 0),
                    'marathon_title' => (string) ($marathon['title'] ?? 'Marathon'),
                    'stand_id' => (int) ($stand['id'] ?? 0),
                    'name' => (string) ($stand['name'] ?? ''),
                    'category' => (string) ($stand['category'] ?? ''),
                    'description' => (string) ($stand['description'] ?? ''),
                    'products_count' => count($stand['products'] ?? []),
                ];
            }
        }

        return $rows;
    }

    public function getAdminReclamationsOverview(): array
    {
        if (!$this->isAdmin()) {
            return [];
        }

        $rows = [];
        foreach (($_SESSION[self::ORDER_ISSUES_KEY] ?? []) as $orderId => $messages) {
            $order = null;
            foreach (($_SESSION[self::ORDERS_KEY] ?? []) as $entry) {
                if ((int) ($entry['id'] ?? 0) === (int) $orderId) {
                    $order = $entry;
                    break;
                }
            }

            if ($order === null || empty($messages)) {
                continue;
            }

            $lastMessage = end($messages);
            $rows[] = [
                'order_id' => (int) $orderId,
                'marathon_title' => (string) ($order['marathon_title'] ?? 'Marathon'),
                'stand_name' => (string) ($order['stand_name'] ?? 'Stand'),
                'participant_name' => (string) ($order['participant_name'] ?? $order['username'] ?? 'Participant'),
                'status' => (string) ($order['status'] ?? 'en attente'),
                'messages_count' => count($messages),
                'last_sender' => (string) ($lastMessage['sender_name'] ?? 'Utilisateur'),
                'last_message' => (string) ($lastMessage['description'] ?? ''),
                'last_date' => (string) ($lastMessage['created_at'] ?? ''),
            ];
        }

        usort(
            $rows,
            static fn(array $left, array $right): int => strcmp((string) ($right['last_date'] ?? ''), (string) ($left['last_date'] ?? ''))
        );

        return $rows;
    }

    public function login(string $username, string $password): bool
    {
        foreach ($_SESSION[self::USERS_KEY] as $user) {
            if ($user['username'] === $username && $user['password'] === $password) {
                $_SESSION[self::AUTH_KEY] = [
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'name' => $user['name'],
                ];
                return true;
            }
        }

        return false;
    }

    public function logout(): void
    {
        $_SESSION[self::AUTH_KEY] = [
            'username' => null,
            'role' => 'visiteur',
            'name' => 'Visiteur',
        ];
    }

    public function registerParticipant(array $data): bool
    {
        $username = trim((string) ($data['username'] ?? ''));
        $password = trim((string) ($data['password'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($username === '' || $password === '' || $name === '') {
            return false;
        }

        foreach ($_SESSION[self::USERS_KEY] as $user) {
            if ($user['username'] === $username) {
                return false;
            }
        }

        $_SESSION[self::USERS_KEY][] = [
            'username' => $username,
            'password' => $password,
            'role' => 'participant',
            'name' => $name,
            'profile' => [
                'age' => (string) ($data['age'] ?? ''),
                'weight' => (string) ($data['weight'] ?? ''),
                'height' => (string) ($data['height'] ?? ''),
                'email' => (string) ($data['email'] ?? ''),
                'country' => (string) ($data['country'] ?? ''),
                'city' => (string) ($data['city'] ?? ''),
                'phone' => (string) ($data['phone'] ?? ''),
                'occupation' => (string) ($data['occupation'] ?? ''),
            ],
        ];

        return $this->login($username, $password);
    }

    public function getCurrentUserProfile(): array
    {
        $current = $this->getCurrentUser();
        if (($current['username'] ?? null) === null) {
            return [];
        }

        foreach ($_SESSION[self::USERS_KEY] as $user) {
            if ($user['username'] === $current['username']) {
                return $user['profile'] ?? [];
            }
        }

        return [];
    }

    public function changeCurrentUserPassword(string $currentPassword, string $newPassword): bool
    {
        $current = $this->getCurrentUser();
        if (($current['username'] ?? null) === null || trim($newPassword) === '') {
            return false;
        }

        foreach ($_SESSION[self::USERS_KEY] as $index => $user) {
            if ($user['username'] !== $current['username']) {
                continue;
            }

            if ($user['password'] !== $currentPassword) {
                return false;
            }

            $_SESSION[self::USERS_KEY][$index]['password'] = $newPassword;
            return true;
        }

        return false;
    }

    public function registerForMarathon(
        int $marathonId,
        int $routeId,
        array $selectedProducts = [],
        ?int $bibNumber = null,
        string $pickupMethod = 'sur_place',
        array $bibCustomization = []
    ): bool
    {
        if (!$this->isParticipant()) {
            return false;
        }

        $marathon = $this->getBook($marathonId);
        if ($marathon === null || !$this->routeExists($marathon, $routeId)) {
            return false;
        }

        if ($bibNumber === null || !$this->isBibNumberAvailable($marathonId, $bibNumber)) {
            return false;
        }

        if (!in_array($pickupMethod, ['sur_place', 'livraison'], true)) {
            return false;
        }

        $username = $this->getCurrentUser()['username'];
        $cartItems = [];
        $standSelections = [];

        foreach ($selectedProducts as $standId => $productIds) {
            $standId = (int) $standId;
            if (!$this->standExists($marathon, $standId)) {
                return false;
            }

            $stand = $this->getStandById($marathonId, $standId);
            if ($stand === null) {
                return false;
            }

            $validProducts = [];
            foreach (($stand['products'] ?? []) as $product) {
                if (in_array((int) $product['id'], array_map('intval', (array) $productIds), true)) {
                    $validProducts[] = $product;
                    $cartItems[] = [
                        'stand_id' => $standId,
                        'stand_name' => $stand['name'],
                        'product_id' => (int) $product['id'],
                        'product_name' => $product['name'],
                        'price' => (float) $product['price'],
                    ];
                }
            }

            if (!empty((array) $productIds) && empty($validProducts)) {
                return false;
            }

            if (!empty($validProducts)) {
                $standSelections[] = [
                    'stand_id' => $standId,
                    'stand_name' => $stand['name'],
                    'products' => array_map(
                        static fn(array $product): array => [
                            'id' => (int) $product['id'],
                            'name' => $product['name'],
                            'price' => (float) $product['price'],
                        ],
                        $validProducts
                    ),
                ];
            }
        }

        $_SESSION[self::CART_KEY][$username][$marathonId] = $cartItems;
        $marathonPrice = (float) ($marathon['price'] ?? 0);
        $productsTotal = array_sum(array_map(static fn(array $item): float => (float) $item['price'], $cartItems));
        $deliveryFee = $pickupMethod === 'livraison' ? 8.0 : 0.0;
        $total = $marathonPrice + $productsTotal + $deliveryFee;
        $cleanCustomization = [
            'frame' => (string) ($bibCustomization['frame'] ?? 'classic'),
            'color' => (string) ($bibCustomization['color'] ?? '#102a43'),
            'theme' => (string) ($bibCustomization['theme'] ?? 'sport'),
        ];

        $_SESSION[self::PARTICIPATIONS_KEY][$username][$marathonId] = [
            'route_id' => $routeId,
            'bib_number' => $bibNumber,
            'pickup_method' => $pickupMethod,
            'delivery_fee' => $deliveryFee,
            'bib_customization' => $cleanCustomization,
            'stand_id' => !empty($standSelections) ? (int) $standSelections[0]['stand_id'] : null,
            'stand_selections' => $standSelections,
            'marathon_price' => $marathonPrice,
            'products_total' => $productsTotal,
            'total' => $total,
            'payment_status' => $total > 0 ? 'paid' : 'free',
            'registered_at' => date('Y-m-d H:i:s'),
        ];

        $this->replaceOrdersForParticipation(
            $username,
            $marathonId,
            (string) ($marathon['title'] ?? 'Marathon'),
            $standSelections,
            $pickupMethod,
            $total > 0
        );

        return true;
    }

    public function hasParticipated(int $id): bool
    {
        if (!$this->isParticipant()) {
            return false;
        }

        $username = $this->getCurrentUser()['username'];
        return isset($_SESSION[self::PARTICIPATIONS_KEY][$username][$id]);
    }

    public function getParticipation(int $marathonId): ?array
    {
        if (!$this->isParticipant()) {
            return null;
        }

        $username = $this->getCurrentUser()['username'];
        return $_SESSION[self::PARTICIPATIONS_KEY][$username][$marathonId] ?? null;
    }

    public function getCurrentParticipantOrders(): array
    {
        if (!$this->isParticipant()) {
            return [];
        }

        $username = $this->getCurrentUser()['username'];
        $orders = array_values(array_filter(
            $_SESSION[self::ORDERS_KEY],
            static fn(array $order): bool => ($order['username'] ?? null) === $username
        ));

        usort(
            $orders,
            static fn(array $left, array $right): int => strcmp((string) ($right['date'] ?? ''), (string) ($left['date'] ?? ''))
        );

        return $orders;
    }

    public function getCurrentParticipantOrderById(int $orderId): ?array
    {
        if (!$this->isParticipant()) {
            return null;
        }

        $username = $this->getCurrentUser()['username'];
        foreach ($_SESSION[self::ORDERS_KEY] as $order) {
            if ((int) ($order['id'] ?? 0) === $orderId && ($order['username'] ?? null) === $username) {
                return $order;
            }
        }

        return null;
    }

    public function getCurrentOrganizerOrders(): array
    {
        if (!$this->isOrganizer() && !$this->isAdmin()) {
            return [];
        }

        $username = $this->getCurrentUser()['username'];
        $orders = array_values(array_filter(
            $_SESSION[self::ORDERS_KEY],
            function (array $order) use ($username): bool {
                if ($this->isAdmin()) {
                    return true;
                }

                return $this->resolveOrderOrganizerUsername($order) === $username;
            }
        ));

        usort(
            $orders,
            static fn(array $left, array $right): int => strcmp((string) ($right['date'] ?? ''), (string) ($left['date'] ?? ''))
        );

        return $orders;
    }

    public function getOrderByIdForCurrentUser(int $orderId): ?array
    {
        foreach ($_SESSION[self::ORDERS_KEY] as $order) {
            if ((int) ($order['id'] ?? 0) !== $orderId) {
                continue;
            }

            if ($this->isParticipant() && ($order['username'] ?? null) === $this->getCurrentUser()['username']) {
                return $order;
            }

            if (($this->isOrganizer() || $this->isAdmin()) && $this->canCurrentUserManageOrder($order)) {
                return $order;
            }
        }

        return null;
    }

    public function deleteCurrentParticipantOrder(int $orderId): bool
    {
        if (!$this->isParticipant()) {
            return false;
        }

        $username = $this->getCurrentUser()['username'];
        foreach ($_SESSION[self::ORDERS_KEY] as $index => $order) {
            if ((int) ($order['id'] ?? 0) !== $orderId || ($order['username'] ?? null) !== $username) {
                continue;
            }

            unset($_SESSION[self::ORDERS_KEY][$index]);
            $_SESSION[self::ORDERS_KEY] = array_values($_SESSION[self::ORDERS_KEY]);
            return true;
        }

        return false;
    }

    public function reportIssueForCurrentParticipantOrder(int $orderId, string $description): bool
    {
        return $this->addOrderMessageForCurrentUser($orderId, $description);
    }

    public function getIssuesForCurrentParticipantOrder(int $orderId): array
    {
        if ($this->getOrderByIdForCurrentUser($orderId) === null) {
            return [];
        }

        return $_SESSION[self::ORDER_ISSUES_KEY][$orderId] ?? [];
    }

    public function addOrderMessageForCurrentUser(int $orderId, string $message): bool
    {
        $order = $this->getOrderByIdForCurrentUser($orderId);
        $message = trim($message);
        if ($order === null || $message === '') {
            return false;
        }

        $currentUser = $this->getCurrentUser();
        $_SESSION[self::ORDER_ISSUES_KEY][$orderId] ??= [];
        $_SESSION[self::ORDER_ISSUES_KEY][$orderId][] = [
            'description' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'sender_role' => (string) ($currentUser['role'] ?? 'visiteur'),
            'sender_name' => (string) ($currentUser['name'] ?? 'Utilisateur'),
        ];

        foreach ($_SESSION[self::ORDERS_KEY] as $index => $entry) {
            if ((int) ($entry['id'] ?? 0) !== $orderId) {
                continue;
            }

            if ($this->isParticipant()) {
                $_SESSION[self::ORDERS_KEY][$index]['status'] = 'en attente';
            }
            $_SESSION[self::ORDERS_KEY][$index]['last_message_at'] = date('Y-m-d H:i:s');
            break;
        }

        return true;
    }

    public function validateOrderForCurrentOrganizer(int $orderId): bool
    {
        if (!$this->isOrganizer() && !$this->isAdmin()) {
            return false;
        }

        foreach ($_SESSION[self::ORDERS_KEY] as $index => $order) {
            if ((int) ($order['id'] ?? 0) !== $orderId || !$this->canCurrentUserManageOrder($order)) {
                continue;
            }

            $_SESSION[self::ORDERS_KEY][$index]['status'] = 'valide';
            $_SESSION[self::ORDERS_KEY][$index]['validated_at'] = date('Y-m-d H:i:s');
            return true;
        }

        return false;
    }

    public function isBibNumberAvailable(int $marathonId, int $bibNumber): bool
    {
        if ($bibNumber <= 0) {
            return false;
        }

        $currentUsername = $this->getCurrentUser()['username'];
        foreach ($_SESSION[self::PARTICIPATIONS_KEY] as $username => $entries) {
            if (!isset($entries[$marathonId])) {
                continue;
            }

            $entry = $entries[$marathonId];
            if ((int) ($entry['bib_number'] ?? 0) === $bibNumber && $username !== $currentUsername) {
                return false;
            }
        }

        return true;
    }

    public function getRoutesForMarathon(int $marathonId): array
    {
        $marathon = $this->getBook($marathonId);
        return $marathon['routes'] ?? [];
    }

    public function getStandsForMarathon(int $marathonId): array
    {
        $marathon = $this->getBook($marathonId);
        return $marathon['stands'] ?? [];
    }

    public function getStandById(int $marathonId, int $standId): ?array
    {
        $stands = $this->getStandsForMarathon($marathonId);
        foreach ($stands as $stand) {
            if ((int) $stand['id'] === $standId) {
                return $stand;
            }
        }

        return null;
    }

    public function addRouteToMarathon(int $marathonId, string $name, string $distance, string $difficulty, string $description, string $zone = '', string $mapPath = ''): bool
    {
        if (!$this->canManageMarathonContent($marathonId)) {
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $index => $marathon) {
            if ((int) $marathon['id'] === $marathonId) {
                $routes = $marathon['routes'] ?? [];
                $nextId = empty($routes) ? 1 : (max(array_column($routes, 'id')) + 1);
                $routes[] = [
                    'id' => $nextId,
                    'name' => $name,
                    'distance' => $distance,
                    'difficulty' => $difficulty,
                    'description' => $description,
                    'zone' => $zone,
                    'map_path' => $mapPath,
                ];
                $_SESSION[self::SESSION_KEY][$index]['routes'] = $routes;
                return true;
            }
        }

        return false;
    }

    public function addStandToMarathon(int $marathonId, string $name, string $category, string $description): bool
    {
        if (!$this->canManageMarathonContent($marathonId)) {
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $index => $marathon) {
            if ((int) $marathon['id'] === $marathonId) {
                $stands = $marathon['stands'] ?? [];
                $nextId = empty($stands) ? 1 : (max(array_column($stands, 'id')) + 1);
                $stands[] = [
                    'id' => $nextId,
                    'name' => $name,
                    'category' => $category,
                    'description' => $description,
                    'products' => [],
                ];
                $_SESSION[self::SESSION_KEY][$index]['stands'] = $stands;
                return true;
            }
        }

        return false;
    }

    public function setSponsorsForMarathon(int $marathonId, array $sponsors): bool
    {
        if (!$this->canManageMarathonContent($marathonId) && !$this->isAdmin()) {
            return false;
        }

        $cleanSponsors = [];
        foreach ($sponsors as $sponsor) {
            $name = trim((string) ($sponsor['name'] ?? ''));
            $type = trim((string) ($sponsor['type'] ?? ''));
            $amount = trim((string) ($sponsor['amount'] ?? ''));
            $materials = trim((string) ($sponsor['materials'] ?? ''));

            if ($name === '' || !in_array($type, ['montant', 'materiels'], true)) {
                continue;
            }

            $cleanSponsors[] = [
                'name' => $name,
                'type' => $type,
                'amount' => $type === 'montant' ? $amount : '',
                'materials' => $type === 'materiels' ? $materials : '',
            ];
        }

        foreach ($_SESSION[self::SESSION_KEY] as $index => $marathon) {
            if ((int) $marathon['id'] === $marathonId) {
                $_SESSION[self::SESSION_KEY][$index]['sponsors'] = $cleanSponsors;
                return true;
            }
        }

        return false;
    }

    public function addSponsorToMarathon(int $marathonId, string $name, string $type, string $amount = '', string $materials = ''): bool
    {
        if (!$this->canManageMarathonContent($marathonId) && !$this->isAdmin()) {
            return false;
        }

        $name = trim($name);
        $type = trim($type);
        if ($name === '' || !in_array($type, ['montant', 'materiels'], true)) {
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $index => $marathon) {
            if ((int) $marathon['id'] === $marathonId) {
                $_SESSION[self::SESSION_KEY][$index]['sponsors'] ??= [];
                $_SESSION[self::SESSION_KEY][$index]['sponsors'][] = [
                    'name' => $name,
                    'type' => $type,
                    'amount' => $type === 'montant' ? trim($amount) : '',
                    'materials' => $type === 'materiels' ? trim($materials) : '',
                ];
                return true;
            }
        }

        return false;
    }

    public function getDraftSponsors(): array
    {
        return $_SESSION[self::DRAFT_SPONSORS_KEY] ?? [];
    }

    public function addDraftSponsor(string $name, string $type, string $amount = '', string $materials = ''): bool
    {
        $name = trim($name);
        $type = trim($type);
        if ($name === '' || !in_array($type, ['montant', 'materiels'], true)) {
            return false;
        }

        $_SESSION[self::DRAFT_SPONSORS_KEY][] = [
            'name' => $name,
            'type' => $type,
            'amount' => $type === 'montant' ? trim($amount) : '',
            'materials' => $type === 'materiels' ? trim($materials) : '',
        ];

        return true;
    }

    public function deleteDraftSponsor(int $index): bool
    {
        $drafts = $_SESSION[self::DRAFT_SPONSORS_KEY] ?? [];
        if (!isset($drafts[$index])) {
            return false;
        }

        unset($drafts[$index]);
        $_SESSION[self::DRAFT_SPONSORS_KEY] = array_values($drafts);
        return true;
    }

    public function clearDraftSponsors(): void
    {
        $_SESSION[self::DRAFT_SPONSORS_KEY] = [];
    }

    public function deleteSponsorFromMarathon(int $marathonId, int $sponsorIndex): bool
    {
        if (!$this->canManageMarathonContent($marathonId) && !$this->isAdmin()) {
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $index => $marathon) {
            if ((int) $marathon['id'] === $marathonId) {
                $sponsors = $marathon['sponsors'] ?? [];
                if (!isset($sponsors[$sponsorIndex])) {
                    return false;
                }
                unset($sponsors[$sponsorIndex]);
                $_SESSION[self::SESSION_KEY][$index]['sponsors'] = array_values($sponsors);
                return true;
            }
        }

        return false;
    }

    public function addProductToStand(int $marathonId, int $standId, string $name, float $price, string $description): bool
    {
        if (!$this->canManageMarathonContent($marathonId)) {
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $marathonIndex => $marathon) {
            if ((int) $marathon['id'] !== $marathonId) {
                continue;
            }

            foreach (($marathon['stands'] ?? []) as $standIndex => $stand) {
                if ((int) $stand['id'] === $standId) {
                    $products = $stand['products'] ?? [];
                    $nextId = empty($products) ? 1 : (max(array_column($products, 'id')) + 1);
                    $products[] = [
                        'id' => $nextId,
                        'name' => $name,
                        'price' => $price,
                        'description' => $description,
                    ];
                    $_SESSION[self::SESSION_KEY][$marathonIndex]['stands'][$standIndex]['products'] = $products;
                    return true;
                }
            }
        }

        return false;
    }

    public function getProductCatalog(): array
    {
        return [
            ['id' => 1, 'name' => 'Eau minerale', 'price' => 3.0, 'description' => 'Bouteille d eau fraiche pour les coureurs.', 'image' => $this->buildProductSvg('#8ecae6', '#023047', 'EAU')],
            ['id' => 2, 'name' => 'Banane fraiche', 'price' => 4.5, 'description' => 'Fruit pratique pour recuperation rapide.', 'image' => $this->buildProductSvg('#ffd166', '#7f5539', 'BANANE')],
            ['id' => 3, 'name' => 'Orange fraiche', 'price' => 4.5, 'description' => 'Orange douce riche en energie.', 'image' => $this->buildProductSvg('#fb8500', '#ffffff', 'ORANGE')],
            ['id' => 4, 'name' => 'Jus de fraise', 'price' => 6.0, 'description' => 'Jus fruite frais et vitaminise.', 'image' => $this->buildProductSvg('#ef476f', '#ffffff', 'FRAISE')],
            ['id' => 5, 'name' => 'Jus d orange', 'price' => 6.0, 'description' => 'Jus naturel d orange presse.', 'image' => $this->buildProductSvg('#ffb703', '#102a43', 'JUS')],
            ['id' => 6, 'name' => 'Espresso', 'price' => 5.0, 'description' => 'Cafe court et intense.', 'image' => $this->buildProductSvg('#6f4e37', '#ffffff', 'ESPRESSO')],
            ['id' => 7, 'name' => 'Cappuccino', 'price' => 7.0, 'description' => 'Cafe cremeux avec mousse de lait.', 'image' => $this->buildProductSvg('#c49a6c', '#102a43', 'CAPPU')],
        ];
    }

    public function assignCatalogProductsToStand(int $marathonId, int $standId, array $productIds): bool
    {
        if (!$this->canManageMarathonContent($marathonId)) {
            return false;
        }

        $catalog = $this->getProductCatalog();
        $selectedProducts = [];

        foreach ($catalog as $product) {
            if (in_array((int) $product['id'], $productIds, true)) {
                $selectedProducts[] = $product;
            }
        }

        foreach ($_SESSION[self::SESSION_KEY] as $marathonIndex => $marathon) {
            if ((int) $marathon['id'] !== $marathonId) {
                continue;
            }

            foreach (($marathon['stands'] ?? []) as $standIndex => $stand) {
                if ((int) $stand['id'] === $standId) {
                    $_SESSION[self::SESSION_KEY][$marathonIndex]['stands'][$standIndex]['products'] = $selectedProducts;
                    return true;
                }
            }
        }

        return false;
    }

    public function addProductToCart(int $marathonId, int $standId, int $productId): bool
    {
        if (!$this->isParticipant()) {
            return false;
        }

        $stand = $this->getStandById($marathonId, $standId);
        if ($stand === null) {
            return false;
        }

        $product = null;
        foreach (($stand['products'] ?? []) as $entry) {
            if ((int) $entry['id'] === $productId) {
                $product = $entry;
                break;
            }
        }

        if ($product === null) {
            return false;
        }

        $username = $this->getCurrentUser()['username'];
        $_SESSION[self::CART_KEY][$username][$marathonId] ??= [];
        $_SESSION[self::CART_KEY][$username][$marathonId][] = [
            'stand_id' => $standId,
            'stand_name' => $stand['name'],
            'product_id' => $productId,
            'product_name' => $product['name'],
            'price' => $product['price'],
        ];

        return true;
    }

    public function getCartForMarathon(int $marathonId): array
    {
        if (!$this->isParticipant()) {
            return [];
        }

        $username = $this->getCurrentUser()['username'];
        return $_SESSION[self::CART_KEY][$username][$marathonId] ?? [];
    }

    public function getCartTotalForMarathon(int $marathonId): float
    {
        return array_sum(array_map(static fn(array $item): float => (float) $item['price'], $this->getCartForMarathon($marathonId)));
    }

    public function requireDashboardAccess(): void
    {
        if (!$this->canAccessDashboard()) {
            header('Location: ../FrontOffice/login.php');
            exit;
        }
    }

    private function countRoutes(): int
    {
        $count = 0;
        foreach ($this->listBooks() as $marathon) {
            $count += count($marathon['routes'] ?? []);
        }

        return $count;
    }

    private function countStands(): int
    {
        $count = 0;
        foreach ($this->listBooks() as $marathon) {
            $count += count($marathon['stands'] ?? []);
        }

        return $count;
    }

    private function routeExists(array $marathon, int $routeId): bool
    {
        foreach (($marathon['routes'] ?? []) as $route) {
            if ((int) $route['id'] === $routeId) {
                return true;
            }
        }

        return false;
    }

    private function standExists(array $marathon, int $standId): bool
    {
        foreach (($marathon['stands'] ?? []) as $stand) {
            if ((int) $stand['id'] === $standId) {
                return true;
            }
        }

        return false;
    }

    private function replaceOrdersForParticipation(
        string $username,
        int $marathonId,
        string $marathonTitle,
        array $standSelections,
        string $pickupMethod,
        bool $onlinePayment
    ): void {
        $_SESSION[self::ORDERS_KEY] = array_values(array_filter(
            $_SESSION[self::ORDERS_KEY],
            static fn(array $order): bool => !(($order['username'] ?? null) === $username && (int) ($order['marathon_id'] ?? 0) === $marathonId)
        ));

        $participantName = (string) ($this->getCurrentUser()['name'] ?? 'Participant');
        $marathon = $this->getBook($marathonId);
        $organizerUsername = (string) ($marathon['owner_username'] ?? '');
        $organizerName = (string) ($marathon['organizer'] ?? 'Organisateur');

        foreach ($standSelections as $selection) {
            $amount = array_sum(array_map(
                static fn(array $product): float => (float) ($product['price'] ?? 0),
                $selection['products'] ?? []
            ));

            $_SESSION[self::ORDERS_KEY][] = [
                'id' => $this->getNextOrderId(),
                'username' => $username,
                'participant_name' => $participantName,
                'organizer_username' => $organizerUsername,
                'organizer_name' => $organizerName,
                'marathon_id' => $marathonId,
                'marathon_title' => $marathonTitle,
                'stand_id' => (int) ($selection['stand_id'] ?? 0),
                'stand_name' => (string) ($selection['stand_name'] ?? 'Stand'),
                'date' => date('Y-m-d H:i:s'),
                'amount' => $amount,
                'online_payment' => $onlinePayment ? 'oui' : 'non',
                'pickup_method' => $pickupMethod,
                'status' => $onlinePayment ? 'valide' : 'en attente',
                'last_message_at' => null,
            ];
        }
    }

    private function canCurrentUserManageOrder(array $order): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->isOrganizer()) {
            return false;
        }

        return $this->resolveOrderOrganizerUsername($order) === $this->getCurrentUser()['username'];
    }

    private function resolveOrderOrganizerUsername(array $order): ?string
    {
        $organizerUsername = $order['organizer_username'] ?? null;
        if (is_string($organizerUsername) && trim($organizerUsername) !== '') {
            return $organizerUsername;
        }

        $marathonId = (int) ($order['marathon_id'] ?? 0);
        if ($marathonId <= 0) {
            return null;
        }

        $marathon = $this->getBook($marathonId);
        if ($marathon === null) {
            return null;
        }

        return isset($marathon['owner_username']) ? (string) $marathon['owner_username'] : null;
    }

    private function getNextOrderId(): int
    {
        $ids = array_map(
            static fn(array $order): int => (int) ($order['id'] ?? 0),
            $_SESSION[self::ORDERS_KEY] ?? []
        );

        return empty($ids) ? 1 : (max($ids) + 1);
    }

    private function getDefaultMarathons(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Marathon de Tunis Medina',
                'author' => 'Tunis',
                'publicationDate' => '2026-05-18',
                'langue' => '42 km',
                'status' => 'Inscriptions ouvertes',
                'copies' => 240,
                'category' => 'Urbain',
                'image' => 'images/product-item1.jpg',
                'organizer' => 'Run Tunisia Events',
                'owner_username' => 'organisateur',
                'price' => 65,
                'visible' => true,
                'summary' => 'Un grand rendez-vous entre patrimoine, avenues centrales et ambiance mediterraneenne.',
                'sponsors' => [
                    ['name' => 'Tunisie Telecom', 'type' => 'montant', 'amount' => '15000', 'materials' => ''],
                    ['name' => 'Vital', 'type' => 'materiels', 'amount' => '', 'materials' => 'Eaux, frigos, presentoirs'],
                ],
                'routes' => [
                    ['id' => 1, 'name' => 'Parcours Medina', 'distance' => '10 km', 'difficulty' => 'Moyen', 'description' => 'Circuit urbain entre medina, avenues et passages historiques.'],
                    ['id' => 2, 'name' => 'Parcours Grand Tunis', 'distance' => '42 km', 'difficulty' => 'Avance', 'description' => 'Long parcours pour coureurs confirmes avec zones de ravitaillement.'],
                ],
                'stands' => [
                    [
                        'id' => 1,
                        'name' => 'Stand Hydratation',
                        'category' => 'Boissons',
                        'description' => 'Boissons isotoniques, eau et packs energie.',
                        'products' => [
                            ['id' => 1, 'name' => 'Eau minerale', 'price' => 3, 'description' => 'Bouteille d eau fraiche pour les coureurs.', 'image' => $this->buildProductSvg('#8ecae6', '#023047', 'EAU')],
                            ['id' => 2, 'name' => 'Jus d orange', 'price' => 6, 'description' => 'Jus naturel d orange presse.', 'image' => $this->buildProductSvg('#ffb703', '#102a43', 'JUS')],
                        ],
                    ],
                    [
                        'id' => 2,
                        'name' => 'Stand Souvenirs',
                        'category' => 'Textile',
                        'description' => 'Produits souvenirs et vetements finisher.',
                        'products' => [
                            ['id' => 1, 'name' => 'Cafe', 'price' => 5, 'description' => 'Cafe chaud pour accompagnateurs et staff.', 'image' => $this->buildProductSvg('#6f4e37', '#ffffff', 'CAFE')],
                            ['id' => 2, 'name' => 'Cappuccino', 'price' => 7, 'description' => 'Cafe cremeux avec mousse de lait.', 'image' => $this->buildProductSvg('#c49a6c', '#102a43', 'CAPPU')],
                        ],
                    ],
                ],
            ],
            [
                'id' => 2,
                'title' => 'Semi Marathon de Sousse Corniche',
                'author' => 'Sousse',
                'publicationDate' => '2026-06-08',
                'langue' => '21 km',
                'status' => 'Places limitees',
                'copies' => 120,
                'category' => 'Bord de mer',
                'image' => 'images/product-item2.jpg',
                'organizer' => 'Ocean Pace Club',
                'owner_username' => 'organisateur',
                'price' => 48,
                'visible' => true,
                'summary' => 'Une course rapide au bord de la mer avec stands food, musique et zone famille.',
                'sponsors' => [
                    ['name' => 'Delice', 'type' => 'materiels', 'amount' => '', 'materials' => 'Boissons, frigos mobiles, comptoirs'],
                ],
                'routes' => [
                    ['id' => 1, 'name' => 'Corniche Sprint', 'distance' => '5 km', 'difficulty' => 'Facile', 'description' => 'Ideal pour debuter au bord de la mer.'],
                    ['id' => 2, 'name' => 'Semi Corniche', 'distance' => '21 km', 'difficulty' => 'Moyen', 'description' => 'Parcours principal le long de la corniche.'],
                ],
                'stands' => [
                    [
                        'id' => 1,
                        'name' => 'Stand Nutrition',
                        'category' => 'Nutrition',
                        'description' => 'Collations sportives et nutrition rapide.',
                        'products' => [
                            ['id' => 1, 'name' => 'Banane fraiche', 'price' => 4.5, 'description' => 'Fruit pratique pour recuperation rapide.', 'image' => $this->buildProductSvg('#ffd166', '#7f5539', 'BANANE')],
                            ['id' => 2, 'name' => 'Jus de fraise', 'price' => 6, 'description' => 'Jus fruite frais et vitaminise.', 'image' => $this->buildProductSvg('#ef476f', '#ffffff', 'FRAISE')],
                        ],
                    ],
                ],
            ],
            [
                'id' => 3,
                'title' => 'Trail de Zaghouan',
                'author' => 'Zaghouan',
                'publicationDate' => '2026-09-14',
                'langue' => '15 km',
                'status' => 'Nouveau',
                'copies' => 180,
                'category' => 'Trail',
                'image' => 'images/product-item3.jpg',
                'organizer' => 'Atlas Outdoor',
                'owner_username' => 'organisateur2',
                'price' => 38,
                'visible' => true,
                'summary' => 'Un parcours nature pour les coureurs qui aiment les reliefs, les oliviers et les vues ouvertes.',
                'sponsors' => [
                    ['name' => 'Outdoor Pro', 'type' => 'materiels', 'amount' => '', 'materials' => 'Tentes, balises, arches gonflables'],
                ],
                'routes' => [
                    ['id' => 1, 'name' => 'Trail Oliviers', 'distance' => '8 km', 'difficulty' => 'Moyen', 'description' => 'Boucle courte entre champs et monticules.'],
                    ['id' => 2, 'name' => 'Trail Montagne', 'distance' => '15 km', 'difficulty' => 'Avance', 'description' => 'Denivele plus important et sections rocheuses.'],
                ],
                'stands' => [
                    [
                        'id' => 1,
                        'name' => 'Stand Outdoor',
                        'category' => 'Equipement',
                        'description' => 'Accessoires trail et montagne.',
                        'products' => [
                            ['id' => 1, 'name' => 'Eau minerale', 'price' => 3, 'description' => 'Bouteille d eau fraiche pour les coureurs.', 'image' => $this->buildProductSvg('#8ecae6', '#023047', 'EAU')],
                            ['id' => 2, 'name' => 'Orange fraiche', 'price' => 4.5, 'description' => 'Orange douce riche en energie.', 'image' => $this->buildProductSvg('#fb8500', '#ffffff', 'ORANGE')],
                        ],
                    ],
                ],
            ],
            [
                'id' => 4,
                'title' => 'Marathon International de Djerba',
                'author' => 'Djerba',
                'publicationDate' => '2026-11-01',
                'langue' => '42 km',
                'status' => 'Premium',
                'copies' => 320,
                'category' => 'Destination',
                'image' => 'images/product-item4.jpg',
                'organizer' => 'Djerba Sport Tourism',
                'owner_username' => 'organisateur2',
                'price' => 82,
                'visible' => true,
                'summary' => 'Une experience complete avec tourisme sportif, zones stands et formule weekend.',
                'sponsors' => [
                    ['name' => 'Banque Horizon', 'type' => 'montant', 'amount' => '25000', 'materials' => ''],
                ],
                'routes' => [
                    ['id' => 1, 'name' => 'Parcours Lagoon', 'distance' => '10 km', 'difficulty' => 'Facile', 'description' => 'Parcours touristique et rapide.'],
                    ['id' => 2, 'name' => 'Parcours International', 'distance' => '42 km', 'difficulty' => 'Avance', 'description' => 'Le grand format du marathon international.'],
                ],
                'stands' => [
                    [
                        'id' => 1,
                        'name' => 'Stand Premium',
                        'category' => 'Souvenirs',
                        'description' => 'Cadeaux, textile et packs officiels.',
                        'products' => [
                            ['id' => 1, 'name' => 'Espresso', 'price' => 5, 'description' => 'Cafe court et intense.', 'image' => $this->buildProductSvg('#6f4e37', '#ffffff', 'ESPRESSO')],
                            ['id' => 2, 'name' => 'Jus de fraise', 'price' => 6, 'description' => 'Jus fruite frais et vitaminise.', 'image' => $this->buildProductSvg('#ef476f', '#ffffff', 'FRAISE')],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getDefaultUsers(): array
    {
        return [
            ['username' => 'admin', 'password' => 'admin', 'role' => 'admin', 'name' => 'Administrateur'],
            ['username' => 'organisateur', 'password' => 'organisateur', 'role' => 'organisateur', 'name' => 'Organisateur Demo'],
            ['username' => 'organisateur2', 'password' => 'organisateur2', 'role' => 'organisateur', 'name' => 'Organisateur Nord'],
            ['username' => 'participant', 'password' => 'participant', 'role' => 'participant', 'name' => 'Participant Demo'],
        ];
    }

    private function bookToRow(Book $book, int $id): array
    {
        return [
            'id' => $id,
            'title' => (string) $book->getName(),
            'author' => (string) $book->getLocation(),
            'publicationDate' => (string) $book->getEventDate(),
            'langue' => (string) $book->getDistance(),
            'status' => (string) $book->getStatus(),
            'copies' => (int) $book->getSlots(),
            'category' => (string) $book->getCategory(),
            'image' => (string) $book->getImage(),
            'organizer' => (string) $book->getOrganizer(),
            'owner_username' => $this->isOrganizer() ? (string) $this->getCurrentUser()['username'] : 'admin',
            'price' => (float) $book->getPrice(),
            'visible' => true,
            'summary' => 'Evenement personnalise ajoute depuis le back office.',
            'sponsors' => [],
            'routes' => [],
            'stands' => [],
        ];
    }

    private function buildProductSvg(string $bgColor, string $textColor, string $label): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 220">'
            . '<rect width="320" height="220" rx="28" fill="' . $bgColor . '"/>'
            . '<circle cx="70" cy="68" r="36" fill="rgba(255,255,255,0.25)"/>'
            . '<circle cx="260" cy="150" r="54" fill="rgba(255,255,255,0.18)"/>'
            . '<rect x="36" y="144" width="248" height="40" rx="20" fill="rgba(255,255,255,0.22)"/>'
            . '<text x="160" y="95" text-anchor="middle" font-family="Arial, sans-serif" font-size="26" font-weight="700" fill="' . $textColor . '">' . htmlspecialchars($label, ENT_QUOTES) . '</text>'
            . '<text x="160" y="170" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" font-weight="700" fill="' . $textColor . '">BarchaThon</text>'
            . '</svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
