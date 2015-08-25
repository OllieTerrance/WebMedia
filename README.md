# WebMedia

A basic media player for the web, allowing streaming of audio files over the internet.

Requires `jQuery.min.js` in `lib/js/`.

## Building the library

The script reads from `files.txt`, which is assumed to list files in following structure:

    <album artist>/<album>/<artist> - <title>.<ext>

Set `$root` to the library location.
