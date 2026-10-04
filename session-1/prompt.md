# PLATEFORME D’ENCHÈRES — MODERN WEB UI/UX DESIGN

Design and improve my existing **PHP/MySQL Auction Platform**.

The goal is to transform the current basic interface into a **modern, professional, clean, responsive auction platform UI**, while keeping the existing application logic and workflow exactly as it is.

---

# IMPORTANT DEVELOPMENT CONSTRAINT

The UI and implementation must follow the **existing project and existing database**.

## DO NOT:

* Change the application's business logic.
* Change the existing workflow.
* Add login or registration.
* Add authentication.
* Add admin authentication.
* Add payments.
* Add notifications.
* Add messaging.
* Add dashboards that are not required.
* Add unnecessary modules.
* Invent new entities.
* Invent new relationships.
* Replace PHP with another backend technology.
* Replace MySQL.
* Use React, Vue, Angular, Laravel, Symfony, Bootstrap, Tailwind or other frameworks.
* Rewrite the project into a complex architecture.

The goal is to improve the existing student project while keeping it simple and understandable.

If a database change is necessary, **do not make it silently**. Explain exactly which change is required.

---

# 1. EXISTING APPLICATION

The current project is a simple PHP/MySQL auction platform.

The current project contains:

```text
plateforme-encheres/
│
├── connexion.php
├── index.php
├── ajouter.php
├── modifier.php
├── supprimer.php
└── style.css
```

The project already uses:

* PHP
* PDO
* MySQL
* HTML
* CSS
* Vanilla JavaScript when necessary

The current database is:

```text
plateforme_encheres
```

The existing database contains the core entities:

```text
utilisateur
type_lot
article
offre
```

Respect these existing entities and relationships.

---

# 2. CURRENT USER WORKFLOW

There is currently **one simple user**.

There is NO login system.

There is NO authentication system.

There is NO admin system.

The user enters directly into the application.

The user must be able to:

```text
Main Page
    │
    ├── Consulter les articles
    │
    ├── Ajouter un article
    │
    ├── Modifier un article
    │
    ├── Supprimer un article
    │
    └── Participer aux enchères
```

Keep this workflow.

Do not introduce authentication.

---

# 3. MAIN PAGE

The main page is:

```text
index.php
```

Transform it into a modern auction marketplace homepage.

Page title:

**Plateforme d’Enchères**

The page should clearly communicate that users can discover and participate in auctions.

---

# 4. HEADER

Create a clean modern header.

Display:

**Plateforme d’Enchères**

Navigation/actions should remain simple.

Include a primary button:

```text
+ Ajouter un article
```

Do not add unnecessary navigation.

The header should feel like a real auction website.

---

# 5. AUCTION CARDS

Display each article as a modern card.

Each card should contain:

### Image

Display the article image.

If no image exists:

Display a clean placeholder area.

### Title

Display:

```text
Titre de l'article
```

### Description

Display the article description.

### Starting price

Display:

```text
Prix de départ
```

Example:

```text
Prix de départ
350 DH
```

### Auction countdown

Display:

```text
Temps restant
01j 05h 32m 18s
```

The countdown must use the existing:

```text
date_fin
```

from MySQL.

---

# 6. COUNTDOWN SYSTEM

Create a real-time countdown for every auction article.

Use Vanilla JavaScript.

Each article must have its own countdown.

Example:

```text
01j 05h 32m 18s
```

The countdown must:

* update every second
* use the real `date_fin`
* be different for every article
* stop when the auction reaches zero
* display:

```text
Enchère terminée
```

when the auction is finished.

Use a subtle animation to make the countdown visually dynamic.

For example:

* small pulse effect
* smooth number transition
* subtle visual emphasis when the deadline approaches

Do NOT use excessive animations.

The animation must remain professional.

---

# 7. AUCTION STATUS

Each article should visually indicate its status.

For example:

```text
En cours
```

or:

```text
Enchère terminée
```

Only use statuses that correspond to the existing application logic.

Do not invent additional business states.

Use badges with a professional design.

---

# 8. ARTICLE ACTIONS

Each article card should provide:

```text
Modifier
Supprimer
```

