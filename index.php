<?php // -*- coding:utf-8; mode:html; -*-
declare(encoding='UTF-8');
require 'notenservice.php';
$ns = new Notenservice\Notenservice(TRUE);
$ns->performAction(TRUE);
?><!DOCTYPE html>

<html>
  <head>
    <meta charset="UTF-8" />
    <title>Die Super Fancy Notenauskunft!</title>
    <link rel="stylesheet" type="text/css" href="notenservice.css" />
    <script type="text/javascript" src="notenservice.js"></script>
  </head>
  <body onload="notenservice_init()">
    <?php $ns->yankPageTitle('    '); ?>
    <p>
      Um Ihren aktuellen Leistungsstand zu erfahren, geben Sie bitte Ihre offizielle E-Mail Adresse
      (d.h.&nbsp;<var>vorname</var>.<var>nachname</var>@schule.at) ein.  Andere Adressen
      (z.B.&nbsp;scherzkeks42@gmx.net) werden nicht akzeptiert.  Nach dem Absenden des Formulars erhalten Sie zeitnah
      ein E-Mail an die angegebene Adresse mit Ihrem aktuellen Leistungsstand.
    </p>
    <form action="./index.php" method="post">
      <ul class="controls">
        <li class="ctrl">
          <label for="klasse">Klasse:</label>
          <select id="klasse" name="klasse" required="true" title="Bitte wählen Sie Ihre Klasse">
            <?php $ns->yankOptionsKlasse('          '); ?>
          </select>
        </li>
        <li class="ctrl">
          <label for="fach">Fach:</label>
          <select id="fach" name="fach" required="true" title="Bitte wählen Sie das gewünschte Unterrichtsfach">
            <?php $ns->yankOptionsFach('          '); ?>
          </select>
        </li>
        <li class="ctrl">
          <label for="email">E-Mail:</label>
          <input type="email" id="email" name="email" required="true" placeholder="E-Mail&thinsp;&hellip;" title="Bitte geben Sie Ihre offizielle E-Mail-Adresse ein" />
        </li>
        <li class="ctrl">
          <button type="submit" title="Bitte klicken Sie um die Information
                                       anzufordern">Anfordern</button>
        </li>
        <li class="ctrl">
          <button type="reset" title="Bitte klicken Sie um Ihre Eingaben zu verwerfen">Abbrechen</button>
        </li>
      </ul>
    </form>
    <?php $ns->yankFormActionResult('    '); ?>
    <div id="more-info">
      <p><strong>Hinweise:</strong></p>
      <ol>
        <li>
          Die aktuelle Notenauskunft wird an die angegebene E-Mail Adresse geschickt.  Es ist nicht möglich, die Daten
          an eine andere, als die <em>eigene</em> offizielle Schul-Adresse senden zu lassen.
        </li>
        <li>
          Bitte beachten Sie, dass die Angabe einer anderen als der eigenen E-Mail Adresse eine moralisch verwerfliche
          und rechtswidrige Handlung darstellen und verfolgt werden kann.
        </li>
        <li>
          Die Anzahl der erlaubten Auskünfte pro Tag ist limitiert.  Ist das Limit erreicht, werden weitere Anfragen bis
          zum Ablauf des Kalendertages verweigert.  Das Limit gilt individuell für jedes Unterrichtsfach.
        </li>
        <li>
          Anhand der Statusmeldungen des Systems wäre ein Angreifer prinzipiell in der Lage, zu einer gegebenen E-Mail
          Adresse herauszufinden, ob eine Schüler*in mit einer solchen existiert, und wie viele Auskünfte für diese am
          aktuellen Tag bereits angefordert wurden.  Um diesen potentiellen Angriff zu erschweren, ist der Zugriff auf
          das gesammte System mit einem &bdquo;Klassenpasswort&ldquo; geschützt, dass Sie von Ihrer Lehrer*in erhalten
          haben sollten.  Es ist Ihnen nicht gestattet, dieses Passwort an unbefugte Dritte weiterzugeben und sind dazu
          verpflichtet, zumutbare Maßnahmen nach dem aktuellen Stand der Technik zu ergreifen, um zu verhindern, dass
          unbefugte Dritte Kenntnis davon erlangen.
        </li>
        <li>
          Diese Anwendung speichert pro Klasse und Fach eine Datei mit dem aktuellen Leistungsständen der jeweiligen
          Schüler*innen nebst ihren Namen und E-Mail Adressen.  Außerdem wird eine Logdatei geführt, in der bei jeder
          erfolgreichen Anfrage erfasst wird, um die wievielte Anfrage an diesem Tag es sich handelt.  In der Logdatei
          wird ein kryptographischer Hash &ndash; gebildet aus der Klassen, dem Fach und der E-Mail Adresse &ndash;
          verwendet, um das Datum (Kalendertag) &ndash; nicht jedoch die Uhrzeit &ndash; und die Information, wie viele
          Abfragen bereits erfolgten, zu erfassen.  Diese Daten werden automatisch gelöscht, wenn nach Ende des Tages
          zum ersten Mal wieder auf die Logdatei zugegriffen wird.  Der genaue Zeitpunkt dafür ist aus technischen
          Gründen abhängig von der Systemlast.  Bitte seien Sie sich bewusst, dass die Verwendung einer
          kryptographischen Hashfunktion an dieser Stelle eher eine nette Geste darstellt, und keine wirkliche
          Urbildresistenz liefert, da aufgrund des sehr kleinen Urbildraums auch ein minderbemittelter Angreifer sehr
          einfach die Hashfunktion invertieren könnte.  Alle Daten werden im Einklang mit den Vorgaben der Schule zum
          verantwortlichen Umgang mit personenbezogenen Daten von Schüler*innen in dem privaten
          &bdquo;Home&ldquo;-Verzeichnis der verantwortlichen Lehrer*in gespeichert.
        </li>
        <li>
          Mit Ausnahme der oben erwähnten Logdatei handelt es sich bei allen von dieser Anwendung gespeicherten und
          verarbeiteten personenbezogenen Daten ausschließlich um solche Daten, deren Verarbeitung durch die
          verantwortliche Lehrer*in im Zuge der Erfüllung ihrer Dienstpflichten rechtlich zulälssig ist
          (<a rel="external nofollow" href="https://dsgvo-gesetz.de/art-6-dsgvo/">Art&nbsp;6 Abs&nbsp;1 Lit&nbsp;e
          DSGVO</a>).  Rechtsgrundlage für die Speicherung und Verarbeitung der personenbezogenen Daten in der Logdatei
          ist das berechtigte Interesse (<a rel="external nofollow"
          href="https://dsgvo-gesetz.de/art-6-dsgvo/">Art&nbsp;6 Abs&nbsp;1 Lit&nbsp;f DSGVO</a>), eine missbräuchliche
          Verwendung des Systems zu unterbinden, wodurch der zuverlässige Betrieb des Systems gewährleistet und eine
          Schädigung Dritter verhindert werden soll.  Die hierfür verwendeten Daten beschänken sich auf das technisch
          erforderliche Minimum, um den erstrebten Zweck zu erreichen.  Eine anderweitige Verwendung der Daten findet
          nicht statt.
        </li>
        <li>
          Von der oben erwähnten Datenverarbeitung unabhängig sind solche Daten, die außerhalb dieses Systems etwa im
          Zuge des allgemeinen Monitorings der Webseite der Schule erfasst, gespeichert und verarbeitet werden.  Hierauf
          hat Ihre Lehrer*in weder Einfluss noch Einblick hierein.
        </li>
        <li>
          Wenn Sie Anlass zur Sorge haben, dass das System nicht einwandfrei funktioniert oder seine Sicherheit
          kompromittiert wurde, wenden Sie sich bitte unverzüglich an <?php $ns->yankTeacherContactLink("Ihre Lehrer*in"); ?>.
        </li>
        <li>
          Für weitere Fragen zum Datenschutz konsultieren Sie bitte die aktuell gültige Fassung der allgemeinen
          <?php $ns->yankPrivacyPolicyLink("Datenschutzerklärung"); ?> Ihrer Schule.
        </li>
      </ol>
    </div>
  </body>
</html>
