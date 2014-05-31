## Software für das Backtesting von Tradingsystemen
Die Software für das Backtesting von Tradingsystemen verfügt über eine Datenbank und zwei entsprechende Benutzeroberflächen, auf welchen die Daten der gewünschten Aktien und Indexes eingegeben werden können. Das Bindeglied dazwischen bildet das API (engl. Application programming interface), welches nachfolgend beschrieben wird.
Die Programmierschnittstelle stellt verschiedene Funktionen zur Verfügung und stellt die Kommunikation zwischen dem GUI (engl. Graphical User Interface) und der Datenbank sicher.

## Installationsanweisungen
### API Software
Die jeweils neuste Version des API kann über dieses GitHub Repository [heruntergeladen](https://github.com/SNKBT/api6o/archive/master.zip) und auf einem Apache Webserver installiert werden. Anschliessend muss die Beispiel-Konfigurationsdatei auf `config.php` umbenannt und angepasst werden.
Auf der Datenbankseite muss eine neue Datenbank angelegt und mit der beigelegten Struktur aufgebaut werden (SQL-Dump: `includes/db_structure.sql`). Zudem können erste Yahoo Finance Initialdaten importiert werden (SQL-Dump: `includes/db_sample-data.sql`).
Damit das Aktualisieren der Yahoo Finance Daten funktioniert, muss jeweils ein Initial-Datensatz zur entsprechenden Aktie existieren. Beispiel:
Tabelle indexe

| id   | name | kuerzel |
| ---- | ---- | ------- |
| 1101 | SMI  | ^SSMI   |

Tabelle indexe_values

| id | tradeDate  | adjClose | fk_indexe_id |
| -- | ---------- | -------- | ------------ |
| 1  | 1971-01-05 | 0        | 1101         |

### Swagger-PHP
Um die API Funktionalitäten zu testen wurde die Dokumentationssoftware [Swagger](https://github.com/wordnik/swagger-ui) eingesetzt, welche eine benutzerfreundliche Oberfläche zur Verfügung stellt.
Die verschiedenen Funktionalitäten können in diesem GUI in entsprechender REST Routine (GET, POST, PUT, DELETE) getestet werden.
Damit die Dokumentation aber erstellt werden kann, sind JSON Dateien der jeweiligen Klassen notwendig, welche beim Klick auf „Raw“ angeschaut werden können. Das Tool [Swagger-PHP](https://github.com/zircote/swagger-php) hilft, diese JSON Dateien automatisch zu erstellen. Dazu muss der API Code entsprechend kommentiert werden:
```php
/**
* @SWG\Api(
* path="/leseIndexe",
* @SWG\Operation(
* summary="Gibt die in der DB vorhandenen Indexe zurueck.",
* method="GET",
* type="Index",
* @SWG\ResponseMessage(
* message="Keine Indexe gefunden",
* code=404
* )
* )
* )
* @SWG\Model(id="Index")
* @SWG\Property(name="leseIndexe",type="array",required=true,@SWG\Items("leseIndexe"))
* @SWG\Property(name="error",type="boolean",required=true)
* @SWG\Property(name="status",type="integer",required=true)
* @SWG\Model(id="leseIndexe")
* @SWG\Property(name="id",type="string",format="date",required=true)
* @SWG\Property(name="name",type="string",required=true)
* @SWG\Property(name="kuerzel",type="string",required=true)
*/
public function leseIndexe() {
$result = array ();
$result ['leseIndexe'] = $this->dbh->leseIndexe ();
$this->app->render ( 200, $result );
}
```
Swagger-PHP wird wie folgt ausgeführt, indem das zu dokumentierende Verzeichnis und der Output Ordner für die JSON Dateien mitgegeben wird:
```php
php swagger.phar /project/root/top_level -o /var/html/swagger-docs
```
In unserem Fall sieht der Befehl auf einer Windows Konsole wie folgt aus:
```php
"[...]\php\php" "[...]\htdocs\swagger-php-master\swagger.phar" "[...]\htdocs\api6o" -o "[...]\htdocs\api6o\includes\api-docs"
```
Beim Aufruf der index.php Datei wird automatisch zur Swagger Dokumentation verwiesen: `http://[URL]/libs/swagger/`.


## API Dokumentation
(c) 2014 Dieses API wurde im Rahmen des Wirtschaftsinformatikstudiums an der Berner Fachhochschule programmiert.