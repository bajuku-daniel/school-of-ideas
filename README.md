# School of Ideas

Kirby CMS implementation for the School of Ideas website.

Crafted by Daniel Frings & Björn Byns at We and the Machine Studio.

## Stack

- Kirby CMS 5
- PHP via Composer
- Vite for SCSS/JS builds
- DDEV for local development

## Local Development

```bash
ddev start
composer install
npm install
npm run dev
```

Local site:

```text
https://school-of-ideas.ddev.site
```

Kirby Panel:

```text
https://school-of-ideas.ddev.site/panel
```

Production build:

```bash
npm run build
```

## Git Deployment

Recommended workflow:

1. Commit locally.
2. Push to GitHub.
3. Pull on the server.
4. Run Composer install on the server.

Local first push:

```bash
git remote add origin git@github.com:OWNER/REPO.git
git push -u origin main
```

Server setup, first time:

```bash
ssh p708685@185.15.195.192
cd /home/www/p708685
git clone git@github.com:OWNER/REPO.git school-of-ideas
cd school-of-ideas
composer install --no-dev --optimize-autoloader
```

The web root should point to:

```text
/home/www/p708685/school-of-ideas/public
```

Apache needs the committed `public/.htaccess` file. Without it, Kirby media URLs such as `/media/pages/...` can run into internal redirect loops.

If the host can only point to `/home/www/p708685/html`, deploy the project into `/home/www/p708685/school-of-ideas` and symlink `html` to `school-of-ideas/public`:

```bash
cd /home/www/p708685
mv html html_old
ln -s school-of-ideas/public html
```

Future deploys:

```bash
ssh p708685@185.15.195.192
cd /home/www/p708685/school-of-ideas
git pull
composer install --no-dev --optimize-autoloader
```

Built frontend files in `public/assets` are committed, so the production server does not need Node for normal deploys.

## Kirby Content and Accounts

Content is stored in `content/` and is part of the repository.

The following runtime/private folders are intentionally not committed:

- `site/accounts`
- `site/sessions`
- `site/cache`
- `media`
- `public/media`
- `vendor`
- `kirby`
- `node_modules`

Create the production Panel account directly on the server, or copy `site/accounts` manually via a secure channel if you explicitly want the same local account.

## Production Notes

Default production config:

- `debug: false`
- `panel.install: false`

DDEV/local config enables debug and panel installation via host-specific config files.

Before handing the site to the client:

```bash
npm run build
git status
```

Then commit and push.

## Rsync Fallback

If Git is not available on the server, use rsync from the project root:

```bash
rsync -avz --progress \
  --exclude='.ddev' \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='site/cache' \
  --exclude='site/sessions' \
  --exclude='media' \
  --exclude='public/media' \
  --exclude='*.log' \
  /Users/daniel/WATM/SOI/school-of-ideas/ \
  p708685@185.15.195.192:/home/www/p708685/school-of-ideas/
```

Then SSH into the server and run:

```bash
cd /home/www/p708685/school-of-ideas
composer install --no-dev --optimize-autoloader
```
