#!/usr/bin/env python

import os
import re
import sqlite3
import sys

if len(sys.argv) < 2:
    sys.exit("Usage: {0} <root> [<db>]".format(sys.argv[0]))

dbfile = sys.argv[2] if len(sys.argv) >= 3 else "library.db"
root = sys.argv[1].rstrip("/")
patt = re.compile("(?P<albumartist>.*?)/(?P<album>.*?)/((?P<track>[0-9]+)\. )?(?P<artist>.*?) - (?P<title>.*)\.[a-z]+", re.I)

db = sqlite3.connect(dbfile)
cur = db.cursor()
cur.execute("DROP TABLE IF EXISTS songs")
cur.execute("CREATE TABLE songs (path TEXT NOT NULL PRIMARY KEY, track INTEGER, title TEXT, artist TEXT, album TEXT, albumartist TEXT)")
cur.execute("DROP TABLE IF EXISTS config")
cur.execute("CREATE TABLE config (key TEXT NOT NULL PRIMARY KEY, value TEXT)")
cur.execute("INSERT INTO config (key, value) VALUES ('root', ?)", (root,))
print("Initialised database {0}.".format(dbfile))

songfiles = []

print("Starting file scan...")
sys.stdout.flush()
for base, subdirs, files in os.walk(root):
    for f in files:
        if os.path.splitext(f)[1] == ".mp3":
            songfiles.append(os.path.join(base[len(root) + 1:], f))
            print("\033[F\033[KFound {0} files so far... (current: {1})".format(len(songfiles), base))
print("\033[F\033[KFound {0} files total.".format(len(songfiles)))

print("Writing tags to db...")
for f in songfiles:
    tags = patt.match(f).groupdict()
    tagtuple = tuple(tags[k] for k in ("track", "title", "artist", "album", "albumartist"))
    cur.execute("INSERT INTO songs (path, track, title, artist, album, albumartist) VALUES (?, ?, ?, ?, ?, ?)", (f,) + tagtuple)
print("\033[F\033[KWrote all tags to db.")

print("Comitting changes...")
db.commit()
db.close()
print("\033[F\033[KAll done!")
