Team Toolbox Next (Vue + Vuetify)
=================================

Cette branche introduit une nouvelle version frontale de Team Toolbox, orientée
collaboration manager/équipe, avec une expérience inspirée d’un fil de discussion
(type réseau social interne) et des espaces d’échanges plus formels.

Fonctionnalités principales
---------------------------

- Fil d’actualité d’équipe (messages quotidiens, formels, alertes) ;
- Publication rapide d’un message ;
- Réactions (likes) et commentaires ;
- Filtres par canal + recherche ;
- Espace “échanges formels” pour stocker des comptes-rendus, décisions et actions ;
- Persistance locale (localStorage) pour conserver le contexte d’équipe.

L’application est volontairement plus compacte que l’ancienne version, afin de
fournir un socle clair et moderne facilement extensible.

Lancer en local (sans Docker)
-----------------------------

```bash
cd frontend-vue
npm install
npm run dev
```

Application disponible sur <http://localhost:5173>.

Utilisation via Docker Compose
------------------------------

Un `docker-compose.yml` minimal est fourni pour lancer uniquement le nouveau front.

```bash
docker compose up --build
```

Application disponible sur <http://localhost:5173>.

Arrêter:

```bash
docker compose down
```

Structure
---------

- `frontend-vue/`: nouvelle application Vue 3 + Vuetify (Vite)
- `docker-compose.yml`: exécution simple du front dans un conteneur Node

