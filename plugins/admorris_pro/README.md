## Systemvoraussetzungen:

**Bitte beachten Sie, dass Sie vor dem Update des Shops auf Version 5.5 mindestens die admorris Pro Version 3.0.1 installiert haben müssen.**

- Bis JTL-Shop  5.5.3
- PHP 8.2 - 8.3
- Bis JTL Shop 5.0.3 ist der ionCube Loader notwendig
- ZipArchive
- Apache Server   
  (für NGINX müssen Sie selber alle notwendigen .htaccess Einstellungen übernehmen)
- SSL Zertifikat (gilt für SmartSearch und Push Notifications)
- allow_url_fopen aktiviert
- Maximale PHP Übertragungsgröße (FILE/POST) mindestens 16 MB
- 350 MB freier Speicher
- Ausgehender Port 9243 darf bei Verwendung der SmartSearch nicht blockiert werden

_Zur Bedienung des admorris Pro Backends empfehlen wir eine aktuelle Version von Google Chrome._

**⚠ Bei der Verwendung des <u>ecomDATA LiteSpeed Cache Plugin</u> kann es zu Kompatibilitätsproblemen und Fehlern kommen.**

---

## Versionen:

### Version 3.1.9

**Release:** 11.9.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.3

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Template Bugfixes:

- Offcanvas-Menü: Textfarbe und Hintergrundfarbe wurden nicht korrekt ins CSS übernommen.
- Filter Dropdowns: Begrenzung der maximalen Höhe.

### Version 3.1.8

**Release:** 10.9.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.3

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Template Bugfixes:

- Buttontext Farbeinstellung wurde nicht korrekt ins CSS übernommen.
- Bestimmte Rundungs-Einstellungen verursachten Console Errors.
- Social Media Links in den Footer Einstellungen konnten nicht gelöscht werden.
- Migrations-Assistent hat in Elements `</label>` fälschlicherweise durch `</badge>` ersetzt.
- Verpackungseinheits-Preis Update bug in der Produktliste in Kombination mit Staffelpreisen.
- Produktdetailseite: Ausrichtung der Aktionsbuttons im Warenkorb-Bereich in der Mobilansicht.
- Checkout Steps: Optimierung des Responsive Design der Fortschrittsanzeige für kleinere Smartphonedisplays.
- Startseiten User-Styles wurden nicht geladen.

#### Salesbooster Bugfixes:

- Bonuspunkte: Rundungsskript für Simple Variations und Staffelpreise um die Bonuspunkte upzudaten hatte die Rundungseinstellung nicht korrekt berücksichtigt.

### Version 3.1.7

**Release:** 3.9.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.3

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Template Bugfixes:

- Suche hatte bei deaktivierter Dropdown Funktion nicht funktioniert (betrifft Version 3.1.6)

### Version 3.1.6

**Release:** 21.8.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.3

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Salesbooster Bugfixes:

- SmartSearch:
  - Accessibility Optimierung
  - Hängende Cronjob Abarbeitung konnte nach dem letzten Bugfix trotzdem noch auftreten.
  - Suchindex Löschen Funktion kann jetzt auf Wunsch auch die Cronjob Liste leeren.
- Rabattanzeige wurde in Produktslidern nicht immer korrekt angezeigt.
- Salecountdown Selector für Anzeige oberhalb des Headers hatte nicht funktioniert in admorris Pro und war im Nova innerhalb des sticky Headers. Falls das Problem existiert bitte die Selektoren für den Salecountdown in den Experteneinstellungen zurücksetzen.

#### Template Bugfixes:

- Template-Anpassungen des Nova Template von Shop Version 5.5.3
- Pro Slider:
  - Bei aktivem Cache wurden die Slides nicht richtig aktualisiert bis man den Cache geleert hat.
  - Parallax Effekt Einstellung führte zu einen Layout Shift beim Text.
- Staffelpreis Preisanzeige-Update hatte nicht funktioniert bei Mengenänderung.
- PushedToBasket Modal: "Kunden kauften dazu folgende Artikel" Productslider zeigte die Artikel zu schmal an.
- Wunschliste: Fehler das der Button des Artikel nicht klickbar ist, bei Artikeln in der letzten Reihe.
- Bei der Variationsauswahl in der Produktliste Listen-Ansicht kam es manchmal nach dem Wechsel der Variation zu unerwünschtem scrollen, wegen Focus-Wiederherstellung auf das falsche Element.

### Version 3.1.5

**Release:** 31.7.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.2

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Bugfixes:

- Imagegenerierung bei Elfinder Filemanager: Im admorris Pro Backend generiert der Filemanager auch verschiedene Bildgrößen für die Verwendung von Sourcesets. Seit Shop 5.5 kam es hier allerdings zu einem Fehler.
- Anzeige von Fehlermeldungen bei der Mengenauswahl. Vor allem bei teilbaren Stückzahlen wurden keine Hinweise angezeigt.
- Smart Search Salesbooster: Cron Job Abarbeitung hängt wenn Artikel nicht gefunden wird.

### Version 3.1.4

**Release:** 24.7.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.2

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Verbesserungen:

- Elements Admin Vorschau Modus: Elements verfügen jetzt über eine "Nur für Admins sichtbar" Einstellung.

### Version 3.1.3

**Release:** 23.7.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.2

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Bugfixes:

- Cart Sidebar Option: Der Sidebar Warenkorb wurde nicht auf die volle Bildschirmhöhe skaliert.
- Bug bezügich Suchfeld Stil Standardwert behoben.
- Elements Kategorie Toggle hat nicht funktioniert.

#### Verbesserungen:

- Datenbank Request Optimierung von Elements, Cookie Notice Pro und Popups.
- Neuer Request URI Triggerfilter: Erlaubt es, nur den Teil der URL nach der Domain abzufragen.
- EU Energie-Label: neues Funktionsattribut `admorris_pro_eu_energy_label_image_alt_text` für die Angabe des Alt-Textes des Energie-Labels.
- Popup Manager Accessibility:
  - Allgemeine Verbesserungen der Barrierefreiheit der Popups durch anpassung der Attribute.
  - Die Bilder von Popups hatten bisher den Titel des Popups als alt-Text verwendet, jetzt kann ein eigener Text als alt-Text verwendet werden.
  - Der für das Popup angegebene Titel wird jetzt auch als `aria-label` für das Popup verwendet.
  - Iframe Popup kann mit einem title Attribut versehen werden.
- Emoji Regen: aria-hidden für screenreader User hinzugefügt.

### Version 3.1.2

**Release:** 9.7.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.2

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Template Bugfixes & Verbesserungen:

- Pro Slider: Navigationspfeile und Dots auf Buttons umgestellt
- Mega-Menü (header/megamenu.tpl): Fataler Error behoben, wenn die Linkgruppe megamenu gelöscht wurde
- Zweites Produktbild: Hintergrundfarbe als Fallback für transparente Bilder hinzugefügt
- Produktdetailseite Beschreibungs Akkordeon: Mehrere Abschnitte können nun gleichzeitig geöffnet werden
- Mobile Suche: Mindestbreite (min-width) angepasst für bessere Darstellung
- Preisformatierung: `<strong>`-Tag entfernt für saubere Struktur. Behebt SEO Tool Warnung wegen sich wiederholendem Inhalt in `<strong>` Tags
- Slick Slider: Layout-Shift beim Initialisieren behoben

#### Allgemeine Bugfixes:

- Import/Export: Fehler beim Datenimport/-export behoben

### Version 3.1.1

**Release:** 25.6.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.2

**PHP Version:** 8.2 - 8.4

**Changelog:**

#### Bugfixes:

Bugfixes der Version 3.0.4

**Template**

- Header Border Settings 'leftRight' und 'around' setzen auch zwischen Columns Borders.
- Header Row Paddings und Border Radius Settings werden nicht übernommen (seit Version 3.1.0).
- Entfernung der h2 Elemente aus Produktdetails Akkordion (deaktivierte Tabs) Card Header Elementen (seit Version 3.1.0).
- Doppelte px Unit in Header Search (seit Version 3.1.0).

### Version 3.0.4

**Release:** 24.6.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- Beheben von Kompatibilitätsproblemen mit Safari 14 und niedriger bei AJAX Requests z.B. beim wechseln von Variationen oder Konfigurationen auf.
- Productcell Hover funktioniert nicht mit Endlos-Paginierung.

**Plugin**

- Cookie Notice Pro: Fehler beim editieren von angelegten Skripten wenn neue Sprachen nachträglich zum Shop hinzugefügt wurden.

### Version 3.1.0

**Release:** 12.6.2025

**Kompatibilität:** JTL-Shop 5.5.0 bis 5.5.1

**PHP Version:** 8.2 - 8.3 (8.4 noch in Prüfung)

**Changelog:**

#### Verbesserungen:

Kompatibilität mit JTL-Shop 5.5.0 und 5.5.1.
Verbesserung der Barrierefreiheit.

**Bitte beachten Sie, dass Sie vor dem Update des Shops auf Version 5.5 mindestens die admorris Pro Version 3.0.1 installiert haben müssen.**

### Version 3.0.3

**Release:** 11.6.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- Das Link Aussehen für die Sprachauswahl wurde angepasst.
- Bundle Styles wurden angepasst.

#### Verbesserungen:

- Unser neuer Backend Editor kann nun mit VSCode Tastaturkürzeln bedient werden.

**Salesbooster**

- Extraprodukt: Man kann nun ein Bild für das Extraprodukt per Option ausgeben lassen.

**Template**

- Für bessere Kompatibilität wurde die Klasse container-fluid zu admPro-container in unserem Template geändert.

### Version 3.0.2

**Release:** 15.5.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

- AI Designer: Generierungen von Slider Bildern korrigiert.
- AI Designer: Validierung für die Header Konfiguration hinzugefügt.

**Template**

- OPC Portlets konnten nicht mehr per Drag and Drop verschoben werden.
- "Ausverkauft" und "zum Artikel" Buttons waren nicht mehr sichtbar.

### Version 3.0.1

**Release:** 12.5.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

- Neuinstallations Problem bei aktivem Cache behoben.

**Salesbooster**

- Smart Search: Fehler beim Aufrufen der Einstellungen im Backend behoben.
- Google Kundenrezensionen: Fehler bei der Anzeige im Bestellabschluss behoben.
- Discount Display: Anzeige im Admorris Pro Template wurde an die Overlay Labels angepasst.

**Template**

- Footer Branding Einstellung: Einstellung wurde nicht mehr korrekt übernommen.
- Youtube Video Consent gewähren war fehlerhaft.
- Header Suche: Textfarben wurden nicht mehr korrekt angezeigt.

#### Verbesserungen:

**Salesbooster**

- Checkout Motivator: Mindestbestellwert Einstellung wurde hinzugefügt.

**Template**

- Coupon-Formular im Mini-Warenkorb.

### Version 3.0.0

**Release:** 29.4.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Verbesserungen:

**Template**

- **AI Designer**: Revolutionäres System zur automatischen Template-Konfiguration
  - Hochladen von Screenshots zur Analyse und Anpassung von Design-Elementen
  - Textuelle Beschreibung des Wunschdesigns für KI-gestützte Umsetzung
  - Nahtloser Übergang zum manuellen Einstellungsmodus für Feinabstimmungen
- **Neue Adminoberfläche** Komplett überarbeitete Adminoberfläche mit modernem Design
- **Header**:
  - Boxed Header Option
  - Flexible Border Style Settings
  - Margin, Padding und Border Radius
  - Designoption und Dropdownoption für Suchfeld (Standard Inputfeld oder minimale Linie)
- **Rundungseinstellungen**:
  - Rundungen für Panels, Bilder, Buttons und Header
- **Pro Slider**:
  - Slider & Slides duplizieren für einfache Verwaltung
  - Containergrößen für den Slider für Boxed Designs
- **Elements**:
  - Import & Exportfunktion
  - Neuer Code Editor
- **User-Styles**:
  - Neuer SASS Code Editor mit Autocomplete und Highlighting
  - Code Splitting

**Technische Verbesserungen**

- Umstellung auf SASS und Bootstrap 4 für bessere Kompatibilität mit Drittanbieterplugins
- Code Splitting der CSS Files für Ladeoptimierungen und besseren PageSpeed
- Überarbeiteter Checkout und Konfigurator für intuitiveres Einkaufserlebnis
- Erste Anpassungen für das Barrierefreiheitsgesetz

### Version 2.7.0

**Release:** 29.4.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

Diese Version wird für das Update auf die Version 3.0.0 benötigt, da wir im Hintergrund eine neue Lizenz API verwenden, um gleichzeitig Updates für Version 2 liefern zu können.

### Version 2.6.15

**Release:** 21.3.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

Bugfix im Installer.

### Version 2.6.14

**Release:** 6.3.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

**Plugin**

- Wiederbestellen: Fehler bei der Anzeige im Kundenkonto wenn kein admorris pro template aktiv. Variationskombinationen auswählen hat im Nova Template nicht funktioniert.

