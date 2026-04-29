# 📡 API Documentation

## Overview

RQBI Messagerie API est une API **REST** qui permet de:
- Gérer les utilisateurs (auth, profil)
- Créer et gérer les conversations
- Envoyer et modifier les messages
- Recevoir les mises à jour en temps réel via Mercure

**Base URL**: `http://localhost/api`

---

## Authentication

Tous les endpoints protégés nécessitent un Bearer token:

```
Authorization: Bearer <token>
```

Le token est obtenu via `/auth/login` ou `/auth/register`.

---

## Response Format

Toutes les réponses sont en **JSON**:

```json
{
  "id": "uuid",
  "email": "user@example.com",
  "firstName": "Jean",
  "lastName": "Dupont",
  "fullName": "Jean Dupont",
  "token": "base64-encoded-token",
  "createdAt": "2026-04-29T11:00:00+00:00"
}
```

**Erreurs:**
```json
{
  "error": "Message d'erreur",
  "code": 400
}
```

---

## Endpoints

### 🔐 Authentication

#### POST `/auth/register`

Créer un nouvel utilisateur.

**Request:**
```json
{
  "firstName": "Jean",
  "lastName": "Dupont",
  "email": "jean@example.com",
  "password": "SecurePassword123"
}
```

**Response:** `201 Created`
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "email": "jean@example.com",
  "firstName": "Jean",
  "lastName": "Dupont",
  "fullName": "Jean Dupont",
  "token": "amVhbkBleGFtcGxlLmNvbQ==",
  "createdAt": "2026-04-29T11:00:00+00:00"
}
```

**Erreurs:**
- `400 Bad Request` - Champs manquants ou invalides
- `400 Bad Request` - Email déjà utilisé

---

#### POST `/auth/login`

Authentifier un utilisateur.

**Request:**
```json
{
  "email": "jean@example.com",
  "password": "SecurePassword123"
}
```

**Response:** `200 OK`
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "email": "jean@example.com",
  "firstName": "Jean",
  "lastName": "Dupont",
  "fullName": "Jean Dupont",
  "token": "amVhbkBleGFtcGxlLmNvbQ==",
  "createdAt": "2026-04-29T11:00:00+00:00"
}
```

**Erreurs:**
- `401 Unauthorized` - Identifiants invalides
- `400 Bad Request` - Champs manquants

---

### 💬 Conversations

#### GET `/conversations`

Lister toutes les conversations de l'utilisateur.

**Headers:**
```
Authorization: Bearer <token>
```

**Response:** `200 OK`
```json
[
  {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Chat avec Alice",
    "type": "private",
    "members": [
      {
        "id": "550e8400-e29b-41d4-a716-446655440001",
        "name": "Alice Dupont",
        "email": "alice@example.com"
      },
      {
        "id": "550e8400-e29b-41d4-a716-446655440002",
        "name": "Jean Dupont",
        "email": "jean@example.com"
      }
    ],
    "lastMessage": "À bientôt!",
    "lastMessageTime": "2026-04-29T11:30:00+00:00",
    "createdAt": "2026-04-29T10:00:00+00:00",
    "updatedAt": "2026-04-29T11:30:00+00:00"
  }
]
```

**Erreurs:**
- `401 Unauthorized` - Token invalide/expiré

---

#### GET `/conversations/{conversationId}`

Récupérer les détails d'une conversation.

**Response:** `200 OK`
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "name": "Chat avec Alice",
  "type": "private",
  "members": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440001",
      "name": "Alice Dupont",
      "email": "alice@example.com"
    }
  ],
  "lastMessage": "À bientôt!",
  "lastMessageTime": "2026-04-29T11:30:00+00:00",
  "createdAt": "2026-04-29T10:00:00+00:00",
  "updatedAt": "2026-04-29T11:30:00+00:00"
}
```

**Erreurs:**
- `404 Not Found` - Conversation inexistante
- `401 Unauthorized` - Accès refusé

---

#### POST `/conversations/private/{userId}`

Créer/obtenir une conversation privée avec un utilisateur.

**Response:** `201 Created` ou `200 OK`
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "name": null,
  "type": "private",
  "members": [...]
}
```

---

#### POST `/conversations/group`

Créer un groupe de conversation.

