<?
$root = "/mnt/Oracle/Music/";
if (array_key_exists("f", $_GET)) {
    if (strpos("/" . $_GET["f"] . "/", "/../") !== false) {
        http_response_code(401);
        die();
    }
    $path = $root . $_GET["f"];
    if (!is_readable($path)) {
        http_response_code(404);
        die();
    }
    $finfo = finfo_open();
    header("Content-type: " . finfo_file($finfo, $path, FILEINFO_MIME));
    finfo_close($finfo);
    die(file_get_contents($path));
}
$paths = fopen("files.txt", "r");
if (!$paths) die("Failed to open file list.");
?><!DOCTYPE html>
<html>
    <head>
        <title>Media</title>
        <style>
            body {
                margin: 0;
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
            }
            #list > .file {
                padding: 2px 5px 5px;
                cursor: default;
            }
            #list > .file:nth-child(odd) {
                background-color: #ddd;
            }
            #list > .file.playing {
                background-color: #acf;
            }
        </style>
    </head>
    <body>
        <div id="player">
            <audio controls></audio>
        </div>
        <div id="list">
<?
while (($path = fgets($paths)) !== false) {
    list($albumartist, $album, $filename) = explode("/", $path);
    list($artists, $title) = explode(" - ", $filename, 2);
    $title = explode(".", $title);
    array_pop($title);
    $title = implode(".", $title);
?>
            <div class="file" data-path="<?=htmlspecialchars(trim($path))?>"><strong><?=htmlspecialchars($title)?></strong> by <?=htmlspecialchars($artists)?> <small>[<?=htmlspecialchars($album)?> by <?=htmlspecialchars($albumartist)?>]</small></div>
<?
}
fclose($paths);
?>
        </div>
        <script src="lib/js/jquery.min.js"></script>
        <script>
            $("#player audio").on("canplay", function(e) {
                this.play();
            });
            $("#list > .file").click(function(e) {
                var player = $("#player audio")[0];
                player.src = "?f=" + encodeURIComponent($(this).data("path"));
                player.load();
                document.title = "\u266a " + $(this).data("path");
                $("#list > .file.playing").removeClass("playing");
                $(this).addClass("playing");
            });
        </script>
    </body>
</html>