### Version 2.6.13

**Release:** 5.3.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

**Plugin**

- Cookie Notice Pro hat einen Fehler verursacht, wegen dem auch andere Salesbooster und Elements nicht mehr richtig funktioniert haben.

### Version 2.6.12

**Release:** 25.2.2025

**Kompatibilität:** JTL-Shop 5.4.0 bis 5.4.1

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- GPSR: Fehler bei den Links zu den Herstellerhomepage mit doppeltem https:// behoben.
- Manchmal fehlende custom Sprachvariablen für Artikel Overlay Labels.

**Plugin**

- Fehler beim Bonuspunkte Guthaben Verwaltung im Backend und im Frontend wurde im Warenkorb der die Summe nach dem Bonuspunkte hinzufügen erst beim nächsten Laden aktualisiert.
- Energielabel Styles fehlten in der Wiederbestellen Liste auf der Kunden-Account Seite.

### Version 2.6.11

**Release:** 22.1.2025

**Kompatibilität:** JTL-Shop 5.4.0

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

**Plugin**

Behebung von Konflikten mit Composer Packages anderer Plugins, die die gleichen Packages verwenden. Unsere verwendeten Packages werden jetzt mit php-scoper umbenannt, um Konflikte zu vermeiden.

- Bonuspunkte: Lösung eines Kompatibilitätsproblems mit JTL Vouchers Plugin

### Version 2.6.10

**Release:** 13.1.2025

**Kompatibilität:** JTL-Shop 5.4.0

**PHP Version:** 8.2 - 8.3

**Changelog:**

#### Bugfixes:

**Plugin**

- Bonuspunkte: Eine Inkompatiblilität mit der vom JTL Shop verwendeten Carbon Library wurde behoben, wodurch es zu einem Fatal Error mit den Bonuspunkten kam (`Declaration of Illuminate\Support\Carbon::setTestNow($testNow = null) must be compatible with Carbon\Carbon::setTestNow(mixed $testNow = null): void in /var/www/html/plugins/admorris_pro/vendor/illuminate/support/Carbon.php`).

### Version 2.6.9

**Release:** 18.11.2024

**Kompatibilität:** JTL-Shop 5.3.0 bis 5.4.0

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

Die Version behebt Bugs, die in Version 2.6.8 aufgetreten sind.

- Registrierungsformular im Checkout: Wenn keine eigene Lieferadresse angegeben hat, gab es Probleme wenn Bundesland als Pflichtfeld vorhanden war oder ein zu kurzes Passwort für die Accountgenerierung angegeben wurde, weil die Lieferadressen Felder nicht komplett deaktiviert wurden.
- Elements: addClass, removeClass, addAttr und removeAttr Modifikatoren funktionierten nicht mehr.

### Version 2.6.8

**Release:** 13.11.2024

**Kompatibilität:** JTL-Shop 5.3.0 bis 5.4.0

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Verbesserungen:

**Plugin**

- Enhanced Ecommerce Tracking: Umstellung auf den Advanced Consent Mode. Damit können mit Google Consent Mode V2 schon vor der Zustimmung des Nutzers Daten an Google Analytics gesendet werden, ohne dabei Cookies zu verwenden. Diese Funktion ist optional und kann im Plugin deaktiviert werden.

**Template**

