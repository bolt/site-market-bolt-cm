Bolt Extensions Market Place Repository
=======================================

Dump package repository JSON from database

```
./app/nut package:dump
```


Rebuild JSON data for all packages:

```
./app/nut package:build 
```

Rebuild a single package's JSON data

```
./app/nut package:build author/pachage

```

Flushing the hook generated update queue:

```
./app/nut package:queue
```

Running extension tests

```
./app/nut package:extension-tester [--wait=n] [--protocol=http] [--protocol=https] [--private-key=~/.ssh/id_rsa]
```
