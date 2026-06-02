# Block-System

Modulares Sektionen-System. Jede Seite (Landingpage, Unterseite, Agenturliste, Agentur-Detail) wird aus wiederverwendbaren Blocks zusammengesetzt. Per Drag & Drop im Panel sortier- und konfigurierbar.

## Verfügbare Blocks (Stand: heutige Übergabe)

| Block | Wofür | Ersetzt (alt) |
| --- | --- | --- |
| **Hero** | Großer Video-Header mit Headline + optionalem Inline-Icon | `.hero` Sektion |
| **Hero-Foto** | Eingangs-Block mit Foto-Tile statt Video (Headline links, Bild rechts) | (Neu) |
| **Split-Intro** | Zweispaltig: Headline + Bild ◀ ▶ Sub + Body + CTAs | `.intro` Sektion |
| **Highlight-Text (Manifest)** | Lange Absätze mit Inline-Icons via `{icon:name}` Tokens | `.manifest` Sektion |
| **Headline-Solo** | Einzelne Headline-Sektion mit optionalem Kicker + Sub-Text (left/center/right) | (Neu) |
| **Editorial Split-Intro** | Headline + 1/2/3-Spalten Text + optionales Bild | `.expect` / `.year` / `.values` Sektionen |
| **Editorial Plain 2-Col** | Reiner 2-Spalten-Fließtext ohne Headline (optional Kicker) | (Neu) |
| **Editorial Image + Text** | Bild links/rechts + Headline + Body + optionaler CTA | (Neu) |
| **Editorial Bullet-List** | Headline + optionale Sub + Bullet-Liste im 4er-Grid (blaue Punkte) | `.audience` / `.outcome` Sektionen |
| **Bild (mit Schatten)** | Einzelnes Bild mit konfigurierbarem blauen Schlagschatten | Image-only Sektion |
| **Bild-Grid** | Mehrere Bilder im 4er-Grid mit col/row/span pro Bild + optionalem Schatten | (Neu) |
| **Team-Cards (Information-Images)** | Karten-Grid mit Foto + Name + Position, optional verlinkt | (Neu) Team/Mentor:innen |
| **Work-Showcase** | Case-Sektion: Video oder 2/3 Bilder + Headline + 2-spaltiger Text | (Neu) Agentur-Cases |
| **Icon-Divider** | Großes zentriertes Icon als Trenner zwischen Sektionen | (Neu) |
| **Agentur-Grid mit Filter** | Auto-Liste aller Agentur-Unterseiten mit Filter (Standort/Arbeitsweise/Schwerpunkte/Größe) | (Neu) Nur auf agenturen-Seite |
| **Q & A (FAQ)** | Frage-Antwort-Listen mit optionalen Themengruppen | FAQ-Seite |
| **Conversion (CTA-Cards)** | Abschluss mit Headline + Bild + mehreren CTA-Karten | `.cta-final` Sektion |

## Pro Block — was immer da ist

Jeder Block hat in seinem Panel-Formular drei (Bilder-Blocks: vier) Tabs:

**Tab "Inhalt"** — die spezifischen Felder des Block-Typs (Headlines, Texte, Bilder, Strukturen).

**Tab "Layout"** — geteilte Wrapper-Einstellungen:
- **Hintergrund**: Hell / Mint / Blau
- **Anker-ID**: für #links (`#about`, `#contact` etc.)
- **Abstand oben / unten**: jeweils in **Pixeln pro Breakpoint** — Mobile, Tablet, Desktop. Leer = SCSS-Default.

**Tab "Section-Icon"** — optionales dekoratives Icon, das frei auf der Section positioniert werden kann:
- **Aus Bibliothek wählen**: Select aus den 20 Standard-Icons. Bilder kommen automatisch aus `/public/icons/png/<bp>/<key>.png`.
- **Eigene Uploads** pro Breakpoint (Mobile / Tablet / Desktop) — Override. Z. B. dickere Strichstärken auf Mobile.
- Leere Breakpoints erben in der Kaskade: mobile → tablet → desktop.
- **Position pro Breakpoint**: X (px), Y (px), Größe (px), Rotation (°). Position ist relativ zur oberen-linken Ecke der Section.
- **Animation**: Waber (4 Richtungen) / Glide (sanft) / Keine — plus Dauer + Verzögerung in Sekunden. Default: Waber 6.5s.

