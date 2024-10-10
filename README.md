=== RRZE Glossary ===
Contributors: rrze-webteam
Plugin URI: https://gitlab.rrze.fau.de/rrze-webteam/rrze-glossary
Tags: glossary, shortcode, block, widget, synchronization
Requires at least: 6.1
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 2.1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Author: RRZE Webteam
Author URI: https://blogs.fau.de/webworking/
Text Domain: rrze-glossary
Domain Path: /languages

Plugin, um Glossar-Einträge zu erstellen und aus dem FAU-Netzwerk zu synchronisieren. Verwendbar als Shortcode, Block oder Widget.


# RRZE-Glossary
WordPress-Plugin: Shortcode / Gutenberg Block / Widget zur Einbindung von eigenen Glossar-Einträgen sowie von Glossar-Einträge aus dem FAU-Netzwerk. 

## Allgemeines

Das Plugin kann genutzt werden, um Glossar-Einträge zu erstellen und Glossar-Einträge von Websites aus dem FAU-Netzwerk zu synchronisieren. Es kann nach Kategorien und Schlagwörtern gefiltert werden. Das Layout lässt sich derart bestimmen, dass ein A-Z Register, die Kategorien bzw Schlagwörter als Links oder als Links, die sich nach Anzahl der gefundenen Treffer in der Größe unterscheiden ausgebeben werden kann. Kategorien und Schlagwörter werden in Akkordeons gruppiert. Es ist ebenso möglich, einzelne Glossar-Einträgen auszugeben. Zur Verbesserung der SEO wird https://schema.org/DefinedTerm verwendet.

## Verwendung des Shortcodes

```html
[glossary id=456, 123"] 
[glossary category="kategorie-1, kategorie-1"]
[glossary tag="schlagwort-1, schlagwort-2"]
[glossary category="kategorie-1, kategorie-1"  tag="schlagwort-1, schlagwort-2"]
```


## Alle Attribute des Shortcodes

```html
[glossary 
register=".." 
category=".."  
tag=".." 
id=".."
hide=".."
show=".."
class=".."
sort=".."
order=".."
hstart=".."
] 
```

Alle Attribute sind optional.


## Erklärungen und Werte zu den Attributen des Shortcodes

register : bestimmt, wonach gruppiert werden soll. Mögliche Werte für die Gruppierung sind "category" oder "tag". D.h. entweder es wird nach den Kategorien gruppiert oder nach Schlagwörtern. Um gar nicht zu gruppieren, reicht es, das Attribut register wegzulassen. Darüberhinaus können Sie das Aussehen des Registers bestimmen: "a-z" stellt ein alphabetisches Register dar. Mit "tabs" werden die Begriffe ausgegeben und ebenso mit "tagcloud", wobei sie hier abhängig von der Anzahl an gefundenen Treffer unterschiedlich groß dargestellt werden. Voreingestellt ist die Darstellung "a-z".

category : mit diesem Attribut wird bestimmt, zu welchen Kategorien passende Glossar-Einträge ausgegeben werden sollen. Es können beliebig viele Kategorien angegeben werden. Nutzen Sie dazu die Titelform der Kategorien, die Sie im Menü unter "Glossar"->"Kategorie" finden und trennen Sie diese voneinander durch Kommata.

tag : mit diesem Attribut wird bestimmt, zu welchen Schlagwörtern passende Glossar-Einträge ausgegeben werden sollen. Es können beliebig viele Schlagwörter angegeben werden. Nutzen Sie dazu die Titelform der Schlagwörter, die Sie im Menü unter "Glossar"->"Schlagwörter" finden und trennen Sie diese voneinander durch Kommata.

id : mit diesem Attribut erfolgt die Ausgabe eines oder mehrerer Glossar-Einträge. Sie finden die ID in der rechten Spalte unter "Glossar"->"Alle Glossar-Einträge" sowie in der Informationsbox "Einfügen in Seiten und Beiträgen" bei jedem Glossar-Eintrag im Bearbeitungsmodus. Sie können damit auch die Reihenfolge der Glossar-Einträge in der Ausgabe bestimmen. 

hide : hiermit können Sie bestimmen, welche standardmässige Ausgabe nicht dargestellt werden soll. Mit "accordion" werden die Glossar-Einträge nicht in einem Akkordeon, sondern direkt mit Titel und Inhalt ausgeben. "title" verbirgt dabei die Ausgabe des Titels und mit dem Wert "register" wird das Register nicht angezeigt. Voreingestellt ist die Ausgabe als accordions.

