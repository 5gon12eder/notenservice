# Die Super Fancy Notenauskunft

> This little application was written as a proof-of-concept for a specific school teacher in Austria.  It might or might
> not be useful for other people, too.  For starters, this means that the user interface as well as this documentation
> is written in German and you'll have to live with that.
>
> **Warning:** As it is now, the software is *not recommended* for production use.  It is merely published online so the
> aforementioned school can discuss the idea and decide whether it is worthwhile to put in additional effort to make
> this software production ready.  Please do not entrust the software in its current form any valuable data or -- if you
> do anyway -- at least don't blame the author for any bad consequences that might result!

Die *Super Fancy Notenauskunft* (im Folgenden *Notenservice* genannt) erlaubt es Ihnen als Leher\*in, Ihren
Schüler\*innen eine einfache Webanwendung zur Verfügung zu stellen, vermittels derer sie jederzeit selbständig ihren
aktuellen Leistungsstand abfragen können.  Einfaches Deployment auf Infrastruktur, die Schulen üblicherweise zur
Verfügung stellen, und Wartbarkeit durch Leher*innen, die zwar technisch kompetent, aber nicht notwendigerweise
Softwareentwickler\*innen sind, sowie Konformität mit der [EU Datenschutz-Grundverordnung][DSGVO] waren wesentliche
Design-Grundlagen.

**Warnung:** In ihrer aktuellen Form ist die Software ein Prototyp und *nicht* bereit für einen produktiven Einsatz!
Bitte vertrauen sie ihr keine schützenswerten Daten an!

**Vorsicht:** Der Autor dieser Software und dieses Dokuments ist bestrebt, Ihnen einen möglichst fundierten Überblick
über die technischen und rechtlichen Fragen, nicht zuletzt bezüglich des Datenschutz' zu präsentieren, und hat derlei
Überlegungen maßgeblich in das Design der Software einfließen lassen.  Gleichwohl kann dies kein Ersatz für eine
professionelle Beratung und Konsultation mit den für Ihre konkrete Schule verantwortlichen Personen sein.  Bitte
betrachten Sie nichts in diesem Dokument Geschriebenes als rechtliche Beratung und behalten Sie beim Lesen des Rests
dieses Dokuments im Hinterkopf, dass dessen Verfasser gelegentlich zu sarkastischen Ausdrucksweisen neigt.

**Achtung:** Als Lehrer\*in sind Ihnen eine umfangreiche sensible Informationen Ihrer Schüler\*innen anvertraut.  Bitte
seien Sie sich der daraus resultierenden Verantwortung bewusst.  Wenn Sie Zweifel an Ihnen selbst haben, die in diesem
Dokument beschriebene Software kompetent einsetzen zu können, suchen Sie sich bitte fachkundige Unterstützung oder
halten Sie von der Verwendung der Software insgesamt Abstand.  Bitte betrachten Sie aber jede Option auch stets im
Verhältnis zu ihren Alternativen.  Es ist wenig geholfen, wenn Sie aus Sorge über Risiken von IT-Systemen alle Daten auf
Papier ausdrucken und für alle sichtbar an die Pinnwand in der Klasse aushängen.  In jedem Fall: Bitte denken Sie daran,
dass Datenschutz ein Menschenrecht ist, und seine Wahrung eine ethische Pflicht; nicht lediglich die Erfüllung lästiger
gesetzlicher Vorschriften.  Vielen Dank!

