<?php

/** Adminer - Compact database management
 * @link https://www.adminer.org/
 * @author Jakub Vrana, https://www.vrana.cz/
 * @copyright 2007 Jakub Vrana
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 * @version 5.3.0
 */

namespace
Adminer;

const
    VERSION = "5.3.0";
error_reporting(24575);
set_error_handler(function ($qc, $sc) {
    return !!preg_match('~^Undefined (array key|offset|index)~', $sc);
}, E_WARNING | E_NOTICE);
$Lc = !preg_match('~^(unsafe_raw)?$~', ini_get("filter.default"));
if ($Lc || ini_get("filter.default_flags")) {
    foreach (array('_GET', '_POST', '_COOKIE', '_SERVER') as $X) {
        $oi = filter_input_array(constant("INPUT$X"), FILTER_UNSAFE_RAW);
        if ($oi) $$X = $oi;
    }
}
if (function_exists("mb_internal_encoding")) mb_internal_encoding("8bit");
function
connection($f = null)
{
    return ($f ?: Db::$instance);
}
function
adminer()
{
    return
        Adminer::$instance;
}
function
driver()
{
    return
        Driver::$instance;
}
function
connect()
{
    $ub = adminer()->credentials();
    $K = Driver::connect($ub[0], $ub[1], $ub[2]);
    return (is_object($K) ? $K : null);
}
function
idf_unescape($u)
{
    if (!preg_match('~^[`\'"[]~', $u)) return $u;
    $ie = substr($u, -1);
    return
        str_replace($ie . $ie, $ie, substr($u, 1, -1));
}
function
q($sh)
{
    return
        connection()->quote($sh);
}
function
escape_string($X)
{
    return
        substr(q($X), 1, -1);
}
function
idx($ra, $y, $j = null)
{
    return ($ra && array_key_exists($y, $ra) ? $ra[$y] : $j);
}
function
number($X)
{
    return
        preg_replace('~[^0-9]+~', '', $X);
}
function
number_type()
{
    return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';
}
function
remove_slashes(array $ng, $Lc = false)
{
    if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
        while (list($y, $X) = each($ng)) {
            foreach (
                $X
                as $be => $W
            ) {
                unset($ng[$y][$be]);
                if (is_array($W)) {
                    $ng[$y][stripslashes($be)] = $W;
                    $ng[] = &$ng[$y][stripslashes($be)];
                } else $ng[$y][stripslashes($be)] = ($Lc ? $W : stripslashes($W));
            }
        }
    }
}
function
bracket_escape($u, $za = false)
{
    static $bi = array(':' => ':1', ']' => ':2', '[' => ':3', '"' => ':4');
    return
        strtr($u, ($za ? array_flip($bi) : $bi));
}
function
min_version($Ci, $we = "", $f = null)
{
    $f = connection($f);
    $Xg = $f->server_info;
    if ($we && preg_match('~([\d.]+)-MariaDB~', $Xg, $B)) {
        $Xg = $B[1];
        $Ci = $we;
    }
    return $Ci && version_compare($Xg, $Ci) >= 0;
}
function
charset(Db $e)
{
    return (min_version("5.5.3", 0, $e) ? "utf8mb4" : "utf8");
}
function
ini_bool($Ld)
{
    $X = ini_get($Ld);
    return (preg_match('~^(on|true|yes)$~i', $X) || (int)$X);
}
function
sid()
{
    static $K;
    if ($K === null) $K = (SID && !($_COOKIE && ini_bool("session.use_cookies")));
    return $K;
}
function
set_password($Bi, $P, $V, $G)
{
    $_SESSION["pwds"][$Bi][$P][$V] = ($_COOKIE["adminer_key"] && is_string($G) ? array(encrypt_string($G, $_COOKIE["adminer_key"])) : $G);
}
function
get_password()
{
    $K = get_session("pwds");
    if (is_array($K)) $K = ($_COOKIE["adminer_key"] ? decrypt_string($K[0], $_COOKIE["adminer_key"]) : false);
    return $K;
}
function
get_val($I, $l = 0, $kb = null)
{
    $kb = connection($kb);
    $J = $kb->query($I);
    if (!is_object($J)) return
        false;
    $L = $J->fetch_row();
    return ($L ? $L[$l] : false);
}
function
get_vals($I, $c = 0)
{
    $K = array();
    $J = connection()->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) $K[] = $L[$c];
    }
    return $K;
}
function
get_key_vals($I, $f = null, $ah = true)
{
    $f = connection($f);
    $K = array();
    $J = $f->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) {
            if ($ah) $K[$L[0]] = $L[1];
            else $K[] = $L[0];
        }
    }
    return $K;
}
function
get_rows($I, $f = null, $k = "<p class='error'>")
{
    $kb = connection($f);
    $K = array();
    $J = $kb->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_assoc()) $K[] = $L;
    } elseif (!$J && !$f && $k && (defined('Adminer\PAGE_HEADER') || $k == "-- ")) echo $k . error() . "\n";
    return $K;
}
function
unique_array($L, array $w)
{
    foreach (
        $w
        as $v
    ) {
        if (preg_match("~PRIMARY|UNIQUE~", $v["type"])) {
            $K = array();
            foreach ($v["columns"] as $y) {
                if (!isset($L[$y])) continue
                    2;
                $K[$y] = $L[$y];
            }
            return $K;
        }
    }
}
function
escape_key($y)
{
    if (preg_match('(^([\w(]+)(' . str_replace("_", ".*", preg_quote(idf_escape("_"))) . ')([ \w)]+)$)', $y, $B)) return $B[1] . idf_escape(idf_unescape($B[2])) . $B[3];
    return
        idf_escape($y);
}
function
where(array $Z, array $m = array())
{
    $K = array();
    foreach ((array)$Z["where"] as $y => $X) {
        $y = bracket_escape($y, true);
        $c = escape_key($y);
        $l = idx($m, $y, array());
        $Jc = $l["type"];
        $K[] = $c . (JUSH == "sql" && $Jc == "json" ? " = CAST(" . q($X) . " AS JSON)" : (JUSH == "sql" && is_numeric($X) && preg_match('~\.~', $X) ? " LIKE " . q($X) : (JUSH == "mssql" && strpos($Jc, "datetime") === false ? " LIKE " . q(preg_replace('~[_%[]~', '[\0]', $X)) : " = " . unconvert_field($l, q($X)))));
        if (JUSH == "sql" && preg_match('~char|text~', $Jc) && preg_match("~[^ -@]~", $X)) $K[] = "$c = " . q($X) . " COLLATE " . charset(connection()) . "_bin";
    }
    foreach ((array)$Z["null"] as $y) $K[] = escape_key($y) . " IS NULL";
    return
        implode(" AND ", $K);
}
function
where_check($X, array $m = array())
{
    parse_str($X, $Qa);
    remove_slashes(array(&$Qa));
    return
        where($Qa, $m);
}
function
where_link($s, $c, $Y, $pf = "=")
{
    return "&where%5B$s%5D%5Bcol%5D=" . urlencode($c) . "&where%5B$s%5D%5Bop%5D=" . urlencode(($Y !== null ? $pf : "IS NULL")) . "&where%5B$s%5D%5Bval%5D=" . urlencode($Y);
}
function
convert_fields(array $d, array $m, array $O = array())
{
    $K = "";
    foreach (
        $d
        as $y => $X
    ) {
        if ($O && !in_array(idf_escape($y), $O)) continue;
        $sa = convert_field($m[$y]);
        if ($sa) $K
            .= ", $sa AS " . idf_escape($y);
    }
    return $K;
}
function
cookie($D, $Y, $qe = 2592000)
{
    header("Set-Cookie: $D=" . urlencode($Y) . ($qe ? "; expires=" . gmdate("D, d M Y H:i:s", time() + $qe) . " GMT" : "") . "; path=" . preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"]) . (HTTPS ? "; secure" : "") . "; HttpOnly; SameSite=lax", false);
}
function
get_settings($qb)
{
    parse_str($_COOKIE[$qb], $bh);
    return $bh;
}
function
get_setting($y, $qb = "adminer_settings")
{
    $bh = get_settings($qb);
    return $bh[$y];
}
function
save_settings(array $bh, $qb = "adminer_settings")
{
    $Y = http_build_query($bh + get_settings($qb));
    cookie($qb, $Y);
    $_COOKIE[$qb] = $Y;
}
function
restart_session()
{
    if (!ini_bool("session.use_cookies") && (!function_exists('session_status') || session_status() == 1)) session_start();
}
function
stop_session($Qc = false)
{
    $wi = ini_bool("session.use_cookies");
    if (!$wi || $Qc) {
        session_write_close();
        if ($wi && @ini_set("session.use_cookies", '0') === false) session_start();
    }
}
function &get_session($y)
{
    return $_SESSION[$y][DRIVER][SERVER][$_GET["username"]];
}
function
set_session($y, $X)
{
    $_SESSION[$y][DRIVER][SERVER][$_GET["username"]] = $X;
}
function
auth_url($Bi, $P, $V, $i = null)
{
    $si = remove_from_uri(implode("|", array_keys(SqlDriver::$drivers)) . "|username|ext|" . ($i !== null ? "db|" : "") . ($Bi == 'mssql' || $Bi == 'pgsql' ? "" : "ns|") . session_name());
    preg_match('~([^?]*)\??(.*)~', $si, $B);
    return "$B[1]?" . (sid() ? SID . "&" : "") . ($Bi != "server" || $P != "" ? urlencode($Bi) . "=" . urlencode($P) . "&" : "") . ($_GET["ext"] ? "ext=" . urlencode($_GET["ext"]) . "&" : "") . "username=" . urlencode($V) . ($i != "" ? "&db=" . urlencode($i) : "") . ($B[2] ? "&$B[2]" : "");
}
function
is_ajax()
{
    return ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest");
}
function
redirect($A, $C = null)
{
    if ($C !== null) {
        restart_session();
        $_SESSION["messages"][preg_replace('~^[^?]*~', '', ($A !== null ? $A : $_SERVER["REQUEST_URI"]))][] = $C;
    }
    if ($A !== null) {
        if ($A == "") $A = ".";
        header("Location: $A");
        exit;
    }
}
function
query_redirect($I, $A, $C, $vg = true, $xc = true, $Fc = false, $Oh = "")
{
    if ($xc) {
        $oh = microtime(true);
        $Fc = !connection()->query($I);
        $Oh = format_time($oh);
    }
    $jh = ($I ? adminer()->messageQuery($I, $Oh, $Fc) : "");
    if ($Fc) {
        adminer()->error
            .= error() . $jh . script("messagesPrint();") . "<br>";
        return
            false;
    }
    if ($vg) redirect($A, $C . $jh);
    return
        true;
}
class
Queries
{
    static $queries = array();
    static $start = 0;
}
function
queries($I)
{
    if (!Queries::$start) Queries::$start = microtime(true);
    Queries::$queries[] = (preg_match('~;$~', $I) ? "DELIMITER ;;\n$I;\nDELIMITER " : $I) . ";";
    return
        connection()->query($I);
}
function
apply_queries($I, array $T, $tc = 'Adminer\table')
{
    foreach (
        $T
        as $R
    ) {
        if (!queries("$I " . $tc($R))) return
            false;
    }
    return
        true;
}
function
queries_redirect($A, $C, $vg)
{
    $qg = implode("\n", Queries::$queries);
    $Oh = format_time(Queries::$start);
    return
        query_redirect($qg, $A, $C, $vg, false, !$vg, $Oh);
}
function
format_time($oh)
{
    return
        sprintf('%.3f s', max(0, microtime(true) - $oh));
}
function
relative_uri()
{
    return
        str_replace(":", "%3a", preg_replace('~^[^?]*/([^?]*)~', '\1', $_SERVER["REQUEST_URI"]));
}
function
remove_from_uri($If = "")
{
    return
        substr(preg_replace("~(?<=[?&])($If" . (SID ? "" : "|" . session_name()) . ")=[^&]*&~", '', relative_uri() . "&"), 0, -1);
}
function
get_file($y, $Fb = false, $Lb = "")
{
    $Kc = $_FILES[$y];
    if (!$Kc) return
        null;
    foreach (
        $Kc
        as $y => $X
    ) $Kc[$y] = (array)$X;
    $K = '';
    foreach ($Kc["error"] as $y => $k) {
        if ($k) return $k;
        $D = $Kc["name"][$y];
        $Wh = $Kc["tmp_name"][$y];
        $mb = file_get_contents($Fb && preg_match('~\.gz$~', $D) ? "compress.zlib://$Wh" : $Wh);
        if ($Fb) {
            $oh = substr($mb, 0, 3);
            if (function_exists("iconv") && preg_match("~^\xFE\xFF|^\xFF\xFE~", $oh)) $mb = iconv("utf-16", "utf-8", $mb);
            elseif ($oh == "\xEF\xBB\xBF") $mb = substr($mb, 3);
        }
        $K
            .= $mb;
        if ($Lb) $K
            .= (preg_match("($Lb\\s*\$)", $mb) ? "" : $Lb) . "\n\n";
    }
    return $K;
}
function
upload_error($k)
{
    $Ce = ($k == UPLOAD_ERR_INI_SIZE ? ini_get("upload_max_filesize") : 0);
    return ($k ? 'Unable to upload a file.' . ($Ce ? " " . sprintf('Maximum allowed file size is %sB.', $Ce) : "") : 'File does not exist.');
}
function
repeat_pattern($Uf, $oe)
{
    return
        str_repeat("$Uf{0,65535}", $oe / 65535) . "$Uf{0," . ($oe % 65535) . "}";
}
function
is_utf8($X)
{
    return (preg_match('~~u', $X) && !preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $X));
}
function
format_number($X)
{
    return
        strtr(number_format($X, 0, ".", ','), preg_split('~~u', '0123456789', -1, PREG_SPLIT_NO_EMPTY));
}
function
friendly_url($X)
{
    return
        preg_replace('~\W~i', '-', $X);
}
function
table_status1($R, $Gc = false)
{
    $K = table_status($R, $Gc);
    return ($K ? reset($K) : array("Name" => $R));
}
function
column_foreign_keys($R)
{
    $K = array();
    foreach (adminer()->foreignKeys($R) as $o) {
        foreach ($o["source"] as $X) $K[$X][] = $o;
    }
    return $K;
}
function
fields_from_edit()
{
    $K = array();
    foreach ((array)$_POST["field_keys"] as $y => $X) {
        if ($X != "") {
            $X = bracket_escape($X);
            $_POST["function"][$X] = $_POST["field_funs"][$y];
            $_POST["fields"][$X] = $_POST["field_vals"][$y];
        }
    }
    foreach ((array)$_POST["fields"] as $y => $X) {
        $D = bracket_escape($y, true);
        $K[$D] = array("field" => $D, "privileges" => array("insert" => 1, "update" => 1, "where" => 1, "order" => 1), "null" => 1, "auto_increment" => ($y == driver()->primary),);
    }
    return $K;
}
function
dump_headers($xd, $Qe = false)
{
    $K = adminer()->dumpHeaders($xd, $Qe);
    $Ff = $_POST["output"];
    if ($Ff != "text") header("Content-Disposition: attachment; filename=" . adminer()->dumpFilename($xd) . ".$K" . ($Ff != "file" && preg_match('~^[0-9a-z]+$~', $Ff) ? ".$Ff" : ""));
    session_write_close();
    if (!ob_get_level()) ob_start(null, 4096);
    ob_flush();
    flush();
    return $K;
}
function
dump_csv(array $L)
{
    foreach (
        $L
        as $y => $X
    ) {
        if (preg_match('~["\n,;\t]|^0|\.\d*0$~', $X) || $X === "") $L[$y] = '"' . str_replace('"', '""', $X) . '"';
    }
    echo
    implode(($_POST["format"] == "csv" ? "," : ($_POST["format"] == "tsv" ? "\t" : ";")), $L) . "\r\n";
}
function
apply_sql_function($q, $c)
{
    return ($q ? ($q == "unixepoch" ? "DATETIME($c, '$q')" : ($q == "count distinct" ? "COUNT(DISTINCT " : strtoupper("$q(")) . "$c)") : $c);
}
function
get_temp_dir()
{
    $K = ini_get("upload_tmp_dir");
    if (!$K) {
        if (function_exists('sys_get_temp_dir')) $K = sys_get_temp_dir();
        else {
            $n = @tempnam("", "");
            if (!$n) return '';
            $K = dirname($n);
            unlink($n);
        }
    }
    return $K;
}
function
file_open_lock($n)
{
    if (is_link($n)) return;
    $p = @fopen($n, "c+");
    if (!$p) return;
    chmod($n, 0660);
    if (!flock($p, LOCK_EX)) {
        fclose($p);
        return;
    }
    return $p;
}
function
file_write_unlock($p, $_b)
{
    rewind($p);
    fwrite($p, $_b);
    ftruncate($p, strlen($_b));
    file_unlock($p);
}
function
file_unlock($p)
{
    flock($p, LOCK_UN);
    fclose($p);
}
function
first(array $ra)
{
    return
        reset($ra);
}
function
password_file($g)
{
    $n = get_temp_dir() . "/adminer.key";
    if (!$g && !file_exists($n)) return '';
    $p = file_open_lock($n);
    if (!$p) return '';
    $K = stream_get_contents($p);
    if (!$K) {
        $K = rand_string();
        file_write_unlock($p, $K);
    } else
        file_unlock($p);
    return $K;
}
function
rand_string()
{
    return
        md5(uniqid(strval(mt_rand()), true));
}
function
select_value($X, $_, array $l, $Nh)
{
    if (is_array($X)) {
        $K = "";
        foreach (
            $X
            as $be => $W
        ) $K
            .= "<tr>" . ($X != array_values($X) ? "<th>" . h($be) : "") . "<td>" . select_value($W, $_, $l, $Nh);
        return "<table>$K</table>";
    }
    if (!$_) $_ = adminer()->selectLink($X, $l);
    if ($_ === null) {
        if (is_mail($X)) $_ = "mailto:$X";
        if (is_url($X)) $_ = $X;
    }
    $K = adminer()->editVal($X, $l);
    if ($K !== null) {
        if (!is_utf8($K)) $K = "\0";
        elseif ($Nh != "" && is_shortable($l)) $K = shorten_utf8($K, max(0, +$Nh));
        else $K = h($K);
    }
    return
        adminer()->selectVal($K, $_, $l, $X);
}
function
is_mail($gc)
{
    $ta = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
    $Ub = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    $Uf = "$ta+(\\.$ta+)*@($Ub?\\.)+$Ub";
    return
        is_string($gc) && preg_match("(^$Uf(,\\s*$Uf)*\$)i", $gc);
}
function
is_url($sh)
{
    $Ub = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    return
        preg_match("~^(https?)://($Ub?\\.)+$Ub(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $sh);
}
function
is_shortable(array $l)
{
    return
        preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~', $l["type"]);
}
function
count_rows($R, array $Z, $Vd, array $r)
{
    $I = " FROM " . table($R) . ($Z ? " WHERE " . implode(" AND ", $Z) : "");
    return ($Vd && (JUSH == "sql" || count($r) == 1) ? "SELECT COUNT(DISTINCT " . implode(", ", $r) . ")$I" : "SELECT COUNT(*)" . ($Vd ? " FROM (SELECT 1$I GROUP BY " . implode(", ", $r) . ") x" : $I));
}
function
slow_query($I)
{
    $i = adminer()->database();
    $Ph = adminer()->queryTimeout();
    $fh = driver()->slowQuery($I, $Ph);
    $f = null;
    if (!$fh && support("kill")) {
        $f = connect();
        if ($f && ($i == "" || $f->select_db($i))) {
            $de = get_val(connection_id(), 0, $f);
            echo
            script("const timeout = setTimeout(() => { ajax('" . js_escape(ME) . "script=kill', function () {}, 'kill=$de&token=" . get_token() . "'); }, 1000 * $Ph);");
        }
    }
    ob_flush();
    flush();
    $K = @get_key_vals(($fh ?: $I), $f, false);
    if ($f) {
        echo
        script("clearTimeout(timeout);");
        ob_flush();
        flush();
    }
    return $K;
}
function
get_token()
{
    $tg = rand(1, 1e6);
    return ($tg ^ $_SESSION["token"]) . ":$tg";
}
function
verify_token()
{
    list($Xh, $tg) = explode(":", $_POST["token"]);
    return ($tg ^ $_SESSION["token"]) == $Xh;
}
function
lzw_decompress($Ea)
{
    $Qb = 256;
    $Fa = 8;
    $Ya = array();
    $Eg = 0;
    $Fg = 0;
    for ($s = 0; $s < strlen($Ea); $s++) {
        $Eg = ($Eg << 8) + ord($Ea[$s]);
        $Fg += 8;
        if ($Fg >= $Fa) {
            $Fg -= $Fa;
            $Ya[] = $Eg >> $Fg;
            $Eg &= (1 << $Fg) - 1;
            $Qb++;
            if ($Qb >> $Fa) $Fa++;
        }
    }
    $Pb = range("\0", "\xFF");
    $K = "";
    $Li = "";
    foreach (
        $Ya
        as $s => $Xa
    ) {
        $fc = $Pb[$Xa];
        if (!isset($fc)) $fc = $Li . $Li[0];
        $K
            .= $fc;
        if ($s) $Pb[] = $Li . $fc[0];
        $Li = $fc;
    }
    return $K;
}
function
script($hh, $ai = "\n")
{
    return "<script" . nonce() . ">$hh</script>$ai";
}
function
script_src($ti, $Ib = false)
{
    return "<script src='" . h($ti) . "'" . nonce() . ($Ib ? " defer" : "") . "></script>\n";
}
function
nonce()
{
    return ' nonce="' . get_nonce() . '"';
}
function
input_hidden($D, $Y = "")
{
    return "<input type='hidden' name='" . h($D) . "' value='" . h($Y) . "'>\n";
}
function
input_token()
{
    return
        input_hidden("token", get_token());
}
function
target_blank()
{
    return ' target="_blank" rel="noreferrer noopener"';
}
function
h($sh)
{
    return
        str_replace("\0", "&#0;", htmlspecialchars($sh, ENT_QUOTES, 'utf-8'));
}
function
nl_br($sh)
{
    return
        str_replace("\n", "<br>", $sh);
}
function
checkbox($D, $Y, $Sa, $fe = "", $of = "", $Wa = "", $he = "")
{
    $K = "<input type='checkbox' name='$D' value='" . h($Y) . "'" . ($Sa ? " checked" : "") . ($he ? " aria-labelledby='$he'" : "") . ">" . ($of ? script("qsl('input').onclick = function () { $of };", "") : "");
    return ($fe != "" || $Wa ? "<label" . ($Wa ? " class='$Wa'" : "") . ">$K" . h($fe) . "</label>" : $K);
}
function
optionlist($sf, $Sg = null, $xi = false)
{
    $K = "";
    foreach (
        $sf
        as $be => $W
    ) {
        $tf = array($be => $W);
        if (is_array($W)) {
            $K
                .= '<optgroup label="' . h($be) . '">';
            $tf = $W;
        }
        foreach (
            $tf
            as $y => $X
        ) $K
            .= '<option' . ($xi || is_string($y) ? ' value="' . h($y) . '"' : '') . ($Sg !== null && ($xi || is_string($y) ? (string)$y : $X) === $Sg ? ' selected' : '') . '>' . h($X);
        if (is_array($W)) $K
            .= '</optgroup>';
    }
    return $K;
}
function
html_select($D, array $sf, $Y = "", $nf = "", $he = "")
{
    static $fe = 0;
    $ge = "";
    if (!$he && substr($sf[""], 0, 1) == "(") {
        $fe++;
        $he = "label-$fe";
        $ge = "<option value='' id='$he'>" . h($sf[""]);
        unset($sf[""]);
    }
    return "<select name='" . h($D) . "'" . ($he ? " aria-labelledby='$he'" : "") . ">" . $ge . optionlist($sf, $Y) . "</select>" . ($nf ? script("qsl('select').onchange = function () { $nf };", "") : "");
}
function
html_radios($D, array $sf, $Y = "", $Wg = "")
{
    $K = "";
    foreach (
        $sf
        as $y => $X
    ) $K
        .= "<label><input type='radio' name='" . h($D) . "' value='" . h($y) . "'" . ($y == $Y ? " checked" : "") . ">" . h($X) . "</label>$Wg";
    return $K;
}
function
confirm($C = "", $Tg = "qsl('input')")
{
    return
        script("$Tg.onclick = () => confirm('" . ($C ? js_escape($C) : 'Are you sure?') . "');", "");
}
function
print_fieldset($t, $ne, $Fi = false)
{
    echo "<fieldset><legend>", "<a href='#fieldset-$t'>$ne</a>", script("qsl('a').onclick = partial(toggle, 'fieldset-$t');", ""), "</legend>", "<div id='fieldset-$t'" . ($Fi ? "" : " class='hidden'") . ">\n";
}
function
bold($Ha, $Wa = "")
{
    return ($Ha ? " class='active $Wa'" : ($Wa ? " class='$Wa'" : ""));
}
function
js_escape($sh)
{
    return
        addcslashes($sh, "\r\n'\\/");
}
function
pagination($E, $xb)
{
    return " " . ($E == $xb ? $E + 1 : '<a href="' . h(remove_from_uri("page") . ($E ? "&page=$E" . ($_GET["next"] ? "&next=" . urlencode($_GET["next"]) : "") : "")) . '">' . ($E + 1) . "</a>");
}
function
hidden_fields(array $ng, array $_d = array(), $gg = '')
{
    $K = false;
    foreach (
        $ng
        as $y => $X
    ) {
        if (!in_array($y, $_d)) {
            if (is_array($X)) hidden_fields($X, array(), $y);
            else {
                $K = true;
                echo
                input_hidden(($gg ? $gg . "[$y]" : $y), $X);
            }
        }
    }
    return $K;
}
function
hidden_fields_get()
{
    echo (sid() ? input_hidden(session_name(), session_id()) : ''), (SERVER !== null ? input_hidden(DRIVER, SERVER) : ""), input_hidden("username", $_GET["username"]);
}
function
enum_input($U, $ua, array $l, $Y, $jc = null)
{
    preg_match_all("~'((?:[^']|'')*)'~", $l["length"], $ye);
    $K = ($jc !== null ? "<label><input type='$U'$ua value='$jc'" . ((is_array($Y) ? in_array($jc, $Y) : $Y === $jc) ? " checked" : "") . "><i>" . 'empty' . "</i></label>" : "");
    foreach ($ye[1] as $s => $X) {
        $X = stripcslashes(str_replace("''", "'", $X));
        $Sa = (is_array($Y) ? in_array($X, $Y) : $Y === $X);
        $K
            .= " <label><input type='$U'$ua value='" . h($X) . "'" . ($Sa ? ' checked' : '') . '>' . h(adminer()->editVal($X, $l)) . '</label>';
    }
    return $K;
}
function
input(array $l, $Y, $q, $ya = false)
{
    $D = h(bracket_escape($l["field"]));
    echo "<td class='function'>";
    if (is_array($Y) && !$q) {
        $Y = json_encode($Y, 128 | 64 | 256);
        $q = "json";
    }
    $Dg = (JUSH == "mssql" && $l["auto_increment"]);
    if ($Dg && !$_POST["save"]) $q = null;
    $Zc = (isset($_GET["select"]) || $Dg ? array("orig" => 'original') : array()) + adminer()->editFunctions($l);
    $Rb = stripos($l["default"], "GENERATED ALWAYS AS ") === 0 ? " disabled=''" : "";
    $ua = " name='fields[$D]'$Rb" . ($ya ? " autofocus" : "");
    $pc = driver()->enumLength($l);
    if ($pc) {
        $l["type"] = "enum";
        $l["length"] = $pc;
    }
    echo
    driver()->unconvertFunction($l) . " ";
    $R = $_GET["edit"] ?: $_GET["select"];
    if ($l["type"] == "enum") echo
    h($Zc[""]) . "<td>" . adminer()->editInput($R, $l, $ua, $Y);
    else {
        $ld = (in_array($q, $Zc) || isset($Zc[$q]));
        echo (count($Zc) > 1 ? "<select name='function[$D]'$Rb>" . optionlist($Zc, $q === null || $ld ? $q : "") . "</select>" . on_help("event.target.value.replace(/^SQL\$/, '')", 1) . script("qsl('select').onchange = functionChange;", "") : h(reset($Zc))) . '<td>';
        $Nd = adminer()->editInput($R, $l, $ua, $Y);
        if ($Nd != "") echo $Nd;
        elseif (preg_match('~bool~', $l["type"])) echo "<input type='hidden'$ua value='0'>" . "<input type='checkbox'" . (preg_match('~^(1|t|true|y|yes|on)$~i', $Y) ? " checked='checked'" : "") . "$ua value='1'>";
        elseif ($l["type"] == "set") {
            preg_match_all("~'((?:[^']|'')*)'~", $l["length"], $ye);
            foreach ($ye[1] as $s => $X) {
                $X = stripcslashes(str_replace("''", "'", $X));
                $Sa = in_array($X, explode(",", $Y), true);
                echo " <label><input type='checkbox' name='fields[$D][$s]' value='" . h($X) . "'" . ($Sa ? ' checked' : '') . ">" . h(adminer()->editVal($X, $l)) . '</label>';
            }
        } elseif (preg_match('~blob|bytea|raw|file~', $l["type"]) && ini_bool("file_uploads")) echo "<input type='file' name='fields-$D'>";
        elseif ($q == "json" || preg_match('~^jsonb?$~', $l["type"])) echo "<textarea$ua cols='50' rows='12' class='jush-js'>" . h($Y) . '</textarea>';
        elseif (($Mh = preg_match('~text|lob|memo~i', $l["type"])) || preg_match("~\n~", $Y)) {
            if ($Mh && JUSH != "sqlite") $ua
                .= " cols='50' rows='12'";
            else {
                $M = min(12, substr_count($Y, "\n") + 1);
                $ua
                    .= " cols='30' rows='$M'";
            }
            echo "<textarea$ua>" . h($Y) . '</textarea>';
        } else {
            $ji = driver()->types();
            $Ee = (!preg_match('~int~', $l["type"]) && preg_match('~^(\d+)(,(\d+))?$~', $l["length"], $B) ? ((preg_match("~binary~", $l["type"]) ? 2 : 1) * $B[1] + ($B[3] ? 1 : 0) + ($B[2] && !$l["unsigned"] ? 1 : 0)) : ($ji[$l["type"]] ? $ji[$l["type"]] + ($l["unsigned"] ? 0 : 1) : 0));
            if (JUSH == 'sql' && min_version(5.6) && preg_match('~time~', $l["type"])) $Ee += 7;
            echo "<input" . ((!$ld || $q === "") && preg_match('~(?<!o)int(?!er)~', $l["type"]) && !preg_match('~\[\]~', $l["full_type"]) ? " type='number'" : "") . " value='" . h($Y) . "'" . ($Ee ? " data-maxlength='$Ee'" : "") . (preg_match('~char|binary~', $l["type"]) && $Ee > 20 ? " size='" . ($Ee > 99 ? 60 : 40) . "'" : "") . "$ua>";
        }
        echo
        adminer()->editHint($R, $l, $Y);
        $Mc = 0;
        foreach (
            $Zc
            as $y => $X
        ) {
            if ($y === "" || !$X) break;
            $Mc++;
        }
        if ($Mc && count($Zc) > 1) echo
        script("qsl('td').oninput = partial(skipOriginal, $Mc);");
    }
}
function
process_input(array $l)
{
    if (stripos($l["default"], "GENERATED ALWAYS AS ") === 0) return;
    $u = bracket_escape($l["field"]);
    $q = idx($_POST["function"], $u);
    $Y = $_POST["fields"][$u];
    if ($l["type"] == "enum" || driver()->enumLength($l)) {
        if ($Y == -1) return
            false;
        if ($Y == "") return "NULL";
    }
    if ($l["auto_increment"] && $Y == "") return
        null;
    if ($q == "orig") return (preg_match('~^CURRENT_TIMESTAMP~i', $l["on_update"]) ? idf_escape($l["field"]) : false);
    if ($q == "NULL") return "NULL";
    if ($l["type"] == "set") $Y = implode(",", (array)$Y);
    if ($q == "json") {
        $q = "";
        $Y = json_decode($Y, true);
        if (!is_array($Y)) return
            false;
        return $Y;
    }
    if (preg_match('~blob|bytea|raw|file~', $l["type"]) && ini_bool("file_uploads")) {
        $Kc = get_file("fields-$u");
        if (!is_string($Kc)) return
            false;
        return
            driver()->quoteBinary($Kc);
    }
    return
        adminer()->processInput($l, $Y, $q);
}
function
search_tables()
{
    $_GET["where"][0]["val"] = $_POST["query"];
    $Vg = "<ul>\n";
    foreach (table_status('', true) as $R => $S) {
        $D = adminer()->tableName($S);
        if (isset($S["Engine"]) && $D != "" && (!$_POST["tables"] || in_array($R, $_POST["tables"]))) {
            $J = connection()->query("SELECT" . limit("1 FROM " . table($R), " WHERE " . implode(" AND ", adminer()->selectSearchProcess(fields($R), array())), 1));
            if (!$J || $J->fetch_row()) {
                $jg = "<a href='" . h(ME . "select=" . urlencode($R) . "&where[0][op]=" . urlencode($_GET["where"][0]["op"]) . "&where[0][val]=" . urlencode($_GET["where"][0]["val"])) . "'>$D</a>";
                echo "$Vg<li>" . ($J ? $jg : "<p class='error'>$jg: " . error()) . "\n";
                $Vg = "";
            }
        }
    }
    echo ($Vg ? "<p class='message'>" . 'No tables.' : "</ul>") . "\n";
}
function
on_help($db, $dh = 0)
{
    return
        script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $db, $dh) }, onmouseout: helpMouseout});", "");
}
function
edit_form($R, array $m, $L, $ri, $k = '')
{
    $Ah = adminer()->tableName(table_status1($R, true));
    page_header(($ri ? 'Edit' : 'Insert'), $k, array("select" => array($R, $Ah)), $Ah);
    adminer()->editRowPrint($R, $m, $L, $ri);
    if ($L === false) {
        echo "<p class='error'>" . 'No rows.' . "\n";
        return;
    }
    echo "<form action='' method='post' enctype='multipart/form-data' id='form'>\n";
    if (!$m) echo "<p class='error'>" . 'You have no privileges to update this table.' . "\n";
    else {
        echo "<table class='layout'>" . script("qsl('table').onkeydown = editingKeydown;");
        $ya = !$_POST;
        foreach (
            $m
            as $D => $l
        ) {
            echo "<tr><th>" . adminer()->fieldName($l);
            $j = idx($_GET["set"], bracket_escape($D));
            if ($j === null) {
                $j = $l["default"];
                if ($l["type"] == "bit" && preg_match("~^b'([01]*)'\$~", $j, $Bg)) $j = $Bg[1];
                if (JUSH == "sql" && preg_match('~binary~', $l["type"])) $j = bin2hex($j);
            }
            $Y = ($L !== null ? ($L[$D] != "" && JUSH == "sql" && preg_match("~enum|set~", $l["type"]) && is_array($L[$D]) ? implode(",", $L[$D]) : (is_bool($L[$D]) ? +$L[$D] : $L[$D])) : (!$ri && $l["auto_increment"] ? "" : (isset($_GET["select"]) ? false : $j)));
            if (!$_POST["save"] && is_string($Y)) $Y = adminer()->editVal($Y, $l);
            $q = ($_POST["save"] ? idx($_POST["function"], $D, "") : ($ri && preg_match('~^CURRENT_TIMESTAMP~i', $l["on_update"]) ? "now" : ($Y === false ? null : ($Y !== null ? '' : 'NULL'))));
            if (!$_POST && !$ri && $Y == $l["default"] && preg_match('~^[\w.]+\(~', $Y)) $q = "SQL";
            if (preg_match("~time~", $l["type"]) && preg_match('~^CURRENT_TIMESTAMP~i', $Y)) {
                $Y = "";
                $q = "now";
            }
            if ($l["type"] == "uuid" && $Y == "uuid()") {
                $Y = "";
                $q = "uuid";
            }
            if ($ya !== false) $ya = ($l["auto_increment"] || $q == "now" || $q == "uuid" ? null : true);
            input($l, $Y, $q, $ya);
            if ($ya) $ya = false;
            echo "\n";
        }
        if (!support("table") && !fields($R)) echo "<tr>" . "<th><input name='field_keys[]'>" . script("qsl('input').oninput = fieldChange;") . "<td class='function'>" . html_select("field_funs[]", adminer()->editFunctions(array("null" => isset($_GET["select"])))) . "<td><input name='field_vals[]'>" . "\n";
        echo "</table>\n";
    }
    echo "<p>\n";
    if ($m) {
        echo "<input type='submit' value='" . 'Save' . "'>\n";
        if (!isset($_GET["select"])) echo "<input type='submit' name='insert' value='" . ($ri ? 'Save and continue edit' : 'Save and insert next') . "' title='Ctrl+Shift+Enter'>\n", ($ri ? script("qsl('input').onclick = function () { return !ajaxForm(this.form, '" . 'Saving' . "…', this); };") : "");
    }
    echo ($ri ? "<input type='submit' name='delete' value='" . 'Delete' . "'>" . confirm() . "\n" : "");
    if (isset($_GET["select"])) hidden_fields(array("check" => (array)$_POST["check"], "clone" => $_POST["clone"], "all" => $_POST["all"]));
    echo
    input_hidden("referer", (isset($_POST["referer"]) ? $_POST["referer"] : $_SERVER["HTTP_REFERER"])), input_hidden("save", 1), input_token(), "</form>\n";
}
function
shorten_utf8($sh, $oe = 80, $wh = "")
{
    if (!preg_match("(^(" . repeat_pattern("[\t\r\n -\x{10FFFF}]", $oe) . ")($)?)u", $sh, $B)) preg_match("(^(" . repeat_pattern("[\t\r\n -~]", $oe) . ")($)?)", $sh, $B);
    return
        h($B[1]) . $wh . (isset($B[2]) ? "" : "<i>…</i>");
}
function
icon($wd, $D, $vd, $Rh)
{
    return "<button type='submit' name='$D' title='" . h($Rh) . "' class='icon icon-$wd'><span>$vd</span></button>";
}
if (isset($_GET["file"])) {
    if (substr(VERSION, -4) != '-dev') {
        if ($_SERVER["HTTP_IF_MODIFIED_SINCE"]) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 365 * 24 * 60 * 60) . " GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: immutable");
    }
    @ini_set("zlib.output_compression", '1');
    if ($_GET["file"] == "default.css") {
        header("Content-Type: text/css; charset=utf-8");
        echo
        lzw_decompress("h:M��h��g�б���\"P�i��m��cQCa��	2ó��d<��f�a��:;NB�q�R;1Lf�9��u7&)�l;3�����J/��CQX�r2M�a�i0���)��e:LuÝh�-9��23l��i7��m�Zw4���њ<-��̴�!�U,��Fé�vt2��S,��a�҇F�VX�a�Nq�)�-���ǜh�:n5���9�Y�;j��-��_�9kr��ٓ;.�tTq�o�0�����{��y��\r�Hn��GS��Zh��;�i^�ux�WΒC@����k��=��b����/A��0�+�(���l���\\��x�:\r��b8\0�0!\0F�\nB�͎�(�3�\r\\�����Ȅa���'I�|�(i�\n�\r���4O�g@�4�C��@@�!�QB��	°�c��¯�q,\r1Eh��&2PZ���iG�H9G�\"v���������4r����D�R�\n�pJ�-A�|/.�c�Du�����:,��=��R�]U5�mV�k�LLQ@-\\����@9��%�S�r���MPD��Ia\r�(YY\\�@X�p��:��p�l�LC �������O,\r�2]7�?m06�p�T��aҥC�;_˗�yȴd�>��bn���n�ܣ3�X���8\r�[ˀ-)�i>V[Y�y&L3�#�X|�	�X�\\ù`�C���#��H��2�2.#���Z�`�<��s����Ò��\0u�h־��M��_\niZeO/CӒ_�`3���1>�=��k3����R/;�/d��\0�����ڵm���7/���A�X�������q.�s�L��� :\$�F�������w�8�߾~�H�j��\"�����Գ7gS���FL�ί�Q�_��O'W��]c=�5�1X~7;��i��\r�*\n��JS1Z���������c���t��A�V�86f�d�y;Y�]��zI�p�����c�3�Y�]}@�\$.+�1�'>Z�cpd���GL��#k�8Pz�Y�Au�v�]s9���_Aq���:���\nK�hB�;���XbAHq,��CI�`����j�S[ˌ�1�V�r���;�p�B��)#鐉;4�H��/*�<�3L��;lf�\n�s\$K`�}��Ք���7�jx`d�%j]��4��Y��HbY��J`�GG��.��K��f�I�)2�Mfָ�X�RC��̱V,���~g\0���g6�:�[j�1H�:AlIq�u3\"���q��|8<9s'�Q]J�|�\0�`p���jf�O�b�����q��\$����1J�>R�H(ǔq\n#r����@�e(y�VJ�0�Q҈��6�P�[C:�G伞���4���^����PZ��\\���(\n��)�~���9R%�Sj�{��7�0�_��s	z|8�H�	\"@�#9DVL�\$H5�WJ@��z�a�J �^	�)�2\nQv��]�������j (A���BB05�6�b˰][��k�A�wvkg�ƴ���+k[jm�zc�}�MyDZi�\$5e��ʷ���	�A��CY%.W�b*뮼�.���q/%}B�X���ZV337�ʻa�������wW[�L�Q�޲�_��2`�1I�i,�曣�Mf&(s-����Aİ�*��Dw��TN�ɻ�jX\$�x�+;���F�93�JkS;���qR{>l�;B1A�I�b)��(6��r�\r�\rڇ����Z�R^SOy/��M#��9{k���v\"�KC�J��rEo\0��\\,�|�fa͚��hI��/o�4�k^p�1H�^����phǡV�vox@�`�g�&�(����;��~Ǎz�6�8�*���5����E���p����Ә���3��ņg��rD�L�)4g{���峩�L��&�>脻����Z�7�\0��̊@�����ff�RVh֝��I�ۈ���r�w)����=x^�,k��2��ݓj�b�l0u�\"�fp��1�RI��z[]�w�pN6dI�z���n.7X{;��3��-I	����7pjÝ�R�#�,�_-���[�>3�\\���Wq�q�J֘�uh���FbL�K���yVľ����ѕ�����V���f{K}S��ޝ��M���̀��.M�\\�ix�b���1�+�α?<�3�~H��\$�\\�2�\$� e�6t�Ö�\$s���x��x���C�nSkV��=z6����'æ�Na��ָh��������R�噣8g�����w:_�����ҒIRKÝ�.�nkVU+dwj��%�`#,{�醳����Y����(oվ��.�c�0g�DXOk�7��K��l��hx;�؏ ݃L��\$09*�9 �hNr�M�.>\0�rP9�\$�g	\0\$\\F�*�d'��L�:�b���4�2����9��@�Hnb�-��E #Ĝ����rPY�� t� �\n�5.�����\$op�l�X\n@`\r��	��\r���� � ���	������ �	@�@�\n � �	\0j@�Q@�1\r��@� �	\$p	 V\0�``\n\0�\n �\n@�'����\n\0`\r����	��\r���\0�r����	\0�`�	���{	,�\"��^P�0�\n��4�\n0���.0�p���\rp�\r��p���p��q�Q0�%���1Q8\n �\0�k�ȼ\0^���\0`��@���>\n�o1w�,Y	h*=����P�:іV��и.q����\r�\r�p���1��Q	��1� �`��/17����\r�^��\"y`�\n�� �#��\0�	 p\n��\n��`� �r �Q��b�1��3\n��#��#�1�\$q�\$ѱ%0�%q�%��&�&q� �&�'1�\rR}16	 �@b\r`�`�\r��	�����d���	j\n�``��\n��`dcсP��,�1R��\$�rI�O �	Q	�Y32b1�&��01��� �� f��\0�\0���f�\0j\n�f`�	 �\n`�@�\$n=`�\0��v nI�\$�P(�d'�����g�6��-��-�C7R��� �	4��-1�&��2t\r�\"\n 	H*@�	�`\n � �	��l�2�,z\r�~� �\r�F�th�������m����z�~�\0]G�F\\��I�\\��}It�C\n�T�}���IEJ\rx����>�Mp��IH�~��fht��.b��xYE��iK��oj�\n���L��tr�.�~d�H�2U4�G�\\A��4��uPt����谐����L/�P�	\"G!R��Mt�O-��<#�APuI��R�\$�c���D�Ɗ����-��G�O`Pv�^W@tH;Q��Rę�\$��gK�F<\rR*\$4���'�����[���I��Um��h:+��5@/�l�I���2���^�\0OD�����\rR'�\r�TЭ[����Ī��MC�M�Z4�E B\"�`���euN�,䙬�]��t�\r�`�@h��*\r�.V��%�!MBlPF��\"��&�/@�v\\C��:mMgn����i8�I2\rp�vj�����+Z mT�ue��fv>f��C�T�M.�M�3Pv'kt�dO\rd��k���yW��߂��V6�U�ʖ����-~X��������BGd�\$i��%�qj�E��rLJ�Pr�%n=��H�\"\"\"h��_\$b@�t��\0f\"��n�H�*��Bv\$\$�B��@\"@r��(\r`� �C�X�(0&�.`�Nk9B\n&#(���@䂯��d��^�Z��� �@�`�I-u�0��\n�B�u�4sGu���tN�b�u �ub}�O~)uB��w�{��Ł5=w9[ɫs��	8=�\0\r��%`�]x&^�3sc݃.\$�̓�Z4�4��u`���Ǆ�ۅ �8;�wT�M��e��х�݇8X��zl�K�U��`�������^XO��m�@M��⋀W��X���j��߈ؽ���XG��T�E����H��x�Y��a�,�Í��Ŋ�o|��t�%��U�j���q�7w�X�=ػ�d��xU�8�\r��O������������iy��c��.&Y%�9A��k���d�Dz�9D�ċģ[���Y��<�x��X^V�ς��DG��g�wǏ����C����I�`Ƨ��=ٗzYO� 6�|x��)���ߋ8#�ل�e��ٙ8Q�x�Y��������ߛ&K�����͝��(��%�r-��ٝ��J���� @��Z ��x�����Dz!�Wǝ�ۣy���� \0�rYy|b�|�!�Y��y���9���yzC+���=�٦�ç:]�s����w���a���x��f*�qz�ӧ`[�#�s�y���+u�XT���ٌ�c����࿎C1�8U�9��5�ݭǖ`��ͬ������Z�yg�z�� ڧ�����࿰������#�e˱Z��+��Ր�p>ǡ�L)����ű�O��+�I���x�R ˯��yB�y����I{z\0\r�ᜄ�{k�^��=������:��Γ��A�2;������7��\$�;c�������;���]XX��kY�#�5vT\\�Q��:��>��ɓ�k��'����[��a�ħ�0�xI�[���;��\0[���A�Z���e���?�ec��p΄�Ճ��:�#�fCZ��SDv��.\r��#�ߔ�U ");
    } elseif ($_GET["file"] == "dark.css") {
        header("Content-Type: text/css; charset=utf-8");
        echo
        lzw_decompress("h:M��h��g���h0�LЁ�d91�S!��	�F�!��\"-6N����bd�Gg���:;Nr�)��c7�\r�(H�b81��s9���k\r�c)�m8�O��VA��c1��c34Of*��-�P��1��r41��6��d2�ց���o���#3���B�f#	��g9Φ�،fc\r�I���b6E�C&��,�bu��m7a�V���s��#m!��h��r���v\\3\rL:SA��dk5�n������aF��3��e6fS��y���r!�L��-�K,�3L�@��J��˲�*J��쵣����	������b�c��9���9���@����H�8��\\���6>�`�Ŏ��;�A��<T�'�p&q�qE��4�\rl���h�<5#p��R �#I��%��fBI��ܲ��>�ʫ29<��C�j2��7j��8j��c(n���?(a\0�@�5*3:δ�6����0��-�A�lL��P�4@�ɰ�\$�H�4�n31��1�t�0��͙9���WO!�r��������H����9�Q��96�F���<�7�\r�-xC\n ��@�������:\$i�ضm���4�Kid��{\n6\r���xhˋ�#^'4V�@a��<�#h0�S�-�c��9�+p���a�2�cy�h�BO\$��9�w�iX�ɔ�VY9�*r�Htm	�@b��|@�/��l�\$z���+�%p2l���.�������7�;�&{��m��X�C<l9��6x9�m�������7R��0\\�4��P�)A�o��x���q�O#����f[;��6~P�\r�a��T�GT0���u�ޟ���\n3�\\ \\ʎ�J�ud�CG���PZ�>����d8�Ҩ������C?V��dL��L.(ti���>�,�֜�R+9i��ޞC\$��#\"�AC�hV�b\n��6�T2�ew�\nf��6m	!1'c��;��*eLRn\r�G\$�2S\$��0���a�'�l6�&�~A�d\$�J�\$s� �ȃB4���j�.�RC̔�Q�j�\"7\n�Xs!�6=�BȀ}");
    } elseif ($_GET["file"] == "functions.js") {
        header("Content-Type: text/javascript; charset=utf-8");
        echo
        lzw_decompress("':�̢���i1��1��	4������Q6a&��:OAI��e:NF�D|�!���Cy��m2��\"���r<�̱���/C�#����:DbqSe�J�˦Cܺ\n\n��ǱS\rZ��H\$RAܞS+XKvtd�g:��6��EvXŞ�j��mҩej�2�M�����B��&ʮ�L�C�3���Q0�L��-x�\n��D���yNa�Pn:�����s��͐�(�cL��/���(�5{���Qy4��g-�����i4ڃf��(��bU���k��o7�&�ä�*ACb����`.����\r����������\n��Ch�<\r)`�إ`�7�Cʒ���Z���X�<�Q�1X���@�0dp9EQ�f����F�\r��!���(h��)��\np'#Č��H�(i*�r��&<#��7K��~�# ��A:N6�����l�,�\r��JP�3�!@�2>Cr���h�N��]�(a0M3�2��6��U��E2'!<��#3R�<�����X���CH�7�#n�+��a\$!��2��P�0�.�wd�r:Y����E��!]�<��j��@�\\�pl�_\r�Z���ғ�TͩZ�s�3\"�~9���j��P�)Q�YbݕD�Yc��`��z�c��Ѩ��'�#t�BOh�*2��<ŒO�fg-Z����#��8a�^��+r2b��\\��~0�������W����n��p!#�`��Z��6�1�2��@�ky��9\r��B3�pޅ�6��<�!p�G�9�n�o�6s��#F�3���bA��6�9���Z�#��6��%?�s��\"��|؂�)�b�Jc\r����N�s��ih8����ݟ�:�;��H�ތ�u�I5�@�1��A�PaH^\$H�v��@ÛL~���b9�'�����S?P�-���0�C�\nR�m�4���ȓ:���Ը�2��4��h(k\njI��6\"�EY�#��W�r�\r��G8�@t���Xԓ��BS\nc0�k�C I\rʰ<u`A!�)��2��C�\0=��� ���P�1�ӢK!�!��p�Is�,6�d���i1+����k���<��^�	�\n��20�Fԉ_\$�)f\0��C8E^��/3W!א)�u�*���&\$�2�Y\n�]��Ek�DV�\$�J���xTse!�RY� R��`=L���ޫ\nl_.!�V!�\r\nH�k��\$א`{1	|�����i<jRrPTG|��w�4b�\r���4d�,�E��6���<�h[N�q@Oi�>'ѩ\r����;�]#��}�0�ASI�Jd�A/Q����⸵�@t\r�UG��_G�<��<y-I�z򄤝�\"�P��B\0������q`��vA��a̡J�R�ʮ)��JB.�T��L��y����Cpp�\0(7�cYY�a��M��1�em4�c��r��S)o����p�C!I���Sb�0m��(d�EH����߳�X���/���P���y�X��85��\$+�֖���gd�����y��ϝ�J��� �lE��ur�,dCX�}e������m�]��2�̽�(-z����Z��;I��\\�) ,�\n�>�)����\rVS\njx*w`ⴷSFi��d��,���Z�JFM}Њ ��\\Z�P��`�z�Z�E]�d��ɟO�cmԁ]� ������%�\"w4��\n\$��zV�SQD�:�6���G�wM��S0B�-s��)�Z��cǁ2��δA;��n�Wz/A�Zh�G~�c�c%�[�D�&l�FR�77|�I���3��g0�L���a��c�0RJ�2��%���F� S� �L�^� tr���t����ʩ;��.喚Ł�>����[�a�N���^�(!g�@1����N�z�<b�ݖ�����O,��Cu��D�tj޹I;)�݀�\nn�c��Ȃ�W<s�	�\0�hN�P�9��{ue��ut뵕������3��=��g�����J����WQ�0���w9p-���	�������'5��\nO��e)M�)_k�z\0V�����;j�l��\n����x�Pf�-�`C�.@&]#\0ڶp�y͖ƛ�t�d�� ��b}�	G1�m�ru���*�_�xD�3�q��B�sQ��u��s%�\n�5s�ut���{�s�y���N��4�,J{4@��\0��P���^��=��l���`�e~F١h3o�\"��q�R<iUT�[Q��U��M�6�T. ��0'�pe\\�����5����pCe	ٕ�\"*�M	����D���?�h��2���zU�@7�C�4�a��iE!f�\$�B��<�9o*\$��lH�\$ �@����P\rN�Y�n<\$�	�Q�=�F&��*@]\0��� W'd� z\$��j�P[��\$���0#&��_�`+�B)�w�v%	����LcJ��RS��i`�Ů	�F�W	��\nBP\n�\r\0}	瑩0�Z���/`j\$�: �8ie���φx�����a ���Gn�sgO��U%VU��@�N��ϐ�d+�(oJ�@X���zM'F٣�WhV�I^٢�1>�@�\"���� ��Q�R!�\\�`[������.�0fb�F;���Fp�p/t`����(��V���b�Ȳ�(��H�l����ԯ1v�����H��1T�3�q���1�Ѫf�\nT\$���Nq+��`ލv�ǜ�\r�Vm���r���'ϸ��g%�\"L�m����(�(CLz��\"h�X�m=�\\H\n0U�� f&M\$�g\$�U`a\rP�>`�#g��h��`�R4H��'�����GK;\"M�ۨT�h�BE�n\"b>���\r���#�\0�N:�#_	QQ1{	f:B���R�&���)J��Br�+�K.\$�Pq�-r�S%TIT&Q���{#2o(*P��5�`�1H���'	<T�d����s��,N�� ����^\r%�3��\r&��4�B�/\0�kLH\$�4d�>���/�ඵ�H���*���3J�А�<�Hh��p�'��O/&�2I.�x3V.�s5�e3��ێZ�(�9E�g�;R�;�J��Q�@��vgz@������'dZ&�,U���F��b*�D��H! �\r�;%�x'G#��͠w��#�֠�2;#�Bv�X��a�\nb�{4K�G��%���GuE`\\\rB\r\0�-mW\rM\"��#E�cFbF�nz���@4J��[\$��%2V��%�&T�V��d�4hemN�-;Eľ%E�E�r�<\"@�F�P�L �߭�4E����z`�u�7�N�4��\0�F:h�K�h/:�\"�M�Z��\r+P4\r?��S��O;B��0\$FCEp��M\"�%H4D�|��LN�FtE��g���5�=J\r\"��޼5��4�K�P\rbZ�\r\"pEQ'DwK�W0��g'�l\"h�QF�C,�Cc���IH�P�hF]5�& f�T��iSTUS�����[4�[u�Ne�\$o�K��O ��b\" 5�\0�D�)E�%\"�]��/���ЌJ�6U�d��`��a)V-0��DӔbM�)���������`��%�ELt��+��6C7j�d��:�V4ơ3� -�R\rG�IT��#�<4-CgCP{V�\$'����g��R@�'��S=%���F�k:��k��9����e]aO��G9�;��-6��8W��*�x\"U��YlB���������	��\n��p���l����Z�m\0�5����Oq̨��b�W1s@��K�-p���E�Spw\nGWoQ�qG}vp�w}q��q�\\�7�RZ�@��t��t�;pG}w׀/%\"L�E\0t�h�)�\r��J�\\W@�	�|D#S��ƃV��R�z�2���v�����	�}�����(�\0y<�X\r��x���q�<��Isk1S�-Q4Yq8�#��v���d.ֹS;q�!,'(���<.�J7H�\"��.����u�����#�Q�\re�r�Xv[�h\$�{-�Y���JBg��iM8��'�\nƘtDZ~/�b���8��\$��DbR�O�O��`O5S>����[�D�ꔸ����_3X�)��'��Jd\r�X����UD�U�X8�x�-旅�P�N`�	�\n�Z���@Ra48��:���\0�x���N�\\�0%��f��\\��>\"@^\0Zx�Z�\0ZaBr#�X��\r��{��˕�flFb\0[�ވ\0[�6���	��� �=��\n��WB��\$'�kG�(\$y�e9�(8�& h��Rܔ��o�ȼ Ǉ���Y��4��7_��d��9�'���������z\r���  ����v�G��O8���MOh'��X�S0�\0\0�	��9�s?���I�MY�8� 9����HO��,4	��xs��P�*G����c8��Qɠ��wB|�z	@�	���9c�K��QG�bFj�X��oS�\$��dFHĂP�@ѧ<嶴�,�}�m��r��\"�'k�`��c�x��e�C��C��:���:X� �T���^�d�Æqh��s���Lv�Ү0\r,4�\r_v�L�j�jM��b[  ��ls���Z�@�����;f��`2Yc�e�'�Mer��F\$�!��\n��	*0\r�AN�LP��jٓ����;ƣV�Q|(��3����[p��8���|�^\r�Bf/�D���Ҟ B��_�N5M�� \$�\naZЦ���~�Ule�rŧr��Z�aZ�����գs8R�G�Z��w���N�_Ʊ�Yϣ�m����]��;ƚL�����c������Ű��I�Q3��O��|�y*`� �5��4�;&v8�#�R�8+`X�bV�6�ƫi�3F��E���oc82�M�\"����G�Wb\rO�C�Vd�ӭ�w\\�ͯ*cSi�Qү��R`�d7}	���)�ϴ�,�+bd�۹�FN�3��L\\��eRn\$&\\r��+d��]O5kq,&\"D�CU6j�p���\\'�@o�~�5N=�|�&�!��B�w�H�yyz7��(Ǎ���b5(3փ_\0`z�b�Уr��8	�Z�v�8L˓�)��S�M<�*7\$��\rR�b���B%��ƴDs�z�R>[�Q����&Q������'\r�pp�z�/<��}L�#��Ε���Z��\"t��\n��.4�g�P��p�D�n�ʹN��F�d\0`^����\rnȂ׳#_�� w(�2�<7-��X޹\0��s��,^�hC,�!:�\rK��.��Ӣ�Ţ���\\��+v�Z��\0�Q9eʛ˞E�w?>�\$}��D#���c�0MV3�%Y���\r��tj5��7��{ŝ�Lz=�<��8I�M�����G����L�\$��2��{(�pe?u�,R�d*X�4�����\0\"@���}<.@��	��N��\$�XU�js�/��<>\"* �#\$����&CPI	��t������?� ��	�O��\\��_��Q5Y�H@���b��c�h����뱖��O0T�'�8�w�����j+H�v_#�����06�w֎�X��d+�ܓ\\��\n\0	\\�>s��A	PF�d8m'@�\nH�\0�c�OwS�����Y�`�����R��Dna\"��~�?�m���|@6��+�GxV��\0��W�Ӱ�nw���.�؃b��9Í��E�|E���\rЈr�\"��x���-���\rN6�n�\$Ҭ�-B�H�^�)��y&��ךW�ǧ�bv�R�	���N\0��n�	T��`8X��A\r:{O�@\" �!��\$K�qo��jY֪J�����h}d<1I�xd����TT4NeeC0䥿�:D�F�5L�*::H�jZ��F�R�MրnS\n>PO�[�\$V8;#�K\\'�B��R�د��R�_�8�j��*Ej�\\~v���v��p@T�X�\0002dE	�H�V���D�\"Q'EDJB~A��A�Il*'\n�Y��.�+�9��pg���/�\"�1�8�0�IA�FCȨ�V*a��P�d�У5H\"�A��6�s�Y��;訞�/��0��v}y�\r����ץ1�u\"ˋ�m��_�0焄`���\\B1^\nk\r]lh�}]HBW`��0�꨹rFf�)�W,�ҧ]sm9'O�xԽ�,�9J8��?�4�����\"҅�۽�<�-S����M�;�v��6y|�Z����%�a�#8��TC�!�p��\n��CZ(�w��a������?9|��0<BL\r�\n�]�PB0�&�+t�H���օ�Dx^��,�L�}[��B�x}��ru��\0��\0005��S@\"Uؔ@��\0�\$��ސ\"Ҡ��]l/	��I�B4��.�6���d7��\r@=���߬���*G j����f`��:Hn��bĀ71��)C<@A�Y#�����e�o��Y!��I�DM�\nlt����/)�\\43)��2��ɸ�)���f[ ppp1���#��Ð�p\0��œl��^{��A��TH�6�����\n\0P�H�.\r���|�T�FD0��S�y����'1���K���d�����B���C�&�)�W�s Hee+@4� r���ۚ*Lp1<�f�N�Y'�-	XKVa��L���\"���\"�l��q��.YJH�m HV�/�lC�&��H)o�&\\2���%���z\n^Q(6�D� ����Jq���\00a#�6\0vr,�M��&A�������9%Yd��B�h��!W\0�b\r{���@�1��I�22�A��)�H�a@r�0G��7Dd.�LM�<��2���,k/��Me����}Ғ3�=\0�&��B��\nPd.\"��F3X��Sd(*�J6 ���F:��)1�1�?lQ&����h<J͋�f�d�Eպ*�x\n\0��.\"B -�#��Ηt�IΫ���	I8 ��8dh	��x���~��	L!K(�BX��-��h��c/�r��P�I���N�2�|��׶��|\"�M�'��K,\\H��e5*o]4��FP	2��<)�T���o��\n���I�ڢ�!�(���_8Xr�;u�����NJ�����[r��DC:�@�ͳ�l�\0�e\\*x@Aȡ&�(�5��,����#1x� �!T�D���(Q���DJ|D D:\0�A�й� �baE�?rn��Wkx��X=i��,\$3�[�r�9B�Ʊ�d��\0��H��4���<(z���?�sIbJ�g U�\n(}���J\"��A��B�19�~�I�#�\$��%d  e\"�`���t���'O=���@\$��O�\nmT�o+�Z����-�����PF?�_�I�J�X ģ2���-V�;�?2���0�*P3����_T<E�J�\\(�2����)�IQ���鬩���R��L&��!ȯK�iц�t����K�HRl�ȬEs�������D��xǴ�i���!faB���F��e>�V����-Qj�I��7���\"%Rh� g��M������-�b�58R����*��9��ꊰ���9�2Q0���IR[�Z��N\0���20�����\\[@�Q\0��Jx�����EC{���\$lp1=\0�Rо�>E~�������:0���%��R+)\0�	ƑQ�@(\"�_j�T�X\0����\r1�\0P�9#\0����H;B�|���L�Z�����6�/B��\nB�{���|H�,�	*;��(�`�2@6�>�	�?P\0/���\0|\\�eB�`��jq�U/\rc�����҆�6(N\0�/\$�\n8�j*U�\$��y*�=�;���\$�f��8X�BCE��r\"/�����kځ%\\9k���B���0�F��(��'�U���Ʈm�@k�T\0��E��sEhy�e\n�)�)��b7��(W%,�J�r��2D�rhE��\n0Q�3� U�9TPO������8j|�}�R<0���Zl ��T�������*�\$��U\r�\"�.� Ts~�~(�3�a���@��+���l�`:�`�:O�i��BX�?ʄ��7��Lj|�:n�K:ز}�\0��UMc`P%nn\n,�4�Q'%+H.�\"#G��3`�����\n1fg\0�М'�k��qxD<\"��,a|{~���C<S�i�B�\nkN���G�}���k:��������g�)�JD���hÛf�\"�kV~��mM`HO�kD��^�0/tj�l�\r�!�f<��G��T���v�#@�ek@2�w���0�ܭt���į1�u�yvː%8�?1���l��xt��mp��fK3Z�J�=\0@�^p��ۑ����]Ҳ'�t١@C�b��\r[��V��-���o�-��ݠe�}��Y��	-�-m�I\0+��V�D�[B+��(�-�4�>�q��i>=��/0-�cL�pJ b\nd��)�#��G�s����\"�Q�N����`.�ȍ�yȐEtP�q�I]��J8���rWT��I���f�aG�.떄7y��l��A��7'�1�	�S�-�xI��m���L:e�ΉA�W��ζEI��Wz��3W���)*/)C���x*c]�%�}����_��IvͲ�'�\$U��S4k�5WʏJC���7*�b%<WC@�	����c{޴���3)X�&&��eL�I���,N� 2k#p5���f4���Ǻ�z�#��\\����N�b�U��oy���S�4�`q�~1�=�8厉�*�OOJ�C�����'Dd,@kL�������\\�j2ͩ����<�@_q�2�\0�ձ�)`�������s���F\0�����\n���F��<*�x*����`����-��\r���|@����7�H@w����H]��\0�����_w��h0!�s�1Ϗ��Ǭ�hW��.��=W��R*�A_���EDԷ�?1,Ub�9=t�4è��W��^���;����@��(1<D�ÊHx�T()0z�`�_�;��AL��)\n�K[f�H���Wo�@bBK�iM���d+�>�vI�(z:��.݀��9uiѤDY����O`���]I\0��R�Ć,K,���6L��\"\"�1g�(���|T.,�9vb+\rk]u�&�|��b�S��d[�,g��aJ�(C��k��\rF�+	��9��L��))UA�B�U�h�g��c3x�-n9�����x��2��q�ib�rY7�k�y�f�,������)�٪�J:�N�8�Rcly\n��2�W�;�.>�v6Q#A0��{έi��7~@VX���^��11-�+�v|��]Vf���.�{	���\r��;�1lp�/��uF��d�\$PЮ0=@kS�0h��Ɉ@��/*(O�V.��G>�(r��!�6�����Y=XZ@�:�'&0�6kE|���'|H;���N�g�%�W�+��4�;̓��'x|�f�9���(O��d���w%9]��f}��G���s���¾�����XM0����gQ���8̄�+O}�͝0}�9�������Nh�/mgD���s������\n�74勳P~}O)�Ug�9���j�8P��ݸ�(�%����j�7oAB��i)��K��u�� �}s�1�=od�V[Ĵ\n��zl�Mзr:F#{��*#�x��ܰ�<Ds��k/mw :^����1��ύD��2�z*��n��%�����i�Ù *�!8-��tH�'����\r�к�4����8`��\"�����i]�ZZ�>Z\0ަ9����+䟂~��\$ޭ��L�P\\쇁�XA�������i���z�h�\$�SM�T'���1���D��	��5E�\0Ğ\$�ttԮ��:\rMƷS��Ӗ�ls��Af�K�k,N�l�D^zz�dS��/rt�N�>��o%i��\0J�B�po��R����/֘٫x\ny�+��,e4��q5Q'JD�]�B@m����R�Ski~����t0�[ 1�z	���&��^�\nO����V����GV@T*�H9�ωG0\0'�`�Ѱ\r���bQKsLd�*;\n����.ĔUNp�,L�@TR�e��b��F���y�n> IK��rG�	@��?cI�ݓu%G�O�1���C�h�5T�y��I��:\\0��X��>�ʊ�0�޾�QB���EI/-LBT�!b��6���k`jp\0K���>k�d���/���ISk.+*���R�|gR���W\\w���t�.)�^Zc8�Z�~F��Sǵ�S�m̕;b>\0jz=�T'�>��q�y}:�u��&��W�DQ��c-����6<[��e�x�ؠ���[���L�\0wm�l�t�z��<S�&��db�x��oi�gK�\r`�µ�?D5u@b���N��O�𤷤���Y�[�����{�Nr鉞�t���\0��tMs�cBW?�*D�.p���'2��Ge\rp*#�e�����C���\"�QI\n��hi�Q�@���\rl	����_.���t*�^��s�9���Whq���~,��Yθ��dQs¦\r�Bj��D�ǡ��<<T)C�\n�����&�D{\r�l���-R��\r@rk��Ϣ��+Z���P������u8Ȩ����s�و���o�#��g��u\$F�&\n-v\"P����j�nnt�1��V������Awbx߄�D�5��-�0�a�\0\r�/!�I����|/����h��n�Gf-Mdna�^(e�a��¨�Y��Z,�S�E�N��\\�����=�4~Mʹ�\r����Ft�Ŧ�u\"|`��E��R�z��D�`�{��@�k/K�Y����3sJ�䃿5XGͪ�%�9)Q�� �Q���1t�h��!TR���H���Q�\r�C��E�0�#w�G2��/���/��=^ �/Ժ�ΐ����E��\0{+���t�+��q�б��I�t�|����v��q��Ԉƌ&�\r\\�Vߠ=���Eb��nO�rn��X({�ɹuzK��`=:�\n����\0����[�%�:p���q+��R�ldY��\"��[V�u{H-��H�_��8j��V��5����\"\0\"N?E;+�O~�wN�];L�'���SOF����䁻��D�-�!#sN�<��� ¯��mu����G�8���Tn]�����:�zIMn� O�8���z5���o\\5�7�<��Ų#8���?sN�L��	}�x��&4�?�[�z���󳷶����<*W������e}{HZ���,(<o�o�xW�t�2���#�A*�����o\\�R�}xH>NP�|Qɚ|x�'�-� ��2\0��?ƾ2*\r|]t��p�\"�ڲJuuXyb�D\n�Z|�H7�_�W���GuXyH>T\r�G����Ql�������n!�u'�*�C5��>U�2!b	��9Pw��4�����}y�W�|���a\$�g������T�U��&~9(\\*�!b_����w�7\\����]=�\\*���@�#N7ͪ��5QN`@<\0�6!�9��l��\$�wI\$4���2��\$�&���.RZ����Y��uyᤳ�p�&SI��@�EJiL�c���V�1F�1��Z\r\r���h��k���HH��˿�����K���?x��-0\n��d�N3K��C�59)ľ:B#���dN5A1�Ɖ����Od[3ڠ��h�[s~)�9�DN�y����>���X��'Ƚ�ϐH���,��)ڂ�\"�e�0;\0�qeo>��=�|�2�G+B�@z�������@]}��rQ��� k/�|�G�:ѯ�W\0�a4>��^|���g�o�XE�9p���Lrg�A��6��p�e����1�*����7��[�>]�#�?jB�~�/�}�3�:��U\$�?�<��G��a���\n>0#!i�>.{A}'hQ�Lw�~�W_��Th#d��û��d��FQ�����*{��\"�\"�P{���}�4�N���i��\r_����e?l4�2�?\n�F��	��q�U��Ľ�_��`_�����j��{_k_�o�~��c*#�(�/�!Dn�F�`��?@s�B�!�?;�E��������\0k�	�*N��D;���+d\nZZdB��� ��`B5�P\n8�������c#ou��k�ˊM�ݯw�.��F�J���!|�Ĉ2Fc�Y).����XHy�[��~����#/�&����[�����Y@���(|\r\0,O��0Yb��βŬ�\$0���aˑ����� �A\$��0,�@�Ӱ>>9��\\t�i�<�\0�q\0�}@`�\0fVj����dߠ'(����	!_�n��0+c���iig8a]'=-�B!(��8�_���x�j�����)\rH5H�Yn	,f�r��}-d\$��H��2n鴆ܛ�=�-�d���FE-d��a��N_z4@��[�n��\$x!!i0T����u�8�ɸ����\0PZ8Z����c����+Њ�AAF(����`mg*�vS, ǆ��KcA�۬ &��9������c�0w�+�n��=��)\$���Q�~A��a�\0004\0u�{�(��\$���y	!��B�� A<�a��Az ���ZA4\$ZY9.aX\r��d�A�L�v|oOz|�Z�(�e�Z�Ć�");
    } elseif ($_GET["file"] == "jush.js") {
        header("Content-Type: text/javascript; charset=utf-8");
        echo
        lzw_decompress("v0��F����==��FS	��_6MƳ���r:�E�CI��o:�C��Xc��\r�؄J(:=�E���a28�x�?�'�i�SANN���xs�NB��Vl0���S	��Ul�(D|҄��P��>�E�㩶yHch��-3Eb�� �b��pE�p�9.����~\n�?Kb�iw|�`��d.�x8EN��!��2��3���\r���Y���y6GFmY�8o7\n\r�0�<d4�E'�\n#�\r���.�C!�^t�(��bqH��.���s���2�N�q٤�9��#{�c�����3nӸ2��r�:<�+�9�CȨ���\n<�\r`��/b�\\���!�H�2SڙF#8Ј�I�78�K��*ں�!���鎑��+��:+���&�2|�:��9���:��N���pA/#�� �0D�\\�'�1����2�a@��+J�.�c,�����1��@^.B��ь�`OK=�`B��P�6����>(�eK%! ^!Ϭ�B��HS�s8^9�3�O1��.Xj+���M	#+�F�:�7�S�\$0�V(�FQ�\r!I��*�X�/̊���67=�۪X3݆؇���^��gf#W��g��8ߋ�h�7��E�k\r�ŹG�)��t�We4�V؝����&7�\0R��N!0�1W���y�CP��!��i|�gn��.\r�0�9�Aݸ���۶�^�8v�l\"�b�|�yHY�2�9�0�߅�.��:y���6�:�ؿ�n�\0Q�7��bk�<\0��湸�-�B�{��;�����W����&�/n�w��2A׵�����A�0yu)���kLƹtk�\0�;�d�=%m.��ŏc5�f���*�@4�� ���c�Ƹ܆|�\"맳�h�\\�f�P�N��q����s�f�~P��pHp\n~���>T_��QOQ�\$�V��S�pn1�ʚ��}=���L��Jeuc�����aA|;��ȓN��-��Z�@R��ͳ� �	��.��2�����`RE���^iP1&��ވ(���\$�C�Y�5�؃��axh@��=Ʋ�+>`��ע���\r!�b���r��2p�(=����!�es�X4G�Hhc �M�S.��|YjH��zB�SV��0�j�\nf\r�����D�o��%��\\1���MI`(�:�!�-�3=0������S���gW�e5��z�(h��d�r�ӫ�Ki�@Y.�����\$@�s�ѱEI&��Df�SR}��rڽ?�x\"�@ng����PI\\U��<�5X\"E0��t8��Y�=�`=��>�Q�4B�k���+p`�(8/N�qSK�r����i�O*[J��RJY�&u���7������#�>���Xû�?AP���CD�D���\$�����Y��<���X[�d�d��:��a\$�����Π��W�/ɂ�!+eYIw=9���i�;q\r\n���1��x�0]Q�<�zI9~W��9RD�KI6��L���C�z�\"0NW�WzH4��x�g�ת�x&�F�aӃ��\\�x��=�^ԓ���KH��x��ٓ0�EÝ҂ɚ�X�k,��R���~	��̛�Ny��Sz���6\0D	���؏�hs|.��=I�x}/�uN���'R���n'�|so8r��t����a�\0�5�P�֠dẘ��̕q����5(X�Hp|K�2`�]FU�~!��=� �|�,up�\\���C�o�T�e╙C�}*��f�#�shp��5����mZ�x��fn~v)DH4�e��v��V��by�T��̥,���<�y,̫֞�2���z^����K��2�xo	� ���2� I��a�h�~��c�ej�6��)�]����5�͍dG׊E�t�'N�=V��ɜ@����b^����p:k��1�StTԙ�F�F��`��`��{{���4��7�pcP�ط��V��9�ىLt�	M�����{�C�l��n47s�PL��!�9{l a�������!pG%��)�<��2*�<�9rV���\\����]�W�tn\r<ė�0�vJ�栱I�i��1��Ys{uHհ?�ۖ�����U�o�Aߒr`S��Cc���v�˳J�c�����=��-H/���q'E���w|��N�{\r};��>�x�r���u5�B�*\0����M�����a��\0�{HU����C�W�廳�yB'�<�6�[��s���y����@��{�Q����>?/<�K@� ���B�|aH\"�� R	�@>~@�BhE�L\$�[��Sa \"�Ђ0�Fe`b\0����@�\n`�=���n��.*̔�O��Ϙ���n��<jO�lM�\"mR���/��*�&T肙�T� _E4����8���|R0*�o��Bo>S%\$�� �N�<�|��ξ����y�7\n����޴�,鯢������쐬�Pt��\"l&T�o��E05n����o��r��v�������֣Bp��p��\n���P���.-,��q����3\r/�p��P���b����%m���P2?P����@��0(�/gpz�0�`��gυ��ϑ�\\嬳q��>��p�@\\��u��@���\$Ne�Q������0(A(�mc�L'`Bh\r-�!�b`��k`�������`N�0�	�ЯnN�`��D\0�@~����`K���] �\r�|���ʾ�A#��i�Y�xf�\r�4 ,v�\0ދQ�ɠN��Xo������ q�'��t�r\$��np�6%�%lyMb�ʕ(�S)L')��ޯL�M�I�s {&� KH�@d�l�wf0��x��6��~3�X�h0\"��D�+�A�\$��`b�\$��%2V�L�� Q\"��%���R�FV�Ny+F\n��	 �%fz���+1Z���Mɾ�R�%@ڝ6\"�bN�5.��\0�W���d��4�'l|9.#`��e憀�أj6�Τ�v����vڥ�\rh\r�s7��\"@�\\DŰi8cq8Ğ	�\0ֶbL. �\rdTb@E � \\2`P( B'�����0��/��|�3����&R.Ss+-���cAi4K�}�:�����\0O9,�B��@�CC��A'B@N=�;��7S�<3�DI��MW7��ED�\rŨ�v��@�DȺ�9 �l~\r�d���5�z^�r!�}I������sB��\0eT�K�!�K�UH���/����2�i%<=���^ ��g�8�r7s���%N��E@v�sl5\rp��\$�@����P��\r�\$=�%4��nX\\Xd��z٬~O��x�:��m\"�&��g5Qn�(ൕ5&rs� N\r9���.I�Y63g6�]Qsv�b/O �|��@�y��^ur\"UvI�{V-MVuOD�h`�5�t����\0��T�,	(��q�R�G�.l6[S0@�%���C}T7�85mY��)�8�C��r�;�ئ)�M+�4	� ��4��|�Ϊ1�ZJ`׉5X,�L\0�7T\rx��H��dR*����JЦ\r����52����-Cm1S�R��T`N�e@'Ʀ*�*`�>���\0|��I!�E,�ag�.��cup�Ý9�`B��aa���p�`�m�6��R~�\0���g-cmO��1�\reIN�QN�qo\r��nq����R6�n�Sn�t�w�æ\r�]a���-�a*���\\5Wpv^ OV`AF����3#82p�H'J'nM('M=j�9k�ZbBn�<�@�<� \0�fe�:\0�K(��N���v����-!�1�ލH(�Qg��µ���y�<�� �d�\\�c\\�s,u�˃q0�~�i~���e�Ѷ��*�~��Ƞ�~��M�m���}Wؘ\r�� �@�\"i�\$B���c�g��5b?�6!w��+xl1�`��`��	s�� ��������.�vCnhEd Q��id\"6���`�\"&f�x�(\"�2�Qz��\$�[0%�0lw u��>w�%ر��%�w�Z�\"-���u�%�����Y�g��>x\\�-��פ���-v�\\��x^'M	�P���Y�P����)�8�%�C���@�DF ��\r@�\0�\\�0N��.��S\$��YI��C�I�� �i�>xP͸͒�:ͷ�=��T,�'L��ٞ�q�Q2͌�\r�����d���Δ���@�ђ���9F���`�O��f�O�w�\\h�=�}S�jGGW����A��L��RJ\$JP+�7���L�v,ә(̵���ZP�g����&z+����j���˘7�ͷ��-�vA��w��h �^9�T�OD���Z��C���m����`O��R�yӒ��!�Gvzs��G�\$IhY�����58��xF������Y9����i�ݍ8��U��C��[���e���Zq�uA��1���?���و�9!����:�ړ����b0��{\r�Qh`Md7�{2� ۲8�H`%Ƹ��{-�l�C�Xk�H��Ӟ��|\0�}�X`ShխX�ց��\r���O�y��X��� �:w7�����n�鲌�#�/�:4�(M��;���c�D��z;�Z3��������]��砛�?�.�����\r�o���bO�^`Ϻ�|��������/��X׎�]�|�����^�!%Xٽ��8���\$�;��z�T�xK��-~� 8X)<!��y�x�9�����:�� ىF�������x�z+U຃�����A�E�;��'�%c����Yߪ����w�<{��9���V:��`��ʇ<����G�ء�Y��\0�Z��U�Zq\nmx�)_�}�YǏ�_�z�����y\r�Y��,ۚ3�L��٪�Y��ٸϻ>�M��	�M���	�)��P\0u8 S!Z{Y����9θ���fV3�o�Oϼ�E��`CЭ��࿿XU���}�lw��0�}�����͙�7�Y3ӬӔ��4��G��J��&�ä��ͭ(���-A��V=f|���@E/�%\0r}�ޮnn�\0��Ly�����<+���_��|��#�A��\"C[y��EW���rW��f(\0��Л�>�)�����_�U��,U�\\�#��e���*r�`�N�Y� �*�=a�\\��&�^g4�mü����e#��^�|ނ�QXN����I�>��\0rƉ���4��^Y�V#�)��k�>�׾���Ι�ԚF�W^��%�ݒ\$+�ՍP�kY*u�~��,��M��W͂hhG��K��\\C��7HmZ���S�Z_U��%�\r��b)��gg	q�������@@�����΅t�\rJ���۔��7s������U�K_1��t�j&S�Bi\0��� &\r���`�:�j�F�~=T̪��g�侑��!���^h�^�ו�������/[{�B����(�/�|���g���j/�d\\ޖSɗ�9���G`��u��1�M��?���3}�Q\$q�I�m~����G=��oVz�\0_p���!t�r{��^Z&��	��u�X��1@��G{���Ь�	NI������\$=0�Bu82�S\"�6��Qpj�ov\r<��ɶU�\0.�����E�M\n8�V�oQ\\�`?��L6���=\r�l�����������\"�����B2pu�&\0���5�\r�j��0V�A�����;v\0eH;��ʇTJ��6pH?/\\�H�@!pp�C��+5�\\+a�8;�\r(*��T�Ƣ;�O�|���^Ld�&/��NI�T��|#���G�`j%Ǘ��D����Z�ġ4�ni�i��4��]@t��#5c�ľ��	�Z��R�yR`@ँ\$I{z����胇��4|� ��׉�܀@=hCE��H��, ,Z����i���K��àP�|,g�z*�����E)AjknK\n��C\"J79�}4�f����*�4�65����׏���Q\\���c��M��\r�{��*�1j����lF��m�4��M�*�`�X�G�D�A-qqab���1�9R�H���b��g8�+�l/������ń� (�ʀL\" 8���0(Dc��#ihc���`��8���A1�\\�uK(�4!�������d��3�8����ш�Ʈ4�j�;�#��Ø���s8���5,ucnc�F�N��pPa8�G8�r�K��������ύ�k�i�˕4�A�	�8TҨ�26 ;*i��X����2%M�BJG� &�C*1T\n4	-#�.�%���'z�#�8�A+�@S.0�׀���II`U�Q��U�dd\$)��*]���T����C��9M*��	\$b+��ѽΑ�ydt�\0-��L��8\$��e\$���<Aɍ!�d�\$�p@]�d����&��M+2E��y߈((_|�Md�vU�9!�eD	�(��W=���#���_�'�bN�;��'��\0�O�<�L�iA�� Р�T�����\0�Q�J# }Ba(�/�u�GB���%-)��h��u����~\0�I�U��Pr�+1�������%51��ɒL`ܞE'(�/��Q�Ô��%T�)9�Or��T��],�?�<�a�	����/|�\$O�@Z��I�XN�|�%�,�SK:]ha��%��)k��P\0,���'�0J�:��	��&����V�0����jهJM�*�x���P)���j�K��R ��\\\r�u\r�(ÐW���F: k�����\0��NJ��P!�Q2 'H� *\0�g�T|����~g`D,�Ͼ\0#�	�;(\0�� ��L����f�5�'��`'���&t(��L�g�A���\0��'��ksi���&����d�m���P\"�Ng`O�&����� X@�	��%sh��g_�sb��f�5��M>s3�@T��77�+��nS��dӧ5'�6s\0\\���\0O:��NLS@ P�{;9�Ͷp�F��@�78_�l�9��\n���)�Rg�9�@a�:i�\0�vS�D�g��\0�S����\0�s�M\0B�\0�+O�q�`��>��4	 T9��7=�M�v�=q�'y;�'L�f�F��f�)�ϖwP��T�f�>\0�O�|��?0)O�~�|�����`#N���\0��>�'Ϫ}�ՠ���>�~��e	\0�?�*P��3��\\��@�͌��5\r'�C��P�� O�E\n�MB�#к�T;���=jPރ49����Ez#NƉ٢���FY���\\��\0CA�QJ�TV���������7 \n��v�0@��_��Q�L�RRc!��V�|�z��6��KKю��eS������4��\$�aI��|P��A+��.qKD-�S �EvbCO�>��H����<��\r#��LPܘ�s⥺P֭2�0� =�*��WL��2d�t� \0�!��<	�b�q�\\pa@�Rd o�fKM���p ���\0}����z�\0�����2�Ձ��3\"� �)@\\*g�r�M#!�8����dP4�%>K�mA��\$C�jtqP�9��Ƹ�Y�jP:vTu� 䆀��T��`=�p��cj��*�x���d�m\0���MJjFm�p���A�F�QR����6�F�Q�lDj���E�MSȖ�4\"\"m�@J�Q�H����@�(���h`O��f�8�>P8	�{;57�,)�䌆mSv�g�Aᓋ����|���P��d���O�x2��.�S�,᦯8���6q��,�Nz:��L����\n(%>��O� N%'��>\0�����U�9��aÏ����P�I�OH	�9\ne�5@\0���ALS!m����qv���(�N�7=�=Y�AN������R��)ϊ���4��X�JgSmZu\"N:�*4�*�,�:�	��	р������L���5Q2����VXR�5ש%��a@�v�J�򋈵a��Xv�(�u�j��T��6\$�X胙V�ا&�����H8z~�y�^�k`��?l� wuuz�@�lS~�@.\"�E�S��*�����ebM5��Z{l{a�/��X���U�1�֡��񎅦a�X�Ul1�ʢ�\\����6s�����:�§��Y}��ި���e<,��s9�.!�SV��\n�b��\nhK#���l%g[;̤��X��>˙gQ�\0��ӳ�l��v�D���Au���X=B�*�d����s�aڊԕEZ�Fl~�b��{\$_�r�\0Mkw�/�~�y��|C�j���^�5�D�2%�[D��u�xo{Dڶݶg��1\0Ƭ���>���/����Z�ҙa\n�!�E� �A�d*�e�@�}U�0 7}h\\�+��1��U5��\0��9R��V�anh���m b	��  =�έb4��IO_��[@J����u�`}��N@�ܳ()���xS\0 �z���\r\\j�W 'M��w>�[��.KN��x��v\0� \$)�� z}(�Z]b��Ẽ�+Xz־�G��h?E�Q��b����vK�WQR�KqE~�I�T�5�)��n��\n�T-yD�K{��`P/��V�:��I]�n�i���3�X���^~�����L�\"(S���2���k��\r?�c��l��U,;�M�\n7ꦖR�f���R�y�ԔzV.�ko� >�b��s��(!ۋ^�=���F\0.סMJI.�Hiً8A3����� `(\$ړ\0Uр�?�(��\$�~�D/�p�a�����Tp\0�C�Z2�.,.�}�� �Ѱu��D4�X�	�p�3��x+��i��\r���Ex�	��l����ј2)0�pr\$�����>% �z3��!P�(1P�p��L\"\r�s\$��	�7%Ɍ���6��12����B��l0��|.Z(?�Dr��Z@<���m��{��fC�,a�n#>���2���A8` N��\0�U��f1�<A+8�Zq�ja?}�Fp�:�\"8ɇ9�`�0݄a��\0nB7=n\"�o��.���i��J0��G�����b�\\l:b�kdXP��ˊ<)�\r\"��K;1Udb��8��L��V�� ���7�0H�Q�*c*˙WY��X��ḳ�-ѱ�7|R��8K�ܓWtL2<{����5ٍ�>���e���{�ʟ3��\"Jā*�R�BcлV��q<�+�q̅'##>�2F0��\"X��fE�{�|c�t��M�5��ȷ>D\\X��Mg�c��	g���U'���5�\r9%�Q�W�����'�d�NOW�����#���~�u��e���Wߧ�;���\\S��&����,��}��_|���� ��!�vx� ��]m�1�|��Dx.�Bo�,Y�	�tø ��]�Y�l�/�;�YA[�˄u�`7��q���?�F�\r�v-�@٘��\n3��h�j#���K 6N�^H���\$��(|��\$����e�'�H��%�p�ğ%	l\n�K �����cg����B����\0%�{E�z���Yjic&�5�nG�g��/��Z���} 7G�\\K�K��-�Qf���pl�����㘾�w�8�a�L�*��}�]�@�R�}JQ�qg����^a<�\\ �g��C�[���PR�*4_Y�Vv�\"eq�0?Yw�V\n�ݛ2�Z����&m�2��@hn��\"&�@���H��4�O�U�,;����˘#L[��(T�����].���v��{|�\$\06{+\0\n�/\0�tI��T��Ŷ�1d�I��	����@��*96�t�Szi��Mr�숁�(���\r�ӱX�\":�A��ބO����\0(	M�wӆ�4��J��K�V���9�2jf��~��@�L\rCjyz�\n�����^�KR��԰����WVڸ�7���W�Ћ�F�5��c�GP�=n������;�&�8�+-C�\r�z�qf��T���@SАC�ͅ�5kyk/�bEŠ8}t餎f���o�H�{內����I%����a����o�d\$��qzJ����N!oP6�dF�H��\rq��iv������c���E�P�E��	����4�p)�}�+f���wI͓U�	f���{����Y0�u=�Mw�`U*���Nci�4+�. ���m+lL!�\0ro}��;a!���'��l�]bY��v��%�����\r����Z�}m�\r�'p�˷N�i��O�IW�Uay��\r�sܨv����n+��+m����dL��p\nU�\nC\r�wqc>�Q7c�\r���ۙ����BU�Ϛ�v[�7�Ŵ`�Q\$gY�T9�PU��@�v[�ܦ�����ōL�PNξ�n7p&\r/w�=~�혃rp<@e-�K��/e�V�=��*��_�a�;ڈ5�e5�y%��JB��b-08 .֦V	�� Dg�\$U�5�4�Am?@��\$���\"���'{���h�85���c���׶B�P\0����&L+C�\0P0'�0�P?&��ϐ���#͒\n�B�\r�X'��s�`!rNhx1d�B�d�ɿ�;����rJa�+�C�F��.�6)����},&����Q��/L�?�	�ƬW�k�p�`�Q,UF�5�=h�{ZO��e�W���f�����W�e���\0�ѯoPi<�Q���&\$0��7{�����J��ebw�4�,��[j?Y�\\� ]�#K�.���Z����=�\0Z�%�&Q2�]r��B����m�|<��>���/k��/J���Vk�l�\"�������A�a��o���۫�s�u���o99�c���G��\\�Ξ��m�p�_R��V���E� �p���R���@\\^b�mҸ&�����g����_���.A(�4��0w���N~�K�|�x�����r(r|��'#�G���D���k���W�B�\\���[�=��ScRc���[�7�8i�1�/�m�^���\"�w}��z�y\n��p`�6(�m���\0u�#�Ne�c��T��\0x�GZ:�87W������V!��ˮ��hB�E�B�eے�/�&��,Gڍ�Bf��t����쎓��耷z&�b2֐}a׏B���ގ��܀Կ ��|\nt�\0t(��k��Pp���Wi	Ͻ;�4��v�Nt@�q�ʣI\\՟?;��=Wt��V��H)\0'B�q[1�v���3��z�n��#��N�c}I�S99�|#��3�\"43��p6�8F�'��9X�}�^�ov����e�6����C뗎�6��B[�9�B��1�ŵqN��y&�rY\\\"�\"�v;���p��=�&�a\nT��\na����C\\\0002�D�H�0�<R�������\"����R�c��=.�7��U��|FS>i�Z1Lƾ���_O��!!�D4�.f�e�����౷,��Z���@�\0<F.�����c>_}�������ʠp�C<�I�=�Z05ь��)ގ������Nz,u�����\\p6ޟy�-�=5�!T#G�\0*m�����Inʩ��������:�*���i�`7�h�����罈���EB.֜Xʉ��`U��q��s����sl[S+{��	��0���{�7�<p��n�͹��.����]x�m��b�}���<�[ݻ��W����Vh��kY���>�e�5�\raa�F��Sx̶�OcÐ����[L�e�𗂤 7����>@����+|���;������.��@Y'������>E�@0��@@��H=C��`ր�b��-Oz�m���t>=�=��3n!�����8DM&��J�w���.]\0�O?�[�4D#~<�O�����\"�	o�t���BcLK1�	�YĉI��x���8Q��e)���`���ǐL�6�+�جB���wbo�L]+VP����B�E��幊�_V���x�r�|[���\"_~����HG�sp��C�\n\$��R`��Ǯ` �\"�@F?�	Q��q���^��`�W\"�;�\"IPD���ǆ�2������oC�n���}s��x�2��i�wc%������\r��N�\0I�����C�8�p����YP!�)�F5� ��	���c�.��o�7dZ��`<*7��������4w(�O�r��0n�������9��'���t�X�6g�6�\"�V:������V�`\rZ�Z��A��&!�_O\r�O�D�	���� ,��34/��o��m)���#}����yĤ҈[n�4\0���<D��.���� B��>\"\r�C����;��@�������\0��D0�Z�b\r��V%\nRRA}����\"�.��P��>n1�4�=&:>�\$�^RMpQ���\$�L\0�1��𥀔���}���m�H�B��	��	���Ah1�V�=&�L�	A���T�!�\\)^�2è.�W�p.�C�Qؔ��\0�\n`(�.	9�@���`(�|\0ʦ�>����#�@2D@֒�\$�E\0f��r�v\n�*`��.�W�s�\\�|�>�H��*��Ar��Q�j���P�;�D<|�I�N��0UAX=)�k���X0��v1�ݐ�A�U,#�DAc@(P�AZt��p[A��/����	0�0_B5Ik��4�d��bQ�g�T1��%{ѩ�=\$�PI=KH���\"0\\A;	aҢ��]�&7A�\n:Y���\$)��\n�.�k§	�Ps\$�\n�Rp�����+Ј/�'�O��#\$,𡥰*dЀB`� �B	����AHTD1)I�QtLN�C�=\0���e��\n{,���<4B��Ii%���\"P�\$BW�h%S�P���30MAE\n(�p�A�|c�)���\n�#Ǯ'\r8�X'�[����5C���0��B<�BS0iBf�L�x	�1f	��@�ДC	�;��C�U,8�b\0ޖt)\"2A��iRC�\n�/������:��ì�;	_�X(��\rC�\r�;��C_(��B�,-�dC�\n\\9P�C��.��=B���-�t�]P��o�\0��N	��ֳH�h��^���F@�/�<��\nDj�`����れl����|/ !��*���G�3�^�\n@��'�	C�����(��H��'&��\"���\rLF���2��Dx���!jD�I@<�vQ��0�� ��C��XH\0002�^;i�~���TH�6�5  C��H5(��49�I �,SF�_TS�F�B5\0Cq!�`�����\0O�P�DEW�Q�PEE�?�Y��<S1]EY�Q�_�H��KX8|X�\\�7�QqO�x�����,G�J�\\I2��0SQ#���a\n�,<\\� �=\\]�+�%�I\n���\\��\n�\\FiTĪ+�,D��K�/���1�\$'�Z1T���U�PE |R@/�({Q`0��\\W�E9�Uщ�+�c <�9DX�^E�|R!lF,� q�\"�H,d�bŧ<eq�F]�ɎFk�UQ�FClRq��(	���D�g�F!De��Fw0ܱ]��le1�F%\$e�Ÿm�M�]ğ���ۓ���ۀ�x��x\0�����)�Xm`�[�K`����]mP�:i�SM=@9�pSFĘx�B.�t��\0�p\0�U��\rNA;�����h��x~� �w���҅x�8����s`�!_�k�\0P��1@�\$4��w�����1��nª��*\0�#R��0?�	�'\0d� 	��(Hx�G��\$��\0�H�`(����x�GD�tH�!�\nɁ �Ǟ�FJJ|�6�>?�XBQF��Q�\$`�\0>06�T�\r��Z\\�:	(+ �FJ�Q���df`>\0��d1��h��P��pK�\0�T�@0�B��H�>5 �i 6Ɩ��j��B8��Z �?�����4 7꼀���D*�<l���H1 ����7�5�%�?leQFȝ�\n`<�bĊmr���d���\0�\"����ȼ9��1+�V��G�\0ɒ1�?#l���H���Al\0����̈'�3i����BD���٦o��c��ZX��MJ\\�#2�mbJ�\n�k@�G�ikQf)(&\\~�k���������!*E(�B�&�T�-�`�R݃\n��\"i�H�\n���\n=��W��<����@ *Jz�7,�z��!�&��\$C.�H��\r��F�`+?�������t��֟����u��]-����~�b%x�d)�\"|^BW����L3S(\nb��#�A�����R\"��&H�CTؙi�W�f��IX!q����D��	^H�T�	9(̚��\0��҇�����4)) �\$��~0�6#\$�*�t��#;<�D��ڤ��Rx�}*#��3ʐ�g�C��c4������`�@�&������[��#�da?��Y�n�;��x�:L����F-�C�_���rk� 	�\$��2��#\\K\nH�+����\r�T��;�+�TR�2����\0��r���*D<r�p��!@I��Z�ҙ��(��o���ChO��넚`�����2�\0����,]��\rx�@:@����\"��(H+R���-ܡ�텱+�b �Ib(��aHH�# I@)�ҙ\0��X��.@Ɍ����\\�i���̵�3-`���������<��&��� �Ҟ\r�#�`ҍ*�H\0�L ��K�.	i\nv\0�/Xe��0��\0��/T�@6K�)���\0��́�!��c�힬����\n5��E�v\0��\0L�Ҹ')�Vҍ;:&X��@�,�\0�Jo,ळ��1L�b�ʎ��s�(�ǓK:ɴ�b#I�-؉R�J�\"@��J�W�s�:��)Lt�ʁ6̬��*����\\������;�!F�9	0�V�G;�,�S����o]L�3�4�:�53�GL�1h`���h'��ÿK�02Cjo\$N|�HY�1��#\$��3��I�\"�Ԛ�KJq1Ժ�J�0�BW��1\$0���伮��\\�\$&�T��Հ�L_5\"p4�5H��H�0�ҌMR���[��6\0i7�~	\0iB͇6@#�͎�����@`��M*��M�K�LF	%A�Yh\r�!j��sp,)-(`�,M��}�#>�¤Q��\r�Ԍ�WTҐ\n(�M��A����i�l><�ދs�{(H\"�M�萤������i�SN�� �M�\r�+�������6zZ@? 7M�9\0B�����K�������J��Ijl��q�*��RM#T��'�n�쇒k)�9��!���\"��D��������/�JO�/�+3�\r˗.P[�H���\n�\r%#X8�m����1�ZX6��6z�xn\0�HT�|�J4ms���)� �K<�̜����l�C�����P��.�\0006@\$���\rKH�>�K�����;p5�D\0�;t��-#\$���I�0��f�8���3�;���3ʦ/=@�?g-kA�\"�i4H82a��%R2+�F����pa��@��pm�I�>\$��*?�����(\0���|�\r�ϩ5 1�(\r��2�˶`�OqmMJ�\\��	�>|���O�8��,:�8��,���c��r_����Y���c4D�&�\0���d��B�e0\$�T���~S�&Z\n��R�\n	� �K��r�:�<�����%|�����9��oPM95 6PQ=������:�5����1�@9�m�\r�E�`,��\rT�m��F	P2Q��+}�3�^f� )�Lnd�`�\0�+Ь���\$D�R\0���'��J\rd~�j\0���қ��:\\��\nJ�[�?: ��L����:�/+�>(!%1��f�g�/�;;3���v%|�t<M�C���DLC������Jo:���� �\n@9P�+Ȯ`��ջ���/P5t1J0 O�Ȍu5H�R�MR��ղ���:D�*�ȞD��T�oD\$�3�6z�=�T��FT졵�R\r\\��F\r@�<�)�o<S\$GL	�\$t�4-�XZ]Έ��f��2i�s\$Q��%6���+G�cSFz��4+LlA=�W�.lG�,��\re��R\0�˔�)�3e�Qf�P��V���.�x�y��H�bDI�H�x�{��G��4��G��ԎR\0�� T�R\n�%@�\nKI}%T��S3\0��M��A��EI\$���Q��M't��\"E'A��I�(��;JH�b��wH䂡��9I�T�G�J�T�\0�J5%��QgJ�)s.R�Ju#t�RE�%Q��q.��c��Kt�4����`���˂	U�NS\r'Ն�F�E�6I�K�(���\\\n@%H<��IL��<�s\"(%@(L	�Pq�\nC:aR��L�3��&rC�&SFZ�Y�0�>�ĸ@:53Md�Ի���R-4x 2I9G9iaGQ�g����)(�T�\0�FQt�K�	LDeӔD�kq�A�rAN@w�DB&5l;�ۉ8�sA\$�,h'�!U��\nA��\r�`(\$#@N���	��\n�Jx�04����O|�o��\$�Y����D�\r)Jƌ�Ϡׇ�\"(5����B��D���'9F�0��`�I��5��N��c�?E/���`=����K�0!c0\0Ҽ|��\$�H́F��0I�FJG&�\">��\0�u!�p�4m��\0F�r�L%HO��D)A�\0@�bX	b��d�/A�kl)А��OU�@6cM�b`�:���U:�ӲO������8�I���\ng#�T�� )����O\0�\0�`	�\0�(�i���� \\��D���\nu>'�T�� �?j��b~`&\0�`4�IBu	L�SAz�C�����i~�8z`!��?R�K-%��;53T�eL@�T�,9qU\\�� )=\0�D��A���`ɏ�<���&�@Tzc���(\n�;�\0��4Q�U5ԩU��+Բ�Wu.�KV-M5��`'��IV�s5(��e	�alUd?p�U_X\nZ�YyU�J�t�sWeK�^��[\0B�	�_f�MWJU~Cp��_5T��2\0 ���yX�;iPB̕�'qD�W-cQ9�*�LF�^��>p<\0�Ć�6�f3\\H,���YQ��}��<Ju��Y�f���%Y�g`6V{LM�M4�)0�C����j`9F갡�լ�Z�x�YV�Z�i�<�oZڜ5��NH�j�OV��m����[a�HP�Tmm���:j'\0<V�[�^ռ��0��!?\\ou���[�g���^mmUƀ�[C�u��Dmj��V��R\$��zga��Z�sࢫ��n�\r��&����+\0�.��]x�̆�ɽvc@Wk[�u��\0[[�P݃�]�v׉]���Z�:+�V�ey���������^�O�hV��<U�L�^�tM�\0O��	%\\���\\`�Q����[T��5���|5���]Aj�2%[S���\0�m��T[P8�o�+u\n�MV�UA�4)\\x<5�W�&�Z����)4�U��O�\0�]��,��`�C��Tppd��? ��7\0�\\x�͢\n�\r����X4>}jJoXf���W�S���J�it����b\r�v�n����ɀ�a{4@�Ta=vf�\0���E�a5�j�X�a\r���س`틑u�gaE��jX�Ռ�mX�`ŋ�Y�v�h]��XI`͍-��aѓD�\0�݊��X�c�V5�~2@\r\r&��d�VA��c݅�Ɔ�c�6G�]\0�5��D�vF4�aE�VLW�b��JX�aU�6���gi^�MdX���H��o��YGd଱�ȷe����X�e�vY�%[UgC�\n�N!%6\0G�0e�aM	�(�g�d�UvS�9d0\"QyN�_���g�Sf�]a�W�3�+��af ��r��)�M�Y�[�xa���!������zR����AY�\n�v+4Y�g�9zL�ݠv���6\0��n�mVx�iwu�/�\r�U���b���P�#cꝖ?�af�v�X��U����Ag��)�h����He���Y\rfxCR���i����Y\reM�`2F�eͧ��V��*�Y�i�]aTړbͨ���j}�v�XY���fکj]��uٲ��6�X�j���\"@\n��B\n��E���X�	����j�gv1F�}� 7J��+͢,\0�Q\0�;`B��Z�m�k��Z�~��bŮ����m��&��l�V�Y�l=}���c���5k���5g��`�Y�8�O6���lղ���-d\\b��Z�l�]v��mv5��m�����ؐ�\r�#?	g�V����T��@�)�]��\rx��k��>�k%\0;��d����ٮ�M65bL��X֭��SshE�֛\n`S%h'��Z ���kZ=��5�jmf���Z|o�Q<|`U߂(�76,ق�:��֛]�dpV�a�����mm�!��l��\$�E�p��\0�֨6E�Ad�������/�5u�79�,mi�Z�pzȗ	Y�:�Ģ>����b\\ip�77�f�{�q���܃p\\����0�����c5���4��6��_c��v�\\;d5ė+\\.���!�۟r��5�ܧoʷ\"9�h�	XQr� 70\\�q5���3Ir�9�M������ �W�A]\r��\\�t|�o\\_r�ʗ!��r��WF�7s���6]%tS�W>\0�s��wWOEj���\\�m��hZ�Qʣs�WS�9q���]_r`CW'�^u�7\\�]�ժ]qn-y�(Y�t�\"�^�a�]���aVgrmط^]�sUή����O��ݣZ��w]W���wl]2����XZ�tu�\0�	�tl6�օA�NQ�VcpͿv��3pj �P�vd�]�w%�# \\���<^v�{����v5���]U;q��Xi�j��^���~W@��l1�S�R�v����>�\n@���`ɞ��`�,PS�f�X*�\"��V��#��?�@��B��W^|�E�\0\"��y�	)���z2��\0�@:f�^Oy�3r��:n)���h,��D�\n��.�yr�����8�C���!^�z=귕�ƻ����f\n� ���Qc��q\"�\n�,���嗗�\n�'���!r��(��«f>� -\0�y�n\0/��L���/���I����\\�D\0�>����z�0��ս|��7���y�����y���_\\�U��^^\r��Ԁ�{\ng \$�	}����_c{����	S}�*�\0���8{}m�#��{y��ס(�z-�\0�{	 (�z�`&^v!r��TzW�^�Zg��\0�~E������n  ����7�ލ~��7��[}���_�}Q2�#��|f\0��_�\n�n!r�Ț����ެe��^�{��`�^�}���^��(���^�z6W�`~m�7�`'{���^�|��@��}���'_|]�_|���~�	ע߽z%����ꚨkX#\0��7����a�U� kC��������\0�|��\"�=��8\$ǩz���ˀkX��yM�7��6������}\r����zv §Ky�P�D��z@)����uI�}T���'ʞ�	�U/��*ɛ�n`kC��y��8N_���	�*��������y�z7���}MZ�#�\\%VXU_�	�uar���	U�N���C}M�W�a�Bz8`aW{��EaTv7ԧ�?2��ea@��X,^ܜ�fU�v�Ea��o���W���W�an�I\0���?����Hy�\nW�ǩyjf�r��?e�xz���������~�@_� �O�%���&c�T�?�w�M�}=�U<�g{@\\��'�y}�J����	x�&oS�\$\0)�M��Wݏ��Y\0�U����� �	�P�{\0���&��a�?�8���? 8�__�E��>bS���i��Р���?��HC�D�?@�@(bY{n���?��*���oy��8��\n��j��/�\\�bߪL�*X��m��I�ق�؀��Fm⋈>.��`�~/\0�\r��/��� �jɬ&z��R��=Ox��ޭ�f����`�������w�bӅ.J�.�X	8��\rTw��*{x��yDzx�ˇf68��鈖6�r���2�����XLǩ2����b}��%���a��8���QV�N��c?�b?\"t��b7��p)�_ܚ���m���~��&�?&9��`�z�* �~�P5A�B?�˴�0?�\nS,a=T�*�4����w�����x=ǩ�����(�w�U-�Ⳙ�����H��X*�.�L�T�&�V?��i����ⷉ�B��G��\$y	d3�FR/���ND��aߑ89_n����\0V?\0�X��i��2 *du��5�^����!�}V��aϒrj�*�C�8n\0���f��d���p���ݒ^I�\0��'������M1ᩒ�\0Y7d���/��d����''��\0�w���)^i�c����b�{�M\n��������_��1w�.�&0a�UV�f�B*�yB�	��K�\0\$�� �	�eE��[�oe����Ĺ~&X�AɄ��Ab�T��b���Iب�?nB�\0�^�B_��X�e��\r��'�ݖ2�	����e���/@�;��*������DU=d��'�i(u�����_O�X*��_ƺ�T	���	�J�˒�	��嗖[�jT�{�@8���7�����\n���0�\0&�T�7�d<�zc���kI�*�S�[�leZF* *\0�-Z�\0��^i�fG�.1i�fP\n�g��\0��l���ޥ��0����2�e�ۀ���kY��/��B���:�����dJq�Kf�?�g�L��j ��{Bj�m��)�f�.e͜+h����8o�v\n9-����\n�֓(?p\n�/�\$?m��_<��%9�����Nק���\"�/ǯ�oi��H�nJ�g�x�!���oٮ\n�(�_\$����B��~��|��͜L�@Hy�U��*zX��5�rfND�	��N?�5��'�>A�=��?#�a��TE[Ra-�N�S+q�����-��z5=呐ҹW��5�\n�8�bx~Y�c���ձ�fF�,\n����)��|��Ǟ�g	�+k���\0�H�*@ ��@Vy�����|ז\0���[���~�)c�b�yF~J�,?F�X�h��zq.����V?(�yRU�V�y�+j�\r��n�?��� #�V���h3�h	��\0i�f4�*\0q�f{��\0��>��\"���bjX�f<>����(b?b��'�zH	\0/�z��)�(b�~\$�՟�}�')��~c��͢�������n+�bp��.鞨b�\n�i�g��\nhs���zh`������f����h���L瓢2�j ��z-��(������h�L�����?\0�c���TY�⁤���͏�J܅@\$��\"f�dg埦�9��{*g�TiW!S�9QH���_�C��.��_E�^~x�������䡦�\$�C��B�d'>�	����:~y�ã>�n&p��B�-���c�_�~�{Xޏ匎��k�68�򀉃&m�R?v�m�3V�9�q��0	)�c�#:�#�d������  �	��[��iS��}����Vu8�US�<����N���ynt���{�j��^ِ���i�Ϩ�O�'���@#���ƀ�\"��gY����c�{撣�_թzp:��?�V��(?�jk #�����`P�~I�+kV���U'�*~Q.^��U��`F�����e�]�8�\n���قj�����aK�����ǫn?��'p�µ��ga����������(e��v�Շ��<�1>���9P\"@<ш����\$`d�9���g�\0��F&�\rq�\r1b\r���\$;@ڃ�-@��I�L܂�TP#�;%��aj��\r}2rM��eL�aR��������Ӄ��L��3I�����k�ard�k��zs�#L�IS��V�z�\0ۮ�i��k�B&��ψ#X.����l���f�{F :�S��0�3Ζj�`�W�濺������k�Ce��f*8�i|��أ}�oY�㧘��`\"\0�!P)��!P����d�\n͜hnFKc�^݈H����́>�)���	�����R�&����zXK����Plq�UR���F\\��u�BxR�O�F��H�v*}��lo��;'l������l�@H	)���J)�^{�%���D�O\0Ø��>�HJ�0�>�;���hUTBP�#��{8��\0?�:kqIa��]��Hк��H�{4�Jj]�\"��&���F�)\$E54�!e	��\0�\$����J��ޕKx�0���C,�RAK/˿�0��%�Q6y���r���P���،���\\���%��R	01��Έ]/(8�d�F���\0ƀd�ؙ�^�TۘL)5s�L\n���m�G#�����#\n+ 4�kM�D��9]N	�{R��*��>\0뵈5{U��kA��ƿĳA�9�����T�����T~\0�O�\0asπ��y���*ȕ����\"|r��Oc�y�:G�	��rz><�D&�\r��2H\rC\$������rWm�)�R]��eI���k�Z��a)����L��0���Tؤ�&;�8��mI�.�}��I�E����n�+4@�Je����MY��򯄔P��1݂Y����\0��IT�/����*�Mha4z�\r��[�낉���R��!on�/v�`4TDQ��VW��T����M����)��&�ۖn��Z��ϼ��Ik��}��Vg�Ƹ3��#9^��D�Q9k���k��f����N�\0�o9JD����g���Y������G*�՛�n���5�����nú�����k�kB���E�{�RHkZǌ��<�F�Z�=����j�K�/%|�3}��'R���o�')�Sۖ��i@6������f8n����VZY^�\0�?�8��o׷����W�\r��M0�\r�� ��Xb�ށ��LL\r��7�0�rDW�l���L�T8��0�k;����K\"+Q����\n��\$��u��y�=v�C[��1��� �%L�m���_�� ��6�	!.���[[ʵvL��,�}��	8�raxg������%��\$v�#�5a�����6R\"��!pO��6z�}I\"��>�ҿ�ܙA��O�q�9M��l�k�TԢ@\$��ȋ�tF�D��mb&|IDl��@�Ipo��W9uرF�5A��[�۴�&�Z��n�I{��8����5qe4��Yl�¬h�����@�qb%[y���C�c6�T'�6t�\\Zk�����0�����q�)/��q����:��;��AE\0�֫|qőƶ��Ηf�@�F�6�:\0��3#\\F��Lcn�m���������b���(�-�Q�l�`2k�#Tm�G%{�}=��k2�'\"�9S���2\"��+7!!,�.�|�#\"W�����U	�\$7�>�frP�&6�k�m��{�k�l�'Z������a����r��\0<��5�����8@2/�g)\\���ʏ)��k�l�'6�qO��\r��r���+�5Љ����r��E}ܵr���%|��ˏ-|�\0���*<��\r�t�|�r���&�<��pd�Hs�����S��D�����0:�I��)<���`kB�\n;@��WO-;/�x�N��5y(�N�E���õ�y\\	�P&��3H^���w?P�6f��uC�.)�v�ͼ��c���#�Y�u�'��!Z���\0�¡�(��p8������`0��,o�sy��QE��':������������W�r�qO���r�s�!_�[�Vaz#���:	zM�q	�9��kT������� ���w��R�2a	�W��������u� 4]�\rf������\\��\ntC�\0I�����!���Ϗ=P(�'��C\\�s���ӕ��M~#�D�(!�+�����X�\r��]��gI��I�|5��s��_J����GK;\\vN�¶�t����lG@Wm�D����OA7m�8 \"\0���Q��|,t�� 5=3��Գ��iU��>�}E7A#)D=�a	���\r��{O��BOQ2+s��:���g��=J����6u�M�O=��t�	�>��i������Ѽ�\0�b`�Y����[�Y �\$&��i��~���\0�e��\r`1bX�����g�5� ��bG)�GD{���0o`7S\r�?8�%����ѝf\0����qu�X����ͿY%5��y6�pe�8�脀�0`�e�:8xO����9d\n!.��~F�=jo��%����O֣�;PP�֧V��ᬐ+F;V�d*�rX�Յ���u��Ѕt3��Go\rQ�g\\��u�b�>�qs��1m�5�LrCt�҇`��vֺ=%�rK�vo\rgK`���?[Q�K4��9�|��m��Uڀ<���0/Z�%�5�8`���5ïY}b]+�Y�FW�\$\\C�Q5\0q�bp�pj�u��dн�u���i}p��#'m�W�8�oFW���qQ|1T���otd�AG�\\�&&07�Eќ��t�}�f�)Uҡ���,l�=ʂ.\n�uv��n�=UW��p�y�!�s�%wu�������+�ek�t�vvFW�]���wU�<���É��u_ek\rk��	���P\"\n�Y=qwy�`J�4Pj�T���5���qG)gp���R��gnbp�&җzP(п=��7w���I\$��!��X��v\\Y]�{G�ߠn��]��]�����;'X�4Hy߸Ϣ�J8'nrvg�'p��^({�>	\0�bW\\E�v�H�qV�,�y�b����^P�\rЇ�W���m�f��l�k�A>�(�3��7R|E�s�#Q��;Fr��2xresQ8A�@PtR	��]bmq �uժ�ޕw]qpL��Li\nEօ>3G���y������}�����N�lw��<��3w�ݵ�jF�0p�*u��,}~,�h��W���XT��s�8��}qs�/wU�Y��HR]�\r�Op���LQ��v�C@֘��s^<�[3��T#.��A|^��j~�� �\0����`i\0�Ǟ�i�+l�d�xMm�I�<�H���OI{C%��h�`t��wu%�Q:4��\\8ݔH�u>)�/u>�Z3��@�oS�b�@��/Ѽr����ןx!��v_\$H����(��I]��3�=��Ii�O�q��Y\\�}BtG6�\r��p]Ҷ�=,lf�\\����+�P�ݺ�v��7��αpDv���/�y��oe��e��Y�&�ba�9�#�O{S��i�wo���TL(�ot�^7_T�[�Ηi�N�k�]�����D>�	�謑A.k) ����Yi�K�m&W����Ɠ��xz������M4̂[�I�%M�g���wX1�7J�؛�N��mz��Z]��t�f����X�-���ui����͞��pd�<�ə.I��d�M.�66(bUv���\r��z��p���)���O�(�N������M�Uz�h�Y2����g��;�?�<��M��<�Z�?>J�q짴>�\0��D՝�TEߗ{b��-t�2g������w��߹�P����%/�B���o��T�{�Ӄ\\{�x��Z�Y��K�Y��[!.�:�ހY�(��.���h+�)�]|��Kv��.{�������=��t�?gK��z���4w��>�e�l�W>�ƾ��?P����v��b��냵吤�\0����i�>�>wu�;a6���k�����)�?Ž�mdm6��S��U���anC�z^�!�4�=���|~M��?���K�?#����O�O�M�8�3�ۯc�mN6bgI&tO�\"�,\r�9=�Ap#�'��^w�M����)�8T%w�v�^����<��Ŷ�\\O���}��\n��3�?Яh?E�Yє�!�����EaQ���C�3�o��^���gA,��WE���1�0%�XK4L����.�	^�|�)�o�auS�o�=��i��?@�y4w�4>\n��M���[�lF�cx~տ���E���}��2���:�4	#����,|���tqa�^�!��̘i\"�..��r��bg\\�����K��|�#g�}v�YX]!w,r�^�v���ݝI��H!�;��q��J�긒?z�Wח��{����������`I���7D�}��\0�u�Ә+�2~U�,~��~]�O 7ܜ�3E�W�_K����:����^s���_��P~=�ʵr��������`6��-T�a�H���	t��OS�1Hyn��\ntw�K���=���p��&��?�vu���>��M����2n\$��?LO9_�2A���ⲓi��I2�|��WK�уw]eϿ����u_�]?�������=�]�Jv����\$��E�U�#����ܷ?�Lu�f��i������\n�l����pm��X�>t�f\rx�������s����.�*O�(A�P?4��]�x\r^�E8nJRo\0¦�%[�\0004�v");
    } elseif ($_GET["file"] == "logo.png") {
        header("Content-Type: image/png");
        echo "�PNG\r\n\n\0\0\0\rIHDR\0\0\09\0\0\09\0\0\0~6��\0\0\0000PLTE\0\0\0���+NvYt�s���������������su�IJ����/.�������C��\0\0\0tRNS\0@��f\0\0\0	pHYs\0\0\0\0\0��\0\0�IDAT8�Ք�N�@��E��l϶��p6�G.\$=���>��	w5r}�z7�>��P�#\$��K�j�7��ݶ����?4m�����t&�~�3!0�0��^��Af0�\"��,��*��4���o�E���X(*Y��	6	�PcOW���܊m��r�0�~/��L�\rXj#�m���j�C�]G�m�\0�}���ߑu�A9�X�\n��8�V�Y�+�D#�iq�nKQ8J�1Q6��Y0�`��P�bQ�\\h�~>�:pSɀ������GE�Q=�I�{�*�3�2�7�\ne�L�B�~�/R(\$�)�� ��HQn�i�6J�	<��-.�w�ɪj�Vm���m�?S�H��v����Ʃ��\0��^�q��)���]��U�92�,;�Ǎ�'p���!X˃����L�D.�tæ��/w����R��	w�d��r2�Ƥ�4[=�E5�S+�c\0\0\0\0IEND�B`�";
    }
    exit;
}
if ($_GET["script"] == "version") {
    $n = get_temp_dir() . "/adminer.version";
    @unlink($n);
    $p = file_open_lock($n);
    if ($p) file_write_unlock($p, serialize(array("signature" => $_POST["signature"], "version" => $_POST["version"])));
    exit;
}
if (!$_SERVER["REQUEST_URI"]) $_SERVER["REQUEST_URI"] = $_SERVER["ORIG_PATH_INFO"];
if (!strpos($_SERVER["REQUEST_URI"], '?') && $_SERVER["QUERY_STRING"] != "") $_SERVER["REQUEST_URI"] .= "?$_SERVER[QUERY_STRING]";
if ($_SERVER["HTTP_X_FORWARDED_PREFIX"]) $_SERVER["REQUEST_URI"] = $_SERVER["HTTP_X_FORWARDED_PREFIX"] . $_SERVER["REQUEST_URI"];
define('Adminer\HTTPS', ($_SERVER["HTTPS"] && strcasecmp($_SERVER["HTTPS"], "off")) || ini_bool("session.cookie_secure"));
@ini_set("session.use_trans_sid", '0');
if (!defined("SID")) {
    session_cache_limiter("");
    session_name("adminer_sid");
    session_set_cookie_params(0, preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"]), "", HTTPS, true);
    session_start();
}
remove_slashes(array(&$_GET, &$_POST, &$_COOKIE), $Lc);
if (function_exists("get_magic_quotes_runtime") && get_magic_quotes_runtime()) set_magic_quotes_runtime(false);
@set_time_limit(0);
@ini_set("precision", '15');
function
lang($u, $df = null)
{
    $qa = func_get_args();
    $qa[0] = $u;
    return
        call_user_func_array('Adminer\lang_format', $qa);
}
function
lang_format($ci, $df = null)
{
    if (is_array($ci)) {
        $bg = ($df == 1 ? 0 : 1);
        $ci = $ci[$bg];
    }
    $ci = str_replace("'", '’', $ci);
    $qa = func_get_args();
    array_shift($qa);
    $Uc = str_replace("%d", "%s", $ci);
    if ($Uc != $ci) $qa[0] = format_number($df);
    return
        vsprintf($Uc, $qa);
}
define('Adminer\LANG', 'en');
abstract
class
SqlDb
{
    static $instance;
    var $extension;
    var $flavor = '';
    var $server_info;
    var $affected_rows = 0;
    var $info = '';
    var $errno = 0;
    var $error = '';
    protected $multi;
    abstract
    function
    attach($P, $V, $G);
    abstract
    function
    quote($sh);
    abstract
    function
    select_db($Bb);
    abstract
    function
    query($I, $ki = false);
    function
    multi_query($I)
    {
        return $this->multi = $this->query($I);
    }
    function
    store_result()
    {
        return $this->multi;
    }
    function
    next_result()
    {
        return
            false;
    }
}
if (extension_loaded('pdo')) {
    abstract
    class
    PdoDb
    extends
    SqlDb
    {
        protected $pdo;
        function
        dsn($Zb, $V, $G, array $sf = array())
        {
            $sf[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_SILENT;
            $sf[\PDO::ATTR_STATEMENT_CLASS] = array('Adminer\PdoResult');
            try {
                $this->pdo = new
                    \PDO($Zb, $V, $G, $sf);
            } catch (\Exception $vc) {
                return $vc->getMessage();
            }
            $this->server_info = @$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            return '';
        }
        function
        quote($sh)
        {
            return $this->pdo->quote($sh);
        }
        function
        query($I, $ki = false)
        {
            $J = $this->pdo->query($I);
            $this->error = "";
            if (!$J) {
                list(, $this->errno, $this->error) = $this->pdo->errorInfo();
                if (!$this->error) $this->error = 'Unknown error.';
                return
                    false;
            }
            $this->store_result($J);
            return $J;
        }
        function
        store_result($J = null)
        {
            if (!$J) {
                $J = $this->multi;
                if (!$J) return
                    false;
            }
            if ($J->columnCount()) {
                $J->num_rows = $J->rowCount();
                return $J;
            }
            $this->affected_rows = $J->rowCount();
            return
                true;
        }
        function
        next_result()
        {
            $J = $this->multi;
            if (!is_object($J)) return
                false;
            $J->_offset = 0;
            return @$J->nextRowset();
        }
    }
    class
    PdoResult
    extends
    \PDOStatement
    {
        var $_offset = 0, $num_rows;
        function
        fetch_assoc()
        {
            return $this->fetch_array(\PDO::FETCH_ASSOC);
        }
        function
        fetch_row()
        {
            return $this->fetch_array(\PDO::FETCH_NUM);
        }
        private
        function
        fetch_array($Oe)
        {
            $K = $this->fetch($Oe);
            return ($K ? array_map(array($this, 'unresource'), $K) : $K);
        }
        private
        function
        unresource($X)
        {
            return (is_resource($X) ? stream_get_contents($X) : $X);
        }
        function
        fetch_field()
        {
            $L = (object)$this->getColumnMeta($this->_offset++);
            $U = $L->pdo_type;
            $L->type = ($U == \PDO::PARAM_INT ? 0 : 15);
            $L->charsetnr = ($U == \PDO::PARAM_LOB || (isset($L->flags) && in_array("blob", (array)$L->flags)) ? 63 : 0);
            return $L;
        }
        function
        seek($ff)
        {
            for ($s = 0; $s < $ff; $s++) $this->fetch();
        }
    }
}
function
add_driver($t, $D)
{
    SqlDriver::$drivers[$t] = $D;
}
function
get_driver($t)
{
    return
        SqlDriver::$drivers[$t];
}
abstract
class
SqlDriver
{
    static $instance;
    static $drivers = array();
    static $extensions = array();
    static $jush;
    protected $conn;
    protected $types = array();
    var $insertFunctions = array();
    var $editFunctions = array();
    var $unsigned = array();
    var $operators = array();
    var $functions = array();
    var $grouping = array();
    var $onActions = "RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";
    var $partitionBy = array();
    var $inout = "IN|OUT|INOUT";
    var $enumLength = "'(?:''|[^'\\\\]|\\\\.)*'";
    var $generated = array();
    static
    function
    connect($P, $V, $G)
    {
        $e = new
            Db;
        return ($e->attach($P, $V, $G) ?: $e);
    }
    function
    __construct(Db $e)
    {
        $this->conn = $e;
    }
    function
    types()
    {
        return
            call_user_func_array('array_merge', array_values($this->types));
    }
    function
    structuredTypes()
    {
        return
            array_map('array_keys', $this->types);
    }
    function
    enumLength(array $l) {}
    function
    unconvertFunction(array $l) {}
    function
    select($R, array $O, array $Z, array $r, array $uf = array(), $z = 1, $E = 0, $jg = false)
    {
        $Vd = (count($r) < count($O));
        $I = adminer()->selectQueryBuild($O, $Z, $r, $uf, $z, $E);
        if (!$I) $I = "SELECT" . limit(($_GET["page"] != "last" && $z && $r && $Vd && JUSH == "sql" ? "SQL_CALC_FOUND_ROWS " : "") . implode(", ", $O) . "\nFROM " . table($R), ($Z ? "\nWHERE " . implode(" AND ", $Z) : "") . ($r && $Vd ? "\nGROUP BY " . implode(", ", $r) : "") . ($uf ? "\nORDER BY " . implode(", ", $uf) : ""), $z, ($E ? $z * $E : 0), "\n");
        $oh = microtime(true);
        $K = $this->conn->query($I);
        if ($jg) echo
        adminer()->selectQuery($I, $oh, !$K);
        return $K;
    }
    function
    delete($R, $rg, $z = 0)
    {
        $I = "FROM " . table($R);
        return
            queries("DELETE" . ($z ? limit1($R, $I, $rg) : " $I$rg"));
    }
    function
    update($R, array $Q, $rg, $z = 0, $Wg = "\n")
    {
        $_i = array();
        foreach (
            $Q
            as $y => $X
        ) $_i[] = "$y = $X";
        $I = table($R) . " SET$Wg" . implode(",$Wg", $_i);
        return
            queries("UPDATE" . ($z ? limit1($R, $I, $rg, $Wg) : " $I$rg"));
    }
    function
    insert($R, array $Q)
    {
        return
            queries("INSERT INTO " . table($R) . ($Q ? " (" . implode(", ", array_keys($Q)) . ")\nVALUES (" . implode(", ", $Q) . ")" : " DEFAULT VALUES") . $this->insertReturning($R));
    }
    function
    insertReturning($R)
    {
        return "";
    }
    function
    insertUpdate($R, array $M, array $ig)
    {
        return
            false;
    }
    function
    begin()
    {
        return
            queries("BEGIN");
    }
    function
    commit()
    {
        return
            queries("COMMIT");
    }
    function
    rollback()
    {
        return
            queries("ROLLBACK");
    }
    function
    slowQuery($I, $Ph) {}
    function
    convertSearch($u, array $X, array $l)
    {
        return $u;
    }
    function
    convertOperator($pf)
    {
        return $pf;
    }
    function
    value($X, array $l)
    {
        return (method_exists($this->conn, 'value') ? $this->conn->value($X, $l) : $X);
    }
    function
    quoteBinary($N)
    {
        return
            q($N);
    }
    function
    warnings() {}
    function
    tableHelp($D, $Zd = false) {}
    function
    inheritsFrom($R)
    {
        return
            array();
    }
    function
    inheritedTables($R)
    {
        return
            array();
    }
    function
    partitionsInfo($R)
    {
        return
            array();
    }
    function
    hasCStyleEscapes()
    {
        return
            false;
    }
    function
    engines()
    {
        return
            array();
    }
    function
    supportsIndex(array $S)
    {
        return !is_view($S);
    }
    function
    indexAlgorithms(array $_h)
    {
        return
            array();
    }
    function
    checkConstraints($R)
    {
        return
            get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME
WHERE c.CONSTRAINT_SCHEMA = " . q($_GET["ns"] != "" ? $_GET["ns"] : DB) . "
AND t.TABLE_NAME = " . q($R) . "
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'", $this->conn);
    }
    function
    allFields()
    {
        $K = array();
        if (DB != "") {
            foreach (
                get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length" . (JUSH == 'sql' ? ", COLUMN_KEY = 'PRI' AS `primary`" : "") . "
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = " . q($_GET["ns"] != "" ? $_GET["ns"] : DB) . "
ORDER BY TABLE_NAME, ORDINAL_POSITION", $this->conn) as $L
            ) {
                $L["null"] = ($L["nullable"] == "YES");
                $K[$L["tab"]][] = $L;
            }
        }
        return $K;
    }
}
class
Adminer
{
    static $instance;
    var $error = '';
    function
    name()
    {
        return "<a href='https://www.adminer.org/'" . target_blank() . " id='h1'><img src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=logo.png&version=5.3.0") . "' width='24' height='24' alt='' id='logo'>Adminer</a>";
    }
    function
    credentials()
    {
        return
            array(SERVER, $_GET["username"], get_password());
    }
    function
    connectSsl() {}
    function
    permanentLogin($g = false)
    {
        return
            password_file($g);
    }
    function
    bruteForceKey()
    {
        return $_SERVER["REMOTE_ADDR"];
    }
    function
    serverName($P)
    {
        return
            h($P);
    }
    function
    database()
    {
        return
            DB;
    }
    function
    databases($Pc = true)
    {
        return
            get_databases($Pc);
    }
    function
    pluginsLinks() {}
    function
    operators()
    {
        return
            driver()->operators;
    }
    function
    schemas()
    {
        return
            schemas();
    }
    function
    queryTimeout()
    {
        return
            2;
    }
    function
    headers() {}
    function
    csp(array $vb)
    {
        return $vb;
    }
    function
    head($zb = null)
    {
        return
            true;
    }
    function
    bodyClass()
    {
        echo " adminer";
    }
    function
    css()
    {
        $K = array();
        foreach (array("", "-dark") as $Oe) {
            $n = "adminer$Oe.css";
            if (file_exists($n)) {
                $Kc = file_get_contents($n);
                $K["$n?v=" . crc32($Kc)] = ($Oe ? "dark" : (preg_match('~prefers-color-scheme:\s*dark~', $Kc) ? '' : 'light'));
            }
        }
        return $K;
    }
    function
    loginForm()
    {
        echo "<table class='layout'>\n", adminer()->loginFormField('driver', '<tr><th>' . 'System' . '<td>', input_hidden("auth[driver]", "server") . "MySQL / MariaDB"), adminer()->loginFormField('server', '<tr><th>' . 'Server' . '<td>', '<input name="auth[server]" value="' . h(SERVER) . '" title="hostname[:port]" placeholder="localhost" autocapitalize="off">'), adminer()->loginFormField('username', '<tr><th>' . 'Username' . '<td>', '<input name="auth[username]" id="username" autofocus value="' . h($_GET["username"]) . '" autocomplete="username" autocapitalize="off">'), adminer()->loginFormField('password', '<tr><th>' . 'Password' . '<td>', '<input type="password" name="auth[password]" autocomplete="current-password">'), adminer()->loginFormField('db', '<tr><th>' . 'Database' . '<td>', '<input name="auth[db]" value="' . h($_GET["db"]) . '" autocapitalize="off">'), "</table>\n", "<p><input type='submit' value='" . 'Login' . "'>\n", checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], 'Permanent login') . "\n";
    }
    function
    loginFormField($D, $od, $Y)
    {
        return $od . $Y . "\n";
    }
    function
    login($te, $G)
    {
        if ($G == "") return
            sprintf('Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.', target_blank());
        return
            true;
    }
    function
    tableName(array $_h)
    {
        return
            h($_h["Name"]);
    }
    function
    fieldName(array $l, $uf = 0)
    {
        $U = $l["full_type"];
        $fb = $l["comment"];
        return '<span title="' . h($U . ($fb != "" ? ($U ? ": " : "") . $fb : '')) . '">' . h($l["field"]) . '</span>';
    }
    function
    selectLinks(array $_h, $Q = "")
    {
        $D = $_h["Name"];
        echo '<p class="links">';
        $se = array("select" => 'Select data');
        if (support("table") || support("indexes")) $se["table"] = 'Show structure';
        $Zd = false;
        if (support("table")) {
            $Zd = is_view($_h);
            if ($Zd) $se["view"] = 'Alter view';
            else $se["create"] = 'Alter table';
        }
        if ($Q !== null) $se["edit"] = 'New item';
        foreach (
            $se
            as $y => $X
        ) echo " <a href='" . h(ME) . "$y=" . urlencode($D) . ($y == "edit" ? $Q : "") . "'" . bold(isset($_GET[$y])) . ">$X</a>";
        echo
        doc_link(array(JUSH => driver()->tableHelp($D, $Zd)), "?"), "\n";
    }
    function
    foreignKeys($R)
    {
        return
            foreign_keys($R);
    }
    function
    backwardKeys($R, $zh)
    {
        return
            array();
    }
    function
    backwardKeysPrint(array $_a, array $L) {}
    function
    selectQuery($I, $oh, $Fc = false)
    {
        $K = "</p>\n";
        if (!$Fc && ($Hi = driver()->warnings())) {
            $t = "warnings";
            $K = ", <a href='#$t'>" . 'Warnings' . "</a>" . script("qsl('a').onclick = partial(toggle, '$t');", "") . "$K<div id='$t' class='hidden'>\n$Hi</div>\n";
        }
        return "<p><code class='jush-" . JUSH . "'>" . h(str_replace("\n", " ", $I)) . "</code> <span class='time'>(" . format_time($oh) . ")</span>" . (support("sql") ? " <a href='" . h(ME) . "sql=" . urlencode($I) . "'>" . 'Edit' . "</a>" : "") . $K;
    }
    function
    sqlCommandQuery($I)
    {
        return
            shorten_utf8(trim($I), 1000);
    }
    function
    sqlPrintAfter() {}
    function
    rowDescription($R)
    {
        return "";
    }
    function
    rowDescriptions(array $M, array $Sc)
    {
        return $M;
    }
    function
    selectLink($X, array $l) {}
    function
    selectVal($X, $_, array $l, $Df)
    {
        $K = ($X === null ? "<i>NULL</i>" : (preg_match("~char|binary|boolean~", $l["type"]) && !preg_match("~var~", $l["type"]) ? "<code>$X</code>" : (preg_match('~json~', $l["type"]) ? "<code class='jush-js'>$X</code>" : $X)));
        if (preg_match('~blob|bytea|raw|file~', $l["type"]) && !is_utf8($X)) $K = "<i>" . lang_format(array('%d byte', '%d bytes'), strlen($Df)) . "</i>";
        return ($_ ? "<a href='" . h($_) . "'" . (is_url($_) ? target_blank() : "") . ">$K</a>" : $K);
    }
    function
    editVal($X, array $l)
    {
        return $X;
    }
    function
    config()
    {
        return
            array();
    }
    function
    tableStructurePrint(array $m, $_h = null)
    {
        echo "<div class='scrollable'>\n", "<table class='nowrap odds'>\n", "<thead><tr><th>" . 'Column' . "<td>" . 'Type' . (support("comment") ? "<td>" . 'Comment' : "") . "</thead>\n";
        $th = driver()->structuredTypes();
        foreach (
            $m
            as $l
        ) {
            echo "<tr><th>" . h($l["field"]);
            $U = h($l["full_type"]);
            $bb = h($l["collation"]);
            echo "<td><span title='$bb'>" . (in_array($U, (array)$th['User types']) ? "<a href='" . h(ME . 'type=' . urlencode($U)) . "'>$U</a>" : $U . ($bb && isset($_h["Collation"]) && $bb != $_h["Collation"] ? " $bb" : "")) . "</span>", ($l["null"] ? " <i>NULL</i>" : ""), ($l["auto_increment"] ? " <i>" . 'Auto Increment' . "</i>" : "");
            $j = h($l["default"]);
            echo (isset($l["default"]) ? " <span title='" . 'Default value' . "'>[<b>" . ($l["generated"] ? "<code class='jush-" . JUSH . "'>$j</code>" : $j) . "</b>]</span>" : ""), (support("comment") ? "<td>" . h($l["comment"]) : ""), "\n";
        }
        echo "</table>\n", "</div>\n";
    }
    function
    tableIndexesPrint(array $w, array $_h)
    {
        $Lf = false;
        foreach (
            $w
            as $D => $v
        ) $Lf |= !!$v["partial"];
        echo "<table>\n";
        $Gb = first(driver()->indexAlgorithms($_h));
        foreach (
            $w
            as $D => $v
        ) {
            ksort($v["columns"]);
            $jg = array();
            foreach ($v["columns"] as $y => $X) $jg[] = "<i>" . h($X) . "</i>" . ($v["lengths"][$y] ? "(" . $v["lengths"][$y] . ")" : "") . ($v["descs"][$y] ? " DESC" : "");
            echo "<tr title='" . h($D) . "'>", "<th>$v[type]" . ($Gb && $v['algorithm'] != $Gb ? " ($v[algorithm])" : ""), "<td>" . implode(", ", $jg);
            if ($Lf) echo "<td>" . ($v['partial'] ? "<code class='jush-" . JUSH . "'>WHERE " . h($v['partial']) : "");
            echo "\n";
        }
        echo "</table>\n";
    }
    function
    selectColumnsPrint(array $O, array $d)
    {
        print_fieldset("select", 'Select', $O);
        $s = 0;
        $O[""] = array();
        foreach (
            $O
            as $y => $X
        ) {
            $X = idx($_GET["columns"], $y, array());
            $c = select_input(" name='columns[$s][col]'", $d, $X["col"], ($y !== "" ? "selectFieldChange" : "selectAddRow"));
            echo "<div>" . (driver()->functions || driver()->grouping ? html_select("columns[$s][fun]", array(-1 => "") + array_filter(array('Functions' => driver()->functions, 'Aggregation' => driver()->grouping)), $X["fun"]) . on_help("event.target.value && event.target.value.replace(/ |\$/, '(') + ')'", 1) . script("qsl('select').onchange = function () { helpClose();" . ($y !== "" ? "" : " qsl('select, input', this.parentNode).onchange();") . " };", "") . "($c)" : $c) . "</div>\n";
            $s++;
        }
        echo "</div></fieldset>\n";
    }
    function
    selectSearchPrint(array $Z, array $d, array $w)
    {
        print_fieldset("search", 'Search', $Z);
        foreach (
            $w
            as $s => $v
        ) {
            if ($v["type"] == "FULLTEXT") echo "<div>(<i>" . implode("</i>, <i>", array_map('Adminer\h', $v["columns"])) . "</i>) AGAINST", " <input type='search' name='fulltext[$s]' value='" . h(idx($_GET["fulltext"], $s)) . "'>", script("qsl('input').oninput = selectFieldChange;", ""), checkbox("boolean[$s]", 1, isset($_GET["boolean"][$s]), "BOOL"), "</div>\n";
        }
        $Na = "this.parentNode.firstChild.onchange();";
        foreach (array_merge((array)$_GET["where"], array(array())) as $s => $X) {
            if (!$X || ("$X[col]$X[val]" != "" && in_array($X["op"], adminer()->operators()))) echo "<div>" . select_input(" name='where[$s][col]'", $d, $X["col"], ($X ? "selectFieldChange" : "selectAddRow"), "(" . 'anywhere' . ")"), html_select("where[$s][op]", adminer()->operators(), $X["op"], $Na), "<input type='search' name='where[$s][val]' value='" . h($X["val"]) . "'>", script("mixin(qsl('input'), {oninput: function () { $Na }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});", ""), "</div>\n";
        }
        echo "</div></fieldset>\n";
    }
    function
    selectOrderPrint(array $uf, array $d, array $w)
    {
        print_fieldset("sort", 'Sort', $uf);
        $s = 0;
        foreach ((array)$_GET["order"] as $y => $X) {
            if ($X != "") {
                echo "<div>" . select_input(" name='order[$s]'", $d, $X, "selectFieldChange"), checkbox("desc[$s]", 1, isset($_GET["desc"][$y]), 'descending') . "</div>\n";
                $s++;
            }
        }
        echo "<div>" . select_input(" name='order[$s]'", $d, "", "selectAddRow"), checkbox("desc[$s]", 1, false, 'descending') . "</div>\n", "</div></fieldset>\n";
    }
    function
    selectLimitPrint($z)
    {
        echo "<fieldset><legend>" . 'Limit' . "</legend><div>", "<input type='number' name='limit' class='size' value='" . intval($z) . "'>", script("qsl('input').oninput = selectFieldChange;", ""), "</div></fieldset>\n";
    }
    function
    selectLengthPrint($Nh)
    {
        if ($Nh !== null) echo "<fieldset><legend>" . 'Text length' . "</legend><div>", "<input type='number' name='text_length' class='size' value='" . h($Nh) . "'>", "</div></fieldset>\n";
    }
    function
    selectActionPrint(array $w)
    {
        echo "<fieldset><legend>" . 'Action' . "</legend><div>", "<input type='submit' value='" . 'Select' . "'>", " <span id='noindex' title='" . 'Full table scan' . "'></span>", "<script" . nonce() . ">\n", "const indexColumns = ";
        $d = array();
        foreach (
            $w
            as $v
        ) {
            $yb = reset($v["columns"]);
            if ($v["type"] != "FULLTEXT" && $yb) $d[$yb] = 1;
        }
        $d[""] = 1;
        foreach (
            $d
            as $y => $X
        ) json_row($y);
        echo ";\n", "selectFieldChange.call(qs('#form')['select']);\n", "</script>\n", "</div></fieldset>\n";
    }
    function
    selectCommandPrint()
    {
        return !information_schema(DB);
    }
    function
    selectImportPrint()
    {
        return !information_schema(DB);
    }
    function
    selectEmailPrint(array $hc, array $d) {}
    function
    selectColumnsProcess(array $d, array $w)
    {
        $O = array();
        $r = array();
        foreach ((array)$_GET["columns"] as $y => $X) {
            if ($X["fun"] == "count" || ($X["col"] != "" && (!$X["fun"] || in_array($X["fun"], driver()->functions) || in_array($X["fun"], driver()->grouping)))) {
                $O[$y] = apply_sql_function($X["fun"], ($X["col"] != "" ? idf_escape($X["col"]) : "*"));
                if (!in_array($X["fun"], driver()->grouping)) $r[] = $O[$y];
            }
        }
        return
            array($O, $r);
    }
    function
    selectSearchProcess(array $m, array $w)
    {
        $K = array();
        foreach (
            $w
            as $s => $v
        ) {
            if ($v["type"] == "FULLTEXT" && idx($_GET["fulltext"], $s) != "") $K[] = "MATCH (" . implode(", ", array_map('Adminer\idf_escape', $v["columns"])) . ") AGAINST (" . q($_GET["fulltext"][$s]) . (isset($_GET["boolean"][$s]) ? " IN BOOLEAN MODE" : "") . ")";
        }
        foreach ((array)$_GET["where"] as $y => $X) {
            $Za = $X["col"];
            if ("$Za$X[val]" != "" && in_array($X["op"], adminer()->operators())) {
                $jb = array();
                foreach (($Za != "" ? array($Za => $m[$Za]) : $m) as $D => $l) {
                    $gg = "";
                    $ib = " $X[op]";
                    if (preg_match('~IN$~', $X["op"])) {
                        $Bd = process_length($X["val"]);
                        $ib
                            .= " " . ($Bd != "" ? $Bd : "(NULL)");
                    } elseif ($X["op"] == "SQL") $ib = " $X[val]";
                    elseif (preg_match('~^(I?LIKE) %%$~', $X["op"], $B)) $ib = " $B[1] " . adminer()->processInput($l, "%$X[val]%");
                    elseif ($X["op"] == "FIND_IN_SET") {
                        $gg = "$X[op](" . q($X["val"]) . ", ";
                        $ib = ")";
                    } elseif (!preg_match('~NULL$~', $X["op"])) $ib
                        .= " " . adminer()->processInput($l, $X["val"]);
                    if ($Za != "" || (isset($l["privileges"]["where"]) && (preg_match('~^[-\d.' . (preg_match('~IN$~', $X["op"]) ? ',' : '') . ']+$~', $X["val"]) || !preg_match('~' . number_type() . '|bit~', $l["type"])) && (!preg_match("~[\x80-\xFF]~", $X["val"]) || preg_match('~char|text|enum|set~', $l["type"])) && (!preg_match('~date|timestamp~', $l["type"]) || preg_match('~^\d+-\d+-\d+~', $X["val"])))) $jb[] = $gg . driver()->convertSearch(idf_escape($D), $X, $l) . $ib;
                }
                $K[] = (count($jb) == 1 ? $jb[0] : ($jb ? "(" . implode(" OR ", $jb) . ")" : "1 = 0"));
            }
        }
        return $K;
    }
    function
    selectOrderProcess(array $m, array $w)
    {
        $K = array();
        foreach ((array)$_GET["order"] as $y => $X) {
            if ($X != "") $K[] = (preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~', $X) ? $X : idf_escape($X)) . (isset($_GET["desc"][$y]) ? " DESC" : "");
        }
        return $K;
    }
    function
    selectLimitProcess()
    {
        return (isset($_GET["limit"]) ? intval($_GET["limit"]) : 50);
    }
    function
    selectLengthProcess()
    {
        return (isset($_GET["text_length"]) ? "$_GET[text_length]" : "100");
    }
    function
    selectEmailProcess(array $Z, array $Sc)
    {
        return
            false;
    }
    function
    selectQueryBuild(array $O, array $Z, array $r, array $uf, $z, $E)
    {
        return "";
    }
    function
    messageQuery($I, $Oh, $Fc = false)
    {
        restart_session();
        $qd = &get_session("queries");
        if (!idx($qd, $_GET["db"])) $qd[$_GET["db"]] = array();
        if (strlen($I) > 1e6) $I = preg_replace('~[\x80-\xFF]+$~', '', substr($I, 0, 1e6)) . "\n…";
        $qd[$_GET["db"]][] = array($I, time(), $Oh);
        $lh = "sql-" . count($qd[$_GET["db"]]);
        $K = "<a href='#$lh' class='toggle'>" . 'SQL command' . "</a>\n";
        if (!$Fc && ($Hi = driver()->warnings())) {
            $t = "warnings-" . count($qd[$_GET["db"]]);
            $K = "<a href='#$t' class='toggle'>" . 'Warnings' . "</a>, $K<div id='$t' class='hidden'>\n$Hi</div>\n";
        }
        return " <span class='time'>" . @date("H:i:s") . "</span>" . " $K<div id='$lh' class='hidden'><pre><code class='jush-" . JUSH . "'>" . shorten_utf8($I, 1000) . "</code></pre>" . ($Oh ? " <span class='time'>($Oh)</span>" : '') . (support("sql") ? '<p><a href="' . h(str_replace("db=" . urlencode(DB), "db=" . urlencode($_GET["db"]), ME) . 'sql=&history=' . (count($qd[$_GET["db"]]) - 1)) . '">' . 'Edit' . '</a>' : '') . '</div>';
    }
    function
    editRowPrint($R, array $m, $L, $ri) {}
    function
    editFunctions(array $l)
    {
        $K = ($l["null"] ? "NULL/" : "");
        $ri = isset($_GET["select"]) || where($_GET);
        foreach (array(driver()->insertFunctions, driver()->editFunctions) as $y => $Zc) {
            if (!$y || (!isset($_GET["call"]) && $ri)) {
                foreach (
                    $Zc
                    as $Uf => $X
                ) {
                    if (!$Uf || preg_match("~$Uf~", $l["type"])) $K
                        .= "/$X";
                }
            }
            if ($y && $Zc && !preg_match('~set|blob|bytea|raw|file|bool~', $l["type"])) $K
                .= "/SQL";
        }
        if ($l["auto_increment"] && !$ri) $K = 'Auto Increment';
        return
            explode("/", $K);
    }
    function
    editInput($R, array $l, $ua, $Y)
    {
        if ($l["type"] == "enum") return (isset($_GET["select"]) ? "<label><input type='radio'$ua value='-1' checked><i>" . 'original' . "</i></label> " : "") . ($l["null"] ? "<label><input type='radio'$ua value=''" . ($Y !== null || isset($_GET["select"]) ? "" : " checked") . "><i>NULL</i></label> " : "") . enum_input("radio", $ua, $l, $Y, $Y === 0 ? 0 : null);
        return "";
    }
    function
    editHint($R, array $l, $Y)
    {
        return "";
    }
    function
    processInput(array $l, $Y, $q = "")
    {
        if ($q == "SQL") return $Y;
        $D = $l["field"];
        $K = q($Y);
        if (preg_match('~^(now|getdate|uuid)$~', $q)) $K = "$q()";
        elseif (preg_match('~^current_(date|timestamp)$~', $q)) $K = $q;
        elseif (preg_match('~^([+-]|\|\|)$~', $q)) $K = idf_escape($D) . " $q $K";
        elseif (preg_match('~^[+-] interval$~', $q)) $K = idf_escape($D) . " $q " . (preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i", $Y) ? $Y : $K);
        elseif (preg_match('~^(addtime|subtime|concat)$~', $q)) $K = "$q(" . idf_escape($D) . ", $K)";
        elseif (preg_match('~^(md5|sha1|password|encrypt)$~', $q)) $K = "$q($K)";
        return
            unconvert_field($l, $K);
    }
    function
    dumpOutput()
    {
        $K = array('text' => 'open', 'file' => 'save');
        if (function_exists('gzencode')) $K['gz'] = 'gzip';
        return $K;
    }
    function
    dumpFormat()
    {
        return (support("dump") ? array('sql' => 'SQL') : array()) + array('csv' => 'CSV,', 'csv;' => 'CSV;', 'tsv' => 'TSV');
    }
    function
    dumpDatabase($i) {}
    function
    dumpTable($R, $uh, $Zd = 0)
    {
        if ($_POST["format"] != "sql") {
            echo "\xef\xbb\xbf";
            if ($uh) dump_csv(array_keys(fields($R)));
        } else {
            if ($Zd == 2) {
                $m = array();
                foreach (fields($R) as $D => $l) $m[] = idf_escape($D) . " $l[full_type]";
                $g = "CREATE TABLE " . table($R) . " (" . implode(", ", $m) . ")";
            } else $g = create_sql($R, $_POST["auto_increment"], $uh);
            set_utf8mb4($g);
            if ($uh && $g) {
                if ($uh == "DROP+CREATE" || $Zd == 1) echo "DROP " . ($Zd == 2 ? "VIEW" : "TABLE") . " IF EXISTS " . table($R) . ";\n";
                if ($Zd == 1) $g = remove_definer($g);
                echo "$g;\n\n";
            }
        }
    }
    function
    dumpData($R, $uh, $I)
    {
        if ($uh) {
            $_e = (JUSH == "sqlite" ? 0 : 1048576);
            $m = array();
            $yd = false;
            if ($_POST["format"] == "sql") {
                if ($uh == "TRUNCATE+INSERT") echo
                truncate_sql($R) . ";\n";
                $m = fields($R);
                if (JUSH == "mssql") {
                    foreach (
                        $m
                        as $l
                    ) {
                        if ($l["auto_increment"]) {
                            echo "SET IDENTITY_INSERT " . table($R) . " ON;\n";
                            $yd = true;
                            break;
                        }
                    }
                }
            }
            $J = connection()->query($I, 1);
            if ($J) {
                $Od = "";
                $Ja = "";
                $ce = array();
                $ad = array();
                $wh = "";
                $Ic = ($R != '' ? 'fetch_assoc' : 'fetch_row');
                $rb = 0;
                while ($L = $J->$Ic()) {
                    if (!$ce) {
                        $_i = array();
                        foreach (
                            $L
                            as $X
                        ) {
                            $l = $J->fetch_field();
                            if (idx($m[$l->name], 'generated')) {
                                $ad[$l->name] = true;
                                continue;
                            }
                            $ce[] = $l->name;
                            $y = idf_escape($l->name);
                            $_i[] = "$y = VALUES($y)";
                        }
                        $wh = ($uh == "INSERT+UPDATE" ? "\nON DUPLICATE KEY UPDATE " . implode(", ", $_i) : "") . ";\n";
                    }
                    if ($_POST["format"] != "sql") {
                        if ($uh == "table") {
                            dump_csv($ce);
                            $uh = "INSERT";
                        }
                        dump_csv($L);
                    } else {
                        if (!$Od) $Od = "INSERT INTO " . table($R) . " (" . implode(", ", array_map('Adminer\idf_escape', $ce)) . ") VALUES";
                        foreach (
                            $L
                            as $y => $X
                        ) {
                            if ($ad[$y]) {
                                unset($L[$y]);
                                continue;
                            }
                            $l = $m[$y];
                            $L[$y] = ($X !== null ? unconvert_field($l, preg_match(number_type(), $l["type"]) && !preg_match('~\[~', $l["full_type"]) && is_numeric($X) ? $X : q(($X === false ? 0 : $X))) : "NULL");
                        }
                        $N = ($_e ? "\n" : " ") . "(" . implode(",\t", $L) . ")";
                        if (!$Ja) $Ja = $Od . $N;
                        elseif (JUSH == 'mssql' ? $rb % 1000 != 0 : strlen($Ja) + 4 + strlen($N) + strlen($wh) < $_e) $Ja
                            .= ",$N";
                        else {
                            echo $Ja . $wh;
                            $Ja = $Od . $N;
                        }
                    }
                    $rb++;
                }
                if ($Ja) echo $Ja . $wh;
            } elseif ($_POST["format"] == "sql") echo "-- " . str_replace("\n", " ", connection()->error) . "\n";
            if ($yd) echo "SET IDENTITY_INSERT " . table($R) . " OFF;\n";
        }
    }
    function
    dumpFilename($xd)
    {
        return
            friendly_url($xd != "" ? $xd : (SERVER != "" ? SERVER : "localhost"));
    }
    function
    dumpHeaders($xd, $Qe = false)
    {
        $Ff = $_POST["output"];
        $Ac = (preg_match('~sql~', $_POST["format"]) ? "sql" : ($Qe ? "tar" : "csv"));
        header("Content-Type: " . ($Ff == "gz" ? "application/x-gzip" : ($Ac == "tar" ? "application/x-tar" : ($Ac == "sql" || $Ff != "file" ? "text/plain" : "text/csv") . "; charset=utf-8")));
        if ($Ff == "gz") {
            ob_start(function ($sh) {
                return
                    gzencode($sh);
            }, 1e6);
        }
        return $Ac;
    }
    function
    dumpFooter()
    {
        if ($_POST["format"] == "sql") echo "-- " . gmdate("Y-m-d H:i:s e") . "\n";
    }
    function
    importServerPath()
    {
        return "adminer.sql";
    }
    function
    homepage()
    {
        echo '<p class="links">' . ($_GET["ns"] == "" && support("database") ? '<a href="' . h(ME) . 'database=">' . 'Alter database' . "</a>\n" : ""), (support("scheme") ? "<a href='" . h(ME) . "scheme='>" . ($_GET["ns"] != "" ? 'Alter schema' : 'Create schema') . "</a>\n" : ""), ($_GET["ns"] !== "" ? '<a href="' . h(ME) . 'schema=">' . 'Database schema' . "</a>\n" : ""), (support("privileges") ? "<a href='" . h(ME) . "privileges='>" . 'Privileges' . "</a>\n" : "");
        return
            true;
    }
    function
    navigation($Ne)
    {
        echo "<h1>" . adminer()->name() . " <span class='version'>" . VERSION;
        $Ye = $_COOKIE["adminer_version"];
        echo " <a href='https://www.adminer.org/#download'" . target_blank() . " id='version'>" . (version_compare(VERSION, $Ye) < 0 ? h($Ye) : "") . "</a>", "</span></h1>\n";
        if ($Ne == "auth") {
            $Ff = "";
            foreach ((array)$_SESSION["pwds"] as $Bi => $Yg) {
                foreach (
                    $Yg
                    as $P => $yi
                ) {
                    $D = h(get_setting("vendor-$Bi-$P") ?: get_driver($Bi));
                    foreach (
                        $yi
                        as $V => $G
                    ) {
                        if ($G !== null) {
                            $Eb = $_SESSION["db"][$Bi][$P][$V];
                            foreach (($Eb ? array_keys($Eb) : array("")) as $i) $Ff
                                .= "<li><a href='" . h(auth_url($Bi, $P, $V, $i)) . "'>($D) " . h($V . ($P != "" ? "@" . adminer()->serverName($P) : "") . ($i != "" ? " - $i" : "")) . "</a>\n";
                        }
                    }
                }
            }
            if ($Ff) echo "<ul id='logins'>\n$Ff</ul>\n" . script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");
        } else {
            $T = array();
            if ($_GET["ns"] !== "" && !$Ne && DB != "") {
                connection()->select_db(DB);
                $T = table_status('', true);
            }
            adminer()->syntaxHighlighting($T);
            adminer()->databasesPrint($Ne);
            $fa = array();
            if (DB == "" || !$Ne) {
                if (support("sql")) {
                    $fa[] = "<a href='" . h(ME) . "sql='" . bold(isset($_GET["sql"]) && !isset($_GET["import"])) . ">" . 'SQL command' . "</a>";
                    $fa[] = "<a href='" . h(ME) . "import='" . bold(isset($_GET["import"])) . ">" . 'Import' . "</a>";
                }
                $fa[] = "<a href='" . h(ME) . "dump=" . urlencode(isset($_GET["table"]) ? $_GET["table"] : $_GET["select"]) . "' id='dump'" . bold(isset($_GET["dump"])) . ">" . 'Export' . "</a>";
            }
            $Cd = $_GET["ns"] !== "" && !$Ne && DB != "";
            if ($Cd) $fa[] = '<a href="' . h(ME) . 'create="' . bold($_GET["create"] === "") . ">" . 'Create table' . "</a>";
            echo ($fa ? "<p class='links'>\n" . implode("\n", $fa) . "\n" : "");
            if ($Cd) {
                if ($T) adminer()->tablesPrint($T);
                else
                    echo "<p class='message'>" . 'No tables.' . "</p>\n";
            }
        }
    }
    function
    syntaxHighlighting(array $T)
    {
        echo
        script_src(preg_replace("~\\?.*~", "", ME) . "?file=jush.js&version=5.3.0", true);
        if (support("sql")) {
            echo "<script" . nonce() . ">\n";
            if ($T) {
                $se = array();
                foreach (
                    $T
                    as $R => $U
                ) $se[] = preg_quote($R, '/');
                echo "var jushLinks = { " . JUSH . ": [ '" . js_escape(ME) . (support("table") ? "table=" : "select=") . "\$&', /\\b(" . implode("|", $se) . ")\\b/g ] };\n";
                foreach (array("bac", "bra", "sqlite_quo", "mssql_bra") as $X) echo "jushLinks.$X = jushLinks." . JUSH . ";\n";
                if (isset($_GET["sql"]) || isset($_GET["trigger"]) || isset($_GET["check"])) {
                    $Eh = array_fill_keys(array_keys($T), array());
                    foreach (driver()->allFields() as $R => $m) {
                        foreach (
                            $m
                            as $l
                        ) $Eh[$R][] = $l["field"];
                    }
                    echo "addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('" . idf_escape("") . "', " . json_encode($Eh) . "); });\n";
                }
            }
            echo "</script>\n";
        }
        echo
        script("syntaxHighlighting('" . preg_replace('~^(\d\.?\d).*~s', '\1', connection()->server_info) . "', '" . connection()->flavor . "');");
    }
    function
    databasesPrint($Ne)
    {
        $h = adminer()->databases();
        if (DB && $h && !in_array(DB, $h)) array_unshift($h, DB);
        echo "<form action=''>\n<p id='dbs'>\n";
        hidden_fields_get();
        $Cb = script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");
        echo "<label title='" . 'Database' . "'>" . 'DB' . ": " . ($h ? html_select("db", array("" => "") + $h, DB) . $Cb : "<input name='db' value='" . h(DB) . "' autocapitalize='off' size='19'>\n") . "</label>", "<input type='submit' value='" . 'Use' . "'" . ($h ? " class='hidden'" : "") . ">\n";
        foreach (array("import", "sql", "schema", "dump", "privileges") as $X) {
            if (isset($_GET[$X])) {
                echo
                input_hidden($X);
                break;
            }
        }
        echo "</p></form>\n";
    }
    function
    tablesPrint(array $T)
    {
        echo "<ul id='tables'>" . script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");
        foreach (
            $T
            as $R => $ph
        ) {
            $R = "$R";
            $D = adminer()->tableName($ph);
            if ($D != "" && !$ph["inherited"]) echo '<li><a href="' . h(ME) . 'select=' . urlencode($R) . '"' . bold($_GET["select"] == $R || $_GET["edit"] == $R, "select") . " title='" . 'Select data' . "'>" . 'select' . "</a> ", (support("table") || support("indexes") ? '<a href="' . h(ME) . 'table=' . urlencode($R) . '"' . bold(in_array($R, array($_GET["table"], $_GET["create"], $_GET["indexes"], $_GET["foreign"], $_GET["trigger"], $_GET["check"], $_GET["view"])), (is_view($ph) ? "view" : "structure")) . " title='" . 'Show structure' . "'>$D</a>" : "<span>$D</span>") . "\n";
        }
        echo "</ul>\n";
    }
}
class
Plugins
{
    private
    static $append = array('dumpFormat' => true, 'dumpOutput' => true, 'editRowPrint' => true, 'editFunctions' => true, 'config' => true);
    var $plugins;
    var $error = '';
    private $hooks = array();
    function
    __construct($Zf)
    {
        if ($Zf === null) {
            $Zf = array();
            $Da = "adminer-plugins";
            if (is_dir($Da)) {
                foreach (glob("$Da/*.php") as $n) $Dd = include_once "./$n";
            }
            $pd = " href='https://www.adminer.org/plugins/#use'" . target_blank();
            if (file_exists("$Da.php")) {
                $Dd = include_once "./$Da.php";
                if (is_array($Dd)) {
                    foreach (
                        $Dd
                        as $Yf
                    ) $Zf[get_class($Yf)] = $Yf;
                } else $this->error
                    .= sprintf('%s must <a%s>return an array</a>.', "<b>$Da.php</b>", $pd) . "<br>";
            }
            foreach (get_declared_classes() as $Wa) {
                if (!$Zf[$Wa] && preg_match('~^Adminer\w~i', $Wa)) {
                    $_g = new
                        \ReflectionClass($Wa);
                    $lb = $_g->getConstructor();
                    if ($lb && $lb->getNumberOfRequiredParameters()) $this->error
                        .= sprintf('<a%s>Configure</a> %s in %s.', $pd, "<b>$Wa</b>", "<b>$Da.php</b>") . "<br>";
                    else $Zf[$Wa] = new $Wa;
                }
            }
        }
        $this->plugins = $Zf;
        $ga = new
            Adminer;
        $Zf[] = $ga;
        $_g = new
            \ReflectionObject($ga);
        foreach ($_g->getMethods() as $Le) {
            foreach (
                $Zf
                as $Yf
            ) {
                $D = $Le->getName();
                if (method_exists($Yf, $D)) $this->hooks[$D][] = $Yf;
            }
        }
    }
    function
    __call($D, array $Jf)
    {
        $qa = array();
        foreach (
            $Jf
            as $y => $X
        ) $qa[] = &$Jf[$y];
        $K = null;
        foreach ($this->hooks[$D] as $Yf) {
            $Y = call_user_func_array(array($Yf, $D), $qa);
            if ($Y !== null) {
                if (!self::$append[$D]) return $Y;
                $K = $Y + (array)$K;
            }
        }
        return $K;
    }
}
abstract
class
Plugin
{
    protected $translations = array();
    function
    description()
    {
        return $this->lang('');
    }
    function
    screenshot()
    {
        return "";
    }
    protected
    function
    lang($u, $df = null)
    {
        $qa = func_get_args();
        $qa[0] = idx($this->translations[LANG], $u) ?: $u;
        return
            call_user_func_array('Adminer\lang_format', $qa);
    }
}
Adminer::$instance = (function_exists('adminer_object') ? adminer_object() : (is_dir("adminer-plugins") || file_exists("adminer-plugins.php") ? new
    Plugins(null) : new
    Adminer));
SqlDriver::$drivers = array("server" => "MySQL / MariaDB") + SqlDriver::$drivers;
if (!defined('Adminer\DRIVER')) {
    define('Adminer\DRIVER', "server");
    if (extension_loaded("mysqli") && $_GET["ext"] != "pdo") {
        class
        Db
        extends
        \MySQLi
        {
            static $instance;
            var $extension = "MySQLi", $flavor = '';
            function
            __construct()
            {
                parent::init();
            }
            function
            attach($P, $V, $G)
            {
                mysqli_report(MYSQLI_REPORT_OFF);
                list($td, $ag) = explode(":", $P, 2);
                $nh = adminer()->connectSsl();
                if ($nh) $this->ssl_set($nh['key'], $nh['cert'], $nh['ca'], '', '');
                $K = @$this->real_connect(($P != "" ? $td : ini_get("mysqli.default_host")), ($P . $V != "" ? $V : ini_get("mysqli.default_user")), ($P . $V . $G != "" ? $G : ini_get("mysqli.default_pw")), null, (is_numeric($ag) ? intval($ag) : ini_get("mysqli.default_port")), (is_numeric($ag) ? null : $ag), ($nh ? ($nh['verify'] !== false ? 2048 : 64) : 0));
                $this->options(MYSQLI_OPT_LOCAL_INFILE, false);
                return ($K ? '' : $this->error);
            }
            function
            set_charset($Pa)
            {
                if (parent::set_charset($Pa)) return
                    true;
                parent::set_charset('utf8');
                return $this->query("SET NAMES $Pa");
            }
            function
            next_result()
            {
                return
                    self::more_results() && parent::next_result();
            }
            function
            quote($sh)
            {
                return "'" . $this->escape_string($sh) . "'";
            }
        }
    } elseif (extension_loaded("mysql") && !((ini_bool("sql.safe_mode") || ini_bool("mysql.allow_local_infile")) && extension_loaded("pdo_mysql"))) {
        class
        Db
        extends
        SqlDb
        {
            private $link;
            function
            attach($P, $V, $G)
            {
                if (ini_bool("mysql.allow_local_infile")) return
                    sprintf('Disable %s or enable %s or %s extensions.', "'mysql.allow_local_infile'", "MySQLi", "PDO_MySQL");
                $this->link = @mysql_connect(($P != "" ? $P : ini_get("mysql.default_host")), ("$P$V" != "" ? $V : ini_get("mysql.default_user")), ("$P$V$G" != "" ? $G : ini_get("mysql.default_password")), true, 131072);
                if (!$this->link) return
                    mysql_error();
                $this->server_info = mysql_get_server_info($this->link);
                return '';
            }
            function
            set_charset($Pa)
            {
                if (function_exists('mysql_set_charset')) {
                    if (mysql_set_charset($Pa, $this->link)) return
                        true;
                    mysql_set_charset('utf8', $this->link);
                }
                return $this->query("SET NAMES $Pa");
            }
            function
            quote($sh)
            {
                return "'" . mysql_real_escape_string($sh, $this->link) . "'";
            }
            function
            select_db($Bb)
            {
                return
                    mysql_select_db($Bb, $this->link);
            }
            function
            query($I, $ki = false)
            {
                $J = @($ki ? mysql_unbuffered_query($I, $this->link) : mysql_query($I, $this->link));
                $this->error = "";
                if (!$J) {
                    $this->errno = mysql_errno($this->link);
                    $this->error = mysql_error($this->link);
                    return
                        false;
                }
                if ($J === true) {
                    $this->affected_rows = mysql_affected_rows($this->link);
                    $this->info = mysql_info($this->link);
                    return
                        true;
                }
                return
                    new
                    Result($J);
            }
        }
        class
        Result
        {
            var $num_rows;
            private $result;
            private $offset = 0;
            function
            __construct($J)
            {
                $this->result = $J;
                $this->num_rows = mysql_num_rows($J);
            }
            function
            fetch_assoc()
            {
                return
                    mysql_fetch_assoc($this->result);
            }
            function
            fetch_row()
            {
                return
                    mysql_fetch_row($this->result);
            }
            function
            fetch_field()
            {
                $K = mysql_fetch_field($this->result, $this->offset++);
                $K->orgtable = $K->table;
                $K->charsetnr = ($K->blob ? 63 : 0);
                return $K;
            }
            function
            __destruct()
            {
                mysql_free_result($this->result);
            }
        }
    } elseif (extension_loaded("pdo_mysql")) {
        class
        Db
        extends
        PdoDb
        {
            var $extension = "PDO_MySQL";
            function
            attach($P, $V, $G)
            {
                $sf = array(\PDO::MYSQL_ATTR_LOCAL_INFILE => false);
                $nh = adminer()->connectSsl();
                if ($nh) {
                    if ($nh['key']) $sf[\PDO::MYSQL_ATTR_SSL_KEY] = $nh['key'];
                    if ($nh['cert']) $sf[\PDO::MYSQL_ATTR_SSL_CERT] = $nh['cert'];
                    if ($nh['ca']) $sf[\PDO::MYSQL_ATTR_SSL_CA] = $nh['ca'];
                    if (isset($nh['verify'])) $sf[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $nh['verify'];
                }
                return $this->dsn("mysql:charset=utf8;host=" . str_replace(":", ";unix_socket=", preg_replace('~:(\d)~', ';port=\1', $P)), $V, $G, $sf);
            }
            function
            set_charset($Pa)
            {
                return $this->query("SET NAMES $Pa");
            }
            function
            select_db($Bb)
            {
                return $this->query("USE " . idf_escape($Bb));
            }
            function
            query($I, $ki = false)
            {
                $this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$ki);
                return
                    parent::query($I, $ki);
            }
        }
    }
    class
    Driver
    extends
    SqlDriver
    {
        static $extensions = array("MySQLi", "MySQL", "PDO_MySQL");
        static $jush = "sql";
        var $unsigned = array("unsigned", "zerofill", "unsigned zerofill");
        var $operators = array("=", "<", ">", "<=", ">=", "!=", "LIKE", "LIKE %%", "REGEXP", "IN", "FIND_IN_SET", "IS NULL", "NOT LIKE", "NOT REGEXP", "NOT IN", "IS NOT NULL", "SQL");
        var $functions = array("char_length", "date", "from_unixtime", "lower", "round", "floor", "ceil", "sec_to_time", "time_to_sec", "upper");
        var $grouping = array("avg", "count", "count distinct", "group_concat", "max", "min", "sum");
        static
        function
        connect($P, $V, $G)
        {
            $e = parent::connect($P, $V, $G);
            if (is_string($e)) {
                if (function_exists('iconv') && !is_utf8($e) && strlen($N = iconv("windows-1250", "utf-8", $e)) > strlen($e)) $e = $N;
                return $e;
            }
            $e->set_charset(charset($e));
            $e->query("SET sql_quote_show_create = 1, autocommit = 1");
            $e->flavor = (preg_match('~MariaDB~', $e->server_info) ? 'maria' : 'mysql');
            add_driver(DRIVER, ($e->flavor == 'maria' ? "MariaDB" : "MySQL"));
            return $e;
        }
        function
        __construct(Db $e)
        {
            parent::__construct($e);
            $this->types = array('Numbers' => array("tinyint" => 3, "smallint" => 5, "mediumint" => 8, "int" => 10, "bigint" => 20, "decimal" => 66, "float" => 12, "double" => 21), 'Date and time' => array("date" => 10, "datetime" => 19, "timestamp" => 19, "time" => 10, "year" => 4), 'Strings' => array("char" => 255, "varchar" => 65535, "tinytext" => 255, "text" => 65535, "mediumtext" => 16777215, "longtext" => 4294967295), 'Lists' => array("enum" => 65535, "set" => 64), 'Binary' => array("bit" => 20, "binary" => 255, "varbinary" => 65535, "tinyblob" => 255, "blob" => 65535, "mediumblob" => 16777215, "longblob" => 4294967295), 'Geometry' => array("geometry" => 0, "point" => 0, "linestring" => 0, "polygon" => 0, "multipoint" => 0, "multilinestring" => 0, "multipolygon" => 0, "geometrycollection" => 0),);
            $this->insertFunctions = array("char" => "md5/sha1/password/encrypt/uuid", "binary" => "md5/sha1", "date|time" => "now",);
            $this->editFunctions = array(number_type() => "+/-", "date" => "+ interval/- interval", "time" => "addtime/subtime", "char|text" => "concat",);
            if (min_version('5.7.8', 10.2, $e)) $this->types['Strings']["json"] = 4294967295;
            if (min_version('', 10.7, $e)) {
                $this->types['Strings']["uuid"] = 128;
                $this->insertFunctions['uuid'] = 'uuid';
            }
            if (min_version(9, '', $e)) {
                $this->types['Numbers']["vector"] = 16383;
                $this->insertFunctions['vector'] = 'string_to_vector';
            }
            if (min_version(5.1, '', $e)) $this->partitionBy = array("HASH", "LINEAR HASH", "KEY", "LINEAR KEY", "RANGE", "LIST");
            if (min_version(5.7, 10.2, $e)) $this->generated = array("STORED", "VIRTUAL");
        }
        function
        unconvertFunction(array $l)
        {
            return (preg_match("~binary~", $l["type"]) ? "<code class='jush-sql'>UNHEX</code>" : ($l["type"] == "bit" ? doc_link(array('sql' => 'bit-value-literals.html'), "<code>b''</code>") : (preg_match("~geometry|point|linestring|polygon~", $l["type"]) ? "<code class='jush-sql'>GeomFromText</code>" : "")));
        }
        function
        insert($R, array $Q)
        {
            return ($Q ? parent::insert($R, $Q) : queries("INSERT INTO " . table($R) . " ()\nVALUES ()"));
        }
        function
        insertUpdate($R, array $M, array $ig)
        {
            $d = array_keys(reset($M));
            $gg = "INSERT INTO " . table($R) . " (" . implode(", ", $d) . ") VALUES\n";
            $_i = array();
            foreach (
                $d
                as $y
            ) $_i[$y] = "$y = VALUES($y)";
            $wh = "\nON DUPLICATE KEY UPDATE " . implode(", ", $_i);
            $_i = array();
            $oe = 0;
            foreach (
                $M
                as $Q
            ) {
                $Y = "(" . implode(", ", $Q) . ")";
                if ($_i && (strlen($gg) + $oe + strlen($Y) + strlen($wh) > 1e6)) {
                    if (!queries($gg . implode(",\n", $_i) . $wh)) return
                        false;
                    $_i = array();
                    $oe = 0;
                }
                $_i[] = $Y;
                $oe += strlen($Y) + 2;
            }
            return
                queries($gg . implode(",\n", $_i) . $wh);
        }
        function
        slowQuery($I, $Ph)
        {
            if (min_version('5.7.8', '10.1.2')) {
                if ($this->conn->flavor == 'maria') return "SET STATEMENT max_statement_time=$Ph FOR $I";
                elseif (preg_match('~^(SELECT\b)(.+)~is', $I, $B)) return "$B[1] /*+ MAX_EXECUTION_TIME(" . ($Ph * 1000) . ") */ $B[2]";
            }
        }
        function
        convertSearch($u, array $X, array $l)
        {
            return (preg_match('~char|text|enum|set~', $l["type"]) && !preg_match("~^utf8~", $l["collation"]) && preg_match('~[\x80-\xFF]~', $X['val']) ? "CONVERT($u USING " . charset($this->conn) . ")" : $u);
        }
        function
        warnings()
        {
            $J = $this->conn->query("SHOW WARNINGS");
            if ($J && $J->num_rows) {
                ob_start();
                print_select_result($J);
                return
                    ob_get_clean();
            }
        }
        function
        tableHelp($D, $Zd = false)
        {
            $ve = ($this->conn->flavor == 'maria');
            if (information_schema(DB)) return
                strtolower("information-schema-" . ($ve ? "$D-table/" : str_replace("_", "-", $D) . "-table.html"));
            if (DB == "mysql") return ($ve ? "mysql$D-table/" : "system-schema.html");
        }
        function
        partitionsInfo($R)
        {
            $Xc = "FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = " . q(DB) . " AND TABLE_NAME = " . q($R);
            $J = connection()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $Xc ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");
            $K = array();
            list($K["partition_by"], $K["partition"], $K["partitions"]) = $J->fetch_row();
            $Qf = get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Xc AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");
            $K["partition_names"] = array_keys($Qf);
            $K["partition_values"] = array_values($Qf);
            return $K;
        }
        function
        hasCStyleEscapes()
        {
            static $Ka;
            if ($Ka === null) {
                $mh = get_val("SHOW VARIABLES LIKE 'sql_mode'", 1, $this->conn);
                $Ka = (strpos($mh, 'NO_BACKSLASH_ESCAPES') === false);
            }
            return $Ka;
        }
        function
        engines()
        {
            $K = array();
            foreach (get_rows("SHOW ENGINES") as $L) {
                if (preg_match("~YES|DEFAULT~", $L["Support"])) $K[] = $L["Engine"];
            }
            return $K;
        }
        function
        indexAlgorithms(array $_h)
        {
            return (preg_match('~^(MEMORY|NDB)$~', $_h["Engine"]) ? array("HASH", "BTREE") : array());
        }
    }
    function
    idf_escape($u)
    {
        return "`" . str_replace("`", "``", $u) . "`";
    }
    function
    table($u)
    {
        return
            idf_escape($u);
    }
    function
    get_databases($Pc)
    {
        $K = get_session("dbs");
        if ($K === null) {
            $I = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";
            $K = ($Pc ? slow_query($I) : get_vals($I));
            restart_session();
            set_session("dbs", $K);
            stop_session();
        }
        return $K;
    }
    function
    limit($I, $Z, $z, $ff = 0, $Wg = " ")
    {
        return " $I$Z" . ($z ? $Wg . "LIMIT $z" . ($ff ? " OFFSET $ff" : "") : "");
    }
    function
    limit1($R, $I, $Z, $Wg = "\n")
    {
        return
            limit($I, $Z, 1, 0, $Wg);
    }
    function
    db_collation($i, array $b)
    {
        $K = null;
        $g = get_val("SHOW CREATE DATABASE " . idf_escape($i), 1);
        if (preg_match('~ COLLATE ([^ ]+)~', $g, $B)) $K = $B[1];
        elseif (preg_match('~ CHARACTER SET ([^ ]+)~', $g, $B)) $K = $b[$B[1]][-1];
        return $K;
    }
    function
    logged_user()
    {
        return
            get_val("SELECT USER()");
    }
    function
    tables_list()
    {
        return
            get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");
    }
    function
    count_tables(array $h)
    {
        $K = array();
        foreach (
            $h
            as $i
        ) $K[$i] = count(get_vals("SHOW TABLES IN " . idf_escape($i)));
        return $K;
    }
    function
    table_status($D = "", $Gc = false)
    {
        $K = array();
        foreach (get_rows($Gc ? "SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() " . ($D != "" ? "AND TABLE_NAME = " . q($D) : "ORDER BY Name") : "SHOW TABLE STATUS" . ($D != "" ? " LIKE " . q(addcslashes($D, "%_\\")) : "")) as $L) {
            if ($L["Engine"] == "InnoDB") $L["Comment"] = preg_replace('~(?:(.+); )?InnoDB free: .*~', '\1', $L["Comment"]);
            if (!isset($L["Engine"])) $L["Comment"] = "";
            if ($D != "") $L["Name"] = $D;
            $K[$L["Name"]] = $L;
        }
        return $K;
    }
    function
    is_view(array $S)
    {
        return $S["Engine"] === null;
    }
    function
    fk_support(array $S)
    {
        return
            preg_match('~InnoDB|IBMDB2I' . (min_version(5.6) ? '|NDB' : '') . '~i', $S["Engine"]);
    }
    function
    fields($R)
    {
        $ve = (connection()->flavor == 'maria');
        $K = array();
        foreach (get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . q($R) . " ORDER BY ORDINAL_POSITION") as $L) {
            $l = $L["COLUMN_NAME"];
            $U = $L["COLUMN_TYPE"];
            $bd = $L["GENERATION_EXPRESSION"];
            $Dc = $L["EXTRA"];
            preg_match('~^(VIRTUAL|PERSISTENT|STORED)~', $Dc, $ad);
            preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~', $U, $xe);
            $j = $L["COLUMN_DEFAULT"];
            if ($j != "") {
                $Yd = preg_match('~text|json~', $xe[1]);
                if (!$ve && $Yd) $j = preg_replace("~^(_\w+)?('.*')$~", '\2', stripslashes($j));
                if ($ve || $Yd) {
                    $j = ($j == "NULL" ? null : preg_replace_callback("~^'(.*)'$~", function ($B) {
                        return
                            stripslashes(str_replace("''", "'", $B[1]));
                    }, $j));
                }
                if (!$ve && preg_match('~binary~', $xe[1]) && preg_match('~^0x(\w*)$~', $j, $B)) $j = pack("H*", $B[1]);
            }
            $K[$l] = array("field" => $l, "full_type" => $U, "type" => $xe[1], "length" => $xe[2], "unsigned" => ltrim($xe[3] . $xe[4]), "default" => ($ad ? ($ve ? $bd : stripslashes($bd)) : $j), "null" => ($L["IS_NULLABLE"] == "YES"), "auto_increment" => ($Dc == "auto_increment"), "on_update" => (preg_match('~\bon update (\w+)~i', $Dc, $B) ? $B[1] : ""), "collation" => $L["COLLATION_NAME"], "privileges" => array_flip(explode(",", "$L[PRIVILEGES],where,order")), "comment" => $L["COLUMN_COMMENT"], "primary" => ($L["COLUMN_KEY"] == "PRI"), "generated" => ($ad[1] == "PERSISTENT" ? "STORED" : $ad[1]),);
        }
        return $K;
    }
    function
    indexes($R, $f = null)
    {
        $K = array();
        foreach (get_rows("SHOW INDEX FROM " . table($R), $f) as $L) {
            $D = $L["Key_name"];
            $K[$D]["type"] = ($D == "PRIMARY" ? "PRIMARY" : ($L["Index_type"] == "FULLTEXT" ? "FULLTEXT" : ($L["Non_unique"] ? ($L["Index_type"] == "SPATIAL" ? "SPATIAL" : "INDEX") : "UNIQUE")));
            $K[$D]["columns"][] = $L["Column_name"];
            $K[$D]["lengths"][] = ($L["Index_type"] == "SPATIAL" ? null : $L["Sub_part"]);
            $K[$D]["descs"][] = null;
            $K[$D]["algorithm"] = $L["Index_type"];
        }
        return $K;
    }
    function
    foreign_keys($R)
    {
        static $Uf = '(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';
        $K = array();
        $sb = get_val("SHOW CREATE TABLE " . table($R), 1);
        if ($sb) {
            preg_match_all("~CONSTRAINT ($Uf) FOREIGN KEY ?\\(((?:$Uf,? ?)+)\\) REFERENCES ($Uf)(?:\\.($Uf))? \\(((?:$Uf,? ?)+)\\)(?: ON DELETE (" . driver()->onActions . "))?(?: ON UPDATE (" . driver()->onActions . "))?~", $sb, $ye, PREG_SET_ORDER);
            foreach (
                $ye
                as $B
            ) {
                preg_match_all("~$Uf~", $B[2], $hh);
                preg_match_all("~$Uf~", $B[5], $Ih);
                $K[idf_unescape($B[1])] = array("db" => idf_unescape($B[4] != "" ? $B[3] : $B[4]), "table" => idf_unescape($B[4] != "" ? $B[4] : $B[3]), "source" => array_map('Adminer\idf_unescape', $hh[0]), "target" => array_map('Adminer\idf_unescape', $Ih[0]), "on_delete" => ($B[6] ?: "RESTRICT"), "on_update" => ($B[7] ?: "RESTRICT"),);
            }
        }
        return $K;
    }
    function
    view($D)
    {
        return
            array("select" => preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU', '', get_val("SHOW CREATE VIEW " . table($D), 1)));
    }
    function
    collations()
    {
        $K = array();
        foreach (get_rows("SHOW COLLATION") as $L) {
            if ($L["Default"]) $K[$L["Charset"]][-1] = $L["Collation"];
            else $K[$L["Charset"]][] = $L["Collation"];
        }
        ksort($K);
        foreach (
            $K
            as $y => $X
        ) sort($K[$y]);
        return $K;
    }
    function
    information_schema($i)
    {
        return ($i == "information_schema") || (min_version(5.5) && $i == "performance_schema");
    }
    function
    error()
    {
        return
            h(preg_replace('~^You have an error.*syntax to use~U', "Syntax error", connection()->error));
    }
    function
    create_database($i, $bb)
    {
        return
            queries("CREATE DATABASE " . idf_escape($i) . ($bb ? " COLLATE " . q($bb) : ""));
    }
    function
    drop_databases(array $h)
    {
        $K = apply_queries("DROP DATABASE", $h, 'Adminer\idf_escape');
        restart_session();
        set_session("dbs", null);
        return $K;
    }
    function
    rename_database($D, $bb)
    {
        $K = false;
        if (create_database($D, $bb)) {
            $T = array();
            $Ei = array();
            foreach (tables_list() as $R => $U) {
                if ($U == 'VIEW') $Ei[] = $R;
                else $T[] = $R;
            }
            $K = (!$T && !$Ei) || move_tables($T, $Ei, $D);
            drop_databases($K ? array(DB) : array());
        }
        return $K;
    }
    function
    auto_increment()
    {
        $xa = " PRIMARY KEY";
        if ($_GET["create"] != "" && $_POST["auto_increment_col"]) {
            foreach (indexes($_GET["create"]) as $v) {
                if (in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"], $v["columns"], true)) {
                    $xa = "";
                    break;
                }
                if ($v["type"] == "PRIMARY") $xa = " UNIQUE";
            }
        }
        return " AUTO_INCREMENT$xa";
    }
    function
    alter_table($R, $D, array $m, array $Rc, $fb, $kc, $bb, $wa, $F)
    {
        $oa = array();
        foreach (
            $m
            as $l
        ) {
            if ($l[1]) {
                $j = $l[1][3];
                if (preg_match('~ GENERATED~', $j)) {
                    $l[1][3] = (connection()->flavor == 'maria' ? "" : $l[1][2]);
                    $l[1][2] = $j;
                }
                $oa[] = ($R != "" ? ($l[0] != "" ? "CHANGE " . idf_escape($l[0]) : "ADD") : " ") . " " . implode($l[1]) . ($R != "" ? $l[2] : "");
            } else $oa[] = "DROP " . idf_escape($l[0]);
        }
        $oa = array_merge($oa, $Rc);
        $ph = ($fb !== null ? " COMMENT=" . q($fb) : "") . ($kc ? " ENGINE=" . q($kc) : "") . ($bb ? " COLLATE " . q($bb) : "") . ($wa != "" ? " AUTO_INCREMENT=$wa" : "");
        if ($F) {
            $Qf = array();
            if ($F["partition_by"] == 'RANGE' || $F["partition_by"] == 'LIST') {
                foreach ($F["partition_names"] as $y => $X) {
                    $Y = $F["partition_values"][$y];
                    $Qf[] = "\n  PARTITION " . idf_escape($X) . " VALUES " . ($F["partition_by"] == 'RANGE' ? "LESS THAN" : "IN") . ($Y != "" ? " ($Y)" : " MAXVALUE");
                }
            }
            $ph
                .= "\nPARTITION BY $F[partition_by]($F[partition])";
            if ($Qf) $ph
                .= " (" . implode(",", $Qf) . "\n)";
            elseif ($F["partitions"]) $ph
                .= " PARTITIONS " . (+$F["partitions"]);
        } elseif ($F === null) $ph
            .= "\nREMOVE PARTITIONING";
        if ($R == "") return
            queries("CREATE TABLE " . table($D) . " (\n" . implode(",\n", $oa) . "\n)$ph");
        if ($R != $D) $oa[] = "RENAME TO " . table($D);
        if ($ph) $oa[] = ltrim($ph);
        return ($oa ? queries("ALTER TABLE " . table($R) . "\n" . implode(",\n", $oa)) : true);
    }
    function
    alter_indexes($R, $oa)
    {
        $Oa = array();
        foreach (
            $oa
            as $X
        ) $Oa[] = ($X[2] == "DROP" ? "\nDROP INDEX " . idf_escape($X[1]) : "\nADD $X[0] " . ($X[0] == "PRIMARY" ? "KEY " : "") . ($X[1] != "" ? idf_escape($X[1]) . " " : "") . "(" . implode(", ", $X[2]) . ")");
        return
            queries("ALTER TABLE " . table($R) . implode(",", $Oa));
    }
    function
    truncate_tables(array $T)
    {
        return
            apply_queries("TRUNCATE TABLE", $T);
    }
    function
    drop_views(array $Ei)
    {
        return
            queries("DROP VIEW " . implode(", ", array_map('Adminer\table', $Ei)));
    }
    function
    drop_tables(array $T)
    {
        return
            queries("DROP TABLE " . implode(", ", array_map('Adminer\table', $T)));
    }
    function
    move_tables(array $T, array $Ei, $Ih)
    {
        $Cg = array();
        foreach (
            $T
            as $R
        ) $Cg[] = table($R) . " TO " . idf_escape($Ih) . "." . table($R);
        if (!$Cg || queries("RENAME TABLE " . implode(", ", $Cg))) {
            $Kb = array();
            foreach (
                $Ei
                as $R
            ) $Kb[table($R)] = view($R);
            connection()->select_db($Ih);
            $i = idf_escape(DB);
            foreach (
                $Kb
                as $D => $Di
            ) {
                if (!queries("CREATE VIEW $D AS " . str_replace(" $i.", " ", $Di["select"])) || !queries("DROP VIEW $i.$D")) return
                    false;
            }
            return
                true;
        }
        return
            false;
    }
    function
    copy_tables(array $T, array $Ei, $Ih)
    {
        queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        foreach (
            $T
            as $R
        ) {
            $D = ($Ih == DB ? table("copy_$R") : idf_escape($Ih) . "." . table($R));
            if (($_POST["overwrite"] && !queries("\nDROP TABLE IF EXISTS $D")) || !queries("CREATE TABLE $D LIKE " . table($R)) || !queries("INSERT INTO $D SELECT * FROM " . table($R))) return
                false;
            foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($R, "%_\\"))) as $L) {
                $ei = $L["Trigger"];
                if (!queries("CREATE TRIGGER " . ($Ih == DB ? idf_escape("copy_$ei") : idf_escape($Ih) . "." . idf_escape($ei)) . " $L[Timing] $L[Event] ON $D FOR EACH ROW\n$L[Statement];")) return
                    false;
            }
        }
        foreach (
            $Ei
            as $R
        ) {
            $D = ($Ih == DB ? table("copy_$R") : idf_escape($Ih) . "." . table($R));
            $Di = view($R);
            if (($_POST["overwrite"] && !queries("DROP VIEW IF EXISTS $D")) || !queries("CREATE VIEW $D AS $Di[select]")) return
                false;
        }
        return
            true;
    }
    function
    trigger($D, $R)
    {
        if ($D == "") return
            array();
        $M = get_rows("SHOW TRIGGERS WHERE `Trigger` = " . q($D));
        return
            reset($M);
    }
    function
    triggers($R)
    {
        $K = array();
        foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($R, "%_\\"))) as $L) $K[$L["Trigger"]] = array($L["Timing"], $L["Event"]);
        return $K;
    }
    function
    trigger_options()
    {
        return
            array("Timing" => array("BEFORE", "AFTER"), "Event" => array("INSERT", "UPDATE", "DELETE"), "Type" => array("FOR EACH ROW"),);
    }
    function
    routine($D, $U)
    {
        $ma = array("bool", "boolean", "integer", "double precision", "real", "dec", "numeric", "fixed", "national char", "national varchar");
        $ih = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";
        $mc = driver()->enumLength;
        $ii = "((" . implode("|", array_merge(array_keys(driver()->types()), $ma)) . ")\\b(?:\\s*\\(((?:[^'\")]|$mc)++)\\))?" . "\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";
        $Uf = "$ih*(" . ($U == "FUNCTION" ? "" : driver()->inout) . ")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$ii";
        $g = get_val("SHOW CREATE $U " . idf_escape($D), 2);
        preg_match("~\\(((?:$Uf\\s*,?)*)\\)\\s*" . ($U == "FUNCTION" ? "RETURNS\\s+$ii\\s+" : "") . "(.*)~is", $g, $B);
        $m = array();
        preg_match_all("~$Uf\\s*,?~is", $B[1], $ye, PREG_SET_ORDER);
        foreach (
            $ye
            as $If
        ) $m[] = array("field" => str_replace("``", "`", $If[2]) . $If[3], "type" => strtolower($If[5]), "length" => preg_replace_callback("~$mc~s", 'Adminer\normalize_enum', $If[6]), "unsigned" => strtolower(preg_replace('~\s+~', ' ', trim("$If[8] $If[7]"))), "null" => true, "full_type" => $If[4], "inout" => strtoupper($If[1]), "collation" => strtolower($If[9]),);
        return
            array("fields" => $m, "comment" => get_val("SELECT ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME = " . q($D)),) + ($U != "FUNCTION" ? array("definition" => $B[11]) : array("returns" => array("type" => $B[12], "length" => $B[13], "unsigned" => $B[15], "collation" => $B[16]), "definition" => $B[17], "language" => "SQL",));
    }
    function
    routines()
    {
        return
            get_rows("SELECT ROUTINE_NAME AS SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");
    }
    function
    routine_languages()
    {
        return
            array();
    }
    function
    routine_id($D, array $L)
    {
        return
            idf_escape($D);
    }
    function
    last_id($J)
    {
        return
            get_val("SELECT LAST_INSERT_ID()");
    }
    function
    explain(Db $e, $I)
    {
        return $e->query("EXPLAIN " . (min_version(5.1) && !min_version(5.7) ? "PARTITIONS " : "") . $I);
    }
    function
    found_rows(array $S, array $Z)
    {
        return ($Z || $S["Engine"] != "InnoDB" ? null : $S["Rows"]);
    }
    function
    create_sql($R, $wa, $uh)
    {
        $K = get_val("SHOW CREATE TABLE " . table($R), 1);
        if (!$wa) $K = preg_replace('~ AUTO_INCREMENT=\d+~', '', $K);
        return $K;
    }
    function
    truncate_sql($R)
    {
        return "TRUNCATE " . table($R);
    }
    function
    use_sql($Bb)
    {
        return "USE " . idf_escape($Bb);
    }
    function
    trigger_sql($R)
    {
        $K = "";
        foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($R, "%_\\")), null, "-- ") as $L) $K
            .= "\nCREATE TRIGGER " . idf_escape($L["Trigger"]) . " $L[Timing] $L[Event] ON " . table($L["Table"]) . " FOR EACH ROW\n$L[Statement];;\n";
        return $K;
    }
    function
    show_variables()
    {
        return
            get_rows("SHOW VARIABLES");
    }
    function
    show_status()
    {
        return
            get_rows("SHOW STATUS");
    }
    function
    process_list()
    {
        return
            get_rows("SHOW FULL PROCESSLIST");
    }
    function
    convert_field(array $l)
    {
        if (preg_match("~binary~", $l["type"])) return "HEX(" . idf_escape($l["field"]) . ")";
        if ($l["type"] == "bit") return "BIN(" . idf_escape($l["field"]) . " + 0)";
        if (preg_match("~geometry|point|linestring|polygon~", $l["type"])) return (min_version(8) ? "ST_" : "") . "AsWKT(" . idf_escape($l["field"]) . ")";
    }
    function
    unconvert_field(array $l, $K)
    {
        if (preg_match("~binary~", $l["type"])) $K = "UNHEX($K)";
        if ($l["type"] == "bit") $K = "CONVERT(b$K, UNSIGNED)";
        if (preg_match("~geometry|point|linestring|polygon~", $l["type"])) {
            $gg = (min_version(8) ? "ST_" : "");
            $K = $gg . "GeomFromText($K, $gg" . "SRID($l[field]))";
        }
        return $K;
    }
    function
    support($Hc)
    {
        return
            preg_match('~^(comment|columns|copy|database|drop_col|dump|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view' . (min_version(5.1) ? '|event' : '') . (min_version(8) ? '|descidx' : '') . (min_version('8.0.16', '10.2.1') ? '|check' : '') . ')$~', $Hc);
    }
    function
    kill_process($X)
    {
        return
            queries("KILL " . number($X));
    }
    function
    connection_id()
    {
        return "SELECT CONNECTION_ID()";
    }
    function
    max_connections()
    {
        return
            get_val("SELECT @@max_connections");
    }
    function
    types()
    {
        return
            array();
    }
    function
    type_values($t)
    {
        return "";
    }
    function
    schemas()
    {
        return
            array();
    }
    function
    get_schema()
    {
        return "";
    }
    function
    set_schema($Ng, $f = null)
    {
        return
            true;
    }
}
define('Adminer\JUSH', Driver::$jush);
define('Adminer\SERVER', $_GET[DRIVER]);
define('Adminer\DB', $_GET["db"]);
define('Adminer\ME', preg_replace('~\?.*~', '', relative_uri()) . '?' . (sid() ? SID . '&' : '') . (SERVER !== null ? DRIVER . "=" . urlencode(SERVER) . '&' : '') . ($_GET["ext"] ? "ext=" . urlencode($_GET["ext"]) . '&' : '') . (isset($_GET["username"]) ? "username=" . urlencode($_GET["username"]) . '&' : '') . (DB != "" ? 'db=' . urlencode(DB) . '&' . (isset($_GET["ns"]) ? "ns=" . urlencode($_GET["ns"]) . "&" : "") : ''));
function
page_header($Rh, $k = "", $Ia = array(), $Sh = "")
{
    page_headers();
    if (is_ajax() && $k) {
        page_messages($k);
        exit;
    }
    if (!ob_get_level()) ob_start('ob_gzhandler', 4096);
    $Th = $Rh . ($Sh != "" ? ": $Sh" : "");
    $Uh = strip_tags($Th . (SERVER != "" && SERVER != "localhost" ? h(" - " . SERVER) : "") . " - " . adminer()->name());
    echo '<!DOCTYPE html>
<html lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>', $Uh, '</title>
<link rel="stylesheet" href="', h(preg_replace("~\\?.*~", "", ME) . "?file=default.css&version=5.3.0"), '">
';
    $wb = adminer()->css();
    if (is_int(key($wb))) $wb = array_fill_keys($wb, 'light');
    $md = in_array('light', $wb) || in_array('', $wb);
    $kd = in_array('dark', $wb) || in_array('', $wb);
    $zb = ($md ? ($kd ? null : false) : ($kd ?: null));
    $Fe = " media='(prefers-color-scheme: dark)'";
    if ($zb !== false) echo "<link rel='stylesheet'" . ($zb ? "" : $Fe) . " href='" . h(preg_replace("~\\?.*~", "", ME) . "?file=dark.css&version=5.3.0") . "'>\n";
    echo "<meta name='color-scheme' content='" . ($zb === null ? "light dark" : ($zb ? "dark" : "light")) . "'>\n", script_src(preg_replace("~\\?.*~", "", ME) . "?file=functions.js&version=5.3.0");
    if (adminer()->head($zb)) echo "<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n", "<link rel='apple-touch-icon' href='" . h(preg_replace("~\\?.*~", "", ME) . "?file=logo.png&version=5.3.0") . "'>\n";
    foreach (
        $wb
        as $ti => $Oe
    ) {
        $ua = ($Oe == 'dark' && !$zb ? $Fe : ($Oe == 'light' && $kd ? " media='(prefers-color-scheme: light)'" : ""));
        echo "<link rel='stylesheet'$ua href='" . h($ti) . "'>\n";
    }
    echo "\n<body class='" . 'ltr' . " nojs";
    adminer()->bodyClass();
    echo "'>\n";
    $n = get_temp_dir() . "/adminer.version";
    if (!$_COOKIE["adminer_version"] && function_exists('openssl_verify') && file_exists($n) && filemtime($n) + 86400 > time()) {
        $Ci = unserialize(file_get_contents($n));
        $pg = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";
        if (openssl_verify($Ci["version"], base64_decode($Ci["signature"]), $pg) == 1) $_COOKIE["adminer_version"] = $Ci["version"];
    }
    echo
    script("mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick" . (isset($_COOKIE["adminer_version"]) ? "" : ", onload: partial(verifyVersion, '" . VERSION . "', '" . js_escape(ME) . "', '" . get_token() . "')") . "});
document.body.classList.replace('nojs', 'js');
const offlineMessage = '" . js_escape('You are offline.') . "';
const thousandsSeparator = '" . js_escape(',') . "';"), "<div id='help' class='jush-" . JUSH . " jsonly hidden'></div>\n", script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"), "<div id='content'>\n", "<span id='menuopen' class='jsonly'>" . icon("move", "", "menu", "") . "</span>" . script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");
    if ($Ia !== null) {
        $_ = substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1);
        echo '<p id="breadcrumb"><a href="' . h($_ ?: ".") . '">' . get_driver(DRIVER) . '</a> » ';
        $_ = substr(preg_replace('~\b(db|ns)=[^&]*&~', '', ME), 0, -1);
        $P = adminer()->serverName(SERVER);
        $P = ($P != "" ? $P : 'Server');
        if ($Ia === false) echo "$P\n";
        else {
            echo "<a href='" . h($_) . "' accesskey='1' title='Alt+Shift+1'>$P</a> » ";
            if ($_GET["ns"] != "" || (DB != "" && is_array($Ia))) echo '<a href="' . h($_ . "&db=" . urlencode(DB) . (support("scheme") ? "&ns=" : "")) . '">' . h(DB) . '</a> » ';
            if (is_array($Ia)) {
                if ($_GET["ns"] != "") echo '<a href="' . h(substr(ME, 0, -1)) . '">' . h($_GET["ns"]) . '</a> » ';
                foreach (
                    $Ia
                    as $y => $X
                ) {
                    $Mb = (is_array($X) ? $X[1] : h($X));
                    if ($Mb != "") echo "<a href='" . h(ME . "$y=") . urlencode(is_array($X) ? $X[0] : $X) . "'>$Mb</a> » ";
                }
            }
            echo "$Rh\n";
        }
    }
    echo "<h2>$Th</h2>\n", "<div id='ajaxstatus' class='jsonly hidden'></div>\n";
    restart_session();
    page_messages($k);
    $h = &get_session("dbs");
    if (DB != "" && $h && !in_array(DB, $h, true)) $h = null;
    stop_session();
    define('Adminer\PAGE_HEADER', 1);
}
function
page_headers()
{
    header("Content-Type: text/html; charset=utf-8");
    header("Cache-Control: no-cache");
    header("X-Frame-Options: deny");
    header("X-XSS-Protection: 0");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: origin-when-cross-origin");
    foreach (adminer()->csp(csp()) as $vb) {
        $nd = array();
        foreach (
            $vb
            as $y => $X
        ) $nd[] = "$y $X";
        header("Content-Security-Policy: " . implode("; ", $nd));
    }
    adminer()->headers();
}
function
csp()
{
    return
        array(array("script-src" => "'self' 'unsafe-inline' 'nonce-" . get_nonce() . "' 'strict-dynamic'", "connect-src" => "'self'", "frame-src" => "https://www.adminer.org", "object-src" => "'none'", "base-uri" => "'none'", "form-action" => "'self'",),);
}
function
get_nonce()
{
    static $af;
    if (!$af) $af = base64_encode(rand_string());
    return $af;
}
function
page_messages($k)
{
    $si = preg_replace('~^[^?]*~', '', $_SERVER["REQUEST_URI"]);
    $Ke = idx($_SESSION["messages"], $si);
    if ($Ke) {
        echo "<div class='message'>" . implode("</div>\n<div class='message'>", $Ke) . "</div>" . script("messagesPrint();");
        unset($_SESSION["messages"][$si]);
    }
    if ($k) echo "<div class='error'>$k</div>\n";
    if (adminer()->error) echo "<div class='error'>" . adminer()->error . "</div>\n";
}
function
page_footer($Ne = "")
{
    echo "</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";
    adminer()->navigation($Ne);
    echo "</div>\n";
    if ($Ne != "auth") echo '<form action="" method="post">
<p class="logout">
<span>', h($_GET["username"]) . "\n", '</span>
<input type="submit" name="logout" value="Logout" id="logout">
', input_token(), '</form>
';
    echo "</div>\n\n", script("setupSubmitHighlight(document);");
}
function
int32($Se)
{
    while ($Se >= 2147483648) $Se -= 4294967296;
    while ($Se <= -2147483649) $Se += 4294967296;
    return (int)$Se;
}
function
long2str(array $W, $Gi)
{
    $N = '';
    foreach (
        $W
        as $X
    ) $N
        .= pack('V', $X);
    if ($Gi) return
        substr($N, 0, end($W));
    return $N;
}
function
str2long($N, $Gi)
{
    $W = array_values(unpack('V*', str_pad($N, 4 * ceil(strlen($N) / 4), "\0")));
    if ($Gi) $W[] = strlen($N);
    return $W;
}
function
xxtea_mx($Ni, $Mi, $xh, $be)
{
    return
        int32((($Ni >> 5 & 0x7FFFFFF) ^ $Mi << 2) + (($Mi >> 3 & 0x1FFFFFFF) ^ $Ni << 4)) ^ int32(($xh ^ $Mi) + ($be ^ $Ni));
}
function
encrypt_string($rh, $y)
{
    if ($rh == "") return "";
    $y = array_values(unpack("V*", pack("H*", md5($y))));
    $W = str2long($rh, true);
    $Se = count($W) - 1;
    $Ni = $W[$Se];
    $Mi = $W[0];
    $H = floor(6 + 52 / ($Se + 1));
    $xh = 0;
    while ($H-- > 0) {
        $xh = int32($xh + 0x9E3779B9);
        $bc = $xh >> 2 & 3;
        for ($Gf = 0; $Gf < $Se; $Gf++) {
            $Mi = $W[$Gf + 1];
            $Re = xxtea_mx($Ni, $Mi, $xh, $y[$Gf & 3 ^ $bc]);
            $Ni = int32($W[$Gf] + $Re);
            $W[$Gf] = $Ni;
        }
        $Mi = $W[0];
        $Re = xxtea_mx($Ni, $Mi, $xh, $y[$Gf & 3 ^ $bc]);
        $Ni = int32($W[$Se] + $Re);
        $W[$Se] = $Ni;
    }
    return
        long2str($W, false);
}
function
decrypt_string($rh, $y)
{
    if ($rh == "") return "";
    if (!$y) return
        false;
    $y = array_values(unpack("V*", pack("H*", md5($y))));
    $W = str2long($rh, false);
    $Se = count($W) - 1;
    $Ni = $W[$Se];
    $Mi = $W[0];
    $H = floor(6 + 52 / ($Se + 1));
    $xh = int32($H * 0x9E3779B9);
    while ($xh) {
        $bc = $xh >> 2 & 3;
        for ($Gf = $Se; $Gf > 0; $Gf--) {
            $Ni = $W[$Gf - 1];
            $Re = xxtea_mx($Ni, $Mi, $xh, $y[$Gf & 3 ^ $bc]);
            $Mi = int32($W[$Gf] - $Re);
            $W[$Gf] = $Mi;
        }
        $Ni = $W[$Se];
        $Re = xxtea_mx($Ni, $Mi, $xh, $y[$Gf & 3 ^ $bc]);
        $Mi = int32($W[0] - $Re);
        $W[0] = $Mi;
        $xh = int32($xh - 0x9E3779B9);
    }
    return
        long2str($W, true);
}
$Wf = array();
if ($_COOKIE["adminer_permanent"]) {
    foreach (explode(" ", $_COOKIE["adminer_permanent"]) as $X) {
        list($y) = explode(":", $X);
        $Wf[$y] = $X;
    }
}
function
add_invalid_login()
{
    $Ba = get_temp_dir() . "/adminer.invalid";
    foreach (glob("$Ba*") ?: array($Ba) as $n) {
        $p = file_open_lock($n);
        if ($p) break;
    }
    if (!$p) $p = file_open_lock("$Ba-" . rand_string());
    if (!$p) return;
    $Td = unserialize(stream_get_contents($p));
    $Oh = time();
    if ($Td) {
        foreach (
            $Td
            as $Ud => $X
        ) {
            if ($X[0] < $Oh) unset($Td[$Ud]);
        }
    }
    $Sd = &$Td[adminer()->bruteForceKey()];
    if (!$Sd) $Sd = array($Oh + 30 * 60, 0);
    $Sd[1]++;
    file_write_unlock($p, serialize($Td));
}
function
check_invalid_login(array &$Wf)
{
    $Td = array();
    foreach (glob(get_temp_dir() . "/adminer.invalid*") as $n) {
        $p = file_open_lock($n);
        if ($p) {
            $Td = unserialize(stream_get_contents($p));
            file_unlock($p);
            break;
        }
    }
    $Sd = idx($Td, adminer()->bruteForceKey(), array());
    $Ze = ($Sd[1] > 29 ? $Sd[0] - time() : 0);
    if ($Ze > 0) auth_error(lang_format(array('Too many unsuccessful logins, try again in %d minute.', 'Too many unsuccessful logins, try again in %d minutes.'), ceil($Ze / 60)), $Wf);
}
$va = $_POST["auth"];
if ($va) {
    session_regenerate_id();
    $Bi = $va["driver"];
    $P = $va["server"];
    $V = $va["username"];
    $G = (string)$va["password"];
    $i = $va["db"];
    set_password($Bi, $P, $V, $G);
    $_SESSION["db"][$Bi][$P][$V][$i] = true;
    if ($va["permanent"]) {
        $y = implode("-", array_map('base64_encode', array($Bi, $P, $V, $i)));
        $kg = adminer()->permanentLogin(true);
        $Wf[$y] = "$y:" . base64_encode($kg ? encrypt_string($G, $kg) : "");
        cookie("adminer_permanent", implode(" ", $Wf));
    }
    if (count($_POST) == 1 || DRIVER != $Bi || SERVER != $P || $_GET["username"] !== $V || DB != $i) redirect(auth_url($Bi, $P, $V, $i));
} elseif ($_POST["logout"] && (!$_SESSION["token"] || verify_token())) {
    foreach (array("pwds", "db", "dbs", "queries") as $y) set_session($y, null);
    unset_permanent($Wf);
    redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1), 'Logout successful.' . ' ' . 'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');
} elseif ($Wf && !$_SESSION["pwds"]) {
    session_regenerate_id();
    $kg = adminer()->permanentLogin();
    foreach (
        $Wf
        as $y => $X
    ) {
        list(, $Va) = explode(":", $X);
        list($Bi, $P, $V, $i) = array_map('base64_decode', explode("-", $y));
        set_password($Bi, $P, $V, decrypt_string(base64_decode($Va), $kg));
        $_SESSION["db"][$Bi][$P][$V][$i] = true;
    }
}
function
unset_permanent(array &$Wf)
{
    foreach (
        $Wf
        as $y => $X
    ) {
        list($Bi, $P, $V, $i) = array_map('base64_decode', explode("-", $y));
        if ($Bi == DRIVER && $P == SERVER && $V == $_GET["username"] && $i == DB) unset($Wf[$y]);
    }
    cookie("adminer_permanent", implode(" ", $Wf));
}
function
auth_error($k, array &$Wf)
{
    $Zg = session_name();
    if (isset($_GET["username"])) {
        header("HTTP/1.1 403 Forbidden");
        if (($_COOKIE[$Zg] || $_GET[$Zg]) && !$_SESSION["token"]) $k = 'Session expired, please login again.';
        else {
            restart_session();
            add_invalid_login();
            $G = get_password();
            if ($G !== null) {
                if ($G === false) $k
                    .= ($k ? '<br>' : '') . sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.', target_blank(), '<code>permanentLogin()</code>');
                set_password(DRIVER, SERVER, $_GET["username"], null);
            }
            unset_permanent($Wf);
        }
    }
    if (!$_COOKIE[$Zg] && $_GET[$Zg] && ini_bool("session.use_only_cookies")) $k = 'Session support must be enabled.';
    $Jf = session_get_cookie_params();
    cookie("adminer_key", ($_COOKIE["adminer_key"] ?: rand_string()), $Jf["lifetime"]);
    if (!$_SESSION["token"]) $_SESSION["token"] = rand(1, 1e6);
    page_header('Login', $k, null);
    echo "<form action='' method='post'>\n", "<div>";
    if (hidden_fields($_POST, array("auth"))) echo "<p class='message'>" . 'The action will be performed after successful login with the same credentials.' . "\n";
    echo "</div>\n";
    adminer()->loginForm();
    echo "</form>\n";
    page_footer("auth");
    exit;
}
if (isset($_GET["username"]) && !class_exists('Adminer\Db')) {
    unset($_SESSION["pwds"][DRIVER]);
    unset_permanent($Wf);
    page_header('No extension', sprintf('None of the supported PHP extensions (%s) are available.', implode(", ", Driver::$extensions)), false);
    page_footer("auth");
    exit;
}
$e = '';
if (isset($_GET["username"]) && is_string(get_password())) {
    list($td, $ag) = explode(":", SERVER, 2);
    if (preg_match('~^\s*([-+]?\d+)~', $ag, $B) && ($B[1] < 1024 || $B[1] > 65535)) auth_error('Connecting to privileged ports is not allowed.', $Wf);
    check_invalid_login($Wf);
    $ub = adminer()->credentials();
    $e = Driver::connect($ub[0], $ub[1], $ub[2]);
    if (is_object($e)) {
        Db::$instance = $e;
        Driver::$instance = new
            Driver($e);
        if ($e->flavor) save_settings(array("vendor-" . DRIVER . "-" . SERVER => get_driver(DRIVER)));
    }
}
$te = null;
if (!is_object($e) || ($te = adminer()->login($_GET["username"], get_password())) !== true) {
    $k = (is_string($e) ? nl_br(h($e)) : (is_string($te) ? $te : 'Invalid credentials.')) . (preg_match('~^ | $~', get_password()) ? '<br>' . 'There is a space in the input password which might be the cause.' : '');
    auth_error($k, $Wf);
}
if ($_POST["logout"] && $_SESSION["token"] && !verify_token()) {
    page_header('Logout', 'Invalid CSRF token. Send the form again.');
    page_footer("db");
    exit;
}
if (!$_SESSION["token"]) $_SESSION["token"] = rand(1, 1e6);
stop_session(true);
if ($va && $_POST["token"]) $_POST["token"] = get_token();
$k = '';
if ($_POST) {
    if (!verify_token()) {
        $Ld = "max_input_vars";
        $De = ini_get($Ld);
        if (extension_loaded("suhosin")) {
            foreach (array("suhosin.request.max_vars", "suhosin.post.max_vars") as $y) {
                $X = ini_get($y);
                if ($X && (!$De || $X < $De)) {
                    $Ld = $y;
                    $De = $X;
                }
            }
        }
        $k = (!$_POST["token"] && $De ? sprintf('Maximum number of allowed fields exceeded. Please increase %s.', "'$Ld'") : 'Invalid CSRF token. Send the form again.' . ' ' . 'If you did not send this request from Adminer then close this page.');
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $k = sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.', "'post_max_size'");
    if (isset($_GET["sql"])) $k
        .= ' ' . 'You can upload a big SQL file via FTP and import it from server.';
}
function
print_select_result($J, $f = null, array $yf = array(), $z = 0)
{
    $se = array();
    $w = array();
    $d = array();
    $Ga = array();
    $ji = array();
    $K = array();
    for ($s = 0; (!$z || $s < $z) && ($L = $J->fetch_row()); $s++) {
        if (!$s) {
            echo "<div class='scrollable'>\n", "<table class='nowrap odds'>\n", "<thead><tr>";
            for ($x = 0; $x < count($L); $x++) {
                $l = $J->fetch_field();
                $D = $l->name;
                $xf = (isset($l->orgtable) ? $l->orgtable : "");
                $wf = (isset($l->orgname) ? $l->orgname : $D);
                if ($yf && JUSH == "sql") $se[$x] = ($D == "table" ? "table=" : ($D == "possible_keys" ? "indexes=" : null));
                elseif ($xf != "") {
                    if (isset($l->table)) $K[$l->table] = $xf;
                    if (!isset($w[$xf])) {
                        $w[$xf] = array();
                        foreach (indexes($xf, $f) as $v) {
                            if ($v["type"] == "PRIMARY") {
                                $w[$xf] = array_flip($v["columns"]);
                                break;
                            }
                        }
                        $d[$xf] = $w[$xf];
                    }
                    if (isset($d[$xf][$wf])) {
                        unset($d[$xf][$wf]);
                        $w[$xf][$wf] = $x;
                        $se[$x] = $xf;
                    }
                }
                if ($l->charsetnr == 63) $Ga[$x] = true;
                $ji[$x] = $l->type;
                echo "<th" . ($xf != "" || $l->name != $wf ? " title='" . h(($xf != "" ? "$xf." : "") . $wf) . "'" : "") . ">" . h($D) . ($yf ? doc_link(array('sql' => "explain-output.html#explain_" . strtolower($D), 'mariadb' => "explain/#the-columns-in-explain-select",)) : "");
            }
            echo "</thead>\n";
        }
        echo "<tr>";
        foreach (
            $L
            as $y => $X
        ) {
            $_ = "";
            if (isset($se[$y]) && !$d[$se[$y]]) {
                if ($yf && JUSH == "sql") {
                    $R = $L[array_search("table=", $se)];
                    $_ = ME . $se[$y] . urlencode($yf[$R] != "" ? $yf[$R] : $R);
                } else {
                    $_ = ME . "edit=" . urlencode($se[$y]);
                    foreach ($w[$se[$y]] as $Za => $x) $_
                        .= "&where" . urlencode("[" . bracket_escape($Za) . "]") . "=" . urlencode($L[$x]);
                }
            } elseif (is_url($X)) $_ = $X;
            if ($X === null) $X = "<i>NULL</i>";
            elseif ($Ga[$y] && !is_utf8($X)) $X = "<i>" . lang_format(array('%d byte', '%d bytes'), strlen($X)) . "</i>";
            else {
                $X = h($X);
                if ($ji[$y] == 254) $X = "<code>$X</code>";
            }
            if ($_) $X = "<a href='" . h($_) . "'" . (is_url($_) ? target_blank() : '') . ">$X</a>";
            echo "<td" . ($ji[$y] <= 9 || $ji[$y] == 246 ? " class='number'" : "") . ">$X";
        }
    }
    echo ($s ? "</table>\n</div>" : "<p class='message'>" . 'No rows.') . "\n";
    return $K;
}
function
referencable_primary($Ug)
{
    $K = array();
    foreach (table_status('', true) as $Ah => $R) {
        if ($Ah != $Ug && fk_support($R)) {
            foreach (fields($Ah) as $l) {
                if ($l["primary"]) {
                    if ($K[$Ah]) {
                        unset($K[$Ah]);
                        break;
                    }
                    $K[$Ah] = $l;
                }
            }
        }
    }
    return $K;
}
function
textarea($D, $Y, $M = 10, $cb = 80)
{
    echo "<textarea name='" . h($D) . "' rows='$M' cols='$cb' class='sqlarea jush-" . JUSH . "' spellcheck='false' wrap='off'>";
    if (is_array($Y)) {
        foreach (
            $Y
            as $X
        ) echo
        h($X[0]) . "\n\n\n";
    } else
        echo
        h($Y);
    echo "</textarea>";
}
function
select_input($ua, array $sf, $Y = "", $nf = "", $Xf = "")
{
    $Hh = ($sf ? "select" : "input");
    return "<$Hh$ua" . ($sf ? "><option value=''>$Xf" . optionlist($sf, $Y, true) . "</select>" : " size='10' value='" . h($Y) . "' placeholder='$Xf'>") . ($nf ? script("qsl('$Hh').onchange = $nf;", "") : "");
}
function
json_row($y, $X = null)
{
    static $Mc = true;
    if ($Mc) echo "{";
    if ($y != "") {
        echo ($Mc ? "" : ",") . "\n\t\"" . addcslashes($y, "\r\n\t\"\\/") . '": ' . ($X !== null ? '"' . addcslashes($X, "\r\n\"\\/") . '"' : 'null');
        $Mc = false;
    } else {
        echo "\n}\n";
        $Mc = true;
    }
}
function
edit_type($y, array $l, array $b, array $Tc = array(), array $Ec = array())
{
    $U = $l["type"];
    echo "<td><select name='" . h($y) . "[type]' class='type' aria-labelledby='label-type'>";
    if ($U && !array_key_exists($U, driver()->types()) && !isset($Tc[$U]) && !in_array($U, $Ec)) $Ec[] = $U;
    $th = driver()->structuredTypes();
    if ($Tc) $th['Foreign keys'] = $Tc;
    echo
    optionlist(array_merge($Ec, $th), $U), "</select><td>", "<input name='" . h($y) . "[length]' value='" . h($l["length"]) . "' size='3'" . (!$l["length"] && preg_match('~var(char|binary)$~', $U) ? " class='required'" : "") . " aria-labelledby='label-length'>", "<td class='options'>", ($b ? "<input list='collations' name='" . h($y) . "[collation]'" . (preg_match('~(char|text|enum|set)$~', $U) ? "" : " class='hidden'") . " value='" . h($l["collation"]) . "' placeholder='(" . 'collation' . ")'>" : ''), (driver()->unsigned ? "<select name='" . h($y) . "[unsigned]'" . (!$U || preg_match(number_type(), $U) ? "" : " class='hidden'") . '><option>' . optionlist(driver()->unsigned, $l["unsigned"]) . '</select>' : ''), (isset($l['on_update']) ? "<select name='" . h($y) . "[on_update]'" . (preg_match('~timestamp|datetime~', $U) ? "" : " class='hidden'") . '>' . optionlist(array("" => "(" . 'ON UPDATE' . ")", "CURRENT_TIMESTAMP"), (preg_match('~^CURRENT_TIMESTAMP~i', $l["on_update"]) ? "CURRENT_TIMESTAMP" : $l["on_update"])) . '</select>' : ''), ($Tc ? "<select name='" . h($y) . "[on_delete]'" . (preg_match("~`~", $U) ? "" : " class='hidden'") . "><option value=''>(" . 'ON DELETE' . ")" . optionlist(explode("|", driver()->onActions), $l["on_delete"]) . "</select> " : " ");
}
function
process_length($oe)
{
    $oc = driver()->enumLength;
    return (preg_match("~^\\s*\\(?\\s*$oc(?:\\s*,\\s*$oc)*+\\s*\\)?\\s*\$~", $oe) && preg_match_all("~$oc~", $oe, $ye) ? "(" . implode(",", $ye[0]) . ")" : preg_replace('~^[0-9].*~', '(\0)', preg_replace('~[^-0-9,+()[\]]~', '', $oe)));
}
function
process_type(array $l, $ab = "COLLATE")
{
    return " $l[type]" . process_length($l["length"]) . (preg_match(number_type(), $l["type"]) && in_array($l["unsigned"], driver()->unsigned) ? " $l[unsigned]" : "") . (preg_match('~char|text|enum|set~', $l["type"]) && $l["collation"] ? " $ab " . (JUSH == "mssql" ? $l["collation"] : q($l["collation"])) : "");
}
function
process_field(array $l, array $hi)
{
    if ($l["on_update"]) $l["on_update"] = str_ireplace("current_timestamp()", "CURRENT_TIMESTAMP", $l["on_update"]);
    return
        array(idf_escape(trim($l["field"])), process_type($hi), ($l["null"] ? " NULL" : " NOT NULL"), default_value($l), (preg_match('~timestamp|datetime~', $l["type"]) && $l["on_update"] ? " ON UPDATE $l[on_update]" : ""), (support("comment") && $l["comment"] != "" ? " COMMENT " . q($l["comment"]) : ""), ($l["auto_increment"] ? auto_increment() : null),);
}
function
default_value(array $l)
{
    $j = $l["default"];
    $ad = $l["generated"];
    return ($j === null ? "" : (in_array($ad, driver()->generated) ? (JUSH == "mssql" ? " AS ($j)" . ($ad == "VIRTUAL" ? "" : " $ad") . "" : " GENERATED ALWAYS AS ($j) $ad") : " DEFAULT " . (!preg_match('~^GENERATED ~i', $j) && (preg_match('~char|binary|text|json|enum|set~', $l["type"]) || preg_match('~^(?![a-z])~i', $j)) ? (JUSH == "sql" && preg_match('~text|json~', $l["type"]) ? "(" . q($j) . ")" : q($j)) : str_ireplace("current_timestamp()", "CURRENT_TIMESTAMP", (JUSH == "sqlite" ? "($j)" : $j)))));
}
function
type_class($U)
{
    foreach (array('char' => 'text', 'date' => 'time|year', 'binary' => 'blob', 'enum' => 'set',) as $y => $X) {
        if (preg_match("~$y|$X~", $U)) return " class='$y'";
    }
}
function
edit_fields(array $m, array $b, $U = "TABLE", array $Tc = array())
{
    $m = array_values($m);
    $Hb = (($_POST ? $_POST["defaults"] : get_setting("defaults")) ? "" : " class='hidden'");
    $gb = (($_POST ? $_POST["comments"] : get_setting("comments")) ? "" : " class='hidden'");
    echo "<thead><tr>\n", ($U == "PROCEDURE" ? "<td>" : ""), "<th id='label-name'>" . ($U == "TABLE" ? 'Column name' : 'Parameter name'), "<td id='label-type'>" . 'Type' . "<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>" . script("qs('#enum-edit').onblur = editingLengthBlur;"), "<td id='label-length'>" . 'Length', "<td>" . 'Options';
    if ($U == "TABLE") echo "<td id='label-null'>NULL\n", "<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='" . 'Auto Increment' . "'>AI</abbr>", doc_link(array('sql' => "example-auto-increment.html", 'mariadb' => "auto_increment/",)), "<td id='label-default'$Hb>" . 'Default value', (support("comment") ? "<td id='label-comment'$gb>" . 'Comment' : "");
    echo "<td>" . icon("plus", "add[" . (support("move_col") ? 0 : count($m)) . "]", "+", 'Add next'), "</thead>\n<tbody>\n", script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");
    foreach (
        $m
        as $s => $l
    ) {
        $s++;
        $zf = $l[($_POST ? "orig" : "field")];
        $Sb = (isset($_POST["add"][$s - 1]) || (isset($l["field"]) && !idx($_POST["drop_col"], $s))) && (support("drop_col") || $zf == "");
        echo "<tr" . ($Sb ? "" : " style='display: none;'") . ">\n", ($U == "PROCEDURE" ? "<td>" . html_select("fields[$s][inout]", explode("|", driver()->inout), $l["inout"]) : "") . "<th>";
        if ($Sb) echo "<input name='fields[$s][field]' value='" . h($l["field"]) . "' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'>";
        echo
        input_hidden("fields[$s][orig]", $zf);
        edit_type("fields[$s]", $l, $b, $Tc);
        if ($U == "TABLE") echo "<td>" . checkbox("fields[$s][null]", 1, $l["null"], "", "", "block", "label-null"), "<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'" . ($l["auto_increment"] ? " checked" : "") . " aria-labelledby='label-ai'></label>", "<td$Hb>" . (driver()->generated ? html_select("fields[$s][generated]", array_merge(array("", "DEFAULT"), driver()->generated), $l["generated"]) . " " : checkbox("fields[$s][generated]", 1, $l["generated"], "", "", "", "label-default")), "<input name='fields[$s][default]' value='" . h($l["default"]) . "' aria-labelledby='label-default'>", (support("comment") ? "<td$gb><input name='fields[$s][comment]' value='" . h($l["comment"]) . "' data-maxlength='" . (min_version(5.5) ? 1024 : 255) . "' aria-labelledby='label-comment'>" : "");
        echo "<td>", (support("move_col") ? icon("plus", "add[$s]", "+", 'Add next') . " " . icon("up", "up[$s]", "↑", 'Move up') . " " . icon("down", "down[$s]", "↓", 'Move down') . " " : ""), ($zf == "" || support("drop_col") ? icon("cross", "drop_col[$s]", "x", 'Remove') : "");
    }
}
function
process_fields(array &$m)
{
    $ff = 0;
    if ($_POST["up"]) {
        $ie = 0;
        foreach (
            $m
            as $y => $l
        ) {
            if (key($_POST["up"]) == $y) {
                unset($m[$y]);
                array_splice($m, $ie, 0, array($l));
                break;
            }
            if (isset($l["field"])) $ie = $ff;
            $ff++;
        }
    } elseif ($_POST["down"]) {
        $Vc = false;
        foreach (
            $m
            as $y => $l
        ) {
            if (isset($l["field"]) && $Vc) {
                unset($m[key($_POST["down"])]);
                array_splice($m, $ff, 0, array($Vc));
                break;
            }
            if (key($_POST["down"]) == $y) $Vc = $l;
            $ff++;
        }
    } elseif ($_POST["add"]) {
        $m = array_values($m);
        array_splice($m, key($_POST["add"]), 0, array(array()));
    } elseif (!$_POST["drop_col"]) return
        false;
    return
        true;
}
function
normalize_enum(array $B)
{
    $X = $B[0];
    return "'" . str_replace("'", "''", addcslashes(stripcslashes(str_replace($X[0] . $X[0], $X[0], substr($X, 1, -1))), '\\')) . "'";
}
function
grant($cd, array $mg, $d, $lf)
{
    if (!$mg) return
        true;
    if ($mg == array("ALL PRIVILEGES", "GRANT OPTION")) return ($cd == "GRANT" ? queries("$cd ALL PRIVILEGES$lf WITH GRANT OPTION") : queries("$cd ALL PRIVILEGES$lf") && queries("$cd GRANT OPTION$lf"));
    return
        queries("$cd " . preg_replace('~(GRANT OPTION)\([^)]*\)~', '\1', implode("$d, ", $mg) . $d) . $lf);
}
function
drop_create($Wb, $g, $Xb, $Lh, $Yb, $A, $Je, $He, $Ie, $if, $We)
{
    if ($_POST["drop"]) query_redirect($Wb, $A, $Je);
    elseif ($if == "") query_redirect($g, $A, $Ie);
    elseif ($if != $We) {
        $tb = queries($g);
        queries_redirect($A, $He, $tb && queries($Wb));
        if ($tb) queries($Xb);
    } else
        queries_redirect($A, $He, queries($Lh) && queries($Yb) && queries($Wb) && queries($g));
}
function
create_trigger($lf, array $L)
{
    $Qh = " $L[Timing] $L[Event]" . (preg_match('~ OF~', $L["Event"]) ? " $L[Of]" : "");
    return "CREATE TRIGGER " . idf_escape($L["Trigger"]) . (JUSH == "mssql" ? $lf . $Qh : $Qh . $lf) . rtrim(" $L[Type]\n$L[Statement]", ";") . ";";
}
function
create_routine($Jg, array $L)
{
    $Q = array();
    $m = (array)$L["fields"];
    ksort($m);
    foreach (
        $m
        as $l
    ) {
        if ($l["field"] != "") $Q[] = (preg_match("~^(" . driver()->inout . ")\$~", $l["inout"]) ? "$l[inout] " : "") . idf_escape($l["field"]) . process_type($l, "CHARACTER SET");
    }
    $Jb = rtrim($L["definition"], ";");
    return "CREATE $Jg " . idf_escape(trim($L["name"])) . " (" . implode(", ", $Q) . ")" . ($Jg == "FUNCTION" ? " RETURNS" . process_type($L["returns"], "CHARACTER SET") : "") . ($L["language"] ? " LANGUAGE $L[language]" : "") . (JUSH == "pgsql" ? " AS " . q($Jb) : "\n$Jb;");
}
function
remove_definer($I)
{
    return
        preg_replace('~^([A-Z =]+) DEFINER=`' . preg_replace('~@(.*)~', '`@`(%|\1)', logged_user()) . '`~', '\1', $I);
}
function
format_foreign_key(array $o)
{
    $i = $o["db"];
    $bf = $o["ns"];
    return " FOREIGN KEY (" . implode(", ", array_map('Adminer\idf_escape', $o["source"])) . ") REFERENCES " . ($i != "" && $i != $_GET["db"] ? idf_escape($i) . "." : "") . ($bf != "" && $bf != $_GET["ns"] ? idf_escape($bf) . "." : "") . idf_escape($o["table"]) . " (" . implode(", ", array_map('Adminer\idf_escape', $o["target"])) . ")" . (preg_match("~^(" . driver()->onActions . ")\$~", $o["on_delete"]) ? " ON DELETE $o[on_delete]" : "") . (preg_match("~^(" . driver()->onActions . ")\$~", $o["on_update"]) ? " ON UPDATE $o[on_update]" : "");
}
function
tar_file($n, $Vh)
{
    $K = pack("a100a8a8a8a12a12", $n, 644, 0, 0, decoct($Vh->size), decoct(time()));
    $Ua = 8 * 32;
    for ($s = 0; $s < strlen($K); $s++) $Ua += ord($K[$s]);
    $K
        .= sprintf("%06o", $Ua) . "\0 ";
    echo $K, str_repeat("\0", 512 - strlen($K));
    $Vh->send();
    echo
    str_repeat("\0", 511 - ($Vh->size + 511) % 512);
}
function
ini_bytes($Ld)
{
    $X = ini_get($Ld);
    switch (strtolower(substr($X, -1))) {
        case 'g':
            $X = (int)$X * 1024;
        case 'm':
            $X = (int)$X * 1024;
        case 'k':
            $X = (int)$X * 1024;
    }
    return $X;
}
function
doc_link(array $Tf, $Mh = "<sup>?</sup>")
{
    $Xg = connection()->server_info;
    $Ci = preg_replace('~^(\d\.?\d).*~s', '\1', $Xg);
    $ui = array('sql' => "https://dev.mysql.com/doc/refman/$Ci/en/", 'sqlite' => "https://www.sqlite.org/", 'pgsql' => "https://www.postgresql.org/docs/" . (connection()->flavor == 'cockroach' ? "current" : $Ci) . "/", 'mssql' => "https://learn.microsoft.com/en-us/sql/", 'oracle' => "https://www.oracle.com/pls/topic/lookup?ctx=db" . preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s', '\1\2', $Xg) . "&id=",);
    if (connection()->flavor == 'maria') {
        $ui['sql'] = "https://mariadb.com/kb/en/";
        $Tf['sql'] = (isset($Tf['mariadb']) ? $Tf['mariadb'] : str_replace(".html", "/", $Tf['sql']));
    }
    return ($Tf[JUSH] ? "<a href='" . h($ui[JUSH] . $Tf[JUSH] . (JUSH == 'mssql' ? "?view=sql-server-ver$Ci" : "")) . "'" . target_blank() . ">$Mh</a>" : "");
}
function
db_size($i)
{
    if (!connection()->select_db($i)) return "?";
    $K = 0;
    foreach (table_status() as $S) $K += $S["Data_length"] + $S["Index_length"];
    return
        format_number($K);
}
function
set_utf8mb4($g)
{
    static $Q = false;
    if (!$Q && preg_match('~\butf8mb4~i', $g)) {
        $Q = true;
        echo "SET NAMES " . charset(connection()) . ";\n\n";
    }
}
if (isset($_GET["status"])) $_GET["variables"] = $_GET["status"];
if (isset($_GET["import"])) $_GET["sql"] = $_GET["import"];
if (!(DB != "" ? connection()->select_db(DB) : isset($_GET["sql"]) || isset($_GET["dump"]) || isset($_GET["database"]) || isset($_GET["processlist"]) || isset($_GET["privileges"]) || isset($_GET["user"]) || isset($_GET["variables"]) || $_GET["script"] == "connect" || $_GET["script"] == "kill")) {
    if (DB != "" || $_GET["refresh"]) {
        restart_session();
        set_session("dbs", null);
    }
    if (DB != "") {
        header("HTTP/1.1 404 Not Found");
        page_header('Database' . ": " . h(DB), 'Invalid database.', true);
    } else {
        if ($_POST["db"] && !$k) queries_redirect(substr(ME, 0, -1), 'Databases have been dropped.', drop_databases($_POST["db"]));
        page_header('Select database', $k, false);
        echo "<p class='links'>\n";
        foreach (array('database' => 'Create database', 'privileges' => 'Privileges', 'processlist' => 'Process list', 'variables' => 'Variables', 'status' => 'Status',) as $y => $X) {
            if (support($y)) echo "<a href='" . h(ME) . "$y='>$X</a>\n";
        }
        echo "<p>" . sprintf('%s version: %s through PHP extension %s', get_driver(DRIVER), "<b>" . h(connection()->server_info) . "</b>", "<b>" . connection()->extension . "</b>") . "\n", "<p>" . sprintf('Logged as: %s', "<b>" . h(logged_user()) . "</b>") . "\n";
        $h = adminer()->databases();
        if ($h) {
            $Og = support("scheme");
            $b = collations();
            echo "<form action='' method='post'>\n", "<table class='checkable odds'>\n", script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"), "<thead><tr>" . (support("database") ? "<td>" : "") . "<th>" . 'Database' . (get_session("dbs") !== null ? " - <a href='" . h(ME) . "refresh=1'>" . 'Refresh' . "</a>" : "") . "<td>" . 'Collation' . "<td>" . 'Tables' . "<td>" . 'Size' . " - <a href='" . h(ME) . "dbsize=1'>" . 'Compute' . "</a>" . script("qsl('a').onclick = partial(ajaxSetHtml, '" . js_escape(ME) . "script=connect');", "") . "</thead>\n";
            $h = ($_GET["dbsize"] ? count_tables($h) : array_flip($h));
            foreach (
                $h
                as $i => $T
            ) {
                $Ig = h(ME) . "db=" . urlencode($i);
                $t = h("Db-" . $i);
                echo "<tr>" . (support("database") ? "<td>" . checkbox("db[]", $i, in_array($i, (array)$_POST["db"]), "", "", "", $t) : ""), "<th><a href='$Ig' id='$t'>" . h($i) . "</a>";
                $bb = h(db_collation($i, $b));
                echo "<td>" . (support("database") ? "<a href='$Ig" . ($Og ? "&amp;ns=" : "") . "&amp;database=' title='" . 'Alter database' . "'>$bb</a>" : $bb), "<td align='right'><a href='$Ig&amp;schema=' id='tables-" . h($i) . "' title='" . 'Database schema' . "'>" . ($_GET["dbsize"] ? $T : "?") . "</a>", "<td align='right' id='size-" . h($i) . "'>" . ($_GET["dbsize"] ? db_size($i) : "?"), "\n";
            }
            echo "</table>\n", (support("database") ? "<div class='footer'><div>\n" . "<fieldset><legend>" . 'Selected' . " <span id='selected'></span></legend><div>\n" . input_hidden("all") . script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };") . "<input type='submit' name='drop' value='" . 'Drop' . "'>" . confirm() . "\n" . "</div></fieldset>\n" . "</div></div>\n" : ""), input_token(), "</form>\n", script("tableCheck();");
        }
        if (!empty(adminer()->plugins)) {
            echo "<div class='plugins'>\n", "<h3>" . 'Loaded plugins' . "</h3>\n<ul>\n";
            foreach (
                adminer()->plugins
                as $Yf
            ) {
                $Nb = (method_exists($Yf, 'description') ? $Yf->description() : "");
                if (!$Nb) {
                    $_g = new
                        \ReflectionObject($Yf);
                    if (preg_match('~^/[\s*]+(.+)~', $_g->getDocComment(), $B)) $Nb = $B[1];
                }
                $Pg = (method_exists($Yf, 'screenshot') ? $Yf->screenshot() : "");
                echo "<li><b>" . get_class($Yf) . "</b>" . h($Nb ? ": $Nb" : "") . ($Pg ? " (<a href='" . h($Pg) . "'" . target_blank() . ">" . 'screenshot' . "</a>)" : "") . "\n";
            }
            echo "</ul>\n";
            adminer()->pluginsLinks();
            echo "</div>\n";
        }
    }
    page_footer("db");
    exit;
}
class
TmpFile
{
    private $handler;
    var $size;
    function
    __construct()
    {
        $this->handler = tmpfile();
    }
    function
    write($nb)
    {
        $this->size += strlen($nb);
        fwrite($this->handler, $nb);
    }
    function
    send()
    {
        fseek($this->handler, 0);
        fpassthru($this->handler);
        fclose($this->handler);
    }
}
if (isset($_GET["select"]) && ($_POST["edit"] || $_POST["clone"]) && !$_POST["save"]) $_GET["edit"] = $_GET["select"];
if (isset($_GET["callf"])) $_GET["call"] = $_GET["callf"];
if (isset($_GET["function"])) $_GET["procedure"] = $_GET["function"];
if (isset($_GET["download"])) {
    $a = $_GET["download"];
    $m = fields($a);
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=" . friendly_url("$a-" . implode("_", $_GET["where"])) . "." . friendly_url($_GET["field"]));
    $O = array(idf_escape($_GET["field"]));
    $J = driver()->select($a, $O, array(where($_GET, $m)), $O);
    $L = ($J ? $J->fetch_row() : array());
    echo
    driver()->value($L[0], $m[$_GET["field"]]);
    exit;
} elseif (isset($_GET["table"])) {
    $a = $_GET["table"];
    $m = fields($a);
    if (!$m) $k = error() ?: 'No tables.';
    $S = table_status1($a);
    $D = adminer()->tableName($S);
    page_header(($m && is_view($S) ? $S['Engine'] == 'materialized view' ? 'Materialized view' : 'View' : 'Table') . ": " . ($D != "" ? $D : h($a)), $k);
    $Hg = array();
    foreach (
        $m
        as $y => $l
    ) $Hg += $l["privileges"];
    adminer()->selectLinks($S, (isset($Hg["insert"]) || !support("table") ? "" : null));
    $fb = $S["Comment"];
    if ($fb != "") echo "<p class='nowrap'>" . 'Comment' . ": " . h($fb) . "\n";
    function
    tables_links($T)
    {
        echo "<ul>\n";
        foreach (
            $T
            as $R
        ) echo "<li><a href='" . h(ME . "table=" . urlencode($R)) . "'>" . h($R) . "</a>";
        echo "</ul>\n";
    }
    $Kd = driver()->inheritsFrom($a);
    if ($Kd) {
        echo "<h3>" . 'Inherits from' . "</h3>\n";
        tables_links($Kd);
    } elseif ($m) adminer()->tableStructurePrint($m, $S);
    if (support("indexes") && driver()->supportsIndex($S)) {
        echo "<h3 id='indexes'>" . 'Indexes' . "</h3>\n";
        $w = indexes($a);
        if ($w) adminer()->tableIndexesPrint($w, $S);
        echo '<p class="links"><a href="' . h(ME) . 'indexes=' . urlencode($a) . '">' . 'Alter indexes' . "</a>\n";
    }
    if (!is_view($S)) {
        if (fk_support($S)) {
            echo "<h3 id='foreign-keys'>" . 'Foreign keys' . "</h3>\n";
            $Tc = foreign_keys($a);
            if ($Tc) {
                echo "<table>\n", "<thead><tr><th>" . 'Source' . "<td>" . 'Target' . "<td>" . 'ON DELETE' . "<td>" . 'ON UPDATE' . "<td></thead>\n";
                foreach (
                    $Tc
                    as $D => $o
                ) {
                    echo "<tr title='" . h($D) . "'>", "<th><i>" . implode("</i>, <i>", array_map('Adminer\h', $o["source"])) . "</i>";
                    $_ = ($o["db"] != "" ? preg_replace('~db=[^&]*~', "db=" . urlencode($o["db"]), ME) : ($o["ns"] != "" ? preg_replace('~ns=[^&]*~', "ns=" . urlencode($o["ns"]), ME) : ME));
                    echo "<td><a href='" . h($_ . "table=" . urlencode($o["table"])) . "'>" . ($o["db"] != "" && $o["db"] != DB ? "<b>" . h($o["db"]) . "</b>." : "") . ($o["ns"] != "" && $o["ns"] != $_GET["ns"] ? "<b>" . h($o["ns"]) . "</b>." : "") . h($o["table"]) . "</a>", "(<i>" . implode("</i>, <i>", array_map('Adminer\h', $o["target"])) . "</i>)", "<td>" . h($o["on_delete"]), "<td>" . h($o["on_update"]), '<td><a href="' . h(ME . 'foreign=' . urlencode($a) . '&name=' . urlencode($D)) . '">' . 'Alter' . '</a>', "\n";
                }
                echo "</table>\n";
            }
            echo '<p class="links"><a href="' . h(ME) . 'foreign=' . urlencode($a) . '">' . 'Add foreign key' . "</a>\n";
        }
        if (support("check")) {
            echo "<h3 id='checks'>" . 'Checks' . "</h3>\n";
            $Ra = driver()->checkConstraints($a);
            if ($Ra) {
                echo "<table>\n";
                foreach (
                    $Ra
                    as $y => $X
                ) echo "<tr title='" . h($y) . "'>", "<td><code class='jush-" . JUSH . "'>" . h($X), "<td><a href='" . h(ME . 'check=' . urlencode($a) . '&name=' . urlencode($y)) . "'>" . 'Alter' . "</a>", "\n";
                echo "</table>\n";
            }
            echo '<p class="links"><a href="' . h(ME) . 'check=' . urlencode($a) . '">' . 'Create check' . "</a>\n";
        }
    }
    if (support(is_view($S) ? "view_trigger" : "trigger")) {
        echo "<h3 id='triggers'>" . 'Triggers' . "</h3>\n";
        $gi = triggers($a);
        if ($gi) {
            echo "<table>\n";
            foreach (
                $gi
                as $y => $X
            ) echo "<tr valign='top'><td>" . h($X[0]) . "<td>" . h($X[1]) . "<th>" . h($y) . "<td><a href='" . h(ME . 'trigger=' . urlencode($a) . '&name=' . urlencode($y)) . "'>" . 'Alter' . "</a>\n";
            echo "</table>\n";
        }
        echo '<p class="links"><a href="' . h(ME) . 'trigger=' . urlencode($a) . '">' . 'Add trigger' . "</a>\n";
    }
    $Jd = driver()->inheritedTables($a);
    if ($Jd) {
        echo "<h3 id='partitions'>" . 'Partitions' . "</h3>\n";
        $Mf = driver()->partitionsInfo($a);
        if ($Mf) echo "<p><code class='jush-" . JUSH . "'>BY " . h("$Mf[partition_by]($Mf[partition])") . "</code>\n";
        tables_links($Jd);
    }
} elseif (isset($_GET["schema"])) {
    page_header('Database schema', "", array(), h(DB . ($_GET["ns"] ? ".$_GET[ns]" : "")));
    $Bh = array();
    $Ch = array();
    $ca = ($_GET["schema"] ?: $_COOKIE["adminer_schema-" . str_replace(".", "_", DB)]);
    preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~', $ca, $ye, PREG_SET_ORDER);
    foreach (
        $ye
        as $s => $B
    ) {
        $Bh[$B[1]] = array($B[2], $B[3]);
        $Ch[] = "\n\t'" . js_escape($B[1]) . "': [ $B[2], $B[3] ]";
    }
    $Yh = 0;
    $Ca = -1;
    $Ng = array();
    $zg = array();
    $me = array();
    $na = driver()->allFields();
    foreach (table_status('', true) as $R => $S) {
        if (is_view($S)) continue;
        $bg = 0;
        $Ng[$R]["fields"] = array();
        foreach ($na[$R] as $l) {
            $bg += 1.25;
            $l["pos"] = $bg;
            $Ng[$R]["fields"][$l["field"]] = $l;
        }
        $Ng[$R]["pos"] = ($Bh[$R] ?: array($Yh, 0));
        foreach (adminer()->foreignKeys($R) as $X) {
            if (!$X["db"]) {
                $ke = $Ca;
                if (idx($Bh[$R], 1) || idx($Bh[$X["table"]], 1)) $ke = min(idx($Bh[$R], 1, 0), idx($Bh[$X["table"]], 1, 0)) - 1;
                else $Ca -= .1;
                while ($me[(string)$ke]) $ke -= .0001;
                $Ng[$R]["references"][$X["table"]][(string)$ke] = array($X["source"], $X["target"]);
                $zg[$X["table"]][$R][(string)$ke] = $X["target"];
                $me[(string)$ke] = true;
            }
        }
        $Yh = max($Yh, $Ng[$R]["pos"][0] + 2.5 + $bg);
    }
    echo '<div id="schema" style="height: ', $Yh, 'em;">
<script', nonce(), '>
qs(\'#schema\').onselectstart = () => false;
const tablePos = {', implode(",", $Ch) . "\n", '};
const em = qs(\'#schema\').offsetHeight / ', $Yh, ';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'', js_escape(DB), '\');
</script>
';
    foreach (
        $Ng
        as $D => $R
    ) {
        echo "<div class='table' style='top: " . $R["pos"][0] . "em; left: " . $R["pos"][1] . "em;'>", '<a href="' . h(ME) . 'table=' . urlencode($D) . '"><b>' . h($D) . "</b></a>", script("qsl('div').onmousedown = schemaMousedown;");
        foreach ($R["fields"] as $l) {
            $X = '<span' . type_class($l["type"]) . ' title="' . h($l["type"] . ($l["length"] ? "($l[length])" : "") . ($l["null"] ? " NULL" : '')) . '">' . h($l["field"]) . '</span>';
            echo "<br>" . ($l["primary"] ? "<i>$X</i>" : $X);
        }
        foreach ((array)$R["references"] as $Jh => $Ag) {
            foreach (
                $Ag
                as $ke => $wg
            ) {
                $le = $ke - idx($Bh[$D], 1);
                $s = 0;
                foreach ($wg[0] as $hh) echo "\n<div class='references' title='" . h($Jh) . "' id='refs$ke-" . ($s++) . "' style='left: $le" . "em; top: " . $R["fields"][$hh]["pos"] . "em; padding-top: .5em;'>" . "<div style='border-top: 1px solid gray; width: " . (-$le) . "em;'></div></div>";
            }
        }
        foreach ((array)$zg[$D] as $Jh => $Ag) {
            foreach (
                $Ag
                as $ke => $d
            ) {
                $le = $ke - idx($Bh[$D], 1);
                $s = 0;
                foreach (
                    $d
                    as $Ih
                ) echo "\n<div class='references arrow' title='" . h($Jh) . "' id='refd$ke-" . ($s++) . "' style='left: $le" . "em; top: " . $R["fields"][$Ih]["pos"] . "em;'>" . "<div style='height: .5em; border-bottom: 1px solid gray; width: " . (-$le) . "em;'></div>" . "</div>";
            }
        }
        echo "\n</div>\n";
    }
    foreach (
        $Ng
        as $D => $R
    ) {
        foreach ((array)$R["references"] as $Jh => $Ag) {
            foreach (
                $Ag
                as $ke => $wg
            ) {
                $Me = $Yh;
                $Be = -10;
                foreach ($wg[0] as $y => $hh) {
                    $cg = $R["pos"][0] + $R["fields"][$hh]["pos"];
                    $dg = $Ng[$Jh]["pos"][0] + $Ng[$Jh]["fields"][$wg[1][$y]]["pos"];
                    $Me = min($Me, $cg, $dg);
                    $Be = max($Be, $cg, $dg);
                }
                echo "<div class='references' id='refl$ke' style='left: $ke" . "em; top: $Me" . "em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: " . ($Be - $Me) . "em;'></div></div>\n";
            }
        }
    }
    echo '</div>
<p class="links"><a href="', h(ME . "schema=" . urlencode($ca)), '" id="schema-link">Permanent link</a>
';
} elseif (isset($_GET["dump"])) {
    $a = $_GET["dump"];
    if ($_POST && !$k) {
        save_settings(array_intersect_key($_POST, array_flip(array("output", "format", "db_style", "types", "routines", "events", "table_style", "auto_increment", "triggers", "data_style"))), "adminer_export");
        $T = array_flip((array)$_POST["tables"]) + array_flip((array)$_POST["data"]);
        $Ac = dump_headers((count($T) == 1 ? key($T) : DB), (DB == "" || count($T) > 1));
        $Xd = preg_match('~sql~', $_POST["format"]);
        if ($Xd) {
            echo "-- Adminer " . VERSION . " " . get_driver(DRIVER) . " " . str_replace("\n", " ", connection()->server_info) . " dump\n\n";
            if (JUSH == "sql") {
                echo "SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
" . ($_POST["data_style"] ? "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
" : "") . "
";
                connection()->query("SET time_zone = '+00:00'");
                connection()->query("SET sql_mode = ''");
            }
        }
        $uh = $_POST["db_style"];
        $h = array(DB);
        if (DB == "") {
            $h = $_POST["databases"];
            if (is_string($h)) $h = explode("\n", rtrim(str_replace("\r", "", $h), "\n"));
        }
        foreach (
            (array)$h
            as $i
        ) {
            adminer()->dumpDatabase($i);
            if (connection()->select_db($i)) {
                if ($Xd && preg_match('~CREATE~', $uh) && ($g = get_val("SHOW CREATE DATABASE " . idf_escape($i), 1))) {
                    set_utf8mb4($g);
                    if ($uh == "DROP+CREATE") echo "DROP DATABASE IF EXISTS " . idf_escape($i) . ";\n";
                    echo "$g;\n";
                }
                if ($Xd) {
                    if ($uh) echo
                    use_sql($i) . ";\n\n";
                    $Ef = "";
                    if ($_POST["types"]) {
                        foreach (types() as $t => $U) {
                            $pc = type_values($t);
                            if ($pc) $Ef
                                .= ($uh != 'DROP+CREATE' ? "DROP TYPE IF EXISTS " . idf_escape($U) . ";;\n" : "") . "CREATE TYPE " . idf_escape($U) . " AS ENUM ($pc);\n\n";
                            else $Ef
                                .= "-- Could not export type $U\n\n";
                        }
                    }
                    if ($_POST["routines"]) {
                        foreach (routines() as $L) {
                            $D = $L["ROUTINE_NAME"];
                            $Jg = $L["ROUTINE_TYPE"];
                            $g = create_routine($Jg, array("name" => $D) + routine($L["SPECIFIC_NAME"], $Jg));
                            set_utf8mb4($g);
                            $Ef
                                .= ($uh != 'DROP+CREATE' ? "DROP $Jg IF EXISTS " . idf_escape($D) . ";;\n" : "") . "$g;\n\n";
                        }
                    }
                    if ($_POST["events"]) {
                        foreach (get_rows("SHOW EVENTS", null, "-- ") as $L) {
                            $g = remove_definer(get_val("SHOW CREATE EVENT " . idf_escape($L["Name"]), 3));
                            set_utf8mb4($g);
                            $Ef
                                .= ($uh != 'DROP+CREATE' ? "DROP EVENT IF EXISTS " . idf_escape($L["Name"]) . ";;\n" : "") . "$g;;\n\n";
                        }
                    }
                    echo ($Ef && JUSH == 'sql' ? "DELIMITER ;;\n\n$Ef" . "DELIMITER ;\n\n" : $Ef);
                }
                if ($_POST["table_style"] || $_POST["data_style"]) {
                    $Ei = array();
                    foreach (table_status('', true) as $D => $S) {
                        $R = (DB == "" || in_array($D, (array)$_POST["tables"]));
                        $_b = (DB == "" || in_array($D, (array)$_POST["data"]));
                        if ($R || $_b) {
                            $Vh = null;
                            if ($Ac == "tar") {
                                $Vh = new
                                    TmpFile;
                                ob_start(array($Vh, 'write'), 1e5);
                            }
                            adminer()->dumpTable($D, ($R ? $_POST["table_style"] : ""), (is_view($S) ? 2 : 0));
                            if (is_view($S)) $Ei[] = $D;
                            elseif ($_b) {
                                $m = fields($D);
                                adminer()->dumpData($D, $_POST["data_style"], "SELECT *" . convert_fields($m, $m) . " FROM " . table($D));
                            }
                            if ($Xd && $_POST["triggers"] && $R && ($gi = trigger_sql($D))) echo "\nDELIMITER ;;\n$gi\nDELIMITER ;\n";
                            if ($Ac == "tar") {
                                ob_end_flush();
                                tar_file((DB != "" ? "" : "$i/") . "$D.csv", $Vh);
                            } elseif ($Xd) echo "\n";
                        }
                    }
                    if (function_exists('Adminer\foreign_keys_sql')) {
                        foreach (table_status('', true) as $D => $S) {
                            $R = (DB == "" || in_array($D, (array)$_POST["tables"]));
                            if ($R && !is_view($S)) echo
                            foreign_keys_sql($D);
                        }
                    }
                    foreach (
                        $Ei
                        as $Di
                    ) adminer()->dumpTable($Di, $_POST["table_style"], 1);
                    if ($Ac == "tar") echo
                    pack("x512");
                }
            }
        }
        adminer()->dumpFooter();
        exit;
    }
    page_header('Export', $k, ($_GET["export"] != "" ? array("table" => $_GET["export"]) : array()), h(DB));
    echo '
<form action="" method="post">
<table class="layout">
';
    $Db = array('', 'USE', 'DROP+CREATE', 'CREATE');
    $Dh = array('', 'DROP+CREATE', 'CREATE');
    $Ab = array('', 'TRUNCATE+INSERT', 'INSERT');
    if (JUSH == "sql") $Ab[] = 'INSERT+UPDATE';
    $L = get_settings("adminer_export");
    if (!$L) $L = array("output" => "text", "format" => "sql", "db_style" => (DB != "" ? "" : "CREATE"), "table_style" => "DROP+CREATE", "data_style" => "INSERT");
    if (!isset($L["events"])) {
        $L["routines"] = $L["events"] = ($_GET["dump"] == "");
        $L["triggers"] = $L["table_style"];
    }
    echo "<tr><th>" . 'Output' . "<td>" . html_radios("output", adminer()->dumpOutput(), $L["output"]) . "\n", "<tr><th>" . 'Format' . "<td>" . html_radios("format", adminer()->dumpFormat(), $L["format"]) . "\n", (JUSH == "sqlite" ? "" : "<tr><th>" . 'Database' . "<td>" . html_select('db_style', $Db, $L["db_style"]) . (support("type") ? checkbox("types", 1, $L["types"], 'User types') : "") . (support("routine") ? checkbox("routines", 1, $L["routines"], 'Routines') : "") . (support("event") ? checkbox("events", 1, $L["events"], 'Events') : "")), "<tr><th>" . 'Tables' . "<td>" . html_select('table_style', $Dh, $L["table_style"]) . checkbox("auto_increment", 1, $L["auto_increment"], 'Auto Increment') . (support("trigger") ? checkbox("triggers", 1, $L["triggers"], 'Triggers') : ""), "<tr><th>" . 'Data' . "<td>" . html_select('data_style', $Ab, $L["data_style"]), '</table>
<p><input type="submit" value="Export">
', input_token(), '
<table>
', script("qsl('table').onclick = dumpClick;");
    $hg = array();
    if (DB != "") {
        $Sa = ($a != "" ? "" : " checked");
        echo "<thead><tr>", "<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Sa>" . 'Tables' . "</label>" . script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);", ""), "<th style='text-align: right;'><label class='block'>" . 'Data' . "<input type='checkbox' id='check-data'$Sa></label>" . script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);", ""), "</thead>\n";
        $Ei = "";
        $Fh = tables_list();
        foreach (
            $Fh
            as $D => $U
        ) {
            $gg = preg_replace('~_.*~', '', $D);
            $Sa = ($a == "" || $a == (substr($a, -1) == "%" ? "$gg%" : $D));
            $jg = "<tr><td>" . checkbox("tables[]", $D, $Sa, $D, "", "block");
            if ($U !== null && !preg_match('~table~i', $U)) $Ei
                .= "$jg\n";
            else
                echo "$jg<td align='right'><label class='block'><span id='Rows-" . h($D) . "'></span>" . checkbox("data[]", $D, $Sa) . "</label>\n";
            $hg[$gg]++;
        }
        echo $Ei;
        if ($Fh) echo
        script("ajaxSetHtml('" . js_escape(ME) . "script=db');");
    } else {
        echo "<thead><tr><th style='text-align: left;'>", "<label class='block'><input type='checkbox' id='check-databases'" . ($a == "" ? " checked" : "") . ">" . 'Database' . "</label>", script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);", ""), "</thead>\n";
        $h = adminer()->databases();
        if ($h) {
            foreach (
                $h
                as $i
            ) {
                if (!information_schema($i)) {
                    $gg = preg_replace('~_.*~', '', $i);
                    echo "<tr><td>" . checkbox("databases[]", $i, $a == "" || $a == "$gg%", $i, "", "block") . "\n";
                    $hg[$gg]++;
                }
            }
        } else
            echo "<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";
    }
    echo '</table>
</form>
';
    $Mc = true;
    foreach (
        $hg
        as $y => $X
    ) {
        if ($y != "" && $X > 1) {
            echo ($Mc ? "<p>" : " ") . "<a href='" . h(ME) . "dump=" . urlencode("$y%") . "'>" . h($y) . "</a>";
            $Mc = false;
        }
    }
} elseif (isset($_GET["privileges"])) {
    page_header('Privileges');
    echo '<p class="links"><a href="' . h(ME) . 'user=">' . 'Create user' . "</a>";
    $J = connection()->query("SELECT User, Host FROM mysql." . (DB == "" ? "user" : "db WHERE " . q(DB) . " LIKE Db") . " ORDER BY Host, User");
    $cd = $J;
    if (!$J) $J = connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");
    echo "<form action=''><p>\n";
    hidden_fields_get();
    echo
    input_hidden("db", DB), ($cd ? "" : input_hidden("grant")), "<table class='odds'>\n", "<thead><tr><th>" . 'Username' . "<th>" . 'Server' . "<th></thead>\n";
    while ($L = $J->fetch_assoc()) echo '<tr><td>' . h($L["User"]) . "<td>" . h($L["Host"]) . '<td><a href="' . h(ME . 'user=' . urlencode($L["User"]) . '&host=' . urlencode($L["Host"])) . '">' . 'Edit' . "</a>\n";
    if (!$cd || DB != "") echo "<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='" . 'Edit' . "'>\n";
    echo "</table>\n", "</form>\n";
} elseif (isset($_GET["sql"])) {
    if (!$k && $_POST["export"]) {
        save_settings(array("output" => $_POST["output"], "format" => $_POST["format"]), "adminer_import");
        dump_headers("sql");
        adminer()->dumpTable("", "");
        adminer()->dumpData("", "table", $_POST["query"]);
        adminer()->dumpFooter();
        exit;
    }
    restart_session();
    $rd = &get_session("queries");
    $qd = &$rd[DB];
    if (!$k && $_POST["clear"]) {
        $qd = array();
        redirect(remove_from_uri("history"));
    }
    stop_session();
    page_header((isset($_GET["import"]) ? 'Import' : 'SQL command'), $k);
    $re = '--' . (JUSH == 'sql' ? ' ' : '');
    if (!$k && $_POST) {
        $p = false;
        if (!isset($_GET["import"])) $I = $_POST["query"];
        elseif ($_POST["webfile"]) {
            $kh = adminer()->importServerPath();
            $p = @fopen((file_exists($kh) ? $kh : "compress.zlib://$kh.gz"), "rb");
            $I = ($p ? fread($p, 1e6) : false);
        } else $I = get_file("sql_file", true, ";");
        if (is_string($I)) {
            if (function_exists('memory_get_usage') && ($Ge = ini_bytes("memory_limit")) != "-1") @ini_set("memory_limit", max($Ge, strval(2 * strlen($I) + memory_get_usage() + 8e6)));
            if ($I != "" && strlen($I) < 1e6) {
                $H = $I . (preg_match("~;[ \t\r\n]*\$~", $I) ? "" : ";");
                if (!$qd || first(end($qd)) != $H) {
                    restart_session();
                    $qd[] = array($H, time());
                    set_session("queries", $rd);
                    stop_session();
                }
            }
            $ih = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$re)[^\n]*\n?|--\r?\n)";
            $Lb = ";";
            $ff = 0;
            $jc = true;
            $f = connect();
            if ($f && DB != "") {
                $f->select_db(DB);
                if ($_GET["ns"] != "") set_schema($_GET["ns"], $f);
            }
            $eb = 0;
            $rc = array();
            $Kf = '[\'"' . (JUSH == "sql" ? '`#' : (JUSH == "sqlite" ? '`[' : (JUSH == "mssql" ? '[' : ''))) . ']|/\*|' . $re . '|$' . (JUSH == "pgsql" ? '|\$[^$]*\$' : '');
            $Zh = microtime(true);
            $ha = get_settings("adminer_import");
            $ac = adminer()->dumpFormat();
            unset($ac["sql"]);
            while ($I != "") {
                if (!$ff && preg_match("~^$ih*+DELIMITER\\s+(\\S+)~i", $I, $B)) {
                    $Lb = preg_quote($B[1]);
                    $I = substr($I, strlen($B[0]));
                } elseif (!$ff && JUSH == 'pgsql' && preg_match("~^($ih*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i", $I, $B)) {
                    $Lb = "\n\\\\\\.\r?\n";
                    $ff = strlen($B[0]);
                } else {
                    preg_match("($Lb\\s*|$Kf)", $I, $B, PREG_OFFSET_CAPTURE, $ff);
                    list($Vc, $bg) = $B[0];
                    if (!$Vc && $p && !feof($p)) $I
                        .= fread($p, 1e5);
                    else {
                        if (!$Vc && rtrim($I) == "") break;
                        $ff = $bg + strlen($Vc);
                        if ($Vc && !preg_match("(^$Lb)", $Vc)) {
                            $La = driver()->hasCStyleEscapes() || (JUSH == "pgsql" && ($bg > 0 && strtolower($I[$bg - 1]) == "e"));
                            $Uf = ($Vc == '/*' ? '\*/' : ($Vc == '[' ? ']' : (preg_match("~^$re|^#~", $Vc) ? "\n" : preg_quote($Vc) . ($La ? '|\\\\.' : ''))));
                            while (preg_match("($Uf|\$)s", $I, $B, PREG_OFFSET_CAPTURE, $ff)) {
                                $N = $B[0][0];
                                if (!$N && $p && !feof($p)) $I
                                    .= fread($p, 1e5);
                                else {
                                    $ff = $B[0][1] + strlen($N);
                                    if (!$N || $N[0] != "\\") break;
                                }
                            }
                        } else {
                            $jc = false;
                            $H = substr($I, 0, $bg + ($Lb[0] == "\n" ? 3 : 0));
                            $eb++;
                            $jg = "<pre id='sql-$eb'><code class='jush-" . JUSH . "'>" . adminer()->sqlCommandQuery($H) . "</code></pre>\n";
                            if (JUSH == "sqlite" && preg_match("~^$ih*+ATTACH\\b~i", $H, $B)) {
                                echo $jg, "<p class='error'>" . 'ATTACH queries are not supported.' . "\n";
                                $rc[] = " <a href='#sql-$eb'>$eb</a>";
                                if ($_POST["error_stops"]) break;
                            } else {
                                if (!$_POST["only_errors"]) {
                                    echo $jg;
                                    ob_flush();
                                    flush();
                                }
                                $oh = microtime(true);
                                if (connection()->multi_query($H) && $f && preg_match("~^$ih*+USE\\b~i", $H)) $f->query($H);
                                do {
                                    $J = connection()->store_result();
                                    if (connection()->error) {
                                        echo ($_POST["only_errors"] ? $jg : ""), "<p class='error'>" . 'Error in query' . (connection()->errno ? " (" . connection()->errno . ")" : "") . ": " . error() . "\n";
                                        $rc[] = " <a href='#sql-$eb'>$eb</a>";
                                        if ($_POST["error_stops"]) break
                                            2;
                                    } else {
                                        $Oh = " <span class='time'>(" . format_time($oh) . ")</span>" . (strlen($H) < 1000 ? " <a href='" . h(ME) . "sql=" . urlencode(trim($H)) . "'>" . 'Edit' . "</a>" : "");
                                        $ja = connection()->affected_rows;
                                        $Hi = ($_POST["only_errors"] ? "" : driver()->warnings());
                                        $Ii = "warnings-$eb";
                                        if ($Hi) $Oh
                                            .= ", <a href='#$Ii'>" . 'Warnings' . "</a>" . script("qsl('a').onclick = partial(toggle, '$Ii');", "");
                                        $zc = null;
                                        $yf = null;
                                        $_c = "explain-$eb";
                                        if (is_object($J)) {
                                            $z = $_POST["limit"];
                                            $yf = print_select_result($J, $f, array(), $z);
                                            if (!$_POST["only_errors"]) {
                                                echo "<form action='' method='post'>\n";
                                                $cf = $J->num_rows;
                                                echo "<p class='sql-footer'>" . ($cf ? ($z && $cf > $z ? sprintf('%d / ', $z) : "") . lang_format(array('%d row', '%d rows'), $cf) : ""), $Oh;
                                                if ($f && preg_match("~^($ih|\\()*+SELECT\\b~i", $H) && ($zc = explain($f, $H))) echo ", <a href='#$_c'>Explain</a>" . script("qsl('a').onclick = partial(toggle, '$_c');", "");
                                                $t = "export-$eb";
                                                echo ", <a href='#$t'>" . 'Export' . "</a>" . script("qsl('a').onclick = partial(toggle, '$t');", "") . "<span id='$t' class='hidden'>: " . html_select("output", adminer()->dumpOutput(), $ha["output"]) . " " . html_select("format", $ac, $ha["format"]) . input_hidden("query", $H) . "<input type='submit' name='export' value='" . 'Export' . "'>" . input_token() . "</span>\n" . "</form>\n";
                                            }
                                        } else {
                                            if (preg_match("~^$ih*+(CREATE|DROP|ALTER)$ih++(DATABASE|SCHEMA)\\b~i", $H)) {
                                                restart_session();
                                                set_session("dbs", null);
                                                stop_session();
                                            }
                                            if (!$_POST["only_errors"]) echo "<p class='message' title='" . h(connection()->info) . "'>" . lang_format(array('Query executed OK, %d row affected.', 'Query executed OK, %d rows affected.'), $ja) . "$Oh\n";
                                        }
                                        echo ($Hi ? "<div id='$Ii' class='hidden'>\n$Hi</div>\n" : "");
                                        if ($zc) {
                                            echo "<div id='$_c' class='hidden explain'>\n";
                                            print_select_result($zc, $f, $yf);
                                            echo "</div>\n";
                                        }
                                    }
                                    $oh = microtime(true);
                                } while (connection()->next_result());
                            }
                            $I = substr($I, $ff);
                            $ff = 0;
                        }
                    }
                }
            }
            if ($jc) echo "<p class='message'>" . 'No commands to execute.' . "\n";
            elseif ($_POST["only_errors"]) echo "<p class='message'>" . lang_format(array('%d query executed OK.', '%d queries executed OK.'), $eb - count($rc)), " <span class='time'>(" . format_time($Zh) . ")</span>\n";
            elseif ($rc && $eb > 1) echo "<p class='error'>" . 'Error in query' . ": " . implode("", $rc) . "\n";
        } else
            echo "<p class='error'>" . upload_error($I) . "\n";
    }
    echo '
<form action="" method="post" enctype="multipart/form-data" id="form">
';
    $xc = "<input type='submit' value='" . 'Execute' . "' title='Ctrl+Enter'>";
    if (!isset($_GET["import"])) {
        $H = $_GET["sql"];
        if ($_POST) $H = $_POST["query"];
        elseif ($_GET["history"] == "all") $H = $qd;
        elseif ($_GET["history"] != "") $H = idx($qd[$_GET["history"]], 0);
        echo "<p>";
        textarea("query", $H, 20);
        echo
        script(($_POST ? "" : "qs('textarea').focus();\n") . "qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '" . js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history")) . "');"), "<p>";
        adminer()->sqlPrintAfter();
        echo "$xc\n", 'Limit rows' . ": <input type='number' name='limit' class='size' value='" . h($_POST ? $_POST["limit"] : $_GET["limit"]) . "'>\n";
    } else {
        echo "<fieldset><legend>" . 'File upload' . "</legend><div>";
        $hd = (extension_loaded("zlib") ? "[.gz]" : "");
        echo (ini_bool("file_uploads") ? "SQL$hd (&lt; " . ini_get("upload_max_filesize") . "B): <input type='file' name='sql_file[]' multiple>\n$xc" : 'File uploads are disabled.'), "</div></fieldset>\n";
        $Ad = adminer()->importServerPath();
        if ($Ad) echo "<fieldset><legend>" . 'From server' . "</legend><div>", sprintf('Webserver file %s', "<code>" . h($Ad) . "$hd</code>"), ' <input type="submit" name="webfile" value="' . 'Run file' . '">', "</div></fieldset>\n";
        echo "<p>";
    }
    echo
    checkbox("error_stops", 1, ($_POST ? $_POST["error_stops"] : isset($_GET["import"]) || $_GET["error_stops"]), 'Stop on error') . "\n", checkbox("only_errors", 1, ($_POST ? $_POST["only_errors"] : isset($_GET["import"]) || $_GET["only_errors"]), 'Show only errors') . "\n", input_token();
    if (!isset($_GET["import"]) && $qd) {
        print_fieldset("history", 'History', $_GET["history"] != "");
        for ($X = end($qd); $X; $X = prev($qd)) {
            $y = key($qd);
            list($H, $Oh, $ec) = $X;
            echo '<a href="' . h(ME . "sql=&history=$y") . '">' . 'Edit' . "</a>" . " <span class='time' title='" . @date('Y-m-d', $Oh) . "'>" . @date("H:i:s", $Oh) . "</span>" . " <code class='jush-" . JUSH . "'>" . shorten_utf8(ltrim(str_replace("\n", " ", str_replace("\r", "", preg_replace("~^(#|$re).*~m", '', $H)))), 80, "</code>") . ($ec ? " <span class='time'>($ec)</span>" : "") . "<br>\n";
        }
        echo "<input type='submit' name='clear' value='" . 'Clear' . "'>\n", "<a href='" . h(ME . "sql=&history=all") . "'>" . 'Edit all' . "</a>\n", "</div></fieldset>\n";
    }
    echo '</form>
';
} elseif (isset($_GET["edit"])) {
    $a = $_GET["edit"];
    $m = fields($a);
    $Z = (isset($_GET["select"]) ? ($_POST["check"] && count($_POST["check"]) == 1 ? where_check($_POST["check"][0], $m) : "") : where($_GET, $m));
    $ri = (isset($_GET["select"]) ? $_POST["edit"] : $Z);
    foreach (
        $m
        as $D => $l
    ) {
        if (!isset($l["privileges"][$ri ? "update" : "insert"]) || adminer()->fieldName($l) == "" || $l["generated"]) unset($m[$D]);
    }
    if ($_POST && !$k && !isset($_GET["select"])) {
        $A = $_POST["referer"];
        if ($_POST["insert"]) $A = ($ri ? null : $_SERVER["REQUEST_URI"]);
        elseif (!preg_match('~^.+&select=.+$~', $A)) $A = ME . "select=" . urlencode($a);
        $w = indexes($a);
        $mi = unique_array($_GET["where"], $w);
        $sg = "\nWHERE $Z";
        if (isset($_POST["delete"])) queries_redirect($A, 'Item has been deleted.', driver()->delete($a, $sg, $mi ? 0 : 1));
        else {
            $Q = array();
            foreach (
                $m
                as $D => $l
            ) {
                $X = process_input($l);
                if ($X !== false && $X !== null) $Q[idf_escape($D)] = $X;
            }
            if ($ri) {
                if (!$Q) redirect($A);
                queries_redirect($A, 'Item has been updated.', driver()->update($a, $Q, $sg, $mi ? 0 : 1));
                if (is_ajax()) {
                    page_headers();
                    page_messages($k);
                    exit;
                }
            } else {
                $J = driver()->insert($a, $Q);
                $je = ($J ? last_id($J) : 0);
                queries_redirect($A, sprintf('Item%s has been inserted.', ($je ? " $je" : "")), $J);
            }
        }
    }
    $L = null;
    if ($_POST["save"]) $L = (array)$_POST["fields"];
    elseif ($Z) {
        $O = array();
        foreach (
            $m
            as $D => $l
        ) {
            if (isset($l["privileges"]["select"])) {
                $sa = ($_POST["clone"] && $l["auto_increment"] ? "''" : convert_field($l));
                $O[] = ($sa ? "$sa AS " : "") . idf_escape($D);
            }
        }
        $L = array();
        if (!support("table")) $O = array("*");
        if ($O) {
            $J = driver()->select($a, $O, array($Z), $O, array(), (isset($_GET["select"]) ? 2 : 1));
            if (!$J) $k = error();
            else {
                $L = $J->fetch_assoc();
                if (!$L) $L = false;
            }
            if (isset($_GET["select"]) && (!$L || $J->fetch_assoc())) $L = null;
        }
    }
    if (!support("table") && !$m) {
        if (!$Z) {
            $J = driver()->select($a, array("*"), array(), array("*"));
            $L = ($J ? $J->fetch_assoc() : false);
            if (!$L) $L = array(driver()->primary => "");
        }
        if ($L) {
            foreach (
                $L
                as $y => $X
            ) {
                if (!$Z) $L[$y] = null;
                $m[$y] = array("field" => $y, "null" => ($y != driver()->primary), "auto_increment" => ($y == driver()->primary));
            }
        }
    }
    edit_form($a, $m, $L, $ri, $k);
} elseif (isset($_GET["create"])) {
    $a = $_GET["create"];
    $Of = driver()->partitionBy;
    $Rf = driver()->partitionsInfo($a);
    $yg = referencable_primary($a);
    $Tc = array();
    foreach (
        $yg
        as $Ah => $l
    ) $Tc[str_replace("`", "``", $Ah) . "`" . str_replace("`", "``", $l["field"])] = $Ah;
    $Af = array();
    $S = array();
    if ($a != "") {
        $Af = fields($a);
        $S = table_status1($a);
        if (count($S) < 2) $k = 'No tables.';
    }
    $L = $_POST;
    $L["fields"] = (array)$L["fields"];
    if ($L["auto_increment_col"]) $L["fields"][$L["auto_increment_col"]]["auto_increment"] = true;
    if ($_POST) save_settings(array("comments" => $_POST["comments"], "defaults" => $_POST["defaults"]));
    if ($_POST && !process_fields($L["fields"]) && !$k) {
        if ($_POST["drop"]) queries_redirect(substr(ME, 0, -1), 'Table has been dropped.', drop_tables(array($a)));
        else {
            $m = array();
            $na = array();
            $vi = false;
            $Rc = array();
            $_f = reset($Af);
            $la = " FIRST";
            foreach ($L["fields"] as $y => $l) {
                $o = $Tc[$l["type"]];
                $hi = ($o !== null ? $yg[$o] : $l);
                if ($l["field"] != "") {
                    if (!$l["generated"]) $l["default"] = null;
                    $og = process_field($l, $hi);
                    $na[] = array($l["orig"], $og, $la);
                    if (!$_f || $og !== process_field($_f, $_f)) {
                        $m[] = array($l["orig"], $og, $la);
                        if ($l["orig"] != "" || $la) $vi = true;
                    }
                    if ($o !== null) $Rc[idf_escape($l["field"])] = ($a != "" && JUSH != "sqlite" ? "ADD" : " ") . format_foreign_key(array('table' => $Tc[$l["type"]], 'source' => array($l["field"]), 'target' => array($hi["field"]), 'on_delete' => $l["on_delete"],));
                    $la = " AFTER " . idf_escape($l["field"]);
                } elseif ($l["orig"] != "") {
                    $vi = true;
                    $m[] = array($l["orig"]);
                }
                if ($l["orig"] != "") {
                    $_f = next($Af);
                    if (!$_f) $la = "";
                }
            }
            $F = array();
            if (in_array($L["partition_by"], $Of)) {
                foreach (
                    $L
                    as $y => $X
                ) {
                    if (preg_match('~^partition~', $y)) $F[$y] = $X;
                }
                foreach ($F["partition_names"] as $y => $D) {
                    if ($D == "") {
                        unset($F["partition_names"][$y]);
                        unset($F["partition_values"][$y]);
                    }
                }
                $F["partition_names"] = array_values($F["partition_names"]);
                $F["partition_values"] = array_values($F["partition_values"]);
                if ($F == $Rf) $F = array();
            } elseif (preg_match("~partitioned~", $S["Create_options"])) $F = null;
            $C = 'Table has been altered.';
            if ($a == "") {
                cookie("adminer_engine", $L["Engine"]);
                $C = 'Table has been created.';
            }
            $D = trim($L["name"]);
            queries_redirect(ME . (support("table") ? "table=" : "select=") . urlencode($D), $C, alter_table($a, $D, (JUSH == "sqlite" && ($vi || $Rc) ? $na : $m), $Rc, ($L["Comment"] != $S["Comment"] ? $L["Comment"] : null), ($L["Engine"] && $L["Engine"] != $S["Engine"] ? $L["Engine"] : ""), ($L["Collation"] && $L["Collation"] != $S["Collation"] ? $L["Collation"] : ""), ($L["Auto_increment"] != "" ? number($L["Auto_increment"]) : ""), $F));
        }
    }
    page_header(($a != "" ? 'Alter table' : 'Create table'), $k, array("table" => $a), h($a));
    if (!$_POST) {
        $ji = driver()->types();
        $L = array("Engine" => $_COOKIE["adminer_engine"], "fields" => array(array("field" => "", "type" => (isset($ji["int"]) ? "int" : (isset($ji["integer"]) ? "integer" : "")), "on_update" => "")), "partition_names" => array(""),);
        if ($a != "") {
            $L = $S;
            $L["name"] = $a;
            $L["fields"] = array();
            if (!$_GET["auto_increment"]) $L["Auto_increment"] = "";
            foreach (
                $Af
                as $l
            ) {
                $l["generated"] = $l["generated"] ?: (isset($l["default"]) ? "DEFAULT" : "");
                $L["fields"][] = $l;
            }
            if ($Of) {
                $L += $Rf;
                $L["partition_names"][] = "";
                $L["partition_values"][] = "";
            }
        }
    }
    $b = collations();
    if (is_array(reset($b))) $b = call_user_func_array('array_merge', array_values($b));
    $lc = driver()->engines();
    foreach (
        $lc
        as $kc
    ) {
        if (!strcasecmp($kc, $L["Engine"])) {
            $L["Engine"] = $kc;
            break;
        }
    }
    echo '
<form action="" method="post" id="form">
<p>
';
    if (support("columns") || $a == "") {
        echo 'Table name' . ": <input name='name'" . ($a == "" && !$_POST ? " autofocus" : "") . " data-maxlength='64' value='" . h($L["name"]) . "' autocapitalize='off'>\n", ($lc ? html_select("Engine", array("" => "(" . 'engine' . ")") + $lc, $L["Engine"]) . on_help("event.target.value", 1) . script("qsl('select').onchange = helpClose;") . "\n" : "");
        if ($b) echo "<datalist id='collations'>" . optionlist($b) . "</datalist>\n", (preg_match("~sqlite|mssql~", JUSH) ? "" : "<input list='collations' name='Collation' value='" . h($L["Collation"]) . "' placeholder='(" . 'collation' . ")'>\n");
        echo "<input type='submit' value='" . 'Save' . "'>\n";
    }
    if (support("columns")) {
        echo "<div class='scrollable'>\n", "<table id='edit-fields' class='nowrap'>\n";
        edit_fields($L["fields"], $b, "TABLE", $Tc);
        echo "</table>\n", script("editFields();"), "</div>\n<p>\n", 'Auto Increment' . ": <input type='number' name='Auto_increment' class='size' value='" . h($L["Auto_increment"]) . "'>\n", checkbox("defaults", 1, ($_POST ? $_POST["defaults"] : get_setting("defaults")), 'Default values', "columnShow(this.checked, 5)", "jsonly");
        $hb = ($_POST ? $_POST["comments"] : get_setting("comments"));
        echo (support("comment") ? checkbox("comments", 1, $hb, 'Comment', "editingCommentsClick(this, true);", "jsonly") . ' ' . (preg_match('~\n~', $L["Comment"]) ? "<textarea name='Comment' rows='2' cols='20'" . ($hb ? "" : " class='hidden'") . ">" . h($L["Comment"]) . "</textarea>" : '<input name="Comment" value="' . h($L["Comment"]) . '" data-maxlength="' . (min_version(5.5) ? 2048 : 60) . '"' . ($hb ? "" : " class='hidden'") . '>') : ''), '<p>
<input type="submit" value="Save">
';
    }
    echo '
';
    if ($a != "") echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', $a));
    if ($Of && (JUSH == 'sql' || $a == "")) {
        $Pf = preg_match('~RANGE|LIST~', $L["partition_by"]);
        print_fieldset("partition", 'Partition by', $L["partition_by"]);
        echo "<p>" . html_select("partition_by", array_merge(array(""), $Of), $L["partition_by"]) . on_help("event.target.value.replace(/./, 'PARTITION BY \$&')", 1) . script("qsl('select').onchange = partitionByChange;"), "(<input name='partition' value='" . h($L["partition"]) . "'>)\n", 'Partitions' . ": <input type='number' name='partitions' class='size" . ($Pf || !$L["partition_by"] ? " hidden" : "") . "' value='" . h($L["partitions"]) . "'>\n", "<table id='partition-table'" . ($Pf ? "" : " class='hidden'") . ">\n", "<thead><tr><th>" . 'Partition name' . "<th>" . 'Values' . "</thead>\n";
        foreach ($L["partition_names"] as $y => $X) echo '<tr>', '<td><input name="partition_names[]" value="' . h($X) . '" autocapitalize="off">', ($y == count($L["partition_names"]) - 1 ? script("qsl('input').oninput = partitionNameChange;") : ''), '<td><input name="partition_values[]" value="' . h(idx($L["partition_values"], $y)) . '">';
        echo "</table>\n</div></fieldset>\n";
    }
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["indexes"])) {
    $a = $_GET["indexes"];
    $Hd = array("PRIMARY", "UNIQUE", "INDEX");
    $S = table_status1($a, true);
    $Fd = driver()->indexAlgorithms($S);
    if (preg_match('~MyISAM|M?aria' . (min_version(5.6, '10.0.5') ? '|InnoDB' : '') . '~i', $S["Engine"])) $Hd[] = "FULLTEXT";
    if (preg_match('~MyISAM|M?aria' . (min_version(5.7, '10.2.2') ? '|InnoDB' : '') . '~i', $S["Engine"])) $Hd[] = "SPATIAL";
    $w = indexes($a);
    $ig = array();
    if (JUSH == "mongo") {
        $ig = $w["_id_"];
        unset($Hd[0]);
        unset($w["_id_"]);
    }
    $L = $_POST;
    if ($L) save_settings(array("index_options" => $L["options"]));
    if ($_POST && !$k && !$_POST["add"] && !$_POST["drop_col"]) {
        $oa = array();
        foreach ($L["indexes"] as $v) {
            $D = $v["name"];
            if (in_array($v["type"], $Hd)) {
                $d = array();
                $pe = array();
                $Ob = array();
                $Gd = (support("partial_indexes") ? $v["partial"] : "");
                $Ed = (in_array($v["algorithm"], $Fd) ? $v["algorithm"] : "");
                $Q = array();
                ksort($v["columns"]);
                foreach ($v["columns"] as $y => $c) {
                    if ($c != "") {
                        $oe = idx($v["lengths"], $y);
                        $Mb = idx($v["descs"], $y);
                        $Q[] = idf_escape($c) . ($oe ? "(" . (+$oe) . ")" : "") . ($Mb ? " DESC" : "");
                        $d[] = $c;
                        $pe[] = ($oe ?: null);
                        $Ob[] = $Mb;
                    }
                }
                $yc = $w[$D];
                if ($yc) {
                    ksort($yc["columns"]);
                    ksort($yc["lengths"]);
                    ksort($yc["descs"]);
                    if ($v["type"] == $yc["type"] && array_values($yc["columns"]) === $d && (!$yc["lengths"] || array_values($yc["lengths"]) === $pe) && array_values($yc["descs"]) === $Ob && $yc["partial"] == $Gd && (!$Fd || $yc["algorithm"] == $Ed)) {
                        unset($w[$D]);
                        continue;
                    }
                }
                if ($d) $oa[] = array($v["type"], $D, $Q, $Ed, $Gd);
            }
        }
        foreach (
            $w
            as $D => $yc
        ) $oa[] = array($yc["type"], $D, "DROP");
        if (!$oa) redirect(ME . "table=" . urlencode($a));
        queries_redirect(ME . "table=" . urlencode($a), 'Indexes have been altered.', alter_indexes($a, $oa));
    }
    page_header('Indexes', $k, array("table" => $a), h($a));
    $m = array_keys(fields($a));
    if ($_POST["add"]) {
        foreach ($L["indexes"] as $y => $v) {
            if ($v["columns"][count($v["columns"])] != "") $L["indexes"][$y]["columns"][] = "";
        }
        $v = end($L["indexes"]);
        if ($v["type"] || array_filter($v["columns"], 'strlen')) $L["indexes"][] = array("columns" => array(1 => ""));
    }
    if (!$L) {
        foreach (
            $w
            as $y => $v
        ) {
            $w[$y]["name"] = $y;
            $w[$y]["columns"][] = "";
        }
        $w[] = array("columns" => array(1 => ""));
        $L["indexes"] = $w;
    }
    $pe = (JUSH == "sql" || JUSH == "mssql");
    $ch = ($_POST ? $_POST["options"] : get_setting("index_options"));
    echo '
<form action="" method="post">
<div class="scrollable">
<table class="nowrap">
<thead><tr>
<th id="label-type">Index Type
';
    $zd = " class='idxopts" . ($ch ? "" : " hidden") . "'";
    if ($Fd) echo "<th id='label-algorithm'$zd>" . 'Algorithm' . doc_link(array('sql' => 'create-index.html#create-index-storage-engine-index-types', 'mariadb' => 'storage-engine-index-types/',));
    echo '<th><input type="submit" class="wayoff">', 'Columns' . ($pe ? "<span$zd> (" . 'length' . ")</span>" : "");
    if ($pe || support("descidx")) echo
    checkbox("options", 1, $ch, 'Options', "indexOptionsShow(this.checked)", "jsonly") . "\n";
    echo '<th id="label-name">Name
';
    if (support("partial_indexes")) echo "<th id='label-condition'$zd>" . 'Condition';
    echo '<th><noscript>', icon("plus", "add[0]", "+", 'Add next'), '</noscript>
</thead>
';
    if ($ig) {
        echo "<tr><td>PRIMARY<td>";
        foreach ($ig["columns"] as $y => $c) echo
        select_input(" disabled", $m, $c), "<label><input disabled type='checkbox'>" . 'descending' . "</label> ";
        echo "<td><td>\n";
    }
    $x = 1;
    foreach ($L["indexes"] as $v) {
        if (!$_POST["drop_col"] || $x != key($_POST["drop_col"])) {
            echo "<tr><td>" . html_select("indexes[$x][type]", array(-1 => "") + $Hd, $v["type"], ($x == count($L["indexes"]) ? "indexesAddRow.call(this);" : ""), "label-type");
            if ($Fd) echo "<td$zd>" . html_select("indexes[$x][algorithm]", array_merge(array(""), $Fd), $v['algorithm'], "label-algorithm");
            echo "<td>";
            ksort($v["columns"]);
            $s = 1;
            foreach ($v["columns"] as $y => $c) {
                echo "<span>" . select_input(" name='indexes[$x][columns][$s]' title='" . 'Column' . "'", ($m ? array_combine($m, $m) : $m), $c, "partial(" . ($s == count($v["columns"]) ? "indexesAddColumn" : "indexesChangeColumn") . ", '" . js_escape(JUSH == "sql" ? "" : $_GET["indexes"] . "_") . "')"), "<span$zd>", ($pe ? "<input type='number' name='indexes[$x][lengths][$s]' class='size' value='" . h(idx($v["lengths"], $y)) . "' title='" . 'Length' . "'>" : ""), (support("descidx") ? checkbox("indexes[$x][descs][$s]", 1, idx($v["descs"], $y), 'descending') : ""), "</span> </span>";
                $s++;
            }
            echo "<td><input name='indexes[$x][name]' value='" . h($v["name"]) . "' autocapitalize='off' aria-labelledby='label-name'>\n";
            if (support("partial_indexes")) echo "<td$zd><input name='indexes[$x][partial]' value='" . h($v["partial"]) . "' autocapitalize='off' aria-labelledby='label-condition'>\n";
            echo "<td>" . icon("cross", "drop_col[$x]", "x", 'Remove') . script("qsl('button').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");
        }
        $x++;
    }
    echo '</table>
</div>
<p>
<input type="submit" value="Save">
', input_token(), '</form>
';
} elseif (isset($_GET["database"])) {
    $L = $_POST;
    if ($_POST && !$k && !$_POST["add"]) {
        $D = trim($L["name"]);
        if ($_POST["drop"]) {
            $_GET["db"] = "";
            queries_redirect(remove_from_uri("db|database"), 'Database has been dropped.', drop_databases(array(DB)));
        } elseif (DB !== $D) {
            if (DB != "") {
                $_GET["db"] = $D;
                queries_redirect(preg_replace('~\bdb=[^&]*&~', '', ME) . "db=" . urlencode($D), 'Database has been renamed.', rename_database($D, $L["collation"]));
            } else {
                $h = explode("\n", str_replace("\r", "", $D));
                $vh = true;
                $ie = "";
                foreach (
                    $h
                    as $i
                ) {
                    if (count($h) == 1 || $i != "") {
                        if (!create_database($i, $L["collation"])) $vh = false;
                        $ie = $i;
                    }
                }
                restart_session();
                set_session("dbs", null);
                queries_redirect(ME . "db=" . urlencode($ie), 'Database has been created.', $vh);
            }
        } else {
            if (!$L["collation"]) redirect(substr(ME, 0, -1));
            query_redirect("ALTER DATABASE " . idf_escape($D) . (preg_match('~^[a-z0-9_]+$~i', $L["collation"]) ? " COLLATE $L[collation]" : ""), substr(ME, 0, -1), 'Database has been altered.');
        }
    }
    page_header(DB != "" ? 'Alter database' : 'Create database', $k, array(), h(DB));
    $b = collations();
    $D = DB;
    if ($_POST) $D = $L["name"];
    elseif (DB != "") $L["collation"] = db_collation(DB, $b);
    elseif (JUSH == "sql") {
        foreach (get_vals("SHOW GRANTS") as $cd) {
            if (preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~', $cd, $B) && $B[1]) {
                $D = stripcslashes(idf_unescape("`$B[2]`"));
                break;
            }
        }
    }
    echo '
<form action="" method="post">
<p>
', ($_POST["add"] || strpos($D, "\n") ? '<textarea autofocus name="name" rows="10" cols="40">' . h($D) . '</textarea><br>' : '<input name="name" autofocus value="' . h($D) . '" data-maxlength="64" autocapitalize="off">') . "\n" . ($b ? html_select("collation", array("" => "(" . 'collation' . ")") + $b, $L["collation"]) . doc_link(array('sql' => "charset-charsets.html", 'mariadb' => "supported-character-sets-and-collations/",)) : ""), '<input type="submit" value="Save">
';
    if (DB != "") echo "<input type='submit' name='drop' value='" . 'Drop' . "'>" . confirm(sprintf('Drop %s?', DB)) . "\n";
    elseif (!$_POST["add"] && $_GET["db"] == "") echo
    icon("plus", "add[0]", "+", 'Add next') . "\n";
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["call"])) {
    $ba = ($_GET["name"] ?: $_GET["call"]);
    page_header('Call' . ": " . h($ba), $k);
    $Jg = routine($_GET["call"], (isset($_GET["callf"]) ? "FUNCTION" : "PROCEDURE"));
    $Bd = array();
    $Ef = array();
    foreach ($Jg["fields"] as $s => $l) {
        if (substr($l["inout"], -3) == "OUT" && JUSH == 'sql') $Ef[$s] = "@" . idf_escape($l["field"]) . " AS " . idf_escape($l["field"]);
        if (!$l["inout"] || substr($l["inout"], 0, 2) == "IN") $Bd[] = $s;
    }
    if (!$k && $_POST) {
        $Ma = array();
        foreach ($Jg["fields"] as $y => $l) {
            $X = "";
            if (in_array($y, $Bd)) {
                $X = process_input($l);
                if ($X === false) $X = "''";
                if (isset($Ef[$y])) connection()->query("SET @" . idf_escape($l["field"]) . " = $X");
            }
            if (isset($Ef[$y])) $Ma[] = "@" . idf_escape($l["field"]);
            elseif (in_array($y, $Bd)) $Ma[] = $X;
        }
        $I = (isset($_GET["callf"]) ? "SELECT" : "CALL") . " " . table($ba) . "(" . implode(", ", $Ma) . ")";
        $oh = microtime(true);
        $J = connection()->multi_query($I);
        $ja = connection()->affected_rows;
        echo
        adminer()->selectQuery($I, $oh, !$J);
        if (!$J) echo "<p class='error'>" . error() . "\n";
        else {
            $f = connect();
            if ($f) $f->select_db(DB);
            do {
                $J = connection()->store_result();
                if (is_object($J)) print_select_result($J, $f);
                else
                    echo "<p class='message'>" . lang_format(array('Routine has been called, %d row affected.', 'Routine has been called, %d rows affected.'), $ja) . " <span class='time'>" . @date("H:i:s") . "</span>\n";
            } while (connection()->next_result());
            if ($Ef) print_select_result(connection()->query("SELECT " . implode(", ", $Ef)));
        }
    }
    echo '
<form action="" method="post">
';
    if ($Bd) {
        echo "<table class='layout'>\n";
        foreach (
            $Bd
            as $y
        ) {
            $l = $Jg["fields"][$y];
            $D = $l["field"];
            echo "<tr><th>" . adminer()->fieldName($l);
            $Y = idx($_POST["fields"], $D);
            if ($Y != "") {
                if ($l["type"] == "set") $Y = implode(",", $Y);
            }
            input($l, $Y, idx($_POST["function"], $D, ""));
            echo "\n";
        }
        echo "</table>\n";
    }
    echo '<p>
<input type="submit" value="Call">
', input_token(), '</form>

<pre>
';
    function
    pre_tr($N)
    {
        return
            preg_replace('~^~m', '<tr>', preg_replace('~\|~', '<td>', preg_replace('~\|$~m', "", rtrim($N))));
    }
    $R = '(\+--[-+]+\+\n)';
    $L = '(\| .* \|\n)';
    echo
    preg_replace_callback("~^$R?$L$R?($L*)$R?~m", function ($B) {
        $Nc = pre_tr($B[2]);
        return "<table>\n" . ($B[1] ? "<thead>$Nc</thead>\n" : $Nc) . pre_tr($B[4]) . "\n</table>";
    }, preg_replace('~(\n(    -|mysql)&gt; )(.+)~', "\\1<code class='jush-sql'>\\3</code>", preg_replace('~(.+)\n---+\n~', "<b>\\1</b>\n", h($Jg['comment']))));
    echo '</pre>
';
} elseif (isset($_GET["foreign"])) {
    $a = $_GET["foreign"];
    $D = $_GET["name"];
    $L = $_POST;
    if ($_POST && !$k && !$_POST["add"] && !$_POST["change"] && !$_POST["change-js"]) {
        if (!$_POST["drop"]) {
            $L["source"] = array_filter($L["source"], 'strlen');
            ksort($L["source"]);
            $Ih = array();
            foreach ($L["source"] as $y => $X) $Ih[$y] = $L["target"][$y];
            $L["target"] = $Ih;
        }
        if (JUSH == "sqlite") $J = recreate_table($a, $a, array(), array(), array(" $D" => ($L["drop"] ? "" : " " . format_foreign_key($L))));
        else {
            $oa = "ALTER TABLE " . table($a);
            $J = ($D == "" || queries("$oa DROP " . (JUSH == "sql" ? "FOREIGN KEY " : "CONSTRAINT ") . idf_escape($D)));
            if (!$L["drop"]) $J = queries("$oa ADD" . format_foreign_key($L));
        }
        queries_redirect(ME . "table=" . urlencode($a), ($L["drop"] ? 'Foreign key has been dropped.' : ($D != "" ? 'Foreign key has been altered.' : 'Foreign key has been created.')), $J);
        if (!$L["drop"]) $k = 'Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.';
    }
    page_header('Foreign key', $k, array("table" => $a), h($a));
    if ($_POST) {
        ksort($L["source"]);
        if ($_POST["add"]) $L["source"][] = "";
        elseif ($_POST["change"] || $_POST["change-js"]) $L["target"] = array();
    } elseif ($D != "") {
        $Tc = foreign_keys($a);
        $L = $Tc[$D];
        $L["source"][] = "";
    } else {
        $L["table"] = $a;
        $L["source"] = array("");
    }
    echo '
<form action="" method="post">
';
    $hh = array_keys(fields($a));
    if ($L["db"] != "") connection()->select_db($L["db"]);
    if ($L["ns"] != "") {
        $Bf = get_schema();
        set_schema($L["ns"]);
    }
    $xg = array_keys(array_filter(table_status('', true), 'Adminer\fk_support'));
    $Ih = array_keys(fields(in_array($L["table"], $xg) ? $L["table"] : reset($xg)));
    $nf = "this.form['change-js'].value = '1'; this.form.submit();";
    echo "<p><label>" . 'Target table' . ": " . html_select("table", $xg, $L["table"], $nf) . "</label>\n";
    if (JUSH != "sqlite") {
        $Eb = array();
        foreach (adminer()->databases() as $i) {
            if (!information_schema($i)) $Eb[] = $i;
        }
        echo "<label>" . 'DB' . ": " . html_select("db", $Eb, $L["db"] != "" ? $L["db"] : $_GET["db"], $nf) . "</label>";
    }
    echo
    input_hidden("change-js"), '<noscript><p><input type="submit" name="change" value="Change"></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target</thead>
';
    $x = 0;
    foreach ($L["source"] as $y => $X) {
        echo "<tr>", "<td>" . html_select("source[" . (+$y) . "]", array(-1 => "") + $hh, $X, ($x == count($L["source"]) - 1 ? "foreignAddRow.call(this);" : ""), "label-source"), "<td>" . html_select("target[" . (+$y) . "]", $Ih, idx($L["target"], $y), "", "label-target");
        $x++;
    }
    echo '</table>
<p>
<label>ON DELETE: ', html_select("on_delete", array(-1 => "") + explode("|", driver()->onActions), $L["on_delete"]), '</label>
<label>ON UPDATE: ', html_select("on_update", array(-1 => "") + explode("|", driver()->onActions), $L["on_update"]), '</label>
', doc_link(array('sql' => "innodb-foreign-key-constraints.html", 'mariadb' => "foreign-keys/",)), '<p>
<input type="submit" value="Save">
<noscript><p><input type="submit" name="add" value="Add column"></noscript>
';
    if ($D != "") echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', $D));
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["view"])) {
    $a = $_GET["view"];
    $L = $_POST;
    $Cf = "VIEW";
    if (JUSH == "pgsql" && $a != "") {
        $ph = table_status1($a);
        $Cf = strtoupper($ph["Engine"]);
    }
    if ($_POST && !$k) {
        $D = trim($L["name"]);
        $sa = " AS\n$L[select]";
        $A = ME . "table=" . urlencode($D);
        $C = 'View has been altered.';
        $U = ($_POST["materialized"] ? "MATERIALIZED VIEW" : "VIEW");
        if (!$_POST["drop"] && $a == $D && JUSH != "sqlite" && $U == "VIEW" && $Cf == "VIEW") query_redirect((JUSH == "mssql" ? "ALTER" : "CREATE OR REPLACE") . " VIEW " . table($D) . $sa, $A, $C);
        else {
            $Kh = $D . "_adminer_" . uniqid();
            drop_create("DROP $Cf " . table($a), "CREATE $U " . table($D) . $sa, "DROP $U " . table($D), "CREATE $U " . table($Kh) . $sa, "DROP $U " . table($Kh), ($_POST["drop"] ? substr(ME, 0, -1) : $A), 'View has been dropped.', $C, 'View has been created.', $a, $D);
        }
    }
    if (!$_POST && $a != "") {
        $L = view($a);
        $L["name"] = $a;
        $L["materialized"] = ($Cf != "VIEW");
        if (!$k) $k = error();
    }
    page_header(($a != "" ? 'Alter view' : 'Create view'), $k, array("table" => $a), h($a));
    echo '
<form action="" method="post">
<p>Name: <input name="name" value="', h($L["name"]), '" data-maxlength="64" autocapitalize="off">
', (support("materializedview") ? " " . checkbox("materialized", 1, $L["materialized"], 'Materialized view') : ""), '<p>';
    textarea("select", $L["select"]);
    echo '<p>
<input type="submit" value="Save">
';
    if ($a != "") echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', $a));
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["event"])) {
    $aa = $_GET["event"];
    $Rd = array("YEAR", "QUARTER", "MONTH", "DAY", "HOUR", "MINUTE", "WEEK", "SECOND", "YEAR_MONTH", "DAY_HOUR", "DAY_MINUTE", "DAY_SECOND", "HOUR_MINUTE", "HOUR_SECOND", "MINUTE_SECOND");
    $qh = array("ENABLED" => "ENABLE", "DISABLED" => "DISABLE", "SLAVESIDE_DISABLED" => "DISABLE ON SLAVE");
    $L = $_POST;
    if ($_POST && !$k) {
        if ($_POST["drop"]) query_redirect("DROP EVENT " . idf_escape($aa), substr(ME, 0, -1), 'Event has been dropped.');
        elseif (in_array($L["INTERVAL_FIELD"], $Rd) && isset($qh[$L["STATUS"]])) {
            $Mg = "\nON SCHEDULE " . ($L["INTERVAL_VALUE"] ? "EVERY " . q($L["INTERVAL_VALUE"]) . " $L[INTERVAL_FIELD]" . ($L["STARTS"] ? " STARTS " . q($L["STARTS"]) : "") . ($L["ENDS"] ? " ENDS " . q($L["ENDS"]) : "") : "AT " . q($L["STARTS"])) . " ON COMPLETION" . ($L["ON_COMPLETION"] ? "" : " NOT") . " PRESERVE";
            queries_redirect(substr(ME, 0, -1), ($aa != "" ? 'Event has been altered.' : 'Event has been created.'), queries(($aa != "" ? "ALTER EVENT " . idf_escape($aa) . $Mg . ($aa != $L["EVENT_NAME"] ? "\nRENAME TO " . idf_escape($L["EVENT_NAME"]) : "") : "CREATE EVENT " . idf_escape($L["EVENT_NAME"]) . $Mg) . "\n" . $qh[$L["STATUS"]] . " COMMENT " . q($L["EVENT_COMMENT"]) . rtrim(" DO\n$L[EVENT_DEFINITION]", ";") . ";"));
        }
    }
    page_header(($aa != "" ? 'Alter event' . ": " . h($aa) : 'Create event'), $k);
    if (!$L && $aa != "") {
        $M = get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = " . q(DB) . " AND EVENT_NAME = " . q($aa));
        $L = reset($M);
    }
    echo '
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="', h($L["EVENT_NAME"]), '" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="', h("$L[EXECUTE_AT]$L[STARTS]"), '">
<tr><th title="datetime">End<td><input name="ENDS" value="', h($L["ENDS"]), '">
<tr><th>Every<td><input type="number" name="INTERVAL_VALUE" value="', h($L["INTERVAL_VALUE"]), '" class="size"> ', html_select("INTERVAL_FIELD", $Rd, $L["INTERVAL_FIELD"]), '<tr><th>Status<td>', html_select("STATUS", $qh, $L["STATUS"]), '<tr><th>Comment<td><input name="EVENT_COMMENT" value="', h($L["EVENT_COMMENT"]), '" data-maxlength="64">
<tr><th><td>', checkbox("ON_COMPLETION", "PRESERVE", $L["ON_COMPLETION"] == "PRESERVE", 'On completion preserve'), '</table>
<p>';
    textarea("EVENT_DEFINITION", $L["EVENT_DEFINITION"]);
    echo '<p>
<input type="submit" value="Save">
';
    if ($aa != "") echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', $aa));
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["procedure"])) {
    $ba = ($_GET["name"] ?: $_GET["procedure"]);
    $Jg = (isset($_GET["function"]) ? "FUNCTION" : "PROCEDURE");
    $L = $_POST;
    $L["fields"] = (array)$L["fields"];
    if ($_POST && !process_fields($L["fields"]) && !$k) {
        $zf = routine($_GET["procedure"], $Jg);
        $Kh = "$L[name]_adminer_" . uniqid();
        foreach ($L["fields"] as $y => $l) {
            if ($l["field"] == "") unset($L["fields"][$y]);
        }
        drop_create("DROP $Jg " . routine_id($ba, $zf), create_routine($Jg, $L), "DROP $Jg " . routine_id($L["name"], $L), create_routine($Jg, array("name" => $Kh) + $L), "DROP $Jg " . routine_id($Kh, $L), substr(ME, 0, -1), 'Routine has been dropped.', 'Routine has been altered.', 'Routine has been created.', $ba, $L["name"]);
    }
    page_header(($ba != "" ? (isset($_GET["function"]) ? 'Alter function' : 'Alter procedure') . ": " . h($ba) : (isset($_GET["function"]) ? 'Create function' : 'Create procedure')), $k);
    if (!$_POST) {
        if ($ba == "") $L["language"] = "sql";
        else {
            $L = routine($_GET["procedure"], $Jg);
            $L["name"] = $ba;
        }
    }
    $b = get_vals("SHOW CHARACTER SET");
    sort($b);
    $Kg = routine_languages();
    echo ($b ? "<datalist id='collations'>" . optionlist($b) . "</datalist>" : ""), '
<form action="" method="post" id="form">
<p>Name: <input name="name" value="', h($L["name"]), '" data-maxlength="64" autocapitalize="off">
', ($Kg ? "<label>" . 'Language' . ": " . html_select("language", $Kg, $L["language"]) . "</label>\n" : ""), '<input type="submit" value="Save">
<div class="scrollable">
<table class="nowrap">
';
    edit_fields($L["fields"], $b, $Jg);
    if (isset($_GET["function"])) {
        echo "<tr><td>" . 'Return type';
        edit_type("returns", (array)$L["returns"], $b, array(), (JUSH == "pgsql" ? array("void", "trigger") : array()));
    }
    echo '</table>
', script("editFields();"), '</div>
<p>';
    textarea("definition", $L["definition"]);
    echo '<p>
<input type="submit" value="Save">
';
    if ($ba != "") echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', $ba));
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["check"])) {
    $a = $_GET["check"];
    $D = $_GET["name"];
    $L = $_POST;
    if ($L && !$k) {
        if (JUSH == "sqlite") $J = recreate_table($a, $a, array(), array(), array(), "", array(), "$D", ($L["drop"] ? "" : $L["clause"]));
        else {
            $J = ($D == "" || queries("ALTER TABLE " . table($a) . " DROP CONSTRAINT " . idf_escape($D)));
            if (!$L["drop"]) $J = queries("ALTER TABLE " . table($a) . " ADD" . ($L["name"] != "" ? " CONSTRAINT " . idf_escape($L["name"]) : "") . " CHECK ($L[clause])");
        }
        queries_redirect(ME . "table=" . urlencode($a), ($L["drop"] ? 'Check has been dropped.' : ($D != "" ? 'Check has been altered.' : 'Check has been created.')), $J);
    }
    page_header(($D != "" ? 'Alter check' . ": " . h($D) : 'Create check'), $k, array("table" => $a));
    if (!$L) {
        $Ta = driver()->checkConstraints($a);
        $L = array("name" => $D, "clause" => $Ta[$D]);
    }
    echo '
<form action="" method="post">
<p>';
    if (JUSH != "sqlite") echo 'Name' . ': <input name="name" value="' . h($L["name"]) . '" data-maxlength="64" autocapitalize="off"> ';
    echo
    doc_link(array('sql' => "create-table-check-constraints.html", 'mariadb' => "constraint/",), "?"), '<p>';
    textarea("clause", $L["clause"]);
    echo '<p><input type="submit" value="Save">
';
    if ($D != "") echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', $D));
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["trigger"])) {
    $a = $_GET["trigger"];
    $D = "$_GET[name]";
    $fi = trigger_options();
    $L = (array)trigger($D, $a) + array("Trigger" => $a . "_bi");
    if ($_POST) {
        if (!$k && in_array($_POST["Timing"], $fi["Timing"]) && in_array($_POST["Event"], $fi["Event"]) && in_array($_POST["Type"], $fi["Type"])) {
            $lf = " ON " . table($a);
            $Wb = "DROP TRIGGER " . idf_escape($D) . (JUSH == "pgsql" ? $lf : "");
            $A = ME . "table=" . urlencode($a);
            if ($_POST["drop"]) query_redirect($Wb, $A, 'Trigger has been dropped.');
            else {
                if ($D != "") queries($Wb);
                queries_redirect($A, ($D != "" ? 'Trigger has been altered.' : 'Trigger has been created.'), queries(create_trigger($lf, $_POST)));
                if ($D != "") queries(create_trigger($lf, $L + array("Type" => reset($fi["Type"]))));
            }
        }
        $L = $_POST;
    }
    page_header(($D != "" ? 'Alter trigger' . ": " . h($D) : 'Create trigger'), $k, array("table" => $a));
    echo '
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time<td>', html_select("Timing", $fi["Timing"], $L["Timing"], "triggerChange(/^" . preg_quote($a, "/") . "_[ba][iud]$/, '" . js_escape($a) . "', this.form);"), '<tr><th>Event<td>', html_select("Event", $fi["Event"], $L["Event"], "this.form['Timing'].onchange();"), (in_array("UPDATE OF", $fi["Event"]) ? " <input name='Of' value='" . h($L["Of"]) . "' class='hidden'>" : ""), '<tr><th>Type<td>', html_select("Type", $fi["Type"], $L["Type"]), '</table>
<p>Name: <input name="Trigger" value="', h($L["Trigger"]), '" data-maxlength="64" autocapitalize="off">
', script("qs('#form')['Timing'].onchange();"), '<p>';
    textarea("Statement", $L["Statement"]);
    echo '<p>
<input type="submit" value="Save">
';
    if ($D != "") echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', $D));
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["user"])) {
    $da = $_GET["user"];
    $mg = array("" => array("All privileges" => ""));
    foreach (get_rows("SHOW PRIVILEGES") as $L) {
        foreach (explode(",", ($L["Privilege"] == "Grant option" ? "" : $L["Context"])) as $ob) $mg[$ob][$L["Privilege"]] = $L["Comment"];
    }
    $mg["Server Admin"] += $mg["File access on server"];
    $mg["Databases"]["Create routine"] = $mg["Procedures"]["Create routine"];
    unset($mg["Procedures"]["Create routine"]);
    $mg["Columns"] = array();
    foreach (array("Select", "Insert", "Update", "References") as $X) $mg["Columns"][$X] = $mg["Tables"][$X];
    unset($mg["Server Admin"]["Usage"]);
    foreach ($mg["Tables"] as $y => $X) unset($mg["Databases"][$y]);
    $Ve = array();
    if ($_POST) {
        foreach ($_POST["objects"] as $y => $X) $Ve[$X] = (array)$Ve[$X] + idx($_POST["grants"], $y, array());
    }
    $dd = array();
    $jf = "";
    if (isset($_GET["host"]) && ($J = connection()->query("SHOW GRANTS FOR " . q($da) . "@" . q($_GET["host"])))) {
        while ($L = $J->fetch_row()) {
            if (preg_match('~GRANT (.*) ON (.*) TO ~', $L[0], $B) && preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~', $B[1], $ye, PREG_SET_ORDER)) {
                foreach (
                    $ye
                    as $X
                ) {
                    if ($X[1] != "USAGE") $dd["$B[2]$X[2]"][$X[1]] = true;
                    if (preg_match('~ WITH GRANT OPTION~', $L[0])) $dd["$B[2]$X[2]"]["GRANT OPTION"] = true;
                }
            }
            if (preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~", $L[0], $B)) $jf = $B[1];
        }
    }
    if ($_POST && !$k) {
        $kf = (isset($_GET["host"]) ? q($da) . "@" . q($_GET["host"]) : "''");
        if ($_POST["drop"]) query_redirect("DROP USER $kf", ME . "privileges=", 'User has been dropped.');
        else {
            $Xe = q($_POST["user"]) . "@" . q($_POST["host"]);
            $Sf = $_POST["pass"];
            if ($Sf != '' && !$_POST["hashed"] && !min_version(8)) {
                $Sf = get_val("SELECT PASSWORD(" . q($Sf) . ")");
                $k = !$Sf;
            }
            $tb = false;
            if (!$k) {
                if ($kf != $Xe) {
                    $tb = queries((min_version(5) ? "CREATE USER" : "GRANT USAGE ON *.* TO") . " $Xe IDENTIFIED BY " . (min_version(8) ? "" : "PASSWORD ") . q($Sf));
                    $k = !$tb;
                } elseif ($Sf != $jf) queries("SET PASSWORD FOR $Xe = " . q($Sf));
            }
            if (!$k) {
                $Gg = array();
                foreach (
                    $Ve
                    as $ef => $cd
                ) {
                    if (isset($_GET["grant"])) $cd = array_filter($cd);
                    $cd = array_keys($cd);
                    if (isset($_GET["grant"])) $Gg = array_diff(array_keys(array_filter($Ve[$ef], 'strlen')), $cd);
                    elseif ($kf == $Xe) {
                        $hf = array_keys((array)$dd[$ef]);
                        $Gg = array_diff($hf, $cd);
                        $cd = array_diff($cd, $hf);
                        unset($dd[$ef]);
                    }
                    if (preg_match('~^(.+)\s*(\(.*\))?$~U', $ef, $B) && (!grant("REVOKE", $Gg, $B[2], " ON $B[1] FROM $Xe") || !grant("GRANT", $cd, $B[2], " ON $B[1] TO $Xe"))) {
                        $k = true;
                        break;
                    }
                }
            }
            if (!$k && isset($_GET["host"])) {
                if ($kf != $Xe) queries("DROP USER $kf");
                elseif (!isset($_GET["grant"])) {
                    foreach (
                        $dd
                        as $ef => $Gg
                    ) {
                        if (preg_match('~^(.+)(\(.*\))?$~U', $ef, $B)) grant("REVOKE", array_keys($Gg), $B[2], " ON $B[1] FROM $Xe");
                    }
                }
            }
            queries_redirect(ME . "privileges=", (isset($_GET["host"]) ? 'User has been altered.' : 'User has been created.'), !$k);
            if ($tb) connection()->query("DROP USER $Xe");
        }
    }
    page_header((isset($_GET["host"]) ? 'Username' . ": " . h("$da@$_GET[host]") : 'Create user'), $k, array("privileges" => array('', 'Privileges')));
    $L = $_POST;
    if ($L) $dd = $Ve;
    else {
        $L = $_GET + array("host" => get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));
        $L["pass"] = $jf;
        if ($jf != "") $L["hashed"] = true;
        $dd[(DB == "" || $dd ? "" : idf_escape(addcslashes(DB, "%_\\"))) . ".*"] = array();
    }
    echo '<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="', h($L["host"]), '" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="', h($L["user"]), '" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="', h($L["pass"]), '" autocomplete="new-password">
', ($L["hashed"] ? "" : script("typePassword(qs('#pass'));")), (min_version(8) ? "" : checkbox("hashed", 1, $L["hashed"], 'Hashed', "typePassword(this.form['pass'], this.checked);")), '</table>

', "<table class='odds'>\n", "<thead><tr><th colspan='2'>" . 'Privileges' . doc_link(array('sql' => "grant.html#priv_level"));
    $s = 0;
    foreach (
        $dd
        as $ef => $cd
    ) {
        echo '<th>' . ($ef != "*.*" ? "<input name='objects[$s]' value='" . h($ef) . "' size='10' autocapitalize='off'>" : input_hidden("objects[$s]", "*.*") . "*.*");
        $s++;
    }
    echo "</thead>\n";
    foreach (array("" => "", "Server Admin" => 'Server', "Databases" => 'Database', "Tables" => 'Table', "Columns" => 'Column', "Procedures" => 'Routine',) as $ob => $Mb) {
        foreach ((array)$mg[$ob] as $lg => $fb) {
            echo "<tr><td" . ($Mb ? ">$Mb<td" : " colspan='2'") . ' lang="en" title="' . h($fb) . '">' . h($lg);
            $s = 0;
            foreach (
                $dd
                as $ef => $cd
            ) {
                $D = "'grants[$s][" . h(strtoupper($lg)) . "]'";
                $Y = $cd[strtoupper($lg)];
                if ($ob == "Server Admin" && $ef != (isset($dd["*.*"]) ? "*.*" : ".*")) echo "<td>";
                elseif (isset($_GET["grant"])) echo "<td><select name=$D><option><option value='1'" . ($Y ? " selected" : "") . ">" . 'Grant' . "<option value='0'" . ($Y == "0" ? " selected" : "") . ">" . 'Revoke' . "</select>";
                else
                    echo "<td align='center'><label class='block'>", "<input type='checkbox' name=$D value='1'" . ($Y ? " checked" : "") . ($lg == "All privileges" ? " id='grants-$s-all'>" : ">" . ($lg == "Grant option" ? "" : script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$s-all'); };"))), "</label>";
                $s++;
            }
        }
    }
    echo "</table>\n", '<p>
<input type="submit" value="Save">
';
    if (isset($_GET["host"])) echo '<input type="submit" name="drop" value="Drop">', confirm(sprintf('Drop %s?', "$da@$_GET[host]"));
    echo
    input_token(), '</form>
';
} elseif (isset($_GET["processlist"])) {
    if (support("kill")) {
        if ($_POST && !$k) {
            $ee = 0;
            foreach ((array)$_POST["kill"] as $X) {
                if (kill_process($X)) $ee++;
            }
            queries_redirect(ME . "processlist=", lang_format(array('%d process has been killed.', '%d processes have been killed.'), $ee), $ee || !$_POST["kill"]);
        }
    }
    page_header('Process list', $k);
    echo '
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds">
', script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");
    $s = -1;
    foreach (process_list() as $s => $L) {
        if (!$s) {
            echo "<thead><tr lang='en'>" . (support("kill") ? "<th>" : "");
            foreach (
                $L
                as $y => $X
            ) echo "<th>$y" . doc_link(array('sql' => "show-processlist.html#processlist_" . strtolower($y),));
            echo "</thead>\n";
        }
        echo "<tr>" . (support("kill") ? "<td>" . checkbox("kill[]", $L[JUSH == "sql" ? "Id" : "pid"], 0) : "");
        foreach (
            $L
            as $y => $X
        ) echo "<td>" . ((JUSH == "sql" && $y == "Info" && preg_match("~Query|Killed~", $L["Command"]) && $X != "") || (JUSH == "pgsql" && $y == "current_query" && $X != "<IDLE>") || (JUSH == "oracle" && $y == "sql_text" && $X != "") ? "<code class='jush-" . JUSH . "'>" . shorten_utf8($X, 100, "</code>") . ' <a href="' . h(ME . ($L["db"] != "" ? "db=" . urlencode($L["db"]) . "&" : "") . "sql=" . urlencode($X)) . '">' . 'Clone' . '</a>' : h($X));
        echo "\n";
    }
    echo '</table>
</div>
<p>
';
    if (support("kill")) echo ($s + 1) . "/" . sprintf('%d in total', max_connections()), "<p><input type='submit' value='" . 'Kill' . "'>\n";
    echo
    input_token(), '</form>
', script("tableCheck();");
} elseif (isset($_GET["select"])) {
    $a = $_GET["select"];
    $S = table_status1($a);
    $w = indexes($a);
    $m = fields($a);
    $Tc = column_foreign_keys($a);
    $gf = $S["Oid"];
    $ia = get_settings("adminer_import");
    $Hg = array();
    $d = array();
    $Qg = array();
    $vf = array();
    $Nh = "";
    foreach (
        $m
        as $y => $l
    ) {
        $D = adminer()->fieldName($l);
        $Te = html_entity_decode(strip_tags($D), ENT_QUOTES);
        if (isset($l["privileges"]["select"]) && $D != "") {
            $d[$y] = $Te;
            if (is_shortable($l)) $Nh = adminer()->selectLengthProcess();
        }
        if (isset($l["privileges"]["where"]) && $D != "") $Qg[$y] = $Te;
        if (isset($l["privileges"]["order"]) && $D != "") $vf[$y] = $Te;
        $Hg += $l["privileges"];
    }
    list($O, $r) = adminer()->selectColumnsProcess($d, $w);
    $O = array_unique($O);
    $r = array_unique($r);
    $Vd = count($r) < count($O);
    $Z = adminer()->selectSearchProcess($m, $w);
    $uf = adminer()->selectOrderProcess($m, $w);
    $z = adminer()->selectLimitProcess();
    if ($_GET["val"] && is_ajax()) {
        header("Content-Type: text/plain; charset=utf-8");
        foreach ($_GET["val"] as $ni => $L) {
            $sa = convert_field($m[key($L)]);
            $O = array($sa ?: idf_escape(key($L)));
            $Z[] = where_check($ni, $m);
            $K = driver()->select($a, $O, $Z, $O);
            if ($K) echo
            first($K->fetch_row());
        }
        exit;
    }
    $ig = $pi = array();
    foreach (
        $w
        as $v
    ) {
        if ($v["type"] == "PRIMARY") {
            $ig = array_flip($v["columns"]);
            $pi = ($O ? $ig : array());
            foreach (
                $pi
                as $y => $X
            ) {
                if (in_array(idf_escape($y), $O)) unset($pi[$y]);
            }
            break;
        }
    }
    if ($gf && !$ig) {
        $ig = $pi = array($gf => 0);
        $w[] = array("type" => "PRIMARY", "columns" => array($gf));
    }
    if ($_POST && !$k) {
        $Ki = $Z;
        if (!$_POST["all"] && is_array($_POST["check"])) {
            $Ta = array();
            foreach ($_POST["check"] as $Qa) $Ta[] = where_check($Qa, $m);
            $Ki[] = "((" . implode(") OR (", $Ta) . "))";
        }
        $Ki = ($Ki ? "\nWHERE " . implode(" AND ", $Ki) : "");
        if ($_POST["export"]) {
            save_settings(array("output" => $_POST["output"], "format" => $_POST["format"]), "adminer_import");
            dump_headers($a);
            adminer()->dumpTable($a, "");
            $Xc = ($O ? implode(", ", $O) : "*") . convert_fields($d, $m, $O) . "\nFROM " . table($a);
            $fd = ($r && $Vd ? "\nGROUP BY " . implode(", ", $r) : "") . ($uf ? "\nORDER BY " . implode(", ", $uf) : "");
            $I = "SELECT $Xc$Ki$fd";
            if (is_array($_POST["check"]) && !$ig) {
                $li = array();
                foreach ($_POST["check"] as $X) $li[] = "(SELECT" . limit($Xc, "\nWHERE " . ($Z ? implode(" AND ", $Z) . " AND " : "") . where_check($X, $m) . $fd, 1) . ")";
                $I = implode(" UNION ALL ", $li);
            }
            adminer()->dumpData($a, "table", $I);
            adminer()->dumpFooter();
            exit;
        }
        if (!adminer()->selectEmailProcess($Z, $Tc)) {
            if ($_POST["save"] || $_POST["delete"]) {
                $J = true;
                $ja = 0;
                $Q = array();
                if (!$_POST["delete"]) {
                    foreach ($_POST["fields"] as $D => $X) {
                        $X = process_input($m[$D]);
                        if ($X !== null && ($_POST["clone"] || $X !== false)) $Q[idf_escape($D)] = ($X !== false ? $X : idf_escape($D));
                    }
                }
                if ($_POST["delete"] || $Q) {
                    $I = ($_POST["clone"] ? "INTO " . table($a) . " (" . implode(", ", array_keys($Q)) . ")\nSELECT " . implode(", ", $Q) . "\nFROM " . table($a) : "");
                    if ($_POST["all"] || ($ig && is_array($_POST["check"])) || $Vd) {
                        $J = ($_POST["delete"] ? driver()->delete($a, $Ki) : ($_POST["clone"] ? queries("INSERT $I$Ki" . driver()->insertReturning($a)) : driver()->update($a, $Q, $Ki)));
                        $ja = connection()->affected_rows;
                        if (is_object($J)) $ja += $J->num_rows;
                    } else {
                        foreach ((array)$_POST["check"] as $X) {
                            $Ji = "\nWHERE " . ($Z ? implode(" AND ", $Z) . " AND " : "") . where_check($X, $m);
                            $J = ($_POST["delete"] ? driver()->delete($a, $Ji, 1) : ($_POST["clone"] ? queries("INSERT" . limit1($a, $I, $Ji)) : driver()->update($a, $Q, $Ji, 1)));
                            if (!$J) break;
                            $ja += connection()->affected_rows;
                        }
                    }
                }
                $C = lang_format(array('%d item has been affected.', '%d items have been affected.'), $ja);
                if ($_POST["clone"] && $J && $ja == 1) {
                    $je = last_id($J);
                    if ($je) $C = sprintf('Item%s has been inserted.', " $je");
                }
                queries_redirect(remove_from_uri($_POST["all"] && $_POST["delete"] ? "page" : ""), $C, $J);
                if (!$_POST["delete"]) {
                    $eg = (array)$_POST["fields"];
                    edit_form($a, array_intersect_key($m, $eg), $eg, !$_POST["clone"], $k);
                    page_footer();
                    exit;
                }
            } elseif (!$_POST["import"]) {
                if (!$_POST["val"]) $k = 'Ctrl+click on a value to modify it.';
                else {
                    $J = true;
                    $ja = 0;
                    foreach ($_POST["val"] as $ni => $L) {
                        $Q = array();
                        foreach (
                            $L
                            as $y => $X
                        ) {
                            $y = bracket_escape($y, true);
                            $Q[idf_escape($y)] = (preg_match('~char|text~', $m[$y]["type"]) || $X != "" ? adminer()->processInput($m[$y], $X) : "NULL");
                        }
                        $J = driver()->update($a, $Q, " WHERE " . ($Z ? implode(" AND ", $Z) . " AND " : "") . where_check($ni, $m), ($Vd || $ig ? 0 : 1), " ");
                        if (!$J) break;
                        $ja += connection()->affected_rows;
                    }
                    queries_redirect(remove_from_uri(), lang_format(array('%d item has been affected.', '%d items have been affected.'), $ja), $J);
                }
            } elseif (!is_string($Kc = get_file("csv_file", true))) $k = upload_error($Kc);
            elseif (!preg_match('~~u', $Kc)) $k = 'File must be in UTF-8 encoding.';
            else {
                save_settings(array("output" => $ia["output"], "format" => $_POST["separator"]), "adminer_import");
                $J = true;
                $cb = array_keys($m);
                preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~', $Kc, $ye);
                $ja = count($ye[0]);
                driver()->begin();
                $Wg = ($_POST["separator"] == "csv" ? "," : ($_POST["separator"] == "tsv" ? "\t" : ";"));
                $M = array();
                foreach ($ye[0] as $y => $X) {
                    preg_match_all("~((?>\"[^\"]*\")+|[^$Wg]*)$Wg~", $X . $Wg, $ze);
                    if (!$y && !array_diff($ze[1], $cb)) {
                        $cb = $ze[1];
                        $ja--;
                    } else {
                        $Q = array();
                        foreach ($ze[1] as $s => $Za) $Q[idf_escape($cb[$s])] = ($Za == "" && $m[$cb[$s]]["null"] ? "NULL" : q(preg_match('~^".*"$~s', $Za) ? str_replace('""', '"', substr($Za, 1, -1)) : $Za));
                        $M[] = $Q;
                    }
                }
                $J = (!$M || driver()->insertUpdate($a, $M, $ig));
                if ($J) driver()->commit();
                queries_redirect(remove_from_uri("page"), lang_format(array('%d row has been imported.', '%d rows have been imported.'), $ja), $J);
                driver()->rollback();
            }
        }
    }
    $Ah = adminer()->tableName($S);
    if (is_ajax()) {
        page_headers();
        ob_start();
    } else
        page_header('Select' . ": $Ah", $k);
    $Q = null;
    if (isset($Hg["insert"]) || !support("table")) {
        $Jf = array();
        foreach ((array)$_GET["where"] as $X) {
            if (isset($Tc[$X["col"]]) && count($Tc[$X["col"]]) == 1 && ($X["op"] == "=" || (!$X["op"] && (is_array($X["val"]) || !preg_match('~[_%]~', $X["val"]))))) $Jf["set" . "[" . bracket_escape($X["col"]) . "]"] = $X["val"];
        }
        $Q = $Jf ? "&" . http_build_query($Jf) : "";
    }
    adminer()->selectLinks($S, $Q);
    if (!$d && support("table")) echo "<p class='error'>" . 'Unable to select the table' . ($m ? "." : ": " . error()) . "\n";
    else {
        echo "<form action='' id='form'>\n", "<div style='display: none;'>";
        hidden_fields_get();
        echo (DB != "" ? input_hidden("db", DB) . (isset($_GET["ns"]) ? input_hidden("ns", $_GET["ns"]) : "") : ""), input_hidden("select", $a), "</div>\n";
        adminer()->selectColumnsPrint($O, $d);
        adminer()->selectSearchPrint($Z, $Qg, $w);
        adminer()->selectOrderPrint($uf, $vf, $w);
        adminer()->selectLimitPrint($z);
        adminer()->selectLengthPrint($Nh);
        adminer()->selectActionPrint($w);
        echo "</form>\n";
        $E = $_GET["page"];
        $Wc = null;
        if ($E == "last") {
            $Wc = get_val(count_rows($a, $Z, $Vd, $r));
            $E = floor(max(0, intval($Wc) - 1) / $z);
        }
        $Rg = $O;
        $ed = $r;
        if (!$Rg) {
            $Rg[] = "*";
            $pb = convert_fields($d, $m, $O);
            if ($pb) $Rg[] = substr($pb, 2);
        }
        foreach (
            $O
            as $y => $X
        ) {
            $l = $m[idf_unescape($X)];
            if ($l && ($sa = convert_field($l))) $Rg[$y] = "$sa AS $X";
        }
        if (!$Vd && $pi) {
            foreach (
                $pi
                as $y => $X
            ) {
                $Rg[] = idf_escape($y);
                if ($ed) $ed[] = idf_escape($y);
            }
        }
        $J = driver()->select($a, $Rg, $Z, $ed, $uf, $z, $E, true);
        if (!$J) echo "<p class='error'>" . error() . "\n";
        else {
            if (JUSH == "mssql" && $E) $J->seek($z * $E);
            $ic = array();
            echo "<form action='' method='post' enctype='multipart/form-data'>\n";
            $M = array();
            while ($L = $J->fetch_assoc()) {
                if ($E && JUSH == "oracle") unset($L["RNUM"]);
                $M[] = $L;
            }
            if ($_GET["page"] != "last" && $z && $r && $Vd && JUSH == "sql") $Wc = get_val(" SELECT FOUND_ROWS()");
            if (!$M) echo "<p class='message'>" . 'No rows.' . "\n";
            else {
                $Aa = adminer()->backwardKeys($a, $Ah);
                echo "<div class='scrollable'>", "<table id='table' class='nowrap checkable odds'>", script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"), "<thead><tr>" . (!$r && $O ? "" : "<td><input type='checkbox' id='all-page' class='jsonly'>" . script("qs('#all-page').onclick = partial(formCheck, /check/);", "") . " <a href='" . h($_GET["modify"] ? remove_from_uri("modify") : $_SERVER["REQUEST_URI"] . "&modify=1") . "'>" . 'Modify' . "</a>");
                $Ue = array();
                $Zc = array();
                reset($O);
                $ug = 1;
                foreach ($M[0] as $y => $X) {
                    if (!isset($pi[$y])) {
                        $X = idx($_GET["columns"], key($O)) ?: array();
                        $l = $m[$O ? ($X ? $X["col"] : current($O)) : $y];
                        $D = ($l ? adminer()->fieldName($l, $ug) : ($X["fun"] ? "*" : h($y)));
                        if ($D != "") {
                            $ug++;
                            $Ue[$y] = $D;
                            $c = idf_escape($y);
                            $ud = remove_from_uri('(order|desc)[^=]*|page') . '&order%5B0%5D=' . urlencode($y);
                            $Mb = "&desc%5B0%5D=1";
                            echo "<th id='th[" . h(bracket_escape($y)) . "]'>" . script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});", "");
                            $Yc = apply_sql_function($X["fun"], $D);
                            $gh = isset($l["privileges"]["order"]) || $Yc;
                            echo ($gh ? "<a href='" . h($ud . ($uf[0] == $c || $uf[0] == $y || (!$uf && $Vd && $r[0] == $c) ? $Mb : '')) . "'>$Yc</a>" : $Yc), "<span class='column hidden'>";
                            if ($gh) echo "<a href='" . h($ud . $Mb) . "' title='" . 'descending' . "' class='text'> ↓</a>";
                            if (!$X["fun"] && isset($l["privileges"]["where"])) echo '<a href="#fieldset-search" title="' . 'Search' . '" class="text jsonly"> =</a>', script("qsl('a').onclick = partial(selectSearch, '" . js_escape($y) . "');");
                            echo "</span>";
                        }
                        $Zc[$y] = $X["fun"];
                        next($O);
                    }
                }
                $pe = array();
                if ($_GET["modify"]) {
                    foreach (
                        $M
                        as $L
                    ) {
                        foreach (
                            $L
                            as $y => $X
                        ) $pe[$y] = max($pe[$y], min(40, strlen(utf8_decode($X))));
                    }
                }
                echo ($Aa ? "<th>" . 'Relations' : "") . "</thead>\n";
                if (is_ajax()) ob_end_clean();
                foreach (adminer()->rowDescriptions($M, $Tc) as $Se => $L) {
                    $mi = unique_array($M[$Se], $w);
                    if (!$mi) {
                        $mi = array();
                        reset($O);
                        foreach ($M[$Se] as $y => $X) {
                            if (!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~', current($O))) $mi[$y] = $X;
                            next($O);
                        }
                    }
                    $ni = "";
                    foreach (
                        $mi
                        as $y => $X
                    ) {
                        $l = (array)$m[$y];
                        if ((JUSH == "sql" || JUSH == "pgsql") && preg_match('~char|text|enum|set~', $l["type"]) && strlen($X) > 64) {
                            $y = (strpos($y, '(') ? $y : idf_escape($y));
                            $y = "MD5(" . (JUSH != 'sql' || preg_match("~^utf8~", $l["collation"]) ? $y : "CONVERT($y USING " . charset(connection()) . ")") . ")";
                            $X = md5($X);
                        }
                        $ni
                            .= "&" . ($X !== null ? urlencode("where[" . bracket_escape($y) . "]") . "=" . urlencode($X === false ? "f" : $X) : "null%5B%5D=" . urlencode($y));
                    }
                    echo "<tr>" . (!$r && $O ? "" : "<td>" . checkbox("check[]", substr($ni, 1), in_array(substr($ni, 1), (array)$_POST["check"])) . ($Vd || information_schema(DB) ? "" : " <a href='" . h(ME . "edit=" . urlencode($a) . $ni) . "' class='edit'>" . 'edit' . "</a>"));
                    reset($O);
                    foreach (
                        $L
                        as $y => $X
                    ) {
                        if (isset($Ue[$y])) {
                            $c = current($O);
                            $l = (array)$m[$y];
                            $X = driver()->value($X, $l);
                            if ($X != "" && (!isset($ic[$y]) || $ic[$y] != "")) $ic[$y] = (is_mail($X) ? $Ue[$y] : "");
                            $_ = "";
                            if (preg_match('~blob|bytea|raw|file~', $l["type"]) && $X != "") $_ = ME . 'download=' . urlencode($a) . '&field=' . urlencode($y) . $ni;
                            if (!$_ && $X !== null) {
                                foreach ((array)$Tc[$y] as $o) {
                                    if (count($Tc[$y]) == 1 || end($o["source"]) == $y) {
                                        $_ = "";
                                        foreach ($o["source"] as $s => $hh) $_
                                            .= where_link($s, $o["target"][$s], $M[$Se][$hh]);
                                        $_ = ($o["db"] != "" ? preg_replace('~([?&]db=)[^&]+~', '\1' . urlencode($o["db"]), ME) : ME) . 'select=' . urlencode($o["table"]) . $_;
                                        if ($o["ns"]) $_ = preg_replace('~([?&]ns=)[^&]+~', '\1' . urlencode($o["ns"]), $_);
                                        if (count($o["source"]) == 1) break;
                                    }
                                }
                            }
                            if ($c == "COUNT(*)") {
                                $_ = ME . "select=" . urlencode($a);
                                $s = 0;
                                foreach ((array)$_GET["where"] as $W) {
                                    if (!array_key_exists($W["col"], $mi)) $_
                                        .= where_link($s++, $W["col"], $W["val"], $W["op"]);
                                }
                                foreach (
                                    $mi
                                    as $be => $W
                                ) $_
                                    .= where_link($s++, $be, $W);
                            }
                            $vd = select_value($X, $_, $l, $Nh);
                            $t = h("val[$ni][" . bracket_escape($y) . "]");
                            $fg = idx(idx($_POST["val"], $ni), bracket_escape($y));
                            $dc = !is_array($L[$y]) && is_utf8($vd) && $M[$Se][$y] == $L[$y] && !$Zc[$y] && !$l["generated"];
                            $U = (preg_match('~^(AVG|MIN|MAX)\((.+)\)~', $c, $B) ? $m[idf_unescape($B[2])]["type"] : $l["type"]);
                            $Mh = preg_match('~text|json|lob~', $U);
                            $Wd = preg_match(number_type(), $U) || preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~', $c);
                            echo "<td id='$t'" . ($Wd && ($X === null || is_numeric(strip_tags($vd)) || $U == "money") ? " class='number'" : "");
                            if (($_GET["modify"] && $dc && $X !== null) || $fg !== null) {
                                $id = h($fg !== null ? $fg : $L[$y]);
                                echo ">" . ($Mh ? "<textarea name='$t' cols='30' rows='" . (substr_count($L[$y], "\n") + 1) . "'>$id</textarea>" : "<input name='$t' value='$id' size='$pe[$y]'>");
                            } else {
                                $ue = strpos($vd, "<i>…</i>");
                                echo " data-text='" . ($ue ? 2 : ($Mh ? 1 : 0)) . "'" . ($dc ? "" : " data-warning='" . h('Use edit link to modify this value.') . "'") . ">$vd";
                            }
                        }
                        next($O);
                    }
                    if ($Aa) echo "<td>";
                    adminer()->backwardKeysPrint($Aa, $M[$Se]);
                    echo "</tr>\n";
                }
                if (is_ajax()) exit;
                echo "</table>\n", "</div>\n";
            }
            if (!is_ajax()) {
                if ($M || $E) {
                    $wc = true;
                    if ($_GET["page"] != "last") {
                        if (!$z || (count($M) < $z && ($M || !$E))) $Wc = ($E ? $E * $z : 0) + count($M);
                        elseif (JUSH != "sql" || !$Vd) {
                            $Wc = ($Vd ? false : found_rows($S, $Z));
                            if (intval($Wc) < max(1e4, 2 * ($E + 1) * $z)) $Wc = first(slow_query(count_rows($a, $Z, $Vd, $r)));
                            else $wc = false;
                        }
                    }
                    $Hf = ($z && ($Wc === false || $Wc > $z || $E));
                    if ($Hf) echo (($Wc === false ? count($M) + 1 : $Wc - $E * $z) > $z ? '<p><a href="' . h(remove_from_uri("page") . "&page=" . ($E + 1)) . '" class="loadmore">' . 'Load more data' . '</a>' . script("qsl('a').onclick = partial(selectLoadMore, $z, '" . 'Loading' . "…');", "") : ''), "\n";
                    echo "<div class='footer'><div>\n";
                    if ($Hf) {
                        $Ae = ($Wc === false ? $E + (count($M) >= $z ? 2 : 1) : floor(($Wc - 1) / $z));
                        echo "<fieldset>";
                        if (JUSH != "simpledb") {
                            echo "<legend><a href='" . h(remove_from_uri("page")) . "'>" . 'Page' . "</a></legend>", script("qsl('a').onclick = function () { pageClick(this.href, +prompt('" . 'Page' . "', '" . ($E + 1) . "')); return false; };"), pagination(0, $E) . ($E > 5 ? " …" : "");
                            for ($s = max(1, $E - 4); $s < min($Ae, $E + 5); $s++) echo
                            pagination($s, $E);
                            if ($Ae > 0) echo ($E + 5 < $Ae ? " …" : ""), ($wc && $Wc !== false ? pagination($Ae, $E) : " <a href='" . h(remove_from_uri("page") . "&page=last") . "' title='~$Ae'>" . 'last' . "</a>");
                        } else
                            echo "<legend>" . 'Page' . "</legend>", pagination(0, $E) . ($E > 1 ? " …" : ""), ($E ? pagination($E, $E) : ""), ($Ae > $E ? pagination($E + 1, $E) . ($Ae > $E + 1 ? " …" : "") : "");
                        echo "</fieldset>\n";
                    }
                    echo "<fieldset>", "<legend>" . 'Whole result' . "</legend>";
                    $Tb = ($wc ? "" : "~ ") . $Wc;
                    $of = "const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Tb' : checked); selectCount('selected2', this.checked || !checked ? '$Tb' : checked);";
                    echo
                    checkbox("all", 1, 0, ($Wc !== false ? ($wc ? "" : "~ ") . lang_format(array('%d row', '%d rows'), $Wc) : ""), $of) . "\n", "</fieldset>\n";
                    if (adminer()->selectCommandPrint()) echo '<fieldset', ($_GET["modify"] ? '' : ' class="jsonly"'), '><legend>Modify</legend><div>
<input type="submit" value="Save"', ($_GET["modify"] ? '' : ' title="' . 'Ctrl+click on a value to modify it.' . '"'), '>
</div></fieldset>
<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="Edit">
<input type="submit" name="clone" value="Clone">
<input type="submit" name="delete" value="Delete">', confirm(), '</div></fieldset>
';
                    $Uc = adminer()->dumpFormat();
                    foreach ((array)$_GET["columns"] as $c) {
                        if ($c["fun"]) {
                            unset($Uc['sql']);
                            break;
                        }
                    }
                    if ($Uc) {
                        print_fieldset("export", 'Export' . " <span id='selected2'></span>");
                        $Ff = adminer()->dumpOutput();
                        echo ($Ff ? html_select("output", $Ff, $ia["output"]) . " " : ""), html_select("format", $Uc, $ia["format"]), " <input type='submit' name='export' value='" . 'Export' . "'>\n", "</div></fieldset>\n";
                    }
                    adminer()->selectEmailPrint(array_filter($ic, 'strlen'), $d);
                    echo "</div></div>\n";
                }
                if (adminer()->selectImportPrint()) echo "<p>", "<a href='#import'>" . 'Import' . "</a>", script("qsl('a').onclick = partial(toggle, 'import');", ""), "<span id='import'" . ($_POST["import"] ? "" : " class='hidden'") . ">: ", "<input type='file' name='csv_file'> ", html_select("separator", array("csv" => "CSV,", "csv;" => "CSV;", "tsv" => "TSV"), $ia["format"]), " <input type='submit' name='import' value='" . 'Import' . "'>", "</span>";
                echo
                input_token(), "</form>\n", (!$r && $O ? "" : script("tableCheck();"));
            }
        }
    }
    if (is_ajax()) {
        ob_end_clean();
        exit;
    }
} elseif (isset($_GET["variables"])) {
    $ph = isset($_GET["status"]);
    page_header($ph ? 'Status' : 'Variables');
    $Ai = ($ph ? show_status() : show_variables());
    if (!$Ai) echo "<p class='message'>" . 'No rows.' . "\n";
    else {
        echo "<table>\n";
        foreach (
            $Ai
            as $L
        ) {
            echo "<tr>";
            $y = array_shift($L);
            echo "<th><code class='jush-" . JUSH . ($ph ? "status" : "set") . "'>" . h($y) . "</code>";
            foreach (
                $L
                as $X
            ) echo "<td>" . nl_br(h($X));
        }
        echo "</table>\n";
    }
} elseif (isset($_GET["script"])) {
    header("Content-Type: text/javascript; charset=utf-8");
    if ($_GET["script"] == "db") {
        $yh = array("Data_length" => 0, "Index_length" => 0, "Data_free" => 0);
        foreach (table_status() as $D => $S) {
            json_row("Comment-$D", h($S["Comment"]));
            if (!is_view($S)) {
                foreach (array("Engine", "Collation") as $y) json_row("$y-$D", h($S[$y]));
                foreach ($yh + array("Auto_increment" => 0, "Rows" => 0) as $y => $X) {
                    if ($S[$y] != "") {
                        $X = format_number($S[$y]);
                        if ($X >= 0) json_row("$y-$D", ($y == "Rows" && $X && $S["Engine"] == (JUSH == "pgsql" ? "table" : "InnoDB") ? "~ $X" : $X));
                        if (isset($yh[$y])) $yh[$y] += ($S["Engine"] != "InnoDB" || $y != "Data_free" ? $S[$y] : 0);
                    } elseif (array_key_exists($y, $S)) json_row("$y-$D", "?");
                }
            }
        }
        foreach (
            $yh
            as $y => $X
        ) json_row("sum-$y", format_number($X));
        json_row("");
    } elseif ($_GET["script"] == "kill") connection()->query("KILL " . number($_POST["kill"]));
    else {
        foreach (count_tables(adminer()->databases()) as $i => $X) {
            json_row("tables-$i", $X);
            json_row("size-$i", db_size($i));
        }
        json_row("");
    }
    exit;
} else {
    $Gh = array_merge((array)$_POST["tables"], (array)$_POST["views"]);
    if ($Gh && !$k && !$_POST["search"]) {
        $J = true;
        $C = "";
        if (JUSH == "sql" && $_POST["tables"] && count($_POST["tables"]) > 1 && ($_POST["drop"] || $_POST["truncate"] || $_POST["copy"])) queries("SET foreign_key_checks = 0");
        if ($_POST["truncate"]) {
            if ($_POST["tables"]) $J = truncate_tables($_POST["tables"]);
            $C = 'Tables have been truncated.';
        } elseif ($_POST["move"]) {
            $J = move_tables((array)$_POST["tables"], (array)$_POST["views"], $_POST["target"]);
            $C = 'Tables have been moved.';
        } elseif ($_POST["copy"]) {
            $J = copy_tables((array)$_POST["tables"], (array)$_POST["views"], $_POST["target"]);
            $C = 'Tables have been copied.';
        } elseif ($_POST["drop"]) {
            if ($_POST["views"]) $J = drop_views($_POST["views"]);
            if ($J && $_POST["tables"]) $J = drop_tables($_POST["tables"]);
            $C = 'Tables have been dropped.';
        } elseif (JUSH == "sqlite" && $_POST["check"]) {
            foreach ((array)$_POST["tables"] as $R) {
                foreach (get_rows("PRAGMA integrity_check(" . q($R) . ")") as $L) $C
                    .= "<b>" . h($R) . "</b>: " . h($L["integrity_check"]) . "<br>";
            }
        } elseif (JUSH != "sql") {
            $J = (JUSH == "sqlite" ? queries("VACUUM") : apply_queries("VACUUM" . ($_POST["optimize"] ? "" : " ANALYZE"), $_POST["tables"]));
            $C = 'Tables have been optimized.';
        } elseif (!$_POST["tables"]) $C = 'No tables.';
        elseif ($J = queries(($_POST["optimize"] ? "OPTIMIZE" : ($_POST["check"] ? "CHECK" : ($_POST["repair"] ? "REPAIR" : "ANALYZE"))) . " TABLE " . implode(", ", array_map('Adminer\idf_escape', $_POST["tables"])))) {
            while ($L = $J->fetch_assoc()) $C
                .= "<b>" . h($L["Table"]) . "</b>: " . h($L["Msg_text"]) . "<br>";
        }
        queries_redirect(substr(ME, 0, -1), $C, $J);
    }
    page_header(($_GET["ns"] == "" ? 'Database' . ": " . h(DB) : 'Schema' . ": " . h($_GET["ns"])), $k, true);
    if (adminer()->homepage()) {
        if ($_GET["ns"] !== "") {
            echo "<h3 id='tables-views'>" . 'Tables and views' . "</h3>\n";
            $Fh = tables_list();
            if (!$Fh) echo "<p class='message'>" . 'No tables.' . "\n";
            else {
                echo "<form action='' method='post'>\n";
                if (support("table")) {
                    echo "<fieldset><legend>" . 'Search data in tables' . " <span id='selected2'></span></legend><div>", "<input type='search' name='query' value='" . h($_POST["query"]) . "'>", script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');", ""), " <input type='submit' name='search' value='" . 'Search' . "'>\n", "</div></fieldset>\n";
                    if ($_POST["search"] && $_POST["query"] != "") {
                        $_GET["where"][0]["op"] = driver()->convertOperator("LIKE %%");
                        search_tables();
                    }
                }
                echo "<div class='scrollable'>\n", "<table class='nowrap checkable odds'>\n", script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"), '<thead><tr class="wrap">', '<td><input id="check-all" type="checkbox" class="jsonly">' . script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);", ""), '<th>' . 'Table', '<td>' . 'Engine' . doc_link(array('sql' => 'storage-engines.html')), '<td>' . 'Collation' . doc_link(array('sql' => 'charset-charsets.html', 'mariadb' => 'supported-character-sets-and-collations/')), '<td>' . 'Data Length' . doc_link(array('sql' => 'show-table-status.html',)), '<td>' . 'Index Length' . doc_link(array('sql' => 'show-table-status.html',)), '<td>' . 'Data Free' . doc_link(array('sql' => 'show-table-status.html')), '<td>' . 'Auto Increment' . doc_link(array('sql' => 'example-auto-increment.html', 'mariadb' => 'auto_increment/')), '<td>' . 'Rows' . doc_link(array('sql' => 'show-table-status.html',)), (support("comment") ? '<td>' . 'Comment' . doc_link(array('sql' => 'show-table-status.html',)) : ''), "</thead>\n";
                $T = 0;
                foreach (
                    $Fh
                    as $D => $U
                ) {
                    $Di = ($U !== null && !preg_match('~table|sequence~i', $U));
                    $t = h("Table-" . $D);
                    echo '<tr><td>' . checkbox(($Di ? "views[]" : "tables[]"), $D, in_array("$D", $Gh, true), "", "", "", $t), '<th>' . (support("table") || support("indexes") ? "<a href='" . h(ME) . "table=" . urlencode($D) . "' title='" . 'Show structure' . "' id='$t'>" . h($D) . '</a>' : h($D));
                    if ($Di) echo '<td colspan="6"><a href="' . h(ME) . "view=" . urlencode($D) . '" title="' . 'Alter view' . '">' . (preg_match('~materialized~i', $U) ? 'Materialized view' : 'View') . '</a>', '<td align="right"><a href="' . h(ME) . "select=" . urlencode($D) . '" title="' . 'Select data' . '">?</a>';
                    else {
                        foreach (array("Engine" => array(), "Collation" => array(), "Data_length" => array("create", 'Alter table'), "Index_length" => array("indexes", 'Alter indexes'), "Data_free" => array("edit", 'New item'), "Auto_increment" => array("auto_increment=1&create", 'Alter table'), "Rows" => array("select", 'Select data'),) as $y => $_) {
                            $t = " id='$y-" . h($D) . "'";
                            echo ($_ ? "<td align='right'>" . (support("table") || $y == "Rows" || (support("indexes") && $y != "Data_length") ? "<a href='" . h(ME . "$_[0]=") . urlencode($D) . "'$t title='$_[1]'>?</a>" : "<span$t>?</span>") : "<td id='$y-" . h($D) . "'>");
                        }
                        $T++;
                    }
                    echo (support("comment") ? "<td id='Comment-" . h($D) . "'>" : ""), "\n";
                }
                echo "<tr><td><th>" . sprintf('%d in total', count($Fh)), "<td>" . h(JUSH == "sql" ? get_val("SELECT @@default_storage_engine") : ""), "<td>" . h(db_collation(DB, collations()));
                foreach (array("Data_length", "Index_length", "Data_free") as $y) echo "<td align='right' id='sum-$y'>";
                echo "\n", "</table>\n", "</div>\n";
                if (!information_schema(DB)) {
                    echo "<div class='footer'><div>\n";
                    $zi = "<input type='submit' value='" . 'Vacuum' . "'> " . on_help("'VACUUM'");
                    $rf = "<input type='submit' name='optimize' value='" . 'Optimize' . "'> " . on_help(JUSH == "sql" ? "'OPTIMIZE TABLE'" : "'VACUUM OPTIMIZE'");
                    echo "<fieldset><legend>" . 'Selected' . " <span id='selected'></span></legend><div>" . (JUSH == "sqlite" ? $zi . "<input type='submit' name='check' value='" . 'Check' . "'> " . on_help("'PRAGMA integrity_check'") : (JUSH == "pgsql" ? $zi . $rf : (JUSH == "sql" ? "<input type='submit' value='" . 'Analyze' . "'> " . on_help("'ANALYZE TABLE'") . $rf . "<input type='submit' name='check' value='" . 'Check' . "'> " . on_help("'CHECK TABLE'") . "<input type='submit' name='repair' value='" . 'Repair' . "'> " . on_help("'REPAIR TABLE'") : ""))) . "<input type='submit' name='truncate' value='" . 'Truncate' . "'> " . on_help(JUSH == "sqlite" ? "'DELETE'" : "'TRUNCATE" . (JUSH == "pgsql" ? "'" : " TABLE'")) . confirm() . "<input type='submit' name='drop' value='" . 'Drop' . "'>" . on_help("'DROP TABLE'") . confirm() . "\n";
                    $h = (support("scheme") ? adminer()->schemas() : adminer()->databases());
                    if (count($h) != 1 && JUSH != "sqlite") {
                        $i = (isset($_POST["target"]) ? $_POST["target"] : (support("scheme") ? $_GET["ns"] : DB));
                        echo "<p><label>" . 'Move to other database' . ": ", ($h ? html_select("target", $h, $i) : '<input name="target" value="' . h($i) . '" autocapitalize="off">'), "</label> <input type='submit' name='move' value='" . 'Move' . "'>", (support("copy") ? " <input type='submit' name='copy' value='" . 'Copy' . "'> " . checkbox("overwrite", 1, $_POST["overwrite"], 'overwrite') : ""), "\n";
                    }
                    echo "<input type='hidden' name='all' value=''>", script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));" . (support("table") ? " selectCount('selected2', formChecked(this, /^tables\[/) || $T);" : "") . " }"), input_token(), "</div></fieldset>\n", "</div></div>\n";
                }
                echo "</form>\n", script("tableCheck();");
            }
            echo "<p class='links'><a href='" . h(ME) . "create='>" . 'Create table' . "</a>\n", (support("view") ? "<a href='" . h(ME) . "view='>" . 'Create view' . "</a>\n" : "");
            if (support("routine")) {
                echo "<h3 id='routines'>" . 'Routines' . "</h3>\n";
                $Lg = routines();
                if ($Lg) {
                    echo "<table class='odds'>\n", '<thead><tr><th>' . 'Name' . '<td>' . 'Type' . '<td>' . 'Return type' . "<td></thead>\n";
                    foreach (
                        $Lg
                        as $L
                    ) {
                        $D = ($L["SPECIFIC_NAME"] == $L["ROUTINE_NAME"] ? "" : "&name=" . urlencode($L["ROUTINE_NAME"]));
                        echo '<tr>', '<th><a href="' . h(ME . ($L["ROUTINE_TYPE"] != "PROCEDURE" ? 'callf=' : 'call=') . urlencode($L["SPECIFIC_NAME"]) . $D) . '">' . h($L["ROUTINE_NAME"]) . '</a>', '<td>' . h($L["ROUTINE_TYPE"]), '<td>' . h($L["DTD_IDENTIFIER"]), '<td><a href="' . h(ME . ($L["ROUTINE_TYPE"] != "PROCEDURE" ? 'function=' : 'procedure=') . urlencode($L["SPECIFIC_NAME"]) . $D) . '">' . 'Alter' . "</a>";
                    }
                    echo "</table>\n";
                }
                echo '<p class="links">' . (support("procedure") ? '<a href="' . h(ME) . 'procedure=">' . 'Create procedure' . '</a>' : '') . '<a href="' . h(ME) . 'function=">' . 'Create function' . "</a>\n";
            }
            if (support("event")) {
                echo "<h3 id='events'>" . 'Events' . "</h3>\n";
                $M = get_rows("SHOW EVENTS");
                if ($M) {
                    echo "<table>\n", "<thead><tr><th>" . 'Name' . "<td>" . 'Schedule' . "<td>" . 'Start' . "<td>" . 'End' . "<td></thead>\n";
                    foreach (
                        $M
                        as $L
                    ) echo "<tr>", "<th>" . h($L["Name"]), "<td>" . ($L["Execute at"] ? 'At given time' . "<td>" . $L["Execute at"] : 'Every' . " " . $L["Interval value"] . " " . $L["Interval field"] . "<td>$L[Starts]"), "<td>$L[Ends]", '<td><a href="' . h(ME) . 'event=' . urlencode($L["Name"]) . '">' . 'Alter' . '</a>';
                    echo "</table>\n";
                    $uc = get_val("SELECT @@event_scheduler");
                    if ($uc && $uc != "ON") echo "<p class='error'><code class='jush-sqlset'>event_scheduler</code>: " . h($uc) . "\n";
                }
                echo '<p class="links"><a href="' . h(ME) . 'event=">' . 'Create event' . "</a>\n";
            }
            if ($Fh) echo
            script("ajaxSetHtml('" . js_escape(ME) . "script=db');");
        }
    }
}
page_footer();
