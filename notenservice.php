<?php

declare(encoding='UTF-8');
namespace Notenservice;

const special_csv_columns = array('Name', 'E-Mail', 'Datum');

final class Notenservice {

    private $setup;
    private $user;
    private $klasse;
    private $fach;
    private $email;
    private $actionResult = NULL;
    private $actionInform = NULL;

    public function __construct($handleEx = FALSE) {
        try {
            $this->setup  = Notenservice::loadSetup('notenservice.json');
        } catch (\Exception $e) {
            $handleEx ? Notenservice::handleBadSetup($e) : rethrow($e);
        }
        $this->user   = get_authenticated_user_name();
        $this->klasse = get_query_parameter_trimmed('klasse');
        $this->fach   = get_query_parameter_trimmed('fach');
        $this->email  = get_query_parameter_trimmed('email');
    }

    public function actionRequired() {
        return !empty($_POST);
    }

    public function performAction($header = FALSE) {
        if ($this->actionRequired()) {
            try {
                $recipient = $this->sanityAccessCheck();
                $info = $this->getStudentInfo();
                $daycount = $this->getDailyCounter();
                $this->serveInfo($info, $recipient, $daycount);
                $this->actionResult = TRUE;
                $this->actionInform = "Eine Nachricht wurde an {$recipient} versandt.";
            } catch (\Exception $e) {
                $this->actionResult = FALSE;
                $this->actionInform = $e->getMessage();
                if ($header) {
                    http_response_code($e->getCode());
                }
            }
        }
    }

    public function yankFormActionResult($indent = '') {
        if ($this->actionResult === NULL) {
            echo "<!-- There was no previous PHP action. -->\n";
        } else {
            $clazz = $this->actionResult ? 'success' : 'failure';
            echo "<!-- This is the result of the previous PHP action. -->\n";
            echo $indent . sprintf('<p class="%s">%s</p>', $clazz, html_escape($this->actionInform)) . "\n";
        }
    }

    public function yankPageTitle($indent = '') {
        $title = "Super Fancy Notenauskunft" ;
        if ($this->user === $this->setup->teacher->user) {
            $title .= " (Lehrermodus: " . $this->setup->teacher->name . ")";
        } else if ($this->isValidUser(FALSE)) {
            $title .= " (" . strtoupper($this->user) . ", " . $this->setup->teacher->name . ")";
        }
        echo "<!-- This is a PHP generated page title. -->\n";
        echo $indent . "<h1>" . html_escape($title) . "</h1>\n";
    }

    public function yankOptionsKlasse($indent = '') {
        $n = count($this->setup->klassen);
        echo "<!-- This is a PHP generated list of {$n} choices. -->\n";
        if (!$this->isValidUser(FALSE)) {
            echo $indent . '<option value="" disabled="true" selected="true" style="display:none">Klasse&thinsp;&hellip;</option>' . "\n";
        } else {
            echo $indent . '<option value="" disabled="true" style="display:none">Klasse&thinsp;&hellip;</option>' . "\n";
        }
        foreach ($this->setup->klassen as $val) {
            $xkey = html_escape(strtolower($val));
            $xval = html_escape(strtoupper($val));
            if ($this->user === $this->setup->teacher->user) {
                printf('%s<option value="%s">%s</option>', $indent, $xkey, $xval);
            } else if (!strcasecmp($this->user, $val)) {
                printf('%s<option value="%s" selected="true">%s</option>', $indent, $xkey, $xval);
            } else {
                printf('%s<option value="%s" disabled="true">%s</option>', $indent, $xkey, $xval);
            }
            echo "\n";
        }
    }

    public function yankOptionsFach($indent = '') {
        $n = count($this->setup->faecher);
        echo "<!-- This is a PHP generated list of {$n} choices. -->\n";
        echo $indent . '<option value="" disabled="true" selected="true" style="display:none">Fach&thinsp;&hellip;</option>' . "\n";
        foreach ($this->setup->faecher as $key => $val) {
            printf('%s<option value="%s">%s</option>', $indent, html_escape($key), html_escape($val));
            echo "\n";
        }
    }

