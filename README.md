# iut-wp-repo

Monorepo d’extensions WordPress (shortcodes + blocs Gutenberg).

## Extensions

| Dossier | Description | Doc |
|---------|-------------|-----|
| [`wp-flipbook`](wp-flipbook/) | Flipbook Turn.js configurable (CPT, shortcode `[flipbook]`, bloc **Flipbook**) | [README](wp-flipbook/README.md) |
| [`wp-mouse-trail`](wp-mouse-trail/) | Zone de contenu avec traînée souris canvas (shortcode `[mouse_trail]`, bloc **Mouse Trail**) | [README](wp-mouse-trail/README.md) |

Prérequis communs : WordPress **6.0+**, PHP **7.4+**.

## Structure

```text
iut-wp-repo/
├── wp-flipbook/          # plugin Flipbook
├── wp-mouse-trail/       # plugin Mouse Trail
├── scripts/
│   └── create-release.sh # bump SemVer + zips
└── dist/                 # archives générées (gitignore)
```

## Installation

### Depuis une archive (recommandé)

1. Générer les zips (voir [Release](#release)).
2. Dans WordPress : **Extensions → Ajouter → Téléverser** chaque fichier `dist/wp-*-x.y.z.zip`.
3. Activer les extensions.

### Depuis les sources

Copier chaque dossier `wp-*` dans `wp-content/plugins/`, puis activer dans l’admin.

## Release

Le script interactif bump la version de **toutes** les extensions `wp-*`, puis produit des zips installables :

```bash
./scripts/create-release.sh
```

1. Choisir **patch** / **minor** / **major** (SemVer).
2. Confirmer le plan (`1.0.0 → …` par plugin).
3. Récupérer les archives dans `dist/` :
   - `wp-flipbook-x.y.z.zip`
   - `wp-mouse-trail-x.y.z.zip`

Le script met à jour le header `Version:` et la constante `*_VERSION` du fichier bootstrap de chaque plugin. Aucun tag / commit Git n’est créé automatiquement.

## Licence

Les plugins sont sous **GPL-2.0-or-later** (voir l’en-tête de chaque fichier principal).
