=== BVBB Tabellenmanager ===
Contributors: assembler23  
Tags: badminton, tabelle, ligen, bvbb, sport  
Requires at least: 5.0  
Tested up to: 6.5  
Requires PHP: 7.4  
Stable tag: 1.6.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Verwalte Badminton-Ligen (BVBB) direkt in WordPress – mit automatischer Tabellenaktualisierung, Rückzugsanzeige und eigenem Shortcode.

== Description ==

Mit dem BVBB Tabellenmanager kannst du Badminton-Ligen aus liga.nu automatisch einbinden und aktualisieren.  
Die Tabellen werden regelmäßig (via Cronjob) abgerufen, farblich markiert und mit einer lokalen Zeitangabe versehen.

**Funktionen:**
- Verwaltung mehrerer Ligen im WordPress-Backend
- Automatische Aktualisierung per Cronjob
- Darstellung per Shortcode: `[bvbb_tabelle id="slug"]`
- Markierung von Auf-/Absteigern
- Anzeige zurückgezogener Mannschaften mit Hinweis
- Eigenes CSS zur einfachen Anpassung

== Installation ==

1. Lade das Plugin hoch (`bvbb-tabellenmanager.zip`)
2. Aktiviere es in WordPress unter *Plugins*
3. Navigiere zu **BVBB Ligen** im Admin-Menü
4. Füge Ligen via liga.nu-URL hinzu
5. Nutze den Shortcode `[bvbb_tabelle id="liga-slug"]` in Seiten oder Beiträgen

== Frequently Asked Questions ==

= Wie aktualisiert sich die Tabelle? =  
Ein WordPress-Cronjob lädt die Tabellen stündlich neu.

= Was bedeutet „zurückgezogen“? =  
Wenn eine Mannschaft sich abmeldet, wird dies in der Tabelle gekennzeichnet. Die Zellen werden zusammengeführt.

= Kann ich das Design anpassen? =  
Ja, das Plugin lädt eine eigene `style.css`, die du bearbeiten kannst.

== Screenshots ==

1. Tabellenanzeige auf der Website
2. Backend mit Ligenverwaltung
3. Darstellung einer zurückgezogenen Mannschaft

== Changelog ==

= 1.6.0 =
* Lokale Zeitangabe (Europe/Berlin)
* Rückzugsanzeige mit `colspan`
* Zusätzliche Spalten: Spiele, Sätze
* Verbesserte CSS-Struktur

= 1.5.0 =
* Plugin-Basis mit Adminbereich und Shortcode

== Upgrade Notice ==

= 1.6.0 =
Diese Version enthält wichtige Darstellungs- und Zeitkorrekturen.

== License ==

GPLv2 or later – frei zur Nutzung, Anpassung und Weitergabe.