    public function yankTeacherContactLink($text = NULL) {
        if ($text === NULL) { $text = $this->setup->teacher->name; }
        printf('<a href="mailto:%s">%s</a>', html_escape($this->setup->teacher->email), html_escape($text));
    }

    public function yankPrivacyPolicyLink($text = NULL, $title = NULL) {
        if ($text === NULL) { $text = "Datenschutzerklärung"; }
        if (is_void_string($title)) {
            printf('<a href="%s">%s</a>', html_escape($this->setup->school_privacy_policy_url), html_escape($text));
        } else {
            printf('<a href="%s" title="%s">%s</a>', html_escape($this->setup->school_privacy_policy_url), html_escape($title), html_escape($text));
        }
    }

    private function sanityAccessCheck() {
        if (!$this->isValidUser()) {
            throw new \Exception("Das hätte nie passieren dürfen!", 401);
        }
        if (!in_array($this->klasse, $this->setup->klassen)) {
            throw new \Exception("Die ausgewählte Klasse ist ungültig.", 404);
        }
        if (!array_key_exists($this->fach, $this->setup->faecher)) {
            throw new \Exception("Das ausgewählte Fach ist ungültig.", 404);
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Die angegebene E-Mail Adresse ist ungültig.", 404);
        }
        if (($this->user !== $this->setup->teacher->user) && (0 !== strcasecmp($this->user, $this->klasse))) {
            $have = strtoupper($this->user);
            $want = strtoupper($this->klasse);
            throw new \Exception("Als Schüler*in der Klasse {$have} dürfen Sie keine Daten der Klasse {$want} abfragen.", 403);
        }
        return (($this->user === $this->setup->teacher->user) ? $this->setup->teacher->email : $this->email);
    }

    private function getStudentInfo() {
        $lookup = sprintf('%s/%s_%s.csv', $this->setup->data_directory, $this->klasse, $this->fach);
        try {
            $headers = NULL;
            $fh = fopen($lookup, 'r');
            if ($fh === FALSE) {
                $dieklasse = strtoupper($this->klasse);
                $dasfach = $this->setup->faecher[$this->fach];
                throw new \Exception("Die Klasse {$dieklasse} wird von {$this->setup->teacher->name} nicht in {$dasfach} unterrichtet.", 404);
            }
            $blank = array(NULL);  // Will be returned by fgetcsv() for blank lines.
            while ($record = fgetcsv($fh, 0, ';')) {
                if ($record === $blank) continue;
                $record = array_map('trim', $record);
                if ($headers === NULL) {
                    $headers = $record;
                    if (!in_array('Name', $headers) || !in_array('E-Mail', $headers)) {
                        throw new \Exception("Üble CSV-Daten: Die Spalten 'Name' und 'E-Mail' sind erforderlich.", 500);
                    }
                } else if ($info = array_to_dict($headers, $record)) {
                    if ($info['E-Mail'] === $this->email) {
                        if (!array_key_exists('Datum', $info) || is_void_string($info['Datum'])) {
                            $st = fstat($fh);
                            $info['Datum'] = strftime('%Y-%m-%d', $st['mtime']);
                        } else if (!preg_match('/^\d\d\d\d-\d\d-\d\d$/', $info['Datum'])) {
                            throw new \Exception("Üble CSV-Daten: Die Spalte 'Datum' muss (sofern vorhanden) ein gültiges Kalenderdatum im ISO 8601 Format (YYYY-MM-DD) enthalten.", 500);
                        }
                        return $info;
                    }
                } else {
                    throw new \Exception("Üble CSV-Daten: Alle nicht leeren Zeilen müssen dieselbe Anzahl an Spalten haben.", 500);
                }
            }
        } finally {
            if ($fh !== FALSE) {
                fclose($fh);
            }
        }
        throw new \Exception("Die angegebene E-Mail Adresse {$this->email} ist unbekannt.", 404);
    }

