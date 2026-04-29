# 💬 RQBI Messagerie & Chat

Application de messagerie et chat en temps réel pour les régies de quartier, construite avec **Symfony 7.4**, **PostgreSQL**, **Mercure**, et **Alpine.js**.

## 🚀 Démarrage Rapide

### Prérequis

- Docker & Docker Compose
- Git

### Installation

1. **Cloner le projet**
```bash
git clone <repo-url>
cd RQBI_messagerie_chat
```

2. **Créer le fichier d'environnement**
```bash
cp .env.local.example .env.local
```

3. **Lancer les services Docker**
```bash
docker-compose up -d
```

4. **Installer les dépendances PHP**
```bash
docker-compose exec app composer install
```

5. **Initialiser la base de données**
```bash
docker-compose exec app php bin/console doctrine:database:create
docker-compose exec app php bin/console doctrine:migrations:migrate
```

6. **Installer les assets frontend**
```bash
npm install
npm run dev
```

7. **Accéder à l'application**
- 🌐 Frontend: `http://localhost`
- 📡 API: `http://localhost/api`
- 🔔 Mercure: `http://localhost:3000`

---

## 📖 Documentation

### Architecture

```
RQBI_messagerie_chat/
├── src/
│   ├── Entity/           # Doctrine entities (User, Conversation, Message, ConversationMember)
│   ├── Repository/       # Data access layer
│   ├── Service/          # Business logic (Auth, Conversation, Message, MercurePublisher)
│   ├── Controller/       # API endpoints
│   ├── Dto/             # Request/Response DTOs
│   └── Security/        # Authentication (ApiTokenAuthenticator)
├── config/              # Symfony configuration
├── templates/           # Twig templates (login, register, chat)
├── assets/              # Frontend (Alpine.js, CSS)
├── migrations/          # Doctrine migrations
├── tests/               # Test suites
└── docker-compose.yml   # Services definition
```

### API Endpoints

#### 🔐 Authentication

```
POST /api/auth/register
  Body: {
    "firstName": "Jean",
    "lastName": "Dupont",
    "email": "jean@example.com",
    "password": "secret123"
  }
  Response: { "id", "email", "fullName", "token", "createdAt" }

POST /api/auth/login
  Body: {
    "email": "jean@example.com",
    "password": "secret123"
  }
  Response: { "id", "email", "fullName", "token", "createdAt" }
```

#### 💬 Conversations

```
GET /api/conversations
  Headers: { "Authorization": "Bearer <token>" }
  Response: [ { "id", "name", "type", "members", "lastMessage", "createdAt" } ]

GET /api/conversations/{id}
  Headers: { "Authorization": "Bearer <token>" }
  Response: { "id", "name", "type", "members", "messages", "createdAt" }

POST /api/conversations/private/{userId}
  Headers: { "Authorization": "Bearer <token>" }
  Response: { "id", "name", "type", "members", "createdAt" }

POST /api/conversations/group
  Headers: { "Authorization": "Bearer <token>" }
  Body: { "name": "Groupe Projet", "members": ["id1", "id2"] }
  Response: { "id", "name", "type", "members", "createdAt" }
```

#### 📨 Messages

```
GET /api/conversations/{conversationId}/messages?limit=50&offset=0
  Headers: { "Authorization": "Bearer <token>" }
  Response: { "messages": [...], "total": 100 }

POST /api/conversations/{conversationId}/messages
  Headers: { "Authorization": "Bearer <token>" }
  Body: { "content": "Bonjour!" }
  Response: { "id", "content", "senderName", "senderId", "createdAt" }

PATCH /api/conversations/{conversationId}/messages/{messageId}
  Headers: { "Authorization": "Bearer <token>" }
  Body: { "content": "Contenu modifié" }
  Response: { "id", "content", "editedAt" }

DELETE /api/conversations/{conversationId}/messages/{messageId}
  Headers: { "Authorization": "Bearer <token>" }
  Response: 204 No Content
```

### 🔔 Temps Réel (Mercure)

Les mises à jour en temps réel sont publiées sur:

```
conversation/{conversationId}/messages  - Nouveaux messages
conversation/{conversationId}           - Mises à jour conversation
user/{userId}/conversations             - Mises à jour conversations utilisateur
conversation/{conversationId}/typing     - Indicateur de saisie
```