Actions must remain visually clear.

The delete action should use a confirmation interface.

Example:

```text
Supprimer cet article ?

Cette action est irréversible.

[Annuler] [Supprimer]
```

Do not delete an article immediately without confirmation.

---

# 9. ADD ARTICLE

Improve the existing:

```text
ajouter.php
```

Create a modern form.

The form must use the existing article information.

Current article information includes:

```text
titre
description
prix_depart
date_debut
date_fin
id_utilisateur
id_type
```

Do not invent unnecessary article fields.

---

# 10. ARTICLE IMAGE

Add the possibility to upload an image for each article.

The user should be able to:

```text
Choisir une image
```

from the computer.

Create:

```text
uploads/
```

to store uploaded images.

The image filename/path should be stored in the database.

The image should then appear on the article card.

---

# 11. DATABASE CHANGE FOR IMAGE

The existing `article` table currently needs an image field if it does not already exist.

If necessary, use:

```sql
ALTER TABLE article
ADD image VARCHAR(255) NULL;
```

IMPORTANT:

Before executing this change, check whether the `image` column already exists.

Do NOT create a duplicate column.

Do NOT change other database fields.

Do NOT change the existing relationships.

---

# 12. EDIT ARTICLE

Improve:

```text
modifier.php
```

The edit form must contain the existing article information.

The user must be able to modify:

* titre
* description
* prix_depart
* date_debut
* date_fin
* image

If an existing image exists:

Display a preview.

Allow the user to replace it.

Do not delete the existing image from the database incorrectly.

---

# 13. DELETE ARTICLE

Keep:

```text
supprimer.php
```

The deletion must continue to work with the existing database.

Add a professional confirmation interface.

Example:

```text
Supprimer cet article ?

Êtes-vous sûr de vouloir supprimer cet article ?

[Annuler]
[Supprimer]
```

Use a clear destructive style for the final delete button.

---

# 14. AUCTION OFFERS

The existing database contains:

```text
offre
```

Use this existing entity for auction offers.

Do NOT create another table for bids.

Do NOT rename the table.

Do NOT invent another auction entity.

The interface should allow the existing auction workflow to evolve toward:

```text
Article
    ↓
Offres
    ↓
Montant
    ↓
Enchérisseur
```

If implementing the offer functionality requires information that is missing from the current UI, inspect the existing database first.

Do not invent business rules.

---

# 15. AUCTION CARD DESIGN

Create cards that visually communicate:

```text
┌──────────────────────────────┐
│                              │
│          IMAGE               │
│                              │
├──────────────────────────────┤
│ Titre de l'article           │
│                              │
│ Description courte...        │
│                              │
│ Prix de départ               │
│ 350 DH                       │
│                              │
│ Temps restant                │
│ 01j 05h 32m 18s              │
│                              │
│ [Modifier] [Supprimer]       │
└──────────────────────────────┘
```

Make the cards visually attractive without becoming overloaded.

---

# 16. DESIGN STYLE

The visual identity should look like a **real modern auction marketplace**.

Style:

* Modern
* Professional
* Clean
* Elegant
* Minimal
* Premium
* Responsive
* Easy to understand

The design should communicate:

**Enchères / marketplace / competition / countdown**

without looking like a casino or gambling website.

Avoid:

* excessive gradients
* excessive animations
* glassmorphism everywhere
* huge typography
* excessive shadows
* too many colors
* crowded cards

---

# 17. COLOR PALETTE

Use a professional auction-inspired palette.

Suggested direction:

### Primary

Deep navy / dark blue

### Secondary

Blue

### Accent

Gold / amber

Use the accent color carefully for:

* auction highlights
* countdown
* price
* important actions

### Background

Very light gray / off-white

### Cards

White

### Text

Dark gray / navy

### Success

Green

### Danger

Red

The interface should not become overly colorful.

---

# 18. TYPOGRAPHY

Use a modern readable font.

Recommended:

```text
Inter
```

Typography hierarchy:

### Main title

Large and bold.

### Article title

Medium/semibold.

### Price

Strong and visually important.

### Countdown

Highly visible but not huge.

### Description

Smaller muted text.

Maintain clean spacing.

---