**Tab "Bild-Schatten"** (nur bei Blocks mit Bild) — steuert den blauen Backdrop-Shadow:
- **Pro Breakpoint** je vier Werte: Schatten X, Schatten Y, Schatten °, Bild ° (Rotation des Bildes selbst).
- Default: SCSS-Werte aus dem Stylesheet.
- **Scroll-Animation**: JS animiert Rotation + Shadow von 0 bis zu den eingestellten Desktop-Werten beim Reinscrollen. Rotationspunkt ist das Bild-Zentrum.

## Welche Blocks gibt's auf welcher Seite?

Die Whitelist liegt im jeweiligen Page-Blueprint unter `fieldsets:`.

| Page-Typ | Datei | Verfügbare Blocks |
| --- | --- | --- |
| Landingpage (Home) | `site/blueprints/pages/home.yml` | Hero, Split-Intro, Highlight-Text, Editorial-Split, Editorial-Bullet, Image, Q&A, Conversion |
| Unterseite | `site/blueprints/pages/default.yml` | Alle außer Hero und Agentur-Grid |
| Agenturen-Übersicht | `site/blueprints/pages/agenturen.yml` | Split-Intro, Image, **Agentur-Grid**, Editorial-Split, Icon-Divider, Conversion |
| Agentur-Detail | `site/blueprints/pages/agency.yml` | Split-Intro, Editorial-Split, Editorial-Bullet, Image, **Image-Grid**, **Work-Showcase**, **Icon-Divider**, Q&A, Conversion |

Der **Agentur-Grid Block** ist nur auf der `agenturen`-Seite verfügbar. Er liest automatisch alle Agentur-Unterseiten und stellt sie ins Grid.

**Pattern**: Das Grid wiederholt sich automatisch alle 4 Zeilen. 12 Content-Positions pro Block (4 Cells bleiben leer für visuellen Rhythmus). Björn pflegt keine Position pro Agentur — sie ergibt sich aus der Reihenfolge der Agentur-Childpages.

**Text-Position-Toggle** (im Block-Settings):
- **Drunter** (Default): Bild oben, Label drunter — Pattern aus 4-Zeilen-Block mit Lücken
- **Rechts**: Bild links, Text rechts (Tile braucht 2 Spalten) — natürlicher Flow ohne Pattern

**Filter-Animation**: Sobald mindestens ein Filter aktiv ist, gibt das Grid seine Pattern-Positionen auf. Sichtbare Tiles rücken sanft nach oben zusammen (CSS-Animation `agencyTileSlideIn`). Bei Filter-Reset: zurück ins Pattern. Mehrere Filter über verschiedene Spalten kombinieren UND-logisch, mehrere Werte innerhalb einer Spalte ODER-logisch.

**Pattern-Tuning**: Die Pattern-Positionen liegen im PHP-Snippet (`site/snippets/blocks/agency-grid.php`) als Array. Pro Pattern-Slot **fünf Format-Varianten**:

```php
$patternPositions = [
    [1, 1, 'below'],         // ◀ Shortcut: Bild + Text drunter (col, row+1)
    [3, 1, 'right'],         // ◀ Shortcut: Bild + Text rechts daneben (col+1, row)
    [2, 1, 4, 1],            // Explizite Text-Position (frei wählbar, z. B. mit Lücke)
    [3, 1],                  // NUR Bild (kein Text)
    [null, null, 4, 2],      // NUR Text (kein Bild)
];
$patternRows = 4;   // nach wie vielen Zeilen wiederholt sich das Pattern
```

**Mix erlaubt**: drunter + rechts beliebig in einem Pattern kombinieren. So entstehen die wechselnden Tile-Anordnungen aus dem Designer-Mockup (manche Bilder mit Label unten, manche mit Label rechts daneben).

So lassen sich auch pure Bild-Akzente (großes Image als Ankerpunkt ohne Label) oder pure Text-Tiles (Statement-Block) ins Raster einstreuen, ohne dass eine Agentur doppelt dargestellt wird. Wenn weniger Agenturen als Pattern-Slots da sind, werden überzählige Pattern-Positionen einfach ausgelassen.

**Zusätzlichen Block für eine Seite freischalten**: Block-Name (Datei-Name ohne `.yml`) in der entsprechenden `fieldsets:`-Liste ergänzen. Order in der Liste = Sortierung im "+ Block einfügen"-Menü.

## Icon-Bibliothek