Pour s'abonner côté frontend:
```javascript
const eventSource = new EventSource('/.well-known/mercure?topic=conversation/123/messages');
eventSource.onmessage = (event) => {
  const message = JSON.parse(event.data);
  // Traiter le message
};
```

---

## 🧪 Tests

### Lancer les tests

```bash
# Tests unitaires
docker-compose exec app php bin/phpunit tests/Unit

# Tests d'intégration API
docker-compose exec app php bin/phpunit tests/Integration

# Tous les tests
docker-compose exec app php bin/phpunit

# Avec couverture de code
docker-compose exec app php bin/phpunit --coverage-html=coverage
```

### Structure des tests

```
tests/
├── Unit/
│   ├── Service/          # Tests unitaires des services
│   └── Entity/           # Tests des entities
└── Integration/
    ├── Api/              # Tests des endpoints API
    └── Security/         # Tests d'authentification
```

### Exemples de tests

**Test de création d'utilisateur:**
```php
public function testUserRegistration(): void
{
    $response = $this->client->post('/api/auth/register', [
        'firstName' => 'Jean',
        'lastName' => 'Dupont',
        'email' => 'jean@test.com',
        'password' => 'password123'
    ]);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertStringContainsString('jean@test.com', $response->getBody());
}
```

**Test d'envoi de message:**
```php
public function testSendMessage(): void
{
    $user = $this->createUser();
    $conversation = $this->createConversation([$user]);

    $response = $this->client->post(
        "/api/conversations/{$conversation->getId()}/messages",
        ['content' => 'Test message'],
        ['Authorization' => "Bearer {$user->getToken()}"]
    );

    $this->assertEquals(201, $response->getStatusCode());
}
```

---

## 🛠 Commandes Utiles

### Symfony

```bash
# Créer une migration
docker-compose exec app php bin/console make:migration

# Exécuter les migrations
docker-compose exec app php bin/console doctrine:migrations:migrate

# Accès à la console Symfony
docker-compose exec app php bin/console

# Vider le cache
docker-compose exec app php bin/console cache:clear
```

### Docker

```bash
# Logs des services
docker-compose logs -f app
docker-compose logs -f db
docker-compose logs -f mercure

# Entrer dans le container PHP
docker-compose exec app bash

# Redémarrer les services
docker-compose restart

# Arrêter les services
docker-compose down
```

---

## 🎨 Design & Couleurs

Le projet utilise la palette RQBI:

- **Rouge primaire**: `#D0201A`
- **Bleu secondaire**: `#2563A8`
- **Fond (cream)**: `#f3ebde`
- **Texte (ink)**: `#1a1a1a`

Logo et assets disponibles dans: `public/images/`

---

## 🔒 Sécurité

- ✅ Hashage des mots de passe (bcrypt)
- ✅ Authentication par token Bearer
- ✅ Validation des entrées utilisateur
- ✅ Protection contre CSRF (Symfony)
- ✅ Validation des permissions sur conversations et messages

**À améliorer en production:**
- [ ] HTTPS (Let's Encrypt)
- [ ] Rate limiting sur les endpoints
- [ ] Logs d'audit
- [ ] 2FA
- [ ] Content Security Policy

---

## 📱 Features

- ✅ Inscription/Login
- ✅ Conversations 1-à-1
- ✅ Groupes de conversation
- ✅ Messages en temps réel (Mercure)
- ✅ Historique des messages
- ✅ Édition/Suppression de messages
- ✅ Interface responsive
- ✅ Design RQBI

**À implémenter:**
- [ ] Recherche de messages
- [ ] Indicateur "en train de taper"
- [ ] Lire/Non-lu
- [ ] Notifications push
- [ ] Upload de fichiers/images
- [ ] Mentions @
- [ ] Réactions emoji

---

## 🤝 Contribution

1. Créer une branche: `git checkout -b feature/ma-feature`
2. Commit: `git commit -m "Add: description"`
3. Push: `git push origin feature/ma-feature`
4. Créer une Pull Request

---

## 📞 Support

Pour toute question ou bug, créer une issue sur GitHub.

---

## 📄 License

Proprietary - RQBI 2026
