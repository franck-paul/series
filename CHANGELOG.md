# Changelog

## 12.0 — 2026-11-08

- Core/Url::getPageNumber() returns false or positive-int
- DC 2.40+
- Use getter methods rather than widget properties as far as possible
- Cope with adminSimpleMenuBeforeEditV2 behavior instead of adminSimpleMenuBeforeEdit, 2.40+
- Cope with RTL
- Enforces some verification before registering module

## 11.0 — 2026-08-03

- To soon for that!
- Cope with current user prefs and other user prefs
- Cope with specific setting for user created or modified
- Use App::url()-&gt;isType() where it is relevant
- Avoid using magic _get(), _set() methods of MetaRecord (2.39+)
- Avoid using magic methods for user preferences and blog settings (2.39+)
- Use MetaRecord::strField(), MetaRecord::intField() and MetaRecord::boolField() whenever is possible
- Use MetaRecord::strField(), MetaRecord::intField() and MetaRecord::boolField() whenever is possible Fav Icon
- Autres correctifs et améliorations.

## 10.1 — 2026-05-15

- Add support of glaz template set
- Switch from jQuery to Vanilla JS (AFAP) when using dotclear.MetaEditor
- Use dotclear.ToolBar rather than old jsToolBar, DC 2.39+
- Cope with dotclear.MetaEditor (2.38) or metaEditor (2.37). Avoid using global variable to store MetaEditor instance.

## 10.0.1 — 2026-05-04

- array or ArrayObject ;-)

## 10.0 — 2026-04-29

- Use new MetaRecord::cardinal() method

## 9.5 — 2026-04-09

- Add a tpl:EntryIf has_series attribute to check if the current entry in in one or more series

## 9.4 — 2026-04-06

- Add feeds class for feed list container

## 9.3 — 2026-04-01

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 9.2 — 2026-03-25

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 9.1 — 2026-03-11

- Use list rather than p for list of feeds

## 9.0 — 2026-02-13

- No more need of shortkey (2.37)

## 8.8 — 2025-12-12

- Add key attribute for toolbar button in order to ignore keyboard layout (2.37+) Keep shortkey in order to keep 2.36 compliance

## 8.7.1 — 2025-12-03

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.7 — 2025-12-01

- Add a Collapse entry list option (use details/summary for entry list)

## 8.6 — 2025-10-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 8.5 — 2025-09-22

- Use backend services rather than static calls

## 8.4 — 2025-09-15

- Use legacyEditor groups management

## 8.3 — 2025-09-15

- Add spacer around toolbar button (waiting for group management)
- Remove spacer separator in toolbar

## 8.2 — 2025-09-12

- Replace static call to Ctx by context() — thanks JcDenis for advice
- Use array rather than ArrayObject for new ArrayObject value of simpleMenu choice

## 8.1 — 2025-09-07

- DC 2.36 compliance

## 8.0 — 2025-09-06

- DC 2.36 compliance

## 7.6 — 2025-07-14

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 7.5 — 2025-06-05

- Add legacyEditor dark icon

## 7.4 — 2025-06-01

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 7.3 — 2025-05-05

- Use Strong(…) rather than Text('strong', …)

## 7.2 — 2025-05-04

- Use Helper\Html\Form\Span rather than Text('span',…)

## 7.1 — 2025-04-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 7.0.1 — 2025-04-04

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 7.0 — 2025-03-17

- Use TemplateHelper for template blocks/values DC 2.34+

## 6.5 — 2025-03-05

- Plugins adding buttons on legacyEditor/CKEditor should have at least 1010 priority 1005 is reserved for additional syntaxes to legacyEditor (as legacyMarkdown)

## 6.4 — 2025-02-27

- Add {{tpl:SerieCount}} template tag (value)

## 6.3 — 2025-02-26

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.2 — 2025-02-20

- Switch from legacy form:: to Help/Html/Form/...

## 6.1 — 2025-01-11

- Be conservative (will see in future if something more is needed)

## 6.0 — 2025-01-09

- 2.33 compliance
- No need to test content as preg_match pattern is deterministic

## 5.13 — 2024-12-24

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.12 — 2024-12-24

- No more stats (anywhere)

## 5.11 — 2024-12-21

- Remove stats on autocomplete list

## 5.10 — 2024-08-23

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.9 — 2024-06-29

- Use lexical sorting for series

## 5.8 — 2024-05-30

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.7 — 2024-03-17

- Switch CKEditor icon from PNG to SVG
- ID is not necessary for the label

## 5.6.1 — 2023-12-05

- Fix actions on posts, fixes #2
- Switch from XHTML to HTML5 for self-closing tags

## 5.6 — 2023-11-29

- Use JSON format for REST service rather than XML

## 5.5 — 2023-11-04

- Replace form (legacy) by Form

## 5.4 — 2023-10-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.3 — 2023-10-25

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.2 — 2023-10-25

- Simplify template dir management

## 5.1 — 2023-10-25

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.0 — 2023-10-24

- Use instanced item constants rather than interface ones
- Avoid magic method as far as possible
- Use 2.28 Config methods
- 2.28 compliance

## 4.0 — 2023-08-26