20 vorbereitete Icons liegen in `/public/icons/png/<bp>/<key>.png` mit `bp = mobile | tablet | desktop`. Die Variante pro Breakpoint hat eine angepasste Linienstärke.

Verfügbare Keys: `airplane`, `bag`, `bottle`, `cloud`, `comet`, `document`, `glasses`, `glasses-bold`, `leaf`, `lightbulb`, `lightning`, `pencil`, `puzzle`, `shooting-star`, `spark`, `sparkles`, `sparkles-line`, `star`, `starlight`, `swirl`.

**Wo die Library zum Einsatz kommt:**
- Im Section-Icon Tab jedes Blocks (Select "Aus Bibliothek wählen")
- Im Bullet-Icon der Editorial Bullet-List (Select "Aus Bibliothek")
- Im Hero-Block (Select "Icon aus Bibliothek")

**Ein eigenes Icon hinzufügen**: PNG in alle drei `mobile/tablet/desktop` Ordner legen, gleicher Dateiname (`<key>.png`). Dann den `<key>` in der entsprechenden Select-Liste im Blueprint ergänzen (`site/blueprints/sections/block-icon-tab.yml`).

**Override pro Section**: Wenn man in einem konkreten Block ein anderes Icon braucht als das aus der Library, kann man im Section-Icon-Tab pro Breakpoint eine eigene Datei hochladen. Uploads gewinnen immer über den Library-Key.

## Bullet-Icon im Editorial Bullet-List

Default ist ein blauer Kreis (CSS-gezeichnet, kein Bild). Optional kann pro Block:
- Ein **Library-Key** ausgewählt werden (verwendet `/icons/png/desktop/<key>.png`)
- Eine **eigene Datei** hochgeladen werden (Override über die Library-Auswahl)

## Hero-Icon

Im Hero-Block:
- **Icon aus Bibliothek**: Select aus den 20 Standard-Keys → lädt `/icons/png/desktop/<key>.png`
- **Hero-Icon eigene Datei**: optionaler File-Upload, der die Library-Auswahl überschreibt

Wenn beides leer ist, wird kein Inline-Icon zwischen den zwei Wörtern der Headline gerendert.

## Inline-Icons im Highlight-Text

Bei `highlight-text` (Manifest) sind Icons direkt im Text platzierbar:

```
Kreativität verändert sich. Durch KI schneller {icon:airplane}
als je zuvor. Doch nicht die Werkzeuge entscheiden über kreative
{icon:shooting-star} Qualität.
```

Das `{icon:airplane}` Token wird beim Rendern durch das hochgeladene PNG/SVG ersetzt. Die verfügbaren Tokens kommen aus dem **Icon-Pool** der gleichen Section (Tab "Inhalt" → "Icon-Pool"):

- **Token-Name**: was im Text steht (`airplane`, `lightbulb`, …). Nur Kleinbuchstaben + Bindestriche.
- **PNG/SVG**: das hochgeladene Icon.

Jede Highlight-Text-Section hat ihren **eigenen Icon-Pool** — keine globale Bibliothek mehr.

## Architektur — wo lebt was?

```
site/
├── blueprints/
│   ├── blocks/                  # Panel-Schemata pro Block-Typ
│   │   ├── hero.yml
│   │   ├── split-intro.yml
│   │   ├── highlight-text.yml
│   │   ├── editorial-split-intro.yml
│   │   ├── editorial-bullet-list.yml
│   │   ├── image.yml
│   │   ├── question-answers.yml
│   │   └── conversion-section.yml
│   ├── sections/                # Geteilte Tab-Partials (via extends:)
│   │   ├── block-layout-tab.yml
│   │   ├── block-icon-tab.yml
│   │   └── block-shadow-tab.yml
│   └── pages/                   # Page-Blueprints mit builder-Feld + fieldsets-Whitelist
│       ├── home.yml
│       ├── default.yml
│       ├── agenturen.yml
│       └── agency.yml
├── snippets/
│   └── blocks/                  # PHP-Renderer pro Block-Typ
│       ├── hero.php
│       ├── split-intro.php
│       └── … (1:1 zu blueprints/blocks/*.yml)
└── plugins/
    └── soi-blocks/
        └── index.php            # Geteilte Helper:
                                 #   soi_text, soi_html, soi_paragraphs,
                                 #   soi_file_url, soi_section_attrs,
                                 #   soi_section_icon, soi_image_shadow_style,
                                 #   soi_inline_icon, soi_icon_text,
                                 #   soi_block_icon_pool

scripts/
└── migrate-home-to-blocks.php   # Einmalig: Alte Home-Felder → Builder-JSON
```