show : belegen Sie dieses Attribut mit dem Wert "expand-all-link", dann erscheint oberhalb der Ausgabe der Glossar-Einträge ein Button, um alle Akkordeons mit einem Klick zu öffnen. Mit "load-open" werden die Akkordeons im geöffneten Zustand geladen. Voreingestellt ist die Ausgabe mit beim Laden geschlossenen Akkordeons und ohne "Alle öffnen"-Button.

class : hier lässt sich festlegen, in welcher Farbe der linke Rand der accordions sein soll. Mögliche Werte sind die Kennungen der Fakultäten "med", "nat", "rw", "phil" oder "tk". Zusätlich können Sie hier beliebig viele CSS-Klassen durch Leerzeichen getrennt angeben, die als Klassen für das umrahmende DIV dienen.

sort : die Sortierung der Ausgabe kann hiermit gesteuert werden. Mögliche Werte sind "title", "id" und "sortfield". 
"sortfield" bezieht sich auf das Sortierfeld, das bei jedem Glossar-Eintrag eingeben werden kann. Bei Verwendung von "sortfield" wird zuerst nach dem Sortierfeld und danach nach dem Titel sortiert. Voreingestellt ist "title", womit alle Glossar-Einträge in alphabetischer Reihenfolge angezeigt werden.

order : legt fest, in welcher Reihenfolge sortiert werden soll. "asc" aufsteigend und "desc" absteigend. Voreingestellt ist "asc".

hstart : bestimmt die Überschriftenebene der ersten Überschrift. Voreingestellt ist 2, womit die Überschriften als <h2> ausgegeben werden.


## Beispiele


[glossary register="tag tagcloud"] 
Oberhalb der Ausgabe aller Glossar-Einträge wird ein Register angezeigt, bei dem die Schlagwörter unterschiedlich groß dargestellt werden. Die Glossar-Einträge sind nach Schlagwörter gruppiert. Das Register verlinkt auf die Schlagwörter

[glossary category="Titelform-der-Kategorie"] 
Alle Glossar-Einträge, die zu dieser Kategorie gehören, werden als Akkordeons ausgegeben. Darüber befindet sich das Register von A-Z.

[glossary category="Titelform-der-Kategorie" tag="Titelform-des-Schlagworts-1, Titelform-des-Schlagworts-2"] 
Alle Glossar-Einträge, die zu dieser Kategorie gehören und die beiden Schlagwörter enthalten, werden als Akkordeons ausgegeben. Darüber befindet sich das Register von A-Z.

[glossary id="456, 987, 123" hide="register"] 
Die drei Glossar-Einträge werden in der angegebene Reihenfolge gezeigt.

[glossary register="category tabs" tag="Titelform-des-Schlagworts-1" show="expand-all-link" order="desc"] 
Unabhängig von der Kategorie werden alle Glossar-Einträge, die das Schlagwort enthalten ausgegeben. Sie werden dabei in Kategorien gruppiert. Diese Kategorien sind im Register verlinkt. Das Register besteht aus den Namen der Kategorien. Die Reihenfolge der Glossar-Einträge ist bezogen auf den Titel in umgekehrter alphabetischer Richtung.


## Glossar-Einträge von anderer Domain

Hierzu muss die gewünschte Domain über den Menüpunkt "Einstellungen" -> "RRZE Glossary" -> Tab "Domains" hinzugefügt werden.
Das Synchronisieren kann über den Menüpunkt "Einstellungen" -> "RRZE Glossary" -> Tab "Synchonisierung" vorgenommen werden.
Synchronisierte Glossar-Einträge können nun wie selbst erstellte Glossar-Einträge mit dem Shortcode ausgegeben werden.



## Verwendung via REST API v2

Beispiele:

https://www.anleitungen.rrze.fau.de/wp-json/wp/v2/glossary

Filterungen:

Tag:
https://www.anleitungen.rrze.fau.de/wp-json/wp/v2/glossary?filter[glossary_tag]=Matrix

Mehrere Tags:
https://www.anleitungen.rrze.fau.de/wp-json/wp/v2/glossary?filter[glossary_tag]=Matrix%2BAccounts

Kategorie:
https://www.anleitungen.rrze.fau.de/wp-json/wp/v2/glossary?filter[glossary_category]=Dienste

Tags und Kategorien:
https://www.anleitungen.rrze.fau.de/wp-json/wp/v2/glossary?filter[glossary_category]=Dienste&filter[glossary_tag]=Sprache

Pagination:
https://developer.wordpress.org/rest-api/using-the-rest-api/pagination/