**Obacht:** In der Dokumentation dieser Software verwenden wir die Domain `schule.at` als Platzhalter für die Domain
Ihrer Schule.  Die Tatsache, dass [`schule.at`](https://www.schule.at/) zufällig eine tatsächlich existierende Domain
ist, bitten wir großzügig zu ignorieren.  Ebenso ist "Herr Lehrer Lämpel" trotz mehrfacher Erwähnung in keiner Weise mit
dieser Software in Verbindung zu bringen.


## Übersicht und Design-Entscheidungen

In diesem Abschnitt wollen wir kurz umreißen, wie das System funktioniert, und welche Design-Überlegungen dabei
eingeflossen sind.  Die nachfolgenden Abschnitte erklären, wie Sie es einsetzen könnten.

Im Idealfall würde es dieser Software nicht bedürfen, weil Ihre Schule ein zentrales professionell gewartetes IT-System
betreiben würde, in das alle Lehrer\*innen ihre Daten eintragen und über das alle Schüler\*innen jederzeit ihre Daten
einsehen könnten.  Sofern sich Ihre Schule in einer idealen Welt befindet, können Sie sich die Lektüre des Rests dieses
Dokuments schenken.  In einer suboptimalen Welt ist es das Ziel dieser Software, einzelnen Lehrer\*innen die Möglichkeit
zu bieten, ihren Schüler\*innen einen nützlichen Zusatz-Service anzubieten, auch wenn ihre Schule insgesamt noch weniger
innovationsfreudig sein sollte.

Der Notenservice bietet Ihren Schüler\*innen eine Webseite mit einem einfachen Formular an, über das sie selbst
jederzeit veranlassen können, dass ihnen ein E-Mail mit ihrem aktuellen Leistungsstand (oder was immer für Daten Sie im
System hinterlegt haben) zugesandt wird.  Das System funktioniert so, dass Schüler\*innen ihre Klasse und ein Fach
auswählen, und eine (ihre) E-Mail Adresse angeben.  Sofern diese E-Mail Adresse eine dem System bekannte Adresse einer
Schüler\*in ist, wird *an diese Adresse* ein E-Mail versandt.  Daten werden also stets nur an die E-Mail Adresse der
betroffenen Schüler\*in selbst (oder gegebenenfalls Sie als Lehrer\*in) versandt.  Dadurch ist sichergestellt, dass die
Anwendung auch ohne selbst eine komplexe Authentifizierung durchzuführen, niemals einer Person andere als die eigenen
Daten zugänglich machen wird.  Es besteht allerdings die Möglichkeit, dass dritte Personen „für jemand anderen“ eine
Anfrage stellen und dadurch den Versand potentiell unerwünschter E-Mails an diese Person bewirken.  Dieses Problem wird
weiter unten ausführlicher diskutiert werden.

Über ein „Klassenpasswort“ wird zusätzlich gewährleistet, dass Schüler\*innen jeweils nur Anfragen für ihre eigene
Klasse stellen können.  Das Klassenpasswort vergeben Sie als Lehrer\*in vermutlich zu Beginn des Schuljahres oder bei
Inbetriebnahme des Systems und teilen es Ihren Schüler\*innen mit.  Offensichtlich bietet ein von einer Vielzahl an
Personen gemeinsam genutztes Passwort keine ernstzunehmende Sicherheit.  Dies scheint jedoch insofern vertretbar, als es
auch bei Kenntnis (und missbräuchlicher Verwendung) des Klassenpassworts nicht möglich ist, fremde Daten einzusehen.  Es
wäre jedoch möglich, anhand der Statusmeldungen des Systems herauszufinden, ob eine gegebene (dem Angreifer bekannte)
E-Mail Adresse zu einer Schüler\*in der fraglichen Klasse hinterlegt ist, was prinzipiell eine Preisgabe
personenbezogener Daten darstellte.  Dasselbe Problem stellt sich natürlich auch innerhalb einer Klasse, jedoch gilt es
hier zu bedenken, dass Schüler\*innen ohnehin wissen (und wissen dürfen), wer ihre Klassenkammerad\*innen sind, und bei
der ausschließlichen Verwendung „offizieller“ E-Mail Adressen (die systematisch aus dem Namen der Schüler\*in und der
Domain der Schule gebildet werden) die E-Mail Adresse selbst bei bereits vorhandener Kenntnis des Namens keinen
Informationsgewinn darstellt.  Prinzipiell könnte die Software in einer zukünftigen Version dahingehend geändert werden,
dass sie nur Statusmeldungen ausgibt, die keinen Rückschluss darüber zulassen, ob tatsächlich ein E-Mail versandt wurde,
und entsprechend nicht offenbaren würde, ob zu der angegebenen Adresse eine Person in dieser Klasse existiert.  Dem
Autor der Software schien dies jedoch eine unverhältnismäßige Einschränkung der Benutzbarkeit der Software darzustellen,
die zu sehr frustrierenden Benutzererfahrungen führen würde, weil die Verwender\*innen nicht mehr unterscheiden könnten,
ob sie eine falsche E-Mail Adresse eingegeben haben, oder ob ein sonstiger Fehler vorliegt.

Es war ferner eine bewusste Design-Entscheidung, dass die Anwendung keine individuellen Passwörter von Schüler\*innen
speichern soll.  Von Benutzern gewählte Passwörter zu speichern, ist eine Verantwortung, die vielfach unterschätzt wird,
und definitiv nicht einzelnen Lehrer\*innen aufgebürdet werden sollte.  In einer idealen Welt würden alle Menschen für
jede Anwendung ein eigenes komplett zufällig gewähltes Passwort verwenden.  In der Realität verwenden viele Menschen
sehr schlechte Passwörter und diese häufig auch noch für mehrere Anwendungen.  Das Letzte, das Sie als Lehrer\*in haben
möchten, ist eine Datei mit Passwörtern, die Ihre Schüler\*innen auch für ihre sozialen Netzwerke und zum
Online-Shopping verwenden.  Wenn Sie individuelle Passwörter vergeben, wird dies darüber hinaus dazu führen, dass Ihre
Schüler\*innen diese selbstverständlich vergessen / verlieren werden.  Nun müssen Sie einen Mechanismus haben, um
Passwörter zurückzusetzen.  Nun müssen Sie eine Möglichkeit haben, legitime Rücksetzanfragen von Missbrauch zu
unterscheiden.  Alle diese Probleme sind irgendwie lösbar, aber nicht auf triviale Art und Weise und am Ende hätten Sie
ein System, dessen Komplexität deutlich höher ist, als alles, was Sie „nebenbei“ zu Ihrer eigentlichen
Dienstverpflichtung als netten Extra-Service für Ihre Schüler\*innen anbieten.

Es hindert aber natürlich trotz Klassenpasswort prinzipiell niemand niemanden daran, die E-Mail Adresse einer
Mitschüler\*in (aus der eigenen Klasse) anzugeben.  In diesem Fall würde die nichtsahnende Kommiliton\*in aus heiterem
Himmel ein E-Mail mit ihrem aktuellen Leistungsstand bei Ihnen erhalten.  Sofern das einmal passiert, ist das nicht
weiter tragisch, aber eine Vielzahl solcher Mails könnte prinzipiell ein Ärgernis darstellen, oder die Technik gar zum
Mobbing gebraucht werden.  Zwar wird es niemals passieren, dass Daten an eine andere Person, als diejenige, um deren
Daten es sich handelt, per Mail versandt werden, aber Menschen haben manchmal fragwürdige Vorstellungen von Humor und
gewiss wird es früher oder später eine Ihrer Schüler\*innen lustig finden, das E-Mail Postfach einer anderen Person mit
exzessiven Notenauskünften zu überfluten.  Irgendein [Scriptkiddie][WikiScriptkiddie] wird entdecken, dass eine
Abwandlung des folgenden `curl(1)` Befehls in einer Endlosschleife ausgeführt Verdruss am anderen Ende der Leitung
verursacht, und im Allgemeinen wird es nicht herauszufinden sein, wer der Übeltäter gewesen ist.

    $ curl -o /dev/null --basic --user '1a:******'           \
        --data-urlencode 'klasse=1a'                         \
        --data-urlencode 'fach=biologie'                     \
        --data-urlencode 'email=fremde.adresse@schule.at'    \
        'https://home.schule.at/~user/notenservice/'

Wir dokumentieren diese missbräuchliche Verwendung des Services hier bewusst öffentlich, damit Sie nicht behaupten
können, dass Ihre Schüler\*innen niemals auf diese Idee kämen, und damit Ihre Scriptkiddies sich nicht intelligent
fühlen, wenn sie den Angriff doch ausprobieren.

Um einem solchen Missbrauch einen Riegel vorzuschieben, erlaubt es die Anwendung, die Anzahl der zulässigen Anfragen pro
Tag zu limitieren.  Sollten Sie dieses Limit auf -- sagen wir -- fünf gesetzt haben, könnten böswillige
Mitschüler\*innen maximal fünf ungewollte Mails pro Tag ins Postfach einer anderen Person zaubern.  Die technische
Realisierung dieses Tageslimits setzt allerdings voraus, dass von der Software eine ansonsten nicht erforderliche
Logdatei geführt werden muss, in der erfasst wird, wie viele Anfragen zu einer E-Mail Adresse am heutigen Tag bereits
gestellt wurden.  Diese Logdatei ist so datensparsam wie möglich konzipiert.  Es wird lediglich ein
[kryptographischer Hash][WikiCryptoHash] der E-Mail Adresse (kombiniert mit Klasse und Fach) sowie der Kalendertag und
der aktuelle Zähler gespeichert.  Insbesondere wird nicht erfasst, zu welcher Uhrzeit eine Anfrage gestellt wurde.  Alte
Einträge werden automatisch aus der Datenbank entfernt, sobald das System nach Ende des Kalendertags die erste neue
Anfrage bearbeitet.  Aus technischen Gründen ist es nicht möglich, dies sofort zu tun, da PHP-Skripte nicht als
persistente Prozesse laufen, sondern lediglich bei aktiven Anfragen vom Webserver ausgeführt werden.

Bitte seien Sie sich bewusst, dass die Verwendung einer kryptographischen Hashfunktion an dieser Stelle eher eine nette
Geste darstellt, und keine wirkliche Urbildresistenz liefert, da aufgrund des sehr kleinen Urbildraums auch ein
minderbemittelter Angreifer sehr einfach die Hashfunktion invertieren könnte.

Sollten Sie trotz der Vorsichtsmaßnahme des Tageslimits befürchten oder beobachten, dass das System systematisch
missbräuchlich genutzt wird, um Personen zuzuspammen, wäre es denkbar, in einer zukünftigen Version einen Mittelweg zu
gehen, und Schüler\*innen ein zufällig gewähltes „Token“ zuzuweisen, das bei einer Anfrage zusätzlich anzugeben ist.  Da
Sie dieses Token als Lehrer\*in frei vergeben würden, und selbst bei unbefugter Kenntnis desselben durch Dritte das
System auch nicht unsicherer wäre, als in seiner aktuellen Version, entstünden nicht die oben skizzierten Probleme bei
der Speicherung von Passwörtern im eigentlichen Sinne.  Allerdings müssten Sie sich nun darauf gefasst machen,
regelmäßig mit Anfragen von Schüler\*innen konfrontiert zu werden, die ihr Token vergessen haben.  Und nein, wir können
dafür keine automatische Rücksetzfunktion in die Software einbauen, denn ansonsten könnte man eben diese Funktion erst
recht wieder verwenden, um anderen Personen unerwünschte Mails zu generieren.  Ein Mittelweg wäre es, die Vergabe eines
solchen Tokens optional zu machen, sodass die Mehrheit Ihrer Schüler\*innen den Service wie gehabt benutzen könnte, Sie
jedoch bei solchen, die in der Vergangenheit Opfer von Spam-Attacken wurden, auf Wunsch der betroffenen Person ein
solches Token vergeben könnten.  Nun müssten Sie allerdings darauf vertrauen, dass sich Opfer von Mobbing ehrlich bei
Ihnen melden.

Noch eine andere Option wäre es, in die versandten E-Mails zusätzliche Informationen wie die IP-Adresse des Geräts, von
dem aus die Anfrage gestellt wurde, aufzunehmen.  Bei legitimen Anfragen sollte das kein Datenschutzproblem darstellen,
da die Person selbst ihre eigene IP-Adresse jederzeit weiß und erfahren darf.  Die Angabe könnte jedoch dabei helfen,
bei einer missbräuchlichen Verwendung dem Übeltäter auf die Schliche zu kommen, und wäre insofern auch durch ein
berechtigtes Interesse iSv Art&nbsp;6 Abs&nbsp;1 Lit&nbsp;f DSGVO zu rechtfertigen.  Der Autor der Software und dieses
Dokuments hält gleichwohl nicht viel von dieser Idee, da Ihnen auch eine IP-Adresse in aller Regel nicht sonderlich
dienlich sein wird, um mit verfügbaren Mitteln herauszufinden, wer eine Anfrage getriggert hat, es für einen Angreifer
einfach ist, sich eine anonyme IP-Adresse zu besorgen, und vor allem die Kenntnis dieser Information eine
Erwartungshaltung nährt, dass Sie als Lehrer\*in gegen Fehlverhalten vorgehen werden, die Sie realistisch betrachtet nur
enttäuschen können.

Die noch noch andere Option, das Web-Interface durch lustige [Captchas][WikiCaptcha] gegen automatisierte Verwendung zu
„härten“ wollen wir dagegen am besten gleich wieder vergessen.  Erstens ist es das gute Recht Ihrer Schüler\*innen sich
für den eigenen legitimen Gebrauch nützliche Skripte zu schreiben oder wenigstens einen textbasierten Webbrowser wie
`lynx(1)` zu verwenden, zweitens mindern Captchas die Barrierefreiheit einer Webseite oder machen sie für manche
Menschen gänzlich unbenutzbar, drittens funktionieren die effektiveren solchen Dienste, indem sie erst recht
personenbezogene Daten an Dritte übertragen, sodass Sie sie im Schulumfeld gar nicht verwenden dürften, und viertens
können gelangweilte Schüler\*innen erstaunliche Ausdauer entwickeln, wenn es darum geht, ein Formular im Zweifel von
Hand auszufüllen, nur um jemand andern zu ärgern.

**Interessant:** Manche Menschen sind manchmal etwas pessimistisch und vielleicht ist das in der Praxis eh alles kein
Problem.

Schließlich unterstützt die Anwendung noch optional den sogenannten „Lehrermodus“, in dem Sie sich die Auskunft selbst
zuschicken lassen können.  Das ist dazu gedacht, dass Sie eventuelle Probleme auf diese Art schneller eingrenzen und
Beschwerden ihrer Schüler\*innen, dass ihnen nicht die gewünschten / erwarteten Auskünfte zuteil geworden wären,
untersuchen können.  Das damit einhergehende Risiko ist gering, denn Sie als Lehrer\*in hatten die Daten aller Ihrer
Schüler\*innen ohnehin schon, also kann ein erfolgreicher Angreifer, der sich Ihres „Lehrerpassworts“ bemächtigen
konnte, nicht wirklich etwas anrichten, außer nun Ihnen lästige Mails zukommen zu lassen, was Sie aber natürlich wieder
abstellen können, indem Sie geschwind das Passwort ändern, und diesmal besser darauf aufpassen.  Gleichwohl können Sie
den Lehrermodus deaktivieren, wenn Ihnen das lieber sein sollte.  Anfragen, die von Ihnen im Lehrermodus gestellt
werden, zählen natürlich nicht auf das Tageslimit der betroffenen Schüler\*in.


## Voraussetzungen

Um den Notenservice für sich nutzen zu können benötigen Sie einen Webserver, mit der Möglichkeit [PHP-Skripte][PHP]
auszuführen.  Der wohl populärste [*Apache* Webserver][ApacheHttpd] in seiner Standardkonfiguration ist hierfür
ausreichend.  Im Idealfall hat Ihre Administrator\*in den Webserver bereits so konfiguriert, dass Sie in Ihrem
Home-Verzeichnis ein spezielles Unterverzeichnis (häufig `~/.public_html` order ähnlich genannt) anlegen können, und der
Webserver dessen Inhalt sodann automatisch unter einer URL einbindet, die sich häufig aus Ihrem Benutzernamen ergibt.
Anderenfalls müssen Sie Rücksprache mit Ihrer Administrator\*in halten.  Auf dem Server muss außerdem die Möglichkeit
bestehen, E-Mails aus Skripten heraus zu versenden.  (Der Notenservice benutzt die Standard [`mail()`][PhpMail] Funktion
von PHP, die üblicherweise ein [`sendmail(1)`][sendmail1] Backend verwendet.)  Schließlich benötigen Sie in Ihrer
PHP-Installation Unterstützung für das [SQLite][SQLite3] Datenbankformat, namentlich die
[SQLite3 Extension][PhpSQLite3].

Der Webserver Ihrer Schule muss [HTTPS][WikiHttps] unterstützen.  Anderenfalls kann der Notenservice nicht sicher
betrieben werden, da Passwörter übertragen werden müssen.

Die Software wurde für die (zum Zeitpunkt des Verfassens dieses Texts aktuelle) PHP Version 7 geschrieben.  Nehmen wir
an, dass Sie bereits wissen, dass der Webserver das Verzeichnis `~/.public_html` einbindet, und dessen Inhalt via
`https://home.schule.at/~user/` anbietet.  (Bitte substituieren Sie gedanklich die Pfade und URLs in den weiteren
Beispielen in diesem Dokument entsprechend für Sie und Ihre Schule.)  Um herauszufinden, ob Sie prinzipiell PHP-Skripte
in Ihrem Webverzeichnis ausführen können, und welche PHP-Version Sie verwenden, legen Sie die Datei
`~/.public_html/index.php` mit folgendem Inhalt an.  (Es ist *nicht* erforderlich, dass Sie diese Datei
[ausführbar][WikiAusführbareDatei] machen -- sie muss für den Webserver lediglich lesbar sein -- und Sie sollten es
tunlichst unterlassen, es dennoch zu tun.)

    <?php phpinfo();

Wenn Sie sodann die URL `https://home.schule.at/~user/index.php` in Ihrem Webbrowser öffnen (das `index.php` am Ende ist
in aller Regel entbehrlich), und mit einer überwältigenden Informationsflut über jedes technische Detail Ihrer
PHP-Installation konfrontiert werden, sind Sie gut gestellt.  Direkt am Beginn der Seite sollten Sie die verwendete
PHP-Version sehen können.  Wenn Sie auf dieser Seite weiter nach „sqlite3“ suchen (und fündig werden), sollten Sie auch
sehen können, ob Ihre PHP-Installation über die oben erwähnte SQLite3 Extension verfügt.

Bitte löschen Sie die soeben angelegte PHP-Datei nach Abschluss dieses Experiments wieder.

Die Software wurde bisher ausschließlich unter [GNU/Linux][GnuLinux] getestet, was üblicherweise das Betriebssystem sein
wird, unter dem der Webserver betrieben wird.  Bitte beachten Sie, dass es gut möglich ist, dass Sie im Alltag über ein
anderes Betriebssystem auf die Daten in Ihrem Home-Verzeichnis zugreifen, und der Webserver trotzdem unter GNU/Linux
läuft.  Im Zweifel konsultieren Sie bitte Ihre Administrator\*in.

Organisatorisch benötigen Sie von jeder Ihrer Schüler\*innen eine vertrauenswürdige E-Mail Adresse.  Im Idealfall stellt
Ihre Schule selbst jeder Schüler\*in eine eigene offizielle E-Mail Adresse für die Kommunikation mit der Schule zur
Verfügung.  In diesem Fall brauchen Sie sich weder dafür zu rechtfertigen, diese Adressen zu speichern, noch stellt sich
Ihnen die Frage, ob Sie es vertreten können, sensible Daten an diese Adressen zu verschicken.  In diesem Fall verlassen
die Daten nicht die Hoheit der IT-Abteilung Ihrer Schule, und die Sicherheit der offiziellen E-Mail Postfächer ist
offenkundig über jeden Zweifel erhaben.

Ferner benötigen Sie die organisatorische Erlaubnis Ihrer Schule, personenbezogene Daten Ihrer Schüler\*innen im
Klartext (also unverschlüsselt) in Ihrem Home-Verzeichnis abzulegen.  Auch wenn Sie diese Erlaubnis prinzipiell haben,
oder es Ihnen zumindest niemand verboten hat, sollten Sie sich auch selbst die Frage stellen, ob Sie diese Verantwortung
tragen können.  Als absolutes Minimum sollten Sie gewährleisten können, dass Sie ein sicheres Passwort verwenden, und
außer Ihnen selbst niemand Zugriff auf Ihren Benutzer-Account hat.

Schließlich macht die Verwendung des Notenservice natürlich nur Sinn, wenn Sie ihn auch regelmäßig mit den aktuellen
Daten Ihrer Schüler\*innen füttern.  Hierzu müssen Sie für jede Klasse und jedes Fach, das Sie unterrichten, eine
CSV-Datei mit den Schüler\*innendaten anlegen und gegebenenfalls aktualisieren.  Wie genau dieses Dateiformat aussieht,
und wo genau Sie die Dateien ablegen müssen, werden wir später erklären.  Sie müssen aber prinzipiell in der Lage sein,
solche Dateien mit vertretbarem Aufwand zu pflegen.  Sofern Sie die Daten Ihrer Schüler\*innen in irgendeiner Form
strukturiert digital verwalten, etwa tabellarisch in einem Datenverarbeitungsprogramm Ihrer Wahl, sollten Sie dies
bewerkstelligen können.  Wie genau Sie diese Dateien anlegen, liegt jedoch außerhalb der Zuständigkeit dieser Software
und des Rahmens dieser Dokumentation.  Wir werden fürs Folgende annehmen, dass Sie diese Dateien beisteuern können.

**Wichtig:** Bitte beachten Sie, dass Sie vor der Inbetriebnahme eines informationstechnischen Systems, das
personenbezogene Daten verarbeitet, verpflichtet sind, die [Datenschutzbeauftragte\*n][Datenschutzbeauftragter] Ihrer
Schule zu konsultieren, und gemeinsam die Risiken und gegebenenfalls erforderlichen Schutzmaßnahmen zu erörtern.  Bei
dieser Gelegenheit werden Sie auch zu klären haben, ob und wie das neue System im
[„Verzeichnis von Verarbeitungstätigkeiten“][Datenverarbeitungsregister] Ihrer Schule einzutragen ist.  Davon unabhängig
steht es Ihnen natürlich frei, nach Lust und Laune mit einem System zu experimentieren, solange Sie ihm keine echten
personenbezogenen Daten zur Verfügung stellen.


## Installation

Am einfachsten ist es, wenn Sie die fertige ZIP-Datei `notenservice.zip` verwenden, die sie [hier][Download]
herunterladen können.  Entpacken Sie diese Datei nun in Ihrem Webverzeichnis.  Natürlich können Sie auch ein beliebiges
Unterverzeichnis Ihrer Wahl verwenden.  Sofern Sie das Archiv auf top-level entpacken, werden alle Daten in dem
Verzeichnis `notenservice` abgelegt, und der Service selbst ist sodann als `https://home.schule.at/~user/notenservice/`
erreichbar, wobei die konkrete URL natürlich von Ihrer Schul-IT abhängig ist.

**Obacht:** Bei allen nachfolgend beschriebenen von Ihnen anzulegenden oder zu editierenden Dateien handelt es sich
stets um [Textdateien][WikiTextdatei].  Bitte stellen Sie sicher, dass alle Dateien stets in [UTF-8][WikiUTF8] codiert
sind, und „UNIX Line Endings“ (also `\n` im Gegensatz zu `\r\n` wie nicht zuletzt von Microsoft Windows bevorzugt)
verwenden.  Sofern Sie mit einem der gängigen Texteditoren unter GNU/Linux editieren, sollte das in der Regel der Fall
sein, es sei denn, Sie haben aktiv etwas anderes eingestellt, was Sie entsprechend nicht tun sollten.


### Anpassen der `.htaccess` Datei

Bevor Sie den Notenservice nutzen können, müssen Sie für jede Ihrer Klassen (und optional auch für sich selbst) einen
HTTP-Benutzer anlegen.  Im einfachsten Fall ist Ihr Webserver so konfiguriert, dass sie selbst
[`.htaccess`][WikiHtaccess] Dateien anlegen dürfen.  Die folgende Erklärung geht davon aus, dass dies der Fall ist.
Anderenfalls brauchen Sie hier die Unterstützung Ihrer Administrator\*in.  Die ZIP-Datei enthält bereits eine
`.htaccess` Datei, die Sie lediglich noch editieren und an Ihre Situation anpassen müssen.

Alles was Sie hieran editieren müssen, ist die `AuthUserFile` Zeile.  Hier geben Sie anstatt `/the/path/to/.htpasswd`
den *absoluten* Pfad einer Datei an, deren Anlegen wir sogleich besprechen werden.  Es ist Ihnen *wärmstens* empfohlen,
diese Datei außerhalb des Webverzeichnis' abzulegen.  Auf diese Weise ist sichergestellt, dass sie selbst im Fall einer
Fehlkonfiguration des Webservers (die dazu führt, dass Ihre `.htaccess` Dateien nicht wie gewünscht berücksichtigt
werden) niemals die Passwort-Datei via HTTP ausgeliefert wird.  Sie müssen allerdings sicherstellen, dass der Webserver
selbst Zugriff auf die Passwort-Datei hat.  Eventuell (je nach dem, wie Ihrer IT aufgesetzt ist) ist dies außerhalb
Ihres Webverzeichnis' nicht gewährleistet.  Bitte fragen Sie im Zweifel Ihre Administrator\*in um Rat.  Falls alles
nichts hilft, können Sie die Datei notfalls prinzipiell durchaus auch innerhalb des Webverzeichnis' ablegen.  Bitte
nennen Sie sie in diesem Fall `.htpasswd`, da Namen, die mit `.ht*` beginnen, vom *Apache* Webserver standardmäßig nicht
via HTTP ausgeliefert werden, und Sie somit zumindest einen gewissen zusätzlichen Schutz haben.  Je nach dem wie Ihr
Home-Verzeichnis auf dem Webserver gemountet ist, kann es schwierig sein, den absoluten Pfad anzugeben.  Legen Sie in
diesem Fall die Datei `~/.public_html/index.php` mit folgendem Inhalt (in Ihrem Webverzeichnis) an.

    <?php echo getcwd();

Wenn Sie nun die Seite `https://home.schule.at/~user/index.php` in Ihrem Webbrowser besuchen, sollte Ihnen der absolute
Pfad des Verzeichnis', in dem Sie die Datei abgelegt haben, angezeigt werden, woraus Sie sich den in der `.htaccess`
Datei anzugebenden Pfad erschließen können sollten.  Bitte löschen Sie die soeben angelegte PHP-Datei wieder, sobald
nicht mehr benötigt.

Wenn Sie die `.htaccess` Datei erfolgreich editiert haben, sollten Sie mit Ihrem Webbrowser nicht mehr auf
`https://home.schule.at/~user/notenservice/` zugreifen können.


### Anlegen der `.htpasswd` Datei

Als nächstes müssen Sie Benutzer anlegen und in die oben festgelegte [`.htpasswd`][WikiHtpasswd] Datei eintragen.  Die
Datei verwendet ein simples Textformat, das in jeder Zeile einen Benutzernamen und einen
[kryptographischen Hash][WikiCryptoHash] des zugehörigen Passworts jeweils durch einen Doppelpunkt getrennt enthält.  Am
einfachsten können Sie die Einträge für diese Datei erzeugen, indem sie das Kommandozeilenprogramm
[`htpasswd(1)`][htpasswd1] benutzen, das Teil von *Apache* ist.  Sollte dieses Programm nicht auf Ihrem System
installiert sein, können Sie seiner eventuell problemlos auf Ihrem privaten Computer habhaft werden.  Sollten Sie etwa
das beliebte GNU/Linux System *Debian* oder eines seiner Derivate (zB *Ubuntu*) verwenden, können Sie das Paket
`apache2-utils` zu diesem Zweck installieren.  Von Webservices [wie diesem][OnlineHtpasswdGenerator] sollten Sie sich
tunlichst fern halten, da es -- ohne ein wertendes Urteil über die Vertrauenswürdigkeit der Betreiber solcher Services
abgeben zu wollen -- offensichtlich ein sehr fragwürdiges Konzept darstellt, seine Passwörter zunächst einer dritten
Person zu übermitteln, nur um diese daraus einen Eintrag für Ihre Datei erzeugen zu lassen, den Sie ebensogut auch
selbst hätten erzeugen können.

Legen Sie für jede von Ihnen unterrichtete Klasse einen Benutzer an, dessen Name die Klasse angeben sollte.  Hier können
Sie richtig kreativ sein, und selbst entscheiden, ob sie Groß- oder Kleinschreibung verwenden wollen.  Der Autor dieses
Dokuments empfiehlt Ihnen, im Zweifel alles klein zu schreiben.  Der „Notenservice“ kommt mit groß- wie mit
kleingeschriebenen Benutzernamen gleichermaßen zurecht und wird Klassennamen stets mit Großbuchstaben anzeigen.
Sofern sie den „Lehrermodus“ verwenden wollen, legen Sie bitte auch einen Benutzer für sich selbst an.

Bitte verwenden Sie hier sinnvolle Passwörter.  Offensichtlich nicht sinnvoll sind solche Passwörter, die sich einfach
erraten lassen, beispielsweise weil Sie aus Ihrem Namen oder dem Namen der Klasse oder dem Schuljahr abgeleitet sind.
Sinn und Zweck der gesamten Übung ist es, zu unterbinden, dass Schüler\*innen versuchen, sich als eine andere Klasse
auszugeben, und dieser Zweck wird unterwandert, wenn die Kenntnis des Passworts der Klasse X dafür genügt, sich das
Passwort der Klasse Y denken zu können.  In keinem Fall sollten Sie (auch und schon gar nicht für Ihr „Lehrerpasswort“)
Passwörter verwenden, die Sie anderswo noch benutzen.  Außerdem sollten Sie die Passwörter natürlich mindestens zu
Beginn jedes neuen Schuljahres ändern, wenn die Schüler\*innen der jeweiligen Klassen gewechselt haben.  Der Verfasser
dieses Dokuments empfiehlt Ihnen, Passwörter grundsätzlich komplett zufällig zu generieren, und einen Passwort-Manager
zu benutzen, um sie sich nicht merken zu müssen.  Ein Passwort, das Sie sich nicht merken, können Sie weder vergessen,
noch ungewollt im Rausch ausplappern.  Auch Ihre Schüler\*innen werden Ihnen für diesen Rat fürs Leben dankbar sein.
Unter GNU/Linux ist beispielsweise `pwgen(1)` ein simples Kommandozeilenprogramm, das zum generieren zufälliger
Passwörter beliebiger Länge und Komplexität gute Dienste leistet.


### Editieren der `notenservice.json` Datei

Zuletzt müssen Sie noch den Notenservice selbst konfigurieren.  Hierfür editieren Sie bitte die Datei
`notenservice.json`, für die bereits eine Vorlage aus dem ZIP-Archiv entpackt wurde.  Die Datei verwendet ein
erweitertes [JSON][JSON]-Format, dessen Syntax Sie auf Punkt & Komma beachten müssen.  Die „Erweiterung“ besteht darin,
dass Sie in dieser Datei auch einfache Kommentare verwenden können, sofern Sie das wünschen.  Zeilen, die mit `//`
(allenfalls mit vorausgehender Einrückung) beginnen, werden beim Lesen der Datei entfernt, bevor der Rest der Datei dem
JSON-Parser vorgeworfen wird.  Das eigentliche JSON-Format unterstützt (leider) keinerlei Kommentare.  Eine weitere
häufige Fehlerquelle ist die bisweilen sehr frustrierende Eigenschaft des JSON-Formats, dass nach dem letzten Element
einer Aufzählung kein redundantes Komma mehr folgen darf.

Am Ende muss die JSON-Datei ein Objekt (im JSON-Sinne) enthalten, das die in nachfolgender Tabelle beschriebenen
Attribute mit den entsprechenden Typen hat.

 Attribut                      | Typ
:------------------------------|:------------------
 `data_directory`              | `STRING`
 `daily_limit`                 | `INTEGER`
 `logfile`                     | `STRING` oder `NULL`
 `teacher`                     | `OBJECT`
 `service`                     | `OBJECT`
 `klassen`                     | `ARRAY` von `STRING`s
 `faecher`                     | `OBJECT`
 `school_privacy_policy_url`   | `STRING`

Falls Sie mit formalen Spezifikationen nicht viel anfangen können, hangeln Sie sich am besten an der Struktur der
vorhandenen Datei entlang.

 * Als `data_directory` geben Sie bitte den (vorzugsweise absoluten) Pfad des Verzeichnis' an, in dem Sie gedenken, die
   Daten Ihrer Schüler für den Service abzulegen.  Hier gelten wieder dieselben Erwägungen wie zuvor für die `.htpasswd`
   Datei genannt: Wenn irgend möglich legen Sie diese Daten bitte außerhalb Ihres Webverzeichnis' ab.  Sollte das aus
   Gründen nicht gehen, ist die mittelmäßige Alternative, das `data` Unterverzeichnis zu verwenden, für das in dem
   ZIP-Archiv bereits eine passende `.htaccess` Datei mitkommt, die den Zugriff auf das Verzeichnis via HTTP verhindern
   soll.  Sollten Sie die Daten (wie empfohlen) anderswo ablegen wollen, löschen Sie den entpackten `data` Ordner bitte
   und passen Sie den Eintrag für `data_directory` entsprechend an.

 * Als `daily_limit` geben Sie bitte eine Ganzzahl an, die angibt, wie oft eine Schüler\*in pro Tag und Fach ihre Daten
   abfragen darf.  Ist dieses Limit überschritten, wird sich das System bis zum Ablauf des Kalendertages weigern,
   weitere E-Mails an die fragliche Adresse zu verschicken.  Sie können `daily_limit` auf 0 setzen, um den Service
   effektiv zu deaktivieren (warum auch immer Sie das tun wollen sollten) oder auf &minus;1, um das Tageslimit zu
   deaktivieren.  Der Autor der Software und Verfasser dieses Dokuments empfiehlt ihnen, das Limit nicht zu
   deaktivieren, und auf einen Wert zwischen 3 und 10 zu setzen.

   Ein zu niedriges Limit ist ärgerlich.  Wenn Sie beispielsweise ankündigen, dass die Noten für eine Arbeit „am
   Freitag“ online sein werden, werden einige Schüler\*innen gewiss nervös sein, und versuchen, regelmäßig ihre Noten
   abzufragen, um zu sehen, ob sie bereits online sind.  Nichts frustriert in einer solchen Situation mehr, als bis zum
   nächsten Tag warten zu müssen, um wieder Anfragen stellen zu dürfen.  Ein zu hohes oder gar kein Limit ist allerdings
   auch nicht zu empfehlen, da es der unter „Überblick“ beschriebenen missbräuchlichen Verwendung des Services gar
   keinen Riegel mehr vorschiebt.

 * Als `logfile` können Sie entweder `null` oder den (vorzugsweise absoluten) Pfad einer Datei angeben, die Sie als
   SQLite Datenbank für die Logdatei verwenden wollen, vermittels derer das Tageslimit erzwungen wird.  Es gelten
   dieselben Bedenken bezüglich der Ablage der Datei innerhalb Ihres Webverzeichnis', die wir bereits für die
   Schüler\*innendaten und die `.htpasswd` Datei diskutiert hatten.  Prinzipiell können Sie sich hier einen beliebigen
   Dateinamen aus der Nase ziehen, aber üblicherweise würden Sie eine Dateiendung wie `*.db` oder `*.sqlite3` verwenden.
   Sollte Ihr Webserver so konfiguriert sein, dass der Benutzer, unter dem PHP-Skripte ausgeführt werden, nicht in Ihr
   Home-Verzeichnis schreiben kann (was aus Sicherheitsgründen grundsätzlich eine *gute* Sache ist), können Sie
   versuchen, für die Logdatei einen Pfad im `/tmp`-Verzeichnis anzugeben.  In diesem Fall ist die Logdatei nach einem
   Systemneustart zwar verloren, aber damit kommt die Anwendung klar, sofern die Maschine, auf der Ihr Webserver läuft,
   nicht permanent (mehrmals täglich) neu gebootet wird.  Bitte konsultieren Sie auch hier im Zweifel Ihre
   Administrator\*in, um zu klären, wo ein geeigneter Ablageort für die Datei wäre.

   Wenn Sie `null` angeben, wird keine solche Logdatei geführt werden, und es kann entsprechend auch kein Tageslimit
   erzwungen werden.  In diesem Fall müssen Sie `daily_limit` wohl oder übel ebenfalls auf &minus;1 setzen.

   Sofern die [PHP SQLite3 Extension][PhpSQLite3] auf Ihrem Server nicht verfügbar ist, bleibt Ihnen fürs Erste keine
   andere Wahl, als die Logdatei zu deaktivieren.

 * Für das Attribut `teacher` geben Sie bitte ein Sub-Objekt an, das die nachfolgend beschriebenen Attribute enthält.

    Attribut    | Typ
   :------------|-----------------
    `user`      | `STRING` oder `NULL`
    `name`      | `STRING`
    `email`     | `STRING`

    - Als Wert des Attributs `user` können Sie entweder den Benutzernamen angeben, den Sie zuvor als „Ihren“ Benutzer
      auserkoren haben, oder `null`, um den „Lehrermodus“ zu deaktivieren.

    - Als Wert des Attributs `name` geben Sie bitte Ihren (eigenen) Namen mit so vielen oder so wenigen akademischen und
      dienstlichen Titeln an, wie es Ihnen gefällt.

    - Als Wert des Attributs `email` geben Sie bitte Ihre eigene offizielle E-Mail Adresse an, die Sie von Ihrer Schule
      erhalten haben.  Sie wird an verschiedenen Stellen als Kontaktmöglichkeit angezeigt werden.  Sofern Sie den
      „Lehrermodus“ aktiviert haben, wird diese Adresse auch benutzt werden, um Mails an Sie selbst zu versenden.

 * Für das Attribut `service` geben Sie bitte ein Sub-Objekt an, das die nachfolgend beschriebenen Attribute enthält.

    Attribut    | Typ
   :------------|-----------------
    `url`       | `STRING`
    `email`     | `STRING`

    - Als Wert des Attributs `url` geben Sie bitte die „kanonische“ URL des Notenservices' an.  Um unserem Beispiel zu
      folgen also `https://home.schule.at/~user/notenservice/`.

    - Als Wert des Attributs `email` geben Sie bitte eine E-Mail Adresse an, die als Absender der vom Notenservice
      versandten E-Mails aufscheinen soll.  Hierfür können Sie Ihre eigene E-Mail Adresse angeben.  Es ist jedoch
      möglich, dass -- wenn Ihr Webserver unter einem anderen Domain-Namen betrieben wird -- manche Spam-Heuristiken
      unglücklich darüber sind, oder sich gar Ihr Mailserver weigert, E-Mail unter dieser Absenderadresse zu versenden.
      In diesem Fall halten Sie bitte Rücksprache mit Ihrer Administrator\*in um zu klären, welche Adresse Sie verwenden
      dürfen.  Vermutlich die eine oder andere Form der berüchtigten `noreply@schule.at` Adresse.

 * Als `klassen` geben Sie bitte eine Liste an, welche die von Ihnen unterrichteten Klassen bezeichnet; oder zumindest
   jene, für die Sie den Notenservice einsetzen wollen.  In dieser Liste sollten sich dieselben Einträge wiederfinden,
   die Sie zuvor als Benutzer in der `.htpasswd` Datei eingetragen haben.  Selbstverständlich ohne Ihren eigenen
   Benutzer.  Sie können die Klassen hier groß oder klein schreiben, unabhängig davon, welche Schreibweise Sie in der
   `.htpasswd` für die Benutzernamen verwendet haben.  Der Autor bevorzugt auch hier die Kleinschreibung.  Bitte
   beachten Sie, dass Klassennamen „Wörter“ sein müssen, also lediglich aus alphanumerischen Zeichen und dem Unterstrich
   bestehen dürfen.

 * Für das Attribut `faecher` geben Sie bitte ein Sub-Objekt an, das als Attribute von Ihnen frei gewählte Wörter (siehe
   oben) und als deren Werte die mehr oder weniger offizielle Bezeichnung der von Ihnen unterrichteten Fächer hat.
   Während Sie als Attribute wie gesagt nur Wörter verwenden dürfen, sind Ihnen bei den Werten keine Grenzen gesetzt.
   Falls Ihre Fächer einfache Bezeichnungen wie „Mathematik“ haben, können Sie prinzipiell als Attribut-Name und
   Attribut-Wert denselben String angeben, wobei der Autor im Fall des Namens wiederum die Kleinschreibung nahelegen
   würde.  Sollte Ihr Fach eine komplexere Bezeichnung wie -- sagen wir -- „Mord & Todschlag“ haben, müssen Sie eine
   eindeutige Kurzbezeichnung erfinden.  Beispielsweise könnte das so aussehen:

       { "mathe" : "Mathematik", "murks" : "Mord & Todschlag" }

   Wenn Sie unterschiedliche Klassen in unterschiedlichen Fächern unterrichten, geben Sie hier bitte *alle* Fächer an,
   die Sie in *irgendeiner* Klasse unterrichten (und dafür den Notenservice anbieten wollen).

 * Als `school_privacy_policy_url` geben Sie bitte die URL der allgemeinen Datenschutzerklärung Ihrer Schule an,
   beziehungsweise jene URL, die Ihnen die Datenschutzbeauftragte Ihrer Schule für diesen Zweck genannt hat.  Sie wird
   benutzt werden, um aus dem Web-Interface des Notenservice' darauf zu verlinken.


### Ein erster Probelauf

Wenn Sie die `.htaccess`, `.htpasswd` und `notenservice.json` Dateien erfolgreich editiert beziehungsweise angelegt
haben, steht einem ersten Probelauf nichts mehr im Wege: Besuchen Sie `https://home.schule.at/~user/notenservice/` in
Ihrem Webbrowser!  Sie sollten mit einem Dialog konfrontiert werden,der Sie dazu auffordert, sich einzuloggen.  Tun Sie
dies vermittels einer der zuvor von Ihnen angelegten Benutzer und dem zugehörigen Passwort.

Wenn Sie jetzt eine Webseite folgenden Inhalts (oder so ähnlich) sehen, haben Sie bisher alles richtig gemacht.

    Super Fancy Notenauskunft (Lehrermodus: Schulrat Lämpel)

    Um Ihren aktuellen Leistungsstand zu erfahren, geben Sie bitte Ihre
    offizielle E-Mail Adresse (d.h. vorname.nachname@schule.at)
    ein.  Andere Adressen (z.B. scherzkeks42@gmx.net) werden nicht
    akzeptiert.  Nach dem Absenden des Formulars erhalten Sie zeitnah ein
    E-Mail an die angegebene Adresse mit Ihrem aktuellen Leistungsstand.

      * Klasse:  [...]
      * Fach:    [...]
      * E-Mail:  ..........
      * [Anfordern]
      * [Abbrechen]

    Hinweise: Lorem ipsum dolor sit amet...

Wenn Sie stattdessen eine Fehlermeldung der Art „500 Internal Server Error“ sehen, haben Sie beim Erstellen / Editieren
der `.htaccess` oder `.htpasswd` Datei einen Fehler gemacht.

Sollten Sie dagegen einen einen Text dieser Art sehen, haben Sie beim Editieren der `notenservice.json` Datei etwas
falsch gemacht.

    HTTP/1.1 500 Fataler Fehler im System

    Die "Super Fancy Notenauskunft" kann aufgrund einer Fehlkonfiguration nicht gestartet werden.
    Bitte informieren Sie Ihre Lehrer*in oder Administrator*in über dieses Problem.

    Fehler: Lorem ipsum dolor sit amet...

Der Text nach „Fehler:“ gibt Ihnen diesfalls hoffentlich Aufschluss darüber, was konkret nicht stimmt.

Sofern Sie keinen Fehler gemacht haben, und die Startseite erfolgreich angezeigt wird, können Sie versuchen, das
Formular auszufüllen, und Auskunft zu beantragen.  Egal welche E-Mail Adresse Sie angeben, sollten nun eine
Fehlermeldung der Art „Die Klasse 1A wird von Schulrat Lämpel nicht in Mathematik unterrichtet.“ bekommen.  Das war zu
erwarten, denn noch haben Sie dem System keine Daten zur Verfügung gestellt.

## Daten

Um sinnvoll Daten abfragen zu können, müssen Sie in dem System pro Klasse und Fach eine [CSV-Datei][WikiCsv]
abspeichern, und zwar in dem Verzeichnis, das Sie zuvor als `data_directory` konfiguriert haben.  Der Name der Datei
muss exakt `${klasse}_${fach}.csv` lauten, wobei `${klasse}` einer der Werte ist, den Sie zuvor in der
Konfigurationsdatei in das Array zu `klassen` eingetragen haben, und `${fach}` eines der Attribute des Objekts, das Sie
ebendort zuvor für `faecher` definiert haben.  Sofern Sie die Klasse `1a` und das Fach `mathe` angelegt haben, würden
Sie die entsprechende Datei also `1a_mathe.csv` nennen.  Bitte achten Sie auf Groß-/Kleinschreibung!  Sofern Sie dem Rat
des Autors gefolgt sind, und stets Kleinschreibung verwendet haben, sollten Sie jetzt kein Problem haben.

Für das CSV-Format selbst sind Sie sehr wenig eingeschränkt.  Wichtig ist, dass Sie die Spalten durch ein Semikolon
trennen; nicht etwa ein Komma.  Zwecks besserer Übersichtlichkeit können Sie (müssen Sie aber nicht) die Semikola
untereinander ausrichten, indem Sie alle Spalten durch Leerzeichen auf dieselbe Breite auffüllen.  Die Anwendung wird
führende und schwänzelnde Leerzeichen verwerfen.  Sie können außerdem Leerzeilen in die Datei einfügen, die ebenfalls
ignoriert werden.

Die erste Zeile jeder CSV-Datei wird als „Titel“ interpretiert.  Hier geben Sie für jede Spalte an, welche Daten die
darauffolgenden Zeilen in ihr enthalten werden.  Sie müssen zumindest zwei Spalten mit den Titeln `Name` und `E-Mail`
definieren.  Anhand dieser wird der Notenservice ermitteln, welche Zeile zu der anfragenden Person passt, und wie diese
anzusprechen ist.  Sie *können* außerdem die Spalte `Datum` einfügen, in der Sie für jede Schüler\*in angeben können,
auf welchem Stand die Daten basieren.  Sofern Sie diese Spalte verwenden, muss sie entweder ein gültiges Datum im
[ISO 8601 Format (YYYY-MM-DD)][WikiISO8601] enthalten, oder leer sein.  Sofern Sie die Spalte leer oder komplett weg
lassen, wird die Anwendung stattdessen das Datum der letzten Modifikation der CSV-Datei verwenden.  Natürlich sollten
Sie zusätzlich zu diesen „Pflichtfeldern“ noch weitere Spalten vorsehen, in denen Sie die für Ihre Schüler\*innen
interessanten Daten, wie beispielsweise ihre aktuelle Note, angeben.  Wie Sie dies gestalten, ist ganz alleine Ihnen
überlassen.  Die Reihenfolge der Spalten können Sie ebenfalls frei wählen.  Ebenso sind Sie nicht daran gebunden, für
alle Dateien dasselbe Format zu verwenden.  Beispielsweise böte es sich an, in jeder Datei nur Spalten für die Tests
einzufügen, die Sie in dieser Klasse in diesem Fach tatsächlich abgehalten haben.

Hier ist ein minimalistisches Beispiel für eine gültige CSV-Datei.

    Name                ; E-Mail                        ; Note ; Kommentar

    Guter Günther       ; guter.guenther@schule.at      ; 1    ; sehr fleißig und aufmerksam
    Schlechter Schorsch ; schlechter.schorsch@schule.at ; 5    ; so geht das nicht weiter

Sofern Sie den „Lehrermodus“ aktiviert haben, können Sie sich nun die Auskunft für eine der soeben eingetragenen
Schüler\*innen an Ihre eigene E-Mail Adresse zusenden lassen.  Sie sollten von der Webseite eine Nachricht der Form
„Eine Nachricht wurde an schlechter.schorsch@schule.at versandt.“ erhalten, und danach zeitnah ein E-Mail dieser Art in
Ihrem Postfach vorfinden.

    Guten Tag,

    jemand, vermutlich Sie selbst, hat Notenauskunft für Schlechter Schorsch
    (1A) in Mathematik beantragt. Falls Sie diese Auskunft nicht beantragt
    haben sollten, ignorieren Sie diese Nachricht bitte. Bitte besuchen Sie
    https://home.schule.at/~user/notenservice/ für weitere Informationen.

    Note: 5
    Kommentar: so geht das nicht weiter

    Die Daten, auf denen diese automatische Auskunft basiert, wurden zuletzt
    am 2020-04-01 aktualisiert.

    Mit freundlichen Grüßen
    i.A. Schulrat Lämpel

Entsprechende Mails würden an die Schüler\*innen selbst versandt, wenn diese sich mit ihrer Klasse und dem zugehörigen
Klassenpasswort einloggen.  Bitte experimentieren Sie mit diversen Daten, um ein Gefühl für das System zu bekommen.

**Nett:** Sofern ein Tageslimit aktiv ist (also insbesondere nicht im Lehrermodus), werden die versandten E-Mails die
beiden Header `X-Notenservice-Counter` und `X-Notenservice-Limit` enthalten, die den aktuellen Zählerstand und das
Tageslimit angeben.


<!-- Referenzen -->

[ApacheHttpd]: https://httpd.apache.org/
[DSGVO]: https://eur-lex.europa.eu/eli/reg/2016/679
[Datenschutzbeauftragter]: https://dsgvo-gesetz.de/themen/datenschutzbeauftragter/
[Datenverarbeitungsregister]: https://dsgvo-gesetz.de/themen/verzeichnis-von-verarbeitungstaetigkeiten/
[Download]: TODO
[GnuLinux]: https://www.gnu.org/gnu/linux-and-gnu.html
[JSON]: https://www.json.org/
[OnlineHtpasswdGenerator]: http://www.htaccesstools.com/htpasswd-generator/
[PHP]: https://www.php.net/
[PhpMail]: https://www.php.net/manual/en/function.mail.php
[PhpSQLite3]: https://www.php.net/manual/en/book.sqlite3.php
[SQLite3]: https://sqlite.org/index.html
[WikiAusführbareDatei]: https://de.wikipedia.org/wiki/Ausf%C3%BChrbare_Datei
[WikiCaptcha]: https://de.wikipedia.org/wiki/Captcha
[WikiCryptoHash]: https://de.wikipedia.org/wiki/Kryptographische_Hashfunktion
[WikiCsv]: https://de.wikipedia.org/wiki/CSV_(Dateiformat)
[WikiHtaccess]: https://de.wikipedia.org/wiki/.htaccess
[WikiHtpasswd]: https://en.wikipedia.org/wiki/.htpasswd
[WikiHttps]: https://de.wikipedia.org/wiki/Hypertext_Transfer_Protocol_Secure
[WikiISO8601]: https://de.wikipedia.org/wiki/ISO_8601
[WikiScriptkiddie]: https://de.wikipedia.org/wiki/Scriptkiddie
[WikiTextdatei]: https://de.wikipedia.org/wiki/Textdatei
[WikiUTF8]: https://de.wikipedia.org/wiki/UTF-8
[htpasswd1]: https://httpd.apache.org/docs/current/programs/htpasswd.html
[sendmail1]: http://www.postfix.org/sendmail.1.html