    private function getDailyCounter() {
        $problem = function ($what = "Ein Problem mit der Datenbank ist aufgetreten.") {
            throw new \Exception($what, 500);
        };
        if ($this->setup->logfile === NULL) {
            assert($this->setup->daily_limit < 0);
            return 0;
        }
        try {
            $db = new \SQLite3($this->setup->logfile);
        } catch (\Exception $e) {
            $problem("Die Datenbank hat heute geschlossen.");
        }
        // This races but what else could we do?  Using umask() is bad, too.
        chmod($this->setup->logfile, 0600);
        try {
            $token = md5($this->klasse . "\033" . $this->fach . "\033" . $this->email);
            $counter = 0;
            $db->exec("CREATE TABLE IF NOT EXISTS `AccessLog` ( `token` STRING PRIMARY KEY, `date` STRING, `count` INTEGER )") or $problem();
            $db->exec("DELETE FROM `AccessLog` WHERE `date` != date('now')") or $problem();
            if ($this->user !== $this->setup->teacher->user) {
                $db->exec("BEGIN") or $problem();
                $stmt = $db->prepare("SELECT `count` FROM `AccessLog` WHERE `token` = :token");
                $stmt->bindValue(':token', $token, SQLITE3_TEXT);
                $result = $stmt->execute();
                $row = $result->fetchArray(SQLITE3_NUM);
                $counter = 1 + (($row === FALSE) ? 0 : intval($row[0]));
                $result->reset();
                $stmt = $db->prepare("INSERT OR REPLACE INTO `AccessLog` VALUES ( :token, date('now'), :count )");
                $stmt->bindValue(':token', $token, SQLITE3_TEXT);
                $stmt->bindValue(':count', $counter, SQLITE3_INTEGER);
                $stmt->execute();
                $db->exec("COMMIT") or $problem();
            }
        } finally {
            $db->close() or $problem();
        }
        if (($this->setup->daily_limit >= 0) && ($counter > $this->setup->daily_limit)) {
            throw new \Exception("Sie dürfen nicht mehr als {$this->setup->daily_limit} Anfragen für ein Fach am Tag stellen.", 429);
        }
        return $counter;
    }

    private function serveInfo($info, $to, $daycount = 0) {
        $host = $_SERVER['SERVER_NAME'] or gethostname();
        $token = uuidgen();
        $dername = $info['Name'];
        $dieklasse = strtoupper($this->klasse);
        $dasfach = $this->setup->faecher[$this->fach];
        $subject = mb_encode_mimeheader("Notenauskunft für {$dername} ({$dieklasse}) in {$dasfach}", 'UTF-8');
        $additional_headers = array('From' => $this->setup->service->email);
        if ($this->setup->service->email !== $this->setup->teacher->email) {
            $additional_headers['Reply-To'] = $this->setup->teacher->email;
        }
        if ($daycount > 0) {
            $additional_headers['X-Notenservice-Counter'] = $daycount;
            $additional_headers['X-Notenservice-Limit'] = $this->setup->daily_limit;
        }
        $additional_headers['Message-Id'] = sprintf('<%s@%s>', $token, $host);
        $additional_headers['MIME-Version'] = '1.0';
        $additional_headers['Content-Type'] = "multipart/alternative; boundary=\"{$token}\"";
        $additional_headers = transmogrify_additional_headers($additional_headers);
        $prolog = "jemand, vermutlich Sie selbst, hat Notenauskunft für {$dername} ({$dieklasse}) in {$dasfach} beantragt. "
                . " Falls Sie diese Auskunft nicht beantragt haben sollten, ignorieren Sie diese Nachricht bitte. "
                . " Bitte besuchen Sie {$this->setup->service->url} für weitere Informationen.";
        $epilog = "Die Daten, auf denen diese automatische Auskunft basiert, wurden zuletzt am {$info['Datum']} aktualisiert.";
        $tabular = array_diff_key($info, array_flip(special_csv_columns));
        $params = array("Guten Tag,", $prolog, $tabular, $epilog, "Mit freundlichen Grüßen", "i.A. {$this->setup->teacher->name}");
        $text = call_user_func_array('Notenservice\Notenservice::composeMailText', $params);
        $html = call_user_func_array('Notenservice\Notenservice::composeMailHtml', $params);
        $payload = Notenservice::makeMultipartAlternative($text, $html, $token);
        if (!mail($to, $subject, $payload, $additional_headers)) {
            throw new \Exception("Beim Versand der Nachricht an {$to} ist ein Fehler aufgetreten.", 500);
        }
    }

