<?
$dbfile = getenv("DATA") . "library.db";
$db = new SQLite3($dbfile);
$root = $db->querySingle("SELECT value FROM config WHERE key = 'root'");
if (array_key_exists("id", $_GET)) {
    $id = $_GET["id"];
    $stmt = $db->prepare("SELECT EXISTS(SELECT 1 FROM songs WHERE id = ?)");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    if (!$stmt->execute()->fetchArray()[0]) {
        http_response_code(404);
        die();
    }
    $path = $root . "/" . $db->querySingle("SELECT path FROM songs WHERE id = $id");
    if (!is_readable($path)) {
        http_response_code(403);
        die();
    }
    $fhandle = fopen($path, "rb");
    if (!$fhandle) {
        http_response_code(403);
        die();
    }
    $finfo = finfo_open();
    $expires = 60 * 60 * 24 * 365;
    header("Pragma: public");
    header("Cache-Control: maxage=" . $expires);
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expires) . " UTC");
    header("Content-Type: " . finfo_file($finfo, $path, FILEINFO_MIME));
    finfo_close($finfo);
    while (!feof($fhandle)) {
        echo fread($fhandle, 4096);
        ob_flush();
        flush();
    }
    fclose($fhandle);
    die();
}
?><!DOCTYPE html>
<html>
    <head>
        <title>Media</title>
        <style>
            body {
                margin: 0;
                font-size: 0.9em;
            }
            #player {
                position: fixed;
                width: 100%;
                height: 30px;
                padding: 5px;
                background-color: #000;
            }
            #player audio {
                width: 100%;
            }
            #list {
                padding-top: 40px;
                width: 100%;
                border-spacing: 0;
            }
            #list .file td {
                padding: 0 5px 2px;
                cursor: default;
            }
            #list .file td:nth-child(1) {
                text-align: right;
            }
            #list .file td:nth-child(2) {
                font-weight: bold;
            }
            #list .file td:nth-child(4), #list .file td:nth-child(5) {
                padding-top: 1px;
                font-size: 0.8em;
            }
            #list .file:nth-child(odd) {
                background-color: #eee;
            }
            #list .file.playing {
                background-color: #acf;
            }
        </style>
    </head>
    <body>
        <div id="player">
            <audio controls></audio>
        </div>
        <table id="list">
<?
$files = $db->query("SELECT id, path, track, title, artist, album, albumartist FROM songs ORDER BY LOWER(albumartist) ASC, LOWER(album) ASC, track ASC, LOWER(title) ASC");
while ($file = $files->fetchArray()) {
?>
            <tr class="file" data-name="<?=htmlspecialchars($file["artist"])?> - <?=htmlspecialchars($file["title"])?>" data-id="<?=$file["id"]?>">
                <td><?=htmlspecialchars($file["track"])?></td>
                <td><?=htmlspecialchars($file["title"])?></td>
                <td><?=htmlspecialchars($file["artist"])?></td>
                <td><?=htmlspecialchars($file["album"])?></td>
                <td><?=htmlspecialchars($file["albumartist"])?></td>
            </tr>
<?
}
?>
        </table>
        <script src="/.cdn/js/jquery.min.js"></script>
        <script>
            $(document).ready(function(e) {
                $("#player audio").on("canplay", function(e) {
                    this.play();
                }).on("ended", function(e) {
                    var $next = $("#list .file.playing").next();
                    if ($next.length) {
                        $next.click();
                    } else {
                        $("#list .file:first").click();
                    }
                });
                $("#list .file").click(function(e) {
                    var player = $("#player audio")[0];
                    player.src = "?id=" + $(this).data("id");
                    player.load();
                    document.title = "\u266a " + $(this).data("name") + " | Media";
                    $("#list .file.playing").removeClass("playing");
                    $(this).addClass("playing");
                });
            });
        </script>
    </body>
</html>
