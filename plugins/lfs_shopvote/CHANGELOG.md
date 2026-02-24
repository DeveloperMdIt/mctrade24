# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.1.5] - 2025-11-07
- Verbessertes Sync-Feedback
- Kleinere Optimierungen und Bugfixes

## [1.1.4] - 2025-06-17
- Bugfix beim Anzeigen von Artikeln die Unterartikeln haben. Die Bewertungen der Vateraktikel werden jetzt zu denen der Kinder dargestellt

## [1.1.3] - 2024-12-17
- Manuelle Produkt-Bewertungs-Sync im Plugin-Admin-Menü hinzugefügt
- Kleinere Optimierungen und Bugfixes

## [1.1.2] - 2024-12-03
- Fehler im Migrationscode behoben

## [1.1.1] - 2024-12-02
- Mysql-Migration angepasst um auch MySQL Versionen < 8.0.13 zu unterstützen (Dennoch: Haltet eure Serversoftware up-to-date :))
- Hinzufügen eines CLI Kommandos um manuelle Review-Synchronisation durchzurühren: "lfs_shopvote:reviews:sync [days]"

## [1.1.0] - 2024-11-15
- Änderung der Speicherung und Abrufung von Shopvote Bewertungen - Der Cronjob ist nun alleine für die Aktualisierung zuständig
- Serverfehler bei fehlenden Coverbildern behoben
- Performance stark verbessert, vor allem bei Artikeln mit vielen Varianten
- Fehlendes Feld "itemReviewed" in den schema.org Meta-Tags ergänzt

## [1.0.9] - 2024-07-11
- Aktualisierung von Bewertungen werden nun einmal täglich über den shopeigenen Cronjob-Manager ausgeführt

## [1.0.8] - 2023-04-21
- Kommunikation mit der Shopvote-API optimiert | Timeouts festgelegt

## [1.0.7] - 2023-03-17
- Fehlenden Link der SHOPVOTE-Datenschutzbedingungen für Consentmanager eingefügt

## [1.0.6] - 2023-02-13
- Möglichkeit zur Nutzung des JTL-Consentmanagers integriert
- Kompatibilität zu Shop-Version >= 5.2 hergestellt
- Abruf von neuen Bewertungen erfolgt alle 2 Stunden (nicht mehr einmal am Tag)
- Anzeige von Bewertungen von Kindartikeln beim Vaterartikel integriert

## [1.0.5] - 2022-02-11
- Kommunikation mit der Shopvote-API optimiert | Abfangen wenn keine Review-Daten für den Shop bereitgestellt werden

## [1.0.4] - 2021-10-27
- Kommunikation mit der Shopvote-API optimiert

## [1.0.3] - 2021-09-10
- Fehler beim Speichern von Bewertungsdaten behoben

## [1.0.2] - 2021-09-07
- Kompatibilitätsupdate für JTL-Shop 5.1.0

## [1.0.1] - 2021-02-02
- Wiederherstellen der vollständigen Kompatibilität zum Evo-Template

## [1.0.0] - 2020-11-03
- Initiales Release des ShopVote-Plugins für JTL-Shop5