    private static function makeMultipartAlternative($text, $html, $boundary) {
        return ""
            . "This message is sent as plain text and HTML so you can pick your choice.\r\n"
            . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=\"UTF-8\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . "\r\n"
            . chunk_split(base64_encode($text))
            . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=\"UTF-8\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . "\r\n"
            . chunk_split(base64_encode($html))
            . "\r\n"
            . "--{$boundary}--\r\n";
    }

    private static function composeMailText($hello, $prolog, $tabular, $epilog, $goodbye, $fromwhom) {
        $ww1 = function ($text) { return wordwrap($text, 72, "\n", FALSE); };
        $ww2 = function ($text) { return wordwrap($text, 70, "\n  ", TRUE); };
        $preformatted = array();
        foreach ($tabular as $key => $val) {
            array_push($preformatted, $ww2("{$key}: {$val}"));
        }
        return ""
            . $hello . "\n\n"
            . $ww1($prolog) . "\n\n"
            . join("\n", $preformatted) . "\n\n"
            . $ww1($epilog) . "\n\n"
            . $goodbye . "\n"
            . $fromwhom . "\n";
    }

    private static function composeMailHtml($hello, $prolog, $tabular, $epilog, $goodbye, $fromwhom) {
        $mkrow = function ($key, $val) {
            $thekey = html_escape($key);
            $theval = html_escape($val);
            return "<tr><th>{$thekey}:</th><td>{$theval}</td></tr>\n";
        };
        $fancy = function ($text) {
            $f = function ($s) { return html_escape($s, TRUE); };
            return join(' ', array_map($f, preg_split('/\s+/', $text)));
        };
        return ""
            . "<!DOCTYPE html>\n"
            . "<html>\n"
            . "<head>\n"
            . "<meta charset=\"UTF-8\" />\n"
            . "<title>Super Fancy Notenauskunft</title>\n"
            . "<style class=\"text/css\">\n"
            . "\ttable.info th { text-align: left; padding-right: 1em; }\n"
            . "</style>\n"
            . "</head>\n"
            . "<body lang=\"de\">\n"
            . "<p>" . html_escape($hello) . "</p>\n"
            . "<p>" . $fancy($prolog) . "</p>\n"
            . "<table class=\"info\">\n"
            . join("", array_map($mkrow, array_keys($tabular), array_values($tabular)))
            . "</table>\n"
            . "<p>" . $fancy($epilog) . "</p>\n"
            . "<p>" . html_escape($goodbye) . "<br/>" . html_escape($fromwhom) . "</p>\n"
            . "</body>\n"
            . "</html>";
    }

    private function isValidUser($teacher = TRUE) {
        if (is_void_string($this->user) || !preg_match('/^\w+$/', $this->user)) {
            return FALSE;
        } else if ($teacher && ($this->user === $this->setup->teacher->user)) {
            return TRUE;
        } else {
            $valid_users_lower = array_map('strtolower', $this->setup->klassen);
            return in_array(strtolower($this->user), $valid_users_lower, TRUE);
        }
    }

