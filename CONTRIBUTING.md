# 🤝 Guide de Contribution

Merci de vouloir contribuer à RQBI Messagerie! 

## 📋 Avant de commencer

1. **Fork** le projet
2. **Cloner** votre fork: `git clone <fork-url>`
3. **Créer une branche**: `git checkout -b feature/ma-feature`

## 🎯 Processus de contribution

### 1. Développement

```bash
# Installer les dépendances
composer install
npm install

# Lancer les services
docker-compose up -d

# Lancer les tests
docker-compose exec app composer test
```

### 2. Code Style

- Suivre **PSR-12** pour le PHP
- Utiliser **camelCase** pour les variables/méthodes
- Utiliser **UPPERCASE_SNAKE_CASE** pour les constantes
- Commenter le code **complexe** uniquement

**Vérifier le style:**
```bash
docker-compose exec app php bin/console lint:yaml config
docker-compose exec app php -l src/
```

### 3. Tests

**Créer des tests pour chaque feature:**
- Tests unitaires dans `tests/Unit/`
- Tests d'intégration dans `tests/Integration/`

**Lancer les tests:**
```bash
# Tous les tests
docker-compose exec app php bin/phpunit

# Tests spécifiques
docker-compose exec app php bin/phpunit tests/Unit/Service/

# Avec couverture
docker-compose exec app php bin/phpunit --coverage-html=coverage
```

**Couverture minimum:**
- Services: 80%+
- Controllers: 75%+
- Entities: 70%+

### 4. Commits

Format des messages:
```
Type: Brief description

Detailed explanation if needed.

- Bullet point 1
- Bullet point 2

Fixes #123
```

**Types:**
- `feat:` Nouvelle feature
- `fix:` Bug fix
- `docs:` Documentation
- `style:` Code style (pas de changement logique)
- `refactor:` Refactoring
- `test:` Ajout/modification de tests
- `chore:` Dépendances, config, etc.

**Exemples:**
```
feat: Add message search functionality

Implements full-text search for messages within conversations.

- Index message content in database
- Add search endpoint /api/search/messages
- Add frontend search UI

Fixes #42

---

fix: Fix authentication token expiration

The API token wasn't validating expiration time.

Fixes #99
```

### 5. Pull Request

1. **Pousser** votre branche: `git push origin feature/ma-feature`
2. **Créer** une PR avec:
   - ✅ Titre clair
   - ✅ Description détaillée
   - ✅ Tests ajoutés/modifiés
   - ✅ Zéro breaking changes (sauf si documenté)
   - ✅ Documentation à jour

**Template PR:**
```markdown
## 📝 Description

Brève description de ce qui a changé.

## 🎯 Type

- [ ] Feature
- [ ] Bug fix
- [ ] Documentation
- [ ] Performance

## 🧪 Tests

- [ ] Tests ajoutés
- [ ] Tests existants passent
- [ ] Couverture: X%

## 📸 Screenshots

(si applicable)

## 🔄 Checklist

- [ ] Code review fait
- [ ] Tests passent
- [ ] Documentation à jour
- [ ] Pas de console.logs / var_dump
```

## 🐛 Signaler un Bug

Créer une issue avec:
- ✅ Titre descriptif
- ✅ Description du problème
- ✅ Étapes pour reproduire
- ✅ Comportement attendu vs réel
- ✅ Environnement (OS, navigateur, version)

**Exemple:**
```markdown
## Bug: Messages n'arrivent pas en temps réel

### Description
Lors de l'envoi d'un message via le chat, le message n'apparaît pas 
en temps réel pour les autres utilisateurs.

### Étapes pour reproduire
1. Créer une conversation privée
2. Ouvrir la conversation dans 2 onglets
3. Envoyer un message depuis l'onglet 1
4. Observer l'onglet 2

### Comportement attendu
Le message apparaît immédiatement dans l'onglet 2.

### Comportement réel
Le message n'apparaît que après un rafraîchissement F5.

### Environnement
- Navigateur: Chrome 120
- OS: Windows 10
- Version app: 1.0.0
```

## 📚 Documentation

Garder à jour:
- `README.md` - Guide général
- `docs/API.md` - Documentation API (à créer)
- Code comments - Pour la logique complexe
- CHANGELOG.md - Historique des versions

## 🚀 Standards de Qualité

### Performance
- Pas de N+1 queries
- Caching approprié
- Mercure pour les updates temps réel

### Sécurité
- Validation des inputs
- Hashage des mots de passe
- Authentification sur tous les endpoints protégés

### Accessibilité
- ARIA labels sur les formulaires
- Contraste suffisant (WCAG AA)
- Navigation au clavier

### Compatibilité
- Supporté: PHP 8.3+, PostgreSQL 13+, Chrome/Firefox/Safari récents
- Responsive: Mobile, Tablet, Desktop

## 🎓 Ressources

- [Symfony Docs](https://symfony.com/doc)
- [PHP Standards](https://www.php-fig.org/)
- [Alpine.js Docs](https://alpinejs.dev)
- [Mercure Docs](https://mercure.rocks)

## ❓ Questions?

- Créer une discussion GitHub
- Contacter les mainteneurs

---

**Merci pour votre contribution!** 🙏