# 19. COUNTDOWN VISUAL DESIGN

The countdown should be one of the main visual elements.

Example:

```text
TEMPS RESTANT

01
J
05
H
32
M
18
S
```

or a compact version:

```text
01j 05h 32m 18s
```

Choose the design that best matches the card.

When the deadline is approaching, the countdown can receive a subtle warning visual state.

Example:

```text
00j 00h 12m 34s
```

Do not introduce a new business rule.

Only use the visual effect.

---

# 20. PRICE DESIGN

Make prices visually prominent.

Example:

```text
Prix de départ

350 DH
```

If the current highest offer is available from the existing offer system, it may be displayed as:

```text
Offre actuelle

420 DH
```

But only if the existing application logic supports this.

Do not invent fake auction data.

---

# 21. RESPONSIVE DESIGN

The website must be fully responsive.

## Desktop

Display several auction cards per row.

Example:

```text
[ Card ] [ Card ] [ Card ]
[ Card ] [ Card ] [ Card ]
```

## Tablet

Use fewer cards per row.

## Mobile

Cards should stack:

```text
[ Card ]

[ Card ]

[ Card ]
```

The header must also adapt to mobile.

Buttons must remain easy to use.

Images must remain proportional.

Countdown must remain readable.

---

# 22. EMPTY STATE

If there are no articles:

Display:

```text
Aucune enchère disponible
```

with:

```text
+ Ajouter un article
```

button.

Make the empty state visually clean.

Do not display fake articles.

---

# 23. SUCCESS MESSAGES

After successful operations, display simple feedback.

Examples:

```text
Article ajouté avec succès.
```

```text
Article modifié avec succès.
```

```text
Article supprimé avec succès.
```

Keep messages subtle.

---

# 24. ERROR MESSAGES

Errors should be understandable.

Do not display:

* PHP stack traces
* SQL errors
* database credentials
* technical file paths

to the normal user.

Use messages such as:

```text
Une erreur est survenue.
```

or a specific understandable validation message.

---

# 25. FORMS DESIGN

All forms must use the same design system.

Use:

* labels above inputs
* clean input fields
* consistent spacing
* rounded borders
* focus states
* primary button
* secondary button

Example:

```text
Titre
[________________________]

Description
[________________________]
[________________________]

Prix de départ
[________________________]

Date de début
[________________________]

Date de fin
[________________________]

Image
[ Choisir une image ]

[Annuler] [Ajouter l'article]
```

---

# 26. BUTTON DESIGN

Create a consistent button system.

Primary:

```text
+ Ajouter un article
```

Secondary:

```text
Modifier
```

Danger:

```text
Supprimer
```

Neutral:

```text
Annuler
```

Use icons only when useful.

Do not make buttons excessively large.

---

# 27. NAVIGATION

Keep navigation simple.

Main navigation can contain:

```text
Accueil
Ajouter un article
```

Do not add:

* Admin
* Login
* Register
* Profile
* Payments
* Notifications
* Messages
* Settings

unless they already exist in the current project.

---

# 28. JAVASCRIPT

Use only Vanilla JavaScript.

JavaScript can handle:

* countdown timers
* delete confirmation
* image preview
* small UI interactions
* responsive menu if necessary

Do not move database logic into JavaScript.

Do not create a SPA.

Do not use React/Vue/Angular.

---

# 29. IMAGE PREVIEW

When selecting an image in `ajouter.php` or `modifier.php`, display a small preview before submitting.

Example:

```text
[ Select image ]

       ↓

[ IMAGE PREVIEW ]
```

Use Vanilla JavaScript.

Keep it simple.

---

# 30. CSS ARCHITECTURE

Improve the existing:

```text
style.css
```

Do not create an unnecessarily complicated CSS architecture.

Use reusable classes and CSS variables.

Example:

```css
:root {
    --primary: ...;
    --primary-dark: ...;
    --accent: ...;
    --background: ...;
    --surface: ...;
    --text: ...;
    --muted: ...;
    --border: ...;
    --success: ...;
    --danger: ...;
    --radius: ...;
}
```

Keep CSS readable and maintainable.

---

# 31. EXISTING CODE PRIORITY