    private static function loadSetup($filename) {
        $line_filter = function($line) {
            return (($line !== '') && (substr($line, 0, 2) !== '//'));
        };
        $lines = file($filename, FILE_USE_INCLUDE_PATH | FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === FALSE) {
            throw new \Exception("Die Konfigurationsdatei '{$filename}' kann nicht gelesen werden.", 500);
        }
        $json = join(' ', array_filter(array_map('trim', $lines), $line_filter));
        $setup = json_decode($json);
        if ($setup === NULL) {
            throw new \Exception("Die Konfigurationsdatei '{$filename}' kann nicht als JSON-Dokument geparst werden.", 500);
        }
        // validation
        $bad = function ($what) use ($filename) {
            throw new \Exception("Die Konfigurationsdatei '{$filename}' enthält ungültige Daten.\n{$what}", 500);
        };
        $badtype = function ($key, $type) use ($bad) {
            $bad("Der Parameter '{$key}' erwartet einen Wert vom Typ {$type}.");
        };
        $badobj = function ($key, $objkeys) use ($bad) {
            $n = count($objkeys);
            $bad("Der Parameter '{$key}' erwartet einen Wert vom Typ OBJECT mit folgenden {$n} Attributen: " . join(", ", $objkeys));
        };
        $badvalue = function ($key, $what) use ($bad) {
            $bad("Der Parameter '{$key}' erwartet als Wert {$what}.");
        };
        $islist = function ($thing) {
            return (is_array($thing) && (array_values($thing) === $thing));
        };
        $isdict = function ($thing, $keys) {
            return (is_object($thing) && (get_object_keys($thing, TRUE) === $keys));
        };
        $isword = function ($token) {
            return (is_string($token) && preg_match('/^\w+$/', $token));
        };
        $expected_keys_top_level = array(
            'daily_limit',
            'data_directory',
            'faecher',
            'klassen',
            'logfile',
            'school_privacy_policy_url',
            'service',
            'teacher',
        );
        $service_keys = array('email', 'url');
        $teacher_keys = array('email', 'name', 'user');
        // syntactic checks
        if (!$isdict($setup, $expected_keys_top_level)) {
            $n = count($expected_keys_top_level);
            $bad("Erwartet wird einen Wert vom Typ OBJECT mit folgenden {$n} Attributen: " . join(", ", $expected_keys_top_level));
        }
        if (!is_int($setup->daily_limit)) { $badtype('daily_limit', 'INTEGER'); }
        if (!is_string($setup->data_directory)) { $badtype('data_directory', 'STRING'); }
        if (!is_object($setup->faecher)) { $badtype('faecher', 'OBJECT'); }
        if (!$islist($setup->klassen)) { $badtype('klassen', 'ARRAY'); }
        if (!is_string($setup->logfile)) { $badtype('logfile', 'STRING'); }
        if (!is_string($setup->school_privacy_policy_url)) { $badtype('school_privacy_policy_url', 'STRING'); }
        if (!$isdict($setup->service, $service_keys)) { $badobj('service', $service_keys); }
        if (!$isdict($setup->teacher, $teacher_keys)) { $badobj('teacher', $teacher_keys); }
        if (!is_string($setup->service->email)) { $badtype('service.email', 'STRING'); }
        if (!is_string($setup->service->url)) { $badtype('service.url', 'STRING'); }
        if (!is_string($setup->teacher->email)) { $badtype('teacher.email', 'STRING'); }
        if (!is_string($setup->teacher->name)) { $badtype('teacher.name', 'STRING'); }
        if (($setup->teacher->user !== NULL) && !is_string($setup->teacher->user)) {
            $badtype('teacher.user', 'STRING oder NULL');
        }
        // transmogrifications
        $setup->faecher = get_object_vars($setup->faecher);
        // semantic checks
        if (!is_dir($setup->data_directory)) {
            $badvalue('data_directory', "ein existierendes Verzeichnis");
        }
        if (!filter_var($setup->school_privacy_policy_url, FILTER_VALIDATE_URL)) {
            $badvalue('school_privacy_policy_url', "eine gültige URL");
        }
        if (!filter_var($setup->service->email, FILTER_VALIDATE_EMAIL)) {
            $badvalue('service.email', "eine gültige E-Mail Adresse");
        }
        if (!filter_var($setup->service->url, FILTER_VALIDATE_URL)) {
            $badvalue('service.url', "eine gültige URL");
        }
        if (!filter_var($setup->teacher->email, FILTER_VALIDATE_EMAIL)) {
            $badvalue('teacher.email', "eine gültige E-Mail Adresse");
        }
        if (is_void_string($setup->teacher->name)) {
            $badvalue('teacher.name', "einen vernünftigen Namen");
        }
        if (($setup->teacher->user !== NULL) && !$isword($setup->teacher->user)) {
            $badvalue('teacher.user', "entweder NULL oder einen \\w+ STRING");
        }
        if (($setup->logfile === NULL) && ($setup->daily_limit >= 0)) {
            $bad("Wenn Sie den Parameter 'logfile' auf NULL setzen, müssen Sie 'daily_limit' ebenfalls auf -1 setzen.");
        }
        if (array_filter($setup->klassen, $isword) !== $setup->klassen) {
            $badvalue('klassen', "ein ARRAY von \\w+ STRINGs");
        }
        if (count($setup->klassen) !== count(array_flip(array_map('strtolower', $setup->klassen)))) {
            $bad("Die Werte des Parameters 'klassen' dürfen sich nicht lediglich bezüglich Ihrer Groß-/Kleinschreibung unterscheiden.");
        }
        if (count($setup->faecher) !== count(array_flip(array_map('strtolower', array_keys($setup->faecher))))) {
            $bad("Die Attribute des Parameters 'faecher' dürfen sich nicht lediglich bezüglich Ihrer Groß-/Kleinschreibung unterscheiden.");
        }
        $faecher_keys = array_keys($setup->faecher);
        if (array_filter($faecher_keys, $isword) !== $faecher_keys) {
            $badvalue('klassen', "ein OBJECT mit \\w+ STRINGs als Attributen");
        }
        if (($setup->teacher->user !== NULL) && in_array(strtolower($setup->teacher->user), array_map('strtolower', $setup->klassen))) {
            $bad("Es ist äußerst unvernünftig, den Parameter 'teacher.user' auf einen Wert zu setzen, der ebenfalls als Wert von 'klassen' angegeben ist.");
        }
        return $setup;
    }