**Request:**
```json
{
  "name": "Team Project",
  "members": [
    "550e8400-e29b-41d4-a716-446655440001",
    "550e8400-e29b-41d4-a716-446655440002"
  ]
}
```

**Response:** `201 Created`
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "name": "Team Project",
  "type": "group",
  "members": [...]
}
```

**Erreurs:**
- `400 Bad Request` - Nom manquant

---

### 📨 Messages

#### GET `/conversations/{conversationId}/messages`

Récupérer les messages d'une conversation.

**Query Parameters:**
- `limit` (optional, default: 50) - Nombre de messages
- `offset` (optional, default: 0) - Décalage pour pagination

**Response:** `200 OK`
```json
{
  "messages": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "content": "Bonjour!",
      "senderName": "Alice Dupont",
      "senderId": "550e8400-e29b-41d4-a716-446655440001",
      "conversationId": "550e8400-e29b-41d4-a716-446655440000",
      "createdAt": "2026-04-29T11:00:00+00:00",
      "editedAt": null
    },
    {
      "id": "550e8400-e29b-41d4-a716-446655440010",
      "content": "Salut!",
      "senderName": "Jean Dupont",
      "senderId": "550e8400-e29b-41d4-a716-446655440002",
      "conversationId": "550e8400-e29b-41d4-a716-446655440000",
      "createdAt": "2026-04-29T11:05:00+00:00",
      "editedAt": null
    }
  ],
  "total": 42
}
```

---

#### POST `/conversations/{conversationId}/messages`

Envoyer un message.

**Request:**
```json
{
  "content": "Bonjour!"
}
```

**Response:** `201 Created`
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "content": "Bonjour!",
  "senderName": "Jean Dupont",
  "senderId": "550e8400-e29b-41d4-a716-446655440002",
  "conversationId": "550e8400-e29b-41d4-a716-446655440000",
  "createdAt": "2026-04-29T11:00:00+00:00",
  "editedAt": null
}
```

**Erreurs:**
- `400 Bad Request` - Contenu vide
- `404 Not Found` - Conversation inexistante
- `401 Unauthorized` - Accès refusé

---

#### PATCH `/conversations/{conversationId}/messages/{messageId}`

Éditer un message.

**Request:**
```json
{
  "content": "Bonjour modifié!"
}
```

**Response:** `200 OK`
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "content": "Bonjour modifié!",
  "senderName": "Jean Dupont",
  "senderId": "550e8400-e29b-41d4-a716-446655440002",
  "conversationId": "550e8400-e29b-41d4-a716-446655440000",
  "createdAt": "2026-04-29T11:00:00+00:00",
  "editedAt": "2026-04-29T11:05:00+00:00"
}
```

**Erreurs:**
- `400 Bad Request` - Contenu vide
- `401 Unauthorized` - Vous n'êtes pas l'auteur
- `404 Not Found` - Message inexistant

---

#### DELETE `/conversations/{conversationId}/messages/{messageId}`

Supprimer un message.

**Response:** `204 No Content`

**Erreurs:**
- `401 Unauthorized` - Vous n'êtes pas l'auteur
- `404 Not Found` - Message inexistant

---

## 🔔 Real-time Updates (Mercure)

S'abonner aux mises à jour en temps réel:

```javascript
const eventSource = new EventSource(
  '/.well-known/mercure?topic=conversation/550e8400-e29b-41d4-a716-446655440000/messages'
);

eventSource.onmessage = (event) => {
  const message = JSON.parse(event.data);
  console.log('Nouveau message:', message);
};
```

**Topics:**
- `conversation/{id}/messages` - Nouveaux messages
- `conversation/{id}` - Mises à jour conversation
- `user/{id}/conversations` - Changements conversations utilisateur
- `conversation/{id}/typing` - Indicateur "en train de taper"

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Succès |
| 201 | Created - Ressource créée |
| 204 | No Content - Succès (sans réponse) |
| 400 | Bad Request - Requête invalide |
| 401 | Unauthorized - Auth requise |
| 404 | Not Found - Ressource inexistante |
| 500 | Server Error - Erreur serveur |

---

## Rate Limiting

À implémenter:
- 100 requêtes/minute par utilisateur
- 1000 requêtes/heure par IP

---

## Versioning

Version actuelle: **v1** (implicite, pas de préfixe)

Future: `/api/v2/...`
