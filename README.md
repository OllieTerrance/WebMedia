# WebMedia

A basic media player for the web, allowing streaming of audio files over the internet.

Requires `jQuery.min.js` in `lib/js/`.

## Building the library

The script reads from `library.db`, which is a database containing files and their metadata.

Included in the repo is `mkdb.py`, which generates this database according to a filename pattern.

It is assumed that files in the directory will have the following structure:

    <album artist>/<album>/<artist> - <title>.<ext>
