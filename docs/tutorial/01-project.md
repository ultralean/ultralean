---
title: Tutorial 1 — Create the project
key: tutorial-01
layout: tutorial
---

# 1. Create the project

Start by getting the ultralean source code onto your computer.

## 1.1 Copy the project

On Windows, place the project somewhere such as:

```text
D:\projects\blog
```

The important folders are:

```text
blog/
├── app/
├── public/
├── system/
├── ul
└── composer.json
```

Do not point Apache at the project root. Point the web server's document root at `public/`.

## 1.2 Understand the split

`public/` is the only web-facing directory. `system/` contains the reusable framework code. `app/` contains your application's code and configuration.

This separation prevents files such as configuration and migration SQL from being directly requested by a browser.

## 1.3 Configure the application

Open:

```text
app/Config/app.php
```

Set the environment appropriately. During development, keep debug enabled so useful errors are visible.

Then open:

```text
app/Config/database.php
```

For a first SQLite application, the default connection can point to the SQLite configuration already supplied by the project.

## 1.4 Start the development server

For local development, use ultralean's built-in `serve` command:

```bash
php ul serve
```

You should see a message showing:

```text
Local:   http://127.0.0.1:8000/
```

Open that address in your browser. The command uses `public/` as the web root and `public/index.php` as the PHP development-server router.

### Test on another device

If your phone or another computer is connected to the same local network, start network mode:

```bash
php ul serve --network
```

You will see a Network URL such as:

```text
Network: http://192.168.1.20:8000/
```

Open that address from the other device. If Windows Firewall asks for permission, allow PHP for your Private network. This is local-network testing; it does not automatically make the application public on the Internet.

You can choose another port when needed:

```bash
php ul serve --port=8080
php ul serve --network --port=8080
```

Static files in `public/` remain outside ultralean's application routing.

## 1.5 Run the CLI

From the project root:

```bash
php ul
```

You should see the available commands, including `serve`.

Try:

```bash
php ul migrate:status
php ul serve --help
```

You now know the two main entry points: the web application through `public/index.php` and the CLI through `ul`.

## Next

Continue to [Tutorial 2 — Pages, routes and views]({{ '/tutorial/02-pages/' | relative_url }}).