## Migrations-Scripts

Alle laufen idempotent und legen vor dem Schreiben ein `.bak-YYYYMMDD-HHMMSS`-Backup an.

```bash
# 1) Home: alte hardcoded Felder → Builder-Blocks (9 Blocks erzeugt)
ddev exec php /var/www/html/scripts/migrate-home-to-blocks.php
ddev exec php /var/www/html/scripts/migrate-home-to-blocks.php --dry-run

# 2) Home: Section-Icon iconKey + Shadow-Werte + Wobble-Defaults prefillen
#    (nur für Felder die noch leer sind — überschreibt keine Panel-Edits)
ddev exec php /var/www/html/scripts/patch-home-prefills.php

# 3) Unterseiten: alte "sections" structure → Builder-Blocks
#    (about, programm, bewerbung, faq, datenschutz, impressum)
ddev exec php /var/www/html/scripts/migrate-subpages-to-blocks.php
ddev exec php /var/www/html/scripts/migrate-subpages-to-blocks.php --dry-run

# 4) Agenturen-Übersicht: Default-Builder (agency-grid + conversion) anlegen
ddev exec php /var/www/html/scripts/seed-agenturen-builder.php

# 5) Existierende Agenturen: gridCol/gridRow/gridSpan + Filter-Metadaten
#    (arbeitsweise, groesse, schwerpunkte, intro) mit Defaults befüllen
ddev exec php /var/www/html/scripts/seed-agency-meta.php

# 6) Demo-Agentur (nova-kollektiv) mit 10 Beispiel-Blocks befüllen,
#    damit der Designer das fertige Schema sieht
ddev exec php /var/www/html/scripts/seed-demo-agency-builder.php
```

## Einen neuen Block-Typ anlegen

1. **Blueprint**: `site/blueprints/blocks/<name>.yml` schreiben
   - Mit `tabs:` strukturieren (`content`, `layout`, `icon`, optional `shadow`)
   - `layout` und `icon` per `extends: sections/block-layout-tab` (bzw. `block-icon-tab`) reinholen — sonst hast du nicht die Standard-Wrapper-Felder
2. **Snippet**: `site/snippets/blocks/<name>.php` schreiben
   - Top-Level `soi_section_attrs($block, 'cssBaseClass')` für die `<section>`-Attribute
   - Bestehende SCSS-Klassen wiederverwenden ODER neues SCSS in `src/scss/_content.scss`
3. **Page-Blueprint(s)**: den neuen Block-Namen unter `fieldsets:` ergänzen, wo er nutzbar sein soll
4. **Build**: `npm run build` (oder `npm run dev` während Designer arbeitet)

## Was heute NICHT migriert wurde

- **Motiv-Stack** (die animierte Wolken-Divider-Sektion zwischen Values und Audience). Kann später als eigener `divider`-Block angelegt werden.
- **Internal Gap-Variablen** (z. B. `intro-motiv-gap`, `manifest-paragraph-gap`). Aktuell SCSS-Defaults. Wenn der Designer pro Block fein-tunen will → in den jeweiligen Block-Blueprint zusätzliche `number`-Felder einbauen + im Snippet als CSS-Variablen ausspielen.
- **Mehrere Float-Icons pro Section** (alte `decorativeIcons` Struktur hatte bis zu 4 Icons pro Sektion). Aktuell hat jeder Block nur EINEN Section-Icon. Wenn mehrere gewünscht: entweder das Section-Icon-Tab zu einer Struktur (Repeater) machen ODER mehrere Blocks übereinander.

## Nächste Sektionen (laut Designer-Screenshot)

Aus dem heutigen Treffen noch offen:
- `headline-solo` (einzelne Headline-Sektion ohne Body)
- `editorial-plain-2col` (Zweispaltiger Text ohne Headline)
- `editorial-image-text` (Bild links, Text rechts oder umgekehrt)
- `information-images` (Team-/Agenturkarten mit Bild + Name + Position)
- `icon-divider` (großes dekoratives Icon als Trenner)
- `hero-foto` (Hero mit Foto statt Video)

Pattern ist überall gleich: Blueprint + Snippet + in `fieldsets:` der Page-Blueprints freischalten.