Before modifying anything:

1. Inspect the existing files.
2. Inspect the existing database structure.
3. Understand the current CRUD.
4. Keep the working PHP logic.
5. Improve the UI.
6. Add only the requested image/countdown functionality.
7. Test everything.

Do NOT rewrite working code unnecessarily.

---

# 32. DATABASE PRIORITY

The database is the source of truth.

Existing tables:

```text
utilisateur
type_lot
article
offre
```

Respect:

* primary keys
* foreign keys
* existing relationships
* existing column names

Do not rename tables.

Do not delete tables.

Do not create duplicate tables.

Do not create unnecessary relationships.

If a modification is necessary, clearly tell me what SQL command must be executed.

---

# 33. CURRENT SIMPLE PROJECT ARCHITECTURE

Keep the project understandable.

Current structure:

```text
plateforme-encheres/
│
├── connexion.php
├── index.php
├── ajouter.php
├── modifier.php
├── supprimer.php
├── style.css
│
└── uploads/
```

Do not transform this into a huge MVC framework.

This is a student project and the code must remain understandable.

---

# 34. TECHNOLOGY RESTRICTIONS

Use only:

### Backend

* PHP
* PDO
* MySQL

### Frontend

* HTML5
* CSS3
* Vanilla JavaScript

Do NOT use:

* React
* Vue
* Angular
* Laravel
* Symfony
* Bootstrap
* Tailwind
* jQuery
* Node.js
* Composer
* external frontend frameworks

---

# 35. UX GOAL

The final interface should feel like a real auction marketplace.

The user should immediately understand:

* what articles are available
* which auctions are active
* how much the starting price is
* how much time remains
* which article they can manage
* how to add an article
* how to modify an article
* how to delete an article

The visual hierarchy should prioritize:

1. Article image
2. Article title
3. Price
4. Countdown
5. Actions

---

# 36. FINAL RESULT

The final application should visually resemble a modern professional auction platform.

Example structure:

```text
┌─────────────────────────────────────────────────────┐
│  PLATEFORME D'ENCHÈRES        + Ajouter un article │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Découvrez les articles aux enchères               │
│                                                     │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐    │
│  │   IMAGE    │  │   IMAGE    │  │   IMAGE    │    │
│  │            │  │            │  │            │    │
│  ├────────────┤  ├────────────┤  ├────────────┤    │
│  │ Article 1  │  │ Article 2  │  │ Article 3  │    │
│  │ Description│  │ Description│  │ Description│    │
│  │ 350 DH     │  │ 500 DH     │  │ 800 DH     │    │
│  │ 02j 04h... │  │ 01j 12h... │  │ 00j 05h... │    │
│  │            │  │            │  │            │    │
│  │ Modifier   │  │ Modifier   │  │ Modifier   │    │
│  │ Supprimer  │  │ Supprimer  │  │ Supprimer  │    │
│  └────────────┘  └────────────┘  └────────────┘    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

The exact layout can be adapted according to the existing project.

---

# 37. MOST IMPORTANT RULE

Do not change the application's meaning.

The existing project is:

```text
Simple Auction Platform
        ↓
One user
        ↓
View articles
        ↓
Add article
        ↓
Modify article
        ↓
Delete article
        ↓
Auction countdown
        ↓
Auction offers
```

Keep this workflow.

Do not add authentication.

Do not add an admin dashboard.

Do not add payments.

Do not add unrelated features.

Do not invent business rules.

Do not change the database relationships.

Improve the **visual design and requested auction functionality**, not the application's purpose.

---

# FINAL OBJECTIVE

Transform the current basic PHP auction project into a:

**Modern + Professional + Responsive + Clean Auction Platform**

using:

```text
PHP
MySQL
PDO
HTML
CSS
Vanilla JavaScript
```

with:

```text
Modern auction cards
Responsive layout
Article images
Image preview
Real-time countdown
Auction status
Modern buttons
Delete confirmation
Clean forms
Professional colors
Responsive mobile design
```

while preserving the existing application logic and database.

**Inspect first. Modify second.**

**Do not rewrite working functionality unnecessarily.**

**Do not invent features.**

**Do not change the workflow.**
