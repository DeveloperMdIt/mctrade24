# Changelog

## 2.4.1

* Behebt Fehler mit Umlauten im Shopnamen und Beschreibung

## 2.4.0

* Kompatibilität Shop 5.5.0
* Behebt Fehler mit Artikeln die nicht versendet werden können

## 2.3.0

* Unterstützung von bulk_price
* Behebt Fehler von Sonderpreisen in Verbindung mit Rabatten


## 2.2.2

* Behebt Fehler mit gleichbleibenden Bruttopreisen
* Behebt Fehler mit sale_price und Kategorierabatten
* Review Feed wird bei Neuinstallation nicht mehr doppelt angezeigt
* Exportformate funktionieren jetzt auch mit Unterpfaden im Dateinamen
* Behebt 2 weitere mögliche kleinere Fehler

## 2.2.1

* Behebt Fehler im XML für Rezensionsfeeds

## 2.2.0

* Kompatibilität zu JTL-Shop 5.2.0 hergestellt
* weiterer Export für Rezensionsfeeds hinzugefügt
* Behebt Fehler mit falschen Steuersätzen für abweichende Lieferländer

## 2.1.1

* Hotfix: Versandpreis wird mit Komma statt Punkt exportiert (SHOP-6250)

## 2.1.0

* Unterstützung für asynchronen Export im Shopbackend
* Sind Versandkosten auf Nettoberechnung eingestellt, werden die Versandkoste jetzt mit USt exportiert 
* Versandgewicht wird mit Einheit exportiert
* Funktionsattribute mit Wert=0 werden jetzt korrekt exportiert
* Vorbestellbare Produkte werden nun mit dem Wert "preorder" für den Parameter "availablility" exportiert
* Für die Standardwährung wird an exportierte Links nun kein Währungsparameter mehr angehängt
* Umlaute werden nun nicht mehr in HTML-Entities umgewandelt
* Die Formatierung für "shipping_weight" wurde korrigiert
* "Stück" wird nun als Einheit an Google Shopping weitergegeben
* Die Gewichtseinheit wird jetzt zusätzlich zum Gewicht bei "shipping_weight" exportiert

## 2.0.1

* Erlaubt leere statische Eingabewerte
* Neue Option: HTML-Tags in Produktbeschreibungen nicht mehr entfernen
* korrekte Behandlung von GTIN
* Behebt Fehler bei Ersetzen von Zeilenumbrüchen
* diverse Codeoptimierungen

## 2.0.0

* Shop5-Kompatibilität
