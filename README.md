# EHNET käivitamise juhend Windowsis

## 1. Paigalda vajalik tarkvara

Paigalda arvutisse:

- Visual Studio Code - https://code.visualstudio.com/thank-you?dv=win64user
- Git - https://git-scm.com/install/windows
- XAMPP - https://www.apachefriends.org/
- Composer - https://getcomposer.org/download/ - Composer-Setup.exe (lae alla ja paigalda Windowsi installeriga. Paigaldamisel vali PHP asukohaks `C:\xampp\php\php.exe`.)
- Node.js LTS - https://nodejs.org/en/download (Node.js LTS: lae alla ja paigalda Windowsi installeriga. Node.js paigaldusega tuleb kaasa ka npm.)

XAMPP-is käivita:

- Apache
- MySQL

## 2. Kontrolli, et käsud töötavad

Ava PowerShell ja käivita:

```powershell
git --version
php -v
composer --version
node -v
npm -v
```

Kui `npm` ei tööta, kasuta edaspidi `npm.cmd`.

## 3. Klooni projekt GitHubist

```powershell
cd C:\
mkdir Projektid
cd Projektid
git clone SINU_REPO_LINK
cd ehnet
```

## 4. Ava projekt VS Code’is

```powershell
code .
```

Kui see ei tööta, ava VS Code käsitsi ja vali kloonitud projekti kaust.

## 5. Paigalda PHP sõltuvused

```powershell
composer install
```

## 6. Paigalda JavaScripti sõltuvused

```powershell
npm.cmd install
```

## 7. Loo `.env` fail

```powershell
copy .env.example .env
```

## 8. Genereeri rakenduse võti

```powershell
php artisan key:generate
```

## 9. Loo andmebaas

Kui MySQL root kasutajal parooli ei ole:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS ehnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Või loo andmebaas käsitsi phpMyAdminis:

```text
http://localhost/phpmyadmin
```

Andmebaasi nimi:

```text
ehnet
```

## 10. Kontrolli `.env` faili

`.env` failis peavad olema vähemalt järgmised seaded:

```env
APP_NAME=EHNET
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_TIMEZONE=Europe/Tallinn
APP_LOCALE=et

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ehnet
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
```

Kui MySQL kasutajal on parool, lisa see reale:

```env
DB_PASSWORD=sinu_parool
```

## 11. Loo andmebaasi tabelid ja algandmed

```powershell
php artisan migrate:fresh --seed
```

## 12. Loo storage link

```powershell
php artisan storage:link
```

## 13. Puhasta cache

```powershell
php artisan optimize:clear
```

## 14. Käivita frontend

Ava esimene PowerShelli aken projekti kaustas:

```powershell
npm.cmd run dev
```

Jäta see aken avatuks.

## 15. Käivita Laravel server

Ava teine PowerShelli aken projekti kaustas:

```powershell
php artisan serve
```

Ava brauseris:

```text
http://127.0.0.1:8000
```

## 16. Registreerimislingi leidmine

Kuna e-kirjad salvestatakse logisse, ava fail:

```text
storage/logs/laravel.log
```

Sealt leiab registreerimise lõpetamise lingi.

## 17. Kasulikud käsud

```powershell
php artisan route:list
php artisan optimize:clear
npm.cmd run build
php artisan migrate:fresh --seed
```

## Testimine

Käesolevas versioonis testiti rakendust manuaalselt. Automaattestid on planeeritud järgmisse arendusetappi.