    private static function handleBadSetup($ex) {
        $status = $ex->getCode();
        assert($status === 500);
        http_response_code($status);
        header('Content-Type: text/plain; charset="UTF-8"');
        echo "{$_SERVER['SERVER_PROTOCOL']} {$status} Fataler Fehler im System\n\n";
        echo "Die \"Super Fancy Notenauskunft\" kann aufgrund einer Fehlkonfiguration nicht gestartet werden.\n";
        echo "Bitte informieren Sie Ihre Lehrer*in oder Administrator*in über dieses Problem.\n\n";
        echo "Fehler: " . $ex->getMessage() . "\n";
        exit(1);
    }
}

function uuidgen() {
    $data = bin2hex(random_bytes(16));
    $data[12] = '4';
    $data[16] = '8';
    $parts = array(
        substr($data,  0,  8),
        substr($data,  8,  4),
        substr($data, 12,  4),
        substr($data, 16,  4),
        substr($data, 20, 12),
    );
    return join('-', $parts);
}

function transmogrify_additional_headers($dict) {
    if (PHP_VERSION_ID >= 70200) {
        // As of PHP 7.2.0 the mail() function finally supports an array for
        // the additional headers parameter.
        return $dict;
    } else {
        $lines = array();
        foreach ($dict as $key => $val) {
            array_push($lines, "{$key}: {$val}");
        }
        return join("\r\n", $lines);
    }
}

function array_to_dict($keys, $values) {
    $n = count($keys);
    if ($n !== count($values)) return NULL;
    $dict = array();
    for ($i = 0; $i < $n; ++$i) {
        $key = $keys[$i];
        $val = $values[$i];
        $dict[$key] = $val;
    }
    return $dict;
}

function html_escape($text, $autolink = FALSE) {
    $flags = ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE;
    $safe = htmlspecialchars($text, $flags);
    if ($autolink) {
        if (preg_match('/^(https?|ftps?):\/\/[^ ]+$/', $text)) {
            return "<a href=\"{$safe}\">{$safe}</a>";
        }
        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            return "<a href=\"mailto:{$safe}\">{$safe}</a>";
        }
    }
    return $safe;
}

function get_authenticated_user_name() {
    return isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : NULL;
}

function get_query_parameter_trimmed($name) {
    return isset($_POST[$name]) ? trim($_POST[$name]) : NULL;
}

function is_void_string($token) {
    return (!isset($token) || ($token === NULL) || (trim($token) === ''));
}

function get_object_keys($obj, $sort = FALSE) {
    $keys = array_keys(get_object_vars($obj));
    if ($sort) { sort($keys); }
    return $keys;
}

function rethrow($ex) {
    throw $ex;
}