- post commit
- Dotclear 2.27 compliance, PHP 8.1+

## 3.4.1 — 2023-06-27

- Add local static $init variable

## 3.4 — 2023-06-20

- Use dcCore::app()-&gt;adminurl-&gt;redirect() rather than Http::redirect()

## 3.3 — 2023-06-19

- Use admin URLs

## 3.2 — 2023-06-15

- Add void Install.php

## 3.1 — 2023-06-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 3.0.1 — 2023-06-08

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 3.0 — 2023-06-08

- Switch to namespace
- Prepare 2.26
- Prepare 2.25
- Minified versions
- Move css in folder

## 2.1 — 2022-12-18

- 2.24 compliance

## 2.0 — 2022-11-30

- Dotclear 2.24 compliance
- Admin lists V2
- Admin actions V2

## 1.3.1 — 2022-08-14

- Usual oups :-)

## 1.3 — 2022-08-11

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 1.2 — 2021-12-09

- Update default branch in _define.php for alternative repository
- Add alternative repository

## 1.1 — 2021-08-07

- Dotclear 2.19 compliance

## 1.0.2 — 2021-05-07

- Fix notice on some template (post)
- Use on(custom_event, ...) rather than obj method

## 1.0.1 — 2020-03-24

- 1.0.1 rather than 1.1
- Publish new release

## 1.0 — 2020-02-28

- Adapt widget code, add support and details URLs, remove obsolete code
- Remove inline JS
- Meta editor (used by Series plugin), let specify a unique id for input (post_meta_input by default)
- Cope with new static home page mode (2.16+)
- Use standard jquery.autocomplete rather than plugin embedded's
- Use let and const rather than var (ES2015/ES6)
- short notation for array (array() → [])

## 0.9 — 2018-08-13

- Remove pre-2.14 code
- Publish 0.9 release
- Préparation 2.14

## 0.8 — 2018-03-16

- Bugfix and 0.8
- Prepare future 2.14 (using new "order by" criteria)
- Actually I prefer single quote!
- More readable this way
- Do not double quote values in SQL queries (specially for PostgreSQL), thanks Da Scritch for report
- Revert back to 255 char
- UTF8-mb4 compatibility, reduce serie_id lentgh from 255 to 191 (stored in meta table)
- Use getURLFor() rather than getBase() deprecated function, min Dotclear version is now 2.11

## 0.7 — 2016-12-28

- Nore more Dublin-core nor CDATA

## 0.6 — 2016-08-16

- Add dotty templateset, dc 2.10 min, next release will be 0.6

## 0.5.1 — 2016-08-03

- Using dcPage::cssLoad, dcPage::jsLoad and dcPage::getPF is a better way to load plugin resources
- Remove unnecessary code.

## 0.5 — 2015-07-20

- span …
- Take profit of lexical sort order
- Add syntax parameter for adminPostEditor behaviour for Dotclear 2.8
- Deal with updated mustek templateset, min version for dotclear will be 2.8
- Add {{tpl:Breadcrumb}} tag in mustek template
- Add Breadcrumb support
- Misuses of jsToolBar object with dcCKEditor
- Split behaviors registration, will be compatible with Dotclear 2.8 min
- Autres correctifs et améliorations.

## 0.4.2 — 2015-01-13

- Set lower priority than dcLegacyEditor in order to register macro function

## 0.4.1 — 2014-12-10

- We should not use blog URL for admin purpose

## 0.4 — 2014-11-20

- Add confirmation dialog before leaving modified form
- Add "offline" option on widgets
- Enable "serie" button for CKEditor, only for posts
- Add series button on CKEditor toolbar (but disabled until some fixes occured, specially for tags plugin)
- Cope with templaset (mustek + currywurst), switch to HTML5
- Add widget class on every widget, see #1901
- New rendering method for widget title (2.7 min)
- Add serie-current class of related post's series in widget, next release will be 0.4

## 0.3.1 — 2013-11-14

- Gestion des options de meta-editor.js (dotclear 2.6.1 min)
- Fixed notice in serie_posts.php

## 0.3 — 2013-10-15

- new _define.php
- Cope with new favorites system
- New toolbar icon
- New icons
- New notices system
- jquery.autocomplete 1.2.3 has changed its default options, restored multipleSeparator into js/post.js
- Cope with revision [2169]
- Cope with new breadcrumb
- Autres correctifs et améliorations.

## 0.2 — 2013-03-16

- Removed tag 0.2
- Version 0.2
- metaEditor meta_url may be used more than once, should not be set on prototype but on instance
- Avoid undefined variable and index notices
- Widget may now be displayed without inclusive div
- harmonize &lt;label&gt;&lt;/label&gt; in forms
- Now uses core message function (dcPage::message)
- Add widgets description
- Autres correctifs et améliorations.

## 0.1.1 — 2012-09-26

- Bugfix : position des éléments de pagination

## 0.1 — 2012-05-21

- Bugfix
- Petite icône de Noé
- Grande icône de Noé et nouveau terme pour la traduction de Siblings
- Choix du format de la liste des série en édition de billet
- Icône ajout
- Modif des libellés
- Nouvelles icônes
- Widget serie(s) courante(s)
- Autres correctifs et améliorations.