- GPSR Attribute: Die für die Umsetzung der Produktsicherheitsverordnung (GPSR) nötigen Herstellerinformationen können jetzt in den Produktdetails angezeigt werden. Dafür können die selben Funktionsattibute des [JTL Plugins](https://www.jtl-software.de/extension-store/produktsicherheitsverordnung-gpsr-plugin-fuer-den-jtl-shop-5-jtl-shop-5) verwendet werden. Das Plugin wird dafür nicht benötigt.
- Lieferadressen Auswahl im Checkout: Anpassung der Auswahl der gespeicherten Lieferadressen an das Nova Template.
- Falls der UVP kleiner als der Preis ist, wird der UVP nicht mehr angezeigt.

#### Bugfixes:

**Plugin**

- EU-Energie-Label: Energielabels können jetzt auch in Produktslidern mit aktivierter Kaufoption im Admorris Pro Template angezeigt werden.

**Template**

- OPC Gallery Slider Pfeile fehlten bei der vergößerten Ansicht der Bilder.

### Version 2.6.7

**Release:** 14.8.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.3

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- Konfigurator gab bei Select Dropdowns fehlerhaften HTML code aus und das Bild des ausgewählten Produkts wurde dadurch manchmal nicht mehr geladen.

### Version 2.6.6

**Release:** 29.7.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.3

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- Fehler bei bootstrap-select Styles in Version 2.6.5. Es wurden die alten noch aus dem Cache geladen und dadurch konnten Probleme bei der Variationsauswahl mit Dropdown Feldern und Darstellungsfehler in der Blog Übersicht bei den Filtern entstehen.

**Plugin**

- EU-Energie-Label: Verschwommene Outline und kleinere Darstellung in der Produktliste und Produktslidern auf Mobilgeräten.

### Version 2.6.5

**Release:** 22.7.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.3

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- Problem mit mehreren Labels in Variationsauswahl behoben.
- Update der bootstrap-select Library auf die neueste Version. Damit wurde eine XSS Sicherheitslücke in der Library geschlossen.
- Konfigurationsgruppen-Bilder wurden nicht mehr angezeigt.
- Später bezahlen Link in der Bestelldetailseite im Account hatte nicht funktioniert.
- Weiterleitung auf Detailseite nach Variationsauswahl auf der Produktliste. Wenn die Produktdetailseite OPC Content verwendet, wurde die Weiterleitung auf die Detailseite ausgeführt.
- Variationsauswahl mit Radiobuttons: Behebung von Stylingfehlern.
- Variationsswatches: Dunkle Hintergurndfarbe in den Themefarben wurde nicht richtig angewendet und immer Weißer hintergrund angezeigt.

**Plugin**

- Fehler mit Popupmanager Bildern wenn admorris Pro Template nicht aktiv.
- EU Energie Labels:
  - Fehler bei der Anzeige seit der letzten Version, wenn nicht das admorris Pro Template verwendet wird.
  - Update des Designs um den neuesten EU Richtlinien zu entsprechen.

#### Verbesserungen:

**Template**

- Lazy Initialisierung von Slick Slidern zur Performance Verbesserung. Senkt die Total Blocking Time (TBT) Performance Metrik und verbessert die Ladezeit.

### Version 2.6.4

**Release:** 17.6.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.3

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- Child Template: JS und CSS Files aus dem Template xml file bekommen jetzt bei Child Templates jetzt wieder wie früher zusätzlich die Versionsnummer des Vater Templates angehängt. Bei Updates mit aktivem Child Template werden sonst noch die alten Dateien aus dem Cache geladen wenn man nicht die Versionsnummer des Child Templates anpasst.

### Version 2.6.3

**Release:** 17.6.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.3

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template**

- Pro Slider: Bei Seitenverhältnis Einstellung "Responsive" und "fixes Seitenverhältnis" wurde das erste Bild etwas verzögert geladen. Mit dem Bugfix ist der LCP Wert von Google Pagespeed / Lighthouse jetzt besser.
- Fehlende Itemdetails bei Bundles.
- Priceranges werden jetzt auch in den Rich Snippets ausgegeben.
- Problem beim Video Portlet mit Lokalen Videos ([#SHOP-7999](https://issues.jtl-software.de/issues/SHOP-7999))
- Internal Checkbox RightOfWithdrawalOfDownloadItems wird in alten Templates immer angezeigt, auch wenn kein Downloadartikel im Warenkorb ist ([#SHOP-8036](https://issues.jtl-software.de/issues?project=shop&query=SHOP-8036))
- Logos mit Alphakanal haben bei aktiver "Progressives Laden von Bildern" Einstellung kurz einen schwarzen Hintergrund angezeigt.

#### Verbesserungen:

**Template**

- Custom Overlay Labels funktionieren jetzt mit Shop 5.3 ([Funktionsattribut custom_item_badge](https://jtl-devguide.readthedocs.io/projects/jtl-shop/de/latest/shop_templates/product_badges.html))
- Pro Slider: Ken Burns Effekt Animation verwendet jetzt eine bessere Animationskurve und ist smoother.

### Version 2.6.2

**Release:** 16.5.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.1

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template:**

- Fehler bei Datenbank Migration nach Shop Updates: Das Deaktivieren des Plugins hatte bei aktivem Template verursacht, dass der Shop die Datenbank Migrationen nach dem Update nicht mehr durchführen konnte.
- Footer Boxen: Inaktive Footer Boxen wurden seit 2.6 wieder ausgegeben.
- Detailseite Bilder:
  - Beim Preloading des ersten Artikelbilds wurde die Einstellung "Containergröße verwenden" deaktiviert ist, die falsche Bildgröße mit preload geladen.
  - Zoom Bilder werden jetzt auch als WebP geladen.
  - Progressives Laden hatte bei dem ersten Produktbild nicht funktioniert.
  - Nicht sichtbare Produktbilder im Slider wurden verzögert nach dem Slidewechsel geladen.
- Bei aktivierter "Progressives Laden von Bildern" Einstellung wurde immer die JPEG Version des kleinsten Bildes vorgeladen.
- Weiterleitung nach Warenkorb zusammenfassen ablehnen: Man bleibt jetzt auf der "Mein Konto" Seite, und wird nicht mehr in den Checkout weitergeleitet.

**Plugin**

- Color Picker konnte von Sticky Submit Button überlagert werden.
- 360 Grad Bilder Thumbnails: Probleme bei der horizontalen Ausrichtung der Thumbnails im admorris Pro Template wurden behoben.

#### Verbesserungen:

**Template**

- HTML Code für Bilder vereinfacht: Bei aktiver WebP Option wird jetzt kein Picture und kein Source Tag mehr ausgegeben. Ältere Browser, die WebP nicht unterstützen, bekommen aber trotzdem noch JPEG Bilder zu verfügung gestellt.

### Version 2.6.1

**Release:** 26.4.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.1

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Bugfixes:

**Template:**

- Sprachmenü: Bei deaktivierter Dropdown Funktion verursachte das Sprachmenü einen Fehler.

### Version 2.6.0

**Release:** 23.4.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.3.1

**PHP Version:** 8.1 - 8.3

**Changelog:**

#### Hinweise:

- OPC Produktstream Slider: Konfiguration der Sichtbaren Elemente pro Mediaquery funktioniert aktuell noch nicht.
- Darkmode im Backend funktioniert, wird noch nicht überall optimal unterstützt.

#### Verbesserungen:

**Plugin**

- Enhanced Ecommerce Tracking: Analytics über Option auch Consent für Google Ads abfragen und dann über Google Consent Mode V2 senden.
- Sticky Submit Buttons im Backend.
- Einstellungs Import/Export Verbesserungen: Versions und Datumsspalten hinzugefügt.

**Template**

- Backend Einstellungen in "Global" wurden in mehrere Menüpunkte aufgeteilt.
- Textausrichtung der Produktzelle: Ausrichtung für alle Produktzellen, auch die in Produktslider. Die Einstellung befindet sich jetzt in Layout & Design > Global > Allgemein.
- Elements Toggle Button: Schnelles Aktivieren und Deaktivieren von Elementen, ohne die Einstellungen öffnen zu müssen.
- Produktslider: Warenkorb Button Anzeige Option.
- Mini-Warenkorb: Sidebar Option.
- Zahlungslogos: Anzeige Option für die Detailseite.
- Unterstützung der Shop Option für das Ausblenden des Mindesthaltbarkeitsdatums.
- Hreflang attribute zu Sprachauswahl Links hinzugefügt.

### Version 2.5.5

**Release:** 13.3.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.4

**PHP Version:** 8.1 - 8.2

**Changelog:**

#### Verbesserungen:

**Plugin**

- Enhanced Ecommerce Tracking: Consent Mode V2 wird jetzt unterstützt.

#### Bugfixes:

**Template**

- Zoomfunktion der Artikeldetail Bilder wurde trotz deaktivierter Option in den Einstellungen geladen.
- DataTables Library nur laden, wenn sie für die Adressverwaltung auf der Kontoseite und im
  Bestellvorgang benötigt wird.

**Plugin:**

- EU Energy Labels: Styles wurden für OPC Produkslider nicht geladen.
- Bonuspunkte: 'reward_points_exclude' Attribut hat bei manchen Shops fehlerhaft funktioniert.
- Template Einstellungen im Backend zeigten beim Speichern nochmal kurz die alten Werte an.
- Javascript Files im Backend wurden teilweise mit // im Pfad geladen, was auf manchen Servern zu Ladefehlern führte.

### Version 2.5.4

**Release:** 21.2.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.4

**PHP Version:** 8.1 - 8.2

**Changelog:**

#### Bugfixes:

**Template:**

- Leere Boxen wurden im Footer ausgegeben.
- OPC Editor: Vorschaubilder von Produktstreams wurden nicht angezeigt.
- Textbox Variationen mit Bild wurden wie Swatches angezeigt.
- Kategorieattribut 'category_seo_url' hat auf Artikeldetailseiten zu robots noindex geführt.

**Plugin**

- 360 Grad Bilder Salesbooster: Fehler beim Generieren der Bilderlisten Files.
- Bonuspunkte: Fehlermeldung beim Abgleich von Stornierungen, wenn keine Bonuspunkte vorhanden sind.
- Wiederbestellen Menü-Icon Settings funktionierten nicht.
- "No settings found" Fehlermeldung: Der Fehler trat auf bei Bonuspunkten und 360 Grad Bilder Salesbooster nach dem Update auf 2.5.
- Enhanced Ecommerce Tracking: Bei Darstellung der Produktliste mit Listen Items wurde das add_to_cart Event nicht getriggert.
  Bei Nova basierten Templates wurde das add_to_cart Event nicht getriggert.
- Shipping Countdown: Versandschluss Feld funktionierte nicht.
- Update Probleme bei aktivem OpCache: Beim Update führen wir ein opcache_reset() aus, damit die neuen Dateien geladen werden.

### Version 2.5.3

**Release:** 30.1.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.4

**PHP Version:** 8.1 - 8.2

**Changelog:**

#### Bugfixes:

**Template:**

- Fatal Error aus item_box.tpl: `Call to a member function getPaths() on null`. Der Fehler trat in manchen Fällen bei OPC Productstreams auf.
- Userstyles Editor: Cursor wurde bei großen Styles versetzt angezeigt.

### Version 2.5.2

**Release:** 1.2.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.4

**PHP Version:** 8.1 - 8.2

**Changelog:**

#### Bugfixes:

**Plugin:**

- Bonuspunkte und 360 Grad Bilder Salesbooster: Nach der Aktivierung konnten die Einstellungen nicht mehr editiert werden, wenn die volle Anzahl an Salesboostern aktiv ist.

### Version 2.5.1

**Release:** 19.1.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.4

**PHP Version:** 8.1 - 8.2

**Changelog:**

#### Bugfixes:

**Template:**

- Mobile Header Settings: Die neuen Einstellungen für die mobilen Headerreihen aus Version 2.5.0 wurden nicht richtig von den Desktop Einstellungen übernommen. Sollten Sie schon auf Version 2.5.0 upgedatet haben, werden die mobilen Einstellungen der Headerreihen beim Updaten nochmals durch die Desktop Settings überschrieben und korrekt für die mobile Ansicht übernommen.

### Version 2.5.0

**Release:** 16.1.2024

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.4

**PHP Version:** 8.1 - 8.2

**Changelog:**

#### Verbesserungen:

**Plugin:**

- Bonuspunkte:
  - Verbesserung der Performance bei der Berechnung der Bonuspunkte.
  - Ausschließen von Artikeln von der Bonuspunkte Berechnung über ein Funktionsattribut.
  - Bonuspunkte können jetzt auch im Checkout eingelöst werden. Über Option auch deaktivierbar.
  - Besseres mobiles Layout für das Bonuspunkte Einlösen Formular.
  - Neue Selektoren, die jetzt in Admorris Pro und im Nova Template verwendet werden können.
- 360 Grad Bilder Salesbooster: Auf Artikelseiten können 360 Grad Bilder angezeigt werden.
- Enhanced Ecommerce Tracking:
  - zusätzlich zu Google Analytics kann jetzt auch Google Tagmanager und Google Ads verwendet werden.
  - Einstellung für Server URL wenn Server Side Tracking verwendet werden soll, damit die Daten nicht bei Google sondern bei einem eigenen Server gespeichert werden.
  - Newsletter Popup: Anrede, Vorname und Nachname können zusätzlich abgefragt werden.

**Template:**

- Warenkorbdropdown kann über Option als Sidebar angezeigt werden.
- Vergleichsliste Button in der Artikelliste.
- Design von Vergleichsliste und Merkzettel Buttons in der Produktliste an das Nova Template angepasst.
- Artikelnummer Anzeige in der Produktliste (Option).
- Verschieben der Breadcrumbs auf der Detailseite über das Produktbild. Dadurch bricht die Breadcrumb nicht mehr um wenn sie sehr lange Namen enthält.
- Lieferadressen-Verwaltung im Kundenkonto
- Header Konfigurator: Header Reihen können bei den Mobile Einstellungen unabhängig von den Desktop Einstellungen angepasst werden.
- Elements: neuer Triggerfilter um Kategorien über die Id zu triggern. Eine zweite Variante dieses Triggerfilters ermöglicht es auch, wenn die aktuelle Kategorie eine Unterkategorie der angegebenen ist, das Element zu triggern.
- Consent Manager: Buttons sind jetzt gleich gestyled, dass sie der EU Richtlinie entsprechen. Eine fehlende id hat zu Problemen mit anderen Cookie Plugins geführt.

#### Bugfixes:

**Plugin:**

- Push Notifications:
  - Öffnen der Links einer Push Notification hatte nicht funktioniert.
  - SQL Fehler "Integrity constraint violation: 1062 Duplicate entry" behoben.
- Energie Label: Bei URLs mit mehreren Slashes hatten die Links nicht funktioniert.
- Emoji-Regen: Ausgewählte Emojis wurden nicht mehr richtig hervorgehoben.
- Advent Kalender: Lazy loading der Türchen Bilder hatte im Nova Template nicht funktioniert. Bilder im Kalender Fenster-Inhalt werden jetzt erst geladen, wenn das Fenster geöffnet wird.
- Mailchimp: Anmelde-Problem bei großen gespeicherten Warenkörben.

**Template**

- OPC Slider: Bilder mit Links werden jetzt wieder korrekt angezeigt.
- Rich Snippets: "availability" war bei Artikeln die vorbestellt werden konnten auch auf "InStock" gesetzt. Jetzt wird korrekt "PreOrder" ausgegeben.
- Icons: bei manchen Icon Familien wurde das Youtube Icon nicht richtig in der Datenbank gespeichert und dann im Backend nicht mehr angezeigt.
- Konfigurator Summary Sidebar: Fehler, dass die Box nicht mehr nach oben mitgescrollt wurde ist behoben.
- Das Kategorie Attribut "category_seo_url" wird jetzt auch in "snippets/categories-offcanvas.tpl" und "productlist/subcategories.tpl" berücksichtigt.
- Kompatibilität mit KNM-Produktbewertungen+ Plugin.
- Lazy loading der Produktbilder in der Listenansicht hatte seit Version 2.4.25 nicht mehr funktioniert.

---

### Version 2.4.29

**Release:** 18.10.2023

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.4

**PHP Version:** 8.1 - 8.2

**Changelog:**

- Bugfix (Template): Kompatibilitätsproblem mit Paypal Checkout behoben.
- Bugfix (Template): In den Rich Snippets wurden bei der Beschreibung Umlaute mit falschen Zeichen ausgegeben.

---

### Version 2.4.28

**Release:** nicht veröffentlicht

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.3

**PHP Version:** 8.1 - 8.2

**Changelog:**

- Bugfix (Template): plugin_js_head.js wurde doppelt geladen.

---

### Version 2.4.27

**Release:** 30.8.2023

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.3

**⚠ In Shop Version 5.2.2 muss die Shop Sprachvariable 'charactersLeft' mit 'Zeichen übrig' ergänzt werden, da sie erst in Shop 5.2.3 hinzugefügt wurde**

**PHP Version:** 8.1 - 8.2

**Changelog:**

Diese Version behebt folgende Fehler, die seit Version 2.4.25 aufgetreten sind:

- Bugfix (Template): OPC Galerie Bilder wurden bei der Layout-Einstellung 'Spalten' verzerrt ausgegeben.
- Bugfix (Template): Favicon HTML Code wird nicht ausgegeben, wenn kein Favicon im admorris Pro Backend hochgeladen wurde und das default Favicon verwendet wird.
- Bugfix (Template): Beschreibung in Strukturierten Daten wurde nicht korrekt escaped.
- Bugfix (Plugin): Die Rabattanzeige hat nicht mehr richtig funktioniert.

Weitere Bugfixes:

- Bugfix (Template): Logo Breiten Einstellung wurde in manchen Fällen nicht richtig übernommen und das Logo konnte in bestimmten Bildschirmgrößen zu klein angezeigt werden.
- Bugfix (Plugin): Problem mit Zusatzprodukten, wenn mit Amazon Pay gezahlt wird.

---

### Version 2.4.26

**Release:** 22.8.2023

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.3

**PHP Version:** 8.1 - 8.2

**Changelog:**

Diese Version behebt Fehler, die in Version 2.4.25 aufgetreten sind.

- Bugfix (Template): Neue Slider konnten nicht gespeichert werden.
- Bugfix (Template): OPC Images wurden zu groß ausgegeben, und die Form-Einstellung hat nicht funktioniert

---

### Version 2.4.25

**Release:** 22.8.2023

**Kompatibilität:** JTL-Shop 5.2.2 bis 5.2.3

**PHP Version:** 8.1 - 8.2

**Changelog:**

- Verbesserung (Template): Elements, welche Smarty Fehler verursachen, blenden für Administratoren jetzt eine Fehlermeldung im Frontend ein und schreiben die Fehlermeldung auch ins Logbuch des Shops.
- Verbesserung (Template): Artikel-Variationen können in der Liste auch bei deaktiviertem Hover-Effekt ausgewählt werden.
- Bugfix (Template): OPC Bilder verfügen jetzt über Sourcesets und geben auch WebP Versionen der Bilder aus, wenn die Option aktiv ist.
- Bugfix (Template): Fehlende startSpinner und stopSpinner Funktionen wurden ergänzt, wegen denen es zu Fehlern bei bestimmten Paypal Zahlungsarten kam.
- Bugfix (Template): "Zur Kasse" Button im Warenkorb hat jetzt styles, damit es zu keinen Problemen mit dem Paypal Express Button mehr kommt.
- Bugfix (Template): Entfernen der Template Einstellungen zur Komprimierung des HTML-Ausgabedokuments. Es kam bei Kunden zu Fehlern, wenn diese Funktion aktiv war.
- Bugfix (Template): Hersteller falsch verlinkt.
- Bugfix (Template): OPC Productstreams verursachten Rich-Snippets Fehler bei Google.
- Bugfix (Template): Favicon HTML Code nicht korrekt für PNG Bilder. Jetzt wird auch im Wartungsmodus das über das Plugin hochgeladene Favicon angezeigt.
- Bugfix (Template): Fix des Kategorieattribut "category_seo_url" im mobilen Menü.
- Bugfix (Template): Die Artikelbeschreibung in den strukturierten Daten konnte zu viele Zeichen beinhalten. Sie wird jetzt unter Berücksichtigung des HTML Inhaltes gekürzt.
- Bugfix (Template): Fehler von Staffelpreisen in den strukturierten Daten behoben.
- Bugfix (Plugin): Verbesserung des Recent Sales Cronjob zum Löschen alter Einträge.
- Bugfix (Plugin): Zusatzartikel aus Bestseller entfernen.

---

### Version 2.4.24

**Release:** 13.7.2023

**Kompatibilität:** JTL-Shop 5.2 bis 5.2.2

**PHP Version:** 8.1

**Changelog:**

Wichtiger Sicherheitsfix für Mailchimp API + Bonuspunkte.

**⚠ Wir empfehlen dieses Update dringend durchzuführen!**

---

### Version 2.4.18+build.4

**Release:** 13.7.2023

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.5

**PHP Version:** 7.4 bis 8.0

**Changelog:**

Wichtiger Sicherheitsfix für Mailchimp API + Bonuspunkte.

**⚠ Wir empfehlen dieses Update dringend durchzuführen!**

---

### Version: 2.4.23

**Release:** 10.5.2023

**Kompatibilität:** JTL-Shop 5.2 bis 5.2.2

**PHP Version:** 8.1

**Changelog:**

Alle Änderungen von 2.4.18+build.3 sind enthalten.

---

### Version: 2.4.18+build.3

**Release:** 10.5.2023

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.5

**PHP Version:** 7.4 bis 8.0

**Changelog:**

- Bugfix (Plugin): An Mailchimp konnte nichts mehr übertragen werden, wenn ein gelöschter Kunde noch in den Cronjobs vorhanden war. Behebung von Warnings in der Mailchimp Datenübertragung.
- Verbesserung (Plugin): Sourceset Generierung unterstützt jetzt größere Bilder. Die Bilder werden jetzt auf maximal 3840 verkleiniert, damit die Sliderbilder auch auf 4K Bildschirmen scharf dargestellt werden können.
- Verbesserung (Plugin): Recent Sales und Recent Visits Optimierung der Datenbankabfragen.
- Bugfix (Plugin): Checkout Motivator wurde bei aktiviertem Paypal Express Button auf der Detailseite automatisch getriggert, weil das Paypal Plugin simuliert den Artikel zum Warenkorb hinzuzufügen.
- Bugfix (Plugin): Fehler in trigger_filter.php wenn der Kategoriename nicht gelesen werden kann und der Triggerfilter "User - Loginstatus" funktionierte nicht mehr.
- Bugfix (Template): Wenn `define('SHOW_CHILD_PRODUCTS',2);` in der config gesetzt wurde, hat das in den Warenkorb legen der Variationskombinations-Artikel in der Produktliste nicht funktioniert.

---

### Version: 2.4.23

**Release:** 10.5.2023

**Kompatibilität:** JTL-Shop 5.2 bis 5.2.2

**PHP Version:** 8.1

**Changelog:**

Alle Änderungen von 2.4.18+build.3 sind enthalten.

---

### Version: 2.4.18+build.3

**Release:** 10.5.2023

**Kompatibilität:** JTL-Shop 5.1 bis 5.2.2

**PHP Version:** 7.4 bis 8.0

**Changelog:**

- Bugfix (Plugin): An Mailchimp konnte nichts mehr übertragen werden, wenn ein gelöschter Kunde noch in den Cronjobs vorhanden war. Behebung von Warnings in der Mailchimp Datenübertragung.
- Verbesserung (Plugin): Sourceset Generierung unterstützt jetzt größere Bilder. Die Bilder werden jetzt auf maximal 3840 verkleiniert, damit die Sliderbilder auch auf 4K Bildschirmen scharf dargestellt werden können.
- Verbesserung (Plugin): Recent Sales und Recent Visits Optimierung der Datenbankabfragen.
- Bugfix (Plugin): Checkout Motivator wurde bei aktiviertem Paypal Express Button auf der Detailseite automatisch getriggert, weil das Paypal Plugin simuliert den Artikel zum Warenkorb hinzuzufügen.
- Bugfix (Plugin): Fehler in trigger_filter.php wenn der Kategoriename nicht gelesen werden kann und der Triggerfilter "User - Loginstatus" funktionierte nicht mehr.
- Bugfix (Template): Wenn `define('SHOW_CHILD_PRODUCTS',2);` in der config gesetzt wurde, hat das in den Warenkorb legen in der Produktliste nicht funktioniert.

---

### Version: 2.4.21

**Release:** 4.4.2023

**Kompatibilität:** JTL-Shop 5.2 bis 5.2.2

**PHP Version:** 8.1

**Changelog:**

Alle Änderungen von 2.4.18+build.2 sind enthalten.

- Bugfix (Template): Fehler bei Variationsauswahl wegen fehlender Funktion 'redirectToArticle' in jtl.article.js. Der Fehler trat auf, wenn der OPC auf der Artikeldetailseite verwendet wurde.

---

### Version: 2.4.18+build.2

**Release:** 4.4.2023

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.5

**PHP Version:** 7.4 bis 8.1

**Changelog:**

- Verbesserung (Plugin): Stornierungen über die WAWI machen die Bonuspunkte Transaktion rückgängig.
- Verbesserung (Template): Merkmalfilter wurden bei einer früheren Version die Werte nach gefundenen Artikeln sortiert. Es gibt jetzt eine Option dafür unter Produktliste/Filter, wo diese Funktion aktiviert werden kann. Standardmäßig ist sie jetzt wieder deaktiviert.
- Bugfix (Plugin): Mailchimp Artikelbilder Pfade fehlerhaft seit 2.4.18
- Bugfix (Plugin): Automatisches Cache löschen bei Salesboostern, die Consent Items registrieren.
- Bugfix (Plugin): Fehler im Wartungsmodus wenn die Preisdifferenzierung aktiviert ist.
- Bugfix (Template): Wenn die Komprimierung auf "statisch" eingestellt war, wurde nicht das statisch generierte CSS file vom Template ausgegeben.
- Bugfix (Template): Der Block "productdetails-attributes" war doppelt im Template.
- Bugfix (Template): Das Caching der Icons hatte nicht richtig funktioniert.

---

### Version: 2.4.20

**Release:** 23.2.2023

**Kompatibilität:** JTL-Shop 5.2 bis 5.2.2

**PHP Version:** 8.1

**Changelog:**

- Bugfix (Template): Blog Kategorie-Auswahl funktioniert wieder korrekt. In Version 2.4.19 konnte sie nach einmal wechseln nicht mehr verwendet werden.
- Bugfix (Template): Als Blog Bannerbild wurde auch das Vorschaubild verwendet, dass meist zu klein dafür ist. Es kann jetzt ein Bild mit 'banner' im Dateinamen hochgeladen werden, dann wird mit sicherheit dieses verwendet.
- Bugfix (Plugin): Import Export Funktion konnte Backups in Version 2.4.19 nicht einspielen und auch nicht downloaden.
- Bugfix(Plugin): Mailchimp hat bei abgebrochenen Warenkörben den Shop Link mit /admin gesendet. Bei Ändern des Mailchimp API Keys wurde die Liste nicht richtig ausgewählt.
- Verbesserung (Plugin): Möglichkeit Zusatzprodukte als optional anzulegen, die erst bei auswahl einer Checkbox auf der Artikeldetailseite in den Warenkorb gelegt werden.

---

### Version: 2.4.19

**Release:** 8.2.2023

**Kompatibilität:** JTL-Shop 5.2 bis 5.2.2

**PHP Version:** 8.1

**Changelog:**

Kompatibilität des Templates zu Shop 5.2.

- Bugfix (Template): Es wurden Depracation Warnings behoben.

---

### Version: 2.4.18

**Release:** 8.2.2023

**Kompatibilität:** JTL-Shop 5.1 bis 5.2.2

**PHP Version:** 7.4 bis 8.1

**Changelog:**

Kompatibilität des Plugins zu Shop 5.2 und PHP 8.1.

- Bugfix (Plugin): Kundengruppen Verdienstfaktor auf 0 stellen funktioniert wieder korrekt, dass keine Bonuspunkte angezeigt werden für diese Kundengruppe.
- Bugfix (Plugin): Cookie Notice Pro Standardsettings wurden bei Neuinstallation nicht richtig initialisiert.
- Bugfix (Template): Rich Snippets enthalten nicht mehr isbn und gtin wenn die Shop-Einstellungen auf "nicht anzeigen" gestellt sind.
- Bugfix (Template): Blöcke in productdetails/basket.tpl hinzugefügt um den Output der darin enthaltenen Smarty Functions in einem Child Template überschreiben zu können.
- Bugfix (Template): Optimiertes Bild verwendet in Pushed Success Meldungsfenster wenn ein Artikel in den Warenkorb gelegt wurde.
- Bugfix (Template): Fehler in checkout/inc_order_items.tpl in Child Template wegen relativem include.
- Bugfix (Template): Diverse Überschriften sind in divs geändert worden und werden über die entsprechende Bootstrap Klasse gestyled. Diese Änderung wurde vom Nova Template übernommen, weil diese Überschriften SEO technisch nicht relevant sind.
- Bugfix (Template): Boxen verwenden nicht mehr das section html element.

---

### Version: 2.4.17

**Release:** 6.12.2022

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.4

**PHP Version:** 7.3 bis 8.0

**Changelog:**

- Bugfix (Plugin): Bonuspunkte Kompatibilität mit JTL Voucher Plugin. Die Bonuspunkte wurden vom Voucher Plugin immer im Checkout aus dem Warenkorb entfernt.
- Bugfix (Plugin): Recent Sales / Visits Performance Optimierung.
- Verbesserung (Plugin): Elements Liste hat jetzt bessere Spaltenaufteilung, damit längere Namen besser lesbar sind.
- Verbesserung (Plugin): Elements Modifier Verbesserungen:
- removeClass: kann jetzt mehrere Klassen auf einmal entfernen.
- replaceClass (neu): um mehrere Klassen auf einmal ersetzen.
- removeAttr: kann jetzt mehrere Attribute auf einmal entfernen.
- addAttr (neu): Attribute können hinzugefügt werden indem im Templatefeld im Format attributname=wert eingegeben werden. Es können auch mehrere mit Leerzeichen getrennt angegeben werden.
- Bugfix (Template): Zahlungsicons im Footer wurden in Firefox nicht mehr angezeigt.
- Verbesserung (Template): Zahlungsicons für Postfinance und Samsung Pay hinzugefügt.
- Verbesserung (Template): Kategorie Icons können jetzt auch bei Unterkategorien im Dropdown ausgegeben werden.
- Verbesserung (Plugin): Consentmanager Integrierung von Cookie Notice Pro und den Salesboostern, die den Consent Manager verwenden, wurde verbessert. Beim akzeptieren der Cookies wird die Seite jetzt nicht mehr neugeladen, weil das für Tracking nachteilig ist.

---

### Version: 2.4.16

**Release:** 28.10.2022

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.4

**PHP Version:** 7.3 bis 8.0

**Changelog:**

- Bugfix (Plugin): Elements remove hat nicht mehr funktioniert.

---

### Version: 2.4.15

**Release:** 20.10.2022

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.4

**PHP Version:**  7.3 bis 8.0

**Changelog:**

- Verbesserung (Template): Endlos scrolling: Früheres Laden der nächsten Produkte. Browser Zurück-Button Funktioniert führt jetzt zur richtigen Seite.
- Bugfix (Template): Elements Performance Verbesserung.
- Bugfix (Template): Quickview Preview Bild Anzeige funktioniert wieder.
- Bugfix (Template): Fehler bei Mengenauswahl wenn Mindestbestellmenge > Abnahmeintervall behoben
- Bugfix (Template): Bei Bundeslandauswahl war nachdem das Land gewechselt wurde nicht mehr das Dropdown verfügbar.
- Bugfix (Template): Fixierter Warenkorb zeigt jetzt Button bei Konfigurationsartikeln an.
- Bugfix (Template): Paypal updateCart function Problem behoben.
- Bugfix (Template): Sourceset Generierung über GD image driver funktioniert jetzt wenn die Einstellung SMARTY_FORCE_COMPILE aktiv ist.
- Bugfix (Template): noindex header für Aufrufe mit Ajax.
- Bugfix (Template): Fehlende Pfeil Bilder im OPC Slider.
- Bugfix (Template): Maximale Bestellmenge war in der Mengenauswahl auf der Produktlisten Galerie Ansicht nicht begrenzt.
- Bugfix (Plugin): Enhanced Ecommerce Fatal Error bei Kreditkarten Zahlungen, die die Bestellabschluss Seite verwenden.

---

### Version: 2.4.14

**Release:** 18.08.2022

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.4

**PHP Version:**  7.3 bis 8.0

**Changelog:**

- Änderung (Template): Die Einstellungen "Javascript asynchron laden" und "jQuery asynchron laden" wurden entfernt. Javascript wird jetzt standardmäßig mit defer asynchron geladen, weil das auch beim Nova Template standard ist. Bei der jQuery Option kam es oft mit anderen Plugins zu Problemen.
- Bugfix (Template): Beim Footer Newsletter Formular fehlte ein Captcha Feld, wenn die Shop Option aktiviert wurde.
- Bugfix (Template): Mollie Payment Plugin Kreditkarten Formular Layout war seit der letzten Version von Mollie verschoben.
- Bugfix (Template): Paypal Checkout Warenkorb Button hat mobil den "zur Kasse" Button überlagert.
- Bugfix (Template): Strukturierte Daten für Rich-Snippets auf Artikeldetailseite bekommen jetzt die Artikelbeschreibung als Fallback wenn keine Kurzbeschreibung verfügbar ist.
- Bugfix (Template): Startseiten News-Slider Überschrift verlinkt jetzt mit dem korrekten Seo-Link der News Seite.
- Bugfix (Template): Bundles konnten nicht in den Warenkorb gelegt werden.
- Bugfix (Plugin): Cookie Notice Pro Migration übernimmt beim update von einer Version vor 2.4.0 das Bestellabschluss Skript nicht.
- Bugfix (Plugin): Cookie Notice Pro Skripte können jetzt type="module" verwenden. Dadurch können Inline Skripte mit defer ausgeführt werden.
- Bugfix (Plugin): Push Notifications skripte wurden manchmal nicht in der richtigen reihenfolge ausgefürt.

---

### Version: 2.4.13

Release: 29.07.2022

Kompatibilität: JTL-Shop 5.1 bis 5.1.4

PHP Version:  7.3 bis 8.0

Changelog:

- Bugfix (Plugin): Wegen Problem in der letzten Version wurde der Reload nach Ablaufen der Browser-Session für das schnellere Laden der Consent Skripte wieder entfernt, weil die Seite teilweise zu oft neu geladen wurde.

---

### Version: 2.4.12

**Release:** 26.07.2022

**Kompatibilität:** JTL-Shop 5.1 bis 5.1.3

**PHP Version:**  7.3 bis 8.0

**Changelog:**

- Bugfix (Template): Pro Slider: Parallax Setting hat manchmal zu verschwommenen Bildern geführt wegen Kommazahlen im srcset 'w'-Parameter
- Bugfix (Template): Pro Slider: Anzeigefilter nach Merkmal hat nicht mehr funktioniert
- Bugfix (Template): Kategorieattribut für Listen-Layout in Produktliste hat beim Laden von Variationen das falsche Artikel-Layout nachgeladen
- Bugfix (Template): Syntaxfehler bei strukturierten Daten (JSON-LD für Google Rich-Snippets) bei Artikel-Reviews
- Bugfix (Template): Fehler bei der Anzeige von Bundles. + Zeichen sind nicht an der richtigen Stelle sichtbar
- Bugfix (Template): ResponsiveImage Smarty Component hat mit opc='true' und lazy='true' trotzdem noch das kleinste Bild als src Attribut ausgegeben
- Bugfix (Template): Sticky Basket funktioniert in Firefox manchmal nicht
- Bugfix (Template): Link auf Logobild zeigt nun auf die Url passend zur Sprache, welche im seo Feld angegeben wurde
- Bugfix (Plugin): Smartsearch: Keyboardnavigation hat nicht mehr funktioniert
- Bugfix (Plugin): Consents von Cookie Notice Pro / Livechat / Advanced Ecommerce Tracking & Review Salesboostern werden nach Ablaufen der Browser-Session erst beim zweiten Seitenaufruf wieder geladen
- Bugfix (Plugin): Bei aktivem Admorris Pro Branding entfernen wurde auch das in den Shop-Meta-Einstellungen angegebene Copyright nicht mehr angezeigt.

---

### Version: 2.4.11

**Release:**  22.06.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.3

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Verbesserung (Template):**  Neue Einstellungen für das Header-Menü:
  - Containerbreite Einstellung
  - Dropdown Einstellungen: Animation und Ausrichtung können jetzt für die Menü Dropdowns angepasst werden.
- **Neue Funktion (Plugin):**  Shopvote: Option "Flyout" hinzugefügt
- Bugfix (Template): JTL Voucher Kompatibilität
- Bugfix (Template): "Warenkorb zusammenfüren" Funktion im Checkout funktioniert nicht
- Bugfix (Plugin): Fehler bei Hinzufügen von Elements-Kategorien
- Bugfix (Plugin): Fehler bei Shopvote Badge Anzeige wurden behoben
- Bugfix (Plugin): Fehler bei Preisdifferenzierung mit Staffelpreisen
- Bugfix (Plugin): Whatsapp Button funktioniert auf Mobilgeräten nicht
- Bugfix (Plugin): Consent Einstellungen von Live Chat und Advanced Ecommerce gehen nach Update verloren
- Bugfix (Plugin): Aktivierte Consents werden nach Ablaufen der Session wieder erneut an den Server gesendet
- Bugfix (Plugin): In der Admin-Oberfläche werden inaktive Cookies fälschlicherweise als 'aktiv' angezeigt
- Bugfix (Plugin): Nach Update werden Styles nicht automatisch kompiliert (ab dem nächsten Update)

---

### Version: 2.4.10

**Release:**  19.05.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Verbesserung (Plugin):**  Bilder, die über den elFinder hochgeladen werden, unterstützen nun 2400x2400px als Maximum - falls admorris Pro Template und Pro Slider verwendet werden und die Qualität der Bilder unzureichend scheint, so laden Sie bitte die originalen Bilder erneut in Ihrem Shop über unser Plugin hoch (das Bild sollte mindestens 2400x2400px groß sein)
- **Verbesserung (Plugin):**  Überarbeitung des Designs vom Checkout Motivator
- Bugfix (Plugin): 500 Fehler, wenn Live Chat und Whatsapp Chat aktiv sind
- Bugfix (Plugin): Icon von Recent Sales verschwindet nach Speichern der globalen Einstellungen
- Bugfix (Template): Beim Laden der Produktvariationen erscheint ein schwarzer Hintergrund
- Bugfix (Template): Mindesbestellmenge wird nicht korrekt in das Mengenfeld übernommen
- Bugfix (Template): Slider-Bilder sind unscharf bei aktivem Parallax-Effekt
- Bugfix (Template): Youtube-Videos im Slider funktionieren nicht
- Bugfix (Template): Sale Countdown verschwindet hinter Overlay-Menü

---

### Version: 2.4.9

**Release:**  10.05.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- Bugfix (Plugin): admorris pro consents werden nicht registriert, wenn Shop nicht deutsch/englisch unterstützt - englische Werte werden nun als Fallback verwendet
- Bugfix (Plugin): Skripte von admorris pro werden im Consent-Manager bei Klick 'Alle akzeptieren' nicht richtig geladen
- Bugfix (Plugin): Zusatzartikel werden bei Live Sale Notifications angezeigt
- Bugfix (Plugin): Extraprodukte können fälschlicherweise zweimal zum Warenkorb hinzugefügt werden
- Bugfix (Plugin): Anzeigeprobleme von Checkout Motivator & Nova-Template bei Safari-Browsern

---

### Version: 2.4.8

**Release:**  05.05.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Verbesserung (Plugin):**  Verbesserte Kompatibilität mit anderen Plugins - Smarty-Variablen und Modifizierer anderer Plugins werden nicht mehr zurückgesetzt
- **Verbesserung (Template):**  Einstellung 'Produktbild Sticky' nun kompatibel mit 'Dropper'-Plugin von Kreativkonzentrat
- Bugfix (Plugin): Cookies von admorris pro werden erst beim Neuladen der Seite/Navigation zu anderer Seite ausgeführt
- Bugfix (Template): Einstellung 'Produktbild Sticky' funktioniert nur, wenn das Menü mit 'sticky' konfiguriert ist

---

### Version: 2.4.7

**Release:**  03.05.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Hotfix (Plugin):**   admorris pro Skripte werden teils nicht korrekt im JTL-Shop registriert

---

### Version: 2.4.6

**Release:**  03.05.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Verbesserung (Plugin):**  Cookie Hinweis Pro - Positionierung der Skripte: bietet ab sofort die Möglichkeit, Skripte an den Head/Body anzuhängen oder voranzustellen
- **Verbesserung (Plugin):**  Cookie Hinweis Pro, Enhanced-E-Commerce, LiveChat, Shopbewertungen - Kompatibilität mir anderen Cookie-Hinweis-Plugins: Wenn JTL's Consent-Manager nicht aktiv ist, werden admorris-Pro-Skripte korrekt geladen und bieten bessere Kompatibilität
- **Neue Funktion (Template):** Unterstützung von 'GD' als Bildprozessor
- Bugfix (Plugin): JTL-Search funktioniert nicht mit Plugin admorris pro
- Bugfix (Plugin): Bilder der Live Sale Notifications werden verschwommen/verpixelt angezeigt
- Bugfix (Plugin): Einstellung 'Livebestellungen mit Demodaten anonymisieren' bei Live Sale Notification führt zu Fehler
- Bugfix (Template): Ergebnisse der JTL-Search werden nicht korrekt unter der Suchleiste positioniert
- Bugfix (Template): Sale Countdown schiebt sich hinter Pro Slider und Overleay-Header

---

### Version: 2.4.5

**Release:**  19.04.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Verbesserung (Template):**  Amazon Pay & Google Pay für Zahlungsanbieter-Logos
- Bugfix (Template): Kompatibilitätsproblem mit neuem 'JTL Paypal Checkout'-Plugin für Shop 5 - **verwenden Sie bitte ab dieser Version von admorris pro auch das überarbeitete Plugin 'JTL Paypal Checkout'** (erhältlich im Extension-Store:  [https://www.jtl-software.de/extension-store/paypal-checkout-jtl-shop-5](https://www.jtl-software.de/extension-store/paypal-checkout-jtl-shop-5) )

---

### Version: 2.4.4

**Release:**  14.04.2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Neue Funktion (Template):**   Neue Paginierungseinstellungen für die Produktliste: 'Standard', 'Mehr Anzeigen per Klick' & 'Endless Scroll'
- **Verbesserung (Template):**  Unterstützung von WebP-Bildern auf der Hersteller-Seite

---

### Version: 2.4.3

**Release:**  29.03. 2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- Bugfix (Plugin): Wartungsmodus führt zu Fehler auf Shopseite
- Bugfix (Plugin): Fehler bei Konfigurator- und Benachrichtigungs-Popup wenn Einstellung "Fixierten Warenkorb Button anzeigen" aktiv ist
- Bugfix (Plugin): Fehler bei Bildpfaden der Live-Sale-Notifications die mit einen "/" enden
- Bugfix (Plugin): Konsolenfehler bei Live-Sales-Notifications
- Bugfix (Plugin): Icons von Salesboostern werden bei anderen Templates nicht richtig angezeigt
- Bugfix (Plugin): Lieferländer werden nicht korrekt in der Shipping-Cost-Progress-Bar angezeigt
- Bugfix (Template): Warenkorb lässt sich nicht mehr zusammenfügen
- Bugfix (Template): Fehler bei der Berechnung der Headerhöhe

---

### Version: 2.4.2

**Release:**  25.03. 2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- Bugfix (Plugin): JTL-Consent-Modal nicht klickbar im Firefox
- Bugfix (Plugin): Bonuspunkte werden falsch für Staffelpreise und rabattierte Preise berechnet
- Bugfix (Template): Thumbnails der Produktgalerie werden zu groß angezeigt, wenn Einstellung "Containergröße verwenden" aktiv ist

---

### Version: 2.4.1

**Release:**  22.03. 2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- Bugfix (Plugin): 500 Fehler bei leeren Bildern im Popupmanager
- Bugfix (Plugin): Checkout-Motivator-Modal nicht sichtbar im NOVA Template
- Bugfix (Plugin): Cookie Notice Pro: Einstellungen und Scripts werden beim Update nicht übernommen
- Bugfix (Plugin): PHP 7.3 Kompatibilität für den Updateprozess
- Bugfix (Template): Fixierter Warenkorb Button wird bei Staffelpreisen nicht aktualisiert
- Bugfix (Template): Darstellungsfehler Klarna Checkout
- Bugfix (Template): Asterisk fehlt bei erforderlichen Formularfeldern

---

### Version: 2.4.0

**Release:**  16.03. 2022

**Kompatibilität:**   JTL-Shop 5.1 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- **Neue Funktion (Plugin):**  Volle automatische Kompatibilität und Integration mit JTL-Consent Manager. Fügen Sie eigene Scripts mit Consent jetzt einfach über unseren Cookie Manager Pro hinzu.
- **Neuer Sales Booster:**  Google Analytics Enhanced E-Commerce Tracking - Tracken Sie alle wichtigen Events wie z.B. Warenkorbaktionen oder den Funnel im Checkout.
- **Neue Funktion (Template):**   Warenkorb Button fixieren / Fixed add to Cart Button - Optionaler immer sichtbarer Warenkorbbutton auf der Produktdetailseite.
- **Neue Funktion (Smart Search):**  Hersteller von der Suche ausschließen.
- **Neue Funktion (Template):**  Neue Option zur Komprimierung von JavaScript- und CSS-Dateien "statisch".
- **Verbesserung (Template):** Überarbeitung der Bilder-Thumbs in der Galerie auf der Artikeldetailseite. Beim scrollen rasten die Thumbs am oberen bzw. linken Rand ein. Am Desktop sind die Thumbs jetzt auch mit dem Scrollwheel scrollbar. Achtung: Die HTML Struktur der Galerie hat sich geändert!
- Bugfix (Shipping Cost Progress Bar): Fehler nach Registrierung bei leerem Warenkorb.
- Bugfix (Bonuspunkte): Wert der Bonuspunkte anzeigen funktioniert nicht, Formatierungsproblem Schweizer Franken.
- Bugfix (Mailchimp API): Adresse übergibt immer Land USA und Zeichenfehler bei Nachnamen und Straße
- Bugfix (PopUp Manager): Pop Out Animation funktioniert nicht.
- Bugfix (Smart Search): URLs mit Unterordnern machen Probleme im Nova-Template.
- Bugfix (Sales Booster): Bei manchen Sales Boostern werden die Sprachvariablen beim ändern der Sprache im Frontend nicht sofort geändert.
- Bugfix (Template): Konfigurator-Sidebar überlappt mit Sticky Header.
- Bugfix (Template): Probleme mit JTL-Vouchers behoben.
- Bugfix (Template): Kategoriebild in Produktliste lädt zu großes Bild.
- Bugfix (Template): Länderauswahl zeigt nicht aktive Länder an.
- Bugfix (Template): Anzeigen der großen Produktbilder von Variationskombinationsbildern auf der Produktdeteailseite lädt falsche Image gallery.
- Bugfix (Template): Fehler durch falsch kalkulierte Bildbreiten bei Thumbnailbildern.
- Bugfix (Template): Basispreis wird nicht angezeigt.
- Bugfix (Template): Thumbnails werden in der neuen Horizontalen Galerie auf der Produktdetailseite gestaucht.
- Bugfix (Plugin): Triggerfilter Kundengruppe greift nicht
- Bugfix (Plugin): Import/Export von Slidern funktioniert nicht
- Diverse kleinere Verbesserungen für Template und Sales Booster

---

### Version: 2.3.4

**Release:**  21.12. 2021

**Kompatibilität:**   JTL-Shop 5.0.3 bis 5.1.2

**PHP Version:**   7.3 bis 8.0

Changelog:

- Bugfix (Sales Booster): SmartSearch: Bei installation des Shops in einem Unterordner wurden für die Suchergebnisse nicht die richtigen Urls ausgegeben.
- Bugfix (Template): Konfigurator Sidebar wurde vom fixed Header beim runterscrollen überdeckt.
- Bugfix  (Template): Auf Produktdetailsseiten von Variationskombinationen mit mehreren Bildern und vertikal angezeigten Thumbs wurde nach dem Laden einer neuen Variation das Hauptbild abgeschnitten.
- Bugfix (Template): Das Kategoriebild in der Produktliste wurde seit der letzten Bugfix Änderung zu groß geladen. Es wird nun wieder nur die Medium Version geladen. Das Layout ist auch wieder ähnlich wie früher. Bei dem letzten Fix wurde das Bild wie im Nova über den  Kategoriebeschreibungstext gesetzt. Jetzt ist es in Desktopansicht wieder daneben, allerdings nicht mehr als gefloatetes Element, sondern als Flexbox item. Ist am Bildschirm weniger Platz für den Text rutscht das Bild automatisch zentriert darüber.
- Bugfix (Template/Salesbooster): Elements / Popupmanager Kundengruppen Triggerfilter hatte einen Fehler und hatte deshalb nicht korrekt funktioniert.
- Bugfix (Template): Baseprice-Anzeige in der Produktliste funktioniert wieder
- Bugfix (Template): Warenkorb-Dropdown Höhenberechnung hatte einen Fehler, der besonders bei mehreren Artikeln im Warenkorb bei aktiver Shipping Progress Bar den Mini-Warenkorb unten abgeschnitten hat.

---

### Version: 2.3.3

**Release:**  29.11. 2021

**Kompatibilität:**   JTL-Shop 5.0.3 bis 5.1.1

**PHP Version:**   7.3 bis 8.0

**Changelog:**

- Bugfix (Sales Booster): Adventskalender: NOVA Template body padding fix für open modal
- Bugfix (Sales Booster): Adventskalender: Spinner des Adventskalenders reparieren, wenn Template nicht admorris_pro ist
- Bugfix (Sales Booster): Google Kundenbewertung: Lieferland wird unter gewissen Einstellungen falsch ausgegeben
- Bugfix (Sales Booster): Bonuspunkte: Kundengruppeneinstellunen werden bei leerem Feld nicht mit Fallback gefüllt
- Bugfix (Sales Booster): Shippingcost Progress Bar: Warenkorbwert setzen, wenn der Warenkorb leer ist

---

### Version: 2.3.2

**Release:**  16.11. 2021

**Kompatibilität:**   JTL-Shop 5.0.3 bis 5.1.1

**PHP Version:**   7.3 bis 8.0

**Changelog:**

- **Neue Funktion (Plugin):**  PHP 8.0 kompatibel
- **Neue Funktion (Plugin):**  IonCuber Loader nicht mehr notwendig
- **Neue Funktion (Template):**  Debug Mode für Elements um alle Elements gleichzeitig zu deaktivieren
- Bugfix (Template): Pro Slider verlinkt automatisch auf Startseite
- Bugfix (Template): Problem bei Hochformatbildern im Slickslider Produktdetailseite
- Bugfix (Template): Ausblenden der Kategoriebilder in der Listenansicht
- Bugfix (Template): Verfügbarkeitsanfrage als Popup hatte nicht mehr funktioniert
- Bugfix (Template): Probleme mit WebP Bildern aus OPC Ordner in Safari
- Bugfix (Template): Strukturierte Daten aus der Produktliste entfernt ( Rich Snippets Fehler)
- Bugfix (Sales Booster): Adventkalender in Fremdtemplates wird richtig dargestellt
- Bugfix (Sales Booster): Bei Adventkalendertürchen Umlaute und Bilder werden nicht richtig angezeigt
- Bugfix (Sales Booster): Shipping Cost Progressbar reagiert auf Staffelpreise
- Bugfix (Sales Booster): Smart Search API Error behoben
- Bugfix (Sales Booster): Bonuspunkte verwalten
- Bugfix (Sales Booster): Automatisch Bonuspunkte entfernen bei Reduktion von Artikeln im Warenkorb
- Bugfix (Sales Booster): Mailchimp sendet Neukunden API nicht

---

### Version: 2.3.1

**Release:**  01.09. 2021

**Kompatibilität:**   JTL-Shop 5.0.3 bis 5.1.1

**PHP Version:**   7.3 bis 7.4 (+ionCube)

![](https://img.zohostatic.eu/zde/static/images/caution.png) Noch nicht kompatibel mit PHP 8.0!

**Changelog:**

- **Neue Funktion (Template):** Optionales Laden von FontAwsome 5
- Bugfix (Template): SrcSet führt bei SVG Logo zu Größenproblemen im Header
- Bugfix (Template): Sliderverlikung auch ohne Button
- Bugfix (Template): elFinder funktioniert nicht in Subdirectory
- Bugfix (Template): Logo größenattribut für Google PageSpeed
- Bugfix (Sales Booster): ShippingCostProgress Bar Styling Problem

---

### Version: 2.3.0

**Release:**  01.09. 2021

**Kompatibilität:**   JTL-Shop 5.0.3

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- **Neue Funktion (Template):** Pro Slider 2.0 mit verbesserter Speedperformance
- **Neue Funktion (Template):**  Pro Slider neue Option für Mobil und Desktop
- **Neue Funktion (Template):** SrcSet Creator zum erstellen von SrcSets und WebP Bilderfür Slider und Content zur Speedoptimierung
- Erweiterung (Template): Footer Icons werden als Sprite statt als Font geladen für bessere Speedperformance
- Erweiterung (Template): Source-Sets für Logo im Header für bessere Speedperformance
- Bugfix (Sales Booster): PopUp-Manager: Klick/Interaktion mit PopUp mit NOVA nicht möglich, Newsletter mit admorris pro nicht möglich, Newsletter-Bildhinweis
- Bugfix (Sales Booster): Mailchimp Cron Job bleibt bei Kundendaten mit NULL-Werten hängen
- Bugfix (Sales Booster): Mailchimp: Neukunden werden nicht versendet, Update des Abo-Status entfernen
- Bugfix (Sales Booster): PopupManager: Element kann nicht gelöscht werden
- Bugfix (Template): Variationslabels werden nicht angezeigt
- Bugfix (Template): Off-Canvas Filter lassen sich auf weiteren Seiten nicht öffnen
- Bugfix (Template): Breadcrumbs links: Problem mit absoluten und relativen Links
- Bugfix (Template): Einfügen von \<base> Tag
- Bugfix (Template): Fehler im Layout der Einkaufswagentabelle, wenn Nettopreise aktiv sind
- Bugfix (Template): Probleme mit Transparenten Bilder behoben
- Bugfix (Template): Uploadmodul funktioniert nicht
- Bugfix (Template): OPC Akkordeon: Erste Gruppe bereits erweitern Funktion funktioniert nicht
- Bugfix (Template): Kasse->Zusammenfassung: Lieferadresse/Rechnungsadresse kann nicht getrennt geändert werden
- Bugfix (Backend): Standardwert für ausgewähltes Symbol in IconSelectorSlim hinzufügen

---

### Version: 2.2.2

**Release:**  13.07. 2021

**Kompatibilität:**   JTL-Shop 5.0.3

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix: Backend funktioniert nicht ordnungsgemäß, bei Installation in einem Unterordner
- Bugfix (Template): Header Padding der einzelnen Zeilen funktioniert nicht
- Bugfix (Sales Booster): PopUp-Manager Sale Countdown ohne Bild versucht nicht vorhandenes Bild anzuzeigen

---

### Version: 2.2.1

**Release:**  30.06. 2021

**Kompatibilität:**   JTL-Shop 5.0.3

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (Template): Speichern bei Elements führt zu doppeltem Escaping
- Bugfix (Template): Fußzeile admorris Branding: ungültiger Code
- Bugfix (Template): Thumbnailbilder werden bei Horizontalerausrichtung auf der Produktdetailseite mobil nicht in Slidern dargestellt
- Bugfix (Sales Booster): Cookie Notice Pro öffnet sich nicht, wenn Pushnotifications aktiv sind
- Bugfix (Sales Booster): Cookie Notice Pro Standardselector angepasst
- Bugfix (Sales Booster):  Shipping Countdown Sprachvariable "schon heute" nicht angezeigt.

---

### Version: 2.2.0

**Release:**  28.06. 2021

**Kompatibilität:**   JTL-Shop 5.0.3

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Neuer Sales Booster: Shipping Cost Progress Bar
- Neuer Sales Booster: EU Energy Lables
- Neuer Sales Booster: Recent Visitors
- Neue Funktion (Sales Booster): Neue Option zum Runden von Bonuspunkten
- Neue Funktion (Template): Neue Option für Thumbnailslider auf Produktdeteilseite vertikal/horizontal
- Neue Funktion (Template): Neue Option für Produktbild auf Produktdeteilseite sticky/nicht sticky
- Erweiterung (Template): Fallback für strukturierte Daten (Beschreibung)
- Erweiterung (Template): Cachebusting für Child-Template integriert
- Bugfix (Template): Number input bei teilbarer Stückzahl fehlerhaft
- Bugfix (Template): Verfügbarkeitsanzeige bei Variationskombinationsauswahl (Dropdown) fehlerhaft
- Bugfix (Template): Unnötiges img Tag in review_item.tpl
- Bugfix (Template): OPC Buttons fehlerhaft
- Bugfix (Template): Bei Neuinstallation fehlt YouTube Icon
- Bugfix (Template): Headingsstruktur auf Produktdetailseite optimiert
- Bugfix (Template): Klarna Checkout Radiobutton fehlerhaft
- Bugfix (Template): Slick-Slider Navigationspfeile werden nicht angezeigt
- Bugfix (Template): Titeltext wird escaped (Breadcrumbs und Produktbild)
- Bugfix (Template): Sortier- und Filter-Dropdowns in der Produktliste auf dem Handy abgeschnitten
- Bugfix (Template): Das Upload-Modul funktioniert nicht
- Bugfix (Sales Booster): Bonuspunkte im Warenkorb besser anzeigen und löschbar machen
- Bugfix (Sales Booster): Sonderangebotcountdown selector für admorris Pro Template falsch
- Bugfix (Sales Booster): SmartSearch curl Errorhandling für Verbindungen
- Bugfix (Sales Booster): Preisdiskriminierung und Rabattanzeige neu strukturiert und Performance verbessert

---

### Version: 2.1.3

**Release:**  04.06. 2021

**Kompatibilität:**   JTL-Shop 5.0.3

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (Template): Kategorien im Header werden bei Shopversion 5.0.3. nicht angezeigt
- Bugfix (Template): Vergleichslistebutton wird in Shopversion 5.0.3 nicht angezeigt
- Bugfix (Template): Artikelbilder haben fehlende strukturierte Daten (image itemprop)
- Bugfix (Template): Blog-Banner-Bilder funktionieren nicht mit mehreren Url-Segmenten

---

### Version: 2.1.2

**Release:**  29.04. 2021

**Kompatibilität:**   JTL-Shop 5.0.0 - 5.0.2

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (Template): Fehler bei Subkategorie srcset Attribut
- Bugfix (Template): OPC Bilder-Slider funktioniert nicht
- Bugfix (Template): JTL-Search funktioniert nicht mit admorris Pro Template
- Bugfix (Template): "Scroll-To-Top-Button" wird nicht korrekt oder gar nicht angezeigt
- Bugfix (Template): Ältere Safari-Versionen zeigen die neuen Icons nicht an
- Bugfix (Template): Elements speichern error handling
- Bugfix (Template): Push Notifications: Schreibfehler bei den Sprachvariablen im Plugin.
- Bugfix (Template): Brand-Logo anzeigen checkbox bleibt immer aktiviert
- Bugfix (Template): Fehlende wrapping smarty blocks in templates
- Bugfix (Template): Display notice for activating admorris plugin if template is set
- Bugfix (Template): Menübuttons mobil zu klein
- Bugfix (Template): Fehlende Icon "Bearbeiten"
- Bugfix (Template): Ausverkauftbutton bei Hover nicht schön
- Bugfix (Template): Globale Containergröße lässt sich nicht ändern.
- Bugfix (Template): Icons verschwinden wenn Animationen im Betriebssystem deaktiviert sind
- Bugfix (Sales Booster): Sale Countdown Experten-Einstellungs Selektoren für Nova Template optimiert
- Neu (Template): Bootstrap 4 Utility Klasse d-none hinzugefügt

---

### Version: 2.1.1

**Release:**  09.04. 2021

**Kompatibilität:**   JTL-Shop 5.0.0 - 5.0.1

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Neue Funktion (Template): Neuer Wartungsmodus mit Impressum
- Bugfix (Sonderangebot Contdown): Darstellungsproblem im Novatemplate
- Bugfix (Template): Wartungsmodus Fehler behoben
- Bugfix (Template): Offcanvas Herstellermenü doppelter Inhalt
- Bugfix (Template): Font Awsome 4 im One-Page-Composer laden
- Bugfix (Template): Font Awsome 4 immer laden funktioniert nicht
- Bugfix (Template): Eazyzoom Flyout hat keine Mindestbeite
- Bugfix (Template): Neuer Block in config.tpl
- Bugfix (Template): CRM Link funktion fehlerhaft
- Bugfix (Template): "Ausverkafut"-Button soll versteckt wenn Produkt nicht käuflich
- Bugfix (Template): Unterkategoriebilder haben keine size="auto" und falsches srcset

### Version: 2.1.0

**Release:**  07.04. 2021

**Kompatibilität:**   JTL-Shop 5.0.0 - 5.0.1

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Neue Funktion (Template): Neues Icon-System mit über 1600 Icons sowie der Möglichkeit jedes Icon individuell auszutauschen
- Neue Funktion (Template): WebP Support
- Neue Funktion (Template): Neue Performanceoption "Progressives Laden von Bildern"
- Neue Funktion (Template): Diverse Speedoptimierungen
- Neue Funktion (Template): Neue Option um Herstellerlogo auf Artikeldetailseite anzuzeigen
- Neue Funktion (Template): Serviceworker für Template
- Neue Funktion (Template): Neue Socialmedia Option TikTok
- Bugfix (Bonuspunkte): Punkte können bei Schweizer Franken nicht eingelöst werden
- Bugfix (Live Sales Notifications): Sprachvariablen werden nicht ausgegeben
- Bugfix (Backend): Smart Search kann nicht deaktiviert werden, wenn keine Sales Booster mehr verfügbar sind
- Bugfix (Backend):  Sprachauswahl im Headerbilder hat leeren Dropdownwert
- Bugfix (Template): Produktvorschau hat fehlende Styles
- Bugfix (Template): Bootstrap Sicherheitsupdate
- Bugfix (Template): Kleinere Templatefixes
- Bugfix (Template): Maximale Logobreite für Mobil funktioniert nicht einwandfrei
- Bugfix (Sales Booster): Wenn kein admorris Pro Template aktiv ist kommt es zu loadjs undefined Fehlern
- Bugfix (Checkout Motivator): Einheit (€/%) wird doppelt angezeigt.

---

### Version: 2.0.6

**Release:**  19.02. 2021

**Kompatibilität:**   JTL-Shop 5.0.0 - 5.0.1

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (PopUp-Manager / Checkout Motivator): PopUp Kompatibliltät zu bestimmten Fremdtemplates
- Bugfix (Template): Template zeigt 500er Error, wenn keine Herstellerseite angelegt ist, aber die Hersteller im Header aktiv sind.
- Bugfix (Template): Lizenzabfrage für Template macht fehler bei bestimmten Fremdtemplates
- Bugfix (Template): Filter werden mobil nicht optimal angezeigt
- Bugfix (Template): Bootstrap Sicherheitsupdate
- Bugfix (Template): Kleinere Templatefixes

---

### Version: 2.0.5

**Release:**  05.02. 202

**Kompatibilität:**   JTL-Shop 5.0.0 - 5.0.1

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Update (Template): Updates für Shopversion 5.0.1
- Bugfix (Mailchimp API): Neuer Store wird mit falscher ID Angelegt, daher werden bei Neuinstalation keine Produkte etc. übergeben
- Bugfix (Backend): Headerbuilder/Menü Logoeinstellung zeigt fälschlicherweise Px als Einheit an.
- Bugfix (Template): Merkmalfilter "mehr anzeigen" funktioniert nicht
- Bugfix (Template): Filter werden mobil nicht optimal angezeigt
- Bugfix (Template): Kategoriebanner Parallaxeffekt kann nicht deaktiviert werden
- Bugfix (Template): Größeneinstellung für Sticky Logo Mobil greift nicht
- Bugfix (Template): Kleinere Templatefixes

---

### Version: 2.0.4

**Release:**  26.01. 2021

**Kompatibilität:**   Min. JTL-Shop 5.0.0

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (Template): Matrix Warenkorbbutton doppelt
- Bugfix (Checkoutmotivator): Nach Update Prozentzeichen doppelt -> Checkoutmotivator funktioniert nicht
- Bugfix: Anzeige fehlende Lizenz bei Fremd-Child-Template wenn keine Templatelizenz vorhanden

---

### Version: 2.0.3

**Release:**  12.01. 2021

**Kompatibilität:**   Min. JTL-Shop 5.0.0

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (Template): Paralaxeffekt bei Kategoriebanner lässt sich nicht aktivieren
- Bugfix (Template): EAN/GTIN, ISBN & Hazardnummer fehlen
- Bugfix (Template): Slider wird bei Einstellung von fixem Seitenverhältnis nicht angezeigt
- Bugfix (Pro Slider): Speichern von Seitentyp, Kundengruppe und Sprache funktioniert manchmal nicht
- Bugfix (Mailchimp API): API Key lässt sich nicht speichern
- Bugfix (Mailchimp API): Warenkorb URL wird falsch gesendet
- Bugfix (Adventkalender): Java-Scriptfehler wenn Kalenderfenster außerhalb Dezember geöffnet werden

---

### Version: 2.0.2

**Release:**  04.01. 2021

**Kompatibilität:**   Min. JTL-Shop 5.0.0

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (Elements): Elements werden nicht geladen/können nicht hinzugefügt werden.
- Bugfix (Pro Slider): Bilder aus Unterordnern werden nicht richtig gespeichert und dadurch im Frontend auch nicht geladen.

---

### Version: 2.0.1

**Release:**  27.12. 2020

**Kompatibilität:**   Min. JTL-Shop 5.0.0

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Changelog:**

- Bugfix (Backend): Backend funktioniert nicht, wenn Plugins wie Amazon Pay oder PayPal aktiviert sind, da diese eine wichtige Shopvariable überschreiben.
- Bugfix (Headerbuilder): Initiale Suchoption mobil wird falsch angezeigt AT-1978
- Bugfix (Sonderangebot Countdown): Countdown wird geladen obwohl kein Enddatumg gesetzt
- Bugfix (Smart Search): Suchindex kann nicht gelöscht werden

---

### Version: 2.0.0

**Release:**  17.12. 2020

**Kompatibilität:**  Min. JTL-Shop 5.0.0

**PHP Version:**   7.3 bis 7.4 (+ionCube)

**Serverarchitektur:**    64bit

**Changelog:**

- Neue Funktion (Template): Kompatibilität und Portletpunkte für den OnPage Composer für Drag & Drop Inhalte
- Neue Funktion (Template): Artikelübersicht optionales zweites Artikelbild bei Hover
- Neue Funktion (Template): Headerbuilder hat nun 4 Textboxen für eigene Inhalte
- Neue Funktion (Template): Artikeldetailansicht Produktbilder scrollen mit
- Neue Funktion (Template): In den Warenkorblegen in der Artikelübersicht per Ajax
- Neue Funktion (Template): Diverse Page Speed Optimierungen nach LCP und Layoutshift
- Neue Funktion (Template): Reduzierung von Abständen, Weißräumen und Überschriftengrößen
- Neue Funktion (Template): Social Icon Positionierung im Footer verbessert
- Neue Funktion (Template): Neue Newsletter Option im Footer
- Neue Funktion (Smart Search): Suchergebnisse werden nach Kundengruppen gefiltert
- Neue Funktion (Backend): Plugin Backend für besser Usability überarbeitet
- Neue Funktion (Backend): Plugin Backend in Englisch
- Neue Funktion (Backend): Umstellung auf Versionierung nach Semantic Versioning
- Bugfix (Adventskalender): Fehler mit nachträglich hinzugefügten Sprachen wurde behoben

---

### Version:  1.15 Build 7

_Dieses Buildupdate kann mit der Funktion "Update erzwingen" eingespielt werden._

**Release:**  16.09.2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Bugfix: (Mailchimp API): Wenn admorris Pro Template nicht aktiviert ist, funktioniert der Abgleich nicht (LoadJs Fehler)
- Bugfix: (Checkout Motivator): Countdown wird nicht geladen
- Bugfix: (Template): PopUps können teilweise nicht geladen werden (LoadJs Fehler)

---

### Version:  1.15 Build 6

_Dieses Buildupdate kann mit der Funktion "Update erzwingen" eingespielt werden._

**Release:**  31.08.2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Bugfix: (Backend: Globale Einstellungen) Auswahl von Farbtheme verursacht Speicherfehler
- Bugfix: (Template): linkgroup_list.tpl Scope Bug

---

### Version:  1.15 Build 5

_Dieses Buildupdate kann mit der Funktion "Update erzwingen" eingespielt werden._

**Release:**  25.08.2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Neue Funktion (Template): Logogrößeneinstellungen für den Mobilen Sticky Header hinzugefügt
- Bugfix: (Cookiehinweis Pro): Google Analytics Script fehlerhaft → Tracking funktioniert nicht
- Bugfix: (Cookiehinweis Pro): Position Center/Center wird mobil nicht angezeigt
- Bugfix: (Template): Blogkategoriebeschreibung wird nicht dargestellt, wenn Banner aktiviert ist

---

### Version:  1.15 Build 4

_Dieses Buildupdate kann mit der Funktion "Update erzwingen" eingespielt werden._

**Release:**  06.08.2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Bugfix (Template): Logogrößeneinstellungen greifen nicht + neue Standardwerte
- Bugfix: Sprachvariablen werden vom JTL-Shop beim wechseln der Sprache nicht sofort übersetzt, im Plugin gelöst, bis JTL das selbst löst

---

### Version:  1.15 Build 3

_Dieses Buildupdate kann mit der Funktion "Update erzwingen" eingespielt werden._

**Release:**  04.08.2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Neu: Sales Booster Zusatzprodukt
- Bugfix (Cookiehinweis): Z-Index angepasst
- Bugfix (Template): AT-649 Variationsbilder werden nach verwenden der Pagination in der Artikelliste nicht ordnungsgemäß geladen
- Bugfix (Template): AT-68 Reviews werden nicht richtig gezählt
- Bugfix (Template): Fehler bei Google Analytics Script
- Bugfix (Template): AT-648 Reviews werden sprachen übergreifend nicht richtig gezählt
- Bugfix (Template): AT-636 X-Selling Push-To-Basket wird in Modal nicht initialisiert
- Bugfix (Template): AT-622 Templatesprachvariablen für Overlaybilder richtig anlegen
- Bugfix (Mailchimp): Warenkorb auf Kundenkontoseite nicht übertragen
- Neue Funktion (Template): SVG-Logos und Logogrößen (Einstellung in Menü verschoben)

---

### Version:  1.15 Build 2

_Dieses Buildupdate kann mit der Funktion "Update erzwingen" eingespielt werden._

**Release:**  07.05. 2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Neu: Pluginsprachvariablen in Französisch
- Bugfix (Template: Artikeldetailseite): Google reCaptcha funktioniert nicht in "Frage zum Produkt" und "Benachrichtigen wenn Verfügbar" Modal
- Bugfix (Template): Kleinere Styleanpassungen
- Neue Funktion (Template): Neues Attribut zum verstecken von Kategorien
- Neue Funktion (Template): Neues Attribut um Kategorien eigene Klassen zuzuweisen
- Bugfix (Liefer- & Versandanzeige): Javascript Countdown onload fix
- Bugfix (Cronjob): Debugmodus deaktiviert
- Bugfix (PopUp Manager): Newsletterpopup doppelte ID

---

### Version:  1.15 Build 1

_Dieses Buildupdate kann mit der Funktion "Update erzwingen" eingespielt werden._

**Release:**  23.04. 2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Bugfix (Mailchimp): Double-Opt-In Anmeldungen werden frühzeitig auf "subscribed" gesetzt
- Bugfix (Template) AT-517: Schema.org breadcrumb itemref auf Seiten die keine Breadcrumb haben
- Bugfix (Template: Artikelliste) AT-572: Bei aktiveren linker Seitenspalte und ohne Off-Canvas-Filter entsteht viel weißer Raum
- Bugfix (Template) AT-570: FitVid.js wird nicht geladen
- Bugfix AT-560: Funktionskonflikt getCategories()
- Bugfix (Liefer- & Versandanzeige): JavaScript Countdown wird nicht immer angezeigt
- Neue Funktion (Template): Image Title über Alt-Attribut auf Produktdetailseite steuerbar

---

### Version:  1.15

**Release:**  20.04. 2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.3 (+ionCube)

**Changelog:**

- Neue Funktion (Template): Scroll-Back-To-Top-Button
- Neue Funktion (PopUp Manager): Priorität & kopieren
- Neue Funktion (Checkout Motivator): Einschränkung nach Kundengruppe, Kategorie, Hersteller und Artikelnummern
- Neue Funktion (Checkout Motivator): Steuersatz einstellbar
- Neue Funktion (Liefer & Versandanzeige): Java-Script Countdown
- Bugfix (Bonuspunktesystem): Fehler bei PayPal Express und PayPal Plus
- Bugfix (Preisdifferenzierung): Fehler bei PayPal Plus
- Bugfix (Mailchimp API): Fehler beim Senden der Produkte und Warenkörbe
- Bugfix (Pushnachrichten): Scriptfehler im Checkout
- Bugfix (Wiederbestellen): Scriptfehler im Checkout und Artikelreihenfolge
- Bugfix (Liefer & Versandanzeige): Berechnungsfehler bei Releaseartikeln
- Bugfix (Livesales Notifications): Verursachen ggf. Fehler auf Newsseite
- Bugfix (PopUp Manager: Fehler bei  Triggerfilter: "Warenkorb größer gleich"
- Bugfix (SmartSearch): Sprache wird nicht richtig übertragen, wenn Standardsprache im Shop nicht mit Standardsprache in Wawi übereinstimmen.
- Bugfix (Template: Vergleichsliste): Darstellungsfehler
- Bugfix (Template: Produktliste): Produkte überlappen manchmal, wenn Seite nicht gecached ist
- Bugfix (Template: Footer): Sortierung der Zahlungsicons funktioniert nicht
- Bugfix (Template: Warenkorbdropdown): Zur Kasse Button bei zu vielen Artikeln ggf. nicht sichtbar
- Bugfix (Template: Checkout): Mengen werden im Bestellabschluss nicht angezeigt.
- Bugfix (Template: Produktdetailseite): Unverkäufliche Artikel über Attribut sind kaufbar
- Sowie diverse kleinere Bugfixes

---

### Version:  1.14 Build 2

**Release:**  03.02. 2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Bugfix: PayPal PLUS Payment Wall Ladefehler

---

### Version:  1.14 Build 1

**Release:**  31.01. 2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Bugfix: Mailchimp API Listen ID wird nicht richtig gespeichert
- Bugfix: Wiederbestellfunktion Icon kann nicht geladen werden und führt zu Fehler
- Bugifx: Megamenü funktioniert im neusten Safari Browser nicht korrekt

---

### Version:  1.14

**Release:**  23.01. 2020

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Neu: Speed Boost durch Komplettoptimierung der zu ladenden Scripte, CSS und Java-Script für Template und alle Sales Booster
- Neu: Sales Booster Mailchimp E-Commerce API
- Neu: Sales Booster Preisdifferenzierung
- Neue Funktion (Bonuspunktesystem): Option Gültigkeitsdauer (Rollend und Hart), neue Punkteverwaltung mit Suche
- Neue Funktion (Pro Slider): Optionen zur Bildausrichtung für besseres Responsiveverhalten
- Neue Funktion (Live Chat): Optionen für tawk.to Widget (für Trigger, Webhooks etc. und bessere Ladezeiten) statt Iframelösung
- Optimierung (Accessibility): Keyboard-Fokus deutlich sichtbar; Header-Dropdownmenüs: Fehlerbehebung bei Keyboardnavigation
- Optimierung (Accessibility): Berücksichtigung der prefers-reduced-motion Betriebssystem-Einstellung für Dropdown-Menüs
- Optimierung (Emojiregen): Mobil werden nun weniger Emojis geladen
- Optimierung (PopUpmanger): Verbesserung der Größeneinstellungen
- Bugfix (Elements): Doppelte Anführungszeichen sind nun möglich
- Bugfix (Elements): Zeichenfehler im Namen bei Elements
- Bugfix (Bonuspunktesystem): Fehler bei PayPal Express
- Bugfix (Bonuspunktesystem): Anzeige im Warenkorb über 1000€
- Bugfix (Rabattanzeige): Rabatte bei Kinderartikeln werden nun richtig dargestellt
- Bugfix (Rabattanzeige): Umrechnung bei Sonderpreisen in Fremdwährungen

---

### Version:  1.13b

**Release:**  7.11. 2019

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Bugfix: Lizenzfehler Cookiehinweis Pro

_Sollten Sie die Version 13 bereits installiert haben und diesen Bug beheben wollen, klicken Sie bitte auf  **Update erzwingen** ._

---

### Version:  1.13

**Release:**  6.11. 2019

**Kompatibilität:**  Min. JTL-Shop 4.06. Build 13

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Neuer Sales Booster: Cookiehinweis Pro
- Neuer Sales Booster: Emojiregen
- Neuer Triggerfilter
- Bugfix: Artikelliste in alten Safaribrowsern wird fehlerhaft dargestellt
- Bugfix: Artikelliste im Internetexplorer wird fehlerhaft dargestellt
- Bugfix: Asyncrones Analytics E-Commerce Tracking - ID Fehler (Template)
- Bugfix: Fehler im Megamenü (Template)
- Bugfix: Shopvote - ID Fehler
- Bugfix: Lazy Load in Artikelliste hängt sich auf, wenn man den Zurückbutton des Browsers verwendet
- Bugfix: Listenansicht Darstellungsfehler in Mobil

---

### Version:  1.12 Build 3

**Release:**  11.09. 2019

**Kompatibilität:**  Diese Version unterstützt nur noch JTL-Shop 4.06.13 bis 4.06.14

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Bugfix: Wiederbestellen Styles fehlen wenn gleichzeitig Preisrabattanzeige aktiv
- Bugfix: Falscher z-index in Styles führt zu Überlagerungen bei Live Sale Notifications Anzeige.
- Bugfix: Doppeltes Recent Sales Formular im Backend.
- Bugfix: Logo wurde nur dann richtig angezeigt, wenn auch ein Overlay Logo hochgeladen wurde.
- Optimierung: Promise polyfill & simplebar.js lokal laden.
- og:image metatag für Blog und Artikel angepasst.
- Template: 'webfont-loading' Smarty Block in header.tpl hinzugefügt, um bei im Child Template eigene Fonts hinzuzufügen und die standardmäßig geladenen zu entfernen zu können.

---

### Version:  1.12 Build 2

**Release:**  27.05. 2019

**Kompatibilität:**  Diese Version unterstützt nur noch JTL-Shop 4.06.12 bis 4.06.14

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Bugfix: Pro Slider Kategoriefilter verschwindet nach erneutem Laden
- Bugfix: Listenansicht Actionbuttons (Vergleichsliste ...) funktionieren unter Umständen nicht richtig

---

### Version:  1.12

**Release:**  27.05. 2019

**Kompatibilität:**  Diese Version unterstützt nur noch JTL-Shop 4.06.12 bis 4.06.14

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Neuer Sales Booster: Rabattanzeige
- Pro Slider individuell für einzelne Seiten steuerbar (z.B. Artikel, Kategorien, eigene Seiten), HTML Code im Slider verwendbar
- Smart Search: Filter für Vater- & Kinderartikel, Multidomain fähig ( [https://admorris.com/shop/Multidomain-Plugin](https://admorris.com/shop/Multidomain-Plugin) )
- Neue Filter für den PopUp-Manager und Elements: Eigene Felder, Attribute, URL, Bruttopreise
- Neues Analytics E-Commerce Tracking ( analytics.js)
- Overlaybilder können statt Lables aktiviert werden
- Bugfix: Live Sales Notifications "undefined"
- Neue Vorschau für Schriftarten
- Java-Script Optimierungen & Komprimierungen
- Pluginsprachvariablen in Italienisch verfügbar
- Sowie diverse kleinere Fixes

---

### Version:  1.11

**Release:**  15.05. 2019

**Kompatibilität:**  Diese Version unterstützt nur noch JTL-Shop 4.06.10 bis 4.06.13

**PHP Version:**  5.6 bis 7.2 (+ionCube)

**Changelog:**

- Neue Import-/Exportfunktion mit Demoshops als Startpunkt
- Neues Bloglayout
- Neuer Sales Booster: Recent Sales
- Elements: Viele neue Modifikatoren, Kategoriesierung und Prioritäten
- Neue Filter für den PopUp-Manager und Elements: Produkte im Warenkorb, zuletzt in den Warenkorb gelegtes Produkt, Anzahl der Artikel im Warenkorb, Sprache
- Bugfix: Live Sales Notifications "undefined"
- Bugfix: Off-Canvasmenü Kategorien werden nun wieder richtig geladen
- Bugfix: Liefer- und Versandanzeige Berechnungsfehler bei Anzeige des Versanddatums nach Versandschluss
- Bugfix: Sliderlinks können nun auch auf Videos gesetzt werden
- Bugfix: Megamenü Icons werden jetzt richtig dargestellt
- Neue Scrollingfunktion für das Warenkorbdropdownmodal
- Sowie diverse kleinere Fixes

---

### Version:  1.10b2

**Release:**  16.04. 2019

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.11

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- Bugfix: Checkout Motivator funktioniert nicht
- Bugfix: Herstellermenü in Mobiler Ansicht
- Bugfix: Überschriftgrößenfaktor ändert Kategorietitel nicht
- Bugfix: Diverse Templatefehler
- Bugfix: Bonuspunkte werden bei Bestellübersicht angezeigt

---

### Version:  1.10

**Release:**  20.03. 2019

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.11

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- Wiederbestellfunktion
- Neuer Filter "URL enthält" für Elements und PopUp-Manager
- Diverse kleine Verbesserungen Templatedarstellung
- Live Sales Notifications: Anzeigedauer als neue Option
- Bonuspunkte: Unsterstützung von Fremdwährungen
- Bugfix: CMS-Megamenü funktioniert in Off-Canvas nicht
- Bugifx: FlipClock bei Varkombikindern nicht geladen
- Bugfix: Kommerzahlen können in manchen Browsern nicht eingegeben werden
- Bugfix Style-Kompilierung funktioniert in Edge nicht

---

### Version:  1.09

**Release:**  08.02.2019

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.11

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- Google Kundenrezensionen
- Neuer Filter "Gerätetyp" für Elements und PopUp-Manager
- Neues responsive Sliderlayout
- Diverse kleine Verbesserungen Templatedarstellung
- Bugfix: Maximale Breite bei Suche greift nicht
- Bugfix: YouTube-Videoslider bei Installation in Unterordner funktioniert nicht
- Bugfix: PopUp-Manager Cookie wird nicht bei Button- und Linkklick nicht gesetzt
- Bugfix: Backend weißer Bildschirm bei Safari im Reiter Schaltplan
- Bugfix: Live Chat Darstellungsfehler

---

### Version:  1.08

**Release:**  24.01. 2019

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.11

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- Live Sales Notifications
- Verbesserungen Templatedarstellung
- Neue Experteneinstellungen für CSS Selectoren der Sales Booster
- Fehlerbehebung bei Varkombiauswahl auf Artikelseite
- Bugfix: Checkout Motivatior Countdownwidget wird nun nach abgeschlossenem Kauf nicht mehr angezeigt
- Bugfixes: Pushnachrichten bei Anfrage durch Template funktioniert nicht
- Bugfix: Bonuspunktesyste Backend

---

### Version:  1.07

**Release:**  08.01. 2019

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.11

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- Bonuspunktesystem
- Stock Progress Bar
- Bugfix: Release Countdown

---

### Version:  1.06

**Release:**  21.12.2018

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.11

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- Anpassungen Lizenzsystem

---

### Version:  1.05

**Release:**  03.12. 2018

**Kompatibilität:**  JTL-Shop 4.06.10 bis 4.06.11

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- kompatibel mit JTL-Shop 4.06.10 und 4.06.11
- kleinere Bugfixes

---

### Version:  1.04

**Release:**  23.11. 2018

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.9

**PHP Version:**  5.6 bis 7.20 (+ionCube)

**Changelog:**

- Adventskalender
- kleinere Bugfixes

---

### Version:  1.03

**Release:**  15.11. 2018

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.9

**PHP Version:**  5.6 (+ionCube)

**Changelog:**

- Sales Countdown
- Sales PopUps
- Black Friday & Cyber Monday Grafiken für Slider und PopUps
- Verfügbar für PHP 7.1 und PHP 7.2
- Childtemplate kompatibel

---

### Version:  1.02

**Release:**  23.10. 2018

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.9

**PHP Version:**  5.6 (+ionCube)

**Changelog:**

- Elements
- Custom CSS/Less Editor
- Überarbeitete Bildergalerie auf Produktdetailansicht
- Neues Bloglayout
- Diverse Bugfixes

---

### Version:  1.01

**Release:**  01.09. 2018

**Kompatibilität:**  JTL-Shop 4.06 bis 4.06.9

**PHP Version:**  5.6 (+ionCube)

**Features:**

- Layout&Design
- Headerdesigner
- Pro Slider
- Footer
- SmartSearch
- Pushnachrichten
- PopUp Manager
- Live Chat
- Whatsapp Chat
- Shopbewertungen
- Sonderangebot Countdown
- Release Countdown
- Liefer- & Versandanzeige
- Cookiehinweis
